<?php

/**
 * Router for `php artisan serve` (cwd is `public/`).
 *
 * The default Laravel router treats `public/storage` as an on-disk file; the filesystem
 * entry is a symlink to `storage/app/public`, which lives outside `public/`. PHP's built-in
 * server then refuses to follow it and returns 403. Force those requests through the app.
 */
$publicPath = getcwd();

$uri = urldecode(
    parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? ''
);

if ($uri !== '/' && str_starts_with($uri, '/storage')) {
    require_once $publicPath.'/index.php';

    return;
}

if ($uri !== '/' && file_exists($publicPath.$uri)) {
    return false;
}

require_once $publicPath.'/index.php';
