<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['name'];
$user_role = $_SESSION['role'];

// Fetch syllabus completion stats
$query = "SELECT COUNT(*) as total, SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed FROM topics";
$result = $conn->query($query);
$data = $result->fetch_assoc();

$total_topics = $data['total'];
$completed_topics = $data['completed'];
$progress = ($total_topics > 0) ? ($completed_topics / $total_topics) * 100 : 0;

// Fetch recent messages
$msg_query = "SELECT * FROM messages WHERE receiver_id = '$user_id' ORDER BY timestamp DESC LIMIT 5";
$msg_result = $conn->query($msg_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-6">
    <h1 class="text-3xl font-bold mb-4">Welcome, <?php echo $user_name; ?>!</h1>

    <div class="bg-white p-6 rounded shadow-md">
        <h2 class="text-xl font-semibold">Syllabus Progress</h2>
        <p><?php echo number_format($progress, 2); ?>% Completed</p>
        <div class="w-full bg-gray-200 rounded-full h-4 mt-2">
            <div class="bg-blue-500 h-4 rounded-full" style="width: <?php echo $progress; ?>%;"></div>
        </div>
    </div>

    <div class="bg-white p-6 rounded shadow-md mt-6">
        <h2 class="text-xl font-semibold">Recent Messages</h2>
        <ul>
            <?php while ($row = $msg_result->fetch_assoc()): ?>
                <li class="border-b p-2"><?php echo $row['content']; ?> <span class="text-sm text-gray-500">(<?php echo $row['timestamp']; ?>)</span></li>
            <?php endwhile; ?>
        </ul>
    </div>
</body>
</html>
