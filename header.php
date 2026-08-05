<!doctype html>
<html <?php language_attributes(); ?>>
<head>
  <meta charset="<?php bloginfo('charset'); ?>">
  <script src="<?php echo esc_url(get_template_directory_uri()); ?>/assets/slider.js"></script>
  <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<header class="site-header">
  <h1><?php bloginfo('name'); ?></h1>
  <img src="<?php echo esc_url(get_template_directory_uri()); ?>/assets/logo.png">
</header>
