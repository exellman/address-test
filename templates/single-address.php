<?php get_header(); ?>

<main>
    <article>
        <h1><?php the_title(); ?></h1>
        <div><?php the_content(); ?></div>
        <p><strong><?php esc_html_e('Category:', 'textdomain'); ?></strong> <?php the_terms(get_the_ID(), 'address_category', '', ', '); ?></p>
    </article>

</main>

<?php get_footer(); ?>
