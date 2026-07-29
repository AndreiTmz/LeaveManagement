<?php

namespace LeaveManagement\Controllers;

use LeaveManagement\Core\BaseController;
use LeaveManagement\Services\LeaveService;

class LeaveController extends BaseController
{
    public function __construct(private LeaveService $leaveService) {}

    public function showRequestLeave(): void 
    {
        $this->requireAuth();
        $this->view('leaves/request', [
            'title' => 'Request Leave'
        ]);
    }

    public function requestLeave(): void 
    {
        $this->requireAuth();

        // Validate and process the leave request
        $startDate = $_POST['start_date'] ?? '';
        $endDate = $_POST['end_date'] ?? '';
        $reason = $_POST['reason'] ?? '';

        // Basic validation
        if (empty($startDate) || empty($endDate) || empty($reason)) {
            $this->view('leaves/request-leave', [
                'title' => 'Request Leave',
                'errorMessage' => 'All fields are required.',
                'startDate' => $startDate,
                'endDate' => $endDate,
                'reason' => $reason,
            ]);
            return;
        }

        // Attempt to create the leave request
        if ($this->leaveService->requestLeave($_SESSION['user_id'], $startDate, $endDate)) {
            header('Location: /calendar');
            exit;
        } else {
            $this->view('leaves/request-leave', [
                'title' => 'Request Leave',
                'errorMessage' => 'Failed to submit leave request. Please try again.',
                'startDate' => $startDate,
                'endDate' => $endDate,
                'reason' => $reason,
            ]);
        }
    }
}