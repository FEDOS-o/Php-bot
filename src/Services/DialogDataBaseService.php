<?php

namespace App\Services;

class DialogDataBaseService
{
    private $connection;
    private $logger;

    function __construct($connection) {
        $this->connection = $connection;
    }


    private function get($chat_id, $key) : int {
        $quarry = 'SELECT ' . $key . ' FROM Dialog WHERE chat_id=' . $chat_id . ';';

        $result = mysqli_query($this->connection, $quarry);

        $fetch = mysqli_fetch_array($result)[$key];
        if ($fetch == null) {
            $this->set_status($chat_id,0);
            return 0;
        }
        return $fetch;
    }

    public function get_status($chat_id) : int {
        return $this->get($chat_id, 'status');
    }

    public function get_count($chat_id) : int {
        return $this->get($chat_id, 'count');
    }

    public function get_min_years($chat_id) : int {
        return $this->get($chat_id, 'min_years');
    }

    public function get_max_years($chat_id) : int {
        return $this->get($chat_id, 'max_years');
    }

    public  function set_status($chat_id, $status) : bool {
        $quarry = 'INSERT INTO Dialog (chat_id, status) (' . $chat_id . ', '  . $status . ');';

        return mysqli_query($this->connection, $quarry);
    }

    private function update($chat_id, $value, $key) : bool {
        $quarry = 'UPDATE Dialog SET ' . $key . '=' . $value . ' WHERE chat_id=' . $chat_id . ';';
        return mysqli_query($this->connection, $quarry);
    }

    public function update_status($chat_id, $status) : bool {
        return $this->update($chat_id, $status, 'status');
    }

    public function update_count($chat_id, $count) : bool {
        return $this->update($chat_id, $count, 'count');
    }

    public function update_min_years($chat_id, $min_years) : bool {
        return $this->update($chat_id, $min_years, 'min_years');
    }

    public function update_max_years($chat_id, $max_years) : bool {
        return $this->update($chat_id, $max_years, 'max_years');
    }
}