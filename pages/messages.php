<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $receiver_id = $_POST['receiver_id'];
    $content = $_POST['message'];

    $query = "INSERT INTO messages (sender_id, receiver_id, content, timestamp) VALUES ('$user_id', '$receiver_id', '$content', NOW())";
    $conn->query($query);
}

$messages_query = "SELECT * FROM messages WHERE sender_id='$user_id' OR receiver_id='$user_id' ORDER BY timestamp DESC";
$messages_result = $conn->query($messages_query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Messages</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-6">
    <h1 class="text-3xl font-bold mb-4">Messages</h1>

    <form method="POST" class="bg-white p-6 rounded shadow-md">
        <select name="receiver_id" class="p-2 border rounded w-full mb-3">
            <option value="1">Admin</option>
            <option value="2">Teacher</option>
        </select>
        <textarea name="message" placeholder="Type a message..." class="p-2 border rounded w-full mb-3"></textarea>
        <button type="submit" class="bg-green-500 text-white p-2 rounded w-full">Send</button>
    </form>

    <div class="bg-white p-6 rounded shadow-md mt-6">
        <h2 class="text-xl font-semibold">Chat History</h2>
        <ul>
            <?php while ($row = $messages_result->fetch_assoc()): ?>
                <li class="border-b p-2"><?php echo $row['content']; ?> <span class="text-sm text-gray-500">(<?php echo $row['timestamp']; ?>)</span></li>
            <?php endwhile; ?>
        </ul>
    </div>
</body>
</html>
