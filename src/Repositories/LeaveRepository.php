<?php

namespace LeaveManagement\Repositories;

use PDO;

class LeaveRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function fetchCalendarLeaves(): array
    {
        $stmt = $this->pdo->query("SELECT l.*, e.first_name, e.last_name, e.department_id, lt.name AS leave_type
            FROM leaves l 
            JOIN employees e ON l.employee_id = e.id
            JOIN leave_types lt ON l.leave_type_id = lt.id
            WHERE l.status IN ('approved', 'pending')");

        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public function fetchLeaveTypes(int $employeeId): array
    {
        $stmt = $this->pdo->prepare("SELECT lt.id, lt.name, lr.replacement_required
            FROM leave_types lt
            JOIN leave_rules lr ON lr.leave_type_id = lt.id
            JOIN employees e ON e.position_id = lr.position_id
            WHERE e.id = :employee_id
            ORDER BY lt.id");
        $stmt->execute(['employee_id' => $employeeId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function fetchRemainingLeaveDays(int $employeeId): array
    {
        $stmt = $this->pdo->prepare("SELECT lt.id, lt.name AS leave_type, lr.max_days_per_year
            FROM employees e
            JOIN leave_rules lr ON lr.position_id = e.position_id
            JOIN leave_types lt ON lt.id = lr.leave_type_id
            WHERE e.id = :employee_id
            ORDER BY lt.id");
        $stmt->execute(['employee_id' => $employeeId]);
        $leaveTypes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $year = (int) date('Y');
        $yearStart = sprintf('%04d-01-01', $year);
        $yearEnd = sprintf('%04d-12-31', $year);
        $leaves = $this->pdo->prepare("SELECT leave_type_id, start_date, end_date
            FROM leaves
            WHERE employee_id = :employee_id
                AND status = 'approved'
                AND start_date <= :year_end
                AND end_date >= :year_start");
        $leaves->execute([
            'employee_id' => $employeeId,
            'year_start' => $yearStart,
            'year_end' => $yearEnd,
        ]);

        $usedDays = [];
        foreach ($leaves->fetchAll(PDO::FETCH_ASSOC) as $leave) {
            $leaveStart = max($leave['start_date'], $yearStart);
            $leaveEnd = min($leave['end_date'], $yearEnd);
            $leaveTypeId = (int) $leave['leave_type_id'];
            $usedDays[$leaveTypeId] = ($usedDays[$leaveTypeId] ?? 0)
                + $this->countWorkingDays($leaveStart, $leaveEnd);
        }

        return array_map(static function (array $leaveType) use ($usedDays): array {
            $leaveTypeId = (int) $leaveType['id'];

            return [
                'leave_type' => $leaveType['leave_type'],
                'remaining_days' => max(
                    0,
                    (int) $leaveType['max_days_per_year'] - ($usedDays[$leaveTypeId] ?? 0)
                ),
            ];
        }, $leaveTypes);
    }

    public function fetchEmployeeRequestsForYear(int $employeeId, int $year): array
    {
        $yearStart = sprintf('%04d-01-01', $year);
        $nextYearStart = sprintf('%04d-01-01', $year + 1);
        $stmt = $this->pdo->prepare("SELECT
                l.id,
                l.start_date,
                l.end_date,
                l.created_at,
                l.status,
                lt.name AS leave_type
            FROM leaves l
            JOIN leave_types lt ON lt.id = l.leave_type_id
            WHERE l.employee_id = :employee_id
                AND (
                    (l.created_at >= :created_year_start AND l.created_at < :created_next_year_start)
                    OR (l.start_date < :leave_next_year_start AND l.end_date >= :leave_year_start)
                )
            ORDER BY l.start_date ASC, l.id ASC");
        $stmt->execute([
            'employee_id' => $employeeId,
            'created_year_start' => $yearStart,
            'created_next_year_start' => $nextYearStart,
            'leave_next_year_start' => $nextYearStart,
            'leave_year_start' => $yearStart,
        ]);

        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public function fetchEligibleReplacementEmployees(
        int $employeeId,
        int $leaveTypeId,
        string $startDate,
        string $endDate
    ): array
    {
        $requester = $this->fetchEmployeeContext($employeeId);
        $rule = $requester ? $this->fetchLeaveRule($requester['position_id'], $leaveTypeId) : null;
        if (!$rule || !$rule['replacement_required']) {
            return [];
        }

        $candidates = $this->fetchDepartmentEmployees($requester['department_id'], $employeeId);
        if (!$candidates) {
            return [];
        }

        $unavailableIds = $this->fetchUnavailableEmployeeIds($candidates, $startDate, $endDate);
        $samePosition = [];
        $otherPositions = [];

        foreach ($candidates as $candidate) {
            if (isset($unavailableIds[$candidate['id']])) {
                continue;
            }

            $employee = [
                'id' => $candidate['id'],
                'name' => $candidate['first_name'] . ' ' . $candidate['last_name'],
            ];
            if ($candidate['position_id'] === $requester['position_id']) {
                $samePosition[] = $employee;
            } else {
                $otherPositions[] = $employee;
            }
        }

        return $samePosition ?: $otherPositions;
    }

    public function createLeaveRequest(
        int $employeeId,
        int $leaveTypeId,
        string $startDate,
        string $endDate,
        ?int $replacementId,
        int $year
    ): bool {
        $this->pdo->exec('SET TRANSACTION ISOLATION LEVEL READ COMMITTED');
        $this->pdo->beginTransaction();

        try {
            $requester = $this->fetchEmployeeContext($employeeId);
            if (!$requester) {
                throw new \InvalidArgumentException('Your employee account could not be found. Please sign in again.');
            }

            $lockDepartment = $this->pdo->prepare('SELECT id FROM departments WHERE id = :department_id FOR UPDATE');
            $lockDepartment->execute(['department_id' => $requester['department_id']]);
            if (!$lockDepartment->fetchColumn()) {
                throw new \InvalidArgumentException('Your department could not be found.');
            }

            $overlap = $this->pdo->prepare('SELECT 1
                FROM leaves
                WHERE employee_id = :employee_id
                    AND start_date <= :end_date
                    AND end_date >= :start_date
                LIMIT 1');
            $overlap->execute([
                'employee_id' => $employeeId,
                'start_date' => $startDate,
                'end_date' => $endDate,
            ]);
            if ($overlap->fetchColumn()) {
                throw new \InvalidArgumentException('You already have a leave request for some or all of the selected dates.');
            }

            $rule = $this->fetchLeaveRule($requester['position_id'], $leaveTypeId);
            if (!$rule) {
                throw new \InvalidArgumentException('Select a valid leave type.');
            }

            $yearStart = sprintf('%04d-01-01', $year);
            $yearEnd = sprintf('%04d-12-31', $year);
            $usage = $this->pdo->prepare("SELECT start_date, end_date
                FROM leaves
                WHERE employee_id = :employee_id
                    AND leave_type_id = :leave_type_id
                    AND status = 'approved'
                    AND start_date <= :overlap_end
                    AND end_date >= :overlap_start");
            $usage->execute([
                'employee_id' => $employeeId,
                'leave_type_id' => $leaveTypeId,
                'overlap_end' => $yearEnd,
                'overlap_start' => $yearStart,
            ]);
            $usedDays = 0;
            foreach ($usage->fetchAll(PDO::FETCH_ASSOC) as $leave) {
                $usedDays += $this->countWorkingDays(
                    max($leave['start_date'], $yearStart),
                    min($leave['end_date'], $yearEnd)
                );
            }

            $requestedStart = max($startDate, $yearStart);
            $requestedEnd = min($endDate, $yearEnd);
            $requestedDays = $requestedStart <= $requestedEnd
                ? $this->countWorkingDays($requestedStart, $requestedEnd)
                : 0;
            if ($usedDays + $requestedDays > (int) $rule['max_days_per_year']) {
                throw new \InvalidArgumentException('You do not have enough remaining leave days for this leave type this calendar year.');
            }

            $eligibleReplacements = $this->fetchEligibleReplacementEmployees(
                $employeeId,
                $leaveTypeId,
                $startDate,
                $endDate
            );
            if ($rule['replacement_required'] && !$eligibleReplacements) {
                throw new \InvalidArgumentException('No eligible replacement employee is available for the selected dates.');
            }
            if ($rule['replacement_required'] && $replacementId === null) {
                throw new \InvalidArgumentException('Select an eligible replacement employee.');
            }
            if ($replacementId !== null) {
                $eligibleIds = array_column($eligibleReplacements, 'id');
                if (!in_array($replacementId, $eligibleIds, true)) {
                    throw new \InvalidArgumentException('Select an eligible replacement employee for the selected dates.');
                }
            }

            $this->validateDepartmentStaffing(
                $requester['department_id'],
                $employeeId,
                $startDate,
                $endDate
            );

            $stmt = $this->pdo->prepare("INSERT INTO leaves
                    (start_date, end_date, leave_type_id, status, employee_id, created_by_id, replacement_id, created_at)
                VALUES
                    (:start_date, :end_date, :leave_type_id, 'Pending', :employee_id, :created_by_id, :replacement_id, NOW())");

            $created = $stmt->execute([
                'start_date' => $startDate,
                'end_date' => $endDate,
                'leave_type_id' => $leaveTypeId,
                'employee_id' => $employeeId,
                'created_by_id' => $employeeId,
                'replacement_id' => $replacementId,
            ]);
            $this->pdo->commit();
            return $created;
        } catch (\Throwable $exception) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            throw $exception;
        }
    }

    private function fetchEmployeeContext(int $employeeId): ?array
    {
        $stmt = $this->pdo->prepare('SELECT id, department_id, position_id FROM employees WHERE id = :employee_id');
        $stmt->execute(['employee_id' => $employeeId]);
        $employee = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$employee) {
            return null;
        }

        return [
            'id' => (int) $employee['id'],
            'department_id' => (int) $employee['department_id'],
            'position_id' => (int) $employee['position_id'],
        ];
    }

    private function countWorkingDays(string $startDate, string $endDate): int
    {
        $start = new \DateTimeImmutable($startDate);
        $end = new \DateTimeImmutable($endDate);
        $workingDays = 0;

        for ($day = $start; $day <= $end; $day = $day->modify('+1 day')) {
            if ((int) $day->format('N') < 6) {
                $workingDays++;
            }
        }

        return $workingDays;
    }

    private function fetchLeaveRule(int $positionId, int $leaveTypeId): ?array
    {
        $stmt = $this->pdo->prepare('SELECT max_days_per_year, replacement_required
            FROM leave_rules
            WHERE position_id = :position_id AND leave_type_id = :leave_type_id');
        $stmt->execute([
            'position_id' => $positionId,
            'leave_type_id' => $leaveTypeId,
        ]);
        $rule = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$rule) {
            return null;
        }

        return [
            'max_days_per_year' => (int) $rule['max_days_per_year'],
            'replacement_required' => (bool) $rule['replacement_required'],
        ];
    }

    private function fetchDepartmentEmployees(int $departmentId, int $excludedEmployeeId): array
    {
        $stmt = $this->pdo->prepare('SELECT id, first_name, last_name, position_id
            FROM employees
            WHERE department_id = :department_id AND id <> :employee_id
            ORDER BY last_name, first_name, id');
        $stmt->execute([
            'department_id' => $departmentId,
            'employee_id' => $excludedEmployeeId,
        ]);

        return array_map(static function (array $employee): array {
            return [
                'id' => (int) $employee['id'],
                'first_name' => $employee['first_name'],
                'last_name' => $employee['last_name'],
                'position_id' => (int) $employee['position_id'],
            ];
        }, $stmt->fetchAll(PDO::FETCH_ASSOC));
    }

    private function fetchUnavailableEmployeeIds(array $employees, string $startDate, string $endDate): array
    {
        if (!$employees) {
            return [];
        }

        $placeholders = [];
        $parameters = [
            'start_date' => $startDate,
            'end_date' => $endDate,
        ];
        foreach ($employees as $index => $employee) {
            $placeholder = 'employee_' . $index;
            $placeholders[] = ':' . $placeholder;
            $parameters[$placeholder] = $employee['id'];
        }

        $stmt = $this->pdo->prepare("SELECT DISTINCT employee_id
            FROM leaves
            WHERE employee_id IN (" . implode(', ', $placeholders) . ")
                AND status IN ('approved', 'pending')
                AND start_date <= :end_date
                AND end_date >= :start_date");
        $stmt->execute($parameters);

        $unavailable = [];
        foreach ($stmt->fetchAll(PDO::FETCH_COLUMN) as $employeeId) {
            $unavailable[(int) $employeeId] = true;
        }

        return $unavailable;
    }

    private function validateDepartmentStaffing(
        int $departmentId,
        int $requesterId,
        string $startDate,
        string $endDate
    ): void {
        $employeeCount = $this->pdo->prepare('SELECT COUNT(*) FROM employees WHERE department_id = :department_id');
        $employeeCount->execute(['department_id' => $departmentId]);
        $departmentSize = (int) $employeeCount->fetchColumn();
        $maxOnLeave = $departmentSize - (int) ceil($departmentSize / 3);

        $stmt = $this->pdo->prepare("SELECT l.employee_id, l.start_date, l.end_date
            FROM leaves l
            JOIN employees e ON e.id = l.employee_id
            WHERE e.department_id = :department_id
                AND l.status IN ('approved', 'pending')
                AND l.start_date <= :end_date
                AND l.end_date >= :start_date");
        $stmt->execute([
            'department_id' => $departmentId,
            'start_date' => $startDate,
            'end_date' => $endDate,
        ]);
        $existingLeaves = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $intervalsByEmployee = [$requesterId => [[$startDate, $endDate]]];
        foreach ($existingLeaves as $leave) {
            $employeeId = (int) $leave['employee_id'];
            $intervalsByEmployee[$employeeId][] = [
                max($startDate, $leave['start_date']),
                min($endDate, $leave['end_date']),
            ];
        }

        $events = [];
        foreach ($intervalsByEmployee as $intervals) {
            usort($intervals, static fn(array $left, array $right): int => $left[0] <=> $right[0]);
            $mergedIntervals = [];

            foreach ($intervals as $interval) {
                $lastIndex = count($mergedIntervals) - 1;
                if ($lastIndex >= 0 && $interval[0] <= $mergedIntervals[$lastIndex][1]) {
                    $mergedIntervals[$lastIndex][1] = max($mergedIntervals[$lastIndex][1], $interval[1]);
                } else {
                    $mergedIntervals[] = $interval;
                }
            }

            foreach ($mergedIntervals as [$intervalStart, $intervalEnd]) {
                $events[$intervalStart] = ($events[$intervalStart] ?? 0) + 1;
                $dayAfterEnd = (new \DateTimeImmutable($intervalEnd))->modify('+1 day')->format('Y-m-d');
                $events[$dayAfterEnd] = ($events[$dayAfterEnd] ?? 0) - 1;
            }
        }

        ksort($events);
        $dates = array_keys($events);
        $unavailableCount = 0;
        for ($index = 0; $index < count($dates) - 1; $index++) {
            $date = $dates[$index];
            $unavailableCount += $events[$date];
            if ($date <= $endDate && $dates[$index + 1] > $startDate && $unavailableCount > $maxOnLeave) {
                $offendingDate = max($date, $startDate);
                throw new \InvalidArgumentException(
                    'This request would leave fewer than one-third of your department available on ' . $offendingDate . '.'
                );
            }
        }
    }
}