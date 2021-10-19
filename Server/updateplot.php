<?php
require("connection.php");

function ChangeDateFormat($date, $timePeriod) {
  if ($timePeriod == "day") return date("H.i", strtotime($date));
  if ($timePeriod == "month") return date("d.m.Y", strtotime($date));
  if ($timePeriod == "year") return date("m.Y", strtotime($date));
}

function GetPlotData($connection, $covID, $timePeriod, $variableType) {
  $tableName = "records_" . $covID;
  $values = [];
  $labels = [];

  if ($timePeriod == "day") {
    $query = "SELECT $variableType, date_time_esp FROM $tableName WHERE date_time_esp > NOW() - INTERVAL 1 DAY ORDER BY date_time_esp;";
    if ($result = mysqli_query($connection, $query)) {
      $numOfRows = mysqli_num_rows($result);
      $returnedData = mysqli_fetch_all($result);
      for ($i = 0; $i < $numOfRows; $i++) {
        array_push($values, (float)$returnedData[$i][0]);
        array_push($labels, ($returnedData[$i][1] == "") ? "---" : ChangeDateFormat((string)$returnedData[$i][1], $timePeriod));
      }
    }
    return array('labels' => $labels, 'values' => $values);
  }
  else if ($timePeriod == "month") {
    for ($day = 27; $day >= 0; $day--) {
      $query = "SELECT ROUND(AVG($variableType), 2), date_time_esp FROM $tableName WHERE DATE(date_time_esp) = DATE(CURRENT_DATE()) - INTERVAL $day DAY;";
      if ($result = mysqli_query($connection, $query)) {
        $returnedData = mysqli_fetch_assoc($result);
        foreach ($returnedData as $key => $value) {
          if ($key == "ROUND(AVG($variableType), 2)") array_push($values, (float)$value);
          else array_push($labels, ($value == "") ? "---" : ChangeDateFormat($value, $timePeriod));
        }
      }
    }
    return array('labels' => $labels, 'values' => $values);
  }
  else if ($timePeriod == "year") {
    for ($month = 11; $month >= 0; $month--) {
      $query = "SELECT ROUND(AVG($variableType), 2), date_time_esp FROM $tableName WHERE MONTH(date_time_esp) = MONTH(CURRENT_DATE() - INTERVAL $month MONTH) AND YEAR(date_time_esp) = YEAR(CURRENT_DATE() - INTERVAL $month MONTH);";
      if ($result = mysqli_query($connection, $query)) {
        $returnedData = mysqli_fetch_assoc($result);
        foreach ($returnedData as $key => $value) {
          if ($key == "ROUND(AVG($variableType), 2)") array_push($values, (float)$value);
          else array_push($labels, ($value == "") ? "---" : ChangeDateFormat($value, $timePeriod));
        }
      }
    }
    return array('labels' => $labels, 'values' => $values);
  }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $sqlResult = GetPlotData($connection, $_POST['covID'], $_POST['timePeriod'], $_POST['variableType']);
  echo json_encode(array(
    'labels' => $sqlResult['labels'],
    'values' => $sqlResult['values'],
    'variableType' => $_POST['variableType']
  ));
  die();
}
