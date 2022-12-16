<?php

namespace App\Services;

use VK\Client\VKApiClient;

class VkApiService
{
    private string $access_token;
    private VkApiClient $client;

    function __construct($vkApi) {
        $this->access_token = "vk1.a._yZ8p18plSQOmZiPgtYyeO2muWSzEC_ODtMLqaBdCJNHAmkvazycnE2d42YMRaKbOSKb-kxYNsOOD_LcvQhzM_WjJKvNrYwacJF1NhToBYyd3gFnxyW9bMfjOVmCK8hHSi86BBzDEqHz3eniRoE1vqfYZLV9rdV9lM07yrx2YB8KJkxfiO2-vOPAMPDI7vW-zzYzf5RDDNv0MtU-lrmA_w";
        $this->client = $vkApi;
    }

    public function vk_msg_send($peer_id,$text){
        $request_params = array(
            'message' => $text,
            'peer_id' => $peer_id,
            'random_id' => random_int(0, PHP_INT_MAX),
        );
        $this->client->messages()->send(
            $this->access_token,
            $request_params
        );
    }

    public function sendKeyboard(int $userId, string $message, string $keyboard): void
    {
        $this->client->messages()->send(
            $this->access_token,
            ['peer_id' => $userId,
                'user_id' => $userId,
                'random_id' => random_int(0, PHP_INT_MAX),
                'message' => $message,
                'keyboard' => $keyboard,
            ]
        );
    }
}