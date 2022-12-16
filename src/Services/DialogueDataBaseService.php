<?php

namespace App\Services;

class DialogueDataBaseService
{
    private $connection;
    private $logger;

    function __construct($connection) {
        $this->connection = $connection;
    }


    public function get_status($chat_id) {
        $quarry = 'SELECT status FROM Dialogue WHERE chat_id=' . $chat_id . ';';

        $result = mysqli_query($this->connection, $quarry);

        $fetch = mysqli_fetch_array($result)['status'];
        if ($fetch == null) {
            $this->set_status($chat_id, 0);
            return 0;
        }
        return $fetch;
    }

    public  function set_status($chat_id, $status) {
        $quarry = 'INSERT INTO Dialogue (chat_id, status) (' . $chat_id . ', '  . $status . ');';

        $result = mysqli_query($this->connection, $quarry);

        if (!$result) {
            //todo
        }
    }

    public function update_status($chat_id, $status) {
        $quarry = 'UPDATE Dialogue SET status=' . $status . ' WHERE chat_id=' . $chat_id . ';';

        $result = mysqli_query($this->connection, $quarry);

        if (!$result) {
            //todo
        }
    }
}