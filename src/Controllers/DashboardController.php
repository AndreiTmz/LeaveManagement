<?php

namespace LeaveManagement\Controllers;

use LeaveManagement\Core\BaseController;
use LeaveManagement\Services\LeaveService;

class DashboardController extends BaseController
{
    public function __construct(private LeaveService $leaveService) {}

    public function index(): void 
    {
        $this->requireAuth();
        $this->view('dashboard/dashboard', [
            'title' => 'Dashboard',
            'styles' => ['/css/dashboard.css'],
            'remainingLeave' => $this->leaveService->getRemainingLeaveDays((int) $_SESSION['user_id']),
            'leaveRequests' => $this->leaveService->getEmployeeRequestsForCurrentYear((int) $_SESSION['user_id'])
        ]);
    }

    public function getLeaveEvents(): void 
    {
        $this->requireAuth();

        $events = $this->leaveService->getCalendarLeaves();
        
        // Output as JSON for FullCalendar
        header('Content-Type: application/json');
        echo json_encode($events);
        exit;
    }
}