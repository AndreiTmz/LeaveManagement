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

    public function fetchLeaveTypes(): array
    {
        $stmt = $this->pdo->query("SELECT * FROM leave_types");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function fetchRemainingLeaveDays(int $employeeId): array
    {
        $stmt = $this->pdo->prepare("SELECT
                lt.name AS leave_type,
                GREATEST(
                    lr.max_days_per_year - COALESCE(SUM(
                        DATEDIFF(
                            LEAST(l.end_date, DATE_SUB(MAKEDATE(YEAR(CURDATE()) + 1, 1), INTERVAL 1 DAY)),
                            GREATEST(l.start_date, MAKEDATE(YEAR(CURDATE()), 1))
                        ) + 1
                    ), 0),
                    0
                ) AS remaining_days
            FROM employees e
            JOIN leave_rules lr ON lr.position_id = e.position_id
            JOIN leave_types lt ON lt.id = lr.leave_type_id
            LEFT JOIN leaves l ON l.employee_id = e.id
                AND l.leave_type_id = lr.leave_type_id
                AND l.status = 'approved'
                AND l.start_date < MAKEDATE(YEAR(CURDATE()) + 1, 1)
                AND l.end_date >= MAKEDATE(YEAR(CURDATE()), 1)
            WHERE e.id = :employee_id
            GROUP BY lt.id, lt.name, lr.max_days_per_year
            ORDER BY lt.id");
        $stmt->execute(['employee_id' => $employeeId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
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
            ORDER BY l.created_at DESC, l.id DESC");
        $stmt->execute([
            'employee_id' => $employeeId,
            'created_year_start' => $yearStart,
            'created_next_year_start' => $nextYearStart,
            'leave_next_year_start' => $nextYearStart,
            'leave_year_start' => $yearStart,
        ]);

        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public function createLeaveRequest(int $employeeId, int $leaveTypeId, string $startDate, string $endDate): bool
    {
        $stmt = $this->pdo->prepare("INSERT INTO leaves
                (start_date, end_date, leave_type_id, status, employee_id, created_by_id, created_at)
            VALUES
                (:start_date, :end_date, :leave_type_id, 'Pending', :employee_id, :created_by_id, NOW())");

        return $stmt->execute([
            'start_date' => $startDate,
            'end_date' => $endDate,
            'leave_type_id' => $leaveTypeId,
            'employee_id' => $employeeId,
            'created_by_id' => $employeeId,
        ]);
    }
}