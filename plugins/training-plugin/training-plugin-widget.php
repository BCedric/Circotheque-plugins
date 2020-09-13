<?php

require_once('training-plugin-DB.php');
require_once('training-plugin-utils.php');

class Training_plugin_widget extends WP_Widget
{
    private $db;

    public function __construct()
    {
        $this->db = new Training_PLugin_DB();
        parent::__construct(
            // widget ID
            'Training_plugin_widget',
            // widget name
            __('Training widget', ' hstngr_widget_domain'),
            // widget description
            array('description' => __('Widget d\'affichage des séances de training', 'training_plugin_widget_domain'),)
        );

        add_action('widgets_init', function () {
            register_widget('Training_plugin_widget');
        });
    }



    public function get_user_trainings($userId)
    {
        global $wpdb;
        return $wpdb->get_results("SELECT * FROM {$wpdb->prefix}training_plugin INNER JOIN {$wpdb->prefix}training_sessions ON {$wpdb->prefix}training_plugin.session_id = {$wpdb->prefix}training_sessions.id  WHERE user_id = {$userId} ORDER BY date");
    }



    public function widget($args, $instance)
    {
        if (Training_plugin_utils::is_user_enable_to_use_training()) {

            $user = wp_get_current_user();
            $userId = $user->data->ID;
            $user_trainings = array_filter($this->db->get_user_trainings($userId), function ($training) {
                return new DateTime($training->date) > new DateTime();
            });
?>
            <div class="training-widget">
                <h3>Vous êtes inscrit pour les séances de training : </h3>
                <div class="training-widget-sessions">
                    <?php
                    if (!empty($user_trainings)) {
                    ?>

                        <ul>
                            <?php
                            foreach ($user_trainings as $key => $training) {
                                $startDatetime = new DateTime("{$training->date} {$training->startTime}");
                            ?>

                                <li>
                                    <span><?php echo "Le {$startDatetime->format('d/m')} à {$startDatetime->format('G\hi')}" ?></span>
                                </li>
                            <?php } ?>
                        </ul>
                    <?php } else { ?>
                        <div class="information-container">
                            <span>Vous n'êtes inscrit pour aucune séance. </span>
                        </div>
                    <?php
                    }

                    ?>
                    <div class="training-link-content">
                        <a href="<?php echo site_url() ?>/index.php/training">Détails | Inscriptions</a>
                    </div>
                </div>
            </div>

<?php
        }
    }
}
