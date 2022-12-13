<?php

namespace App\Controllers;

use VK\CallbackApi\Server\VKCallbackApiServerHandler;

require __DIR__ . '/../../vendor/autoload.php';

class ServerHandler extends VKCallbackApiServerHandler
{
    const SECRET = 'vk1.a._yZ8p18plSQOmZiPgtYyeO2muWSzEC_ODtMLqaBdCJNHAmkvazycnE2d42YMRaKbOSKb-kxYNsOOD_LcvQhzM_WjJKvNrYwacJF1NhToBYyd3gFnxyW9bMfjOVmCK8hHSi86BBzDEqHz3eniRoE1vqfYZLV9rdV9lM07yrx2YB8KJkxfiO2-vOPAMPDI7vW-zzYzf5RDDNv0MtU-lrmA_w';
    const GROUP_ID = 217490194;
    const CONFIRMATION_TOKEN = 'e5f0bdff';
    public $log;

    function __construct($log) {
        $this->log = $log;
    }

    function confirmation(int $group_id, ?string $secret) {
        if ($secret !== null) {
            $this->log->debug($secret);
        }
        if ($group_id === static::GROUP_ID) {
            echo static::CONFIRMATION_TOKEN;
            return;
        }
        echo "kek";
    }

    public function messageNew(int $group_id, ?string $secret, array $object) {
        echo 'ok';
    }
}