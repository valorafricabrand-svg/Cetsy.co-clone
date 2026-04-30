<?php
/**
 * Footer template.
 *
 * @package CetsyBlog
 */
?>
    <footer class="site-footer">
        <div class="site-footer__inner">
            <div>
                <p class="section-kicker"><?php esc_html_e('Cetsy Journal', 'cetsy-blog'); ?></p>
                <h2><?php esc_html_e('Stories for handmade sellers and thoughtful buyers.', 'cetsy-blog'); ?></h2>
                <p><?php esc_html_e('Keep up with marketplace updates, buying guides, and creator tips from Cetsy.', 'cetsy-blog'); ?></p>
            </div>
            <div class="site-footer__links">
                <a href="<?php echo esc_url(cetsy_blog_main_site_url('about')); ?>"><?php esc_html_e('About', 'cetsy-blog'); ?></a>
                <a href="<?php echo esc_url(cetsy_blog_main_site_url('contact')); ?>"><?php esc_html_e('Contact', 'cetsy-blog'); ?></a>
                <a href="<?php echo esc_url(cetsy_blog_main_site_url('user-agreement')); ?>"><?php esc_html_e('Policies', 'cetsy-blog'); ?></a>
            </div>
        </div>
    </footer>

    <nav class="mobile-dock" aria-label="<?php esc_attr_e('Mobile navigation', 'cetsy-blog'); ?>">
        <a href="<?php echo esc_url(home_url('/')); ?>" class="dock-link is-active"><i class="bi bi-house"></i><span><?php esc_html_e('Blog', 'cetsy-blog'); ?></span></a>
        <a href="<?php echo esc_url(cetsy_blog_main_site_url('listings')); ?>" class="dock-link"><i class="bi bi-compass"></i><span><?php esc_html_e('Explore', 'cetsy-blog'); ?></span></a>
        <a href="<?php echo esc_url(cetsy_blog_main_site_url('shops')); ?>" class="dock-link"><i class="bi bi-shop"></i><span><?php esc_html_e('Shops', 'cetsy-blog'); ?></span></a>
        <a href="<?php echo esc_url(cetsy_blog_main_site_url('become-seller')); ?>" class="dock-link"><i class="bi bi-bag-plus"></i><span><?php esc_html_e('Sell', 'cetsy-blog'); ?></span></a>
    </nav>
</div>

<?php wp_footer(); ?>
</body>
</html>

