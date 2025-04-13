<?php
session_start();
// <<< IMPORTANT: Make sure this path is correct relative to access.php >>>
require "../includes/db.php";

// --- Redirect if already logged in ---
if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php"); // Adjust if your dashboard has a different name/path
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

// --- Check for Registration Success Message from Session (Legacy) ---
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
                    header("Location: dashboard.php"); // Adjust if needed
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
        } elseif (!in_array($role, ['student', 'teacher'])) { // Adjust roles if needed
            $register_error_message = "Invalid role selected.";
        } elseif (strlen($password) < 6) { // Adjust minimum password length if needed
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
                        $register_success_message = "Registration successful! You can now sign in.";
                        $input_name = ''; $input_email = ''; $input_dob = ''; $input_role = '';
                        $last_form = 'login';
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
                // Fetch only the DOB, as we don't need other details here
                $stmt = $pdo->prepare("SELECT id, dob FROM users WHERE email = ? LIMIT 1");
                $stmt->execute([$email]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);

                // *** SECURITY WARNING: DOB comparison is insecure ***
                if ($user && isset($user['dob']) && $user['dob'] === $dob) {
                    // Verification Successful - Prepare to show Reset Modal
                    $show_reset_modal_on_load = true;
                    $reset_input_email = $email; // Pass email to the next step
                } else {
                    // Verification Failed
                    $forgot_error_message = "Email not found or date of birth does not match our records.";
                    $show_forgot_modal_on_load = true; // Re-show verification modal
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
         $email = trim($_POST['email']); // Get email passed via hidden field
         $new_password = $_POST['new_password'];
         $confirm_password = $_POST['confirm_password'];
         $reset_input_email = $email; // Keep email available if reset fails

         // Validation
         if (empty($new_password) || empty($confirm_password)) {
             $reset_error_message = "Please enter and confirm your new password.";
             $show_reset_modal_on_load = true;
         } elseif ($new_password !== $confirm_password) {
             $reset_error_message = "The passwords do not match.";
             $show_reset_modal_on_load = true;
         } elseif (strlen($new_password) < 6) { // Use same length check as registration
             $reset_error_message = "Password must be at least 6 characters long.";
             $show_reset_modal_on_load = true;
         } else {
             // Update the password in the database
             try {
                 $hashed_password = password_hash($new_password, PASSWORD_BCRYPT);
                 $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE email = ?");

                 if ($stmt->execute([$hashed_password, $email])) {
                     // Password Update Successful
                     $reset_success_message = "Password updated successfully! You can now log in using your new password.";
                     $show_reset_modal_on_load = true; // Keep modal open to show success
                 } else {
                     // Password Update Failed (DB level)
                     $reset_error_message = "Failed to update password due to a server issue. Please try again.";
                     error_log("Password update failed for email $email: " . print_r($stmt->errorInfo(), true));
                     $show_reset_modal_on_load = true;
                 }
             } catch (PDOException $e) {
                 // General DB Error during update
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
        body { font-family: 'Poppins', sans-serif; background-image: url('./Images/Background/background.jpg'); background-repeat: no-repeat; background-size: cover; background-position: center; } /* <<< UPDATE BACKGROUND IMAGE PATH IF NEEDED */
        input:focus, select:focus { outline: none; border-color: #ec4899; box-shadow: 0 0 0 2px #fce7f3; }
        select { background-image: url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20viewBox%3D%220%200%2020%2020%22%20fill%3D%22%236b7280%22%3E%3Cpath%20fill-rule%3D%22evenodd%22%20d%3D%22M10%2012.586l-4.293-4.293a1%201%200%201%200-1.414%201.414l5%205a1%201%200%200%200%201.414%200l5-5a1%201%200%200%200-1.414-1.414L10%2012.586z%22%20clip-rule%3D%22evenodd%22%2F%3E%3C%2Fsvg%3E'); background-repeat: no-repeat; background-position: right 0.75rem center; background-size: 1.25em 1.25em; -webkit-appearance: none; -moz-appearance: none; appearance: none; }
        input[type="date"]:required:invalid::-webkit-datetime-edit { color: transparent; } input[type="date"]:focus::-webkit-datetime-edit { color: black !important; }
        /* Basic modal animation - Apply these if you want transitions */
        .modal-enter-active { transition: opacity 300ms ease-out, transform 300ms ease-out; }
        .modal-leave-active { transition: opacity 200ms ease-in, transform 200ms ease-in; }
        .modal-enter-from, .modal-leave-to { opacity: 0; transform: scale(0.95); }
        .modal-enter-to, .modal-leave-from { opacity: 1; transform: scale(1); }
    </style>
</head>
<body class="flex justify-center items-center min-h-screen transition-colors duration-500">

    <!-- Forgot Password Modal 1: Verification -->
    <div id="forgotPasswordVerifyModal" class="fixed inset-0 bg-gray-800 bg-opacity-70 flex justify-center items-center z-40 p-4 <?php echo $show_forgot_modal_on_load ? 'modal-enter-to' : 'hidden'; ?>">
        <div class="bg-white p-6 md:p-8 rounded-lg shadow-xl max-w-md w-full relative">
            <button id="closeVerifyModalBtn" type="button" class="absolute top-2 right-3 text-gray-500 hover:text-gray-800 text-2xl font-bold">×</button>
            <h3 class="text-xl font-semibold text-gray-800 mb-6 text-center border-b pb-3">Verify Your Identity</h3>

            <?php if (!empty($forgot_error_message)): ?> <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative text-sm" role="alert"> <span><?php echo htmlspecialchars($forgot_error_message); ?></span> </div> <?php endif; ?>

            <form method="post" action="" class="space-y-4">
                <input type="hidden" name="form_type" value="forgot_password_verify">
                <div>
                    <label for="forgot_email" class="block text-sm font-medium text-gray-700 mb-1">Email Address</label>
                    <input type="email" name="email" id="forgot_email" placeholder="Enter your registered email" required class="w-full p-2.5 text-base border border-gray-400 rounded" value="<?php echo htmlspecialchars($forgot_input_email); ?>">
                </div>
                <div>
                    <label for="forgot_dob" class="block text-sm font-medium text-gray-700 mb-1">Date of Birth</label>
                    <input type="date" name="dob" id="forgot_dob" required class="w-full p-2.5 text-base border border-gray-400 rounded" value="<?php echo htmlspecialchars($forgot_input_dob); ?>" title="Enter your Date of Birth">
                    <p class="text-xs text-gray-500 mt-1"><b> To reset your password, please confirm your date of birth used during registration. </b></p>
                </div>
                <div>
                    <input type="submit" value="Verify Identity" class="w-full bg-red-600 border-none text-white cursor-pointer py-2.5 px-4 rounded hover:bg-red-700 transition-colors duration-200 font-semibold">
                </div>
            </form>
        </div>
    </div>

    <!-- Reset Password Modal 2: Set New Password -->
    <div id="resetPasswordModal" class="fixed inset-0 bg-gray-800 bg-opacity-70 flex justify-center items-center z-50 p-4 <?php echo $show_reset_modal_on_load ? 'modal-enter-to' : 'hidden'; ?>">
        <div class="bg-white p-6 md:p-8 rounded-lg shadow-xl max-w-md w-full relative">
            <button id="closeResetModalBtn" type="button" class="absolute top-2 right-3 text-gray-500 hover:text-gray-800 text-2xl font-bold">×</button>
            <h3 class="text-xl font-semibold text-gray-800 mb-6 text-center border-b pb-3">Set New Password</h3>

            <?php if (!empty($reset_error_message)): ?> <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative text-sm" role="alert"> <span><?php echo htmlspecialchars($reset_error_message); ?></span> </div> <?php endif; ?>
            <?php if (!empty($reset_success_message)): ?> <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative text-sm" role="alert"> <span><?php echo htmlspecialchars($reset_success_message); ?></span> </div> <?php endif; ?>

            <!-- Hide form on success -->
            <?php if (empty($reset_success_message)): ?>
                <form method="post" action="" class="space-y-4"> 
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
                        <input type="submit" value="Update Password" class="w-full bg-red-600 border-none text-white cursor-pointer py-2.5 px-4 rounded hover:bg-red-700 transition-colors duration-200 font-semibold">
                    </div>
                </form>
             <?php endif; ?>
        </div>
    </div>


    <!-- Main Login/Register Container -->
    <div class="container relative w-[800px] h-[550px] m-5"> 
        <div class="loginbg absolute top-[40px] w-full h-[470px] flex justify-center items-center bg-red-500 shadow-lg"> 
            <div class="box relative w-1/2 h-full flex flex-col justify-center items-center text-center px-4">
                <h2 class="text-white text-lg font-medium mb-3">Already Have an Account?</h2>
                <button type="button" class="signinbtn cursor-pointer py-2 px-5 bg-white text-gray-700 text-base font-medium border-none rounded hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-400 focus:ring-offset-red-500">Sign in</button>
            </div>
            <div class="box relative w-1/2 h-full flex flex-col justify-center items-center text-center px-4">
                <h2 class="text-white text-lg font-medium mb-3">Don't Have an Account?</h2>
                <button type="button" class="signupbtn cursor-pointer py-2 px-5 bg-white text-gray-700 text-base font-medium border-none rounded hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-400 focus:ring-offset-red-500">Sign up</button>
            </div>
        </div>

        <!-- Sliding Form Box -->
        <div id="formBox" class="formbx absolute top-0 left-0 w-1/2 h-full bg-white z-10 flex justify-center items-center shadow-xl transition-all duration-500 ease-in-out overflow-hidden">
             <!-- Sign In Form -->
            <div id="signInForm" class="form signinform absolute w-full p-10 md:p-12 transition-all duration-500 left-0">
                <form method="post" action="" class="w-full flex flex-col"> 
                     <h3 class="text-xl text-gray-800 mb-5 font-medium text-center border-b pb-2 border-gray-200">Sign In</h3>
                     <?php if (!empty($login_error_message)): ?> <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative text-sm" role="alert"> <span><?php echo htmlspecialchars($login_error_message); ?></span> </div> <?php endif; ?>
                     <?php if (!empty($register_success_message)): ?> <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative text-sm" role="alert"> <span><?php echo htmlspecialchars($register_success_message); ?></span> </div> <?php endif; ?>

                    <input type="hidden" name="form_type" value="login">
                    <label for="login_email" class="sr-only">Email</label>
                    <input type="email" name="email" id="login_email" placeholder="Email Address" required class="w-full mb-4 p-2.5 text-base border border-gray-400 rounded" value="<?php echo ($last_form === 'login' && empty($register_success_message)) ? htmlspecialchars($input_email) : ''; ?>">
                    <label for="login_password" class="sr-only">Password</label>
                    <input type="password" name="password" id="login_password" placeholder="Password" required class="w-full mb-4 p-2.5 text-base border border-gray-400 rounded">

                    <div class="flex items-center justify-between">
                         <input type="submit" value="Login" class="bg-red-600 border-none text-white max-w-[100px] cursor-pointer py-2 px-4 rounded hover:bg-red-700 transition-colors duration-200">
                         <a href="#" id="forgotPasswordLink" class="forgot text-sm text-red-600 hover:text-red-800 hover:underline">Forgot password?</a>
                    </div>
                </form>
                
            </div>
            <!-- Sign Up Form -->
            <div id="signUpForm" class="form signupform absolute w-full p-10 md:p-12 transition-all duration-500 left-full">
                <form method="post" action="" class="w-full flex flex-col"> 
                    <h3 class="text-xl text-gray-800 mb-5 font-medium text-center border-b pb-2 border-gray-200">Sign Up</h3>
                     <?php if (!empty($register_error_message)): ?> <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative text-sm" role="alert"> <span class="block sm:inline"><?php echo $register_error_message; // Allow button html ?></span> </div> <?php endif; ?>

                    <input type="hidden" name="form_type" value="register">
                    <label for="register_name" class="sr-only">Full Name</label>
                    <input type="text" name="name" id="register_name" placeholder="Full Name" required class="w-full mb-4 p-2.5 text-base border border-gray-400 rounded" value="<?php echo htmlspecialchars($input_name); ?>">
                    <label for="register_email" class="sr-only">Email</label>
                    <input type="email" name="email" id="register_email" placeholder="Email Address" required class="w-full mb-4 p-2.5 text-base border border-gray-400 rounded" value="<?php echo htmlspecialchars($input_email); ?>">
                    <label for="register_dob" class="sr-only">Date of Birth</label>
                    <input type="date" name="dob" id="register_dob" required class="w-full mb-4 p-2.5 text-base border border-gray-400 rounded" value="<?php echo htmlspecialchars($input_dob); ?>" title="Date of Birth">
                    <label for="register_password" class="sr-only">Password</label>
                    <input type="password" name="password" id="register_password" placeholder="Password (min. 6 chars)" required class="w-full mb-4 p-2.5 text-base border border-gray-400 rounded">
                    <label for="role" class="sr-only">Register as</label>
                    <select name="role" id="role" required class="w-full mb-5 p-2.5 text-base border border-gray-400 rounded bg-white">
                        <option value="" disabled <?php echo empty($input_role) ? 'selected' : ''; ?>>-- Select Role --</option>
                        <option value="student" <?php echo ($input_role === 'student') ? 'selected' : ''; ?>>Student</option>
                        <option value="teacher" <?php echo ($input_role === 'teacher') ? 'selected' : ''; ?>>Teacher</option>
                        {/* Add other roles if necessary */}
                    </select>
                    <input type="submit" value="Sign Up" class="bg-red-600 border-none text-white max-w-[110px] cursor-pointer py-2 px-4 rounded hover:bg-red-700 self-start transition-colors duration-200">
                </form>
            </div>
        </div>
    </div>

    <script>
        // --- Selectors ---
        const signinbtn = document.querySelector('.signinbtn');
        const signupbtn = document.querySelector('.signupbtn');
        const signinbtnInline = document.querySelector('.signinbtn-inline'); // Button inside register error msg
        const formBox = document.getElementById('formBox');
        const signInForm = document.getElementById('signInForm');
        const signUpForm = document.getElementById('signUpForm');

        // Modal 1 (Verify)
        const forgotPasswordVerifyModal = document.getElementById('forgotPasswordVerifyModal');
        const forgotPasswordLink = document.getElementById('forgotPasswordLink');
        const closeVerifyModalBtn = document.getElementById('closeVerifyModalBtn');

         // Modal 2 (Reset)
        const resetPasswordModal = document.getElementById('resetPasswordModal');
        const closeResetModalBtn = document.getElementById('closeResetModalBtn');

        // --- Sign In/Sign Up Toggle ---
        function showSignUp() {
            formBox.classList.add('left-1/2'); signInForm.classList.add('left-[-100%]');
            signUpForm.classList.add('left-0'); signUpForm.classList.remove('left-full');
        }
        function showSignIn() {
            formBox.classList.remove('left-1/2'); signInForm.classList.remove('left-[-100%]');
            signInForm.classList.add('left-0'); signUpForm.classList.remove('left-0');
            signUpForm.classList.add('left-full');
        }
        signupbtn.addEventListener('click', showSignUp);
        signinbtn.addEventListener('click', showSignIn);
        if(signinbtnInline) { signinbtnInline.addEventListener('click', showSignIn); }

        // --- Modal Control ---
        // Helper to manage modal visibility and transitions
        // function toggleModal(modalElement, show) {
        //      if (!modalElement) return;

        //      // Simple show/hide without transitions
        //      // modalElement.classList.toggle('hidden', !show);

        //      // With transitions (using Tailwind classes defined in <style>)
        //      if (show) {
        //          modalElement.classList.remove('hidden', 'modal-leave-to');
        //          modalElement.classList.add('modal-enter-to');
        //      } else {
        //          modalElement.classList.add('modal-leave-to');
        //          modalElement.classList.remove('modal-enter-to');
        //          // Wait for animation to finish before hiding completely
        //          modalElement.addEventListener('transitionend', () => {
        //              modalElement.classList.add('hidden');
        //          }, { once: true }); // Remove listener after it runs once
        //      }
        // }
        function toggleModal(modalElement, show) {
     if (!modalElement) return;
     // Simple show/hide without transitions
     modalElement.classList.toggle('hidden', !show); // Use toggle with force parameter
}

        // Open Verify Modal
        forgotPasswordLink.addEventListener('click', (e) => {
            e.preventDefault();
            toggleModal(forgotPasswordVerifyModal, true);
            toggleModal(resetPasswordModal, false); // Ensure reset modal is hidden
        });

        // Close Verify Modal
        closeVerifyModalBtn.addEventListener('click', () => toggleModal(forgotPasswordVerifyModal, false));
        forgotPasswordVerifyModal.addEventListener('click', (e) => {
            // Close only if clicking on the backdrop itself
            if (e.target === forgotPasswordVerifyModal) toggleModal(forgotPasswordVerifyModal, false);
        });

        // Close Reset Modal
        closeResetModalBtn.addEventListener('click', () => toggleModal(resetPasswordModal, false));
        resetPasswordModal.addEventListener('click', (e) => {
            // Close only if clicking on the backdrop itself
            if (e.target === resetPasswordModal) toggleModal(resetPasswordModal, false);
        });


        // --- Retain Form View & Modal State on Page Load ---
        // Read flags set by PHP after POST request
        const lastSubmittedForm = "<?php echo $last_form; ?>";
        const shouldShowForgotModal = <?php echo $show_forgot_modal_on_load ? 'true' : 'false'; ?>;
        const shouldShowResetModal = <?php echo $show_reset_modal_on_load ? 'true' : 'false'; ?>;
        const hasRegisterError = <?php echo !empty($register_error_message) ? 'true' : 'false'; ?>;
        // const hasLoginError = <?php echo !empty($login_error_message) ? 'true' : 'false'; ?>; // Not explicitly needed for view logic below
        const wasRegisterSuccess = <?php echo !empty($register_success_message) ? 'true' : 'false'; ?>;

        // Initialize modal visibility based on PHP flags
        if (shouldShowResetModal) {
             toggleModal(resetPasswordModal, true);
             toggleModal(forgotPasswordVerifyModal, false); // Ensure verify is hidden
        } else if (shouldShowForgotModal) {
             toggleModal(forgotPasswordVerifyModal, true);
             toggleModal(resetPasswordModal, false); // Ensure reset is hidden
        }
        // Else, both modals start hidden (default)

        // Set initial Sign In/Sign Up view
        // Show Sign Up only if registration was attempted and failed
        if (lastSubmittedForm === 'register' && hasRegisterError) {
            showSignUp();
        } else {
             // Default to Sign In view on initial load, after login attempt,
             // after successful registration, or when a modal is open.
             showSignIn();
        }

    </script>
</body>
</html>