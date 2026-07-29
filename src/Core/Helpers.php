<?php

use LeaveManagement\Core\Csrf;

function csrf_field(): string
{
    return sprintf(
        '<input type="hidden" name="csrf_token" value="%s">',
        htmlspecialchars(Csrf::token(), ENT_QUOTES, 'UTF-8')
    );
}