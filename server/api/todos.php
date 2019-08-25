<?php

if ($request['method'] === 'GET') {
  $conn = get_database_connection();
  $query = 'SELECT * FROM `todos`';
  $result = mysqli_query($conn, $query);
  if (!$result) {
    throw new Error('Failed to retrieve `todos` data.', 503);
  }
  $todos = [];
  while ($todo = mysqli_fetch_assoc($result)) {
    $todo['isCompleted'] = boolval($todo['isCompleted']);
    $todos[] = $todo;
  }
  $response['body'] = $todos;
  send($response);
}
