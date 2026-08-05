<?php get_header(); ?>
<main class="site-content">
  <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
    <article <?php post_class(); ?>>
      <?php if (is_front_page()) : ?>
        <h2><?php the_title(); ?></h2>
      <?php else : ?>
        <h1><?php the_title(); ?></h1>
      <?php endif; ?>
      <?php if (has_post_thumbnail()) : ?>
        <?php the_post_thumbnail('full'); ?>
      <?php endif; ?>
      <div><?php the_content(); ?></div>
    </article>
  <?php endwhile; else : ?>
    <p>Aucun contenu.</p>
  <?php endif; ?>
</main>
<?php get_footer(); ?>
