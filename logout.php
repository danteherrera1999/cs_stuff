<?php
session_start();
session_destroy();
header("Location: inventory.php");
exit();
?>