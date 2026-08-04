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
});
