<?php
/*
Plugin Name: Scientist API
Description: Custom REST API endpoints for Scientist project.
Version: 1.0
Author: Me
*/

add_action('rest_api_init', function () {
    require_once __DIR__ . '/includes/routes-academicranks.php';
    require_once __DIR__ . '/includes/routes-scientists.php';
    require_once __DIR__ . '/includes/routes-majors.php';
    require_once __DIR__ . '/includes/routes-paperlinks.php';
    require_once __DIR__ . '/includes/routes-images.php';
    require_once __DIR__ . '/includes/helper.php';
});
