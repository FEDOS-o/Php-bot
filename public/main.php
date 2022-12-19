<?php


use App\Controllers\ServerHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use VK\Client\VKApiClient;


require __DIR__ . '/../vendor/autoload.php';

$config = json_decode(file_get_contents(__DIR__ . '/../config.json'));
$logger = new Logger('logger');
$logger->pushHandler(new StreamHandler($config->LOGGER_DIR));
$logger->info('Server started');

$logger->info("Starting connection to data base");
$connection = mysqli_connect($config->MYSQL_HOSTNAME, $config->MYSQL_USERNAME, $config->MYSQL_PASSWORD, $config->MYSQL_DATABASE);
if (!$connection) {
    $logger->error("Connection failed");
}

$logger->debug("Starting creating VkApiClient");
$vkApi = new VKApiClient($config->VK_API_VERSION);


$logger->debug("Starting creating ServerHandler");
$handler = new ServerHandler($logger, $connection, $vkApi, $config);

$data = json_decode(file_get_contents('php://input'));

$logger->info("Start handling request");
$handler->parse($data);

$connection->close();