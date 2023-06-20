<?php

require "vendor/autoload.php";


$client = new \Saraf\JsonSnitchClient("http://localhost:9898");
$client->setConfig([
    "baseURL" => "https://jsonplaceholder.typicode.com"
]);
$client->get("/todos/1")
    ->then(function ($response) {
        echo json_encode($response, 128);
    });
