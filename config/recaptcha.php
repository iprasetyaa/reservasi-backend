<?php

return [
    'secret_key' => env('SECRET_RECAPTHCA_KEY'),
    'site_secret' => env('GOOGLE_RECAPTCHA_KEY'),
    'base_url' => 'https://www.google.com/recaptcha/api/siteverify?secret=' . env('SECRET_RECAPTHCA_KEY') . 'response=',
];
