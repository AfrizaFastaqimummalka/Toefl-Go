<?php

// Prepare writable storage directory in /tmp for Vercel Serverless
$storage = '/tmp/storage';
foreach (['/framework/views', '/framework/cache/data', '/framework/sessions', '/logs'] as $dir) {
    if (!is_dir($storage . $dir)) {
        @mkdir($storage . $dir, 0755, true);
    }
}

// Fix Vercel Serverless routing:
// Because this handler is in api/index.php, PHP sets SCRIPT_NAME to /api/index.php.
// Symfony's Request::prepareBaseUrl() sees /api/ in SCRIPT_NAME matching the request URI
// and strips '/api' as the base URL, causing requests to /api/tutor or /api/attempts
// to reach Laravel as /tutor or /attempts (resulting in 404 Route Not Found).
$_SERVER['SCRIPT_NAME'] = '/index.php';
$_SERVER['PHP_SELF'] = '/index.php';
$_SERVER['SCRIPT_FILENAME'] = __DIR__ . '/../public/index.php';

require __DIR__ . '/../public/index.php';
