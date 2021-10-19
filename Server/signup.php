<?php
session_start();
require("connection.php");
require("DeviceManager/DeviceManager.php");

if ($_SERVER['REQUEST_METHOD'] == "POST") {
  $deviceManager = new DeviceManager($connection, $_POST['device_id'], $_POST['device_password']);
  $deviceManager->SignUp();
  header("Location: login.php?success=deviceRegistered");
  die();
}
?>

<!DOCTYPE html>
<html lang="sk">

<head>
  <?php require("html/head.html"); ?>
  <title>Ekoprogres | Registrácia do systému</title>
</head>

<body>

  <div class="container top-panel cast-shadow" style="margin-top: 10px;">
    <div class="row">
      <?php include("html/logo.html"); ?>
      <div class="col-md-12 login-title">Registrácia ČOV zariadenia</div>
      <div style="margin: 20px auto 50px auto;">
        <?php
        if (isset($_GET['error'])) echo "<div class='top-panel-item cast-shadow top-panel-status bold-text failure-color'>Zariadenie už existuje!</div>";
        ?>
      </div>

      <div class="col-lg-12">
        <form method="post" class="needs-validation" novalidate>
          <div class="form-group">
            <label class="form-control-label">ID ČOV</label>
            <input type="text" class="form-control" name="device_id" pattern="[Cc][Oo][Vv]_+[0-9]{4}" required>
          </div>
          <div class="form-group">
            <label class="form-control-label">HESLO</label>
            <input type="password" class="form-control" name="device_password" required>
          </div>
          <div class="col-lg-12">
            <div class="row">
              <div class="col-sm">
                <button type="submit" class="btn btn-outline-secondary btn-block">REGISTROVAŤ</button>
              </div>
              <div class="col-sm">
                <a class="btn btn-outline-primary btn-block" href="login.php">Prihlásenie</a>
              </div>
            </div>
            <div style="height: 50px;"></div>
          </div>
        </form>
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

<footer>
  <?php require("html/footer.html"); ?>
</footer>

<script src="js/colormode.js"></script>

<script>
  (function() {
    'use strict'
    var forms = document.querySelectorAll('.needs-validation')
    Array.prototype.slice.call(forms)
      .forEach(function(form) {
        form.addEventListener('submit', function(event) {
          if (!form.checkValidity()) {
            event.preventDefault()
            event.stopPropagation()
          }
          form.classList.add('was-validated')
        }, false)
      })
  })()
</script>

</html>