<?php


if (!function_exists('dd')) {
    function dd($data, $deep = false)
    {
        echo '<pre style="color: #8BC34A;word-wrap:break-word;background: black;font-size: 2.2em;padding: 15px;border-radius: 5px;">';

        if ($deep) {
            var_dump($data);
        } else {
            print_r($data);
        }

        echo '</pre>';
        die;
    }
}
