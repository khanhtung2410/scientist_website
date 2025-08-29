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
    $result = $wpdb->get_row($wpdb->prepare(
        "SELECT s.name, s.birth_year, s.gender, s.work_place, s.position , ar.abbreviation, ar.name, images.link as image_link, ppl.url
        FROM scientists AS s 
        INNER JOIN academic_ranks AS ar ON s.academic_rank_id = ar.id
        LEFT JOIN images ON s.id = images.scientist_id
        LEFT JOIN paper_links AS ppl ON ppl.scientist_id = s.id
        LEFT JOIN research_fields AS rf ON rf.scientist_id = s.id
        WHERE s.id = %d",
        $id
    ), ARRAY_A);

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
    $paper_links = isset($data['paper_links']) && is_array($data['paper_links']) ? array_map('esc_url_raw', $data['paper_links']) : [];

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

    $added_links = [];
    if (!empty($paper_links)) {
        foreach ($paper_links as $link) {
            $result = add_paperlink([
                'scientist_id' => $id,
                'url'          => $link
            ]);
            if (isset($result['id'])) {
                $added_links[] = $link;
            }
        }
    }

    return scientist_json([
        'id' => $id,
        'name' => $name,
        'image_link' => $image_url,
        'paper_urls' => $added_links,
        'message' => 'Scientist added successfully'
    ]);
}

