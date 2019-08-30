<?php

require_once '_database.php';

if ($request['method'] === 'GET') {
  $conn = get_database_connection();
  $query = 'SELECT * FROM `todos`';
  $result = mysqli_query($conn, $query);
  if (!$result) {
    $previous = new Error(mysqli_error($conn));
    throw internal_server_error('Failed to retrieve `todos` data.', $previous);
  }
  $todos = [];
  while ($todo = mysqli_fetch_assoc($result)) {
    $todo['isCompleted'] = boolval($todo['isCompleted']);
    $todos[] = $todo;
  }
  $response['body'] = $todos;
  send($response);
}
