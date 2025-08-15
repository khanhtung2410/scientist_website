<?php
register_rest_route('scientist/v1', '/major/list', [
    'methods' => 'GET',
    'callback' => 'get_majors_list',
    'permission_callback' => '__return_true'
]);

register_rest_route('scientist/v1', '/major/(?P<major_code>\d+)/specialization-group', [
    'methods' => 'GET',
    'callback' => 'get_specialization_groups',
    'permission_callback' => '__return_true'
]);

register_rest_route('scientist/v1', '/major/(?P<major_code>\d+)/specialization-group/(?P<group_code>\w+)', [
    'methods' => 'GET',
    'callback' => 'get_specializations',
    'permission_callback' => '__return_true'
]);

register_rest_route('scientist/v1', '/major/(?P<major_code>\d+)/specialization-group/(?P<group_code>\w+)/(?P<specialization_code>\w+)', [
    'methods' => 'GET',
    'callback' => 'get_specialization',
    'permission_callback' => '__return_true'
]);

register_rest_route('scientist/v1', '/major/add', [
    'methods' => 'POST',
    'callback' => 'add_major',
    'permission_callback' => function () {
        return current_user_can('edit_posts');
    }
]);

function get_majors_list()
{
    global $wpdb;
    try {
        $majors = $wpdb->get_results(
            "SELECT field_code as major_code, field_name as major_name
             FROM field 
             WHERE level ='major'",
            ARRAY_A
        );
        foreach ($majors as &$major) {
            $groups = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT field_code as group_code, field_name as group_name
                     FROM field 
                     WHERE parent_code = %s 
                     AND level = 'group'",
                    $major['major_code']
                ),
                ARRAY_A
            );

            foreach ($groups as &$group) {
                $specialize = $wpdb->get_results(
                    $wpdb->prepare(
                        "SELECT field_code as specialize_code, field_name as specialize_name 
                         FROM field 
                         WHERE parent_code = %s 
                         AND level = 'specialization'",
                        $group['group_code']
                    ),
                    ARRAY_A
                );
                $group['specializations'] = $specialize;
            }
            $major['groups'] = $groups;
        }
        if ($wpdb->last_error) {
            return scientist_error('Database error: ' . $wpdb->last_error, 500);
        }
        return scientist_json($majors);
    } catch (Exception $e) {
        return scientist_error('Unexpected error: ' . $e->getMessage(), 500);
    }
}

function get_specialization_groups($request)
{
    global $wpdb;
    $major_code = isset($request['major_code']) ? intval($request['major_code']) : 0;
    if (!is_numeric($major_code) || $major_code <= 0) {
        return scientist_error('Invalid major code', 400);
    }
    try {
        $exist = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) 
                FROM field 
                WHERE field_code = %s 
                AND level = 'major'",
                $major_code
            )
        );
        if ($exist != 1) {
            return scientist_error('Major not found', 404);
        }
        $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT field_code as group_code, field_name as group_name 
                 FROM field 
                 WHERE parent_code = %s
                 AND level = 'group'",
                $major_code
            ),
            ARRAY_A
        );
        if ($wpdb->last_error) {
            return scientist_error('Database error: ' . $wpdb->last_error, 500);
        }
        return scientist_json($results);
    } catch (Exception $e) {
        return scientist_error('Unexpected error: ' . $e->getMessage(), 500);
    }
}

function get_specializations($request)
{
    global $wpdb;
    $major_code = $request['major_code'];
    $group_code = $request['group_code'];

    if (!is_numeric($major_code) || !is_numeric($group_code) || $major_code <= 0 || $group_code <= 0) {
        return scientist_error('Invalid parameters', 400);
    }

    try {
        $exist = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) 
                 FROM field 
                 WHERE field_code = %d
                 AND level = 'major'",
                $major_code
            )
        );
        if ($exist == 0) {
            return scientist_error('Major not found', 404);
        }
        $exist = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) 
                 FROM field 
                 WHERE parent_code = %s 
                 AND field_code = %d
                 AND level = 'group'",
                $major_code,
                $group_code
            )
        );
        if ($exist == 0) {
            return scientist_error('Specialization group not found', 404);
        }

        $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT field_code as specialization_code, field_name as specialization_name 
                 FROM field 
                 WHERE parent_code = %s
                 AND level = 'specialization'",
                $group_code
            ),
            ARRAY_A
        );

        if ($wpdb->last_error) {
            return scientist_error('Database error: ' . $wpdb->last_error, 500);
        }
        return scientist_json($results);
    } catch (Exception $e) {
        return scientist_error('Unexpected error: ' . $e->getMessage(), 500);
    }
}

function get_specialization($request)
{
    global $wpdb;
    $major_code = $request['major_code'];
    $group_code = $request['group_code'];
    $specialization_code = $request['specialization_code'];

    if (
        !is_numeric($major_code) || !isset($group_code) || !isset($specialization_code) ||
        $major_code <= 0 || $group_code <= 0 || $specialization_code <= 0
    ) {
        return scientist_error('Invalid parameters', 400);
    }
    try {
        $valid_major = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) 
                 FROM field 
                 WHERE field_code = %d
                 AND level = 'major'",
                $major_code
            )
        );
        if ($valid_major == 0) {
            return scientist_error('Major not found', 404);
        }
        $valid_group = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) 
                 FROM field 
                 WHERE parent_code = %s 
                 AND field_code = %d
                 AND level = 'group'",
                $major_code,
                $group_code
            )
        );
        if ($valid_group == 0) {
            return scientist_error('Specialization group not found', 404);
        }
        $valid_specialization = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) 
                 FROM field 
                 WHERE parent_code = %s 
                 AND field_code = %d
                 AND level = 'specialization'",
                $group_code,
                $specialization_code
            )
        );
        if ($valid_specialization == 0) {
            return scientist_error('Specialization not found', 404);
        }

        $result = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT field_code as specialize_code, field_name as specialization_name 
                 FROM field 
                 WHERE parent_code = %s
                 AND field_code = %d
                 AND level = 'specialization'",
                $group_code,
                $specialization_code
            ),
            ARRAY_A
        );

        if ($wpdb->last_error) {
            return scientist_error('Database error: ' . $wpdb->last_error, 500);
        }
        return scientist_json($result);
    } catch (Exception $e) {
        return scientist_error('Unexpected error: ' . $e->getMessage(), 500);
    }
}

function add_major($data)
{
    global $wpdb;

    $major_name = sanitize_text_field($data['major_name']);
    $major_code = sanitize_text_field($data['major_code']);

    if (empty($major_name) || empty($major_code) || !is_numeric($major_code)) {
        return scientist_error('Invalid input data', 400);
    }

    $exists = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT COUNT(*) FROM field WHERE field_code = %s AND level = 'major'",
            $major_code
        )
    );

    if ($exists > 0) {
        return scientist_error('Major code already exists', 409);
    }

    // Insert new major
    $inserted = $wpdb->insert(
        'field',
        [
            'field_name'   => $major_name,
            'field_code'   => $major_code,
            'level'        => 'major',
            'parent_code'  => null
        ],
        [
            '%s',
            '%s',
            '%s',
            '%d'
        ]
    );

    if ($inserted === false) {
        return scientist_error('Failed to add major', 500);
    }

    return scientist_json([
        'major_id' => $wpdb->insert_id
    ]);
}
