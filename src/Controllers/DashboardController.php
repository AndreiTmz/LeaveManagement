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
            'remainingLeave' => "12 - need to get this from db",
            'pendingRequests' => "3 - need to get this from db"
        ]);
    }

    public function getLeaveEvents(): void 
    {
        $this->requireAuth();

        // Fetch all approved leaves from LeaveService
        $events = $this->leaveService->getAllApprovedLeaves();
        
        // Output as JSON for FullCalendar
        header('Content-Type: application/json');
        echo json_encode($events);
        exit;
    }
}