<?php

namespace LeaveManagement\Core;

class Container
{
    private array $bindings = [];
    private array $instances = [];

    public function set(string $id, callable $factory): void
    {
        $this->bindings[$id] = $factory;
    }

    public function get(string $id): mixed
    {
        if (!isset($this->bindings[$id])) {
            throw new \Exception("No binding found for {$id}");
        }

        if (!isset($this->instances[$id])) {
            $this->instances[$id] = $this->bindings[$id]($this);
        }

        return $this->instances[$id];
    }
}