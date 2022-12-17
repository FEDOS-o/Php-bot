<?php


use App\Controllers\ServerHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use VK\Client\VKApiClient;

require __DIR__ . '/../vendor/autoload.php';

$logger = new Logger('logger');
$logger->pushHandler(new StreamHandler('../log.log'));
$logger->info('Server started');

$logger->info("Starting connection to data base");
$connection = mysqli_connect("89.208.87.86", "root", "wsp6WYmX", "VK_BOT");
if (!$connection) {
    $logger->error("Connection failed");
}

$logger->debug("Starting creating VkApiClient");
$vkApi = new VKApiClient('5.131');


$logger->debug("Starting creating ServerHandler");
$handler = new ServerHandler($logger, $connection, $vkApi);

$data = json_decode(file_get_contents('php://input'));

$logger->info("Start handling request");
$handler->parse($data);