<?php

/**
 * Plugin Name: Training plugin
 */

require_once('training-plugin-admin.php');
require_once('training-plugin-form.php');
require_once('training-plugin-DB.php');
require_once('training-plugin-widget.php');
require_once('training-plugin-utils.php');
require_once('training-plugin-user-meta-manager.php');


class Training_Plugin
{
    private $db;

    public function __construct()
    {
        register_activation_hook(__FILE__,  array($this, 'install'));
        register_uninstall_hook(__FILE__,  array($this, 'uninstall'));
        add_shortcode('user_training', array($this, 'user_training'));
        add_action('admin_enqueue_scripts', array($this, 'admin_style'));

        new Training_Plugin_Admin();
        new Training_Plugin_Form();
        new Training_plugin_widget();
        $this->db = new Training_PLugin_DB();
    }

    public function install()
    {
        $this->db->create_sessions_table();
        $this->db->create_trainings_table();
    }

    public function uninstall()
    {
        $this->db->drop_trainings_table();
        $this->db->drop_sessions_table();
    }

    public function admin_style()
    {
        wp_enqueue_style('training-plugin', get_site_url() . '/wp-content/plugins/training-plugin/admin-style.css');
    }



    public function user_training()
    {
        date_default_timezone_set("Europe/Paris");
        $userId = get_current_user_id();
        $user_meta_manager = new Training_Plugin_User_Meta_Manager($userId);

        if (key_exists('delete_training', $_POST)) {
            $this->db->delete_training($_POST['delete_training']);
            if ($_POST['add_training_abandonment'] === '1') {
                $user_meta_manager->user_abandonment();
            }
            wp_redirect(get_permalink());
        }

        $user_trainings = array_filter($this->db->get_user_trainings($userId), function ($training) {
            return new DateTime("$training->date $training->endTime") >= new DateTime();
        });

        ob_start();
?>
        <div>
            <?php if (Training_plugin_utils::is_user_enable_to_use_training()) {
            ?>
                <h3 class="training-title">Vous êtes inscrit pour les séances : </h3>
                <div class="training-summary">
                    <?php
                    if (!empty($user_trainings)) {
                    ?>

                        <ul>
                            <?php
                            foreach ($user_trainings as $training) {
                                $startDatetime = new DateTime("{$training->date} {$training->startTime}");
                                $endDatetime = new DateTime("{$training->date} {$training->endTime}");
                                $isMore24hoursBefore = $user_meta_manager->is_abandonment_suspandable($startDatetime);

                            ?>

                                <li><span><?php echo "{$training->type}: Le {$startDatetime->format('d/m')} de {$startDatetime->format('G\hi')} à {$endDatetime->format('G\hi')}" ?>
                                        <?php if ($training->is_visio != null) {
                                            $visioLabel = $training->is_visio ? 'en visio' : 'sur place';
                                            echo " ({$visioLabel})";
                                        } ?>
                                    </span>
                                    <form method="POST" action="" onsubmit="return getConfirm(<?php echo $isMore24hoursBefore ?>)">
                                        <input type="hidden" name="delete_training" value="<?php echo $training->id ?>" />
                                        <input type="hidden" name="add_training_abandonment" value="<?php echo $isMore24hoursBefore ?>" />
                                        <button type="submit" class="icon-button"><span class="dashicons dashicons-trash"></span></button>
                                    </form>
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
                </div>
                <script type="text/javascript">
                    function getConfirm(isMore24hoursBefore) {
                        const trainingAbandonmentNumber = <?php echo $user_meta_manager->get_training_abandonment_number(); ?>;
                        if (isMore24hoursBefore) {
                            const message = "Attention vous voulez vous désinscrire d'un cours qui est dans moins de 24h. Au bout de trois désistements dans ces conditions, vous ne pourrez plus accéder aux séances de training pendant une semaine"
                            if (trainingAbandonmentNumber > 0) {
                                return confirm(`${message}. Vous vous êtes déjà désisté ${trainingAbandonmentNumber} fois au dernier moment.`)
                            }
                            return confirm(message)
                        }
                        return confirm('Confirmer la désinscription')
                    }
                </script>
            <?php
            } else {
            ?>
                <div class="information-container">
                    <span>Vous n'êtes pas autorisé à utiliser ce module. </span>
                </div>

            <?php

            }
            ?>
        </div> <?php
                return ob_get_clean();
            }
        }

        new Training_Plugin();
        // register_deactivation_hook(__FILE__,  'uninstall');
