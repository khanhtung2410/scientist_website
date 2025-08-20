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
    // Get major id by code
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

    // Get major id
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
    // Get group id
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
    // Get major id
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
    // Get group id
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
    // Get specialization
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

function add_major($data)
{
    global $wpdb;

    $major_name = sanitize_text_field($data['major_name']);
    $major_code = sanitize_text_field($data['major_code']);

    if (empty($major_name) || empty($major_code)) {
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

    if (empty($major_code) || empty($group_name) || empty($group_code)) {
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

    if (empty($major_code) || empty($group_code) || empty($specialization_name) || empty($specialization_code)) {
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

function update_major($data)
{
    global $wpdb;

    $major_code = sanitize_text_field($data['major_code']);
    $new_major_name = !empty($data['new_major_name']) ? sanitize_text_field($data['new_major_name']) : '';
    $new_major_code = !empty($data['new_major_code']) ? sanitize_text_field($data['new_major_code']) : '';

    if (empty($major_code) || (empty($new_major_name) && empty($new_major_code))) {
        return scientist_error('Invalid input data', 400);
    }

    $major = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT id, field_name, field_code FROM field WHERE field_code = %s AND level = 'major'",
            $major_code
        ),
        ARRAY_A
    );
    if (!$major) {
        return scientist_error('Major not found', 404);
    }

    $fields = [];
    $formats = [];

    if (!empty($new_major_name) && $major['field_name'] !== $new_major_name) {
        $fields['field_name'] = $new_major_name;
        $formats[] = '%s';
    }

    if (!empty($new_major_code) && $major['field_code'] !== $new_major_code) {
        // Check duplicate
        $exists = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM field WHERE field_code = %s AND level = 'major' AND id != %d",
                $new_major_code,
                $major['id']
            )
        );
        if ($exists > 0) {
            return scientist_error('Major code already exists', 409);
        }
        $fields['field_code'] = $new_major_code;
        $formats[] = '%s';
    }

    if (empty($fields)) {
        return scientist_json(['success' => false, 'message' => 'No changes detected']);
    }

    $updated = $wpdb->update(
        'field',
        $fields,
        ['id' => $major['id']],
        $formats,
        ['%d']
    );

    if ($updated === false) {
        return scientist_error('Failed to update major', 500);
    }

    return scientist_json([
        'message' => 'Updated successfully',
        'updated_fields' => $fields
    ]);
}

function update_group($data)
{
    global $wpdb;

    $group_code = sanitize_text_field($data['group_code']);
    $new_group_name = !empty($data['new_group_name']) ? sanitize_text_field($data['new_group_name']) : '';
    $new_group_code = !empty($data['new_group_code']) ? sanitize_text_field($data['new_group_code']) : '';

    if (empty($group_code) || (empty($new_group_name) && empty($new_group_code))) {
        return scientist_error('Invalid input data', 400);
    }

    $group = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT id, field_name, field_code FROM field WHERE field_code = %s AND level = 'group'",
            $group_code
        ),
        ARRAY_A
    );
    if (!$group) {
        return scientist_error('Specialization group not found', 404);
    }

    $fields = [];
    $formats = [];

    if (!empty($new_group_name) && $group['field_name'] !== $new_group_name) {
        $fields['field_name'] = $new_group_name;
        $formats[] = '%s';
    }

    if (!empty($new_group_code) && $group['field_code'] !== $new_group_code) {
        // Check duplicate
        $exists = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM field WHERE field_code = %s AND level = 'group' AND id != %d",
                $new_group_code,
                $group['id']
            )
        );
        if ($exists > 0) {
            return scientist_error('Specialization group code already exists', 409);
        }
        $fields['field_code'] = $new_group_code;
        $formats[] = '%s';
    }

    if (empty($fields)) {
        return scientist_json(['success' => false, 'message' => 'No changes detected']);
    }

    $updated = $wpdb->update(
        'field',
        $fields,
        ['id' => $group['id']],
        $formats,
        ['%d']
    );

    if ($updated === false) {
        return scientist_error('Failed to update specialization group', 500);
    }

    return scientist_json([
        'message' => 'Updated successfully',
        'updated_fields' => $fields
    ]);
}

function update_specialization($data)
{
    global $wpdb;

    $specialization_code = sanitize_text_field($data['specialization_code']);
    $new_specialization_name = !empty($data['new_specialization_name']) ? sanitize_text_field($data['new_specialization_name']) : '';
    $new_specialization_code = !empty($data['new_specialization_code']) ? sanitize_text_field($data['new_specialization_code']) : '';

    if (empty($specialization_code) || (empty($new_specialization_name) && empty($new_specialization_code))) {
        return scientist_error('Invalid input data', 400);
    }

    $specialization = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT id, field_name, field_code FROM field WHERE field_code = %s AND level = 'specialization'",
            $specialization_code
        ),
        ARRAY_A
    );
    if (!$specialization) {
        return scientist_error('Specialization not found', 404);
    }

    $fields = [];
    $formats = [];

    if (!empty($new_specialization_name) && $specialization['field_name'] !== $new_specialization_name) {
        $fields['field_name'] = $new_specialization_name;
        $formats[] = '%s';
    }

    if (!empty($new_specialization_code) && $specialization['field_code'] !== $new_specialization_code) {
        // Check duplicate
        $exists = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM field WHERE field_code = %s AND level = 'specialization' AND id != %d",
                $new_specialization_code,
                $specialization['id']
            )
        );
        if ($exists > 0) {
            return scientist_error('Specialization code already exists', 409);
        }
        $fields['field_code'] = $new_specialization_code;
        $formats[] = '%s';
    }

    if (empty($fields)) {
        return scientist_json(['success' => false, 'message' => 'No changes detected']);
    }

    $updated = $wpdb->update(
        'field',
        $fields,
        ['id' => $specialization['id']],
        $formats,
        ['%d']
    );

    if ($updated === false) {
        return scientist_error('Failed to update specialization', 500);
    }
    return scientist_json([
        'message' => 'Updated successfully',
        'updated_fields' => $fields
    ]);
}
