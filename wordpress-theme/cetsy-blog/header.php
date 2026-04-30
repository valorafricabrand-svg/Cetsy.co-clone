<?php
/**
 * Header template.
 *
 * @package CetsyBlog
 */
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php wp_head(); ?>
    <style id="cetsy-blog-critical-css">
        .site-header{position:relative!important;z-index:50!important;padding-bottom:12px!important}.feature-row-wrap{z-index:40!important;padding-top:4px!important;padding-bottom:26px!important}.site-main,.site-footer{z-index:1!important}.site-header img,.cetsy-brand img{width:40px!important;height:40px!important;max-width:40px!important;max-height:40px!important;object-fit:contain!important;border-radius:12px!important}.site-header__inner{display:flex;align-items:center;justify-content:space-between;gap:12px;max-width:1152px;margin:0 auto;padding:12px;border:1px solid rgba(15,23,42,.1);border-radius:18px;background:rgba(255,255,255,.94);box-shadow:0 16px 36px rgba(15,23,42,.08)}.cetsy-brand{display:inline-flex!important;align-items:center;gap:10px}.cetsy-brand span span{display:block;color:#047857;font:700 11px/1.2 Manrope,Arial,sans-serif;letter-spacing:.15em;text-transform:uppercase}.cetsy-brand strong{display:block;color:#0f172a;font:800 17px/1.2 Manrope,Arial,sans-serif}.landing-shell{min-height:100vh;background:radial-gradient(circle at 2% 0%,#ecfdf5 0%,#f8fafc 38%,#fff 100%);font-family:Manrope,Arial,sans-serif;color:#0f172a}.blog-hero{display:grid;grid-template-columns:minmax(0,1.05fr) minmax(18rem,.95fr);gap:16px;max-width:1152px;margin:0 auto;border-radius:24px;padding:24px;background:radial-gradient(130% 170% at 0% 0%,#fff 0,#ef4444 36%,#e60012 74%);box-shadow:0 28px 50px rgba(15,23,42,.2);color:#fff}.site-main{max-width:1152px;margin:0 auto;padding:0 16px 32px}.site-main--standard,.site-main--single{padding-top:20px}.feature-row{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:9px}.feature-pill{border:1px solid rgba(25,135,84,.22);border-radius:15px;background:rgba(22,163,74,.06);color:#0f5132;font-weight:800;text-align:center;padding:11px 13px}.section-title-row{margin:16px 0 12px}.post-grid{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:16px}.single-hero{margin-top:0}.single-content-grid{clear:both}.single-content-grid--article,.comments-area,.post-navigation{max-width:928px;margin-left:auto!important;margin-right:auto!important}.post-navigation,.comments-area{margin-top:16px!important}.site-footer{margin-top:0!important}@media(max-width:980px) and (min-width:761px){.post-grid{grid-template-columns:repeat(2,minmax(0,1fr))}.blog-hero,.single-hero__grid{grid-template-columns:1fr}.hero-panel{min-height:auto}}@media(max-width:760px){.site-header__inner,.blog-hero,.post-grid{grid-template-columns:1fr;display:grid}.site-header__inner{gap:10px}.site-actions{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));width:100%}.site-search{max-width:none;min-width:0;width:100%;order:3}.feature-row{grid-template-columns:1fr}.feature-row-wrap{padding:4px 12px 16px!important}.single-content-grid--article,.comments-area,.post-navigation{max-width:100%!important}.blog-hero h1,.single-hero h1,.page-hero h1{font-size:clamp(2rem,14vw,3.2rem)}}@media(max-width:420px){.quick-item{align-items:flex-start;flex-direction:column}.quick-item em{align-self:flex-start}}
    </style>
    <?php
    $cetsy_inline_css_path = get_template_directory() . '/assets/css/theme.css';
    if (is_readable($cetsy_inline_css_path)) :
        ?>
        <style id="cetsy-blog-inline-css">
            <?php echo file_get_contents($cetsy_inline_css_path); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped, WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents ?>
        </style>
    <?php endif; ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<a class="skip-link" href="#primary"><?php esc_html_e('Skip to content', 'cetsy-blog'); ?></a>

<div class="landing-shell">
    <div class="landing-orb landing-orb-one" aria-hidden="true"></div>
    <div class="landing-orb landing-orb-two" aria-hidden="true"></div>

    <header class="site-header">
        <div class="site-header__inner">
            <div class="site-header__brand">
                <?php echo cetsy_blog_logo_markup(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
            </div>

            <form role="search" method="get" class="search-shell site-search" action="<?php echo esc_url(home_url('/')); ?>">
                <label class="screen-reader-text" for="cetsy-search"><?php esc_html_e('Search blog', 'cetsy-blog'); ?></label>
                <i class="bi bi-search"></i>
                <input id="cetsy-search" type="search" name="s" value="<?php echo esc_attr(get_search_query()); ?>" placeholder="<?php esc_attr_e('Search stories', 'cetsy-blog'); ?>">
                <button type="submit"><?php esc_html_e('Search', 'cetsy-blog'); ?></button>
            </form>

            <nav class="primary-nav" aria-label="<?php esc_attr_e('Primary navigation', 'cetsy-blog'); ?>">
                <?php
                wp_nav_menu([
                    'theme_location' => 'primary',
                    'container'      => false,
                    'fallback_cb'    => false,
                    'menu_class'     => 'primary-nav__menu',
                    'depth'          => 1,
                ]);
                ?>
                <a href="<?php echo esc_url(cetsy_blog_main_site_url('listings')); ?>"><?php esc_html_e('Explore', 'cetsy-blog'); ?></a>
                <a href="<?php echo esc_url(cetsy_blog_main_site_url('shops')); ?>"><?php esc_html_e('Shops', 'cetsy-blog'); ?></a>
                <a href="<?php echo esc_url(cetsy_blog_main_site_url('become-seller')); ?>"><?php esc_html_e('Sell', 'cetsy-blog'); ?></a>
            </nav>

            <div class="site-actions">
                <a class="nav-btn" href="<?php echo esc_url(cetsy_blog_main_site_url('login')); ?>"><?php esc_html_e('Login', 'cetsy-blog'); ?></a>
                <a class="nav-btn nav-btn-primary" href="<?php echo esc_url(cetsy_blog_main_site_url('register')); ?>"><?php esc_html_e('Create account', 'cetsy-blog'); ?></a>
            </div>
        </div>
    </header>

    <section class="feature-row-wrap" aria-label="<?php esc_attr_e('Cetsy marketplace highlights', 'cetsy-blog'); ?>">
        <div class="feature-row">
            <a href="<?php echo esc_url(cetsy_blog_main_site_url('user-agreement#privacy')); ?>" class="feature-pill"><?php esc_html_e('Buyer/Seller Protection', 'cetsy-blog'); ?></a>
            <a href="<?php echo esc_url(cetsy_blog_main_site_url('user-agreement#buyer-tips')); ?>" class="feature-pill"><?php esc_html_e('Global Shipping', 'cetsy-blog'); ?></a>
            <a href="<?php echo esc_url(cetsy_blog_main_site_url('listings?sort=popular')); ?>" class="feature-pill"><?php esc_html_e('Curated Trending Picks Daily', 'cetsy-blog'); ?></a>
        </div>
    </section>
