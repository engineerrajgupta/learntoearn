<?php
session_start();
// <<< Corrected Path >>>
require "includes/db.php";

// --- Redirect if already logged in ---
if (isset($_SESSION['user_id'])) {
    // <<< Corrected Path >>>
    header("Location: pages/dashboard.php");
    exit;
}

// --- Initialize Variables ---
$login_error_message = '';
$register_error_message = '';
$register_success_message = '';

// Forgot Password Modal 1 (Verification)
$forgot_error_message = '';
$forgot_input_email = '';
$forgot_input_dob = '';
$show_forgot_modal_on_load = false;

// Reset Password Modal 2 (Set New Password)
$reset_error_message = '';
$reset_success_message = '';
$reset_input_email = ''; // To pass email from verification to reset modal
$show_reset_modal_on_load = false;

// General Inputs / State
$input_name = '';
$input_email = '';
$input_dob = '';
$input_role = '';
$last_form = 'login';

// --- Check for Registration Success Message from Session ---
if (isset($_SESSION['register_success'])) {
    $register_success_message = $_SESSION['register_success'];
    $last_form = 'login';
    unset($_SESSION['register_success']);
}

// --- Handle POST Requests ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $formType = isset($_POST['form_type']) ? $_POST['form_type'] : null;

    // --- LOGIN LOGIC ---
    if ($formType === 'login') {
        $last_form = 'login';
        $email = trim($_POST['email']);
        $password = $_POST['password'];
        $input_email = $email;

        if (empty($email) || empty($password)) {
            $login_error_message = "Please enter both email and password.";
        } else {
            try {
                $stmt = $pdo->prepare("SELECT id, name, email, password, role FROM users WHERE email = ? LIMIT 1");
                $stmt->execute([$email]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($user && password_verify($password, $user['password'])) {
                    session_regenerate_id(true);
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['role'] = $user['role'];
                    $_SESSION['username'] = $user['name'];
                    // <<< Corrected Path >>>
                    header("Location: pages/dashboard.php");
                    exit;
                } else {
                    $login_error_message = "Invalid email or password.";
                }
            } catch (PDOException $e) {
                error_log("Database error during login: " . $e->getMessage());
                $login_error_message = "An error occurred. Please try again later.";
            }
        }
    }

    // --- REGISTRATION LOGIC ---
    elseif ($formType === 'register') {
        $last_form = 'register';
        $name = trim($_POST['name']);
        $email = trim($_POST['email']);
        $dob = trim($_POST['dob']);
        $password = $_POST['password'];
        $role = isset($_POST['role']) ? trim($_POST['role']) : '';

        $input_name = $name;
        $input_email = $email;
        $input_dob = $dob;
        $input_role = $role;

        $d = DateTime::createFromFormat('Y-m-d', $dob);
        $is_valid_date = $d && $d->format('Y-m-d') === $dob;

        if (empty($name) || empty($email) || empty($password) || empty($role) || empty($dob)) {
            $register_error_message = "Please fill in all required fields.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $register_error_message = "Please enter a valid email address.";
        } elseif (!$is_valid_date) {
             $register_error_message = "Please enter a valid date of birth.";
        } elseif (!in_array($role, ['student', 'teacher'])) {
            $register_error_message = "Invalid role selected.";
        } elseif (strlen($password) < 6) {
             $register_error_message = "Password must be at least 6 characters long.";
        } else {
            try {
                $stmt_check = $pdo->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
                $stmt_check->execute([$email]);
                if ($stmt_check->fetch()) {
                    $register_error_message = "Email already registered. Please <button type='button' class='font-semibold underline text-red-700 hover:text-red-900 signinbtn-inline'>sign in</button>.";
                } else {
                    $hashed_password = password_hash($password, PASSWORD_BCRYPT);
                    $stmt_insert = $pdo->prepare("INSERT INTO users (name, email, dob, password, role) VALUES (?, ?, ?, ?, ?)");

                    if ($stmt_insert->execute([$name, $email, $dob, $hashed_password, $role])) {
                        // Store success message in session and redirect to clear POST data
                        $_SESSION['register_success'] = "Registration successful! You can now sign in.";
                        header("Location: " . htmlspecialchars($_SERVER["PHP_SELF"])); // Redirect to the same page
                        exit;
                    } else {
                        $register_error_message = "Registration failed due to a server issue.";
                        error_log("Registration failed for email $email: " . print_r($stmt_insert->errorInfo(), true));
                    }
                }
            } catch (PDOException $e) {
                error_log("Database error during registration: " . $e->getMessage());
                $register_error_message = "An error occurred during registration.";
            }
        }
    }

    // --- FORGOT PASSWORD - STEP 1: VERIFICATION ---
    elseif ($formType === 'forgot_password_verify') {
        $last_form = 'login';
        $email = trim($_POST['email']);
        $dob = trim($_POST['dob']);
        $forgot_input_email = $email;
        $forgot_input_dob = $dob;

        $d = DateTime::createFromFormat('Y-m-d', $dob);
        $is_valid_date = $d && $d->format('Y-m-d') === $dob;

        if (empty($email) || empty($dob)) {
            $forgot_error_message = "Please enter both email and date of birth.";
            $show_forgot_modal_on_load = true;
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
             $forgot_error_message = "Please enter a valid email address.";
             $show_forgot_modal_on_load = true;
        } elseif (!$is_valid_date) {
             $forgot_error_message = "Please enter a valid date of birth.";
             $show_forgot_modal_on_load = true;
        } else {
            try {
                $stmt = $pdo->prepare("SELECT id, dob FROM users WHERE email = ? LIMIT 1");
                $stmt->execute([$email]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($user && isset($user['dob']) && $user['dob'] === $dob) {
                    $show_reset_modal_on_load = true; // Trigger next modal
                    $reset_input_email = $email; // Pass email to next step
                    $show_forgot_modal_on_load = false; // Hide current modal
                } else {
                    $forgot_error_message = "Email not found or date of birth does not match our records.";
                    $show_forgot_modal_on_load = true;
                }
            } catch (PDOException $e) {
                error_log("Database error during forgot password check: " . $e->getMessage());
                $forgot_error_message = "An error occurred verifying your details. Please try again later.";
                $show_forgot_modal_on_load = true;
            }
        }
    }

    // --- FORGOT PASSWORD - STEP 2: RESET PASSWORD ---
    elseif ($formType === 'reset_password') {
         $last_form = 'login';
         $email = trim($_POST['email']);
         $new_password = $_POST['new_password'];
         $confirm_password = $_POST['confirm_password'];
         $reset_input_email = $email; // Keep email for potential re-display

         if (empty($new_password) || empty($confirm_password)) {
             $reset_error_message = "Please enter and confirm your new password.";
             $show_reset_modal_on_load = true;
         } elseif ($new_password !== $confirm_password) {
             $reset_error_message = "The passwords do not match.";
             $show_reset_modal_on_load = true;
         } elseif (strlen($new_password) < 6) {
             $reset_error_message = "Password must be at least 6 characters long.";
             $show_reset_modal_on_load = true;
         } else {
             try {
                 $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
                 $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE email = ?");

                 if ($stmt->execute([$hashed_password, $email])) {
                     $reset_success_message = "Password updated successfully! You can now log in using your new password.";
                     // Don't clear email here, keep it for display if needed
                     $show_reset_modal_on_load = true; // Keep modal open to show success
                 } else {
                     $reset_error_message = "Failed to update password due to a server issue. Please try again.";
                     error_log("Password update failed for email $email: " . print_r($stmt->errorInfo(), true));
                     $show_reset_modal_on_load = true;
                 }
             } catch (PDOException $e) {
                 error_log("Database error during password update: " . $e->getMessage());
                 $reset_error_message = "An error occurred updating the password. Please try again later.";
                 $show_reset_modal_on_load = true;
             }
         }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Access - LearnToEarn</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@200;300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <style>
        /* --- Blue Theme --- */
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #0a192f; /* Fallback dark blue background */
            overflow-x: hidden; /* Prevent horizontal scroll */
        }
        /* --- Updated focus styles for blue theme --- */
        input:focus, select:focus {
            outline: none;
            border-color: #3b82f6; /* blue-500 */
            box-shadow: 0 0 0 2px #bfdbfe; /* blue-200 */
        }
        select {
            background-image: url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20viewBox%3D%220%200%2020%2020%22%20fill%3D%22%236b7280%22%3E%3Cpath%20fill-rule%3D%22evenodd%22%20d%3D%22M10%2012.586l-4.293-4.293a1%201%200%201%200-1.414%201.414l5%205a1%201%200%200%200%201.414%200l5-5a1%201%200%200%200-1.414-1.414L10%2012.586z%22%20clip-rule%3D%22evenodd%22%2F%3E%3C%2Fsvg%3E');
            background-repeat: no-repeat; background-position: right 0.75rem center; background-size: 1.25em 1.25em;
            -webkit-appearance: none; -moz-appearance: none; appearance: none;
        }
        input[type="date"]:required:invalid::-webkit-datetime-edit { color: transparent; }
        input[type="date"]:focus::-webkit-datetime-edit { color: black !important; }
        /* Basic modal animation (Optional) */
        .modal-content { /* Apply transition to the inner content div */
             transition: opacity 300ms ease-out, transform 300ms ease-out;
        }
        .modal-enter-from { opacity: 0; transform: scale(0.95); }
        .modal-enter-to { opacity: 1; transform: scale(1); }
        .modal-leave-from { opacity: 1; transform: scale(1); }
        .modal-leave-to { opacity: 0; transform: scale(0.95); }

        /* Typed.js cursor style */
        .typed-cursor {
            opacity: 1;
            animation: typedjsBlink 0.7s infinite;
            -webkit-animation: typedjsBlink 0.7s infinite;
            animation: typedjsBlink 0.7s infinite;
            color: #93c5fd; /* Light blue cursor */
        }
        @keyframes typedjsBlink {
            50% { opacity: 0.0; }
        }
        @-webkit-keyframes typedjsBlink {
            0% { opacity: 1; }
            50% { opacity: 0.0; }
            100% { opacity: 1; }
        }
    </style>
</head>
<!-- <<< CHANGED: Use flex justify-center items-center to center the container vertically >>> -->
<body class="flex justify-center items-center min-h-screen transition-colors duration-500">

    <!-- Animated Title (Stays at top) -->
    <div class="absolute top-8 left-1/2 transform -translate-x-1/2 z-20 text-center w-full px-4">
        <h1 class="text-3xl sm:text-4xl font-bold text-blue-300">
            <span id="animated-text"></span>
        </h1>
    </div>

    <!-- tsParticles container -->
    <div id="tsparticles" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: -1;"></div>

    <!-- Forgot Password Modal 1: Verification -->
    <div id="forgotPasswordVerifyModal" class="fixed inset-0 bg-gray-800 bg-opacity-70 flex justify-center items-center z-40 p-4 hidden"> <!-- Start hidden -->
        <!-- <<< CHANGED: Added modal-content class and initial state classes >>> -->
        <div class="modal-content bg-white p-6 md:p-8 rounded-lg shadow-xl max-w-md w-full relative modal-enter-from">
            <button id="closeVerifyModalBtn" type="button" class="absolute top-2 right-3 text-gray-500 hover:text-gray-800 text-2xl font-bold">×</button>
            <h3 class="text-xl font-semibold text-gray-800 mb-6 text-center border-b pb-3">Verify Your Identity</h3>

            <?php if (!empty($forgot_error_message)): ?> <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative text-sm" role="alert"> <span><?php echo htmlspecialchars($forgot_error_message); ?></span> </div> <?php endif; ?>

            <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" class="space-y-4">
                <input type="hidden" name="form_type" value="forgot_password_verify">
                <div>
                    <label for="forgot_email" class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                    <input type="email" name="email" id="forgot_email" placeholder="Enter your registered email" required class="w-full p-2.5 text-base border border-gray-400 rounded" value="<?php echo htmlspecialchars($forgot_input_email); ?>">
                </div>
                <div>
                    <label for="forgot_dob" class="block text-sm font-medium text-gray-700 mb-1">Date of Birth</label>
                    <input type="date" name="dob" id="forgot_dob" required class="w-full p-2.5 text-base border border-gray-400 rounded" value="<?php echo htmlspecialchars($forgot_input_dob); ?>" title="Enter your Date of Birth">
                    <p class="text-xs text-gray-500 mt-1"><b> Please confirm your date of birth used during registration. </b></p>
                </div>
                <div>
                    <input type="submit" value="Verify Identity" class="w-full bg-blue-600 border-none text-white cursor-pointer py-2.5 px-4 rounded hover:bg-blue-700 transition-colors duration-200 font-semibold">
                </div>
            </form>
        </div>
    </div>

    <!-- Reset Password Modal 2: Set New Password -->
    <div id="resetPasswordModal" class="fixed inset-0 bg-gray-800 bg-opacity-70 flex justify-center items-center z-50 p-4 hidden"> <!-- Start hidden -->
         <!-- <<< CHANGED: Added modal-content class and initial state classes >>> -->
        <div class="modal-content bg-white p-6 md:p-8 rounded-lg shadow-xl max-w-md w-full relative modal-enter-from">
            <button id="closeResetModalBtn" type="button" class="absolute top-2 right-3 text-gray-500 hover:text-gray-800 text-2xl font-bold">×</button>
            <h3 class="text-xl font-semibold text-gray-800 mb-6 text-center border-b pb-3">Set New Password</h3>

            <?php if (!empty($reset_error_message)): ?> <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative text-sm" role="alert"> <span><?php echo htmlspecialchars($reset_error_message); ?></span> </div> <?php endif; ?>
            <?php if (!empty($reset_success_message)): ?> <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative text-sm" role="alert"> <span><?php echo htmlspecialchars($reset_success_message); ?></span> </div> <?php endif; ?>

            <?php if (empty($reset_success_message)): ?>
                <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" class="space-y-4">
                    <input type="hidden" name="form_type" value="reset_password">
                    <input type="hidden" name="email" value="<?php echo htmlspecialchars($reset_input_email); ?>">
                    <div>
                        <label for="new_password" class="block text-sm font-medium text-gray-700 mb-1">New Password</label>
                        <input type="password" name="new_password" id="new_password" placeholder="Enter new password (min. 6 chars)" required class="w-full p-2.5 text-base border border-gray-400 rounded">
                    </div>
                     <div>
                        <label for="confirm_password" class="block text-sm font-medium text-gray-700 mb-1">Confirm New Password</label>
                        <input type="password" name="confirm_password" id="confirm_password" placeholder="Confirm new password" required class="w-full p-2.5 text-base border border-gray-400 rounded">
                    </div>
                    <div>
                        <input type="submit" value="Update Password" class="w-full bg-blue-600 border-none text-white cursor-pointer py-2.5 px-4 rounded hover:bg-blue-700 transition-colors duration-200 font-semibold">
                    </div>
                </form>
             <?php endif; ?>
             <?php if (!empty($reset_success_message)): ?>
                 <div class="mt-4 text-center">
                     <button id="closeResetSuccessBtn" type="button" class="bg-blue-600 border-none text-white cursor-pointer py-2 px-4 rounded hover:bg-blue-700 transition-colors duration-200 font-semibold">Close</button>
                 </div>
             <?php endif; ?>
        </div>
    </div>


    <!-- Main Login/Register Container -->
    <!-- <<< CHANGED: Added id, mt-16 for spacing below title, animation classes (opacity, scale, transition) >>> -->
    <div id="main-container" class="container relative w-[800px] h-[550px] mx-5 mt-16 opacity-0 scale-95 transition-all duration-700 ease-out" style="z-index: 1;">
        <!-- Blue background panel -->
        <div class="loginbg absolute top-[40px] w-full h-[470px] flex justify-center items-stretch bg-blue-600/80 backdrop-blur-sm shadow-lg rounded-lg overflow-hidden">
            <!-- Left Prompt Box -->
            <div class="box relative w-1/2 h-full flex flex-col justify-center items-center text-center px-4 text-white">
                <h2 class="text-lg font-medium mb-3">Already Have an Account?</h2>
                <button type="button" class="signinbtn cursor-pointer py-2 px-5 bg-white text-gray-800 text-base font-medium border-none rounded hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-400 focus:ring-offset-blue-600">Sign in</button>
            </div>
            <!-- Right Prompt Box -->
            <div class="box relative w-1/2 h-full flex flex-col justify-center items-center text-center px-4 text-white">
                <h2 class="text-lg font-medium mb-3">Don't Have an Account?</h2>
                <button type="button" class="signupbtn cursor-pointer py-2 px-5 bg-white text-gray-800 text-base font-medium border-none rounded hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-400 focus:ring-offset-blue-600">Sign up</button>
            </div>
        </div>

        <!-- Sliding Form Box -->
        <div id="formBox" class="formbx absolute top-0 left-0 w-1/2 h-full bg-white z-10 flex justify-center items-center shadow-xl transition-all duration-500 ease-in-out overflow-hidden rounded-lg">
             <!-- Sign In Form -->
            <div id="signInForm" class="form signinform absolute w-full p-10 md:p-12 transition-all duration-500 left-0">
                <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" class="w-full flex flex-col">
                     <h3 class="text-xl text-gray-800 mb-5 font-medium text-center border-b pb-2 border-gray-200">Sign In</h3>
                     <?php if (!empty($login_error_message)): ?> <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative text-sm" role="alert"> <span><?php echo htmlspecialchars($login_error_message); ?></span> </div> <?php endif; ?>
                     <?php if (!empty($register_success_message)): ?> <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative text-sm" role="alert"> <span><?php echo htmlspecialchars($register_success_message); ?></span> </div> <?php endif; ?>

                    <input type="hidden" name="form_type" value="login">
                    <label for="login_email" class="sr-only">Email</label>
                    <input type="email" name="email" id="login_email" placeholder="Email Address" required class="w-full mb-4 p-2.5 text-base border border-gray-400 rounded" value="<?php echo ($last_form === 'login' && empty($register_success_message)) ? htmlspecialchars($input_email) : ''; ?>">
                    <label for="login_password" class="sr-only">Password</label>
                    <input type="password" name="password" id="login_password" placeholder="Password" required class="w-full mb-4 p-2.5 text-base border border-gray-400 rounded">

                    <div class="flex items-center justify-between">
                         <input type="submit" value="Login" class="bg-blue-600 border-none text-white max-w-[100px] cursor-pointer py-2 px-4 rounded hover:bg-blue-700 transition-colors duration-200 font-semibold">
                         <a href="#" id="forgotPasswordLink" class="forgot text-sm text-blue-600 hover:text-blue-800 hover:underline">Forgot password?</a>
                    </div>
                </form>
            </div>

            <!-- Sign Up Form -->
            <div id="signUpForm" class="form signupform absolute w-full p-10 md:p-12 transition-all duration-500 left-full">
                <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" class="w-full flex flex-col">
                    <h3 class="text-xl text-gray-800 mb-5 font-medium text-center border-b pb-2 border-gray-200">Sign Up</h3>
                     <?php if (!empty($register_error_message)): ?> <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative text-sm" role="alert"> <span class="block sm:inline"><?php echo $register_error_message; ?></span> </div> <?php endif; ?>

                    <input type="hidden" name="form_type" value="register">
                    <label for="register_name" class="sr-only">Full Name</label>
                    <input type="text" name="name" id="register_name" placeholder="Full Name" required class="w-full mb-4 p-2.5 text-base border border-gray-400 rounded" value="<?php echo ($last_form === 'register') ? htmlspecialchars($input_name) : ''; ?>">
                    <label for="register_email" class="sr-only">Email</label>
                    <input type="email" name="email" id="register_email" placeholder="Email Address" required class="w-full mb-4 p-2.5 text-base border border-gray-400 rounded" value="<?php echo ($last_form === 'register') ? htmlspecialchars($input_email) : ''; ?>">
                    <label for="register_dob" class="sr-only">Date of Birth</label>
                    <input type="date" name="dob" id="register_dob" required class="w-full mb-4 p-2.5 text-base border border-gray-400 rounded" value="<?php echo ($last_form === 'register') ? htmlspecialchars($input_dob) : ''; ?>" title="Date of Birth">
                    <label for="register_password" class="sr-only">Password</label>
                    <input type="password" name="password" id="register_password" placeholder="Password (min. 6 chars)" required class="w-full mb-4 p-2.5 text-base border border-gray-400 rounded">
                    <label for="role" class="sr-only">Register as</label>
                    <select name="role" id="role" required class="w-full mb-5 p-2.5 text-base border border-gray-400 rounded bg-white">
                        <option value="" disabled <?php echo empty($input_role) || $last_form !== 'register' ? 'selected' : ''; ?>>-- Select Role --</option>
                        <option value="student" <?php echo ($last_form === 'register' && $input_role === 'student') ? 'selected' : ''; ?>>Student</option>
                        <option value="teacher" <?php echo ($last_form === 'register' && $input_role === 'teacher') ? 'selected' : ''; ?>>Teacher</option>
                    </select>
                    <input type="submit" value="Sign Up" class="bg-blue-600 border-none text-white max-w-[110px] cursor-pointer py-2 px-4 rounded hover:bg-blue-700 self-start transition-colors duration-200 font-semibold">
                </form>
            </div>
        </div>
    </div>


    <!-- Include tsParticles library -->
    <script src="https://cdn.jsdelivr.net/npm/tsparticles@2.12.0/tsparticles.bundle.min.js"></script>
    <!-- Include Typed.js library -->
    <script src="https://unpkg.com/typed.js@2.1.0/dist/typed.umd.js"></script>

    <!-- Main Script for Form Logic & Initializations -->
    <script>
        // --- Selectors ---
        const signinbtn = document.querySelector('.signinbtn');
        const signupbtn = document.querySelector('.signupbtn');
        const signinbtnInline = document.querySelector('.signinbtn-inline');
        const formBox = document.getElementById('formBox');
        const signInForm = document.getElementById('signInForm');
        const signUpForm = document.getElementById('signUpForm');
        const mainContainer = document.getElementById('main-container'); // <<< Select main container

        // Modal 1 (Verify)
        const forgotPasswordVerifyModal = document.getElementById('forgotPasswordVerifyModal');
        const forgotPasswordLink = document.getElementById('forgotPasswordLink');
        const closeVerifyModalBtn = document.getElementById('closeVerifyModalBtn');

         // Modal 2 (Reset)
        const resetPasswordModal = document.getElementById('resetPasswordModal');
        const closeResetModalBtn = document.getElementById('closeResetModalBtn');
        const closeResetSuccessBtn = document.getElementById('closeResetSuccessBtn');

        // --- Sign In/Sign Up Toggle ---
        function showSignUp() {
            if (formBox && signInForm && signUpForm) {
                formBox.classList.add('left-1/2');
                signInForm.classList.add('left-[-100%]');
                signUpForm.classList.add('left-0');
                signUpForm.classList.remove('left-full');
            }
        }
        function showSignIn() {
             if (formBox && signInForm && signUpForm) {
                formBox.classList.remove('left-1/2');
                signInForm.classList.remove('left-[-100%]');
                signInForm.classList.add('left-0');
                signUpForm.classList.remove('left-0');
                signUpForm.classList.add('left-full');
             }
        }

        // Event listeners for main toggle buttons
        if(signupbtn) signupbtn.addEventListener('click', showSignUp);
        if(signinbtn) signinbtn.addEventListener('click', showSignIn);
        if(signinbtnInline) {
            signinbtnInline.addEventListener('click', (e) => {
                 e.preventDefault();
                 showSignIn();
            });
        }

        // --- Modal Control (Updated for new CSS classes) ---
        function toggleModal(modalElement, show) {
             if (!modalElement) return;
             const content = modalElement.querySelector('.modal-content'); // Target the inner div for animation
             if (!content) return;

             if (show) {
                modalElement.classList.remove('hidden');
                // Force reflow before adding 'enter' classes
                void modalElement.offsetWidth;
                content.classList.add('modal-enter-to');
                content.classList.remove('modal-enter-from');
             } else {
                content.classList.add('modal-leave-to');
                content.classList.remove('modal-enter-to');
                // Hide after animation (match transition duration in CSS)
                setTimeout(() => {
                     modalElement.classList.add('hidden');
                     // Reset classes for next time
                     content.classList.remove('modal-leave-to');
                     content.classList.add('modal-enter-from'); // Ready for next open
                }, 300); // Match CSS transition duration (300ms)
             }
        }

        // Open/Close listeners
        if(forgotPasswordLink) {
            forgotPasswordLink.addEventListener('click', (e) => {
                e.preventDefault();
                toggleModal(forgotPasswordVerifyModal, true);
                toggleModal(resetPasswordModal, false);
            });
        }
        if(closeVerifyModalBtn) closeVerifyModalBtn.addEventListener('click', () => toggleModal(forgotPasswordVerifyModal, false));
        if(forgotPasswordVerifyModal) {
             forgotPasswordVerifyModal.addEventListener('click', (e) => {
                 if (e.target === forgotPasswordVerifyModal) toggleModal(forgotPasswordVerifyModal, false);
             });
        }
        if(closeResetModalBtn) closeResetModalBtn.addEventListener('click', () => toggleModal(resetPasswordModal, false));
        if(closeResetSuccessBtn) closeResetSuccessBtn.addEventListener('click', () => toggleModal(resetPasswordModal, false));
        if(resetPasswordModal) {
            resetPasswordModal.addEventListener('click', (e) => {
                if (e.target === resetPasswordModal) toggleModal(resetPasswordModal, false);
            });
        }

        // --- Retain Form View & Modal State on Page Load ---
        const lastSubmittedForm = "<?php echo $last_form; ?>";
        const shouldShowForgotModal = <?php echo $show_forgot_modal_on_load ? 'true' : 'false'; ?>;
        const shouldShowResetModal = <?php echo $show_reset_modal_on_load ? 'true' : 'false'; ?>;
        const hasRegisterError = <?php echo !empty($register_error_message) ? 'true' : 'false'; ?>;
        const hasLoginError = <?php echo !empty($login_error_message) ? 'true' : 'false'; ?>;

        // Run on DOMContentLoaded
        document.addEventListener('DOMContentLoaded', () => {

            // <<< NEW: Animate main container entrance >>>
            if (mainContainer) {
                 // Small delay helps ensure the transition triggers reliably
                setTimeout(() => {
                    mainContainer.classList.remove('opacity-0', 'scale-95');
                    mainContainer.classList.add('opacity-100', 'scale-100');
                }, 50); // 50ms delay
            }


            // Initialize Typed.js
            var options = {
                strings: ['Welcome To LearnToEarn'],
                typeSpeed: 60,
                backSpeed: 30,
                backDelay: 1000,
                startDelay: 500, // Delay slightly longer after main container fades in
                loop: false,
                showCursor: true,
                cursorChar: '_',
                smartBackspace: false
            };
            // Start Typed.js slightly after main container animation starts
             setTimeout(() => {
                 var typed = new Typed('#animated-text', options);
             }, 300); // Start typing after 300ms (adjust as needed)


            // Initialize tsParticles
             tsParticles.load("tsparticles", {
                background: { color: "#0a192f" },
                particles: {
                    number: { value: 60, density: { enable: true, value_area: 800 } },
                    color: { value: ["#3b82f6", "#60a5fa", "#93c5fd", "#ffffff"] },
                    shape: { type: "circle" },
                    opacity: { value: 0.5, random: true, anim: { enable: true, speed: 0.4, opacity_min: 0.1, sync: false } },
                    size: { value: 2, random: true, anim: { enable: false } },
                    links: { enable: true, distance: 120, color: "#60a5fa", opacity: 0.3, width: 1 },
                    move: { enable: true, speed: 1, direction: "none", random: true, straight: false, out_mode: "out", bounce: false }
                },
                interactivity: {
                    detect_on: "canvas",
                    events: { onhover: { enable: true, mode: "grab" }, onclick: { enable: true, mode: "push" }, resize: true },
                    modes: {
                        grab: { distance: 150, line_linked: { opacity: 0.7 } },
                        push: { particles_nb: 3 }
                    }
                },
                detectRetina: true
            });


            // Initialize modal visibility FIRST
            if (shouldShowResetModal) {
                 toggleModal(resetPasswordModal, true);
                 showSignIn(); // Keep Sign In form active
            } else if (shouldShowForgotModal) {
                 toggleModal(forgotPasswordVerifyModal, true);
                 showSignIn(); // Keep Sign In form active
            } else {
                // Default view logic
                if (lastSubmittedForm === 'register' && hasRegisterError) {
                    showSignUp();
                } else {
                     showSignIn();
                }
            }
        });

    </script>

</body>
</html>