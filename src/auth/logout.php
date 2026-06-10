<?php
declare(strict_types=1);
require_once __DIR__ . '/../config.php';
Auth::startSession();
Auth::logout();
redirect('/auth/login.php');
