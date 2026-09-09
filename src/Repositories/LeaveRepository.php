<?php

namespace LeaveManagement\Repositories;

use PDO;

class LeaveRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function fetchAllApproved(): array
    {
        $stmt = $this->pdo->query("SELECT l.*, e.first_name, e.last_name, e.department_id
            FROM leaves l 
            JOIN employees e ON l.employee_id = e.id 
            WHERE l.status = 'approved'");

        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public function fetchLeaveTypes(): array
    {
        $stmt = $this->pdo->query("SELECT * FROM leave_types");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}