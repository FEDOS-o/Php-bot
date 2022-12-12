<?php


use App\Controllers\ServerHandler;

require 'vendor/autoload.php';

$handler = new ServerHandler();
$data = json_decode(file_get_contents('php://input'));
$handler->parse($data);