<?php

namespace App\Controllers;

use App\Services\DialogDataBaseService;
use App\Services\VkApiService;
use Monolog\Logger;
use VK\CallbackApi\Server\VKCallbackApiServerHandler;
use VK\Client\VKApiClient;

require __DIR__ . '/../../vendor/autoload.php';

class ServerHandler extends VKCallbackApiServerHandler
{
    const TOKEN = 'vk1.a._yZ8p18plSQOmZiPgtYyeO2muWSzEC_ODtMLqaBdCJNHAmkvazycnE2d42YMRaKbOSKb-kxYNsOOD_LcvQhzM_WjJKvNrYwacJF1NhToBYyd3gFnxyW9bMfjOVmCK8hHSi86BBzDEqHz3eniRoE1vqfYZLV9rdV9lM07yrx2YB8KJkxfiO2-vOPAMPDI7vW-zzYzf5RDDNv0MtU-lrmA_w';
    const SECRET = 'aaQ1fsdf9ornzqwemc';
    const GROUP_ID = 217490194;
    const CONFIRMATION_TOKEN = '1b52fd41';
    private Logger $logger;
    private DialogDataBaseService $db;
    private VKApiService $vk;


    function __construct($logger, $connection, $vkApi)
    {
        $this->logger = $logger;
        $this->db = new DialogDataBaseService($connection);
        $this->vk = new VkApiService($vkApi);
    }

    function confirmation(int $group_id, ?string $secret)
    {
        if ($secret !== static::SECRET) {
            $this->logger->debug('secret key is invalid');
            return;
        }
        if ($group_id !== static::GROUP_ID) {
            $this->logger->debug('group id is invalid');
            return;
        }
        echo static::CONFIRMATION_TOKEN;
    }

    public function parse($event)
    {
        if ($event->type == static::CALLBACK_EVENT_CONFIRMATION) {
            $this->confirmation($event->group_id, $event->secret);
        } else {
            $group_id = $event->group_id;
            $secret = $event->secret;
            $type = $event->type;
            $object = (array)$event->object;
            $this->logger->debug("Received message of type $type from group $group_id: " . json_encode($object));
            if (
                $secret !== static::SECRET ||
                $group_id !== static::GROUP_ID
            ) {
                $this->logger->debug("Secret key or group id is invalid");
                return;
            }
            $this->parseObject($group_id, $secret, $type, $object);
        }
    }

    public function messageNew(int $group_id, ?string $secret, array $object)
    {
        $chat_id = $object['peer_id'];
        $status = $this->db->get_status($chat_id);
        if ($object['message'] == '/start') {
            $this->db->update_status($chat_id, 0);
            $status = 0;
        }
        switch ($status) {
            case 0:
                $this->vk->vk_msg_send($chat_id, "Привет, Я бот бла бла бла... Сколько вас?");
                $this->db->update_status($chat_id, 1);
                break;
            case 1:
                if ($this->count_validation($object['message'])) {
                    $this->db->update_status($chat_id, 2);
                    $this->db->update_count($chat_id, intval($object['message']));
                    $this->vk->vk_msg_send($chat_id, "Укажите минимальный год выхода фильма");
                } else {
                    $this->vk->vk_msg_send($chat_id, "Нет, вас не может быть столько. Попробуйте еще раз ввести число от 2 до 20");
                }
                break;
            case 2:
                if ($this->min_years_validation($object['message'])) {
                    $this->db->update_status($chat_id, 3);
                    $this->db->update_min_years($chat_id, intval($object['message']));
                } else {
                    $this->vk->vk_msg_send($chat_id, "Нет, введите год между 1920 и 2022 включительно");
                }
            case 3:
                $res = intval($object['message']);
                $min_years = $this->db->get_min_years($chat_id);
                if ($this->max_years_validation($res,  $min_years)) {
                    $this->db->update_status($chat_id, 4);
                    $this->db->update_max_years($chat_id, intval($object['message']));
                    $this->vk->vk_msg_send($chat_id, "Подбираю вам случайные фильмы");
                } else if ($res < 1920 || $res > 2022) {
                    $this->vk->vk_msg_send($chat_id, "Нет, введите год между 1920 и 2022 включительно");
                } else {
                    $this->vk->vk_msg_send($chat_id, "Нет, введите год между " . $min_years . " и 2022 включительно");
                }
        }
        echo 'ok';
    }

    private function count_validation($value) : bool {
        $res = intval($value);
        return !($res <= 1 || $res > 20);
    }

    private function min_years_validation($value) : bool {
        $res = intval($value);
        return ($res >= 1920 && $res <= 2022);
    }

    public function max_years_validation($res, $min_years) : bool {
        return ($res >= 1920 && $res <= 2022 && $res >= $min_years);
    }
}