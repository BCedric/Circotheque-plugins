<?php

/**
 * Plugin Name: Latest Forum Topics
 */

function last_forum_topics()
{



    $unread_posts = get_unread_posts();
    ob_start();
?>

    <div class="unread-foro-posts">
        <h2>Nouvelles sur le forum</h2>
        <?php
        foreach ($unread_posts as $key => $post) {
        ?>
            <a class="unread-post" href="<?php echo $post['topic']['url'] ?>">

                <div>
                    <span><?php echo ucfirst($post['topic']['title']) ?></span>
                </div>
                <div>
                    <span><?php echo $post['is_first_post'] ?  "Créé par : " :  "Modifié par : " ?> <?php echo $post['user']->display_name ?></span>
                </div>
                <div>
                    <span>Le : <?php echo date_i18n('j F Y', strtotime($post['modified'])) ?></span>
                </div>
            </a>
        <?php
        }
        ?>
    </div>
<?php
    // var_dump(wpforo_post(2));
    // var_dump(WPF()->post->get_posts());
    return ob_get_clean();
}

function get_unread_posts()
{
    $posts_args = array(
        'forumids'        => 4,
    );

    $unread_posts = WPF()->post->get_unread_posts($posts_args, count(WPF()->post->get_posts()));
    $topicsName = array_map(function ($topic) use ($unread_posts) {
        return ['title' => $topic['title'], 'post_number' => count(array_filter($unread_posts, function ($post) use ($topic) {
            return $post['topicid'] === $topic['topicid'];
        }))];
    }, WPF()->topic->get_topics($posts_args));

    $post_infos = array_map(function ($post) {
        $topic = WPF()->topic->get_topic($post['topicid']);
        $topic['url'] = WPF()->topic->get_topic_url($topic['topicid']);
        return [
            'user' => get_user_by('id', $post['userid']),
            'id' => $post['id'],
            'modified' => $post['modified'],
            'topic' => $topic,
            'is_first_post' => $post['is_first_post']
        ];
    }, $unread_posts);

    return $post_infos;
}

add_shortcode('last_forum_topics', 'last_forum_topics');
