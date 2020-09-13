<?php

/**
 * Plugin Name: Redirect user logged shortcode
 */

function redirect_user_logged($atts)
{
    $args = shortcode_atts(array('to' => '/'), $atts);

    if (is_user_logged_in() != null) {
        wp_redirect($args['to']);
    }
}

add_shortcode('redirect_user_logged', 'redirect_user_logged');
