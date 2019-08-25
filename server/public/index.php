<?php

require_once __DIR__ . '/../api/_lifecycle.php';

if (getenv('PHP_ENV') === 'development') {
  $file_path = $request['path'] === '/'
    ? __DIR__ . '/index.html'
    : __DIR__ . "/${request['path']}";
  if (file_exists($file_path)) {
    $file = fopen($file_path, 'r');
    fpassthru($file);
    fclose($file);
    return true;
  }
}

if (!preg_match('/^\/api\//', $request['path'])) {
  throw new Error("Cannot ${request['method']} ${request['path']}", 404);
}

$resource = preg_replace('/^\/api\/_?/', '', $request['path']);
$handler_path = __DIR__ . "/../api/${resource}.php";

if (!file_exists($handler_path)) {
  throw new Error("Cannot ${request['method']} ${request['path']}", 404);
}

require_once $handler_path;

throw new Error("Cannot ${request['method']} ${request['path']}", 404);
