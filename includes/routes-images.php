<?php
register_rest_route('scientist/v1', '/scientist/(?P<id>\d+)/image', [
    'methods' => 'GET',
    'callback' => 'get_scientist_image',
    'permission_callback' => '__return_true'
]);

register_rest_route('scientist/v1', '/scientist/(?P<id>\d+)/image/add', [
    'methods' => 'POST',
    'callback' => 'add_scientist_image',
    'permission_callback' => function () {
        return current_user_can('edit_posts');
    }
]);

register_rest_route('scientist/v1', '/scientist/(?P<id>\d+)/image/remove/(?P<image_id>\d+)', [
    'methods' => 'DELETE',
    'callback' => 'remove_scientist_image',
    'permission_callback' => function () {
        return current_user_can('edit_posts');
    }
]);

function get_scientist_image($data)
{
    global $wpdb;
    $id = (int)$data['id'];
    if ($id <= 0 || !is_numeric($id)) {
        return scientist_error('Invalid scientist ID', 400);
    }
    $image = $wpdb->get_row($wpdb->prepare("
    SELECT link FROM images WHERE scientist_id = %d", $id), ARRAY_A);

    if (empty($image)) {
        return scientist_error('Image not found for this scientist', 404);
    }

    return scientist_json(['image_link' => $image['link']]);
}

function add_scientist_image($data)
{
    global $wpdb;
    $id = (int)$data['id'];
    if ($id <= 0 || !is_numeric($id)) {
        return scientist_error('Invalid scientist ID', 400);
    }

    if (!empty($_FILES['picture']) && isset($_FILES['picture']['tmp_name']) && is_uploaded_file($_FILES['picture']['tmp_name'])) {
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        require_once(ABSPATH . 'wp-admin/includes/image.php');

        $uploaded = wp_handle_upload($_FILES['picture'], ['test_form' => false]);
        if (isset($uploaded['error'])) {
            return scientist_error('Image upload failed: ' . $uploaded['error'], 400);
        }

        $image_url = $uploaded['url'];     // Public URL
        $image_path = $uploaded['file'];   // Absolute server file path

        $wpdb->insert(
            'images',
            [
                'scientist_id' => $id,
                'path'    => $image_path,
                'link'     => $image_url
            ]
        );

        return scientist_json([
            'message'     => 'Image added successfully',
            'id'          => $wpdb->insert_id,
            'image_link'  => $image_url
        ]);
    }

    return scientist_error('No image file provided', 400);
}


function remove_scientist_image($data)
{
    global $wpdb;
    $id = (int)$data['id'];
    $image_id = (int)$data['image_id'];
    if ($id <= 0 || !is_numeric($id) || $image_id <= 0 || !is_numeric($image_id)) {
        return scientist_error('Invalid ID', 400);
    }

    $image = $wpdb->get_row(
        $wpdb->prepare("SELECT path FROM images WHERE scientist_id = %d AND id = %d", $id, $image_id),
        ARRAY_A
    );

    if (empty($image)) {
        return scientist_error('Image not found for this scientist', 404);
    }

    // Delete file from server if exists
    if (!empty($image['path']) && file_exists($image['path'])) {
        unlink($image['path']);
    }

    // Delete from DB
    $wpdb->delete(
        'images',
        [
            'scientist_id' => $id,
            'id'           => $image_id
        ],
        ['%d', '%d']
    );

    return scientist_json(['message' => 'Image removed successfully']);
}

