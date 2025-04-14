<?php
session_start();
// Ensure this path is correct relative to where you save this PHP file!
require_once '../includes/db.php'; // Provides $pdo object

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php"); // Redirect to login if not logged in
    exit();
}

// --- Handle Sending Message (POST Request) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['message'])) {
    // Sanitize message input
    $message = htmlspecialchars(trim($_POST['message']));
    $user_id = $_SESSION['user_id'];

    // Prepare and execute the INSERT statement for the message
    // This part doesn't need changes as it uses standard messages columns
    try {
        $stmt = $pdo->prepare("INSERT INTO messages (user_id, message) VALUES (:user_id, :message)");
        $stmt->execute(['user_id' => $user_id, 'message' => $message]);

        // Redirect after POST to prevent re-submission on refresh
        header("Location: " . $_SERVER['PHP_SELF']); // Redirect back to this same page
        exit();

    } catch (PDOException $e) {
        // Log the error and maybe show a user-friendly message
        error_log("Error inserting message: " . $e->getMessage());
        // For simplicity, we just stop; you could set an error variable to display
        die("There was an error sending your message. Please try again.");
    }
}

// --- Fetch Messages for Display (GET Request or after failed POST) ---
$messages = []; // Initialize empty array
try {
    // Updated SQL: Select user's name AND role from your specific users table
    $sql = "SELECT msg.id, msg.user_id, msg.message, msg.created_at,
                   usr.name, usr.role  -- Fetch name and role from the users table
            FROM messages msg             -- Alias messages table as 'msg'
            JOIN users usr ON msg.user_id = usr.id -- Alias users table as 'usr'
            ORDER BY msg.created_at ASC"; // Order by oldest first for chat flow
            // Using DESC in original code, changed to ASC for typical chat order

    $stmt = $pdo->query($sql);
    // Fetch all messages into the array
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    // Log the error and maybe show a user-friendly message
    error_log("Error fetching messages: " . $e->getMessage());
    // You could set a display error variable here: $display_error = "Could not load messages.";
}

// Close the connection explicitly (optional but good practice)
$pdo = null;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Study Messages</title>
    <style>
        /* Basic CSS Reset & Font */
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
            background-color: #f3f4f6; /* light gray */
            color: #1f2937;      /* dark gray */
            line-height: 1.6;
            transition: background-color 0.3s, color 0.3s;
        }

        /* Dark Mode Base */
        body.dark-mode {
            background-color: #111827; /* very dark blue/gray */
            color: #d1d5db;      /* light gray */
        }

        /* Container */
        .container {
            max-width: 800px;
            margin: 30px auto;
            background: #ffffff;
            padding: 25px 35px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            transition: background-color 0.3s, box-shadow 0.3s;
        }
        body.dark-mode .container {
            background-color: #1f2937; /* dark gray/blue */
            box-shadow: 0 6px 20px rgba(0,0,0,0.2);
        }

        /* Title */
        h2 {
            font-size: 1.6em;
            font-weight: 600;
            margin-bottom: 25px;
            text-align: center;
            color: #111827; /* Dark gray */
        }
        body.dark-mode h2 {
            color: #f3f4f6; /* Light gray */
        }

        /* Chat Box Area */
        .chat-box {
            max-height: 55vh; /* Use viewport height for better responsiveness */
            overflow-y: auto;
            margin-bottom: 25px;
            border: 1px solid #e5e7eb; /* light border */
            padding: 15px;
            border-radius: 8px;
            background-color: #f9fafb; /* very light gray */
            scroll-behavior: smooth; /* Smooth scroll on updates */
        }
        body.dark-mode .chat-box {
            background-color: #111827; /* match body dark */
            border-color: #374151; /* medium gray */
        }

        /* Individual Message Bubble */
        .message {
            margin-bottom: 15px;
            padding: 12px 18px;
            background-color: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.03);
            max-width: 90%; /* Prevent full width */
            word-wrap: break-word; /* Break long words */
        }
        body.dark-mode .message {
            background-color: #374151; /* medium gray */
            border-color: #4b5563; /* darker gray */
        }
        /* Optional: Style own messages differently if needed later */
        /* .message.own { background-color: #dbeafe; border-color: #bfdbfe; } */
        /* body.dark-mode .message.own { background-color: #1e40af; border-color: #1d4ed8; } */

        /* Sender Name */
        .message strong {
            font-weight: 600;
            color: #1d4ed8; /* blue */
            display: inline-block; /* Allow role tag next to it */
            margin-right: 8px; /* Space before role tag */
            margin-bottom: 4px;
        }
        body.dark-mode .message strong {
            color: #60a5fa; /* light blue */
        }

        /* Role Tag (Optional but uses fetched role) */
        .role-tag {
            display: inline-block;
            font-size: 0.7em;
            font-weight: 500;
            padding: 2px 6px;
            border-radius: 4px;
            background-color: #e0e7ff; /* light blue bg */
            color: #3730a3;      /* dark blue text */
            text-transform: capitalize;
            vertical-align: middle; /* Align with name */
        }
        body.dark-mode .role-tag {
            background-color: #374151; /* medium gray */
            color: #9ca3af;      /* light gray text */
        }
        .role-tag.teacher { /* Specific style for teachers */
             background-color: #d1fae5; /* light green bg */
             color: #047857;      /* dark green text */
        }
        body.dark-mode .role-tag.teacher {
            background-color: #065f46; /* dark green bg */
            color: #a7f3d0;      /* light green text */
        }


        /* Timestamp */
        .message em {
            display: block;
            font-size: 0.75em;
            color: #6b7280; /* medium gray */
            margin-top: 8px;
            text-align: right;
        }
        body.dark-mode .message em {
            color: #9ca3af; /* light gray */
        }

        /* Message Form */
        form {
            display: flex; /* Align items horizontally */
            gap: 10px; /* Space between textarea and button */
            margin-top: 10px;
        }
        textarea {
            flex-grow: 1; /* Take available space */
            padding: 12px;
            border: 1px solid #d1d5db; /* light gray border */
            border-radius: 8px;
            resize: vertical; /* Allow vertical resize only */
            font-size: 0.95em;
            line-height: 1.5;
            min-height: 45px; /* Start smaller */
            max-height: 150px; /* Limit growth */
            font-family: inherit;
            transition: border-color 0.3s, background-color 0.3s;
        }
        textarea:focus {
            outline: none;
            border-color: #2563eb; /* blue */
            box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.2);
        }
        body.dark-mode textarea {
            background-color: #1f2937; /* dark gray/blue */
            color: #f3f4f6;      /* light gray text */
            border-color: #4b5563; /* darker gray */
        }
        body.dark-mode textarea:focus {
             border-color: #60a5fa; /* light blue */
             box-shadow: 0 0 0 2px rgba(96, 165, 250, 0.3);
        }

        input[type="submit"] {
            flex-shrink: 0; /* Prevent button from shrinking */
            background-color: #2563eb; /* blue */
            color: white;
            border: none;
            padding: 10px 18px;
            border-radius: 8px;
            font-size: 0.95em;
            font-weight: 500;
            cursor: pointer;
            transition: background-color 0.2s ease;
            align-self: flex-end; /* Align button to bottom of flex container */
        }
        input[type="submit"]:hover {
            background-color: #1d4ed8; /* darker blue */
        }
         body.dark-mode input[type="submit"] {
            background-color: #3b82f6; /* lighter blue */
         }
         body.dark-mode input[type="submit"]:hover {
             background-color: #60a5fa; /* even lighter blue */
         }

        /* Dark Mode Toggle Button */
        #darkToggle {
            position: fixed; /* Keep it in view */
            top: 15px;
            right: 15px;
            background: #ffffff;
            border: 1px solid #d1d5db;
            color: #374151;
            padding: 8px 12px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 0.85em;
            z-index: 100; /* Ensure it's on top */
            transition: background 0.3s ease, color 0.3s, border-color 0.3s;
        }
        #darkToggle:hover {
            background: #f3f4f6;
        }
        body.dark-mode #darkToggle {
            background: #374151;
            border-color: #4b5563;
            color: #e5e7eb;
        }
        body.dark-mode #darkToggle:hover {
             background: #4b5563;
        }

        /* Back Link */
        .back-link {
            display: block;
            text-align: center;
            margin-top: 25px;
            color: #1d4ed8; /* darker blue */
            text-decoration: none;
            font-size: 0.9em;
        }
        .back-link:hover {
            text-decoration: underline;
        }
        body.dark-mode .back-link {
             color: #60a5fa; /* light blue */
        }

        /* Scrollbar styling (Optional but nice) */
        .chat-box::-webkit-scrollbar { width: 8px; }
        .chat-box::-webkit-scrollbar-track { background: #f9fafb; border-radius: 4px;}
        .chat-box::-webkit-scrollbar-thumb { background: #d1d5db; border-radius: 4px;}
        .chat-box::-webkit-scrollbar-thumb:hover { background: #9ca3af; }
        body.dark-mode .chat-box::-webkit-scrollbar-track { background: #111827; }
        body.dark-mode .chat-box::-webkit-scrollbar-thumb { background: #4b5563; }
        body.dark-mode .chat-box::-webkit-scrollbar-thumb:hover { background: #6b7280; }

    </style>
</head>
<body> <!-- Add class="dark-mode" here if you want dark by default -->

<button id="darkToggle">🌙 Dark Mode</button>

<div class="container">

    <h2>📘 Study Discussion</h2>

    <div class="chat-box" id="chatBox">
        <?php if (empty($messages)): ?>
            <p style="text-align: center; color: #6b7280;">No messages yet. Start the discussion!</p>
            <?php if(isset($display_error)): ?>
                 <p style="text-align: center; color: #ef4444;"><?php echo htmlspecialchars($display_error); ?></p>
            <?php endif; ?>
        <?php else: ?>
            <?php foreach ($messages as $row):
                // Determine role class for styling
                $role_class = strtolower(htmlspecialchars($row['role']));
            ?>
            <div class="message">
                <strong><?php echo htmlspecialchars($row['name']); ?></strong>
                 <?php if (!empty($row['role'])): // Display role if available ?>
                    <span class="role-tag <?php echo $role_class; ?>">
                        <?php echo $role_class; ?>
                    </span>
                <?php endif; ?>
                <p><?php echo nl2br(htmlspecialchars($row['message'])); // nl2br AFTER htmlspecialchars ?></p>
                <em><?php echo date('M j, Y, g:i A', strtotime($row['created_at'])); // Slightly shorter date format ?></em>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
         <!-- Anchor for scrolling -->
         <div id="end-of-chat"></div>
    </div>

    <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
        <textarea name="message" placeholder="Ask a question, share insights, or help others…" required aria-label="Type your message"></textarea>
        <input type="submit" value="Send">
    </form>

    <!-- Optional: Link back to a dashboard -->
    <!-- <a class="back-link" href="dashboard.php">← Back to Dashboard</a> -->
</div>

<script>
    // Dark Mode Toggle Logic
    const darkToggleButton = document.getElementById("darkToggle");
    const body = document.body;

    // Function to apply theme based on localStorage
    function applyTheme() {
        const isDarkMode = localStorage.getItem("darkMode") === "enabled";
        if (isDarkMode) {
            body.classList.add("dark-mode");
            darkToggleButton.textContent = "☀️ Light Mode";
        } else {
            body.classList.remove("dark-mode");
            darkToggleButton.textContent = "🌙 Dark Mode";
        }
    }

    // Toggle theme on button click
    darkToggleButton.addEventListener("click", () => {
        const isDarkMode = body.classList.toggle("dark-mode");
        localStorage.setItem("darkMode", isDarkMode ? "enabled" : "disabled");
        darkToggleButton.textContent = isDarkMode ? "☀️ Light Mode" : "🌙 Dark Mode";
    });

    // Apply theme on initial load
    applyTheme();

    // Scroll chat box to the bottom
    function scrollToBottom() {
        const chatBox = document.getElementById('chatBox');
        const endOfChat = document.getElementById('end-of-chat');
        if (chatBox && endOfChat) {
            // Option 1: Scroll container height
             chatBox.scrollTop = chatBox.scrollHeight;
            // Option 2: Scroll anchor into view
            // endOfChat.scrollIntoView({ behavior: 'smooth', block: 'end' }); // Can be jumpy
        }
    }

    // Scroll on page load
    window.addEventListener('load', scrollToBottom);

    // Optional: Scroll again slightly after load in case images/fonts affect height
    // setTimeout(scrollToBottom, 100);

</script>

</body>
</html>