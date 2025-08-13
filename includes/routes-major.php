<?php
register_rest_route('scientist/v1', '/major/list', [
    'methods' => 'GET',
    'callback' => 'get_majors_list',
    'permission_callback' => '__return_true'
]);

register_rest_route('scientist/v1', '/major/add', [
    'methods' => 'POST',
    'callback' => 'add_major',
    'permission_callback' => function () {
        return current_user_can('edit_posts');
    }
]);

register_rest_route('scientist/v1', '/major/update/(?P<id>\d+)', [
    'methods' => 'POST',
    'callback' => 'update_major',
    'permission_callback' => function () {
        return current_user_can('edit_posts');
    }
]);