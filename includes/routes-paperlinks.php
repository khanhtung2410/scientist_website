<?php
register_rest_route('scientist/v1', '/paperlinks/list', [
    'methods' => 'GET',
    'callback' => 'get_all_paperlinks',
    'permission_callback' => '__return_true'
]);

register_rest_route('scientist/v1', '/paperlinks/(?P<scientist_id>\d+)/list', [
    'methods' => 'GET',
    'callback' => 'get_scientist_paperlinks',
    'permission_callback' => '__return_true'
]);

register_rest_route('scientist/v1', '/paperlinks/(?P<scientist_id>\d+)/add', [
    'methods' => 'POST',
    'callback' => 'add_paperlink',
    'permission_callback' => function () {
        return current_user_can('edit_posts');
    }
]);

register_rest_route('scientist/v1', '/paperlinks/(?P<id>\d+)/delete', [
    'methods' => 'DELETE',
    'callback' => 'delete_paperlink',
    'permission_callback' => function () {
        return current_user_can('edit_posts');
    }
]);

register_rest_route('scientist/v1', '/paperlinks/(?P<id>\d+)/update', [
    'methods' => 'POST',
    'callback' => 'update_paperlink',
    'permission_callback' => function () {
        return current_user_can('edit_posts');
    }
]);

function get_all_paperlinks()
{
    global $wpdb;
    $results = $wpdb->get_results("SELECT * FROM paper_links", ARRAY_A);

    if (empty($results)) {
        return scientist_error('No paper links found', 404);
    }

    return scientist_json($results);
}

function get_scientist_paperlinks($request)
{
    global $wpdb;
    $scientist_id = $request['scientist_id'];
    if (!empty($request['scientist_id']) && $request['scientist_id'] !== $scientist_id) {
        return scientist_error('Scientist id mismatch between URL and body', 400);
    }

    if (!is_numeric($scientist_id) || $scientist_id <= 0) {
        return scientist_error('Invalid scientist ID', 400);
    }
    $existing_scientist = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM scientists WHERE id = %d", $scientist_id));
    if ($existing_scientist == 0) {
        return scientist_error('Scientist not found', 404);
    }

    $results = $wpdb->get_results($wpdb->prepare("SELECT * FROM paper_links WHERE scientist_id = %d", $scientist_id), ARRAY_A);

    if (empty($results)) {
        return scientist_error('No paper links found for this scientist', 404);
    }

    return scientist_json($results);
}

function add_paperlink($data)
{
    global $wpdb;

    $scientist_id = intval($data['scientist_id'] ?? 0);
    $urls         = $data['url'] ?? [];

    if ($scientist_id <= 0) {
        return scientist_error('Invalid or missing scientist ID', 400);
    }

    // Normalize to array
    if (!is_array($urls)) {
        $urls = [$urls];
    }

    if (empty($urls)) {
        return scientist_error('At least one URL is required', 400);
    }

    $exists = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM scientists WHERE id = %d",
        $scientist_id
    ));
    if ($exists == 0) {
        return scientist_error('Scientist not found', 404);
    }

    $inserted_links = [];
    $skipped_links  = [];

    foreach ($urls as $url) {
        $url = esc_url_raw(trim($url));
        if (empty($url)) {
            $skipped_links[] = ['url' => $url, 'reason' => 'Invalid URL'];
            continue;
        }

        // Check duplicate
        $existing = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM paper_links WHERE scientist_id = %d AND url = %s",
            $scientist_id, $url
        ));
        if ($existing > 0) {
            $skipped_links[] = ['url' => $url, 'reason' => 'Already exists'];
            continue;
        }

        // Insert
        $inserted = $wpdb->insert('paper_links', [
            'scientist_id' => $scientist_id,
            'url'          => $url,
            'created_at'   => current_time('mysql'),
            'updated_at'   => current_time('mysql')
        ]);

        if ($inserted !== false) {
            $inserted_links[] = [
                'id'  => $wpdb->insert_id,
                'url' => $url
            ];
        } else {
            $skipped_links[] = ['url' => $url, 'reason' => 'DB insert failed'];
        }
    }

    if (empty($inserted_links)) {
        return scientist_error('No valid paper links were added', 400);
    }

    return scientist_json([
        'message'   => 'Paper link(s) processed',
        'inserted'  => $inserted_links,
        'skipped'   => $skipped_links
    ]);
}


function delete_paperlink($request)
{
    global $wpdb;
    $id = $request['id'];

    if (!empty($request['id']) && $request['id'] !== $id) {
        return scientist_error('Paper link id mismatch between URL and body', 400);
    }

    if (!is_numeric($id) || $id <= 0) {
        return scientist_error('Invalid paper link ID', 400);
    }

    $existing_link = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM paper_links WHERE id = %d", $id));
    if ($existing_link == 0) {
        return scientist_error('Paper link not found', 404);
    }

    $deleted = $wpdb->delete('paper_links', ['id' => $id]);

    if ($deleted === false) {
        return scientist_error('Failed to delete paper link', 500);
    }

    return scientist_json(['message' => 'Paper link deleted successfully']);
}

function update_paperlink($request)
{
    global $wpdb;
    $id = $request['id'];

    if (!is_numeric($id) || $id < 0) {
        return scientist_error('Invalid paper link ID', 400);
    }
    if (empty($request['url'])) {
        return scientist_error('URL must be provided', 400);
    }

    $existing_link = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM paper_links WHERE id = %d", $id));
    if ($existing_link == 0) {
        return scientist_error('Paper link not found', 404);
    }

    $update_data = [];
    if (!empty($request['url'])) {
        $update_data['url'] = esc_url_raw($request['url']);
    }
    $update_data['updated_at'] = current_time('mysql');

    $updated = $wpdb->update('paper_links', $update_data, ['id' => $id]);

    if ($updated === false) {
        return scientist_error('Failed to update paper link', 500);
    }

    return scientist_json(['message' => 'Paper link updated successfully']);
}
