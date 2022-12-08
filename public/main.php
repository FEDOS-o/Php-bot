<?php
$confirmation_token = "e6f7125a";
function vk_msg_send($peer_id,$text){
    $access_token = "vk1.a._yZ8p18plSQOmZiPgtYyeO2muWSzEC_ODtMLqaBdCJNHAmkvazycnE2d42YMRaKbOSKb-kxYNsOOD_LcvQhzM_WjJKvNrYwacJF1NhToBYyd3gFnxyW9bMfjOVmCK8hHSi86BBzDEqHz3eniRoE1vqfYZLV9rdV9lM07yrx2YB8KJkxfiO2-vOPAMPDI7vW-zzYzf5RDDNv0MtU-lrmA_w";
    $request_params = array(
        'message' => $text,
        'peer_id' => $peer_id,
        'access_token' => $access_token,
        'v' => '5.87'
    );
    $get_params = http_build_query($request_params);
    file_get_contents('https://api.vk.com/method/messages.send?'. $get_params);
}
$data = json_decode(file_get_contents('php://input'));
switch ($data->type) {
    case 'confirmation':
        echo $confirmation_token;
        break;

    case 'message_new':
        $message_text = $data -> object -> text;
        $chat_id = $data -> object -> peer_id;
        if ($message_text == "привет"){
            vk_msg_send($chat_id, "Привет, я бот, который говорит две фразы.");
        }
        if ($message_text == "пока"){
            vk_msg_send($chat_id, "Пока. Если захочешь с кем-то поговорить, то у тебя есть бот, который говорит две фразы.");
        }
        echo 'ok';
        break;
}
//checking git hook on update
