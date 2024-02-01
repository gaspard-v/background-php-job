<?php
require_once __DIR__ . DIRECTORY_SEPARATOR . "background.php";

$clientConnection = new CloseConnectionBuilder();
$clientConnection->closeConnection();
sleep(10);
