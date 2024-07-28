<?php

use Illuminate\Support\Str;

/*
 * base_path
 * config_path
 * dd (die and dump)
 * throw_when
 */

if (!function_exists('base_path')) {
    function base_path($path = '')
    {
        return __DIR__ . "/../{$path}";
    }
}

if (!function_exists('config_path')) {
    function config_path($path = '')
    {
        return base_path("config/{$path}");
    }
}

if (!function_exists('storage_path')) {
    function storage_path($path = '')
    {
        return base_path("storage/{$path}");
    }
}

if (!function_exists('message_file')) {
    function message_file($bot_name = '')
    {
        return storage_path("json/{$bot_name}/message.json");
    }
}

if (!function_exists('button_file')) {
    function button_file($bot_name = '')
    {
        return storage_path("json/{$bot_name}/button.json");
    }
}

if (!function_exists('dd')) {
    function dd()
    {
        array_map(function ($content) {
            echo "<pre>";
            var_dump($content);
            echo "</pre>";
            echo "<hr>";
        }, func_get_args());

        die;
    }
}

if (!function_exists('throw_when')) {
    function throw_when(bool $fails, string $message, string $exception = Exception::class)
    {
        if (!$fails) return;

        throw new $exception($message);
    }
}

if (!function_exists('config')) {
    function config($path = null)
    {
        $config = [];
        $folder = scandir(config_path());
        $config_files = array_slice($folder, 2, count($folder));

        foreach ($config_files as $file) {
            throw_when(
                Str::after($file, '.') !== 'php',
                'Config files must be .php files'
            );

            data_set($config, Str::before($file, '.php'), require config_path($file));
        }

        return data_get($config, $path);
    }
}

if (!function_exists('persian_number')) {
    function persian_number($text = '', $reverse = false)
    {
        $englishNumbers = range(0, 9);
        $persianNumbers = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
        if($reverse == false) {
            return str_replace($englishNumbers, $persianNumbers, $text);
        }
        else{
            return str_replace($persianNumbers, $englishNumbers, $text);
        }
    }
}