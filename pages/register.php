<?php
 session_start(); // Good practice to start session even if not immediately used
 require "../includes/db.php"; // Ensure this path is correct

 $error_message = '';
 $success_message = '';
 $input_name = '';
 $input_email = '';
 $input_role = '';

 // If user is already logged in, redirect them away from register page
 if (isset($_SESSION['user_id'])) {
     header("Location: dashboard.php");
     exit;
 }

 if ($_SERVER["REQUEST_METHOD"] == "POST") {
     // Sanitize and retrieve inputs
     $name = trim($_POST['name']);
     $email = trim($_POST['email']);
     $password = $_POST['password']; // Get password, hash later after validation
     $role = isset($_POST['role']) ? trim($_POST['role']) : ''; // Get role

     // Store inputs for pre-filling form on error
     $input_name = $name;
     $input_email = $email;
     $input_role = $role;

     // --- Basic Validation ---
     if (empty($name) || empty($email) || empty($password) || empty($role)) {
         $error_message = "Please fill in all required fields.";
     } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
         $error_message = "Please enter a valid email address.";
     } elseif (!in_array($role, ['student', 'teacher'])) {
         $error_message = "Invalid role selected.";
     } // Optional: Add password strength validation here
     // elseif (strlen($password) < 8) {
     //     $error_message = "Password must be at least 8 characters long.";
     // }
     else {
         // --- Database Operations ---
         try {
             // 1. Check if email already exists
             $stmt_check = $pdo->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
             $stmt_check->execute([$email]);
             if ($stmt_check->fetch()) {
                 $error_message = "This email address is already registered. Please <a href='login.php' class='font-semibold underline'>login</a>.";
             } else {
                 // 2. Hash the password
                 $hashed_password = password_hash($password, PASSWORD_BCRYPT);

                 // 3. Insert the new user
                 $stmt_insert = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");

                 if ($stmt_insert->execute([$name, $email, $hashed_password, $role])) {
                     // Registration successful - Redirect to login page with a success message (optional)
                     // Using session flash message is better, but for simplicity:
                     $_SESSION['register_success'] = "Registration successful! Please login."; // Store success msg
                     header("Location: login.php");
                     exit; // Important: stop script execution after redirect
                 } else {
                     $error_message = "Error: Registration failed due to a server issue. Please try again.";
                     // Log detailed error: error_log("Registration failed for email $email: " . print_r($stmt_insert->errorInfo(), true));
                 }
             }
         } catch (PDOException $e) {
             error_log("Database error during registration: " . $e->getMessage()); // Log the actual error
             $error_message = "An error occurred during registration. Please try again later.";
         }
     }
 }

 // Check for success message from redirection (if used)
 if (isset($_SESSION['register_success'])) {
     $success_message = $_SESSION['register_success'];
     unset($_SESSION['register_success']); // Clear the message after displaying
 }

?>

<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - LearnToEarn</title>
    <!-- Link Tailwind CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
    <!-- Optional: Link Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        /* Optional: Add custom styles or overrides here */
    </style>
</head>
<body class="bg-gray-100 dark:bg-gray-900">

    <div class="flex items-center justify-center min-h-screen px-4 py-8">
        <div class="w-full max-w-lg bg-white dark:bg-gray-800 rounded-xl shadow-xl p-8 space-y-6">

            <!-- Logo and Welcome Message -->
            <div class="text-center space-y-2">
                <h1 class="text-3xl font-bold text-indigo-600 dark:text-indigo-400">
                    Learn<span class="text-gray-800 dark:text-gray-200">To</span>Earn
                </h1>
                <p class="text-gray-600 dark:text-gray-400">Create your account to start learning or teaching!</p>
            </div>

            <!-- Display Feedback Messages -->
            <?php if (!empty($error_message)): ?>
                <div class="bg-red-100 dark:bg-red-900 border border-red-400 dark:border-red-700 text-red-700 dark:text-red-200 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline"><i class="fas fa-exclamation-circle mr-2"></i><?php echo $error_message; // Already contains safe HTML if needed ?></span>
                </div>
            <?php endif; ?>
             <?php if (!empty($success_message)): // Usually shown on the login page after redirect ?>
                 <div class="bg-green-100 dark:bg-green-900 border border-green-400 dark:border-green-700 text-green-700 dark:text-green-200 px-4 py-3 rounded relative" role="alert">
                     <span class="block sm:inline"><i class="fas fa-check-circle mr-2"></i><?php echo htmlspecialchars($success_message); ?></span>
                 </div>
            <?php endif; ?>

            <!-- Registration Form -->
            <form method="post" action="register.php" class="space-y-4">
                <div>
                    <label for="name" class="sr-only">Full Name</label>
                    <input type="text" name="name" id="name" placeholder="Full Name" required
                           class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:bg-gray-700 dark:text-gray-100"
                           value="<?php echo htmlspecialchars($input_name); // Pre-fill value ?>">
                </div>
                <div>
                    <label for="email" class="sr-only">Email</label>
                    <input type="email" name="email" id="email" placeholder="Email Address" required
                           class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:bg-gray-700 dark:text-gray-100"
                           value="<?php echo htmlspecialchars($input_email); // Pre-fill value ?>">
                </div>
                <div>
                    <label for="password" class="sr-only">Password</label>
                    <input type="password" name="password" id="password" placeholder="Password" required
                           class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:bg-gray-700 dark:text-gray-100">
                           <!-- Optional: Add password strength meter here -->
                </div>
                <div>
                    <label for="role" class="sr-only">Register as</label>
                    <select name="role" id="role" required
                            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:bg-gray-700 dark:text-gray-100 appearance-none bg-white dark:bg-gray-700"
                            style="background-image: url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20viewBox%3D%220%200%2020%2020%22%20fill%3D%22%23a0aec0%22%3E%3Cpath%20fill-rule%3D%22evenodd%22%20d%3D%22M10%2012.586l-4.293-4.293a1%201%200%201%200-1.414%201.414l5%205a1%201%200%200%200%201.414%200l5-5a1%201%200%200%200-1.414-1.414L10%2012.586z%22%20clip-rule%3D%22evenodd%22%2F%3E%3C%2Fsvg%3E'); background-repeat: no-repeat; background-position: right 0.75rem center; background-size: 1.25em 1.25em;">
                            <!-- Inline SVG for dropdown arrow -->
                        <option value="" disabled <?php echo empty($input_role) ? 'selected' : ''; ?>>-- Select Role --</option>
                        <option value="student" <?php echo ($input_role === 'student') ? 'selected' : ''; ?>>Student</option>
                        <option value="teacher" <?php echo ($input_role === 'teacher') ? 'selected' : ''; ?>>Teacher</option>
                    </select>
                </div>
                <div>
                    <button type="submit"
                            class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2 px-4 rounded-md transition duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800">
                        <i class="fas fa-user-plus mr-2"></i>Register
                    </button>
                </div>
            </form>

            <!-- Link to Login Page -->
            <p class="text-center text-sm text-gray-600 dark:text-gray-400">
                Already have an account?
                <a href="login.php" class="font-medium text-indigo-600 dark:text-indigo-400 hover:underline">
                    Login here
                </a>
            </p>

        </div>
    </div>

</body>
</html>