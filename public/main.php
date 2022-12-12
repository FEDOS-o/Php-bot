<?php


use App\Controllers\ServerHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

require __DIR__ . '/../vendor/autoload.php';

$log = new Logger('logger');
$log->pushHandler(new StreamHandler('../log.log'));
$log->debug('aboba');
$handler = new ServerHandler();
$data = json_decode(file_get_contents('php://input'));
$handler->parse($data);