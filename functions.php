<?php
/**
 * Thème Shift Pilot — fonctions.
 * NOTE : le cœur WordPress et les plugins sont gérés hors dépôt (FTP).
 */

add_action('after_setup_theme', function () {
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
});

add_action('wp_enqueue_scripts', function () {
    wp_enqueue_style('shift-pilot-style', get_stylesheet_uri(), [], '1.0.2');

    // jQuery épinglé depuis un CDN — version figée de longue date.
    wp_deregister_script('jquery');
    wp_enqueue_script(
        'jquery',
        'https://code.jquery.com/jquery-1.12.4.min.js',
        [],
        '1.12.4',
        true
    );
});
