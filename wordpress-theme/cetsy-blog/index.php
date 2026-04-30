<?php
/**
 * Fallback template.
 *
 * @package CetsyBlog
 */

get_header();
?>

<main id="primary" class="site-main site-main--standard">
    <?php if (have_posts()) : ?>
        <?php if (! is_singular()) : ?>
            <section class="page-hero">
                <p class="section-kicker"><?php esc_html_e('Cetsy Journal', 'cetsy-blog'); ?></p>
                <h1><?php echo esc_html(get_bloginfo('name') ?: __('Blog', 'cetsy-blog')); ?></h1>
            </section>
        <?php endif; ?>

        <section class="<?php echo is_singular() ? 'standard-single' : 'post-grid'; ?>">
            <?php
            while (have_posts()) :
                the_post();

                if (is_singular()) {
                    get_template_part('template-parts/content', 'single');
                } else {
                    cetsy_blog_render_post_card();
                }
            endwhile;
            ?>
        </section>

        <?php if (! is_singular()) : ?>
            <div class="pagination-wrap"><?php the_posts_pagination(); ?></div>
        <?php endif; ?>
    <?php else : ?>
        <?php get_template_part('template-parts/content', 'none'); ?>
    <?php endif; ?>
</main>

<?php
get_footer();
