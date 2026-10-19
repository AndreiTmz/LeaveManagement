<?php

namespace LeaveManagement\Controllers;

use LeaveManagement\Core\BaseController;
use LeaveManagement\Services\EmployeeService;

class EmployeeController extends BaseController
{
    public function __construct(private EmployeeService $employeeService) {}

    public function index(): void
    {
        $this->requireAuth();
        
        $employees = $this->employeeService->getAllEmployees();

        $this->view('employees/index', [
            'employees' => $employees
        ]);
    }

    public function showAddForm(): void
    {
        $this->requireManager();

        $this->renderAddForm();
    }

    public function add(): void
    {
        $this->requireManager();

        $firstName = is_string($_POST['first_name'] ?? null) ? $_POST['first_name'] : '';
        $lastName = is_string($_POST['last_name'] ?? null) ? $_POST['last_name'] : '';
        $email = is_string($_POST['email'] ?? null) ? $_POST['email'] : '';
        $username = is_string($_POST['username'] ?? null) ? $_POST['username'] : '';
        $positionId = filter_var($_POST['position_id'] ?? null, FILTER_VALIDATE_INT);
        $managerId = (int) $_SESSION['user_id'];
        $manager = $this->employeeService->getEmployeeById($managerId);

        if ($manager === null) {
            throw new \RuntimeException('The authenticated manager could not be found.');
        }

        try {
            if ($positionId === false || $positionId === null) {
                throw new \InvalidArgumentException('Select a position.');
            }

            $this->employeeService->addEmployee(
                $firstName,
                $lastName,
                $email,
                $username,
                $positionId,
                $manager->department_id,
                $managerId
            );
        } catch (\InvalidArgumentException|\DomainException $exception) {
            $this->renderAddForm([
                'errorMessage' => $exception->getMessage(),
                'firstName' => $firstName,
                'lastName' => $lastName,
                'email' => $email,
                'username' => $username,
                'selectedPositionId' => $positionId === false || $positionId === null ? '' : (string) $positionId,
            ]);
            return;
        }

        header('Location: /employees/add?success=1');
        exit;
    }

    private function renderAddForm(array $data = []): void
    {
        $this->view('employees/add', array_merge($data, [
            'title' => 'Add employee',
            'styles' => ['/css/forms.css'],
            'positions' => $this->employeeService->getAllPositions(),
            'success' => ($_GET['success'] ?? '') === '1',
            'hideAddEmployeeButton' => true,
        ]));
    }
}