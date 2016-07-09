<?php

return [
    'length' => env('SECURIMAGE_LENGTH', 6),
    'width'  => env('SECURIMAGE_WIDTH', 215),
    'height'  => env('SECURIMAGE_HEIGHT', 80),
    'perturbation' => env('SECURIMAGE_PERTURBATION', .85),
    'case_sensitive' => env('SECURIMAGE_CASE_SENSITIVE', false),
    'num_lines' => env('SECURIMAGE_NUM_LINES', 0),
    'charset' => env('SECURIMAGE_CHARSET', 'ABCDEFGHKLMNPRSTUVWYZabcdefghklmnprstuvwyz23456789'),
    'signature' => env('SECURIMAGE_SIGNATURE', null),
    'securimage_path' => env('SECURIMAGE_PATH', '/vendor/securimage/'),
    'audio_icon_url' => env('SECURIMAGE_AUDIO_ICON_URL', '/vendor/securimage/images/audio_icon.png'),
    'loading_icon_url' => env('SECURIMAGE_LOADING_ICON_URL', '/vendor/securimage/images/loading.png'),
    'refresh_icon_url' => env('SECURIMAGE_REFRESH_ICON_URL', '/vendor/securimage/images/refresh.png'),

    // Set false if you want to user your own input text
    'show_text_input' => env('SECURIMAGE_SHOW_TEXT_INPUT', true),
];
