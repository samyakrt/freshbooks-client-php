<?php

/*
 * You can place your custom package configuration in here.
 */
return [
    'client_id' => env('FRESHBOOKS_CLIENT_ID'),
    'client_secret' => env('FRESHBOOKS_CLIENT_SECRET'),
    'freshbook_uri_redirect' => env('APP_URL') . '/redirect',
    'freshbook_uri' => env('FRESHBOOK_URI'),
    'app_url' => env('APP_URL'),
    'account_id' => env('ACCOUNT_ID', null),
    'access_token' => null,
    'refresh_token' => null,
];