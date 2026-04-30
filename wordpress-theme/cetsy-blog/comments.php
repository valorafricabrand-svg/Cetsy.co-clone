<?php
/**
 * Comments template.
 *
 * @package CetsyBlog
 */

if (post_password_required()) {
    return;
}
?>

<section id="comments" class="comments-area content-panel">
    <?php if (have_comments()) : ?>
        <h2><?php comments_number(__('No comments yet', 'cetsy-blog'), __('One comment', 'cetsy-blog'), __('% comments', 'cetsy-blog')); ?></h2>
        <ol class="comment-list">
            <?php wp_list_comments(['style' => 'ol', 'short_ping' => true]); ?>
        </ol>
        <?php the_comments_navigation(); ?>
    <?php endif; ?>

    <?php comment_form(); ?>
</section>

