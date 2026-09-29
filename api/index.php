<?php

// Ensure directories exist in writable /tmp directory for Vercel Serverless environment
$dirs = [
    '/tmp/storage/framework/views',
    '/tmp/storage/framework/cache/data',
    '/tmp/storage/framework/sessions',
    '/tmp/storage/logs',
    '/tmp/storage/app/public',
    '/tmp/bootstrap/cache',
];

foreach ($dirs as $dir) {
    if (! is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }
}

// Forward Vercel requests to public/index.php
require __DIR__.'/../public/index.php';
