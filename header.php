<!doctype html>
<html <?php language_attributes(); ?>>
<head>
  <meta charset="<?php bloginfo('charset'); ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<header class="site-header">
  <?php if (is_front_page()) : ?>
    <h1><?php bloginfo('name'); ?></h1>
  <?php else : ?>
    <p class="site-name"><a href="<?php echo esc_url(home_url('/')); ?>"><?php bloginfo('name'); ?></a></p>
  <?php endif; ?>
  <img src="<?php echo esc_url(get_template_directory_uri()); ?>/assets/logo.png" alt="<?php bloginfo('name'); ?>" width="200" height="60">
</header>
