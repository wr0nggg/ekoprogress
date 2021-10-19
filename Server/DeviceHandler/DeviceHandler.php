<?php
class DeviceHandler {
  private $decodedData;
  private $deviceID;
  private $devicePassword;

  private $serverDataFileName;
  private $metaDataFileName;
  private $dataCounterFileName;

  private $serverDataFilePath;
  private $metaDataFilePath;
  private $dataCounterFilePath;

  public function __construct($serverDataName, $metaDataName, $dataCounterName) {
    $this->serverDataFileName = $serverDataName;
    $this->metaDataFileName = $metaDataName;
    $this->dataCounterFileName = $dataCounterName;
  }

  public function Parse($inputData) {
    $this->decodedData = json_decode($inputData, true);
    $this->deviceID = $this->decodedData['Login']['DeviceID'];
    $this->devicePassword = $this->decodedData['Login']['DevicePassword'];
    $_SESSION['DeviceID'] = $this->deviceID;
    $this->SaveToDirectory("Datalogs/");
  }

  private function SaveToDirectory($directory) {
    $this->serverDataFilePath = $directory.$this->deviceID.$this->serverDataFileName;
    $this->metaDataFilePath = $directory.$this->deviceID.$this->metaDataFileName;
    $this->dataCounterFilePath = $directory.$this->deviceID.$this->dataCounterFileName;
    if (!file_exists($this->serverDataFilePath)) file_put_contents($this->serverDataFilePath, "");
    if (!file_exists($this->metaDataFilePath)) file_put_contents($this->metaDataFilePath, "");
  }

  public function Validate($filePath) {
    $loginData = json_decode(file_get_contents($filePath), true);
    foreach ($loginData as $login) {
      if ($login['DeviceID'] === $this->deviceID && $login['DevicePassword'] === $this->devicePassword) return true;
    }
    return false;
  }

  public function SaveJsonRecord() {
    if (filesize($this->serverDataFilePath)) $record = ",\n";
    else $record = "[\n  ";
    $record .= json_encode($this->decodedData['Record']);
    $record .= "\n]";
    $file = fopen($this->serverDataFilePath, "r+");
    fseek($file, -2, SEEK_END);
    fwrite($file, $record);
    fclose($file);
    file_put_contents($this->serverDataFilePath, json_encode(json_decode(file_get_contents($this->serverDataFilePath), true), JSON_PRETTY_PRINT));
  }

  public function SaveMetadata() {
    file_put_contents($this->metaDataFilePath, json_encode($this->decodedData['Meta'], JSON_PRETTY_PRINT));
  }

  public function UpdateCounter() {
    if (!file_exists($this->dataCounterFilePath)) file_put_contents($this->dataCounterFilePath, "1");
    else {
      $counter = file_get_contents($this->dataCounterFilePath, true);
      file_put_contents($this->dataCounterFilePath, (int)$counter + 1);
    }
  }

  public function InsertRecord($connection) {
    $query = "INSERT INTO records_".$this->deviceID."(date_time_esp,water_volume,power_consumption,water_contamination,membrane,airlift)";
    $query .= " VALUES('".$this->decodedData['Record']['Date']." ".$this->decodedData['Record']['Time']."',";
    $query .= $this->decodedData['Record']['PressureSensor'].",";
    $query .= $this->decodedData['Record']['CurrentSensor'].",";
    $query .= $this->decodedData['Record']['TurbiditySensor'].",";
    $query .= $this->decodedData['Status']['Membrane'].",";
    $query .= $this->decodedData['Status']['Airlift'];
    $query .= ");";
    mysqli_query($connection, $query);
  }
}
