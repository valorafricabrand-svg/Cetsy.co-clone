<?php
/**
 * Archive template.
 *
 * @package CetsyBlog
 */

get_header();
?>

<main id="primary" class="site-main site-main--standard">
    <section class="page-hero">
        <p class="section-kicker"><?php esc_html_e('Browse the journal', 'cetsy-blog'); ?></p>
        <?php the_archive_title('<h1>', '</h1>'); ?>
        <?php the_archive_description('<p>', '</p>'); ?>
    </section>

    <?php get_template_part('template-parts/category', 'chips'); ?>

    <?php if (have_posts()) : ?>
        <div class="section-title-row section-title-row--compact">
            <p class="section-kicker"><?php esc_html_e('Posts', 'cetsy-blog'); ?></p>
        </div>
        <section class="post-grid">
            <?php
            while (have_posts()) :
                the_post();
                cetsy_blog_render_post_card();
            endwhile;
            ?>
        </section>
        <div class="pagination-wrap"><?php the_posts_pagination(); ?></div>
    <?php else : ?>
        <?php get_template_part('template-parts/content', 'none'); ?>
    <?php endif; ?>
</main>

<?php
get_footer();
