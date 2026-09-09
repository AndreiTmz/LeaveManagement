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
            'title' => 'Request Leave',
            'styles' => ['/css/forms.css'],
            'leaveTypes' => $this->leaveService->getLeaveTypes()
        ]);
    }

    public function requestLeave(): void 
    {
        $this->requireAuth();

        // Validate and process the leave request
        $startDate = $_POST['start_date'] ?? '';
        $endDate = $_POST['end_date'] ?? '';

        // Basic validation
        if (empty($startDate) || empty($endDate)) {
            $this->view('leaves/request', [
                'title' => 'Request Leave',
                'styles' => ['/css/forms.css'],
                'errorMessage' => 'All fields are required.',
                'startDate' => $startDate,
                'endDate' => $endDate
            ]);
            return;
        }

        // Attempt to create the leave request
        if ($this->leaveService->requestLeave($_SESSION['user_id'], $startDate, $endDate)) {
            header('Location: /calendar');
            exit;
        } else {
            $this->view('leaves/request', [
                'title' => 'Request Leave',
                'styles' => ['/css/forms.css'],
                'errorMessage' => 'Failed to submit leave request. Please try again.',
                'startDate' => $startDate,
                'endDate' => $endDate
            ]);
        }
    }
}