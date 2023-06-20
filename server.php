<?php

use Saraf\JsonSnitchServer;

require "vendor/autoload.php";

$server = new JsonSnitchServer();

echo "Start Running on http://0.0.0.0:9898" . PHP_EOL;
$server->start("0.0.0.0", 9898);