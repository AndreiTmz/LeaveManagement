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
        $leaveTypeId = filter_var($_POST['leave_type'] ?? null, FILTER_VALIDATE_INT);

        // Attempt to create the leave request
        try {
            $created = $this->leaveService->requestLeave(
                (int) $_SESSION['user_id'],
                $leaveTypeId === false ? 0 : (int) $leaveTypeId,
                $startDate,
                $endDate
            );
            $errorMessage = '';
        } catch (\InvalidArgumentException $exception) {
            $created = false;
            $errorMessage = $exception->getMessage();
        }

        if ($created) {
            header('Location: /calendar');
            exit;
        } else {
            $this->view('leaves/request', [
                'title' => 'Request Leave',
                'styles' => ['/css/forms.css'],
                'leaveTypes' => $this->leaveService->getLeaveTypes(),
                'errorMessage' => $errorMessage ?: 'Failed to submit leave request. Please try again.',
                'startDate' => $startDate,
                'endDate' => $endDate,
                'selectedLeaveType' => $leaveTypeId === false ? '' : $leaveTypeId
            ]);
        }
    }
}