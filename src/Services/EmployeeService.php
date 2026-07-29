<?php

namespace LeaveManagement\Services;

use LeaveManagement\Repositories\EmployeeRepository;

class EmployeeService
{
    public function __construct(private EmployeeRepository $employeeRepository)    
    {
    }

    public function getAllEmployees(): array
    {
        return $this->employeeRepository->getAll();
    }
}