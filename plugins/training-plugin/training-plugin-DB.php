<?php

class Training_PLugin_DB
{

    public function create_trainings_table()
    {
        global $wpdb;
        $wpdb->query("CREATE TABLE {$wpdb->prefix}training_plugin (
        `id` INT NOT NULL AUTO_INCREMENT,
        `user_id` BIGINT UNSIGNED NULL,
        `session_id` INT UNSIGNED NULL,
        `date` DATE NULL,
        `is_visio` BOOLEAN NULL,
        PRIMARY KEY (`id`),
        INDEX `fk_training_plugin_user_id` (`user_id` ASC),
         CONSTRAINT `fk_training_plugin_session`
            FOREIGN KEY (`session_id`)
            REFERENCES {$wpdb->prefix}training_sessions (`id`)
            ON DELETE CASCADE
            ON UPDATE CASCADE,
        CONSTRAINT `fk_training_plugin_user`
            FOREIGN KEY (`user_id`)
            REFERENCES {$wpdb->prefix}users (`ID`)
            ON DELETE CASCADE
            ON UPDATE CASCADE);");
    }

    public function create_sessions_table()
    {
        global $wpdb;
        $wpdb->query("CREATE TABLE {$wpdb->prefix}training_sessions (
        `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
        `day` VARCHAR(45),
        `type` VARCHAR(45),
        `startTime` TIME NULL,
        `endTime` TIME NULL,
        PRIMARY KEY (`id`)
        );");
    }

    public function drop_sessions_table()
    {
        global $wpdb;
        $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}training_sessions;");
    }

    public function drop_trainings_table()
    {
        global $wpdb;
        $wpdb->query("DROP TABLE IF EXISTS {$wpdb->prefix}training_plugin;");
    }

    public function save_training($userId, $session_id, $date, $isVisio)
    {
        global $wpdb;
        if ($isVisio == null) {
            $req = "INSERT INTO {$wpdb->prefix}training_plugin (user_id, session_id, date) VALUES ({$userId}, {$session_id}, '{$date}') ;";
        } else {
            $req = "INSERT INTO {$wpdb->prefix}training_plugin (user_id, session_id, date, is_visio) VALUES ({$userId}, {$session_id}, '{$date}', {$isVisio}) ;";
        }
        $wpdb->query($req);
    }

    public function delete_training($trainingId)
    {
        global $wpdb;
        $wpdb->query("DELETE FROM {$wpdb->prefix}training_plugin WHERE id = {$trainingId};");
    }

    public function get_user_trainings($userId)
    {
        global $wpdb;
        return $wpdb->get_results("SELECT trainings.id, trainings.user_id, trainings.date, trainings.is_visio, trainings.session_id, sessions.type, sessions.startTime, sessions.endTime FROM {$wpdb->prefix}training_plugin as trainings INNER JOIN {$wpdb->prefix}training_sessions as sessions ON trainings.session_id = sessions.id  WHERE user_id = {$userId} ORDER BY date");
    }

    public function get_trainings()
    {
        global $wpdb;
        return $wpdb->get_results("SELECT trainings.id, trainings.user_id, trainings.date, sessions.type, sessions.startTime, sessions.endTime FROM {$wpdb->prefix}training_plugin as trainings INNER JOIN {$wpdb->prefix}training_sessions as sessions ON trainings.session_id = sessions.id ORDER BY date");
    }

    public function get_trainings_with_users()
    {
        global $wpdb;
        return $wpdb->get_results("SELECT * FROM {$wpdb->prefix}training_plugin as trainings INNER JOIN {$wpdb->prefix}users as users ON trainings.user_id = users.ID INNER JOIN {$wpdb->prefix}training_sessions as sessions ON trainings.session_id = sessions.id ORDER BY date");
    }

    public function get_trainings_by_start_date($startDate)
    {
        global $wpdb;
        return $wpdb->get_results("SELECT * FROM {$wpdb->prefix}training_plugin WHERE day(startDate) = day('{$startDate}')");
    }

    public function get_sessions_training()
    {
        global $wpdb;
        return $wpdb->get_results("SELECT * FROM {$wpdb->prefix}training_sessions");
    }

    public function get_sessions_training_by_day($day)
    {
        global $wpdb;
        return $wpdb->get_results("SELECT * FROM {$wpdb->prefix}training_sessions WHERE day = '{$day}'");
    }

    public function delete_sessions_training()
    {
        global $wpdb;
        return $wpdb->get_results("DELETE FROM {$wpdb->prefix}training_sessions");
    }

    public function delete_session_training($id)
    {
        global $wpdb;
        return $wpdb->get_results("DELETE FROM {$wpdb->prefix}training_sessions WHERE id = {$id}");
    }

    public function update_session_training($id, $session)
    {
        global $wpdb;
        return $wpdb->get_results("UPDATE {$wpdb->prefix}training_sessions SET day = '{$session['day']}', type = '{$session['type']}', startTime = '{$session['start_time']}', endTime = '{$session['end_time']}' WHERE id = {$id}");
    }

    public function insert_session_training($session)
    {
        global $wpdb;
        return $wpdb->get_results("INSERT INTO {$wpdb->prefix}training_sessions (day, type, startTime, endTime) VALUES ('{$session['day']}', '{$session['type']}', '{$session['start_time']}', '{$session['end_time']}') ;");
    }
}
