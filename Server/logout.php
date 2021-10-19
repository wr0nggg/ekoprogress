<?php
session_start();
if (isset($_SESSION['device_id'])) unset($_SESSION['device_id']);
session_destroy();
header("Location: login.php");
?>