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
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Study Messages</title>
    <style>
/* 🌑 Dark Mode Styles */
h2 {
    font-size: 24px;
    font-weight: 600;
    margin-bottom: 20px;
    text-align: center;
    color: #111827; /* Dark gray in light mode */
}

body.dark-mode h2 {
    color: #f1f5f9; /* Light gray in dark mode */
}

body.dark-mode {
    background-color: #0f172a;
    color: #e2e8f0;
}

body.dark-mode .container {
    background-color: #1e293b;  
    
    box-shadow: 0 8px 30px rgba(0, 0, 0, 0.5);
}

body.dark-mode .chat-box {
    background-color: #1e293b;
    border-color: #334155;
}

body.dark-mode .message {
    background-color: #334155;
    border-color: #475569;
}

body.dark-mode .message strong {
    color: #93c5fd;
}

body.dark-mode .message em {
    color: #94a3b8;
}

body.dark-mode .role-tag {
    background-color: #475569;
    color: #cbd5e1;
}

body.dark-mode textarea {
    background-color: #0f172a;
    color: #f8fafc;
    border-color: #475569;
}

body.dark-mode input[type="submit"] {
    background-color: #3b82f6;
    color: white;
    border: none;
}

body.dark-mode input[type="submit"]:hover {
    background-color: #2563eb;
}

#darkToggle {
    background: #1e293b;
    border: 1px solid #475569;
    color: #cbd5e1;
    padding: 6px 12px;
    border-radius: 6px;
    cursor: pointer;
    font-size: 0.9em;
    transition: background 0.3s ease;
}

#darkToggle:hover {
    background: #334155;
}


        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 0;
            font-family: 'Inter', sans-serif;
            background-color: #f3f4f6;
            color: #1f2937;
        }

        .container {
            max-width: 800px;
            margin: 40px auto;
            background: #ffffff;
    
            padding: 30px 40px;
            border-radius: 12px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.05);
        }

        h2 {
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 20px;
            text-align: center;
            color: #111827;
        }

        .chat-box {
            max-height: 500px;
            overflow-y: auto;
            margin-bottom: 30px;
            border: 1px solid #e5e7eb;
            padding: 20px;
            border-radius: 10px;
            background-color: #f9fafb;
        }

        .message {
            margin-bottom: 20px;
            padding: 15px 20px;
            background-color: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.02);
            transition: all 0.2s ease;
        }

        .message:hover {
            box-shadow: 0 4px 10px rgba(0,0,0,0.04);
        }

        .message strong {
            font-weight: 600;
            color: #2563eb;
            display: block;
            margin-bottom: 6px;
        }

        .message em {
            display: block;
            font-size: 0.75em;
            color: #6b7280;
            margin-top: 10px;
        }

        form {
            display: flex;
            flex-direction: column;
        }

        textarea {
            padding: 14px;
            border: 1px solid #d1d5db;
            border-radius: 8px;
            resize: vertical;
            font-size: 1em;
            line-height: 1.5;
            margin-bottom: 15px;
            min-height: 80px;
            font-family: inherit;
        }

        input[type="submit"] {
            align-self: flex-end;
            background-color: #2563eb;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            font-size: 1em;
            cursor: pointer;
            transition: background-color 0.2s ease;
        }

        input[type="submit"]:hover {
            background-color: #1d4ed8;
        }

        .back-link {
            display: block;
            text-align: center;
            margin-top: 30px;
            color: #2563eb;
            text-decoration: none;
            font-size: 0.95em;
        }

        .back-link:hover {
            text-decoration: underline;
        }

        @media (max-width: 600px) {
            .container {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
<button id="darkToggle">🌙 Dark Mode</button>
<div class="container">

    <h2>📘 Study Discussion</h2>
    


    <div class="chat-box">
        <?php foreach ($messages as $row): ?>
            <div class="message">
                <strong><?php echo htmlspecialchars($row['name']); ?></strong>
                <?php echo nl2br(htmlspecialchars($row['message'])); ?>
                <em><?php echo date('F j, Y \a\t g:i A', strtotime($row['created_at'])); ?></em>
            </div>
        <?php endforeach; ?>
    </div>

    <form method="POST">
        <textarea name="message" placeholder="Ask a question, share insights, or help others…" required></textarea>
        <input type="submit" value="Send Message">
    </form>

    <a class="back-link" href="dashboard.php">← Back to Dashboard</a>
</div>
<script>
    document.getElementById("darkToggle").addEventListener("click", () => {
        document.body.classList.toggle("dark-mode");
    });
</script>
    

</body>
</html>
