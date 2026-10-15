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
        $startDate = is_string($_GET['start_date'] ?? null) ? $_GET['start_date'] : '';
        $endDate = is_string($_GET['end_date'] ?? null) ? $_GET['end_date'] : '';
        $leaveType = is_string($_GET['leave_type'] ?? null) ? $_GET['leave_type'] : '';
        $replacement = is_string($_GET['replacement_id'] ?? null) ? $_GET['replacement_id'] : '';

        $this->renderRequestForm(
            (int) $_SESSION['user_id'],
            '',
            $startDate,
            $endDate,
            $leaveType,
            $replacement
        );
    }

    public function requestLeave(): void 
    {
        $this->requireAuth();

        // Validate and process the leave request
        $startDate = is_string($_POST['start_date'] ?? null) ? $_POST['start_date'] : '';
        $endDate = is_string($_POST['end_date'] ?? null) ? $_POST['end_date'] : '';
        $leaveTypeValue = is_string($_POST['leave_type'] ?? null) ? $_POST['leave_type'] : null;
        $leaveTypeId = filter_var($leaveTypeValue, FILTER_VALIDATE_INT);
        $selectedReplacement = is_string($_POST['replacement_id'] ?? null) ? $_POST['replacement_id'] : '';
        $replacementId = null;
        if (array_key_exists('replacement_id', $_POST) && $selectedReplacement === '' && $_POST['replacement_id'] !== '') {
            $replacementId = 0;
        } elseif ($selectedReplacement !== '') {
            $validatedReplacementId = filter_var($selectedReplacement, FILTER_VALIDATE_INT);
            $replacementId = $validatedReplacementId === false ? 0 : (int) $validatedReplacementId;
        }

        try {
            $created = $this->leaveService->requestLeave(
                (int) $_SESSION['user_id'],
                $leaveTypeId === false ? 0 : (int) $leaveTypeId,
                $startDate,
                $endDate,
                $replacementId
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
            $this->renderRequestForm(
                (int) $_SESSION['user_id'],
                $errorMessage ?: 'Failed to submit leave request. Please try again.',
                $startDate,
                $endDate,
                $leaveTypeId === false ? '' : $leaveTypeId,
                $selectedReplacement
            );
        }
    }

    public function getReplacementOptions(): void
    {
        $this->requireAuth();

        $leaveTypeValue = is_string($_GET['leave_type'] ?? null) ? $_GET['leave_type'] : null;
        $leaveTypeId = filter_var($leaveTypeValue, FILTER_VALIDATE_INT);
        if ($leaveTypeId === false || $leaveTypeId < 1) {
            http_response_code(422);
            $employees = [];
        } else {
            $employees = $this->leaveService->getEligibleReplacementEmployees(
                (int) $_SESSION['user_id'],
                (int) $leaveTypeId,
                is_string($_GET['start_date'] ?? null) ? $_GET['start_date'] : '',
                is_string($_GET['end_date'] ?? null) ? $_GET['end_date'] : ''
            );
        }

        header('Content-Type: application/json');
        echo json_encode(['employees' => $employees], JSON_THROW_ON_ERROR);
        exit;
    }

    private function renderRequestForm(
        int $employeeId,
        string $errorMessage,
        string $startDate,
        string $endDate,
        string|int $selectedLeaveType,
        string $selectedReplacement
    ): void {
        $leaveTypes = $this->leaveService->getLeaveTypes($employeeId);
        $validatedLeaveType = filter_var($selectedLeaveType, FILTER_VALIDATE_INT);
        $replacements = [];

        if ($validatedLeaveType !== false && $validatedLeaveType > 0) {
            $replacements = $this->leaveService->getEligibleReplacementEmployees(
                $employeeId,
                (int) $validatedLeaveType,
                $startDate,
                $endDate
            );
        }

        $this->view('leaves/request', [
            'title' => 'Request Leave',
            'styles' => ['/css/forms.css'],
            'leaveTypes' => $leaveTypes,
            'errorMessage' => $errorMessage,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'selectedLeaveType' => $validatedLeaveType === false ? '' : $validatedLeaveType,
            'selectedReplacement' => $selectedReplacement,
            'replacementEmployees' => $replacements,
        ]);
    }
}