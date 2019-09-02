<?php

if ($request['method'] === 'POST') {
  $task = $request['body']['task'] ?? '';
  $isCompleted = $request['body']['isCompleted'] ?? false;
  if (empty($task)) {
    throw new ApiError('`task` is a required field.', 400);
  }
  $todo = [
    'task' => $task,
    'isCompleted' => $isCompleted
  ];
  $link = get_db_link();
  $created = create_todo($todo, $link);
  $response['status'] = 201;
  $response['body'] = $created;
  send($response);
}

if ($request['method'] === 'GET') {
  if (!array_key_exists('id', $request['query'])) {
    $link = get_db_link();
    $todos = read_all_todos($link);
    $response['body'] = $todos;
    send($response);
  } else {
    $id = intval($request['query']['id']);
    if (!$id) {
      throw new ApiError('`id` must be a positive integer.', 400);
    }
    $link = get_db_link();
    $todo = read_by_id($id, $link);
    if (!$todo) {
      throw new ApiError("Cannot find todo with `id` $id.", 404);
    }
    $response['body'] = $todo;
    send($response);
  }
}

if ($request['method'] === 'PUT') {
  $id = $request['query']['id'] ?? null;
  if (!intval($id)) {
    throw new ApiError('An integer `id` must be specified.', 400);
  }
  $body = $request['body'];
  $required_fields = ['task', 'isCompleted'];
  foreach ($required_fields as $field) {
    if (!array_key_exists($field, $body)) {
      throw new ApiError("`$field` is a required field.", 400);
    }
  }
  $link = get_db_link();
  $updated = update_by_id($id, $body, $link);
  if (!$updated) {
    throw new ApiError("Cannot find todo with `id` $id.", 400);
  }
  $response['body'] = $updated;
  send($response);
}

if ($request['method'] === 'DELETE') {
  $id = $request['query']['id'] ?? null;
  if (!intval($id)) {
    throw new ApiError('An integer `id` must be specified.', 400);
  }
  $link = get_db_link();
  $deleted = delete_by_id($id, $link);
  if (!$deleted) {
    throw new ApiError("Cannot find todo with `id` $id.", 404);
  }
  $response['status'] = 204;
  send($response);
}

function create_todo($todo, $link) {
  $query = '
    INSERT INTO `todos` (`task`, `isCompleted`)
    VALUES (?, ?)
  ';
  $stmt = $link->prepare($query);
  $stmt->bind_param('si', $todo['task'], $todo['isCompleted']);
  $stmt->execute();
  $id = $stmt->insert_id;
  $stmt->close();
  $created = read_by_id($id, $link);
  return $created;
}

function read_all_todos($link) {
  $query = '
    SELECT * FROM `todos`
  ';
  $result = $link->query($query);
  $todos = [];
  while ($todo = $result->fetch_assoc()) {
    $todo['isCompleted'] = boolval($todo['isCompleted']);
    $todos[] = $todo;
  }
  return $todos;
}

function read_by_id($id, $link) {
  $query = "
    SELECT *
      FROM `todos` WHERE `id` = {intval($id)}
  ";
  $result = $link->query($query);
  $todo = $result->fetch_assoc();
  if ($todo) {
    $todo['isCompleted'] = boolval($todo['isCompleted']);
  }
  return $todo;
}

function update_by_id($id, $updates, $link) {
  $found = read_by_id($id, $link);
  if (!$found) {
    return null;
  }
  $query = "
    UPDATE `todos`
       SET `task` = ?,
           `isCompleted` = ?
     WHERE `id` = {intval($id)}
  ";
  $stmt = $link->prepare($query);
  $stmt->bind_param('si', $updates['task'], $updates['isCompleted']);
  $stmt->execute();
  $stmt->close();
  $updated = read_by_id($id, $link);
  return $updated;
}

function delete_by_id($id, $link) {
  $query = "
    DELETE FROM `todos`
          WHERE `id` = {intval($id)}
  ";
  $link->query($query);
  return $link->affected_rows > 0;
}
