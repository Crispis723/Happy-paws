<?php
// Vercel PHP entrypoint routing all requests to Laravel public/index.php
// Ensure Composer autoload is available

// Change working directory to project root
chdir(__DIR__ . '/..');

// Load Laravel front controller
require __DIR__ . '/../public/index.php';
