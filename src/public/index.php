<?php
// public/index.php — static entry point served by Nginx
// The real app lives one directory up at /app/index.php
// (Docker maps src/ → /app)
$appRoot = dirname(__DIR__); // /app when inside container
require_once $appRoot . '/index.php';
