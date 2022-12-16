<?php

namespace App\Controllers;

use App\Services\DialogueDataBaseService;
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
    private DialogueDataBaseService $db;
    private VKApiService $vk;


    function __construct($logger, $connection, $vkApi)
    {
        $this->logger = $logger;
        $this->db = new DialogueDataBaseService($connection);
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
        switch ($status) {
            case 0:
                break;
            case 1;
                break;

        }
        echo 'ok';
    }
}