<?php

/**
 * Plugin Name: New post email plugin
 */


require_once('new-post-mail-admin.php');

class New_Post_Email
{
    public function __construct()
    {
        new New_Post_Email_Admin();
    }
}

new New_Post_Email();
