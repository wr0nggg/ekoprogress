<?php
class DeviceManager {
  private $connection;
  private $deviceID;
  private $devicePassword;

  public function __construct($connection, $deviceID, $devicePassword) {
    $this->connection = $connection;
    $this->deviceID = strtoupper($deviceID);
    $this->devicePassword = $devicePassword;
  }

  public function SignUp() {
    $this->InsertDeviceIntoDatabase();
    $this->CreateDeviceTables();
    $this->CreateDeviceDirectory();
    $this->AddDeviceToJson();
    $this->InitializeMaintenanceTable("membrane_".$this->deviceID, 550);
    $this->InitializeMaintenanceTable("filter_".$this->deviceID, 90);
  }

  public function Login() {
    if (empty($this->deviceID)) return "Location: login.php?wrongname=emptyDeviceName";
    if (empty($this->devicePassword)) return "Location: login.php?wrongpass=emptyPassword";
    if (!empty($this->deviceID) && !empty($this->devicePassword)) {
      $query = "SELECT * FROM devices WHERE device_id = '$this->deviceID' AND device_password = '$this->devicePassword';";
      $result = mysqli_query($this->connection, $query);
      if ($result) {
        if (mysqli_num_rows($result) > 0) {
          $data = mysqli_fetch_assoc($result);
          if ($data['device_id'] === $this->deviceID && $data['device_password'] === $this->devicePassword) {
            $_SESSION['device_id'] = $data['device_id'];
            return "Location: index.php";
          }
        }
      }
    }
    return "Location: login.php?error=invalidCredentials";
  }

  public static function Authenticate($connection) {
    if (isset($_SESSION['device_id'])) {
      $id = $_SESSION['device_id'];
      $query = "SELECT * FROM devices WHERE device_id = '$id';";
      $result = mysqli_query($connection, $query);
      if ($result && mysqli_num_rows($result) > 0) {
        return mysqli_fetch_assoc($result);
      }
    }
    header("Location: login.php");
    die();
  }

  public function InsertDeviceIntoDatabase() {
    if (!empty($this->deviceID) && !empty($this->devicePassword)) {
      $query = "INSERT INTO devices (device_id, device_password) VALUES ('$this->deviceID', '$this->devicePassword');";
      if (!mysqli_query($this->connection, $query)) {
        header("Location: signup.php?error=duplicateAccount");
        die();
      }
    }
  }

  public function CreateDeviceTables() {
    //--- records table
    $query = "CREATE TABLE IF NOT EXISTS records_".$this->deviceID."(
      date_time_esp DATETIME,
      date_time_server DATETIME DEFAULT NOW(),
      water_volume FLOAT DEFAULT 0,
      power_consumption FLOAT DEFAULT 0,
      water_contamination FLOAT DEFAULT 0,
      membrane SMALLINT,
      airlift SMALLINT,
      PRIMARY KEY (date_time_esp)
    )";
    mysqli_query($this->connection, $query);
  
    //--- membrane table
    $query = "CREATE TABLE IF NOT EXISTS membrane_".$this->deviceID."(
      id INT NOT NULL AUTO_INCREMENT,
      change_date DATE,
      change_plan DATE,
      change_real DATE,
      PRIMARY KEY (id)
    );";
    mysqli_query($this->connection, $query);

    //--- filter table
    $query = "CREATE TABLE IF NOT EXISTS filter_".$this->deviceID."(
      id INT NOT NULL AUTO_INCREMENT,
      change_date DATE,
      change_plan DATE,
      change_real DATE,
      PRIMARY KEY (id)
    );";
    mysqli_query($this->connection, $query);
  }

  public function InitializeMaintenanceTable($table, $days) {
    $days = "P".$days."D";
    $changeDate = new DateTime(date("Y-m-d"));
    $changePlan = new DateTime(date("Y-m-d"));
    $changePlan->add(new DateInterval($days));
    $changeDate = $changeDate->format("Y-m-d");
    $changePlan = $changePlan->format("Y-m-d");
    $query = "INSERT INTO $table (change_date, change_plan) VALUES ('$changeDate', '$changePlan');";
    mysqli_query($this->connection, $query);
  }

  public function CreateDeviceDirectory($fileName="SETTINGS.TXT", $requestsPerDay=4, $maxLogCount=1460) {
    $url = "http://";
    $url .= $_SERVER['HTTP_HOST'];
    $url .= $_SERVER['REQUEST_URI'];
    $url = str_replace("/signup.php", "/", $url);
  
    $config = "DeviceID=" . $this->deviceID . "\n";
    $config .= "DevicePassword=" . $this->devicePassword . "\n";
    $config .= "RequestsPerDay=" . $requestsPerDay . "\n";
    $config .= "MaxDataLogCount=" . $maxLogCount . "\n";
    $config .= "NetworkSSID=NAZOV_SIETE\n";
    $config .= "NetworkPassword=KLUC_SIETE\n";
    $config .= "URL=" . $url . "DeviceHandler/request.php\n";
    $config .= "NTP=2.sk.pool.ntp.org\n";
    $config .= "GMT=+1\n";
    $config .= "CEST=true\n";
  
    $directory = "DeviceHandler/Datalogs";
    $filePath = $directory . "/" . $this->deviceID . "/" . $fileName;
    if (!is_dir($directory)) mkdir($directory);
    if (!is_dir($directory . "/" . $this->deviceID)) mkdir($directory . "/" . $this->deviceID);
    file_put_contents($filePath, $config);
  }

  public function AddDeviceToJson($filePath = 'DeviceHandler/DeviceLoginList.json') {
    if (!file_exists($filePath)) file_put_contents($filePath, "");
    if (filesize($filePath)) $record = ",\n";
    else $record = "[\n  ";
    $record .= "{\"DeviceID\":\"" . $this->deviceID . "\",";
    $record .= "\"DevicePassword\":\"" . $this->devicePassword . "\"}";
    $record .= "\n]";
    $file = fopen($filePath, "r+");
    fseek($file, -2, SEEK_END);
    fwrite($file, $record);
    fclose($file);
    file_put_contents($filePath, json_encode(json_decode(file_get_contents($filePath), true), JSON_PRETTY_PRINT));
  }
}