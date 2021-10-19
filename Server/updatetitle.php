<?php
require("connection.php");

function GetTitleData($connection, $covID, $timePeriod, $variableType, $func) {
  $tableName = "records_" . $covID;
  $value = [];

  if ($timePeriod == "day") {
    $query = "SELECT ROUND($func($variableType), 2) FROM $tableName WHERE date_time_esp > NOW() - INTERVAL 1 DAY;";
    if ($result = mysqli_query($connection, $query)) {
      $returnedData = mysqli_fetch_all($result);
      array_push($value, $returnedData[0][0]);
    }
    if ($value[0] == null) $value = array("0.0");
    return array('value' => $value);
  }
  else if ($timePeriod == "month") {
    $query = "SELECT ROUND($func($variableType), 2) FROM $tableName WHERE date_time_esp > CURDATE() - INTERVAL 1 MONTH;";
    if ($result = mysqli_query($connection, $query)) {
      $returnedData = mysqli_fetch_all($result);
      array_push($value, $returnedData[0][0]);
    }
    if ($value[0] == null) $value = array("0.0");
    return array('value' => $value);
  }
  else if ($timePeriod == "year") {
    $query = "SELECT ROUND($func($variableType), 2) FROM $tableName WHERE date_time_esp > CURDATE() - INTERVAL 1 YEAR;";
    if ($result = mysqli_query($connection, $query)) {
      $returnedData = mysqli_fetch_all($result);
      array_push($value, $returnedData[0][0]);
    }
    if ($value[0] == null) $value = array("0.0");
    return array('value' => $value);
  }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $sqlResult = GetTitleData($connection, $_POST['covID'], $_POST['timePeriod'], $_POST['variableType'], $_POST['func']);
  echo json_encode(array(
    'value' => $sqlResult['value'],
    'variableType' => $_POST['variableType']
  ));
  die();
}
