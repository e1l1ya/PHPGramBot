<?php
require __DIR__ . "/../vendor/autoload.php";

error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE);

// Error If Token was not Found
if(!isset($_GET['token'])) {
    http_response_code(403);
    die();
}

// Init Token
$token = $_GET['token'];

// Bot Not Found
if(is_null(config("bots.$token.name"))) {
    http_response_code(403);
    die();
}
// Define Bot Class
$botName = config("bots.$token.name");
$botClass = config("bots.$token.class");

require_once __DIR__."/../app/Bot/$botName/$botClass.php";