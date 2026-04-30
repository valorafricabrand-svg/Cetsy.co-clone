<?php
/**
 * Category chips.
 *
 * @package CetsyBlog
 */

$categories = get_categories([
    'hide_empty' => true,
    'number'     => 8,
]);

if (empty($categories)) {
    return;
}
?>

<section class="category-strip" aria-label="<?php esc_attr_e('Blog topics', 'cetsy-blog'); ?>">
    <span><?php esc_html_e('Browse by topic:', 'cetsy-blog'); ?></span>
    <a class="<?php echo is_home() ? 'is-active' : ''; ?>" href="<?php echo esc_url(home_url('/')); ?>"><?php esc_html_e('All posts', 'cetsy-blog'); ?></a>
    <?php foreach ($categories as $category) : ?>
        <a class="<?php echo is_category($category->term_id) ? 'is-active' : ''; ?>" href="<?php echo esc_url(get_category_link($category)); ?>">
            <?php echo esc_html($category->name); ?>
        </a>
    <?php endforeach; ?>
</section>

