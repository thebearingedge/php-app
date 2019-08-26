<?php

function not_found($message) {
  return new Error($message, 404);
}

function service_unavailable($message) {
  return new Error($message, 503);
}
