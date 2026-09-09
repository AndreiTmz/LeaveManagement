<?php

namespace LeaveManagement\Services;

use LeaveManagement\Repositories\LeaveRepository;

class LeaveService
{
    public function __construct(private LeaveRepository $leaveRepository)    
    {
    }

    public function getAllApprovedLeaves(): array 
    {
        $leaves = $this->leaveRepository->fetchAllApproved();
        $events = [];

        foreach ($leaves as $leave) {
            $events[] = [
                'title' => $leave->first_name . ' ' . $leave->last_name, // 'John Doe'
                'start' => $leave->start_date,    // '2026-07-10'
                'end'   => $leave->end_date,      // '2026-07-15'
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

    public function requestLeave(int $employeeId, string $startDate, string $endDate): bool
    {
        echo "Requesting leave for employee ID: $employeeId from $startDate to $endDate";
        return true;
        //todo: check this generated code and implement missing logic:
        // // Validate dates
        // if (strtotime($startDate) > strtotime($endDate)) {
        //     throw new \InvalidArgumentException("Start date cannot be after end date.");
        // }

        // // Check for overlapping leaves
        // $overlappingLeaves = $this->leaveRepository->findOverlappingLeaves($employeeId, $startDate, $endDate);
        // if (!empty($overlappingLeaves)) {
        //     throw new \Exception("You already have a leave request that overlaps with these dates.");
        // }

        // // Create the leave request
        // return $this->leaveRepository->createLeaveRequest($employeeId, $startDate, $endDate);
    }
}