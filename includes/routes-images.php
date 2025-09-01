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

        // Check if multiple files were uploaded
        if (!empty($_FILES['pictures']) && is_array($_FILES['pictures']['name'])) {
            require_once(ABSPATH . 'wp-admin/includes/file.php');
            require_once(ABSPATH . 'wp-admin/includes/image.php');

            $uploaded_urls = [];
            $wpdb->query('START TRANSACTION');
            try {
                foreach ($_FILES['pictures']['name'] as $key => $name) {
                    if (!empty($_FILES['pictures']['tmp_name'][$key]) && is_uploaded_file($_FILES['pictures']['tmp_name'][$key])) {
                        
                        $file = [
                            'name'     => $_FILES['pictures']['name'][$key],
                            'type'     => $_FILES['pictures']['type'][$key],
                            'tmp_name' => $_FILES['pictures']['tmp_name'][$key],
                            'error'    => $_FILES['pictures']['error'][$key],
                            'size'     => $_FILES['pictures']['size'][$key],
                        ];

                        $uploaded = wp_handle_upload($file, ['test_form' => false]);
                        if (isset($uploaded['error'])) {
                            $wpdb->query('ROLLBACK');
                            return scientist_error('Image upload failed: ' . $uploaded['error'], 400);
                        }

                        $image_url = $uploaded['url'];   // Public URL
                        $image_path = $uploaded['file']; // Server file path

                        $wpdb->insert(
                            'images',
                            [
                                'scientist_id' => $id,
                                'path'         => $image_path,
                                'link'         => $image_url
                            ]
                        );

                        $uploaded_urls[] = $image_url;
                    }
                }

                if (!empty($uploaded_urls)) {
                    $wpdb->query('COMMIT');
                    return scientist_json([
                        'message'     => 'Images added successfully',
                        'images'      => $uploaded_urls
                    ]);
                } else {
                    $wpdb->query('ROLLBACK');
                    return scientist_error('No valid image uploaded', 400);
                }
            } catch (\Throwable $e) {
                $wpdb->query('ROLLBACK');
                return scientist_error('Image upload failed: ' . $e->getMessage(), 400);
            }
        }

        return scientist_error('No image files provided', 400);
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

    $wpdb->query('START TRANSACTION');
    try {
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

        $wpdb->query('COMMIT');
        return scientist_json(['message' => 'Image removed successfully']);
    } catch (\Throwable $e) {
        $wpdb->query('ROLLBACK');
        return scientist_error('Failed to remove image: ' . $e->getMessage(), 500);
    }
}

