<?php

// Check Token
use App\Support\PHPGramBot;

if (!isset($token)) {
    http_response_code(403);
    die();
}

$bot = new PHPGramBot($token, true);


function simple_start_bot($user_id, $text){
    global $bot;

    // Read Message From Json file
    $msg = $bot->get_message("simple_hello");

    // Read Buttons From Json file
    $buttons = $bot->get_button("simple_hello");

    // Send Message
    $bot->apiRequestWebhook("sendMessage",["chat_id"=>$user_id,
        "text"=>$msg,
        'reply_markup' => ['keyboard' => $buttons,
            'resize_keyboard' => true,
            'is_persistent' => true]
    ]);
}

$bot->command_handler('start', 'simple_start_bot');