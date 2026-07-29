<?php

namespace LeaveManagement\Models;

class Employee
{
    public function __construct(
        public int $id,
        public string $first_name,
        public string $last_name,
        public string $email,
        public string $username,
        public string $password_hash,
        public int $position_id,
        public int $department_id,
        public ?int $manager_id
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            $data['id'],
            $data['first_name'],
            $data['last_name'],
            $data['email'],
            $data['username'],
            $data['password_hash'],
            $data['position_id'],
            $data['department_id'],
            $data['manager_id']
        );
    }

    public function fullName(): string
    {
        return $this->first_name . ' ' . $this->last_name;
    }
}