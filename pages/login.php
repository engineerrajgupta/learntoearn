<?php
 session_start();
 require "../includes/db.php"; // Ensure this path is correct

 $error_message = ''; // Variable to hold error messages

 // If user is already logged in, redirect to dashboard
 if (isset($_SESSION['user_id'])) {
     header("Location: dashboard.php");
     exit; // Stop further script execution
 }


 if ($_SERVER["REQUEST_METHOD"] == "POST") {
     $email = trim($_POST['email']); // Trim whitespace
     $password = $_POST['password']; // No trim needed for password input itself

     if (empty($email) || empty($password)) {
         $error_message = "Please enter both email and password.";
     } else {
         try {
             $stmt = $pdo->prepare("SELECT id, name, email, password, role FROM users WHERE email = ? LIMIT 1"); // Select necessary fields, limit 1
             $stmt->execute([$email]);
             $user = $stmt->fetch(PDO::FETCH_ASSOC); // Use FETCH_ASSOC for associative array

             if ($user && password_verify($password, $user['password'])) {
                 // Password is correct, start session
                 session_regenerate_id(true); // Regenerate session ID for security

                 $_SESSION['user_id'] = $user['id'];
                 $_SESSION['role'] = $user['role'];

                 // *** IMPORTANT: Adjust 'name' if your user's name column is different ***
                 $_SESSION['username'] = $user['name']; // Store the user's actual name/username

                 header("Location: dashboard.php"); // Redirect to dashboard
                 exit; // Stop script execution after redirect
             } else {
                 // Invalid credentials
                 $error_message = "Invalid email or password.";
             }
         } catch (PDOException $e) {
             // Handle potential database errors gracefully
             error_log("Database error during login: " . $e->getMessage()); // Log the actual error
             $error_message = "An error occurred. Please try again later."; // User-friendly message
         }
     }
 }
?>

<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - LearnToEarn</title>
    <!-- Link Tailwind CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
    <!-- Optional: Link Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        /* Optional: Add custom styles or overrides here */
        /* Add a subtle background pattern or gradient if desired */
        body {
            /* Example gradient background */
            /* background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%); */
        }
        .dark body {
             /* Example dark gradient background */
            /* background: linear-gradient(135deg, #2d3748 0%, #1a202c 100%); */
        }
    </style>
</head>
<body class="bg-gray-100 dark:bg-gray-900">

    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="w-full max-w-md bg-white dark:bg-gray-800 rounded-xl shadow-xl p-8 space-y-6">

            <!-- Logo and Welcome Message -->
            <div class="text-center space-y-2">
                 <!-- Optional: Add Logo Image here -->
                 <!-- <img src="../assets/images/logo.svg" alt="LearnToEarn Logo" class="mx-auto h-12 w-auto mb-4"> -->
                <h1 class="text-3xl font-bold text-indigo-600 dark:text-indigo-400">
                    Learn<span class="text-gray-800 dark:text-gray-200">To</span>Earn
                </h1>
                <p class="text-gray-600 dark:text-gray-400">Welcome back! Please login to your account.</p>
            </div>

            <!-- Display Login Errors -->
            <?php if (!empty($error_message)): ?>
                <div class="bg-red-100 dark:bg-red-900 border border-red-400 dark:border-red-700 text-red-700 dark:text-red-200 px-4 py-3 rounded relative" role="alert">
                    <span class="block sm:inline"><i class="fas fa-exclamation-circle mr-2"></i><?php echo htmlspecialchars($error_message); ?></span>
                </div>
            <?php endif; ?>

            <!-- Login Form -->
            <form method="post" action="login.php" class="space-y-4">
                <div>
                    <label for="email" class="sr-only">Email</label>
                    <input type="email" name="email" id="email" placeholder="Email Address" required
                           class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:bg-gray-700 dark:text-gray-100"
                           value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; // Keep email filled on error ?>">
                </div>
                <div>
                    <label for="password" class="sr-only">Password</label>
                    <input type="password" name="password" id="password" placeholder="Password" required
                           class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 dark:bg-gray-700 dark:text-gray-100">
                </div>
                 <!-- Optional: Add "Forgot Password?" link here -->
                 <!-- <div class="text-right">
                    <a href="forgot-password.php" class="text-sm text-indigo-600 dark:text-indigo-400 hover:underline">Forgot Password?</a>
                 </div> -->
                <div>
                    <button type="submit"
                            class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2 px-4 rounded-md transition duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800">
                        <i class="fas fa-sign-in-alt mr-2"></i>Login
                    </button>
                </div>
            </form>

            <!-- Link to Register Page -->
            <p class="text-center text-sm text-gray-600 dark:text-gray-400">
                Don't have an account?
                <a href="register.php" class="font-medium text-indigo-600 dark:text-indigo-400 hover:underline">
                    Register here
                </a>
            </p>

        </div>
    </div>

    <!-- Optional: Add Theme Toggle Script if needed for consistency -->
    <!-- <script> /* Theme toggle JS from dashboard */ </script> -->

</body>
</html>