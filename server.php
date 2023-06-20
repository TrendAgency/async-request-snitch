<?php

require "vendor/autoload.php";

$server = new \Saraf\JsonSnitchServer();
$server->start("0.0.0.0", 9898);