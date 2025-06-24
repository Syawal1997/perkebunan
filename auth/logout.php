<?php
session_start();
session_destroy();
header('Location: /perkebunan/auth/login.php');
exit();
?>