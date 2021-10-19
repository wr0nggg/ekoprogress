<?php
require("connection.php");
include("maintenance.php");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  if ($_POST['update'] == 'true') $deviceMaintainer->UpdateTable($_POST['table'], $_POST['days']);
  $data = $deviceMaintainer->GetTableContent($_POST['table']);
  if ($data[0][2] == NULL) $data[0][2] = "---";
  echo json_encode($data);
  die();
}
?>
