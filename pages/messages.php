<?php
session_start();
require_once '../includes/db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['message'])) {
    $message = htmlspecialchars(trim($_POST['message']));
    $user_id = $_SESSION['user_id'];

    $stmt = $pdo->prepare("INSERT INTO messages (user_id, message) VALUES (:user_id, :message)");
    $stmt->execute(['user_id' => $user_id, 'message' => $message]);
}

$sql = "SELECT messages.*, users.name 
        FROM messages 
        JOIN users ON messages.user_id = users.id 
        ORDER BY messages.created_at DESC";
$stmt = $pdo->query($sql);
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Messages</title>
    <style>
        body { font-family: Arial; max-width: 600px; margin: auto; padding: 20px; }
        .message { border-bottom: 1px solid #ccc; padding: 10px 0; }
        .message strong { color: #333; }
        .message em { font-size: 0.8em; color: #888; }
        form textarea { width: 100%; height: 60px; }
        form input[type="submit"] { margin-top: 10px; padding: 8px 16px; }
    </style>
</head>
<body>

<h2>Public Messages</h2>

<form method="POST">
    <textarea name="message" placeholder="Type your message here..." required></textarea>
    <input type="submit" value="Send">
</form>

<hr>

<?php foreach ($messages as $row): ?>
    <div class="message">
        <strong><?php echo htmlspecialchars($row['name']); ?>:</strong><br>
        <?php echo nl2br(htmlspecialchars($row['message'])); ?><br>
        <em><?php echo $row['created_at']; ?></em>
    </div>
<?php endforeach; ?>

<p><a href="dashboard.php">Back to Dashboard</a></p>

</body>
</html>
