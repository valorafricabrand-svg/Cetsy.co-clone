<?php
/**
 * Page template.
 *
 * @package CetsyBlog
 */

get_header();
?>

<main id="primary" class="site-main site-main--standard">
    <?php
    while (have_posts()) :
        the_post();
        ?>
        <article id="post-<?php the_ID(); ?>" <?php post_class('content-panel'); ?>>
            <header class="entry-header">
                <p class="section-kicker"><?php esc_html_e('Cetsy', 'cetsy-blog'); ?></p>
                <?php the_title('<h1>', '</h1>'); ?>
            </header>
            <div class="entry-content">
                <?php the_content(); ?>
            </div>
        </article>
        <?php
    endwhile;
    ?>
</main>

<?php
get_footer();
