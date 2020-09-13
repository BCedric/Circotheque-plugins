<?php

/**
 * A Simple Category Template
 */

get_header(); ?>

<section id="primary" class="site-content">
    <div id="category-content" role="main">

        <header class="archive-header">
            <h1 class="archive-title"><?php single_cat_title(''); ?></h1>


            <?php
            // Display optional category description
            if (category_description()) : ?>
                <div class="archive-meta"><?php echo category_description(); ?></div>
            <?php endif; ?>
        </header>
        <?php
        // Check if there are any posts to display
        if (have_posts()) : ?>


            <?php

            // The Loop
            while (have_posts()) : the_post(); ?>
                <a class="category-post-link" href="<?php the_permalink() ?>" rel="bookmark">
                    <div class="category-post-container">

                        <div class="category-post">

                            <h2><?php the_title(); ?></h2>
                            <small><?php the_time('j F Y') ?></small>
                            <?php
                            if (has_post_thumbnail($post->ID)) {
                            ?>
                                <div class="post-img-container">
                                    <img src="<?php echo the_post_thumbnail_url() ?>" />
                                </div>
                            <?php
                            }
                            ?>

                            <div class="entry">
                                <?php the_excerpt(); ?>

                                <p class="postmetadata">
                            </div>
                        </div>
                    </div>
                </a>

            <?php endwhile;

        else : ?>
            <div class="information-container">
                <p>Il n'y a aucun article ici</p>
            </div>


        <?php endif; ?>
    </div>
</section>


<?php get_sidebar(); ?>
<?php get_footer(); ?>