<?php

namespace LeaveManagement\Models;

class LeaveRequest
{
    public function __construct(
        public int $id,
        public \DateTime $start_date,
        public \DateTime $end_date,
        public int $leave_type_id,
        public string $status,
        public string $password_hash,
        public int $employee_id,
        public int $created_by_id,
        public ?int $manager_id
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            $data['id'],
            new \DateTime($data['start_date']),
            new \DateTime($data['end_date']),
            $data['email'],
            $data['username'],
            $data['password_hash'],
            $data['position_id'],
            $data['department_id'],
            $data['manager_id']
        );
    }
}