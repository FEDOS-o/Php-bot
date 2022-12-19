<?php

namespace App\Controllers;

use App\Credentials\FilmRequest;
use App\Services\DialogDataBaseService;
use App\Services\RandomFilmService;
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
    private RandomFilmService $rnd_film;


    function __construct($logger, $connection, $vkApi)
    {
        $this->logger = $logger;
        $this->db = new DialogDataBaseService($connection);
        $this->vk = new VkApiService($vkApi, $logger);
        $this->rnd_film = new RandomFilmService();
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
        $this->logger->info("Handling messageNew event");
        $chat_id = $object['message']->peer_id;
        $status = $this->db->get_status($chat_id);
        $text = $object['message']->text;
        if ($text == '/start') {
            $this->db->update_status($chat_id, 1);
            $status = 0;
        }
        switch ($status) {
            case 0:
                $this->vk->vk_msg_send($chat_id, "Чтобы начать напишите /start");
                break;
            case 1:
                $this->vk->vk_msg_send($chat_id, "Cколько вас?");
                $this->db->update_status($chat_id, 2);
                break;
            case 2:
                if ($this->count_validation($text)) {
                    $this->db->update_status($chat_id, 3);
                    $this->db->update_count($chat_id, intval($text));
                    $this->vk->vk_msg_send($chat_id, "Укажите минимальный год выхода фильма");
                } else {
                    $this->vk->vk_msg_send($chat_id, "Нет, вас не может быть столько. Попробуйте еще раз ввести число от 2 до 20");
                }
                break;
            case 3:
                if ($this->min_years_validation($text)) {
                    $this->db->update_status($chat_id, 4);
                    $this->db->update_min_years($chat_id, intval($text));
                    $this->vk->vk_msg_send($chat_id, "Укажите максимальный год выхода фильма");
                } else {
                    $this->vk->vk_msg_send($chat_id, "Нет, введите год между 1920 и 2022 включительно");
                }
                break;
            case 4:
                $res = intval($text);
                $min_years = $this->db->get_min_years($chat_id);
                if ($this->is_integer($text) && $this->max_years_validation($res, $min_years)) {
                    $this->db->update_status($chat_id, 5);
                    $this->db->update_max_years($chat_id, intval($text));
                    $this->vk->vk_msg_send($chat_id, "Подбираю вам случайные фильмы...");
                    if ($this->show_films($chat_id)) {
                        $this->vk->vk_msg_send($chat_id, "Введите номер фильма, который не хотите смотреть");
                    } else {
                        $this->vk->vk_msg_send($chat_id, "Не могу найти столько фильмов для вас в таком диапазоне. Давайте попробуем указать другой временной промежуток");
                        $this->db->update_status($chat_id, 3);
                        $this->vk->vk_msg_send($chat_id, "Укажите минимальный год выхода фильма");
                    }
                } else {
                    $this->vk->vk_msg_send($chat_id, "Нет, введите год между " . $min_years . " и 2022 включительно");
                }
                break;
            default:
                $count = $this->db->get_count($chat_id);
                if ($count == 0) {
                    $this->vk->vk_msg_send($chat_id, "Чтобы выбрать еще 1 фильм напишите /start");
                } else if ($this->kick_validation($text, $count)) {
                    $kick = intval($text);
                    $list = unserialize($this->db->get_films_list($chat_id));
                    $new_list = array($count);
                    $j = 0;
                    for ($i = 0; $i < $count + 1; $i++) {
                        if ($i + 1 != $kick) {
                            $new_list[$j] = $list[$i];
                            $j++;
                        }
                    }
                    $this->db->update_count($chat_id, $count - 1);
                    if (count($new_list) != 1) {
                        $this->db->update_films_list($chat_id, serialize($new_list));
                        $this->vk->vk_msg_send($chat_id, "Остались:\n" . $this->print_list($new_list));
                        $this->vk->vk_msg_send($chat_id, "Введите номер фильма, который не хотите смотреть");
                    } else {
                        $this->vk->vk_msg_send($chat_id, "Победитель: " . $new_list[0]);
                        $this->vk->vk_msg_send($chat_id, "Чтобы выбрать еще 1 фильм напишите /start");
                    }
                } else {
                    $this->vk->vk_msg_send($chat_id, "Нет, введите номер между 1 и " . $count + 1 . " включительно");
                }
                break;
        }
        echo 'ok';
    }

    private function count_validation($value): bool
    {
        $res = intval($value);
        return !(!$this->is_integer($value) || $res <= 1 || $res > 20);
    }

    private function kick_validation($value, $count): bool
    {
        $res = intval($value);
        return $this->is_integer($value) && $res >= 1 && $res <= $count + 1;
    }

    private function min_years_validation($value): bool
    {
        $res = intval($value);
        return ($this->is_integer($value) && $res >= 1920 && $res <= 2022);
    }

    private function max_years_validation($res, $min_years): bool
    {
        return ($res >= 1920 && $res <= 2022 && $res >= $min_years);
    }

    private function is_integer($value): bool
    {
        return preg_match("/^\d+$/", $value);
    }

    private function show_films($chat_id) : bool
    {
        $count = $this->db->get_count($chat_id);
        $min_years = $this->db->get_min_years($chat_id);
        $max_years = $this->db->get_max_years($chat_id);
        $this->logger->debug("Parameters: count=" . $count . ", min_years=" . $min_years . ", max_years=" . $max_years);
        $films = $this->rnd_film->get_films(new FilmRequest($count + 1, $min_years, $max_years));
        if (count($films) != $count + 1) {
            return false;
        }
        $number = 1;
        foreach ($films as $film) {
            $this->vk->vk_send_film($chat_id, $film, $number);
            $number++;
        }

        $number = 1;
        $films_names = array($count + 1);
        foreach ($films as $film) {
            $films_names[$number - 1] = $film->name;
            $number++;
        }
        $this->vk->vk_msg_send($chat_id, "Итого:\n" . $this->print_list($films_names));
        $this->print_list($films_names);
        $this->db->update_films_list($chat_id, serialize($films_names));
        return true;
    }

    private function print_list(array $list) : string
    {
        $str = "";
        for ($i = 0; $i < count($list); $i++) {
            $str .= ($i + 1) . ": " . $list[$i] . "\n";
        }
        return $str;
    }


    public function groupJoin(int $group_id, ?string $secret, array $object)
    {
        $chat_id = $object['user_id'];
        $this->vk->vk_msg_send($chat_id,"Привет, Я бот, котрый помогает выбрать фильм для компании.
    Вы указываете параметры поиска случайных фильмов и количество людей.
    Далее я вам выдаю набор фильмов и вы выбираете порядок в котором каждый человек из компании будет убирать фильм который он не хочет смотреть.
    В итоге отсается один победитель. Чтобы начать напиши /start");
        if ($this->db->get_status($chat_id) != 0) {
            $this->db->update_status($chat_id, 0);
        }
    }

}