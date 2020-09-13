<?php

class Training_plugin_utils
{
    public static function is_user_enable_to_use_training()
    {
        $birthDate = get_user_meta(get_current_user_id(), 'birth_date');
        $birthDateTime = new DateTime();
        $birthDateTime->setTimestamp(strtotime($birthDate[0]));

        $dateTime14yearsBefore = new DateTime();
        $dateTime14yearsBefore->setTimestamp(strtotime("-" . get_option("training_years_limit") . " years"));
        return empty($birthDate) || $birthDateTime < $dateTime14yearsBefore;
    }
}
