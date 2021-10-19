<?php
require('DeviceHandler.php');

ini_set('precision', 10);
ini_set('serialize_precision', 10);

$deviceHandler = new DeviceHandler('/ServerData.json', '/MetaData.json', '/DataCounter.txt');
$encoded = file_get_contents('php://input');
$deviceHandler->Parse($encoded);
if (!$deviceHandler->Validate('DeviceLoginList.json')) die();
$deviceHandler->SaveJsonRecord();
$deviceHandler->SaveMetadata();
$deviceHandler->UpdateCounter();

$connection = mysqli_connect("mysql57.r2.websupport.sk", "5b7l58ch", "Pz4*J@KzO-", "5b7l58ch", 3311);
if (!$connection) die();
$deviceHandler->InsertRecord($connection);
mysqli_close($connection);
