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

function get_majors_list()
{
    global $wpdb;
    try {
        $results = $wpdb->get_results("
        SELECT m.* FROM majors AS m ", ARRAY_A);
        foreach ($results as &$majors) {
            $majors['specialization_groups'] = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT specialization_group_code, specialization_group_name 
                     FROM specialization_group 
                     WHERE major_code = %d",
                    $majors['major_code']
                ),
                ARRAY_A
            );

            foreach ($majors['specialization_groups'] as &$group) {
                $group['specializations'] = $wpdb->get_results(
                    $wpdb->prepare(
                        "SELECT specialization_code, specialization_name 
                         FROM specialization 
                         WHERE specialization_group_code = %s",
                        $group['specialization_group_code']
                    ),
                    ARRAY_A
                );
            }
        }
        if ($wpdb->last_error) {
            return scientist_error('Database error: ' . $wpdb->last_error, 500);
        }
        return scientist_json($results);
    } catch (Exception $e) {
        return scientist_error('Unexpected error: ' . $e->getMessage(), 500);
    }
}

function get_specialization_groups($request)
{
    global $wpdb;
    $major_code = $request['major_code'];
    if (!is_numeric($major_code)) {
        return scientist_error('Invalid major code', 400);
    }
    try {
        $invalid_major = $wpdb->get_var(
            $wpdb->prepare("SELECT COUNT(*) FROM majors WHERE major_code = %d", $major_code)
        );
        if ($invalid_major == 0) {
            return scientist_error('Major not found', 404);
        }
        $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT specialization_group_code, specialization_group_name 
                 FROM specialization_group 
                 WHERE major_code = %d",
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

    if (!is_numeric($major_code) || empty($group_code)) {
        return scientist_error('Invalid parameters', 400);
    }

    try {
        $invalid_major = $wpdb->get_var(
            $wpdb->prepare("SELECT COUNT(*) FROM majors WHERE major_code = %d", $major_code)
        );
        if ($invalid_major == 0) {
            return scientist_error('Major not found', 404);
        }
        $invalid_group = $wpdb->get_var(
            $wpdb->prepare("SELECT COUNT(*) FROM specialization_group WHERE specialization_group_code = %s AND major_code = %d", $group_code, $major_code)
        );
        if ($invalid_group == 0) {
            return scientist_error('Specialization group not found', 404);
        }

        $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT specialization_code, specialization_name 
                 FROM specialization 
                 WHERE specialization_group_code = %s",
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
