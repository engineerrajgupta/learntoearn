<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");  // Redirect if not logged in
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Dashboard - LearnIt Tandem</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body>
    <h1>Welcome to LearnIt Tandem</h1>
    <p>Your personalized learning dashboard</p>
    <a href="logout.php">Logout</a>
</body>
</html>
