<?php

/**
 * Plugin Name: Custom Latest Posts
 */

function shapeSpace_include_custom_jquery()
{

    wp_deregister_script('jquery');
    wp_enqueue_script('jquery', 'https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js', array(), null, true);
}
add_action('wp_enqueue_scripts', 'shapeSpace_include_custom_jquery');

function latest_posts($atts)
{
    $params = shortcode_atts(array(
        'nb_posts' => 3,
    ), $atts);
    // $latestPosts = wp_get_recent_posts(['numberposts' => $params['nb_posts']]);
    $latestPosts = [];
    $posts = get_posts();
    foreach ($posts as $key => $post) {
        if (Groups_Post_Access::user_can_read_post($post->ID)) {
            array_push($latestPosts, $post);
        }
        if (count($latestPosts) === (int) $params['nb_posts']) {
            break;
        }
    }
    ob_start();
?>
    <div class="latest-posts-content">
        <h2>Derniers articles publiés</h2>
        <div class="latest-posts">
            <?php
            foreach ($latestPosts as $key => $post) {
            ?>
                <a class="latest-post" href="<?php echo get_post_permalink($post->ID) ?>">
                    <div>
                        <h3 class="title"><?php echo $post->post_title ?></h3>
                        <?php
                        if (has_post_thumbnail($post->ID)) {
                        ?>
                            <img src="<?php echo get_the_post_thumbnail_url($post) ?>" />
                        <?php
                        }
                        ?>
                        <p class="description">
                            <?php echo get_the_excerpt($post->ID) ?>
                        </p>
                    </div>

                </a>

            <?php
            }
            ?>
        </div>
    </div>
<?php
    return ob_get_clean();
}

add_action('wp_enqueue_scripts', 'add_latest_posts_styles');
function add_latest_posts_styles()
{
    wp_register_style('latest_posts', get_site_url() . '/wp-content/plugins/custom-latest-posts/style.css');
    wp_enqueue_style('latest_posts');
}


add_shortcode('latest_posts', 'latest_posts');
