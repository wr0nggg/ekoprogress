<?php
class DeviceMaintainer {
  private $connection;
  private $deviceID;
  private $waterContaminationDays = 20;

  public function __construct($connection, $deviceID) {
    $this->connection = $connection;
    $this->deviceID = $deviceID;
  }

  public function UpdateTable($table, $days) {
    $table .= "_".$this->deviceID;
    $query = "UPDATE $table SET change_real = CURRENT_DATE() WHERE change_real IS NULL;";
    mysqli_query($this->connection, $query);
    $days = "P".$days."D";
    $changeDate = new DateTime(date("Y-m-d"));
    $changePlan = new DateTime(date("Y-m-d"));
    $changeDate = $changeDate->format("Y-m-d");
    $changePlan->add(new DateInterval($days));
    $changePlan = $changePlan->format("Y-m-d");
    $query = "INSERT INTO $table (change_date, change_plan) VALUES ('$changeDate', '$changePlan');";
    mysqli_query($this->connection, $query);
  }

  public function GetTableContent($table) {
    $table .= "_".$this->deviceID;
    $query = "SELECT date_format(change_date, '%d.%m.%Y'), date_format(change_plan, '%d.%m.%Y'), date_format(change_real, '%d.%m.%Y') FROM $table ORDER BY id DESC LIMIT 10;";
    $result = mysqli_query($this->connection, $query);
    return mysqli_fetch_all($result);
  }

  private function GetRemainingDays($table) {
    $table .= "_".$this->deviceID;
    $query = "SELECT change_plan FROM $table ORDER BY id DESC LIMIT 1;";
    $result = mysqli_query($this->connection, $query);
    $changePlan = mysqli_fetch_assoc($result)['change_plan'];
    $changePlan = new DateTime(date($changePlan));
    $actualDate = new DateTime(date("Y-m-d"));
    return $actualDate->diff($changePlan, false)->format("%r%a");
  }

  public function GetMembraneNotification() {
    $membraneChangeCounter = $this->GetRemainingDays("membrane");
    $toReturn = array();
    if ($membraneChangeCounter > 0) $toReturn['text'] = "<span style='color:var(--white);'>Počet dní do výmeny membrán: $membraneChangeCounter</span>";
    else if ($membraneChangeCounter == 0) $toReturn['text'] = "<span style='color:#FFBE0A;'>V priebehu 24 hodín bude dúchadlo vypnuté - vymeňte membrány na dúchadle.</span>";
    else if ($membraneChangeCounter < 0) $toReturn['text'] = "<span style='color:#FFBE0A;'>Dúchadlo je vypnuté - kliknutím na tlačidlo potvrďte výmenu membrán a dúchadlo bude zapnuté.</span>";
    $toReturn['button'] = "<button id='membrane_button' type='submit' class='btn btn-outline-secondary btn-block' onclick='ConfirmChange(() => {membraneTable.Destroy();membraneTable.GetContent(true);})'>Potvrdiť výmenu membrán</button>";
    return $toReturn;
  }

  public function GetFilterNotification() {
    $filterChangeCounter = $this->GetRemainingDays("filter");
    $toReturn = array();
    if ($filterChangeCounter >= 0) $toReturn['text'] = "<span style='color:var(--white);'>Počet dní do vyčistenia filtra: $filterChangeCounter</span>";
    else $toReturn['text'] = "Filter nevyčistený ".abs($filterChangeCounter).". deň";
    $toReturn['button'] = "<button id='filter_button' type='submit' class='btn btn-outline-secondary btn-block' onclick='ConfirmChange(() => {filterTable.Destroy();filterTable.GetContent(true);})'>Potvrdiť výmenu filtra</button>";
    return $toReturn;
  }

  public function GetContaminationNotification($threshold) {
    $table = "records_".$this->deviceID;
    $query = "SELECT ROUND(AVG(water_contamination), 2) FROM $table WHERE date_time_esp > NOW() - INTERVAL 1 DAY;";
    if ($result = mysqli_query($this->connection, $query)) {
      $data = mysqli_fetch_all($result);
      if ($data[0][0] == NULL) return "<span style='color:var(--white);'>Žiadne záznamy.</span>";
      if ($data[0][0] > $threshold) return "<span style='color:var(--red);'>Vyčistená voda má vysoký zákal.</span>";
      if ($data[0][0] > $threshold/2) return "<span style='color:#FFBE0A;'>Vyčistená voda má mierny zákal.</span>";
      return "<span style='color:var(--white);'>Vyčistená voda je v norme.</span>";
    }
  }

  public function GetSedimentNotification($threshold, $day=0, $previous=0) {
    $table = "records_".$this->deviceID;
    $query = "SELECT ROUND(AVG(water_contamination), 2) FROM $table WHERE date_time_esp = NOW() - INTERVAL $day DAY;";
    if ($result = mysqli_query($this->connection, $query)) {
      $data = mysqli_fetch_all($result);
      if ($data[0][0] == NULL) return "<span style='color:var(--white);'>Žiadne záznamy.</span>";
      if ($data[0][0] > $threshold && $day <= $this->waterContaminationDays) return $this->GetSedimentNotification($threshold, $day+1, $data[0][0]);
      else return $day == 0 ? "<span style='color:var(--white);'>Koncentrácia kalu v norme.</span>" : "<span style='color:#FFBE0A;'>Počet dní od prekročenej koncentrácie kalu: $day - ODKALIŤ</span>";
    }
  }
}