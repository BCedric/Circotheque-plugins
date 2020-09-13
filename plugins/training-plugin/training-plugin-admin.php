<?php

class Training_Plugin_Admin
{
    private $days = [
        'monday' => ['label' => 'Lundi'],
        'tuesday' => ['label' => 'Mardi'],
        'wednesday' => ['label' => 'Mercredi'],
        'thursday' => ['label' => 'Jeudi'],
        'friday' => ['label' => 'Vendredi'],
    ];

    public function __construct()
    {
        add_action('admin_menu', array($this, 'add_admin_menu'), 20);
        add_action('admin_init', array($this, 'register_options'));
        $this->db = new Training_PLugin_DB();
    }

    public function add_admin_menu()
    {
        add_menu_page('Training', 'Training', 'manage_options', 'training', array($this, 'menu_html'));
        add_submenu_page('training', 'Réglages', 'Réglages', 'manage_options', 'settings', array($this, 'settings_html'));
    }

    public function register_options()
    {
        register_setting('training_settings', "training_max_capacity");
        register_setting('training_settings', "training_years_limit");
    }

    public function menu_html()
    {
        echo '<h1>' . get_admin_page_title() . '</h1>';
        $trainings = $this->db->get_trainings_with_users();

        $now = new DateTime();
        $now->setTime(0, 0);
        $trainings = array_filter($this->db->get_trainings_with_users(), function ($training) use ($now) {
            return new DateTime($training->date) >= $now;
        });
        $trainingsByDate = [];
        foreach ($trainings as $key => $training) {
            $key = $training->date . ':' . $training->session_id;
            if (key_exists($key, $trainingsByDate)) {
                array_push($trainingsByDate[$key], $training);
            } else {
                $trainingsByDate[$key] = [$training];
            }
        }
?>
        <div class="training-admin-summary">
            <?php
            foreach ($trainingsByDate as $key => $trainings) {
                [$date] = explode(':', $key);
                $datetime = date_create($date);
                $startDatetime = new DateTime(current($trainings)->startTime);
                $trainingType = current($trainings)->type;

                setlocale(LC_TIME, "fr_FR.utf8");
            ?>
                <div class="training-item">
                    <h3 class="training-title"> <?php echo strftime('%A %e %B', $datetime->getTimestamp()) ?> à <?php echo $startDatetime->format('G\hi') ?> (<?php echo $trainingType ?>)</h3>
                    <div class="training-users-registered">

                        <?php
                        foreach ($trainings as $training) {
                            $displayName = trim(get_user_meta($training->user_id, 'first_name', true) . " " . get_user_meta($training->user_id, 'last_name', true));
                            if ($training->is_visio != null) {
                                $visio = $training->is_visio ? 'en visio' : 'sur place';
                            }
                        ?>
                            <span><?php echo $displayName ?> <?php if ($training->is_visio != null) echo "({$visio})" ?></span>
                        <?php
                        }
                        ?>
                    </div>
                </div>
            <?php
            }
            ?>
        </div>

        <?php
    }

    public function validate_settings()
    {
        foreach ($_POST['sessions_training'] as $key => $session) {
            if ($session['start_time'] > $session['end_time']) {
                return false;
            }
        }
        return true;
    }

    public function update_settings()
    {
        $dbSessions = $this->db->get_sessions_training();
        foreach ($dbSessions as $dbSession) {
            $keepSession = current(array_filter($_POST['sessions_training'], function ($session) use ($dbSession) {
                return key_exists('id', $session) && $session['id'] === $dbSession->id;
            }));
            if (!$keepSession) {
                $this->db->delete_session_training($dbSession->id);
            } else {
                $this->db->update_session_training($dbSession->id, $keepSession);
            }
        }

        foreach ($_POST['sessions_training'] as $session) {
            if (!key_exists('id', $session)) {
                $this->db->insert_session_training($session);
            }
        }
        update_option("training_max_capacity", $_POST['training_max_capacity']);
        update_option('training_years_limit', $_POST['training_years_limit']);
    }

    public function settings_html()
    {
        $trainingTypes = ['Virtual Training', 'Conditionnement Acrobatie', 'Conditionnement Aériens', 'Stretching'];

        $sessionsTraining = $this->db->get_sessions_training();
        echo '<h1>' . get_admin_page_title() . '</h1>';
        if (!empty($_POST)) {
            if ($this->validate_settings()) {
                $this->update_settings();
        ?>

                <span>Les paramètres on été enregistrés</span>
            <?php
            } else {
            ?>
                <span>L'heure de début de chaque séance doit être inférieur à la date de fin</span>
            <?php
            }
        } else {
            ?>
            <script>
                function addSession() {
                    // jQuery(document).ready(function($) {
                    const sessionList = jQuery('#sessions-list')
                    const childrenNumber = jQuery('#sessions-list li').length
                    sessionList.append(`<li id="session-training-${childrenNumber}">
                    <label for="day">Jour</label>
                    <select id="day" name="sessions_training[${childrenNumber}][day]">
                        <option value="monday">Lundi</option>
                        <option value="tuesday">Mardi</option>
                        <option value="wednesday">Mercredi</option>
                        <option value="thursday">Jeudi</option>
                        <option value="friday">Vendredi</option>
                    </select>

                    <label for="training">Type</label>
                    <select name="sessions_training[${childrenNumber}][type]">
                    <?php
                    foreach ($trainingTypes as $trainingType) {
                        echo "<option value='{$trainingType}'?>{$trainingType}</option>";
                    }
                    ?>
                    </select>
                    <label> de:</label>
                    <input type="time" name="sessions_training[${childrenNumber}][start_time]" value="" />
                    <label>à:</label>
                    <input type="time" name="sessions_training[${childrenNumber}][end_time]" value="" /><br />
                    <span class="dashicons dashicons-trash" onclick="removeSession(${childrenNumber})"></span>
                </li>`)
                }

                function removeSession(key) {
                    jQuery(`#session-training-${key}`).remove()
                }
            </script>
            <form method="post" action="" class="training-admin-form">
                <?php settings_fields('training_settings') ?>
                <div class="form-field">
                    <label for="training_max_capacity">Nombre de places par séance :</label>
                    <input type="number" name="training_max_capacity" value="<?php echo get_option("training_max_capacity") ?>" /><br />
                </div>
                <div class="form-field">
                    <label>Age limite: </label>
                    <input type="number" name="training_years_limit" value="<?php echo get_option("training_years_limit") ?>">
                </div>
                <h3>Créneaux de training</h3>
                <button type="button" onclick="addSession()">Ajouter</button>
                <ul id="sessions-list">
                    <?php
                    foreach ($sessionsTraining as $key => $session) {
                    ?>
                        <li id="session-training-<?php echo $key ?>" class="session-training">
                            <label for="day">Jour</label>
                            <select id="day" name="sessions_training[<?php echo $key ?>][day]">
                                <?php
                                foreach ($this->days as $dayName => $day) {
                                    $selected = '';
                                    if ($session->day === $dayName) {
                                        $selected = "selected";
                                    }

                                    echo "<option value='{$dayName}' {$selected}>{$day['label']}</option>";
                                }
                                ?>
                            </select>

                            <label for="training">Type</label>
                            <select name="sessions_training[<?php echo $key ?>][type]">
                                <?php
                                foreach ($trainingTypes as $trainingType) {
                                    $selected = '';
                                    if ($session->type === $trainingType) {
                                        $selected = "selected";
                                    }

                                    echo "<option value='{$trainingType}' {$selected} ?>{$trainingType}</option>"
                                ?>

                                <?php
                                }
                                ?>
                            </select>
                            <label> de:</label>
                            <input type="time" name="sessions_training[<?php echo $key ?>][start_time]" value="<?php echo $session->startTime ?>" />
                            <label>à:</label>
                            <input type="time" name="sessions_training[<?php echo $key ?>][end_time]" value="<?php echo $session->endTime ?>" />
                            <span class="dashicons dashicons-trash" onclick="removeSession(<?php echo $key ?>)"></span>
                            <input type="hidden" name="sessions_training[<?php echo $key ?>][id]" value="<?php echo $session->id ?>" />
                        </li>
                    <?php
                    }
                    ?>
                </ul>

                <?php submit_button(); ?>
            </form> <?php
                }
            }
            // }
        }
