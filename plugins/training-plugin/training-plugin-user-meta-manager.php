<?php

class Training_Plugin_User_Meta_Manager
{
    private $userId;
    private $abandonnement_number_key = 'training_abandonment_number';
    private $suspended_until_key = 'training_suspended_until';

    public function __construct($userId)
    {
        $this->userId = $userId;
    }

    public function get_training_abandonment_number()
    {
        return intval(get_user_meta($this->userId, $this->abandonnement_number_key, true));
    }

    public function get_suspended_until()
    {
        $suspended_until = get_user_meta($this->userId, $this->suspended_until_key, true);
        if ($suspended_until !== '') {
            return new DateTime($suspended_until);
        }
    }

    public function user_abandonment()
    {
        $training_abandonment_number = $this->get_training_abandonment_number();
        if ($training_abandonment_number === 2) {
            $nextWeek = new DateTime();
            $nextWeek->add(new DateInterval('P1W'));
            update_user_meta($this->userId, $this->suspended_until_key, $nextWeek->format(DATE_ATOM));
            update_user_meta($this->userId, $this->abandonnement_number_key, 0);
        } else {
            update_user_meta($this->userId, $this->abandonnement_number_key, $training_abandonment_number + 1);
        }
    }

    public function is_user_suspended()
    {
        $training_suspended_until = $this->get_suspended_until();
        if ($training_suspended_until != null) {
            if ($training_suspended_until < new DateTime()) {
                delete_user_meta($this->userId, $this->suspended_until_key);
                return false;
            } else {
                return true;
            }
        }
        return false;
    }

    public function is_abandonment_suspandable($startDatetime)
    {
        $nowPlus24H = new DateTime();
        $nowPlus24H->add(new DateInterval("P1D"));
        return $nowPlus24H > $startDatetime;
    }
}
