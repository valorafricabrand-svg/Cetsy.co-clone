<?php
/**
 * Cetsy Blog theme setup.
 *
 * @package CetsyBlog
 */

if (! defined('CETSY_BLOG_VERSION')) {
    define('CETSY_BLOG_VERSION', '1.1.6');
}

function cetsy_blog_setup(): void
{
    load_theme_textdomain('cetsy-blog', get_template_directory() . '/languages');

    add_theme_support('automatic-feed-links');
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('html5', ['search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script']);
    add_theme_support('custom-logo', [
        'height'      => 96,
        'width'       => 96,
        'flex-height' => true,
        'flex-width'  => true,
    ]);

    register_nav_menus([
        'primary' => __('Primary menu', 'cetsy-blog'),
        'footer'  => __('Footer menu', 'cetsy-blog'),
    ]);

    add_image_size('cetsy-card', 720, 420, true);
    add_image_size('cetsy-hero', 1200, 675, true);
}
add_action('after_setup_theme', 'cetsy_blog_setup');

function cetsy_blog_assets(): void
{
    wp_enqueue_style(
        'cetsy-blog-fonts',
        'https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Space+Grotesk:wght@500;600;700&display=swap',
        [],
        null
    );

    wp_enqueue_style(
        'cetsy-blog-icons',
        'https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css',
        [],
        '1.11.3'
    );

    wp_enqueue_style(
        'cetsy-blog-style',
        get_stylesheet_uri(),
        ['cetsy-blog-fonts', 'cetsy-blog-icons'],
        CETSY_BLOG_VERSION
    );

    wp_enqueue_style(
        'cetsy-blog-theme',
        get_template_directory_uri() . '/assets/css/theme.css',
        ['cetsy-blog-style'],
        CETSY_BLOG_VERSION
    );

    $root_css = get_stylesheet_directory() . '/style.css';
    if (is_readable($root_css)) {
        wp_add_inline_style('cetsy-blog-style', file_get_contents($root_css)); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
    }

    wp_enqueue_script(
        'cetsy-blog-theme',
        get_template_directory_uri() . '/assets/js/theme.js',
        [],
        CETSY_BLOG_VERSION,
        true
    );
}
add_action('wp_enqueue_scripts', 'cetsy_blog_assets');

function cetsy_blog_main_site_url(string $path = ''): string
{
    $base = apply_filters('cetsy_blog_main_site_url', 'https://cetsy.co');
    return rtrim((string) $base, '/') . '/' . ltrim($path, '/');
}

function cetsy_blog_logo_markup(): string
{
    $logo = cetsy_blog_main_site_url('assets/images/cetsylogmain.png');

    if (has_custom_logo()) {
        $custom_logo_id = (int) get_theme_mod('custom_logo');
        $custom_logo_url = wp_get_attachment_image_url($custom_logo_id, 'full');

        if ($custom_logo_url) {
            $logo = $custom_logo_url;
        }
    }

    return sprintf(
        '<a class="cetsy-brand" href="%s"><img src="%s" alt="%s" loading="eager"><span><span>Marketplace</span><strong>%s</strong></span></a>',
        esc_url(cetsy_blog_main_site_url()),
        esc_url($logo),
        esc_attr(get_bloginfo('name')),
        esc_html(get_bloginfo('name') ?: 'Cetsy')
    );
}

function cetsy_blog_excerpt(int $length = 28): string
{
    return wp_trim_words(get_the_excerpt() ?: wp_strip_all_tags(get_the_content()), $length, '&hellip;');
}

function cetsy_blog_posted_on(): void
{
    printf(
        '<span><i class="bi bi-calendar3"></i> <time itemprop="datePublished" datetime="%s">%s</time></span>',
        esc_attr(get_the_date(DATE_W3C)),
        esc_html(get_the_date())
    );
}

function cetsy_blog_posted_by(): void
{
    printf(
        '<span><i class="bi bi-person"></i> %s</span>',
        esc_html(get_the_author())
    );
}

function cetsy_blog_render_post_card(): void
{
    $category = get_the_category();
    ?>
    <article id="post-<?php the_ID(); ?>" <?php post_class('post-card'); ?> itemscope itemtype="https://schema.org/BlogPosting">
        <a class="post-card__link" href="<?php the_permalink(); ?>" aria-label="<?php echo esc_attr(sprintf(__('Read %s', 'cetsy-blog'), get_the_title())); ?>">
            <div class="post-card__media">
                <?php if (has_post_thumbnail()) : ?>
                    <?php the_post_thumbnail('cetsy-card', ['itemprop' => 'image', 'alt' => the_title_attribute(['echo' => false])]); ?>
                <?php else : ?>
                    <div class="post-card__placeholder"><i class="bi bi-journal-text"></i></div>
                <?php endif; ?>
            </div>

            <div class="post-card__body">
                <div class="entry-meta">
                    <?php cetsy_blog_posted_on(); ?>
                    <meta itemprop="dateModified" content="<?php echo esc_attr(get_the_modified_date(DATE_W3C)); ?>">
                    <span class="screen-reader-text" itemprop="author" itemscope itemtype="https://schema.org/Person">
                        <span itemprop="name"><?php echo esc_html(get_the_author()); ?></span>
                    </span>
                    <?php if (! empty($category)) : ?>
                        <span><i class="bi bi-folder"></i> <span itemprop="articleSection"><?php echo esc_html($category[0]->name); ?></span></span>
                    <?php endif; ?>
                </div>

                <?php the_title('<h3 itemprop="headline">', '</h3>'); ?>
                <p itemprop="description"><?php echo wp_kses_post(cetsy_blog_excerpt()); ?></p>
                <span class="read-more"><?php esc_html_e('Read story', 'cetsy-blog'); ?> <i class="bi bi-arrow-right"></i></span>
            </div>
        </a>
    </article>
    <?php
}

function cetsy_blog_schema_enabled(): bool
{
    $rank_math_active = defined('RANK_MATH_VERSION') || class_exists('RankMath');

    return (bool) apply_filters('cetsy_blog_output_schema', ! $rank_math_active);
}

function cetsy_blog_seo_plugin_active(): bool
{
    return defined('RANK_MATH_VERSION')
        || class_exists('RankMath')
        || defined('WPSEO_VERSION')
        || class_exists('WPSEO_Options');
}

function cetsy_blog_image_url(int $post_id = 0): string
{
    $post_id = $post_id ?: get_the_ID();
    $image = get_the_post_thumbnail_url($post_id, 'full');

    return $image ?: cetsy_blog_main_site_url('assets/images/cetsylogmain.png');
}

function cetsy_blog_output_schema(): void
{
    if (! cetsy_blog_schema_enabled()) {
        return;
    }

    $site_name = get_bloginfo('name') ?: 'Cetsy';
    $logo = cetsy_blog_main_site_url('assets/images/cetsylogmain.png');
    $graph = [
        [
            '@type' => 'Organization',
            '@id' => cetsy_blog_main_site_url() . '#organization',
            'name' => $site_name,
            'url' => cetsy_blog_main_site_url(),
            'logo' => [
                '@type' => 'ImageObject',
                'url' => $logo,
            ],
        ],
        [
            '@type' => 'WebSite',
            '@id' => home_url('/') . '#website',
            'url' => home_url('/'),
            'name' => $site_name,
            'publisher' => [
                '@id' => cetsy_blog_main_site_url() . '#organization',
            ],
            'potentialAction' => [
                '@type' => 'SearchAction',
                'target' => home_url('/?s={search_term_string}'),
                'query-input' => 'required name=search_term_string',
            ],
        ],
    ];

    if (is_singular('post')) {
        $tags = wp_get_post_tags(get_the_ID(), ['fields' => 'names']);
        $categories = wp_get_post_categories(get_the_ID(), ['fields' => 'names']);

        $graph[] = [
            '@type' => 'BlogPosting',
            '@id' => get_permalink() . '#article',
            'mainEntityOfPage' => [
                '@type' => 'WebPage',
                '@id' => get_permalink(),
            ],
            'headline' => wp_strip_all_tags(get_the_title()),
            'description' => wp_strip_all_tags(cetsy_blog_excerpt(32)),
            'image' => [cetsy_blog_image_url()],
            'datePublished' => get_the_date(DATE_W3C),
            'dateModified' => get_the_modified_date(DATE_W3C),
            'wordCount' => str_word_count(wp_strip_all_tags(get_the_content())),
            'keywords' => implode(', ', array_filter(array_merge($categories, $tags))),
            'author' => [
                '@type' => 'Person',
                'name' => get_the_author(),
            ],
            'publisher' => [
                '@id' => cetsy_blog_main_site_url() . '#organization',
            ],
        ];

        $graph[] = cetsy_blog_breadcrumb_schema([
            ['name' => __('Home', 'cetsy-blog'), 'url' => cetsy_blog_main_site_url()],
            ['name' => __('Blog', 'cetsy-blog'), 'url' => home_url('/')],
            ['name' => wp_strip_all_tags(get_the_title()), 'url' => get_permalink()],
        ]);
    } elseif (is_home() || is_front_page()) {
        $posts = get_posts([
            'post_type' => 'post',
            'post_status' => 'publish',
            'numberposts' => 50,
            'orderby' => 'date',
            'order' => 'DESC',
        ]);

        $items = [];
        foreach ($posts as $index => $post) {
            $items[] = [
                '@type' => 'ListItem',
                'position' => $index + 1,
                'url' => get_permalink($post),
                'name' => wp_strip_all_tags(get_the_title($post)),
            ];
        }

        $graph[] = [
            '@type' => 'Blog',
            '@id' => home_url('/') . '#blog',
            'url' => home_url('/'),
            'name' => $site_name . ' Blog',
            'description' => __('Stories, guides, and product updates from Cetsy.', 'cetsy-blog'),
            'publisher' => [
                '@id' => cetsy_blog_main_site_url() . '#organization',
            ],
            'mainEntity' => [
                '@type' => 'ItemList',
                'itemListElement' => $items,
            ],
        ];

        $graph[] = cetsy_blog_breadcrumb_schema([
            ['name' => __('Home', 'cetsy-blog'), 'url' => cetsy_blog_main_site_url()],
            ['name' => __('Blog', 'cetsy-blog'), 'url' => home_url('/')],
        ]);
    } else {
        return;
    }

    echo '<script type="application/ld+json">' . wp_json_encode([
        '@context' => 'https://schema.org',
        '@graph' => $graph,
    ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . '</script>' . "\n";
}
add_action('wp_head', 'cetsy_blog_output_schema', 30);

function cetsy_blog_breadcrumb_schema(array $items): array
{
    $elements = [];

    foreach ($items as $index => $item) {
        $elements[] = [
            '@type' => 'ListItem',
            'position' => $index + 1,
            'name' => wp_strip_all_tags((string) $item['name']),
            'item' => esc_url_raw((string) $item['url']),
        ];
    }

    return [
        '@type' => 'BreadcrumbList',
        'itemListElement' => $elements,
    ];
}

function cetsy_blog_document_meta(): void
{
    if (cetsy_blog_seo_plugin_active()) {
        return;
    }

    $robots = 'index, follow';

    if (is_search() || is_404()) {
        $robots = 'noindex, follow';
    } elseif (is_paged()) {
        $robots = 'noindex, follow';
    }

    if (is_singular()) {
        $canonical = get_permalink();
    } elseif (is_home() || is_front_page()) {
        $canonical = home_url('/');
    } elseif (is_archive()) {
        $canonical = get_pagenum_link(1);
    } else {
        $canonical = home_url(add_query_arg([], $GLOBALS['wp']->request ?? ''));
    }

    echo '<meta name="robots" content="' . esc_attr($robots) . '">' . "\n";
    echo '<link rel="canonical" href="' . esc_url($canonical) . '">' . "\n";

    $title = wp_get_document_title();
    $description = cetsy_blog_meta_description();
    $image = is_singular('post') ? cetsy_blog_image_url() : cetsy_blog_main_site_url('assets/images/cetsylogmain.png');
    $type = is_singular('post') ? 'article' : 'website';

    echo '<meta name="description" content="' . esc_attr($description) . '">' . "\n";
    echo '<meta property="og:site_name" content="' . esc_attr(get_bloginfo('name') ?: 'Cetsy') . '">' . "\n";
    echo '<meta property="og:title" content="' . esc_attr($title) . '">' . "\n";
    echo '<meta property="og:description" content="' . esc_attr($description) . '">' . "\n";
    echo '<meta property="og:type" content="' . esc_attr($type) . '">' . "\n";
    echo '<meta property="og:url" content="' . esc_url($canonical) . '">' . "\n";
    echo '<meta property="og:image" content="' . esc_url($image) . '">' . "\n";
    echo '<meta name="twitter:card" content="summary_large_image">' . "\n";
    echo '<meta name="twitter:title" content="' . esc_attr($title) . '">' . "\n";
    echo '<meta name="twitter:description" content="' . esc_attr($description) . '">' . "\n";
    echo '<meta name="twitter:image" content="' . esc_url($image) . '">' . "\n";
}
add_action('wp_head', 'cetsy_blog_document_meta', 2);

function cetsy_blog_meta_description(): string
{
    if (is_singular('post')) {
        return wp_strip_all_tags(cetsy_blog_excerpt(28));
    }

    if (is_search()) {
        return sprintf(
            /* translators: %s: search query */
            __('Search results for "%s" on the Cetsy blog.', 'cetsy-blog'),
            get_search_query()
        );
    }

    if (is_archive()) {
        return wp_strip_all_tags(get_the_archive_description() ?: get_the_archive_title());
    }

    return __('Stories, guides, maker spotlights, and marketplace updates from Cetsy.', 'cetsy-blog');
}
