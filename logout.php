<?php
session_start();
session_unset();
session_destroy();
$_SESSION['feedback'] = "✅ Logged out successfully.";
header('Location: login.php');
exit();
?>