<?php
/**
 * Blog posts index template.
 *
 * @package CetsyBlog
 */

get_header();

$cetsy_post_statuses = ['publish'];

if (is_user_logged_in() && current_user_can('read_private_posts')) {
    $cetsy_post_statuses[] = 'private';
}

$cetsy_blog_query = new WP_Query([
    'post_type'           => 'post',
    'post_status'         => $cetsy_post_statuses,
    'posts_per_page'      => -1,
    'ignore_sticky_posts' => false,
    'orderby'             => 'date',
    'order'               => 'DESC',
]);
?>

<main id="primary" class="site-main site-main--blog">
    <section class="blog-hero">
        <div class="blog-hero__content">
            <span class="hero-tag"><?php esc_html_e('Journal', 'cetsy-blog'); ?></span>
            <h1>
                <?php
                $posts_page_title = trim((string) single_post_title('', false));
                echo esc_html($posts_page_title ?: __('Cetsy.co Blog', 'cetsy-blog'));
                ?>
            </h1>
            <p><?php esc_html_e('Stories, guides, and product updates for discovering unique handmade goods and growing a stronger Cetsy shop.', 'cetsy-blog'); ?></p>
            <div class="hero-actions">
                <a class="cta-white" href="<?php echo esc_url(cetsy_blog_main_site_url('listings')); ?>"><?php esc_html_e('Shop handmade', 'cetsy-blog'); ?></a>
                <a class="cta-outline" href="<?php echo esc_url(cetsy_blog_main_site_url('become-seller')); ?>"><?php esc_html_e('Start selling', 'cetsy-blog'); ?></a>
            </div>
        </div>
        <aside class="hero-panel">
            <div class="quick-panel__head">
                <p><?php esc_html_e('Deals & Inspiration', 'cetsy-blog'); ?></p>
                <span><?php esc_html_e('Today', 'cetsy-blog'); ?></span>
            </div>
            <a href="<?php echo esc_url(cetsy_blog_main_site_url('listings?sort=popular')); ?>" class="quick-item">
                <span><strong><?php esc_html_e('Top picks', 'cetsy-blog'); ?></strong><small><?php esc_html_e('Trending products and bundles', 'cetsy-blog'); ?></small></span>
                <em><?php esc_html_e('Explore', 'cetsy-blog'); ?></em>
            </a>
            <a href="<?php echo esc_url(cetsy_blog_main_site_url('listings?type=digital')); ?>" class="quick-item">
                <span><strong><?php esc_html_e('Digital deals', 'cetsy-blog'); ?></strong><small><?php esc_html_e('Templates, e-books, guides', 'cetsy-blog'); ?></small></span>
                <em><?php esc_html_e('Open', 'cetsy-blog'); ?></em>
            </a>
        </aside>
    </section>

    <?php get_template_part('template-parts/category', 'chips'); ?>

    <?php if ($cetsy_blog_query->have_posts()) : ?>
        <?php echo "\n" . '<!-- Cetsy Blog: rendering ' . esc_html((string) $cetsy_blog_query->found_posts) . ' posts -->' . "\n"; ?>
        <div class="section-title-row">
            <p class="section-kicker"><?php esc_html_e('Latest stories', 'cetsy-blog'); ?></p>
            <h2><?php esc_html_e('Featured from the blog', 'cetsy-blog'); ?></h2>
        </div>

        <section class="post-grid" aria-label="<?php esc_attr_e('Blog posts', 'cetsy-blog'); ?>">
            <?php
            while ($cetsy_blog_query->have_posts()) :
                $cetsy_blog_query->the_post();
                cetsy_blog_render_post_card();
            endwhile;
            ?>
        </section>

        <div class="pagination-wrap">
            <span class="posts-count">
                <?php
                printf(
                    esc_html(_n('%s story', '%s stories', (int) $cetsy_blog_query->found_posts, 'cetsy-blog')),
                    esc_html(number_format_i18n((int) $cetsy_blog_query->found_posts))
                );
                ?>
            </span>
        </div>
    <?php else : ?>
        <section class="empty-state empty-state--compact">
            <i class="bi bi-journal-plus"></i>
            <h2><?php esc_html_e('No published stories found', 'cetsy-blog'); ?></h2>
            <p><?php esc_html_e('Publish a WordPress post and it will appear here automatically.', 'cetsy-blog'); ?></p>
        </section>
    <?php endif; ?>
    <?php wp_reset_postdata(); ?>
</main>

<?php
get_footer();
