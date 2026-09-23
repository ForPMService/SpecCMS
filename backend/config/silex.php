<?php

return [
    'api_url' => env('SILEX_API_URL', 'http://silex:6805'),
    'editor_url' => env('SILEX_EDITOR_URL', 'http://localhost:6805'),
    'connector_id' => env('SILEX_CONNECTOR_ID', 'fs-storage'),
    'lang' => env('SILEX_LANG', 'en'),
];
