<?php

if ($request['method'] === 'POST') {
  $task = $request['body']['task'] ?? '';
  $isCompleted = $request['body']['isCompleted'] ?? false;
  if (empty($task)) {
    throw bad_request('`task` is a required field.');
  }
  $todo = [
    'task' => $task,
    'isCompleted' => $isCompleted
  ];
  $link = get_db_link();
  $created = create_todo($todo, $link);
  $responst['status'] = 201;
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
      throw bad_request('`id` must be a positive integer.');
    }
    $link = get_db_link();
    $todo = read_by_id($id, $link);
    if (!$todo) {
      throw not_found("Cannot find todo with `id` $id.");
    }
    $response['body'] = $todo;
    send($response);
  }
}

if ($request['method'] === 'PUT') {
  $id = $request['query']['id'] ?? null;
  if (!intval($id)) {
    throw bad_request('An integer `id` must be specified.');
  }
  $body = $request['body'];
  $required_fields = ['task', 'isCompleted'];
  foreach ($required_fields as $field) {
    if (!array_key_exists($field, $body)) {
      throw bad_request("`$field` is a required field.");
    }
  }
  $link = get_db_link();
  $updated = update_by_id($id, $body, $link);
  if (!$updated) {
    throw not_found("Cannot find todo with `id` $id.");
  }
  $response['body'] = $updated;
  send($response);
}

if ($request['method'] === 'DELETE') {
  $id = $request['query']['id'] ?? null;
  if (!intval($id)) {
    throw bad_request('An integer `id` must be specifiied.');
  }
  $link = get_db_link();
  $deleted = delete_by_id($id, $link);
  if (!$deleted) {
    throw not_found("Cannot find todo with `id` $id.");
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
  $stmt->bind_param('si', $todo['task'], intval($todo['isCompleted']));
  $stmt->execute();
  $id = $stmt->insert_id;
  $created = read_by_id($id, $link);
  return $created;
}

function read_all_todos($link) {
  $query = 'SELECT * FROM `todos`';
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
    SELECT `id`,
           `task`,
           `isCompleted`
      FROM `todos` WHERE `id` = $id
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
  $query = '
    UPDATE `todos`
       SET `task` = ?,
           `isCompleted` = ?
     WHERE `id` = $id
  ';
  $stmt = $link->prepare($query);
  $stmt->bind_param('si', $updates['task'], $updates['isCompleted']);
  $stmt->execute();
  $updated = read_by_id($id, $link);
  return $updated;
}

function delete_by_id($id, $link) {
  $query = "
    DELETE FROM `todos`
          WHERE `id` = $id
  ";
  $link->query($query);
  return $link->affected_rows > 0;
}
