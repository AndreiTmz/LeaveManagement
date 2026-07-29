<h1>Employees</h1>

<ul>
<?php foreach ($employees ?? [] as $employee): ?>
    <li>
        <?= htmlspecialchars($employee->first_name) ?>
        <?= htmlspecialchars($employee->last_name) ?>
    </li>
<?php endforeach; ?>
</ul>