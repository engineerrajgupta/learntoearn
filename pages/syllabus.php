<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $topic_name = $_POST['topic_name'];
    $query = "INSERT INTO topics (name, status) VALUES ('$topic_name', 'pending')";
    $conn->query($query);
}

$topics_query = "SELECT * FROM topics ORDER BY id DESC";
$topics_result = $conn->query($topics_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Syllabus Management</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-6">
    <h1 class="text-3xl font-bold mb-4">Syllabus</h1>

    <form method="POST" class="bg-white p-6 rounded shadow-md">
        <input type="text" name="topic_name" placeholder="Add New Topic" class="p-2 border rounded w-full mb-3">
        <button type="submit" class="bg-blue-500 text-white p-2 rounded w-full">Add Topic</button>
    </form>

    <div class="bg-white p-6 rounded shadow-md mt-6">
        <h2 class="text-xl font-semibold">Topics</h2>
        <ul>
            <?php while ($row = $topics_result->fetch_assoc()): ?>
                <li class="border-b p-2"><?php echo $row['name']; ?> - <span class="text-sm text-gray-500"><?php echo $row['status']; ?></span></li>
            <?php endwhile; ?>
        </ul>
    </div>
</body>
</html>
