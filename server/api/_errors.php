<?php

function not_found($message) {
  return new Error($message, 404);
}

function internal_server_error($message) {
  return new Error($message, 500);
}

function service_unavailable($message) {
  return new Error($message, 503);
}
