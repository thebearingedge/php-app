<?php

if (empty($request['body'])) {
  unset($request['body']);
}

if (empty($request['query'])) {
  unset($request['query']);
}

$response['body'] = [
  'received' => $request
];

send($response);
