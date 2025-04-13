<?php
session_start();

// --- STEP 1: Authentication Check ---
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit;
}
$user_id = $_SESSION['user_id'];

// --- STEP 2: Database Connection ---
if (!file_exists('../includes/db.php')) {
    error_log("FATAL ERROR: Database include file not found at ../includes/db.php");
    die("Critical configuration error: Cannot find database connection file.");
}
require '../includes/db.php';

// --- STEP 3: Verify PDO Connection Object ---
if (!isset($pdo) || !$pdo instanceof PDO) {
     error_log("FATAL ERROR: PDO connection object (\$pdo) is not available after including db.php");
     die("Critical configuration error: Database connection object is invalid.");
}

// --- STEP 4: Initialization ---
$name_update_message = ''; $name_update_error = false;
$email_update_message = ''; $email_update_error = false;
$password_change_message = ''; $password_change_error = false;

// --- STEP 5: Handle Form Submissions (POST Requests) ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['form_type'])) {
    $formType = $_POST['form_type'];
    // --- 5.A: Handle Name Update ---
    if ($formType === 'update_name') {
        $new_name = trim($_POST['new_name'] ?? '');
        if (empty($new_name)) { $name_update_message = "Name cannot be empty."; $name_update_error = true; }
        else {
            try {
                $stmt_name_update = $pdo->prepare("UPDATE users SET name = ? WHERE id = ?");
                if ($stmt_name_update->execute([$new_name, $user_id])) { $_SESSION['username'] = $new_name; header("Location: profile.php?name_status=success"); exit; }
                else { $name_update_message = "Failed to update name."; $name_update_error = true; error_log("Name update failed for user $user_id."); }
            } catch (PDOException $e) { error_log("DB error name update user $user_id: " . $e->getMessage()); $name_update_message = "DB error updating name."; $name_update_error = true; }
        }
        if ($name_update_error) { header("Location: profile.php?name_status=error&name_msg=" . urlencode(substr($name_update_message, 0, 150))); exit; }
    }
    // --- 5.B: Handle Email Update ---
    elseif ($formType === 'update_email') {
        $new_email = trim($_POST['new_email'] ?? '');
        $current_password_for_email = $_POST['current_password_for_email'] ?? '';
        if (empty($new_email) || empty($current_password_for_email)) { $email_update_message = "Enter new email and current password."; $email_update_error = true; }
        elseif (!filter_var($new_email, FILTER_VALIDATE_EMAIL)) { $email_update_message = "Invalid email format."; $email_update_error = true; }
        else {
            try {
                $stmt_email_check = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ? LIMIT 1"); $stmt_email_check->execute([$new_email, $user_id]);
                if ($stmt_email_check->fetch()) { $email_update_message = "Email already registered."; $email_update_error = true; }
                else {
                    $stmt_pass_check = $pdo->prepare("SELECT password FROM users WHERE id = ?"); $stmt_pass_check->execute([$user_id]); $user_current_hash = $stmt_pass_check->fetchColumn();
                    if ($user_current_hash && password_verify($current_password_for_email, $user_current_hash)) {
                        $stmt_email_update = $pdo->prepare("UPDATE users SET email = ? WHERE id = ?");
                        if ($stmt_email_update->execute([$new_email, $user_id])) { header("Location: profile.php?email_status=success"); exit; }
                        else { $email_update_message = "Failed to update email."; $email_update_error = true; error_log("Email update failed user $user_id."); }
                    } else { $email_update_message = "Incorrect current password."; $email_update_error = true; }
                }
            } catch (PDOException $e) { error_log("DB error email update user $user_id: " . $e->getMessage()); $email_update_message = "DB error updating email."; $email_update_error = true; }
        }
        if ($email_update_error) { header("Location: profile.php?email_status=error&email_msg=" . urlencode(substr($email_update_message, 0, 150))); exit; }
    }
    // --- 5.C: Handle Password Change ---
    elseif ($formType === 'change_password') {
        $current_password = $_POST['current_password'] ?? ''; $new_password = $_POST['new_password'] ?? ''; $confirm_password = $_POST['confirm_password'] ?? '';
        if (empty($current_password) || empty($new_password) || empty($confirm_password)) { $password_change_message = "Fill all password fields."; $password_change_error = true; }
        elseif ($new_password !== $confirm_password) { $password_change_message = "New passwords do not match."; $password_change_error = true; }
        elseif (strlen($new_password) < 6) { $password_change_message = "New password min 6 chars."; $password_change_error = true; }
        else {
            try {
                $stmt_pass_check = $pdo->prepare("SELECT password FROM users WHERE id = ?"); $stmt_pass_check->execute([$user_id]); $user_current_hash = $stmt_pass_check->fetchColumn();
                if ($user_current_hash && password_verify($current_password, $user_current_hash)) {
                    $new_hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
                    $stmt_pass_update = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                    if ($stmt_pass_update->execute([$new_hashed_password, $user_id])) { header("Location: profile.php?pwd_status=success"); exit; }
                    else { $password_change_message = "Failed to update password."; $password_change_error = true; error_log("Password update failed user $user_id."); }
                } else { $password_change_message = "Incorrect current password."; $password_change_error = true; }
            } catch (PDOException $e) { error_log("DB error password change user $user_id: " . $e->getMessage()); $password_change_message = "DB error changing password."; $password_change_error = true; }
        }
        if ($password_change_error) { header("Location: profile.php?pwd_status=error&pwd_msg=" . urlencode(substr($password_change_message, 0, 150))); exit; }
    }
} // End of POST handling

// --- STEP 6: Get Feedback Status ---
if (isset($_GET['name_status'])) { if ($_GET['name_status'] === 'success') { $name_update_message = "Name updated!"; $name_update_error = false; } elseif ($_GET['name_status'] === 'error') { $name_update_message = "Name update failed: " . (isset($_GET['name_msg']) ? htmlspecialchars(urldecode($_GET['name_msg'])) : "Error."); $name_update_error = true; } }
if (isset($_GET['email_status'])) { if ($_GET['email_status'] === 'success') { $email_update_message = "Email updated!"; $email_update_error = false; } elseif ($_GET['email_status'] === 'error') { $email_update_message = "Email update failed: " . (isset($_GET['email_msg']) ? htmlspecialchars(urldecode($_GET['email_msg'])) : "Error."); $email_update_error = true; } }
if (isset($_GET['pwd_status'])) { if ($_GET['pwd_status'] === 'success') { $password_change_message = "Password updated!"; $password_change_error = false; } elseif ($_GET['pwd_status'] === 'error') { $password_change_message = "Password change failed: " . (isset($_GET['pwd_msg']) ? htmlspecialchars(urldecode($_GET['pwd_msg'])) : "Error."); $password_change_error = true; } }

// --- STEP 7: Fetch User Data for Display ---
$user = null;
try {
    $sql = "SELECT name, email, role, dob FROM users WHERE id = ? LIMIT 1"; $stmt_fetch = $pdo->prepare($sql);
    if ($stmt_fetch->execute([$user_id])) {
        $user = $stmt_fetch->fetch(PDO::FETCH_ASSOC);
        if (!$user) { error_log("User data not found: $user_id"); session_destroy(); header("Location: ../index.php?message=User+session+error"); exit; }
    } else { $errorInfo = $stmt_fetch->errorInfo(); error_log("FATAL: DB query exec failed user $user_id: {$errorInfo[2]}"); die("Error executing profile query. Check logs."); }
} catch (PDOException $e) { error_log("FATAL: PDOException fetching user $user_id: " . $e->getMessage()); die("Error fetching profile data. Check logs."); }
?>
<!DOCTYPE html>
<!-- Added class="scroll-smooth" -->
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile - LearnToEarn</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- <<< NEW: Dark Mode Initializer Script (Run before rendering body) >>> -->
    <script>
        // Checks localStorage and OS preference BEFORE rendering to prevent FOUC
        if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
          document.documentElement.classList.add('dark');
        } else {
          document.documentElement.classList.remove('dark');
        }
    </script>
    <!-- End Dark Mode Initializer -->

    <style>
        /* Base styles (Light Mode) */
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f0f4f8; /* light gray-blue */
            color: #1f2937; /* dark gray */
            transition: background-color 0.3s ease, color 0.3s ease;
        }
        .profile-card { background-color: white; border-radius: 8px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); padding: 1.5rem; /* p-6 */ md:padding: 2rem; /* md:p-8 */ margin-bottom: 2rem; /* mb-8 */ transition: background-color 0.3s ease, border-color 0.3s ease; }
        .form-card h2 { font-size: 1.25rem; font-weight: 600; margin-bottom: 1.25rem; color: #1f2937; border-bottom: 1px solid #e5e7eb; padding-bottom: 0.75rem; transition: color 0.3s ease, border-color 0.3s ease; }
        .message { padding: 12px 15px; border-radius: 5px; margin-bottom: 1.25rem; font-size: 0.9em; border-width: 1px; border-style: solid; }
        .message.success { background-color: #d1fae5; border-color: #6ee7b7; color: #065f46; }
        .message.error { background-color: #fee2e2; border-color: #fca5a5; color: #991b1b; }
        label { display: block; margin-bottom: 0.5rem; font-weight: 500; color: #4a5568; transition: color 0.3s ease; }
        input[type="password"], input[type="text"], input[type="email"] {
             width: 100%; padding: 0.75rem; border: 1px solid #cbd5e0; border-radius: 0.375rem;
             background-color: white; color: #1f2937;
             transition: border-color 0.2s ease-in-out, box-shadow 0.2s ease-in-out, background-color 0.3s ease, color 0.3s ease;
         }
        input:focus { outline: none; border-color: #3b82f6; box-shadow: 0 0 0 2px #bfdbfe; }
        .form-button { background-color: #3b82f6; color: white; padding: 8px 16px; border-radius: 5px; cursor: pointer; transition: background-color 0.2s ease-in-out; display: inline-block; text-align: center; border: none; font-weight: 500; }
        .form-button:hover { background-color: #2563eb; }

        /* <<< NEW: Dark Mode Styles >>> */
        /* Apply styles when html tag has the 'dark' class */
        html.dark body { background-color: #111827; color: #d1d5db; /* gray-300 */ }
        html.dark .profile-card { background-color: #1f2937; /* gray-800 */ border: 1px solid #374151; /* gray-700 */ }
        html.dark nav { background-color: #1f2937; /* gray-800 */ border-bottom: 1px solid #374151; /* gray-700 */}
        html.dark nav a { color: #d1d5db; /* gray-300 */ }
        html.dark nav a:hover { color: white; background-color: #374151; /* gray-700 */ }
        html.dark nav a.font-semibold { color: #eff6ff; /* blue-50 */ background-color: #374151; } /* Highlight active */
        html.dark .form-card h2 { color: #f3f4f6; /* gray-100 */ border-color: #374151; /* gray-700 */ }
        html.dark label { color: #9ca3af; /* gray-400 */ }
        html.dark input[type="password"], html.dark input[type="text"], html.dark input[type="email"] {
            background-color: #374151; /* gray-700 */
            border-color: #4b5563; /* gray-600 */
            color: #f3f4f6; /* gray-100 */
        }
        html.dark input::placeholder { color: #6b7280; /* gray-500 */ }
        html.dark input:focus { border-color: #60a5fa; /* blue-400 */ box-shadow: 0 0 0 2px #3b82f6; /* blue-500 */ }
        html.dark .message.success { background-color: #064e3b; border-color: #10b981; color: #d1fae5; } /* Darker green */
        html.dark .message.error { background-color: #7f1d1d; border-color: #f87171; color: #fee2e2; } /* Darker red */
        html.dark strong { color: #e5e7eb; /* gray-200 */ }
        /* Dark mode toggle button */
        #theme-toggle-button { background-color: #374151; border: 1px solid #4b5563; color: #d1d5db; }
        #theme-toggle-button:hover { background-color: #4b5563; }
        html.dark #theme-toggle-button { background-color: #4b5563; border: 1px solid #6b7280; color: #f3f4f6; }
        html.dark #theme-toggle-button:hover { background-color: #6b7280; }

    </style>
</head>
<!-- Added class="dark:bg-gray-900" -->
<body class="text-gray-800 antialiased">

    <!-- Navbar Placeholder -->
    <nav class="bg-white shadow-md p-4 mb-8 sticky top-0 z-50">
        <div class="container mx-auto flex justify-between items-center">
            <a href="dashboard.php" class="text-xl font-bold text-blue-600">LearnToEarn</a>
            <!-- Right side of Navbar -->
            <div class="flex items-center space-x-4">
                <!-- Navigation Links -->
                <div>
                    <a href="dashboard.php" class="text-gray-600 hover:text-blue-600 mr-4 px-3 py-2 rounded hover:bg-gray-100">Dashboard</a>
                    <a href="profile.php" class="text-blue-600 font-semibold mr-4 px-3 py-2 rounded bg-blue-50">Profile</a>
                    <!-- <a href="settings.php" class="text-gray-600 hover:text-blue-600 mr-4 px-3 py-2 rounded hover:bg-gray-100">Settings</a> -->
                    <a href="../logout.php" class="text-red-500 hover:text-red-700 px-3 py-2 rounded hover:bg-red-50">Logout</a>
                </div>
                <!-- <<< NEW: Dark Mode Toggle Button >>> -->
                <button id="theme-toggle-button" type="button" class="p-2 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition-colors duration-200">
                    <!-- Sun Icon (visible in light mode) -->
                    <svg id="theme-toggle-light-icon" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path d="M10 2a1 1 0 011 1v1a1 1 0 11-2 0V3a1 1 0 011-1zm4 8a4 4 0 11-8 0 4 4 0 018 0zm-.464 4.95l.707.707a1 1 0 001.414-1.414l-.707-.707a1 1 0 00-1.414 1.414zm2.12-10.607a1 1 0 010 1.414l-.706.707a1 1 0 11-1.414-1.414l.707-.707a1 1 0 011.414 0zM17 11a1 1 0 100-2h-1a1 1 0 100 2h1zm-7 4a1 1 0 011 1v1a1 1 0 11-2 0v-1a1 1 0 011-1zM5.05 6.464A1 1 0 106.465 5.05l-.708-.707a1 1 0 00-1.414 1.414l.707.707zm1.414 8.486l-.707.707a1 1 0 01-1.414-1.414l.707-.707a1 1 0 011.414 1.414zM4 11a1 1 0 100-2H3a1 1 0 000 2h1z" fill-rule="evenodd" clip-rule="evenodd"></path></svg>
                    <!-- Moon Icon (hidden in light mode, visible in dark mode) -->
                    <svg id="theme-toggle-dark-icon" class="hidden w-5 h-5" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path d="M17.293 13.293A8 8 0 016.707 2.707a8.001 8.001 0 1010.586 10.586z"></path></svg>
                </button>
            </div>
        </div>
    </nav>

    <div class="container mx-auto p-4 sm:p-6 md:p-8 max-w-2xl">

        <h1 class="text-3xl font-bold mb-6 text-gray-700">Edit Profile</h1>

        <!-- Display Current User Info -->
        <div class="profile-card">
             <h2 class="form-card h2">Current Information</h2>
             <?php if ($user): ?>
                <div class="space-y-3 text-gray-600">
                    <p><strong class="font-medium w-28 inline-block">Name:</strong> <?php echo htmlspecialchars($user['name']); ?></p>
                    <p><strong class="font-medium w-28 inline-block">Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
                    <p><strong class="font-medium w-28 inline-block">Role:</strong> <?php echo htmlspecialchars(ucfirst($user['role'])); ?></p>
                    <?php if (!empty($user['dob'])): $dob_formatted = strtotime($user['dob']) ? date("F j, Y", strtotime($user['dob'])) : 'Invalid Date'; ?>
                        <p><strong class="font-medium w-28 inline-block">Date of Birth:</strong> <?php echo htmlspecialchars($dob_formatted); ?></p>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <p class="text-red-600 font-semibold">Error: Could not load user profile details.</p>
            <?php endif; ?>
        </div>

        <!-- Update Name Card -->
        <div class="profile-card form-card">
             <h2>Update Name</h2>
             <?php if (!empty($name_update_message)): ?><div class="message <?php echo $name_update_error ? 'error' : 'success'; ?>"><?php echo htmlspecialchars($name_update_message); ?></div><?php endif; ?>
             <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" class="space-y-4"> <input type="hidden" name="form_type" value="update_name"> <div> <label for="new_name">New Name</label> <input type="text" name="new_name" id="new_name" value="<?php echo htmlspecialchars($user['name'] ?? ''); ?>" required> </div> <div> <button type="submit" class="form-button">Update Name</button> </div> </form>
        </div>

        <!-- Update Email Card -->
        <div class="profile-card form-card">
             <h2>Update Email</h2>
             <?php if (!empty($email_update_message)): ?><div class="message <?php echo $email_update_error ? 'error' : 'success'; ?>"><?php echo htmlspecialchars($email_update_message); ?></div><?php endif; ?>
             <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" class="space-y-4"> <input type="hidden" name="form_type" value="update_email"> <div> <label for="new_email">New Email Address</label> <input type="email" name="new_email" id="new_email" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>" required> </div> <div> <label for="current_password_for_email">Current Password (Required)</label> <input type="password" name="current_password_for_email" id="current_password_for_email" required> <p class="text-xs text-gray-500 mt-1">Enter password to confirm changes.</p> </div> <div> <button type="submit" class="form-button">Update Email</button> </div> </form>
        </div>

        <!-- Change Password Card -->
        <div class="profile-card form-card">
             <h2>Change Password</h2>
            <?php if (!empty($password_change_message)): ?><div class="message <?php echo $password_change_error ? 'error' : 'success'; ?>"><?php echo htmlspecialchars($password_change_message); ?></div><?php endif; ?>
             <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post" class="space-y-4"> <input type="hidden" name="form_type" value="change_password"> <div> <label for="current_password">Current Password</label> <input type="password" name="current_password" id="current_password" required> </div> <div> <label for="new_password">New Password</label> <input type="password" name="new_password" id="new_password" required minlength="6"> <p class="text-xs text-gray-500 mt-1">Minimum 6 characters.</p> </div> <div> <label for="confirm_password">Confirm New Password</label> <input type="password" name="confirm_password" id="confirm_password" required minlength="6"> </div> <div> <button type="submit" class="form-button mt-2">Update Password</button> </div> </form>
        </div>

    </div><!-- End Container -->

    <!-- <<< NEW: Dark Mode Toggle Script >>> -->
    <script>
      const themeToggleButton = document.getElementById('theme-toggle-button');
      const lightIcon = document.getElementById('theme-toggle-light-icon');
      const darkIcon = document.getElementById('theme-toggle-dark-icon');

      // Function to update icon visibility based on current theme
      function updateIcon() {
        if (document.documentElement.classList.contains('dark')) {
          lightIcon.classList.add('hidden');
          darkIcon.classList.remove('hidden');
        } else {
          lightIcon.classList.remove('hidden');
          darkIcon.classList.add('hidden');
        }
      }

      // Set initial icon state on page load
      updateIcon();

      // Add click listener to the toggle button
      themeToggleButton.addEventListener('click', () => {
        // Toggle the 'dark' class on the <html> element
        document.documentElement.classList.toggle('dark');

        // Update localStorage based on the new state
        if (document.documentElement.classList.contains('dark')) {
          localStorage.theme = 'dark';
        } else {
          localStorage.theme = 'light';
        }

        // Update the button icon
        updateIcon();
      });
    </script>
    <!-- End Dark Mode Toggle Script -->

</body>
</html>