<?php

// Prepare writable storage directory in /tmp for Vercel Serverless
$storage = '/tmp/storage';
foreach (['/framework/views', '/framework/cache/data', '/framework/sessions', '/logs'] as $dir) {
    if (!is_dir($storage . $dir)) {
        @mkdir($storage . $dir, 0755, true);
    }
}

require __DIR__ . '/../public/index.php';
