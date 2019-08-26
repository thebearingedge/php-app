<?php

require_once __DIR__ . '/../api/_lifecycle.php';
require_once __DIR__ . '/../api/_errors.php';

if ($request['path'] === '/') {
  $index = fopen(__DIR__ . '/index.html', 'r');
  fpassthru($index);
  fclose($index);
  return true;
}

if (!preg_match('/^\/api\//', $request['path'])) {
  throw not_found("Cannot ${request['method']} ${request['path']}");
}

$resource = preg_replace('/^\/api\/_?/', '', $request['path']);
$handler_path = __DIR__ . "/../api/${resource}.php";

if (!is_file($handler_path)) {
  throw not_found("Cannot ${request['method']} ${request['path']}");
}

require_once $handler_path;

throw not_found("Cannot ${request['method']} ${request['path']}");
