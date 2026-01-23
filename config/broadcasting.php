<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Broadcaster
    |--------------------------------------------------------------------------
    |
    | This option controls the default broadcaster that will be used by the
    | framework when an event needs to be broadcast. You may set this to
    | any of the broadcasters defined in the "connections" array below.
    |
    | Supported: "pusher", "reverb", "redis", "log", "null"
    |
    */

    'default' => env('BROADCAST_CONNECTION', 'null'),

    /*
    |--------------------------------------------------------------------------
    | Broadcast Connections
    |--------------------------------------------------------------------------
    |
    | Here you may define all of the broadcast connections that will be used
    | to broadcast events to other services. Examples of each available type
    | of connection are provided, though you may add others as you wish.
    |
    | Laravel's event broadcasting system makes it easy to broadcast events
    | over WebSockets for real-time application development. By default, an
    | event is broadcast to every client connected to the application.
    |
    */

    'connections' => [

        'pusher' => [
            'driver' => 'pusher',
            'key' => env('PUSHER_APP_KEY'),
            'secret' => env('PUSHER_APP_SECRET'),
            'app_id' => env('PUSHER_APP_ID'),
            'options' => array_filter([
                'cluster' => env('PUSHER_APP_CLUSTER'),
                'host' => env('PUSHER_HOST') ?: null,
                'port' => env('PUSHER_PORT') ? (int) env('PUSHER_PORT') : null,
                'scheme' => env('PUSHER_SCHEME') ?: null,
                'encrypted' => true,
                'useTLS' => env('PUSHER_SCHEME', 'https') === 'https',
            ], function ($value) {
                return !is_null($value) && $value !== '';
            }),
            'client_options' => [ // Pusher library client options.
                // 'timeout' => 30,
            ],
        ],

        'reverb' => [
            'driver' => 'reverb',
            'host' => env('REVERB_HOST', env('APP_URL') ? parse_url(env('APP_URL'), PHP_URL_HOST) : '127.0.0.1'),
            'port' => env('REVERB_PORT', 8080),
            'app_id' => env('REVERB_APP_ID', '_placeholder'),
            'key' => env('REVERB_APP_KEY', '_placeholder'),
            'secret' => env('REVERB_APP_SECRET', '_placeholder'),
            'options' => [
                'cluster' => env('REVERB_CLUSTER', 'mt1'),
                'host' => env('REVERB_HOST', '127.0.0.1'),
                'port' => env('REVERB_PORT', 8080),
                'scheme' => env('REVERB_SCHEME', 'http'),
                'encrypted' => env('REVERB_SCHEME') === 'https',
                'useTLS' => env('REVERB_SCHEME') === 'https',
                'protocol' => env('REVERB_SCHEME', 'http'),
                'auth_endpoint' => env('REVERB_SCHEME', 'http').'://'.env('REVERB_HOST', '127.0.0.1').':'.env('REVERB_PORT', 8080).'/api/broadcasting/auth',
                'websocket_host' => env('REVERB_HOST', '127.0.0.1'),
                'websocket_port' => env('REVERB_PORT', 8080),
                'websocket_path' => env('REVERB_PATH', ''),
            ],
        ],

        'redis' => [
            'driver' => 'redis',
            'connection' => 'default',
        ],

        'log' => [
            'driver' => 'log',
        ],

        'null' => [
            'driver' => 'null',
        ],

    ],

];
