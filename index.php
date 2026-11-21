<?php
/**
 * Root-level index.php — fallback for hosts that cannot point document root at public/.
 *
 * Place this file and the .htaccess at the project root.
 * The .htaccess routes all requests here; this file forwards to public/index.php.
 *
 * Access to any file outside public/ is denied by the .htaccess rules.
 */

// Forward to public/index.php without exposing the path.
$_SERVER['SCRIPT_FILENAME'] = __DIR__ . '/public/index.php';
$_SERVER['SCRIPT_NAME']     = '/index.php';

require __DIR__ . '/public/index.php';
