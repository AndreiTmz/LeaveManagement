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
}