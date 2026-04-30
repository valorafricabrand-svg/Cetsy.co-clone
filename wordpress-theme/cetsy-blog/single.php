<?php
/**
 * Single post template.
 *
 * @package CetsyBlog
 */

get_header();
?>

<main id="primary" class="site-main site-main--single">
    <?php
    while (have_posts()) :
        the_post();
        ?>
        <?php echo "\n" . '<!-- Cetsy Blog: rendering single post ' . esc_html((string) get_the_ID()) . ' -->' . "\n"; ?>

        <article id="post-<?php the_ID(); ?>" <?php post_class('single-post-layout'); ?> itemscope itemtype="https://schema.org/BlogPosting">
            <header class="single-hero">
                <nav class="breadcrumb" aria-label="<?php esc_attr_e('Breadcrumb', 'cetsy-blog'); ?>">
                    <a href="<?php echo esc_url(cetsy_blog_main_site_url()); ?>"><?php esc_html_e('Home', 'cetsy-blog'); ?></a>
                    <span>/</span>
                    <a href="<?php echo esc_url(home_url('/')); ?>"><?php esc_html_e('Blog', 'cetsy-blog'); ?></a>
                    <span>/</span>
                    <span><?php the_title(); ?></span>
                </nav>

                <div class="single-hero__grid">
                    <div>
                        <?php $category = get_the_category(); ?>
                        <?php if (! empty($category)) : ?>
                            <a class="category-pill" href="<?php echo esc_url(get_category_link($category[0])); ?>">
                                <?php echo esc_html($category[0]->name); ?>
                            </a>
                        <?php endif; ?>

                        <?php the_title('<h1 itemprop="headline">', '</h1>'); ?>
                        <p itemprop="description"><?php echo wp_kses_post(cetsy_blog_excerpt(32)); ?></p>

                        <div class="entry-meta entry-meta--light">
                            <?php cetsy_blog_posted_by(); ?>
                            <?php cetsy_blog_posted_on(); ?>
                            <meta itemprop="dateModified" content="<?php echo esc_attr(get_the_modified_date(DATE_W3C)); ?>">
                            <span class="screen-reader-text" itemprop="author" itemscope itemtype="https://schema.org/Person">
                                <span itemprop="name"><?php echo esc_html(get_the_author()); ?></span>
                            </span>
                        </div>
                    </div>

                    <div class="single-hero__image">
                        <?php if (has_post_thumbnail()) : ?>
                            <?php the_post_thumbnail('cetsy-hero', ['itemprop' => 'image', 'alt' => the_title_attribute(['echo' => false])]); ?>
                        <?php else : ?>
                            <div class="single-hero__placeholder"><i class="bi bi-stars"></i></div>
                        <?php endif; ?>
                    </div>
                </div>
            </header>

            <div class="single-content-grid single-content-grid--article">
                <div class="content-panel">
                    <div class="entry-content" itemprop="articleBody">
                        <?php
                        the_content();
                        wp_link_pages([
                            'before' => '<div class="page-links">' . esc_html__('Pages:', 'cetsy-blog'),
                            'after'  => '</div>',
                        ]);
                        ?>
                    </div>

                    <footer class="article-taxonomy" aria-label="<?php esc_attr_e('Article taxonomy', 'cetsy-blog'); ?>">
                        <div>
                            <span><?php esc_html_e('Updated', 'cetsy-blog'); ?></span>
                            <time datetime="<?php echo esc_attr(get_the_modified_date(DATE_W3C)); ?>"><?php echo esc_html(get_the_modified_date()); ?></time>
                        </div>

                        <?php $categories = get_the_category_list(', '); ?>
                        <?php if ($categories) : ?>
                            <div>
                                <span><?php esc_html_e('Categories', 'cetsy-blog'); ?></span>
                                <p><?php echo wp_kses_post($categories); ?></p>
                            </div>
                        <?php endif; ?>

                        <?php $tags = get_the_tag_list('', ', '); ?>
                        <?php if ($tags) : ?>
                            <div>
                                <span><?php esc_html_e('Tags', 'cetsy-blog'); ?></span>
                                <p><?php echo wp_kses_post($tags); ?></p>
                            </div>
                        <?php endif; ?>
                    </footer>
                </div>
            </div>
        </article>
        <?php

        the_post_navigation([
            'prev_text' => '<span>' . esc_html__('Previous', 'cetsy-blog') . '</span><strong>%title</strong>',
            'next_text' => '<span>' . esc_html__('Next', 'cetsy-blog') . '</span><strong>%title</strong>',
        ]);

        if (comments_open() || get_comments_number()) {
            comments_template();
        }
    endwhile;
    ?>
</main>

<?php
get_footer();
