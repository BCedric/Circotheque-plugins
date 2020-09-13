<?php

class New_Post_Email_Admin
{
    public function __construct()
    {
        add_action('admin_menu', array($this, 'add_admin_menu'), 20);
        add_filter('wp_mail_from', array($this, 'wpb_sender_email'));
        add_filter('wp_mail_from_name', array($this, 'wpb_sender_name'));
    }

    public function add_admin_menu()
    {
        add_menu_page('Articles Emails', 'Articles Emails', 'manage_options', 'new_post_emails', array($this, 'menu_html'));
    }

    function wpb_sender_email($original_email_address)
    {
        return 'lachainedescirques@gmail.com';
    }

    function wpb_sender_name($original_email_from)
    {
        return 'La chaine des cirques';
    }

    public function menu_html()
    {
        echo '<h1>' . get_admin_page_title() . '</h1>';

        if (key_exists('submit', $_POST)) {
            $post = get_post($_POST['post_id']);
            $groups_id = Groups_Post_Access::get_read_group_ids($post->ID);
            $members = [];
            foreach ($groups_id as $id) {
                $group_members = $this->group_members($id);
                $members = array_merge($members, $group_members);
            }
            $headers = array('Content-Type: text/html; charset=UTF-8');
            $email_content = $this->get_email_content($post);

            foreach ($members as $member) {
                wp_mail($member->data->user_email, "[Circothèque] Nouvel Article", $email_content, $headers);
            }
?>
            <span>Les emails ont bien été envoyés</span>
        <?php

        } else {

            $posts = get_posts(['numberposts' => -1]);
        ?>
            <form method="POST" action="">
                <label for="post_id"></label>
                <select name="post_id" id="post_id">
                    <?php
                    foreach ($posts as $post) {
                    ?>
                        <option value="<?php echo $post->ID ?>"><?php echo $post->post_title; ?></option>
                    <?php
                    }
                    ?>

                </select>
                <?php submit_button('Envoyer'); ?>
            </form>
<?php
        }
    }

    private function get_email_content($post)
    {
        $email_content =
            file_get_contents(get_site_url() . '/wp-content/plugins/new-post-mail/new-post-mail-template.php');
        $email_content = str_replace('[article_link]', $post->guid, $email_content);
        return $email_content;
    }

    private  function group_members($group_id)
    {

        global $wpdb;
        $querystr = "SELECT * FROM wp_groups_user_group WHERE group_id = {$group_id}";
        $user_groups = $wpdb->get_results($querystr, OBJECT);
        $users = [];
        foreach ($user_groups as $user_group) {
            array_push($users, get_user_by('id', $user_group->user_id));
        }


        return $users;
    }
}
