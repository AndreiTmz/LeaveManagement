<?php

namespace LeaveManagement\Controllers;

use LeaveManagement\Core\BaseController;

class HomeController extends BaseController
{
    public function index(): void
    {
        header("Location: /login");
        exit;
    }
}