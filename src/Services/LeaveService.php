<?php

namespace LeaveManagement\Services;

use LeaveManagement\Models\RemainingLeaveInfo;
use LeaveManagement\Repositories\LeaveRepository;

class LeaveService
{
    public function __construct(private LeaveRepository $leaveRepository)    
    {
    }

    public function getCalendarLeaves(int $viewerId): array
    {
        $leaves = $this->leaveRepository->fetchCalendarLeaves($viewerId);
        $events = [];

        foreach ($leaves as $leave) {
            $isManager = $leave->viewer_manager_id === null;
            $sameDepartment = (int) $leave->viewer_department_id === (int) $leave->department_id;
            $createdByAnotherDepartmentManager = $leave->creator_manager_id === null
                && (int) $leave->creator_department_id !== (int) $leave->department_id;
            $events[] = [
                'id' => (string) $leave->id,
                'title' => $leave->employee_first_name . ' ' . $leave->employee_last_name,
                'start' => $leave->start_date,    // '2026-07-10'
                'end'   => (new \DateTimeImmutable($leave->end_date))->modify('+1 day')->format('Y-m-d'),
                'allDay' => true,
                'employee' => $leave->employee_first_name . ' ' . $leave->employee_last_name,
                'employee_position' => $leave->employee_position,
                'department' => $leave->department_name,
                'leave_type' => $leave->leave_type,
                'status' => $leave->status,
                'start_date' => $leave->start_date,
                'end_date' => $leave->end_date,
                'created_at' => $leave->created_at,
                'created_by' => $leave->creator_first_name . ' ' . $leave->creator_last_name,
                'replacement' => $leave->replacement_first_name !== null
                    ? $leave->replacement_first_name . ' ' . $leave->replacement_last_name
                    : null,
                'approved_by' => $leave->approver_first_name !== null
                    ? $leave->approver_first_name . ' ' . $leave->approver_last_name
                    : null,
                'can_decide' => $isManager && ($sameDepartment || $createdByAnotherDepartmentManager),
                'classNames' => match (strtolower($leave->status)) {
                    'pending' => ['pending-leave-request'],
                    'rejected' => ['rejected-leave-request'],
                    default => [],
                },
                'backgroundColor' => $this->getDepartmentColor($leave->department_id)
            ];
        }

        return $events;
    }

    public function decideLeaveRequest(int $managerId, int $leaveId, string $decision): string
    {
        if ($leaveId < 1) {
            throw new \InvalidArgumentException('Invalid leave request.');
        }

        if (!in_array($decision, ['approve', 'reject'], true)) {
            throw new \InvalidArgumentException('Choose approve or reject.');
        }

        return $this->leaveRepository->decideLeaveRequest($managerId, $leaveId, $decision);
    }

    public function getDepartmentColor(int $departmentId): string 
    {
        // Define colors for each department
        $colors = [
            1 => '#FF5733', // HR
            2 => '#33FF57', // IT
            3 => '#3357FF', // Finance
            // Add more departments as needed
        ];

        return $colors[$departmentId] ?? '#000000'; // Default to black if not found
    }

    public function getLeaveTypes(int $employeeId): array
    {
        return $this->leaveRepository->fetchLeaveTypes($employeeId);
    }

    public function getEligibleReplacementEmployees(
        int $employeeId,
        int $leaveTypeId,
        string $startDate,
        string $endDate
    ): array {
        $start = $this->parseDate($startDate);
        $end = $this->parseDate($endDate);
        if (!$start || !$end || $end < $start) {
            return [];
        }

        return $this->leaveRepository->fetchEligibleReplacementEmployees(
            $employeeId,
            $leaveTypeId,
            $startDate,
            $endDate
        );
    }

    public function getRemainingLeaveDays(int $employeeId) : array
    {
        $remainingDays = $this->leaveRepository->fetchRemainingLeaveDays($employeeId);
        return array_map([RemainingLeaveInfo::class, 'fromArray'], $remainingDays);
    }

    public function getEmployeeRequestsForCurrentYear(int $employeeId): array
    {
        $requests = $this->leaveRepository->fetchEmployeeRequestsForYear($employeeId, (int) date('Y'));

        foreach ($requests as $request) {
            $request->working_days = $this->countWorkingDays($request->start_date, $request->end_date);
        }

        return $requests;
    }

    private function countWorkingDays(string $startDate, string $endDate): int
    {
        $start = new \DateTimeImmutable($startDate);
        $end = new \DateTimeImmutable($endDate);
        if ($end < $start) {
            return 0;
        }

        $workingDays = 0;
        for ($day = $start; $day <= $end; $day = $day->modify('+1 day')) {
            if ((int) $day->format('N') < 6) {
                $workingDays++;
            }
        }

        return $workingDays;
    }

    public function requestLeave(
        int $employeeId,
        int $leaveTypeId,
        string $startDate,
        string $endDate,
        ?int $replacementId = null
    ): bool
    {
        $start = $this->parseDate($startDate);
        $end = $this->parseDate($endDate);

        if (!$start || !$end) {
            throw new \InvalidArgumentException('Enter valid start and end dates.');
        }

        if ($end < $start) {
            throw new \InvalidArgumentException('End date cannot be before the start date.');
        }

        if ((int) $start->format('N') > 5 || (int) $end->format('N') > 5) {
            throw new \InvalidArgumentException('Start and end dates must be weekdays.');
        }

        if ($leaveTypeId < 1) {
            throw new \InvalidArgumentException('Select a leave type.');
        }

        $earliestStartDate = $this->getEarliestAllowedStartDate();
        if ($start < $earliestStartDate) {
            throw new \InvalidArgumentException(
                'The start date must be at least three working days from today. The earliest allowed date is '
                . $earliestStartDate->format('Y-m-d') . '.'
            );
        }

        return $this->leaveRepository->createLeaveRequest(
            $employeeId,
            $leaveTypeId,
            $startDate,
            $endDate,
            $replacementId,
            (int) (new \DateTimeImmutable('now'))->format('Y')
        );
    }

    private function parseDate(string $date): ?\DateTimeImmutable
    {
        $parsed = \DateTimeImmutable::createFromFormat('!Y-m-d', $date);
        $errors = \DateTimeImmutable::getLastErrors();

        if (!$parsed || ($errors && ($errors['warning_count'] > 0 || $errors['error_count'] > 0))
            || $parsed->format('Y-m-d') !== $date
        ) {
            return null;
        }

        return $parsed;
    }

    private function getEarliestAllowedStartDate(): \DateTimeImmutable
    {
        $date = new \DateTimeImmutable('today');
        $workingDays = 0;

        while ($workingDays < 3) {
            $date = $date->modify('+1 day');
            if ((int) $date->format('N') < 6) {
                $workingDays++;
            }
        }

        return $date;
    }
}