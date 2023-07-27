<?php

$debug = true;
$typesense_api_key = getenv("TYPESENSE_API_KEY");
$typesense_collection_name = "test-collection";

use Typesense\Client;

//----
require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../load.php';

$client = new Client(
    [
        'api_key'         => $typesense_api_key,
        'nodes'           => [
            [
                'host'     => 'localhost', // For Typesense Cloud use xxx.a1.typesense.net
                'port'     => '8108',      // For Typesense Cloud use 443
                'protocol' => 'http',      // For Typesense Cloud use https
            ],
        ],
        'connection_timeout_seconds' => 2,
    ]
);

$client->collections[$typesense_collection_name]->delete();
