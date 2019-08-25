<?php

require_once __DIR__ . '/../startup.php';

$target = $request['path'];

if (getenv('PHP_ENV') === 'development') {
  $file_path = $target === '/'
    ? __DIR__ . '/index.html'
    : __DIR__ . "/$target";
  if (file_exists($file_path)) {
    $file = fopen($file_path, 'r');
    fpassthru($file);
    fclose($file);
    return true;
  }
}

$resource = preg_replace('/^\/api/', '', $target);
$handler_path = __DIR__ . "/../api/${resource}.php";

if (!file_exists($handler_path)) {
  throw new Error("Cannot ${request['method']} ${target}", 404);
}

require_once $handler_path;

throw new Error("Cannot ${request['method']} ${target}", 404);
