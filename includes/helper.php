<?php

if (!defined('ABSPATH')) exit; // Stop if accessed directly

function scientist_json($data, $status = 'success', $code = 200) {
    wp_send_json([
        'status' => $status,
        'data'   => $data
    ], $code, JSON_UNESCAPED_UNICODE);
}

function scientist_error($message, $code = 400) {
    wp_send_json([
        'status'  => 'error',
        'message' => $message
    ], $code, JSON_UNESCAPED_UNICODE);
}

