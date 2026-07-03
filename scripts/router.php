<?php

// Router for PHP's built-in web server, used by scripts/serve.ps1 and the
// composer "serve"/"dev" scripts so upload_max_filesize/post_max_size
// overrides (passed via `php -d`) actually reach the request-handling
// process. `php artisan serve` spawns its own child `php -S` process
// without forwarding `-d` flags, so it can't be used for large uploads.
$publicPath = getcwd();

$uri = urldecode(
    parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? ''
);

if ($uri !== '/' && file_exists($publicPath.$uri)) {
    return false;
}

require_once $publicPath.'/index.php';
