<?php

namespace LeaveManagement\Services;

use LeaveManagement\Models\RemainingLeaveInfo;
use LeaveManagement\Repositories\LeaveRepository;

class LeaveService
{
    public function __construct(private LeaveRepository $leaveRepository)    
    {
    }

    public function getCalendarLeaves(): array
    {
        $leaves = $this->leaveRepository->fetchCalendarLeaves();
        $events = [];

        foreach ($leaves as $leave) {
            $events[] = [
                'title' => $leave->first_name . ' ' . $leave->last_name, // 'John Doe'
                'start' => $leave->start_date,    // '2026-07-10'
                'end'   => (new \DateTimeImmutable($leave->end_date))->modify('+1 day')->format('Y-m-d'),
                'allDay' => true,
                'status' => $leave->status,
                'leave_type' => $leave->leave_type,
                'classNames' => $leave->status === 'pending' ? ['pending-leave-request'] : [],
                'backgroundColor' => $this->getDepartmentColor($leave->department_id)
            ];
        }

        return $events;
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

    public function getLeaveTypes(): array
    {
        return $this->leaveRepository->fetchLeaveTypes();
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

    public function requestLeave(int $employeeId, int $leaveTypeId, string $startDate, string $endDate): bool
    {
        $start = \DateTimeImmutable::createFromFormat('!Y-m-d', $startDate);
        $end = \DateTimeImmutable::createFromFormat('!Y-m-d', $endDate);

        if (!$start || $start->format('Y-m-d') !== $startDate || !$end || $end->format('Y-m-d') !== $endDate) {
            throw new \InvalidArgumentException('Enter valid start and end dates.');
        }

        if ($end < $start) {
            throw new \InvalidArgumentException('End date cannot be before the start date.');
        }

        if ($leaveTypeId < 1) {
            throw new \InvalidArgumentException('Select a leave type.');
        }

        return $this->leaveRepository->createLeaveRequest($employeeId, $leaveTypeId, $startDate, $endDate);
    }
}