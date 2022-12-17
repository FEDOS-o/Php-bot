<?php


use App\Controllers\ServerHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use VK\Client\VKApiClient;

require __DIR__ . '/../vendor/autoload.php';

$logger = new Logger('logger');
$logger->pushHandler(new StreamHandler('../log.log'));
$logger->debug('aboba');

$connection = mysqli_connect("89.208.87.86", "root", "wsp6WYmX", "VK_BOT");

$vkApi = new VKApiClient('5.131');

$handler = new ServerHandler($logger, $connection, $vkApi);

$data = json_decode(file_get_contents('php://input'));

$handler->parse($data);