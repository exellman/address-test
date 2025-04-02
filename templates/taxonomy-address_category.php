<?php get_header(); ?>

<main>
    <h1><?php single_term_title(); ?></h1>

    <?php if (have_posts()) : while (have_posts()) : the_post(); ?>
        <article>
            <h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
            <div><?php the_excerpt(); ?></div>
        </article>
    <?php endwhile; else : ?>
        <p><?php esc_html_e('No addresses found in this category.', 'textdomain'); ?></p>
    <?php endif; ?>

    <?php the_posts_pagination(); ?>
</main>

<?php get_footer(); ?>
