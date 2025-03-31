<?php
session_start();

// Check if the user is logged in
if (!isset($_SESSION['user_id'])) {
    // Redirect to login page if not logged in
    header("Location: login.php");
    exit();
}

// Retrieve user information from session
$username = $_SESSION['username'];
$role = $_SESSION['role'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Dashboard</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body>
    <h2>Welcome, <?php echo htmlspecialchars($username); ?>!</h2>
    <p>Your role: <?php echo htmlspecialchars($role); ?></p>
    <a href="logout.php">Logout</a>
</body>
</html>
