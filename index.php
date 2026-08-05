<?php get_header(); ?>
<main class="site-content">
  <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
    <article <?php post_class(); ?>>
      <h2><?php the_title(); ?></h2>
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
