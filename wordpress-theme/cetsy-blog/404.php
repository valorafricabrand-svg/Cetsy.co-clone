<?php
/**
 * 404 template.
 *
 * @package CetsyBlog
 */

get_header();
?>

<main id="primary" class="site-main">
    <section class="empty-state">
        <i class="bi bi-compass"></i>
        <h1><?php esc_html_e('Page not found', 'cetsy-blog'); ?></h1>
        <p><?php esc_html_e('The story you are looking for may have moved. Try searching the journal.', 'cetsy-blog'); ?></p>
        <?php get_search_form(); ?>
    </section>
</main>

<?php
get_footer();

