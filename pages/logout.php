<?php
session_start();
session_destroy();
header("Location: /learntoearn/pages/login.php");
?>
