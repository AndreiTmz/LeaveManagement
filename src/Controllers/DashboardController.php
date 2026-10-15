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

        $events = $this->leaveService->getCalendarLeaves((int) $_SESSION['user_id']);
        
        // Output as JSON for FullCalendar
        header('Content-Type: application/json');
        echo json_encode($events, JSON_THROW_ON_ERROR);
        exit;
    }

    public function decideLeaveRequest(): void
    {
        $this->requireAuth();

        $leaveId = filter_var($_POST['leave_id'] ?? null, FILTER_VALIDATE_INT);
        $decision = is_string($_POST['decision'] ?? null) ? $_POST['decision'] : '';
        if ($leaveId === false || $leaveId === null) {
            http_response_code(422);
            $response = ['error' => 'Invalid leave request.'];
        } else {
            try {
                $status = $this->leaveService->decideLeaveRequest(
                    (int) $_SESSION['user_id'],
                    (int) $leaveId,
                    $decision
                );
                $response = ['status' => $status];
            } catch (\DomainException $exception) {
                http_response_code(403);
                $response = ['error' => $exception->getMessage()];
            } catch (\InvalidArgumentException $exception) {
                http_response_code(422);
                $response = ['error' => $exception->getMessage()];
            }
        }

        header('Content-Type: application/json');
        echo json_encode($response, JSON_THROW_ON_ERROR);
        exit;
    }
}