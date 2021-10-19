<?php
session_start();
require("connection.php");
require("DeviceManager/DeviceManager.php");
require("DeviceMaintainer/DeviceMaintainer.php");

$data = DeviceManager::Authenticate($connection);
$covID = $data['device_id'];
$deviceMaintainer = new DeviceMaintainer($connection, $covID);

$membraneNotification = $deviceMaintainer->GetMembraneNotification();
$filterNotification = $deviceMaintainer->GetFilterNotification();
$contaminationNotification = $deviceMaintainer->GetContaminationNotification(2500);
$sedimentNotification = $deviceMaintainer->GetSedimentNotification(2600);
?>

<!DOCTYPE html>
<html lang="sk">

<head>
  <?php require("html/head.html"); ?>
  <title>Ekoprogres | Servis a údržba</title>
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
            SERVIS A ÚDRŽBA: <span class="bold-text"><?php echo $covID; ?></span>
          </div>
        </div>
        <div class="col-lg">
          <a href="index.php" style="text-decoration: none;">
            <div class="top-panel-item cast-shadow button" style="background: #00AAE1; color: whitesmoke;">
              <i class="fas fa-chart-bar"></i> Monitoring
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
  </div>

  <div style="height: 10px;"></div>

  <div class="container">
    <!--    PRVY RIADOK OBSAHUJUCI DVA PRVKY | row = ■ ■    -->
    <div class="row">
      <div class="col-lg plot-panel cast-shadow">
        <div class="plot-title-panel cast-shadow">
          <div class="bold-text">VÝMENA MEMBRÁN</div>
          <div class="text-center">
            <?php echo $membraneNotification['text']; ?>
          </div>
        </div>
        <div class="mx-auto maintenance-button">
          <?php echo $membraneNotification['button']; ?>
        </div>
        <table id="membrane" class="display table " style="color: var(--white);" width="100%"></table>
      </div>

      <div class="col-lg plot-panel cast-shadow">
        <div class="plot-title-panel cast-shadow">
          <div class="bold-text">ČISTENIE FILTRA</div>
          <div class="text-center">
            <?php echo $filterNotification['text']; ?>
          </div>
        </div>
        <div class="mx-auto maintenance-button">
          <?php echo $filterNotification['button']; ?>
        </div>
        <table id="filter" class="display table " style="color: var(--white);" width="100%"></table>
      </div>
    </div>

    <!--    DRUHY RIADOK OBSAHUJUCI DVA PRVKY | row = ■ ■    -->
    <div class="row">
      <div class="col-lg plot-panel cast-shadow">
        <div class="plot-title-panel cast-shadow">
          <div class="bold-text">KVALITA VYČISTENEJ VODY</div>
          <div class="text-center">
            <?php echo $contaminationNotification; ?>
          </div>
        </div>
        <div style="height: 15px;"></div>
      </div>

      <div class="col-lg plot-panel cast-shadow">
        <div class="plot-title-panel cast-shadow">
          <div class="bold-text">ODKALOVANIE</div>
          <div class="text-center">
            <?php echo $sedimentNotification; ?>
          </div>
        </div>
        <div style="height: 15px;"></div>
      </div>
    </div>
  </div>
  <div class="container">
    <div class="row">
      <div class="col-lg plot-panel cast-shadow">
        <div class="plot-title-panel cast-shadow">
          <div class="bold-text">NÁVODY</div>
          <div class="text-center">
          </div>
        </div>
        <div class="dropdown iframe-class" style="text-align: center; margin:10px;justify-content: center;">
          <button type="button" id="dropdownMenuLink" style="color:var(--white);" class="maintenance-button btn shadow-none dropdown-toggle btn-block" data-toggle="dropdown">
            Vybrať návod
          </button>
          <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
            <a class="dropdown-item" onclick="switchVideo(this.innerHTML ,'https://www.youtube.com/embed/Sk-U8ruIQyA')">Výmena membrán</a>
            <a class="dropdown-item" onclick="switchVideo(this.innerHTML ,'https://www.youtube.com/embed/IteRJQK__PI')">Výmena filtra</a>
          </div>
        </div>
        <iframe class="container" id="video" width="0" height="0" src="about:blank" frameborder="0" allow="accelerometer;clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
        <div style="height: 15px;"></div>
      </div>
    </div>
  </div>
  <div style="height: 10px;"></div>
  <div class="container top-panel cast-shadow">
    <div class="d-flex align-items-center justify-content-center" style="height: 50px;">
      <span id="color-mode-text" style="color: var(--white); font-weight: bold;">PREPNÚŤ NA SVETLÝ REŽIM</span>
      &ensp;&ensp;
      <label class="switch">
        <input type="checkbox" id="color-mode-switch" onclick="Swap();">
        <span class="slider round"></span>
      </label>
    </div>
  </div>
  <div style="height: 10px;"></div>
</body>

<script>
  let membraneTable = new Table({
    table: "membrane",
    days: 550,
    headers: ["Posledná výmena", "Plán", "Skutočnosť"]
  });
  let filterTable = new Table({
    table: "filter",
    days: 90,
    headers: ["Posledné čistenie", "Plán", "Skutočnosť"]
  });

  membraneTable.GetContent(false);
  filterTable.GetContent(false);

  function ConfirmChange(tableHandler) {
    swal({
        title: "Ste si istý?",
        icon: "warning",
        timer: 3000,
        buttons: {
          confirm: {
            text: "Áno",
            value: true,
            visible: true,
            className: "",
            closeModal: true
          },
          cancel: {
            text: "Nie",
            value: false,
            visible: true,
            className: "",
            closeModal: true,
          }
        }
      })
      .then((button) => {
        if (button) {
          swal({
            text: "Záznam pridaný!",
            icon: "success",
            timer: 1000,
            buttons: false
          });
          tableHandler();
        } else {
          swal({
            text: "Operácia zrušená!",
            icon: "error",
            timer: 1000,
            buttons: false
          });
        }
      });
  }

  function switchVideo(html, source) {
    $('#video').attr("src", source);
    $('#video').attr("width", "720");
    $('#video').attr("height", "480");
    $('#dropdownMenuLink').text(html);
  }
</script>

<script src="js/colormode.js"></script>

<footer>
  <?php require("html/footer.html"); ?>
</footer>

</html>