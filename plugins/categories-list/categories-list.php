<?php

/**
 * Plugin Name: Categories list
 */

function categories_list()
{
    $categories = get_categories();
    $categories_image_url = [];
    $categories_links = [];
    foreach ($categories as
        $category) {
        $categories_image_url[$category->term_id] = z_taxonomy_image_url($category->term_id);
        $categories_links[$category->term_id] = get_term_link($category->term_id);
    }
    ob_start();
?>
    <div class="categories-list-content">
        <h2>Catégories</h2>
        <div class="categories-list">
            <?php
            foreach ($categories as $key => $category) {
                if ($category->count > 0) {
            ?>
                    <a href="<?php echo $categories_links[$category->term_id] ?>" class="category">
                        <h3 class="title"><?php echo $category->name ?></h3>
                        <?php
                        if ($categories_image_url[$category->term_id]) {
                        ?>

                            <div class="img">
                                <img src="<?php echo $categories_image_url[$category->term_id] ?>" />
                            </div>
                        <?php
                        }
                        ?>
                        <div class="description">
                            <p><?php echo $category->description ?></p>
                        </div>
                    </a>

            <?php
                }
            }
            ?>
        </div>
    </div>
<?php

    return ob_get_clean();
}

add_action('wp_enqueue_scripts', 'callback_for_setting_up_scripts');
function callback_for_setting_up_scripts()
{
    wp_register_style('categories_list', get_site_url() . '/wp-content/plugins/categories-list/style.css');
    wp_enqueue_style('categories_list');
}

add_shortcode('categories_list', 'categories_list');
