<?php
session_start();
require("connection.php");
require("DeviceManager/DeviceManager.php");

function GetStatusFromDatabase($connection, $deviceID) {
  $filePath = "DeviceHandler/Datalogs/" . $deviceID . "/MetaData.json";
  $interval = "6";
  if (file_exists($filePath)) {
    $metaData = json_decode(file_get_contents($filePath), true);
    $interval = (string)(24/(int)$metaData['RequestsPerDay']);
  }
  $query = "SELECT membrane, airlift FROM records_".$deviceID." WHERE date_time_esp < NOW() AND date_time_esp > NOW() - INTERVAL $interval HOUR;";
  $result = mysqli_query($connection, $query);
  $data = mysqli_fetch_assoc($result);
  if ($data == NULL) $data = array("membrane" => false, "airlift" => false, "communication" => true);
  else $data += ["communication" => false];
  return $data;
}

$data = DeviceManager::Authenticate($connection);
$covID = $data['device_id'];

$statuses = GetStatusFromDatabase($connection, $covID);
$covStatus = !($statuses["membrane"] || $statuses["airlift"] || $statuses["communication"]);
?>

<!DOCTYPE html>
<html lang="sk">

<head>
  <?php require("html/head.html"); ?>
  <title>Ekoprogres | Monitoring ČOV</title>
</head>

<body>
  <div class="container top-panel cast-shadow">
    <div class="container">
      <div class="row">
        <?php include("html/logo.html"); ?>
      </div>
    </div>

    <div class="container">
      <div class="row">
        <div class="col-lg">
          <div class="top-panel-item cast-shadow" style="background: var(--background-tertiary);">
            MONITORING: <span class="bold-text"><?php echo $covID; ?></span>
          </div>
        </div>
        <div class="col-lg">
          <a href="maintenance.php" style="text-decoration: none;">
            <div class="top-panel-item cast-shadow button" style="background: #00AAE1; color: whitesmoke;">
              <i class="fas fa-tools"></i> Servis a údržba
            </div>
          </a>
        </div>
        <div class="col-lg">
          <a href="DeviceHandler/Datalogs/<?php echo $covID; ?>/SETTINGS.TXT" download="" style="text-decoration: none;">
            <div class="top-panel-item cast-shadow button" style="background: #FFBE0A; color: #222D36;">
              <i class="fas fa-download"></i> Konfiguračný súbor
            </div>
          </a>
        </div>
        <div class="col-lg">
          <a href="logout.php" style="text-decoration: none;">
            <div class="top-panel-item cast-shadow button" style="background: #FF1E3C; color: whitesmoke;">
              <i class="fas fa-sign-out-alt"></i> Odhlásiť
            </div>
          </a>
        </div>
      </div>
    </div>

    <hr>

    <div class="container">
      <div class="row">
        <?php
        if ($covStatus) {
          echo "
            <div class='col-lg'>
            <div class='top-panel-item cast-shadow top-panel-status' style='border-color: #27EF9F; color: #27EF9F;'>
              STATUS ČOV: <span class='bold-text'>BEZ PORUCHY </span><i class='fas fa-check-circle'></i>
            </div>
          </div>";
        } else {
          echo "
            <div class='col-lg'>
            <div class='top-panel-item cast-shadow top-panel-status failure-color'>
              STATUS ČOV: <span class='bold-text'>PORUCHA </span><i class='fas fa-times-circle'></i>
            </div>
          </div>";
        }
        if ($statuses["membrane"]) {
          echo "
            <div class='col-lg'>
            <div class='top-panel-item cast-shadow top-panel-status bold-text failure-color'>
              Membrány
            </div>
          </div>";
        }
        if ($statuses["airlift"]) {
          echo "
            <div class='col-lg'>
            <div class='top-panel-item cast-shadow top-panel-status bold-text failure-color'>
              Mamutka
            </div>
          </div>";
        }
        if ($statuses["communication"]) {
          echo "
            <div class='col-lg'>
            <div class='top-panel-item cast-shadow top-panel-status bold-text failure-color'>
              Komunikácia
            </div>
          </div>";
        }
        ?>
      </div>
    </div>
  </div>

  <div style="height: 10px;"></div>

  <div class="container">
    <div class="row">
      <div class="col-lg plot-panel cast-shadow" style="margin-right: 5px;">
        <div class="plot-title-panel cast-shadow">
          <div class="bold-text">MNOŽSTVO VYČISTENEJ VODY</div>
          <div id="water-volume-sum" style="color: var(--white);">
            ---
          </div>
          <div id="water-volume-avg" style="color: var(--white);">
            ---
          </div>
        </div>
        <div style="height: 360px; margin-top: 15px;">
          <canvas id="water-volume-plot"></canvas>
        </div>
        <div>
          <div class="row">
            <div class="toggle-radio-watervolume">
              <input type="radio" id="day-toggle-watervolume" onclick="WaterVolumeUpdate('day');" name="toggle-option-watervolume" checked>
              <input type="radio" id="month-toggle-watervolume" onclick="WaterVolumeUpdate('month');" name="toggle-option-watervolume">
              <input type="radio" id="year-toggle-watervolume" onclick="WaterVolumeUpdate('year');" name="toggle-option-watervolume">
              <div class="toggle-option-slider-watervolume"></div>
              <label for="day-toggle-watervolume"><span class="day-toggle-span">DEŇ</span></label>
              <label for="month-toggle-watervolume"><span class="month-toggle-span">MESIAC</span></label>
              <label for="year-toggle-watervolume"><span class="year-toggle-span">ROK</span></label>
            </div>
          </div>
        </div>
      </div>

      <div class="col-lg plot-panel cast-shadow" style="margin-left: 5px; margin-right: 5px;">
        <div class="plot-title-panel cast-shadow">
          <div class="bold-text">SPOTREBOVANÁ ELEKTRICKÁ ENERGIA</div>
          <div id="power-consumption-sum" style="color: var(--white);">
            ---
          </div>
          <div id="power-consumption-avg" style="color: var(--white);">
            ---
          </div>
        </div>
        <div style="height: 360px; margin-top: 15px;">
          <canvas id="power-consumption-plot"></canvas>
        </div>
        <div>
          <div class="row">
            <div class="toggle-radio-powerconsumption">
              <input type="radio" id="day-toggle-powerconsumption" onclick="PowerConsumptionUpdate('day');" name="toggle-option-powerconsumption" checked>
              <input type="radio" id="month-toggle-powerconsumption" onclick="PowerConsumptionUpdate('month');" name="toggle-option-powerconsumption">
              <input type="radio" id="year-toggle-powerconsumption" onclick="PowerConsumptionUpdate('year');" name="toggle-option-powerconsumption">
              <div class="toggle-option-slider-powerconsumption"></div>
              <label for="day-toggle-powerconsumption"><span class="day-toggle-span">DEŇ</span></label>
              <label for="month-toggle-powerconsumption"><span class="month-toggle-span">MESIAC</span></label>
              <label for="year-toggle-powerconsumption"><span class="year-toggle-span">ROK</span></label>
            </div>
          </div>
        </div>
      </div>

      <div class="col-lg plot-panel cast-shadow" style="margin-left: 5px;">
        <div class="plot-title-panel cast-shadow">
          <div class="bold-text">KONCENTRÁCIA KALU</div>
          <div id="water-contamination-sum" style="color: var(--white);">
            ---
          </div>
          <div id="water-contamination-avg" style="color: var(--white);">
            ---
          </div>
        </div>
        <div style="height: 360px; margin-top: 15px;">
          <canvas id="water-contamination-plot"></canvas>
        </div>
        <div>
          <div class="row">
            <div class="toggle-radio-watercontamination">
              <input type="radio" id="day-toggle-watercontamination" onclick="WaterContaminationUpdate('day');" name="toggle-option-watercontamination" checked>
              <input type="radio" id="month-toggle-watercontamination" onclick="WaterContaminationUpdate('month');" name="toggle-option-watercontamination">
              <input type="radio" id="year-toggle-watercontamination" onclick="WaterContaminationUpdate('year');" name="toggle-option-watercontamination">
              <div class="toggle-option-slider-watercontamination"></div>
              <label for="day-toggle-watercontamination"><span class="day-toggle-span">DEŇ</span></label>
              <label for="month-toggle-watercontamination"><span class="month-toggle-span">MESIAC</span></label>
              <label for="year-toggle-watercontamination"><span class="year-toggle-span">ROK</span></label>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

  <div style="height: 10px;"></div>
  <div class="container top-panel cast-shadow">
    <div class="d-flex align-items-center justify-content-center" style="height: 50px;">
      <span id="color-mode-text" style="color: var(--white); font-weight: bold;">PREPNÚŤ NA SVETLÝ REŽIM</span>
      &ensp;&ensp;
      <label class="switch">
        <input type="checkbox" id="color-mode-switch" onclick="Swap(ColorMode);">
        <span class="slider round"></span>
      </label>
    </div>
  </div>
  <div style="height: 10px;"></div>
</body>

<footer>
  <?php require("html/footer.html"); ?>
</footer>

<script>
  let covID = '<?php echo $covID; ?>';
  let color = localStorage.getItem("check-state") == "false" ? 'white' : 'black';

  let waterVolumePlot = new Plot({
    canvasID: 'water-volume-plot',
    covID: covID,
    variableType: 'water_volume',
    unit: 'l',
    backgroundColor: '#0A6EFF',
    color: color
  });
  let waterVolumeTitle = new Title({
    sumTitleID: 'water-volume-sum',
    avgTitleID: 'water-volume-avg',
    covID: covID,
    variableType: 'water_volume',
    unit: 'l'
  });

  let powerConsumptionPlot = new Plot({
    canvasID: 'power-consumption-plot',
    covID: covID,
    variableType: 'power_consumption',
    unit: 'kWh',
    backgroundColor: '#FFBE0A',
    color: color
  });
  let powerConsumptionTitle = new Title({
    sumTitleID: 'power-consumption-sum',
    avgTitleID: 'power-consumption-avg',
    covID: covID,
    variableType: 'power_consumption',
    unit: 'kWh'
  });

  let waterContaminationPlot = new Plot({
    canvasID: 'water-contamination-plot',
    covID: covID,
    variableType: 'water_contamination',
    unit: 'mg/l',
    backgroundColor: '#DB7725',
    color: color
  });
  let waterContaminationTitle = new Title({
    sumTitleID: 'water-contamination-sum',
    avgTitleID: 'water-contamination-avg',
    covID: covID,
    variableType: 'water_contamination',
    unit: 'mg/l'
  });

  let plots = [waterVolumePlot, powerConsumptionPlot, waterContaminationPlot];

  function WaterVolumeUpdate(timePeriod) {
    color = localStorage.getItem("check-state") == "false" ? 'white' : 'black';
    waterVolumePlot.Update(timePeriod, color);
    waterVolumeTitle.Update(timePeriod, 'SUM');
    waterVolumeTitle.Update(timePeriod, 'AVG');
  }

  function PowerConsumptionUpdate(timePeriod) {
    color = localStorage.getItem("check-state") == "false" ? 'white' : 'black';
    powerConsumptionPlot.Update(timePeriod, color);
    powerConsumptionTitle.Update(timePeriod, 'SUM');
    powerConsumptionTitle.Update(timePeriod, 'AVG');
  }

  function WaterContaminationUpdate(timePeriod) {
    color = localStorage.getItem("check-state") == "false" ? 'white' : 'black';
    waterContaminationPlot.Update(timePeriod, color);
    waterContaminationTitle.Update(timePeriod, 'SUM');
    waterContaminationTitle.Update(timePeriod, 'AVG');
  }

  function ColorMode() {
    plots.forEach(plot => {
      plot.ColorMode(color);
    });
  }

  WaterVolumeUpdate('day');
  PowerConsumptionUpdate('day');
  WaterContaminationUpdate('day');
</script>

<script src="js/colormode.js"></script>

</html>