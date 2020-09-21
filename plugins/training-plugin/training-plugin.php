<?php

/**
 * Plugin Name: Training plugin
 */

require_once('training-plugin-admin.php');
require_once('training-plugin-form.php');
require_once('training-plugin-DB.php');
require_once('training-plugin-widget.php');
require_once('training-plugin-utils.php');


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

        if (key_exists('delete_training', $_POST)) {
            $this->db->delete_training($_POST['delete_training']);
        }

        $user = wp_get_current_user();
        $userId = $user->data->ID;
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
                            foreach ($user_trainings as $key => $training) {
                                $startDatetime = new DateTime("{$training->date} {$training->startTime}");
                                $endDatetime = new DateTime("{$training->date} {$training->endTime}");
                            ?>

                                <li><span><?php echo "{$training->type}: Le {$startDatetime->format('d/m')} de {$startDatetime->format('G\hi')} à {$endDatetime->format('G\hi')}" ?>
                                        <?php if ($training->is_visio != null) {
                                            $visioLabel = $training->is_visio ? 'en visio' : 'sur place';
                                            echo " ({$visioLabel})";
                                        } ?>
                                    </span>
                                    <form method="POST" action="" onsubmit="return confirm('Confirmer la désinscription');">
                                        <input type="hidden" name="delete_training" value="<?php echo $training->id ?>" />
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
