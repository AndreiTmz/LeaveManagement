<?php

namespace LeaveManagement\Models;

class RemainingLeaveInfo
{
    public function __construct(
        public string $leaveType ,
        public int $remaining_days
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            $data['leave_type'],
            $data['remaining_days']
        );
    }
}