<?php

namespace App\Services;

use App\Credentials\Film;
use Monolog\Logger;
use VK\Client\VKApiClient;

class VkApiService
{
    private string $access_token;
    private VkApiClient $client;
    private Logger $logger;

    function __construct($vkApi, $logger)
    {
        $this->access_token = "vk1.a._yZ8p18plSQOmZiPgtYyeO2muWSzEC_ODtMLqaBdCJNHAmkvazycnE2d42YMRaKbOSKb-kxYNsOOD_LcvQhzM_WjJKvNrYwacJF1NhToBYyd3gFnxyW9bMfjOVmCK8hHSi86BBzDEqHz3eniRoE1vqfYZLV9rdV9lM07yrx2YB8KJkxfiO2-vOPAMPDI7vW-zzYzf5RDDNv0MtU-lrmA_w";
        $this->client = $vkApi;
        $this->logger = $logger;
    }

    public function vk_msg_send($peer_id, $text)
    {
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

    public function vk_send_film($peer_id, Film $film, $number): void
    {
        $text = $number . ": " . $film->name . ", " . $film->info . "\n";
        $text .= $film->actors . "\n";
        $text .= $film->rating . "\n";
        $text .= $film->description . "\n";
        $text .= $film->film_link . "\n";
        $this->vk_msg_send($peer_id, $text);
        //$this->vk_send_img($peer_id, $film->poster_link);
    }

    public function vk_send_img($peer_id, $img) : void
    {
        $image_path = __DIR__ . "/../../static/" . $peer_id . ".jpg";
        $this->logger->debug($img);
        file_put_contents($image_path, file_get_contents($img));
        $this->logger->debug($image_path);
        $address = $this->client->photos()->getMessagesUploadServer($this->access_token);
        $this->logger->debug($address['upload_url']);
        $photo = $this->client->getRequest()->upload($address['upload_url'], 'photo', $image_path);
        //$this->logger->debug($photo);
        $response_save_photo = $this->client->photos()->saveMessagesPhoto($this->access_token, [
            'server' => $photo['server'],
            'photo' => $photo['photo'],
            'hash' => $photo['hash'],
        ]);
        //$this->logger->debug($response_save_photo);
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