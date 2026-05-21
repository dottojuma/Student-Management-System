<?php
session_start();
$_SESSION = array(); // Futa data zote za session
session_destroy(); // Vunja kikao (Session)
header("location: login.php"); // Mrudishe kwenye login
exit;
?>