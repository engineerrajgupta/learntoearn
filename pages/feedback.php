<?php
 // --- Authentication and Session Management ---
 session_start(); // Must be the very first thing

 // Check if the user is logged in, otherwise redirect to login page
 if (!isset($_SESSION['user_id'])) {
     header("Location: ../index.php"); // Adjust path if necessary
     exit; // Stop script execution
 }

 // Get user information from session
 $user_id = $_SESSION['user_id'];
 $username = isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'User';
 $user_email = isset($_SESSION['email']) ? htmlspecialchars($_SESSION['email']) : ''; // Assuming email is in session

 // --- PHPMailer Integration ---
 use PHPMailer\PHPMailer\PHPMailer;
 use PHPMailer\PHPMailer\Exception;

 // ** ADJUST PATH IF NEEDED **
 require '../../vendor/autoload.php';

 $formMessage = ''; // To store success/error message
 $formStatus = ''; // 'success' or 'error'

 // --- Form Processing ---
 if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Sanitize and retrieve form data
    $feedback_name = $username;
    $feedback_email = filter_var($_POST['email'] ?? $user_email, FILTER_SANITIZE_EMAIL, FILTER_NULL_ON_FAILURE);
    $feedback_subject = isset($_POST['subject']) ? htmlspecialchars(trim($_POST['subject'])) : '';
    $feedback_message = isset($_POST['message']) ? htmlspecialchars(trim($_POST['message'])) : '';

    // Basic Validation
    if (empty($feedback_subject) || empty($feedback_message)) {
        $formMessage = 'Please fill in both Subject and Message fields.';
        $formStatus = 'error';
    } elseif (empty($feedback_email) || !filter_var($feedback_email, FILTER_VALIDATE_EMAIL)) {
         $formMessage = 'Please provide a valid email address.';
         $formStatus = 'error';
    } else {
        // Proceed to send email
        $mail = new PHPMailer(true);

        try {
            // ** SMTP Configuration - REPLACE WITH YOUR CREDENTIALS **
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'rajgupta8340@gmail.com'; // Your SMTP username
            $mail->Password   = 'app password required';    // Your SMTP app password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            // Sender & Recipient
            $mail->setFrom('rajgupta8340@gmail.com', 'LearnToEarn Feedback'); // Your sending email
            $mail->addReplyTo($feedback_email, $feedback_name);
            $mail->addAddress('engineerrajgupta@gmail.com', 'Admin Raj Gupta'); // Your receiving email

            // Content
            $mail->isHTML(true);
            $mail->Subject = 'Feedback Received: ' . $feedback_subject;
            $mail->Body    = "<h1>New Feedback Received from LearnToEarn</h1>" .
                             "<p><strong>From User:</strong> " . htmlspecialchars($feedback_name) . "</p>" .
                             "<p><strong>User Email:</strong> " . htmlspecialchars($feedback_email) . "</p>" .
                             "<p><strong>Subject:</strong> " . htmlspecialchars($feedback_subject) . "</p>" .
                             "<hr>" .
                             "<h2>Message:</h2>" .
                             "<div>" . nl2br(htmlspecialchars($feedback_message)) . "</div>";
            $mail->AltBody = "New Feedback Received from LearnToEarn\n\n" .
                             "From User: " . $feedback_name . "\n" .
                             "User Email: " . $feedback_email . "\n" .
                             "Subject: " . $feedback_subject . "\n" .
                             "---------------------------\n\n" .
                             "Message:\n" . $feedback_message;

            // Send Email
            $mail->send();
            $formMessage = 'Thank you for your feedback! It has been sent successfully.';
            $formStatus = 'success';

        } catch (Exception $e) {
            error_log("Mailer Error [Feedback Form]: {$mail->ErrorInfo}");
            $formMessage = "Sorry, your message could not be sent due to a technical issue. Please try again later.";
            $formStatus = 'error';
        }
    }
 }
?>
<!DOCTYPE html>
<html lang="en"> 
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Feedback - LearnToEarn</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <script>
      // Configure Tailwind CSS for dark mode
      tailwind.config = {
        darkMode: 'class',
      }

      // --- Theme Detection & Initial Application ---
      // Apply theme class ASAP based on localStorage or system preference
      ;(function() {
        const theme = localStorage.getItem('theme') || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
        if (theme === 'dark') {
          document.documentElement.classList.add('dark');
        } else {
          document.documentElement.classList.remove('dark');
        }
      })();
    </script>
    <style>
      /* CSS to control icon visibility based on <html> class */
      #theme-toggle .fa-moon,
      #theme-toggle .fa-sun { display: none; width: 1.25em; text-align: center; } /* Hide both by default */

      html:not(.dark) #theme-toggle .fa-moon { display: inline-block; } /* Show moon in light mode */
      html.dark #theme-toggle .fa-sun { display: inline-block; } /* Show sun in dark mode */
    </style>
</head>
<body class="bg-gray-100 dark:bg-gray-900 min-h-screen font-sans text-gray-900 dark:text-gray-100 transition-colors duration-200">
    <div class="flex flex-col h-screen">
        <!-- Header -->
        <header class="bg-white dark:bg-gray-800 shadow-md sticky top-0 z-50 transition-colors duration-200">
            <div class="container mx-auto px-4 py-3 flex items-center justify-between">
                <div class="flex items-center">
                    <img src="/learntoearn/assets/images/learntoearn.png" alt="LearnToEarn Logo" class="h-12 w-12 md:h-16 md:w-16 mr-3">
                    <h1 class="text-xl md:text-2xl font-bold text-indigo-600 dark:text-indigo-400">LearnToEarn</h1>
                </div>
                <div class="hidden md:flex items-center space-x-4">
                    <!-- Desktop Navigation -->
                    <nav>
                         <ul class="flex space-x-6 text-gray-600 dark:text-gray-300">
                            <li><a href="dashboard.php" class="hover:text-indigo-600 dark:hover:text-indigo-400">Dashboard</a></li>
                            <li><a href="learntoearnai.php" class="hover:text-indigo-600 dark:hover:text-indigo-400">LearnToEarnAi</a></li>
                            <li><a href="messages.php" class="hover:text-indigo-600 dark:hover:text-indigo-400">Messages</a></li>
                            <li><a href="feedback.php" class="text-indigo-600 dark:text-indigo-400 font-semibold border-b-2 border-indigo-500">Feedback</a></li>
                         </ul>
                    </nav>
                    <!-- Theme Toggle & User Menu -->
                    <div class="flex items-center space-x-4">
                        <button id="theme-toggle" title="Toggle theme" class="p-2 rounded-full text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800 transition-colors duration-200" >
                            <i class="fas fa-moon text-lg"></i>
                            <i class="fas fa-sun text-lg text-yellow-400"></i>
                            <span class="sr-only">Toggle theme</span>
                        </button>
                        <div class="relative">
                            <button id="user-menu" class="flex items-center space-x-2 focus:outline-none" aria-expanded="false" aria-haspopup="true">
                                <img src="/learntoearn/assets/images/user.jpg" alt="User Avatar" class="w-8 h-8 rounded-full border-2 border-gray-300 dark:border-gray-600">
                                <span class="text-gray-700 dark:text-gray-300 hidden lg:block font-medium"><?php echo $username ?></span>
                                <i class="fas fa-chevron-down text-gray-500 dark:text-gray-400 text-xs"></i>
                            </button>
                            <div id="user-dropdown" class="absolute right-0 mt-2 w-48 bg-white dark:bg-gray-800 rounded-md shadow-lg py-1 hidden z-50 ring-1 ring-black ring-opacity-5 transition-colors duration-200">
                                <a href="profile.php" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">Profile</a>
                                <a href="/learntoearn/pages/logout.php" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">Logout</a>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Mobile Menu Button -->
                <button id="mobile-menu-button" class="md:hidden focus:outline-none p-2 rounded-md text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700" aria-controls="mobile-menu" aria-expanded="false">
                    <i class="fas fa-bars text-xl"></i>
                    <span class="sr-only">Open main menu</span>
                </button>
            </div>
            <!-- Mobile Menu Panel -->
            <div id="mobile-menu" class="hidden md:hidden bg-white dark:bg-gray-800 shadow-md border-t border-gray-200 dark:border-gray-700 transition-colors duration-200">
                 <nav class="px-2 pt-2 pb-3 space-y-1 sm:px-3">
                     <a href="dashboard.php" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">Dashboard</a>
                     <a href="learntoearnai.php" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">LearnToEarn Ai</a>
                     <a href="messages.php" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">Messages</a>
                     <a href="feedback.php" class="block px-3 py-2 rounded-md text-base font-medium text-indigo-600 dark:text-indigo-400 bg-indigo-50 dark:bg-gray-900">Feedback</a>
                     <a href="profile.php" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">Profile</a>
                     <a href="/learntoearn/pages/logout.php" class="block px-3 py-2 rounded-md text-base font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">Logout</a>
                 </nav>
            </div>
        </header>

        <!-- Main Content -->
        <main class="flex-grow overflow-y-auto">
            <div class="container mx-auto px-4 py-6 md:py-8">
                <!-- Feedback Form Section -->
                <section class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-6 md:p-8 max-w-3xl mx-auto transition-colors duration-200">
                    <h2 class="text-2xl md:text-3xl font-semibold text-gray-800 dark:text-gray-200 mb-4 text-center">Submit Your Feedback</h2>
                    <p class="text-gray-600 dark:text-gray-400 mb-6 text-center">We value your opinion! Help us improve LearnToEarn.</p>

                    <!-- Form Status Message -->
                    <?php if ($formMessage): ?>
                        <div class="mb-6 p-4 rounded-md text-sm <?php echo ($formStatus === 'success') ? 'bg-green-100 dark:bg-green-800 text-green-800 dark:text-green-100 border border-green-200 dark:border-green-600' : 'bg-red-100 dark:bg-red-800 text-red-800 dark:text-red-100 border border-red-200 dark:border-red-600'; ?>" role="alert">
                            <i class="fas <?php echo ($formStatus === 'success') ? 'fa-check-circle' : 'fa-exclamation-triangle'; ?> mr-2"></i>
                            <?php echo $formMessage; ?>
                        </div>
                    <?php endif; ?>

                    <!-- Feedback Form -->
                    <form action="feedback.php" method="POST" class="space-y-6">
                        <!-- Name (Readonly) -->
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Your Name</label>
                            <input type="text" id="name" name="name" value="<?php echo $username; ?>" readonly
                                   class="w-full px-3 py-2 bg-gray-100 dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-md text-gray-500 dark:text-gray-400 focus:outline-none cursor-not-allowed">
                        </div>
                        <!-- Email -->
                        <div>
                             <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Your Email <span class="text-red-500">*</span></label>
                             <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user_email); ?>" required placeholder="you@example.com"
                                    class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500 transition-colors duration-200">
                        </div>
                        <!-- Subject -->
                        <div>
                            <label for="subject" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Subject <span class="text-red-500">*</span></label>
                            <input type="text" id="subject" name="subject" required placeholder="e.g., Suggestion for Dashboard"
                                   class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500 transition-colors duration-200">
                        </div>
                        <!-- Message -->
                        <div>
                            <label for="message" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Message <span class="text-red-500">*</span></label>
                            <textarea id="message" name="message" rows="6" required placeholder="Please provide details here..."
                                      class="w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 placeholder-gray-400 dark:placeholder-gray-500 transition-colors duration-200"></textarea>
                        </div>
                        <!-- Submit Button -->
                        <div class="pt-2">
                            <button type="submit"
                                    class="w-full inline-flex justify-center items-center py-2.5 px-4 border border-transparent shadow-sm text-base font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-900 transition duration-150 ease-in-out">
                                <i class="fas fa-paper-plane mr-2"></i> Send Feedback
                            </button>
                        </div>
                    </form>
                </section>
            </div>
        </main>

        <!-- Footer -->
        <footer class="bg-white dark:bg-gray-800 shadow-inner mt-auto border-t border-gray-200 dark:border-gray-700 transition-colors duration-200">
            <div class="container mx-auto px-4 py-4">
                <div class="flex flex-col md:flex-row justify-between items-center text-center md:text-left">
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-2 md:mb-0">
                        © <?php echo date("Y"); ?> LearnToEarn. All rights reserved.
                    </p>
                    <div class="flex justify-center space-x-4">
                        <a href="about.php" class="text-sm text-gray-500 dark:text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400">About</a>
                        <a href="privacy.php" class="text-sm text-gray-500 dark:text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400">Privacy</a>
                        <a href="terms.php" class="text-sm text-gray-500 dark:text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400">Terms</a>
                        <a href="contact.html" class="text-sm text-gray-500 dark:text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400">Contact</a>
                    </div>
                </div>
            </div>
        </footer>
    </div>

    <!-- JavaScript -->
    <script>
        // Wrap everything in DOMContentLoaded to ensure elements exist
        document.addEventListener('DOMContentLoaded', () => {

            const themeToggle = document.getElementById('theme-toggle');
            const htmlElement = document.documentElement;
            const mobileMenuButton = document.getElementById('mobile-menu-button');
            const mobileMenu = document.getElementById('mobile-menu');
            const userMenu = document.getElementById('user-menu');
            const userDropdown = document.getElementById('user-dropdown');

            // --- Mobile menu toggle ---
            if (mobileMenuButton && mobileMenu) {
                mobileMenuButton.addEventListener('click', () => {
                    const isExpanded = mobileMenuButton.getAttribute('aria-expanded') === 'true';
                    mobileMenuButton.setAttribute('aria-expanded', !isExpanded);
                    mobileMenu.classList.toggle('hidden');
                });
            }

            // --- User dropdown toggle ---
            if (userMenu && userDropdown) {
                userMenu.addEventListener('click', (event) => {
                    event.stopPropagation();
                    const isHidden = userDropdown.classList.contains('hidden');
                    userDropdown.classList.toggle('hidden', !isHidden);
                    userMenu.setAttribute('aria-expanded', !isHidden);
                });
                // Close dropdown when clicking outside or pressing Escape
                document.addEventListener('click', (event) => {
                    if (!userDropdown.classList.contains('hidden') && !userMenu.contains(event.target) && !userDropdown.contains(event.target)) {
                        userDropdown.classList.add('hidden');
                        userMenu.setAttribute('aria-expanded', 'false');
                    }
                });
                document.addEventListener('keydown', (event) => {
                    if (event.key === 'Escape' && !userDropdown.classList.contains('hidden')) {
                        userDropdown.classList.add('hidden');
                        userMenu.setAttribute('aria-expanded', 'false');
                    }
                });
            }

            // --- Theme toggle logic ---
            if (themeToggle) {
                themeToggle.addEventListener('click', () => {
                    // 1. Check current state *before* changing
                    const isDark = htmlElement.classList.contains('dark');

                    // 2. Determine the new theme
                    const newTheme = isDark ? 'light' : 'dark';

                    // 3. Toggle the class on the <html> element
                    htmlElement.classList.toggle('dark'); // Simple toggle based on current state

                    // 4. Update localStorage
                    localStorage.setItem('theme', newTheme);

                    // 5. Icon visibility is handled by the CSS rules in the <head>
                });
            } else {
                 console.error("Theme toggle button (#theme-toggle) not found!");
            }

        }); // End DOMContentLoaded
    </script>

</body>
</html>
