<?php
register_rest_route('scientist/v1', '/scientist/list', [
    'methods' => 'GET',
    'callback' => 'get_scientists_list',
    'permission_callback' => '__return_true'
]);

register_rest_route('scientist/v1', '/scientist/add', [
    'methods' => 'POST',
    'callback' => 'add_scientist',
    'permission_callback' => function () {
        return current_user_can('edit_posts');
    }
]);

register_rest_route('scientist/v1', '/scientist/(?P<id>\d+)', [
    'methods' => 'GET',
    'callback' => 'get_scientist_by_id',
    'permission_callback' => '__return_true'
]);

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



function get_scientists_list()
{
    global $wpdb;
    $results = $wpdb->get_results("
    SELECT s.name, s.birth_year, s.gender, s.work_place, s.position , ar.abbreviation, images.link as image_link
    FROM scientists as s 
    INNER JOIN academic_ranks AS ar ON s.academic_rank_id = ar.id
    LEFT JOIN images ON s.id = images.scientist_id;", ARRAY_A);

    if (empty($results)) {
        return scientist_error('No scientists found', 404);
    }

    foreach ($results as &$scientist) {
        $scientist['image_link'] = !empty($scientist['image_link']) ? $scientist['image_link'] : "https://upload.wikimedia.org/wikipedia/commons/a/ac/Default_pfp.jpg";
    }

    return scientist_json($results);
}

function get_scientist_by_id($data)
{
    global $wpdb;
    $id = (int)$data['id'];
    if ($id <= 0 || !is_numeric($id)) {
        return scientist_error('Invalid scientist ID', 400);
    }
    $result = $wpdb->get_row($wpdb->prepare("
    SELECT * FROM scientists WHERE id = %d", $id), ARRAY_A);

    if (empty($result)) {
        return scientist_error('Scientist not found', 404);
    }

    return scientist_json($result);
}

function add_scientist($data)
{
    global $wpdb;

    $name = sanitize_text_field($data['name']);
    $birth_year = intval($data['birth_year']);
    $gender = sanitize_text_field($data['gender']);
    $position = sanitize_text_field($data['position']);
    $work_place = sanitize_text_field($data['work_place']);
    $bio = sanitize_textarea_field($data['bio'] ?? '');
    $academic_rank_id = intval($data['academic_rank_id']);


    if (
        empty($name) || empty($position) || empty($work_place) || $academic_rank_id <= 0
        || $gender !== "Nam" && $gender !== "Nữ"
    ) {
        return scientist_error('Invalid input data', 400);
    }
    $current_year = intval(date("Y"));
    if ($birth_year < 1800 || $birth_year > $current_year) {
        return scientist_error('Invalid birth year', 400);
    }

    $insert = $wpdb->insert(
        'scientists',
        [
            'name' => $name,
            'birth_year' => $birth_year,
            'gender' => $gender,
            'position' => $position,
            'work_place' => $work_place,
            'bio' => $bio,
            'academic_rank_id' => $academic_rank_id,
            'time_created' => current_time('mysql'),
            'time_updated' => current_time('mysql')
        ]
    );
    if ($insert === false) {
        return scientist_error('Failed to add scientist', 500);
    }
    $id = $wpdb->insert_id;
    $image_url = null;

    $image_url = null;
    $image_result = add_scientist_image(['id' => $id]);

    if (is_array($image_result) && isset($image_result['image_link'])) {
        $image_url = $image_result['image_link'];
    } else {

        $default_image = "http://scientist.local/wp-content/uploads/Default_pfp.jpg";

        $wpdb->insert(
            'images',
            [
                'scientist_id' => $id,
                'path' => '', // No file on server
                'link' => $default_image
            ]
        );

        $image_url = $default_image;
    }

    return scientist_json([
        'id' => $id,
        'name' => $name,
        'image_link' => $image_url,
        'message' => 'Scientist added successfully'
    ]);
}

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
                'path' => $image_path,
                'link' => $image_url
            ]
        );

        return scientist_json([
            'message'     => 'Image added successfully',
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
