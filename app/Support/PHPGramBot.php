<?php

namespace App\Support;


class PHPGramBot
{
    public $message;
    public $text;
    public $user_id;
    public $type;
    private $update;
    private $contact;
    private $callback;
    private $token;
    private string $file_name;
    private string $file_ext;
    public string $file_id;

    public function __construct($TOKEN, $run_web_hook)
    {
        $this->token = $TOKEN;
        $this->bot_name = config("bots.{$this->token}.name");
        if ($run_web_hook == true) {
            $this->install();
        }
    }

    private function install()
    {
        $content = file_get_contents("php://input");
        $update = json_decode($content, true);
        if (!$update) {
            exit;
        }

        $this->update = $update;
        if (isset($this->update["message"]['text'])) {
            $this->message = $this->update["message"];
            $this->user_id = $this->message['chat']['id'];
            $this->text = $this->message['text'];
            if (substr($this->text, 0, 1) === "/") {
                $this->type = 'command';
            }
        } elseif (isset($this->update['message']['contact'])) {
            $this->message = $this->update["message"];
            $this->user_id = $this->message['chat']['id'];
            $this->contact = $this->update["message"]["contact"];
            $this->type = 'contact';
        } elseif (isset($this->update['callback_query'])) {
            $this->callback = $this->update['callback_query'];
            $this->user_id = $this->update['callback_query']['from']['id'];
            $this->type = 'callback';
        } elseif (isset($this->update['message']['document'])) {
            $this->message = $this->update["message"];
            $this->user_id = $this->message['chat']['id'];
            $this->file_name = $this->message["document"]["file_name"];
            $this->file_ext = pathinfo($this->file_name, PATHINFO_EXTENSION);
            $this->file_id = $this->message["document"]["file_id"];
            $this->type = 'file';
        } else {
            $this->type = 'text';
        }
    }

    public function apiRequestWebhook($method, $parameters): bool
    {
        if (!is_string($method)) {
            error_log("Method name must be a string\n");
            return false;
        }

        if (!$parameters) {
            $parameters = array();
        } else if (!is_array($parameters)) {
            error_log("Parameters must be an array\n");
            return false;
        }

        $parameters["method"] = $method;
        $payload = json_encode($parameters);
        header('Content-Type: application/json');
        header('Content-Length: ' . strlen($payload));
        echo $payload;
        return true;
    }

    public function apiRequestJson($method, $parameters)
    {
        if (!is_string($method)) {
            error_log("Method name must be a string\n");
            return false;
        }

        if (!$parameters) {
            $parameters = array();
        } else if (!is_array($parameters)) {
            error_log("Parameters must be an array\n");
            return false;
        }

        $parameters["method"] = $method;

        $handle = curl_init('https://api.telegram.org/bot' . $this->token . '/');
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($handle, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($handle, CURLOPT_TIMEOUT, 60);
        curl_setopt($handle, CURLOPT_POST, true);
        curl_setopt($handle, CURLOPT_POSTFIELDS, json_encode($parameters));
        curl_setopt($handle, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));

        return $this->exec_curl_request($handle);
    }

    private function exec_curl_request($handle)
    {
        $response = curl_exec($handle);
        if ($response === false) {
            $errno = curl_errno($handle);
            $error = curl_error($handle);
            error_log("Curl returned error $errno: $error\n");
            curl_close($handle);
            return false;
        }

        $http_code = intval(curl_getinfo($handle, CURLINFO_HTTP_CODE));
        curl_close($handle);
        if ($http_code >= 500) {
            // do not wat to DDOS server if something goes wrong
            sleep(10);
            return false;
        } else if ($http_code != 200) {
            $response = json_decode($response, true);
            error_log("Request has failed with error {$response['error_code']}: {$response['description']}\n");
            if ($http_code == 401) {
                throw new Exception('Invalid access token provided');
            }
            return false;
        } else {
            $response = json_decode($response, true);
            if (isset($response['description'])) {
                error_log("Request was successful: {$response['description']}\n");
            }
            $response = $response['result'];
        }

        return $response;
    }

    public function get_message($id)
    {
        $message_file = message_file($this->bot_name);
        $response = json_decode(file_get_contents($message_file), true);
        foreach ($response as $index => $json) {
            if ($json['id'] == $id) {
                return $json['message'];
            }
        }
    }

    public function get_button($id)
    {
        $button_file = button_file($this->bot_name);
        $response = json_decode(file_get_contents($button_file), true);
        foreach ($response as $index => $json) {
            if ($json['id'] == $id) {
                return $json['buttons'];
            }
        }
    }

    public function is_join_channel($channel_id, $user_id)
    {
        $handle = curl_init('https://api.telegram.org/bot' . $this->token . '/getChatMember');
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($handle, CURLOPT_POST, true);
        curl_setopt($handle, CURLOPT_TIMEOUT, 2);
        curl_setopt($handle, CURLOPT_POSTFIELDS, json_encode(array("chat_id" => "$channel_id", "user_id" => "$user_id")));
        curl_setopt($handle, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
        $status = json_decode(curl_exec($handle), true);
        curl_close($handle);

        if (!isset($status['result']['status'])) {
            return false;
        }

        if ($status['result']['status'] != "left") {
            return true;
        } else {
            return false;
        }
    }

    public function message_handler($text, $call)
    {
        if ($this->text === $text) {
            $call($this->user_id, $this->text);
            die();
        }
    }

    public function message_regex_handler($regex, $call)
    {

        if (preg_match($regex, $this->text, $text_data)) {
            $call($this->user_id, $text_data);
            die();
        }
    }

    public function command_handler($method, $call)
    {
        if ($this->type === 'command') {
            $command = explode(" ", $this->text)[0];
            if ($command == "/" . $method) {
                $call($this->user_id, $this->message);
                die();
            }
        }
        return false;
    }

    public function file_handler($file_ext, $call)
    {
        if ($this->type == "file" && in_array($this->file_ext, $file_ext)) {
            $call($this->user_id, $this->file_name);
            die();
        }
    }

    public function contact_handler($call)
    {
        if ($this->type === 'contact') {
            $call($this->user_id, $this->contact, $this->update);
            die();
        }
    }

    public function query_handler($callback, $call)
    {
        if ($this->type === 'callback') {
            if ($this->callback['data'] == $callback) {
                $call($this->callback['from']['id'], $this->callback['message']['message_id'], $this->callback['data'], $this->update['callback_query']);
                die();
            }
        }
    }

    public function query_regex_handler($callback, $call)
    {
        if ($this->type === 'callback') {
            if (preg_match($callback, $this->callback['data'], $callback_data)) {
                $call($this->callback['from']['id'], $this->callback['message']['message_id'], $callback_data[1], $this->update['callback_query']);
                die();
            }
        }
    }

    public function not_defined_handler($call)
    {
        $call($this->user_id, $this->text);
        die();
    }
}
