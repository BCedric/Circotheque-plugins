<?php

require_once('training-plugin-DB.php');
require_once('training-plugin-utils.php');

class Training_Plugin_Form
{
    private $db;

    private $days = [
        'monday' => ['label' => 'Lundi'],
        'tuesday' => ['label' => 'Mardi'],
        'wednesday' => ['label' => 'Mercredi'],
        'thursday' => ['label' => 'Jeudi'],
        'friday' => ['label' => 'Vendredi'],
    ];

    public function __construct()
    {
        add_shortcode('user_training_form', array($this, 'user_training_form'));
        $this->db = new Training_PLugin_DB();
    }

    public function user_training_form()
    {
        $dates = [];

        foreach ($this->days as $key => $day) {
            $date = new DateTime();
            $date->modify("next {$key}");
            $dates[$date->format('Y-m-d')] = ['rawDate' => $date->format(DateTime::ATOM), 'day' => $key, 'label' => $day['label']];
            $date->modify('+7 days');
            $dates[$date->format('Y-m-d')] = ['rawDate' => $date->format(DateTime::ATOM), 'day' => $key, 'label' => $day['label']];
        }
        ksort($dates);

        if (key_exists('user-training-form', $_POST)) {
            $this->save_form($dates);
            ob_start();
            return
                ob_get_clean();
        }

        ob_start();

        if (Training_plugin_utils::is_user_enable_to_use_training()) {
?>

            <h3 class="training-title">Inscription pour les séances à venir:</h3>
            <form action="" method="post" class="training-form">
                <div class="training-form-choices">
                    <?php
                    foreach ($dates as $strDate => $date) {
                        $sessions = $this->db->get_sessions_training_by_day($date['day']);
                        foreach ($sessions as $key => $session) {
                            if (!$this->is_user_already_register($date, $session) && !$this->is_limit_reached($date, $session)) {
                                setlocale(LC_ALL, 'fr_FR.utf8');
                                $datetime = new DateTime($date['rawDate']);
                                $startDateTime = new DateTime($session->startTime);
                                $endDateTime = new DateTime($session->endTime);
                                $dateStr =  ucfirst(strftime('%A %d %B', $datetime->getTimestamp()));
                    ?>
                                <div class="">
                                    <input data-name="trainings[<?php echo "{$strDate}:{$session->id}" ?>]" type="checkbox" class="<?php if ($session->type === "Virtual Training") echo "virtual-training"; ?>" name="trainings[<?php echo "{$strDate}:{$session->id}][value]" ?>" value="1" />
                                    <label for="<?php echo $key ?>"><?php echo "{$session->type}: {$dateStr} de {$startDateTime->format('H\hi')} à {$endDateTime->format('H\hi')}" ?></label>
                                </div>
                    <?php
                            }
                        }
                    }
                    ?>
                </div>

                <button type="submit" class="training-submit" name="user-training-form">Inscription</button>
            </form>
            <script>
                jQuery('input[type=checkbox].virtual-training').change(function() {
                    if ($(this).is(':checked')) {
                        console.log($(this).attr('data-name'));
                        $(this).parent().append(`
                        <div class="radio-virtual-training">
                        <input type="radio" name="${$(this).attr('data-name')}[visio]" value=true checked>
                        <label>Visio</label>
                        <input type="radio" name="${$(this).attr('data-name')}[visio]" value=false>
                        <label>Presentiel</label>
                        </div>
                        `)
                    } else {
                        $(this).parent().children('.radio-virtual-training').remove()
                    }
                });
            </script>
<?php
        }
        return ob_get_clean();
    }

    public function is_user_already_register($date, $session)
    {
        $user_trainings = $this->db->get_user_trainings(get_current_user_id());

        $rawDateTime = new DateTime($date['rawDate']);
        $dateStr = $rawDateTime->format('Y-m-d');

        foreach ($user_trainings as $key => $user_training) {
            if ($dateStr === $user_training->date && $user_training->session_id === $session->id) {
                return true;
            }
        }
        return false;
    }

    public function is_limit_reached($date, $session)
    {
        $limit = get_option("training_max_capacity");
        $rawDateTime = new DateTime($date['rawDate']);
        $dateStr = $rawDateTime->format('Y-m-d');

        $trainings = $this->db->get_trainings();
        $trainingsFiltered = array_filter($trainings, function ($training) use ($dateStr, $session) {
            return $dateStr === $training->date && $training->session_id = $session->id;
        });
        return count($trainingsFiltered) >= $limit;
    }

    public function save_form($dates)
    {
        $userId = get_current_user_id();
        foreach ($_POST['trainings'] as $key => $value) {
            $visio = key_exists('visio', $value) ? $value['visio'] : null;
            [$date, $session_id] = explode(':', $key);
            $this->db->save_training($userId, $session_id, $date, $visio);
        }
        wp_redirect(get_permalink());
    }
}
