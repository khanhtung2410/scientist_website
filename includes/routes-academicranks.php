<?php
register_rest_route('scientist/v1', '/academicranks/list', [
    'methods' => 'GET',
    'callback' => 'scientist_get_academic_ranks',
    'permission_callback' => '__return_true'
]);

register_rest_route('scientist/v1', '/academicranks/add', [
    'methods' => 'POST',
    'callback' => 'scientist_add_academic_rank',
    'permission_callback' => '__return_true'
]);

function scientist_get_academic_ranks()
{
    global $wpdb;
    try {
        $results = $wpdb->get_results("SELECT * FROM academic_ranks", ARRAY_A);
        if ($wpdb->last_error) {
            return scientist_error('Database error: ' . $wpdb->last_error, 500);
        }
        return scientist_json($results);
    } catch (Exception $e) {
        return scientist_error('Unexpected error: ' . $e->getMessage(), 500);
    }
}

function scientist_add_academic_rank($data)
{
    global $wpdb;
    if (
        !isset($data['name'], $data['abbreviation']) ||
        trim($data['name']) === '' ||
        trim($data['abbreviation']) === ''
    ) {
        return scientist_error('Invalid input', 400);
    }
    $name = sanitize_text_field($data['name']);
    $abbreviation = sanitize_text_field($data['abbreviation']);

    $exists = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT COUNT(*) FROM academic_ranks WHERE name = %s AND abbreviation = %s",
            $name,
            $abbreviation
        )
    );

    if ( $exists > 0 ) {
        return scientist_error( 'Academic rank already exists', 409 );
    }

    $wpdb->insert('academic_ranks', ['name' => $name, 'abbreviation' => $abbreviation]);

    if ($wpdb->last_error) {
        return scientist_error('Error adding academic rank: ' . $wpdb->last_error, 500);
    }

    return scientist_json([
        'status' => 'success',
        'message' => 'Academic rank added successfully'
    ]);
}
