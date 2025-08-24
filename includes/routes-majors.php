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

register_rest_route('scientist/v1', '/major/(?P<field_code>\d+)', [
    'methods' => 'GET',
    'callback' => 'get_by_code',
    'permission_callback' => '__return_true'
]);

register_rest_route('scientist/v1', '/major/add', [
    'methods' => 'POST',
    'callback' => 'add_major',
    'permission_callback' => function () {
        return current_user_can('edit_posts');
    }
]);

register_rest_route('scientist/v1', '/specialization-group/add', [
    'methods' => 'POST',
    'callback' => 'add_specialization_group',
    'permission_callback' => function () {
        return current_user_can('edit_posts');
    }
]);

register_rest_route('scientist/v1', '/specialization/add', [
    'methods' => 'POST',
    'callback' => 'add_specialization',
    'permission_callback' => function () {
        return current_user_can('edit_posts');
    }
]);

register_rest_route('scientist/v1', '/major/update/(?P<major_code>\d+)', [
    'methods' => 'POST',
    'callback' => 'update_major',
    'permission_callback' => function () {
        return current_user_can('edit_posts');
    }
]);

register_rest_route('scientist/v1', '/specialization-group/update/(?P<group_code>\w+)', [
    'methods' => 'POST',
    'callback' => 'update_specialization_group',
    'permission_callback' => function () {
        return current_user_can('edit_posts');
    }
]);

register_rest_route('scientist/v1', '/specialization/update/(?P<specialization_code>\w+)', [
    'methods' => 'POST',
    'callback' => 'update_specialization',
    'permission_callback' => function () {
        return current_user_can('edit_posts');
    }
]);

function get_majors_list()
{
    global $wpdb;
    try {
        $majors = $wpdb->get_results(
            "SELECT id, field_code as major_code, field_name as major_name
             FROM field 
             WHERE level = 'major'",
            ARRAY_A
        );
        foreach ($majors as &$major) {
            $groups = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT id, field_code as group_code, field_name as group_name
                     FROM field 
                     WHERE parent_id = %d 
                     AND level = 'group'",
                    $major['id']
                ),
                ARRAY_A
            );

            foreach ($groups as &$group) {
                $specialize = $wpdb->get_results(
                    $wpdb->prepare(
                        "SELECT field_code as specialize_code, field_name as specialize_name 
                         FROM field 
                         WHERE parent_id = %d 
                         AND level = 'specialization'",
                        $group['id']
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
    $major_code = isset($request['major_code']) ? $request['major_code'] : '';

    if (empty($major_code)) {
        return scientist_error('Missing major code', 400);
    }

    $validation = validate_field_code($major_code, 'major', $wpdb);
    if ($validation !== true) {
        return scientist_error($validation, 400);
    }

    $major = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT id FROM field WHERE field_code = %s AND level = 'major'",
            $major_code
        ),
        ARRAY_A
    );
    if (!$major) {
        return scientist_error('Major not found', 404);
    }
    try {
        $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT field_code as group_code, field_name as group_name 
                 FROM field 
                 WHERE parent_id = %d
                 AND level = 'group'",
                $major['id']
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

    if (empty($major_code) || empty($group_code)) {
        return scientist_error('Missing code(s)', 400);
    }
    $validation = validate_field_code($major_code, 'major', $wpdb);
    if ($validation !== true) {
        return scientist_error($validation, 400);
    }

    $validation2 = validate_field_code($group_code, 'group', $wpdb);
    if ($validation2 !== true) {
        return scientist_error($validation2, 400);
    }

    $major = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT id FROM field WHERE field_code = %s AND level = 'major'",
            $major_code
        ),
        ARRAY_A
    );
    if (!$major) {
        return scientist_error('Major not found', 404);
    }

    $group = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT id FROM field WHERE field_code = %s AND level = 'group' AND parent_id = %d",
            $group_code,
            $major['id']
        ),
        ARRAY_A
    );
    if (!$group) {
        return scientist_error('Specialization group not found', 404);
    }

    try {
        $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT field_code as specialization_code, field_name as specialization_name 
                 FROM field 
                 WHERE parent_id = %d
                 AND level = 'specialization'",
                $group['id']
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

    if (empty($major_code) || empty($group_code) || empty($specialization_code)) {
        return scientist_error('Missing code(s)', 400);
    }

    $validation = validate_field_code($major_code, 'major', $wpdb);
    if ($validation !== true) {
        return scientist_error($validation, 400);
    }

    $validation2 = validate_field_code($group_code, 'group', $wpdb);
    if ($validation2 !== true) {
        return scientist_error($validation2, 400);
    }

    $validation3 = validate_field_code($specialization_code, 'specialization', $wpdb);
    if ($validation3 !== true) {
        return scientist_error($validation3, 400);
    }

    $major = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT id FROM field WHERE field_code = %s AND level = 'major'",
            $major_code
        ),
        ARRAY_A
    );
    if (!$major) {
        return scientist_error('Major not found', 404);
    }

    $group = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT id FROM field WHERE field_code = %s AND level = 'group' AND parent_id = %d",
            $group_code,
            $major['id']
        ),
        ARRAY_A
    );
    if (!$group) {
        return scientist_error('Specialization group not found', 404);
    }

    $specialization = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT field_code as specialize_code, field_name as specialization_name 
             FROM field 
             WHERE field_code = %s AND level = 'specialization' AND parent_id = %d",
            $specialization_code,
            $group['id']
        ),
        ARRAY_A
    );
    if (!$specialization) {
        return scientist_error('Specialization not found', 404);
    }
    if ($wpdb->last_error) {
        return scientist_error('Database error: ' . $wpdb->last_error, 500);
    }
    return scientist_json($specialization);
}

function get_by_code($request)
{
    global $wpdb;
    try {
        $field_code = sanitize_text_field($request['field_code']);

        if (empty($field_code)) {
            return scientist_error('Missing field code', 400);
        }
        if (!preg_match('/^9/', $field_code)) {
            return scientist_error("Code must start with 9", 400);
        }

        $field = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT id, field_name , field_code , level, parent_id
             FROM field 
             WHERE field_code = %s",
                $field_code
            ),
            ARRAY_A
        );

        if ($wpdb->last_error) {
            return scientist_error('Database error: ' . $wpdb->last_error, 500);
        }

        if (!$field) {
            return scientist_error('Field not found', 404);
        }
        return scientist_json($field);
    } catch (Exception $e) {
        return scientist_error('Invalid request: ' . $e->getMessage(), 400);
    }
}


function add_major($data)
{
    global $wpdb;

    $major_name = sanitize_text_field($data['major_name']);
    $major_code = sanitize_text_field($data['major_code']);


    $validation = validate_field_code($major_code, 'major', $wpdb);
    if ($validation !== true) {
        return scientist_error($validation, 400);
    }

    if (empty($major_name)) {
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

    // Insert new major (parent_id is NULL)
    $inserted = $wpdb->insert(
        'field',
        [
            'field_name'   => $major_name,
            'field_code'   => $major_code,
            'level'        => 'major',
            'parent_id'    => null
        ],
        [
            '%s',
            '%s',
            '%s',
            'NULL'
        ]
    );

    if ($inserted === false) {
        return scientist_error('Failed to add major', 500);
    }

    return scientist_json([
        'major_id' => $wpdb->insert_id
    ]);
}

function add_specialization_group($data)
{
    global $wpdb;

    $major_code = sanitize_text_field($data['major_code']);
    $group_name = sanitize_text_field($data['group_name']);
    $group_code = sanitize_text_field($data['group_code']);

    $validation = validate_field_code($group_code, 'group', $wpdb);
    if ($validation !== true) {
        return scientist_error($validation, 400);
    }
    $validation2 = validate_field_code($major_code, 'major', $wpdb);
    if ($validation2 !== true) {
        return scientist_error($validation2, 400);
    }

    if (empty($major_code) || empty($group_name)) {
        return scientist_error('Invalid input data', 400);
    }

    // Get parent major id
    $major = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT id FROM field WHERE field_code = %s AND level = 'major'",
            $major_code
        ),
        ARRAY_A
    );
    if (!$major) {
        return scientist_error('Major not found', 404);
    }

    $exists = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT COUNT(*) FROM field WHERE field_code = %s AND level = 'group' AND parent_id = %d",
            $group_code,
            $major['id']
        )
    );

    if ($exists > 0) {
        return scientist_error('Specialization group code already exists', 409);
    }

    // Insert new specialization group
    $inserted = $wpdb->insert(
        'field',
        [
            'field_name'   => $group_name,
            'field_code'   => $group_code,
            'level'        => 'group',
            'parent_id'    => $major['id']
        ],
        [
            '%s',
            '%s',
            '%s',
            '%d'
        ]
    );

    if ($inserted === false) {
        return scientist_error('Failed to add specialization group', 500);
    }

    return scientist_json([
        'group_id' => $wpdb->insert_id
    ]);
}

function add_specialization($data)
{
    global $wpdb;

    $major_code = sanitize_text_field($data['major_code']);
    $group_code = sanitize_text_field($data['group_code']);
    $specialization_name = sanitize_text_field($data['specialization_name']);
    $specialization_code = sanitize_text_field($data['specialization_code']);

    $validation = validate_field_code($specialization_code, 'specialization', $wpdb);
    if ($validation !== true) {
        return scientist_error($validation, 400);
    }

    if (empty($major_code) || empty($group_code) || empty($specialization_name)) {
        return scientist_error('Invalid input data', 400);
    }

    // Get parent group id
    $major = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT id FROM field WHERE field_code = %s AND level = 'major'",
            $major_code
        ),
        ARRAY_A
    );
    if (!$major) {
        return scientist_error('Major not found', 404);
    }
    $group = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT id FROM field WHERE field_code = %s AND level = 'group' AND parent_id = %d",
            $group_code,
            $major['id']
        ),
        ARRAY_A
    );
    if (!$group) {
        return scientist_error('Specialization group not found', 404);
    }

    $exists = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT COUNT(*) FROM field WHERE field_code = %s AND level = 'specialization' AND parent_id = %d",
            $specialization_code,
            $group['id']
        )
    );

    if ($exists > 0) {
        return scientist_error('Specialization code already exists', 409);
    }

    $inserted = $wpdb->insert(
        'field',
        [
            'field_name'   => $specialization_name,
            'field_code'   => $specialization_code,
            'level'        => 'specialization',
            'parent_id'    => $group['id']
        ],
        [
            '%s',
            '%s',
            '%s',
            '%d'
        ]
    );

    if ($inserted === false) {
        return scientist_error('Failed to add specialization', 500);
    }

    return scientist_json([
        'specialization_id' => $wpdb->insert_id
    ]);
}

function update_major(WP_REST_Request $request)
{
    $major_code = sanitize_text_field($request['major_code']);
    return update_field($major_code, 'major', $request->get_params());
}

function update_specialization_group(WP_REST_Request $request)
{
    $group_code = sanitize_text_field($request['group_code']);
    return update_field($group_code, 'group', $request->get_params());
}

function update_specialization(WP_REST_Request $request)
{
    $specialization_code = sanitize_text_field($request['specialization_code']);
    return update_field($specialization_code, 'specialization', $request->get_params());
}


function validate_field_code($code, $level, $wpdb)
{
    if (empty($code) || !ctype_digit($code)) {
        return "Code must be numeric";
    }

    if ($level === 'major') {
        // Major code: 3 digits, starts with 9
        if (!preg_match('/^9\d{2}$/', $code)) {
            return "Major code must be 3 digits and start with 9";
        }
    } elseif ($level === 'group') {
        // Group code: 5 digits
        if (!preg_match('/^\d{5}$/', $code)) {
            return "Group code must be 5 digits";
        }

        // First 3 digits must be a valid major code
        $major_code = substr($code, 0, 3);
        $exists = $wpdb->get_var(
            $wpdb->prepare("SELECT COUNT(*) FROM field WHERE field_code = %s AND level = 'major'", $major_code)
        );
        if (!$exists) {
            return "Group code must start with an existing major code";
        }
    } elseif ($level === 'specialization') {
        // Example: 7 digits, starts with group code
        if (!preg_match('/^\d{7}$/', $code)) {
            return "Specialization code must be 7 digits";
        }

        // First 5 digits must be a valid group code
        $group_code = substr($code, 0, 5);
        $exists = $wpdb->get_var(
            $wpdb->prepare("SELECT COUNT(*) FROM field WHERE field_code = %s AND level = 'group'", $group_code)
        );
        if (!$exists) {
            return "Specialization code must start with an existing group code";
        }
    } else {
        return "Invalid level";
    }

    return true;
}


function update_field($field_code, $level, $data)
{
    global $wpdb;

    $new_name       = sanitize_text_field($data['new_name'] ?? '');
    $new_code       = sanitize_text_field($data['new_code'] ?? '');

    if (!empty($data['field_code']) && $data['field_code'] !== $field_code) {
        return scientist_error('Field code mismatch between URL and body', 400);
    }

    $validation = validate_field_code($field_code, $level, $wpdb);

    if ($validation !== true) {
        return scientist_error($validation, 400);
    }

    if (!empty($new_code)) {
        $validation = validate_field_code($new_code, $level, $wpdb);
        if ($validation !== true) {
            return scientist_error($validation, 400);
        }
    }

    if (empty($new_name) && empty($new_code)) {
        return scientist_error('No update data provided', 400);
    }
    // Get current field
    $field = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT id, field_name, field_code FROM field WHERE field_code = %s AND level = %s",
            $field_code,
            $level
        ),
        ARRAY_A
    );
    if (!$field) {
        return scientist_error(ucfirst($level) . ' not found', 404);
    }

    $fields  = [];
    $formats = [];

    // Update name if changed
    if ($new_name && $field['field_name'] !== $new_name) {
        $fields['field_name'] = $new_name;
        $formats[] = '%s';
    }

    // Update code if changed
    if ($new_code && $field['field_code'] !== $new_code) {
        $exists = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM field WHERE field_code = %s AND level = %s AND id != %d",
                $new_code,
                $level,
                $field['id']
            )
        );
        if ($exists) {
            return scientist_error(ucfirst($level) . ' code already exists', 409);
        }
        $fields['field_code'] = $new_code;
        $formats[] = '%s';
    }

    if (!$fields) {
        return scientist_error(['message' => 'No changes detected']);
    }

    $updated = $wpdb->update(
        'field',
        $fields,
        ['id' => $field['id']],
        $formats,
        ['%d']
    );

    if ($updated === false) {
        return scientist_error('Failed to update ' . $level, 500);
    }

    return scientist_json([
        'message'        => ucfirst($level) . ' updated successfully',
        'updated_fields' => $fields
    ]);
}
