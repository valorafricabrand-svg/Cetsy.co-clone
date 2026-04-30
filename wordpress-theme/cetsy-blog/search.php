<?php
/**
 * Search template.
 *
 * @package CetsyBlog
 */

get_header();
?>

<main id="primary" class="site-main site-main--standard">
    <section class="page-hero">
        <p class="section-kicker"><?php esc_html_e('Search results', 'cetsy-blog'); ?></p>
        <h1><?php printf(esc_html__('Results for "%s"', 'cetsy-blog'), esc_html(get_search_query())); ?></h1>
    </section>

    <?php if (have_posts()) : ?>
        <div class="section-title-row section-title-row--compact">
            <p class="section-kicker"><?php esc_html_e('Matching stories', 'cetsy-blog'); ?></p>
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
