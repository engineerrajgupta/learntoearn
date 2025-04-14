<?php
 // --- Authentication and Session Management ---
 session_start(); // Must be the very first thing

 // Check if the user is logged in
 if (!isset($_SESSION['user_id'])) {
     header("Location: ../index.php"); // Redirect to login page
     exit; // Stop script execution
 }

 // Check if the user has the 'teacher' role
 if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'teacher') {
     // Redirect non-teachers (e.g., to a general dashboard or login)
     header("Location: dashboard.php"); // Assuming dashboard.php is for students or generic
     exit; // Stop script execution
 }

 // Get user information from session
 $user_id = $_SESSION['user_id']; // This is the teacher's ID
 $username = isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'Teacher'; // Use htmlspecialchars for security
 $role = $_SESSION['role']; // Role is confirmed to be 'teacher'

 // --- Database Connection ---
 // The path must be correct relative to this file's location
 // Example: require_once __DIR__ . '/../config/db_connect.php';
 require_once '../includes/db.php'; // --- IMPORTANT: Update this path ---

 // --- Fetch Subjects Taught by THIS Teacher ---
 // This data populates the syllabus management dropdown
 $teacher_subjects = []; // Initialize array
 $subject_fetch_error = null; // Initialize error message variable

 // Proceed only if the database connection object ($pdo) is available
 if (isset($pdo) && $pdo) {
    try {
        // Query to get subjects linked to this teacher via the syllabi table
        // Using DISTINCT to avoid duplicates if a teacher could somehow be linked multiple times
        $stmt = $pdo->prepare("
            SELECT DISTINCT s.subject_id, s.name
            FROM subjects s
            JOIN syllabi sy ON s.subject_id = sy.subject_id
            WHERE sy.teacher_id = ?
            ORDER BY s.name ASC
        ");
        $stmt->execute([$user_id]);
        $teacher_subjects = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Optional: You could add a check here if $teacher_subjects is empty
        // and query a different table (like teacher_assignments) if your
        // application structure requires it.

    } catch (PDOException $e) {
        // Log the detailed database error for server admin/debugging
        error_log("Error fetching teacher subjects for teacher_id {$user_id}: " . $e->getMessage());
        // Set a user-friendly error message
        $subject_fetch_error = "Could not load the list of subjects due to a database issue.";
    }
 } else {
     // Database connection object $pdo was not found or invalid
     $subject_fetch_error = "Database connection is not configured correctly. Cannot load subjects.";
     // Log this critical configuration error
     error_log("PDO connection object not available in teacher_dashboard.php for teacher_id {$user_id}");
 }
 // --- End Data Fetching ---

?>
<!DOCTYPE html>
<html lang="en" class="dark"> <!-- Sets default mode, JS will override based on localStorage -->
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Dashboard - LearnToEarn</title>

    <!-- External Stylesheets -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <!-- Tailwind CSS from CDN -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- External JavaScript Libraries -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.7.0/chart.min.js"></script>

    <!-- Tailwind CSS Configuration -->
    <script>
      tailwind.config = {
        darkMode: 'class', // Enable class-based dark mode (triggered by JS)
      }
    </script>

    <!-- Internal Styles (Chatbot and Syllabus Specific) -->
    <style>
        /* --- Chatbot Styles --- */
        #chat-trigger {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background-color: #3b82f6; /* Tailwind indigo-500 */
            color: white;
            padding: 10px 15px;
            border-radius: 5px;
            cursor: pointer;
            z-index: 1000;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
            display: inline-flex; /* Align icon and text */
            align-items: center;
        }
        #chat-popup-container {
            display: none; /* Initially hidden */
            position: fixed;
            bottom: 80px; /* Position above the trigger */
            right: 20px;
            border: 1px solid #e5e7eb; /* Tailwind gray-200 */
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            z-index: 1000;
            width: 90%; /* Responsive width */
            max-width: 350px;
            background-color: #ffffff; /* Tailwind white */
            flex-direction: column; /* Ensure vertical layout */
            height: 500px; /* Fixed height */
            max-height: 70vh; /* Max height relative to viewport */
        }
        .dark #chat-popup-container {
             background-color: #1f2937; /* Tailwind gray-800 */
             border: 1px solid #4b5563; /* Tailwind gray-600 */
        }
        .chat-header {
            background-color: #3b82f6; /* Tailwind indigo-500 */
            color: white;
            padding: 12px 16px;
            text-align: center;
            font-weight: bold;
            font-size: 1rem;
            flex-shrink: 0; /* Prevent header from shrinking */
        }
        .chat-body {
            padding: 16px;
            flex-grow: 1; /* Allow body to take remaining space */
            overflow-y: auto; /* Enable scrolling */
            display: flex;
            flex-direction: column;
            background-color: #f9fafb; /* Tailwind gray-50 */
        }
         .dark .chat-body {
             background-color: #374151; /* Tailwind gray-700 */
         }
        .message {
            padding: 8px 12px;
            margin-bottom: 8px;
            border-radius: 8px;
            clear: both;
            max-width: 85%; /* Slightly wider messages */
            word-wrap: break-word;
            line-height: 1.4;
        }
        .user-message {
            background-color: #dbeafe; /* Tailwind blue-100 */
            color: #1e40af; /* Tailwind blue-800 */
            align-self: flex-end;
            margin-left: 15%;
        }
         .dark .user-message {
             background-color: #3730a3; /* Tailwind indigo-800 */
             color: #e0e7ff; /* Tailwind indigo-100 */
         }
        .bot-message {
            background-color: #e5e7eb; /* Tailwind gray-200 */
            color: #1f2937; /* Tailwind gray-800 */
            align-self: flex-start;
            margin-right: 15%;
        }
         .dark .bot-message {
             background-color: #4b5563; /* Tailwind gray-600 */
             color: #f3f4f6; /* Tailwind gray-100 */
         }
        .bot-message pre { /* Code block styling */
             background-color: #f3f4f6;
             padding: 8px;
             border-radius: 4px;
             overflow-x: auto;
             margin-top: 5px;
             font-family: monospace;
             font-size: 0.85em;
             border: 1px solid #d1d5db; /* Light border for code */
        }
        .dark .bot-message pre {
             background-color: #374151;
             color: #d1d5db;
             border: 1px solid #4b5563;
        }
        .bot-message ul { list-style: disc; margin-left: 20px; padding-left: 5px; }
        .bot-message li { margin-bottom: 4px; }

        .chat-input-container {
            display: flex;
            padding: 8px;
            border-top: 1px solid #e5e7eb; /* Tailwind gray-200 */
            background-color: #ffffff; /* Tailwind white */
            flex-shrink: 0; /* Prevent input area from shrinking */
        }
         .dark .chat-input-container {
             border-top: 1px solid #4b5563; /* Tailwind gray-600 */
             background-color: #1f2937; /* Tailwind gray-800 */
         }
        .chat-input {
            flex-grow: 1;
            padding: 10px;
            border: 1px solid #d1d5db; /* Tailwind gray-300 */
            border-radius: 4px;
            font-size: 0.9rem;
            background-color: #ffffff;
            color: #111827;
        }
        .dark .chat-input {
             border: 1px solid #4b5563; /* Tailwind gray-600 */
             background-color: #374151; /* Tailwind gray-700 */
             color: #f9fafb; /* Tailwind gray-50 */
        }
         .chat-input:focus {
             outline: none;
             border-color: #3b82f6; /* Tailwind indigo-500 */
             box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.3);
         }
        .send-button {
            padding: 10px 16px;
            background-color: #3b82f6; /* Tailwind indigo-500 */
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-left: 8px;
            font-size: 0.9rem;
            transition: background-color 0.2s ease;
            display: inline-flex; /* Align icon */
            align-items: center;
            justify-content: center;
        }
        .send-button:hover { background-color: #2563eb; /* Tailwind indigo-600 */ }
        .send-button:disabled { background-color: #9ca3af; cursor: not-allowed; } /* Disabled state */

        /* --- Syllabus Management Styles --- */
        .topic-item { transition: background-color 0.3s ease; }
        /* Style for completed topics */
        .topic-item.completed { background-color: #f0fdfa; /* Tailwind cyan-50 */ }
        .dark .topic-item.completed { background-color: rgba(13, 148, 136, 0.2); /* Dark teal-700 transparent */ }
        /* Style for topic name when completed */
        .topic-item .topic-name.completed { text-decoration: line-through; color: #6b7280; /* gray-500 */ }
        .dark .topic-item .topic-name.completed { color: #9ca3af; /* gray-400 */ }

        /* Loading spinner animation */
        .loading-spinner {
             border: 4px solid rgba(0, 0, 0, 0.1);
             border-left-color: #4f46e5; /* indigo-600 */
             border-radius: 50%;
             width: 24px;
             height: 24px;
             animation: spin 1s linear infinite;
             margin: 1rem auto; /* Center it */
        }
        @keyframes spin { to { transform: rotate(360deg); } }

        /* Fade-in animation for new topics */
        @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        .animate-fade-in { animation: fadeIn 0.5s ease-out forwards; }

        /* Ensure chart canvas is constrained */
        #progressChart { max-height: 300px; /* Or adjust as needed */ }

    </style>
</head>
<body class="bg-gray-100 dark:bg-gray-900 min-h-screen">
    <div class="flex flex-col h-screen">
        <!-- ================== Header ================== -->
        <header class="bg-white dark:bg-gray-800 shadow-md sticky top-0 z-50"> <!-- Made header sticky -->
            <div class="container mx-auto px-4 py-3 flex items-center justify-between">
                <!-- Logo and Title -->
                <div class="flex items-center">
                    <!-- Make sure image path is correct relative to web root -->
                    <img src="/learntoearn/assets/images/learntoearn.png" alt="LearnToEarn Logo" class="h-16 w-16 md:h-20 md:w-20 mr-3">
                    <h1 class="text-xl md:text-2xl font-bold text-indigo-600 dark:text-indigo-400">LearnToEarn</h1>
                    <span class="ml-2 text-xs md:text-sm text-gray-500 dark:text-gray-400 hidden sm:inline">(Teacher Portal)</span>
                </div>

                <!-- Desktop Navigation & User Menu -->
                <div class="hidden md:flex items-center space-x-4">
                    <!-- Main Navigation -->
                    <nav>
                        <ul class="flex space-x-6 text-gray-600 dark:text-gray-300">
                            <li><a href="subjects.php" class="hover:text-indigo-600 dark:hover:text-indigo-400">Subjects</a></li>
                            <li><a href="messages.php" class="hover:text-indigo-600 dark:hover:text-indigo-400">Messages</a></li>
                            <li><a href="learntoearnai.php" class="hover:text-indigo-600 dark:hover:text-indigo-400">LearnToEarnAi</a></li>
                        </ul>
                    </nav>
                    <!-- Theme Toggle & User Actions -->
                    <div class="flex items-center space-x-4">
                        <button id="theme-toggle" title="Toggle theme" class="p-2 rounded-full bg-gray-200 dark:bg-gray-700" >
                            <i class="fas fa-moon text-gray-600 dark:hidden"></i> <!-- Moon icon for light mode -->
                            <i class="fas fa-sun text-yellow-400 hidden dark:block"></i> <!-- Sun icon for dark mode -->
                        </button>
                        <div class="relative">
                            <button id="user-menu" class="flex items-center space-x-2 focus:outline-none">
                                <!-- Make sure image path is correct relative to web root -->
                                <img src="/learntoearn/assets/images/user.jpg" alt="User Avatar" class="w-8 h-8 rounded-full border-2 border-gray-300 dark:border-gray-600">
                                <span class="text-gray-700 dark:text-gray-300 hidden lg:block"><?php echo $username; // Already escaped in PHP ?></span>
                                <i class="fas fa-chevron-down text-gray-500 text-xs"></i>
                            </button>
                            <!-- Dropdown Menu -->
                            <div id="user-dropdown" class="absolute right-0 mt-2 w-48 bg-white dark:bg-gray-800 rounded-md shadow-xl py-1 hidden z-10 border border-gray-200 dark:border-gray-700">
                                <a href="profile.php" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">Profile</a>
                                <!-- <a href="settings.php" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">Settings</a> -->
                                <a href="/learntoearn/pages/logout.php" class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">Logout</a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Mobile Menu Button -->
                <button id="mobile-menu-button" aria-label="Open menu" class="md:hidden focus:outline-none p-2">
                    <i class="fas fa-bars text-gray-600 dark:text-gray-300 text-xl"></i>
                </button>
            </div>

            <!-- Mobile Menu Panel -->
            <div id="mobile-menu" class="hidden md:hidden bg-white dark:bg-gray-800 shadow-lg border-t border-gray-200 dark:border-gray-700">
                <nav class="container mx-auto px-4 py-3">
                    <ul class="space-y-2 text-gray-600 dark:text-gray-300">
                        <li><a href="subjects.php" class="block py-2 hover:text-indigo-600 dark:hover:text-indigo-400">Subjects</a></li>
                        <li><a href="messages.php" class="block py-2 hover:text-indigo-600 dark:hover:text-indigo-400">Messages</a></li>
                        <li><a href="learntoearnai.php" class="block py-2 hover:text-indigo-600 dark:hover:text-indigo-400">LearnToEarn Ai</a></li>
                        <li><hr class="my-2 border-gray-200 dark:border-gray-700"></li>
                        <li><a href="profile.php" class="block py-2 hover:text-indigo-600 dark:hover:text-indigo-400">Profile</a></li>
                        <li><a href="/learntoearn/pages/logout.php" class="block py-2 hover:text-indigo-600 dark:hover:text-indigo-400">Logout</a></li>
                    </ul>
                </nav>
            </div>
        </header>
        <!-- ================== End Header ================== -->

        <!-- ================== Main Content Area ================== -->
        <main class="flex-grow overflow-y-auto pt-6"> <!-- Added padding top -->
            <div class="container mx-auto px-4">
                <div class="flex flex-col md:flex-row gap-6">

                    <!-- ================== Sidebar ================== -->
                    <aside class="w-full md:w-64 bg-white dark:bg-gray-800 rounded-lg shadow-md p-4 hidden md:block h-fit"> <!-- h-fit makes it height of content -->
                        <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200 mb-4">Quick Access</h2>
                        <nav>
                            <ul class="space-y-2">
                                <!-- Link to Syllabus Section -->
                                <li>
                                    <a href="#syllabus-section" class="flex items-center space-x-2 p-2 rounded-md hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 transition-colors duration-150">
                                        <i class="fas fa-book-open w-5 text-center text-indigo-500"></i>
                                        <span>Syllabus Management</span>
                                    </a>
                                </li>
                                <!-- Link to Progress Section -->
                                <li>
                                    <a href="#progress-section" class="flex items-center space-x-2 p-2 rounded-md hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 transition-colors duration-150">
                                        <i class="fas fa-chart-bar w-5 text-center text-green-500"></i> <!-- Changed icon -->
                                        <span>Progress Overview</span>
                                    </a>
                                </li>
                                <!-- Book Reading Link -->
                                <li>
                                    <!-- Ensure this path is correct for your setup -->
                                    <a href="/learntoearn/pages/pdfreader/index.html" target="_blank" class="flex items-center space-x-2 p-2 rounded-md hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 transition-colors duration-150">
                                        <i class="fas fa-book-reader w-5 text-center text-blue-500"></i>
                                        <span>Book Reading</span>
                                    </a>
                                </li>
                                <!-- Other Original Links -->
                                <li>
                                    <a href="revision-tool.php" class="flex items-center space-x-2 p-2 rounded-md hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 transition-colors duration-150">
                                        <i class="fas fa-brain w-5 text-center text-purple-500"></i>
                                        <span>Revision Tool</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="question-bank.php" class="flex items-center space-x-2 p-2 rounded-md hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 transition-colors duration-150">
                                        <i class="fas fa-database w-5 text-center text-yellow-500"></i>
                                        <span>Question Bank</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="ai-experts.php" class="flex items-center space-x-2 p-2 rounded-md hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300 transition-colors duration-150">
                                        <i class="fas fa-robot w-5 text-center text-pink-500"></i>
                                        <span>AI Subject Experts</span>
                                    </a>
                                </li>
                            </ul>
                        </nav>
                    </aside>
                    <!-- ================== End Sidebar ================== -->

                    <!-- ================== Main Content Column ================== -->
                    <div class="flex-grow">

                        <!-- Welcome Section -->
                        <section class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 mb-6">
                            <div class="flex items-center justify-between mb-4 flex-wrap gap-y-2">
                                <h2 class="text-2xl font-semibold text-gray-800 dark:text-gray-200 mr-4">Welcome, <?php echo $username; // Already escaped ?>!</h2>
                                <!-- Example static last login, replace if dynamic needed -->
                                <!-- <span class="text-sm text-gray-500 dark:text-gray-400">Last login: [Date Time]</span> -->
                            </div>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">Here's a summary of your teaching activities and tools.</p>
                            <!-- Status Cards -->
                            <div class="flex flex-wrap gap-4">
                                <!-- Card 1: Course Completion -->
                                <div class="flex items-center bg-indigo-100 dark:bg-indigo-900/50 p-3 rounded-lg flex-grow sm:flex-grow-0 shadow-sm">
                                    <i class="fas fa-tasks text-indigo-600 dark:text-indigo-400 text-xl mr-3"></i> <!-- Changed icon -->
                                    <div>
                                        <h3 class="font-medium text-gray-800 dark:text-gray-200 text-sm">Subject Progress</h3>
                                        <p class="text-gray-600 dark:text-gray-400 text-xs">View overview below</p>
                                    </div>
                                </div>
                                <!-- Card 2: Messages -->
                                <div class="flex items-center bg-green-100 dark:bg-green-900/50 p-3 rounded-lg flex-grow sm:flex-grow-0 shadow-sm">
                                    <i class="fas fa-envelope-open-text text-green-600 dark:text-green-400 text-xl mr-3"></i> <!-- Changed icon -->
                                    <div>
                                        <h3 class="font-medium text-gray-800 dark:text-gray-200 text-sm">Messages</h3>
                                        <!-- Replace with dynamic count if available -->
                                        <p class="text-gray-600 dark:text-gray-400 text-xs">5 unread</p>
                                    </div>
                                </div>
                                <!-- Card 3: Schedule -->
                                <div class="flex items-center bg-yellow-100 dark:bg-yellow-900/50 p-3 rounded-lg flex-grow sm:flex-grow-0 shadow-sm">
                                    <i class="fas fa-calendar-check text-yellow-600 dark:text-yellow-400 text-xl mr-3"></i> <!-- Changed icon -->
                                    <div>
                                        <h3 class="font-medium text-gray-800 dark:text-gray-200 text-sm">Upcoming</h3>
                                         <!-- Replace with dynamic count if available -->
                                        <p class="text-gray-600 dark:text-gray-400 text-xs">3 sessions this week</p>
                                    </div>
                                </div>
                            </div>
                        </section>

                        <!-- Progress Overview Section (with Dynamic Chart) -->
                        <section id="progress-section" class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 mb-6 scroll-mt-20"> <!-- scroll-mt for # link offset -->
                            <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200 mb-4">Syllabus Progress Overview</h2>
                            <div class="flex flex-col xl:flex-row gap-6 items-start"> <!-- Changed to items-start -->
                                <!-- Chart Area -->
                                <div class="w-full xl:w-1/2 relative min-h-[250px]"> <!-- Added min-height -->
                                    <canvas id="progressChart"></canvas>
                                    <!-- Status messages for the chart -->
                                    <p id="chart-loading-msg" class="absolute inset-0 flex items-center justify-center text-gray-500 dark:text-gray-400 hidden"><span class="loading-spinner mr-2"></span>Loading chart data...</p>
                                    <p id="chart-error-msg" class="absolute inset-0 flex items-center justify-center text-red-500 dark:text-red-400 hidden p-4 text-center"></p>
                                    <p id="chart-no-data-msg" class="absolute inset-0 flex items-center justify-center text-gray-500 dark:text-gray-400 hidden p-4 text-center">No syllabus progress data available to display.</p>
                                </div>
                                <!-- Recently Completed Topics List (Static Example) -->
                                <div class="w-full xl:w-1/2 mt-6 xl:mt-0">
                                    <h3 class="text-lg font-medium text-gray-800 dark:text-gray-200 mb-2">Recently Completed Topics</h3>
                                    <!-- This list is static. Replace with PHP loop if data is available -->
                                    <ul class="space-y-2 text-sm max-h-60 overflow-y-auto pr-2"> <!-- Added max-height and scroll -->
                                        <li class="flex items-center justify-between border-b border-gray-200 dark:border-gray-700 pb-2">
                                            <span class="text-gray-700 dark:text-gray-300">Introduction to Calculus</span>
                                            <span class="text-xs text-gray-500 dark:text-gray-400 flex-shrink-0 ml-2">Mar 15</span> <!-- Abbreviated date -->
                                        </li>
                                        <li class="flex items-center justify-between border-b border-gray-200 dark:border-gray-700 pb-2">
                                            <span class="text-gray-700 dark:text-gray-300">Cell Biology Fundamentals</span>
                                            <span class="text-xs text-gray-500 dark:text-gray-400 flex-shrink-0 ml-2">Mar 12</span>
                                        </li>
                                        <li class="flex items-center justify-between border-b border-gray-200 dark:border-gray-700 pb-2">
                                            <span class="text-gray-700 dark:text-gray-300">Shakespeare's Sonnets Analysis</span>
                                            <span class="text-xs text-gray-500 dark:text-gray-400 flex-shrink-0 ml-2">Mar 10</span>
                                        </li>
                                         <li class="flex items-center justify-between border-b border-gray-200 dark:border-gray-700 pb-2">
                                            <span class="text-gray-700 dark:text-gray-300">Newton's Laws Application</span>
                                            <span class="text-xs text-gray-500 dark:text-gray-400 flex-shrink-0 ml-2">Mar 08</span>
                                        </li>
                                        <!-- Add more static items or implement dynamic fetching later -->
                                    </ul>
                                    <!-- Link to a potential detailed progress page -->
                                    <!-- <a href="progress.php" class="block mt-4 text-indigo-600 dark:text-indigo-400 hover:underline text-sm">View full progress report →</a> -->
                                </div>
                            </div>
                        </section>

                        <!-- Syllabus Management Section -->
                        <section id="syllabus-section" class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 mb-6 scroll-mt-20"> <!-- scroll-mt for # link offset -->
                            <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200 mb-4">Syllabus Management</h2>

                            <!-- Check if there was an error fetching subjects or if the list is empty -->
                            <?php if ($subject_fetch_error): ?>
                                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
                                  <strong class="font-bold">Error:</strong>
                                  <span class="block sm:inline"><?php echo htmlspecialchars($subject_fetch_error); ?></span>
                                </div>
                            <?php elseif (empty($teacher_subjects)): ?>
                                <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 px-4 py-3 rounded relative" role="alert">
                                  <strong class="font-bold">Notice:</strong>
                                  <span class="block sm:inline">You are not currently managing any syllabi. If you teach subjects, a syllabus entry may be created automatically when you select one below (if assigned).</span>
                                  <!-- Add a link to fetch ALL subjects teacher is assigned to? Needs modification -->
                                   <select id="subject-select-all" name="subject_id_all" class="mt-2 block w-full md:w-1/2 pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200">
                                      <option value="">-- Or Select Subject to Create Syllabus --</option>
                                      <!-- Populate this dropdown with ALL subjects the teacher *could* manage -->
                                      <!-- Requires a different PHP query -->
                                  </select>
                                </div>

                            <?php else: ?>
                                <!-- Subject Selection Dropdown -->
                                <div class="mb-4">
                                    <label for="subject-select" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Select Subject to Manage Syllabus:</label>
                                    <select id="subject-select" name="subject_id" class="mt-1 block w-full md:w-1/2 pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200 shadow-sm">
                                        <option value="">-- Select a Subject --</option>
                                        <?php foreach ($teacher_subjects as $subject): ?>
                                            <option value="<?php echo $subject['subject_id']; ?>">
                                                <?php echo htmlspecialchars($subject['name']); // Escape subject name ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <hr class="my-4 dark:border-gray-600">

                                <!-- Area to display syllabus topics (populated by JavaScript) -->
                                <div id="syllabus-topics-container" class="space-y-3 min-h-[150px] relative mb-6">
                                    <!-- Initial message or loading spinner -->
                                    <p class="text-center text-gray-500 dark:text-gray-400" id="topics-placeholder">Select a subject above to view or manage its syllabus topics.</p>
                                    <!-- Loading spinner will be added here by JS -->
                                    <!-- Error message placeholder -->
                                    <p id="syllabus-error-msg" class="text-center text-red-500 dark:text-red-400 hidden p-4"></p>
                                </div>

                                <!-- Add Topic Form (Initially hidden, shown after subject selection) -->
                                <div id="add-topic-section" class="mt-6 border-t pt-4 dark:border-gray-700 hidden">
                                     <h3 class="text-lg font-medium text-gray-800 dark:text-gray-200 mb-3">Add New Topic</h3>
                                     <form id="add-topic-form" class="space-y-3">
                                        <!-- Hidden input to store the current syllabus_id for the form submission -->
                                        <input type="hidden" id="current-syllabus-id" name="syllabus_id" value="">

                                        <div>
                                             <label for="new-topic-name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Topic Name <span class="text-red-500">*</span></label>
                                             <input type="text" id="new-topic-name" name="topic_name" required placeholder="E.g., Chapter 1: Introduction" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200">
                                        </div>
                                        <div>
                                             <label for="new-topic-description" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Description (Optional)</label>
                                             <textarea id="new-topic-description" name="description" rows="2" placeholder="Brief details about the topic" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-gray-700 dark:border-gray-600 dark:text-gray-200"></textarea>
                                        </div>
                                        <!-- Submit Button and Status Message Area -->
                                        <div class="flex items-center space-x-3">
                                            <button type="submit" id="add-topic-btn" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-md inline-flex items-center transition duration-150 ease-in-out shadow-sm disabled:opacity-50 disabled:cursor-not-allowed">
                                                 <i class="fas fa-plus mr-2"></i>Add Topic
                                            </button>
                                             <span id="add-topic-status" class="text-sm"></span> <!-- Status messages appear here -->
                                        </div>
                                     </form>
                                </div>
                            <?php endif; // End check for subjects ?>
                        </section>

                        <!-- Quick Actions Section -->
                        <section class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
                            <!-- Message Students Card -->
                            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 hover:shadow-lg transition-shadow duration-200">
                                <div class="flex items-center mb-4">
                                    <i class="fas fa-comments text-green-500 text-xl mr-3"></i>
                                    <h3 class="text-lg font-medium text-gray-800 dark:text-gray-200">Message Students</h3>
                                </div>
                                <p class="text-gray-600 dark:text-gray-300 mb-4 text-sm">Communicate directly with students via the messaging system.</p>
                                <a href="messages.php" class="block w-full px-4 py-2 text-center bg-green-500 hover:bg-green-600 text-white rounded-md text-sm font-medium transition duration-150 ease-in-out">Open Messages</a>
                            </div>
                            <!-- Add other original quick action cards here if they existed -->
                             <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 hover:shadow-lg transition-shadow duration-200">
                                <div class="flex items-center mb-4">
                                    <i class="fas fa-clipboard-list text-blue-500 text-xl mr-3"></i>
                                    <h3 class="text-lg font-medium text-gray-800 dark:text-gray-200">Manage Assignments</h3>
                                </div>
                                <p class="text-gray-600 dark:text-gray-300 mb-4 text-sm">Create, distribute, and grade student assignments.</p>
                                <a href="assignments.php" class="block w-full px-4 py-2 text-center bg-blue-500 hover:bg-blue-600 text-white rounded-md text-sm font-medium transition duration-150 ease-in-out">Go to Assignments</a>
                            </div>
                             <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 hover:shadow-lg transition-shadow duration-200">
                                <div class="flex items-center mb-4">
                                    <i class="fas fa-user-graduate text-purple-500 text-xl mr-3"></i>
                                    <h3 class="text-lg font-medium text-gray-800 dark:text-gray-200">View Students</h3>
                                </div>
                                <p class="text-gray-600 dark:text-gray-300 mb-4 text-sm">See enrolled students and their overall progress.</p>
                                <a href="students.php" class="block w-full px-4 py-2 text-center bg-purple-500 hover:bg-purple-600 text-white rounded-md text-sm font-medium transition duration-150 ease-in-out">Student List</a>
                            </div>
                        </section>

                    </div>
                    <!-- ================== End Main Content Column ================== -->

                </div> <!-- End Flex container for Sidebar + Main Content -->
            </div> <!-- End Main container -->
        </main>
        <!-- ================== End Main Content Area ================== -->

        <!-- ================== Footer ================== -->
        <footer class="bg-white dark:bg-gray-800 shadow-md mt-auto border-t dark:border-gray-700">
            <div class="container mx-auto px-4 py-4">
                <div class="flex flex-col md:flex-row justify-between items-center">
                    <div class="text-center md:text-left mb-4 md:mb-0">
                        <p class="text-sm text-gray-600 dark:text-gray-300">© <?php echo date("Y"); // Dynamic year ?> LearnToEarn. All rights reserved.</p>
                    </div>
                    <div class="flex space-x-4">
                        <a href="about.php" class="text-sm text-gray-600 dark:text-gray-300 hover:text-indigo-600 dark:hover:text-indigo-400">About</a>
                        <a href="privacy.php" class="text-sm text-gray-600 dark:text-gray-300 hover:text-indigo-600 dark:hover:text-indigo-400">Privacy Policy</a>
                        <a href="terms.php" class="text-sm text-gray-600 dark:text-gray-300 hover:text-indigo-600 dark:hover:text-indigo-400">Terms of Service</a>
                        <a href="contact.php" class="text-sm text-gray-600 dark:text-gray-300 hover:text-indigo-600 dark:hover:text-indigo-400">Contact</a>
                    </div>
                </div>
            </div>
        </footer>
        <!-- ================== End Footer ================== -->

    </div> <!-- End Flex container for Full Page -->

    <!-- ================== Chatbot HTML ================== -->
    <button id="chat-trigger" title="Chat with AI Assistant">
       <i class="fas fa-comment-dots mr-2"></i> Chat with AI
    </button>
    <div id="chat-popup-container">
        <div class="chat-header">LearnToEarn AI Helper</div>
        <div id="chat-body" class="chat-body">
            <!-- Messages will be dynamically added here -->
        </div>
        <div class="chat-input-container">
            <input type="text" id="message-input" class="chat-input" placeholder="Ask a question...">
            <button id="send-button" class="send-button" aria-label="Send message">
                <i class="fas fa-paper-plane"></i>
            </button>
        </div>
    </div>
    <!-- ================== End Chatbot HTML ================== -->


    <!-- ================== JavaScript ================== -->
    <script>
        // Wrap all JS in an IIFE (Immediately Invoked Function Expression)
        // This prevents polluting the global namespace and helps avoid conflicts
        (function() {
            'use strict'; // Enable strict mode for better error detection

            // --- Utility Function ---
            // Escapes HTML to prevent XSS attacks when inserting dynamic text content
            function escapeHtml(unsafe) {
                if (typeof unsafe !== 'string') return ''; // Return empty string for non-strings
                return unsafe
                     .replace(/&/g, "&")
                     .replace(/</g, "<")
                     .replace(/>/g, ">")
                     .replace(/"/g, """)
                     .replace(/'/g, "'");
            }

            // --- DOM Element References ---
            // Store references to frequently used DOM elements
            const mobileMenuButton = document.getElementById('mobile-menu-button');
            const mobileMenu = document.getElementById('mobile-menu');
            const userMenu = document.getElementById('user-menu');
            const userDropdown = document.getElementById('user-dropdown');
            const themeToggle = document.getElementById('theme-toggle');
            const progressChartCanvas = document.getElementById('progressChart');
            const chartLoadingMsg = document.getElementById('chart-loading-msg');
            const chartErrorMsg = document.getElementById('chart-error-msg');
            const chartNoDataMsg = document.getElementById('chart-no-data-msg');
            const subjectSelect = document.getElementById('subject-select');
            const subjectSelectAll = document.getElementById('subject-select-all'); // For creating syllabus if needed
            const topicsContainer = document.getElementById('syllabus-topics-container');
            const topicsPlaceholder = document.getElementById('topics-placeholder');
            const syllabusErrorMsg = document.getElementById('syllabus-error-msg');
            const addTopicSection = document.getElementById('add-topic-section');
            const addTopicForm = document.getElementById('add-topic-form');
            const currentSyllabusIdInput = document.getElementById('current-syllabus-id');
            const addTopicBtn = document.getElementById('add-topic-btn');
            const addTopicStatus = document.getElementById('add-topic-status');
            const newTopicNameInput = document.getElementById('new-topic-name');
            const newTopicDescInput = document.getElementById('new-topic-description');
            const chatTrigger = document.getElementById('chat-trigger');
            const chatPopupContainer = document.getElementById('chat-popup-container');
            const chatBody = document.getElementById('chat-body');
            const messageInput = document.getElementById('message-input');
            const sendButton = document.getElementById('send-button');

            // Global variable for the Chart instance (within the IIFE scope)
            let currentChartInstance = null;

            // --- Core UI Interactivity (Mobile Menu, User Dropdown) ---
            if (mobileMenuButton && mobileMenu) {
                mobileMenuButton.addEventListener('click', () => {
                    mobileMenu.classList.toggle('hidden');
                    // Optional: Add ARIA attribute changes
                    const isExpanded = !mobileMenu.classList.contains('hidden');
                    mobileMenuButton.setAttribute('aria-expanded', isExpanded);
                });
            }

            if (userMenu && userDropdown) {
                userMenu.addEventListener('click', (event) => {
                    event.stopPropagation(); // Prevent click from immediately closing menu
                    userDropdown.classList.toggle('hidden');
                     const isExpanded = !userDropdown.classList.contains('hidden');
                     userMenu.setAttribute('aria-expanded', isExpanded);
                });
            }

            // Close menus when clicking outside
            document.addEventListener('click', (event) => {
                // Close user dropdown if open and click is outside
                if (userDropdown && !userDropdown.classList.contains('hidden')) {
                    if (userMenu && !userMenu.contains(event.target) && !userDropdown.contains(event.target)) {
                        userDropdown.classList.add('hidden');
                         userMenu.setAttribute('aria-expanded', 'false');
                    }
                }
                // Close mobile menu if open and click is outside
                if (mobileMenu && !mobileMenu.classList.contains('hidden')) {
                     if (mobileMenuButton && !mobileMenuButton.contains(event.target) && !mobileMenu.contains(event.target)) {
                          mobileMenu.classList.add('hidden');
                          mobileMenuButton.setAttribute('aria-expanded', 'false');
                     }
                }
            });

            // --- Theme Management ---
            function applyTheme(theme) {
                if (theme === 'dark') {
                    document.documentElement.classList.add('dark');
                } else {
                    document.documentElement.classList.remove('dark');
                }
                // Store the preference
                try { // Use try-catch for localStorage access
                    localStorage.setItem('theme', theme);
                } catch (e) {
                    console.warn("Could not save theme preference to localStorage:", e);
                }
                updateThemeIcon(theme);
                // Update chart colors if the chart exists
                if (currentChartInstance) {
                    updateChartTheme(theme);
                }
            }

            function updateThemeIcon(theme) {
                if (!themeToggle) return;
                const isDark = (theme === 'dark');
                const moonIcon = themeToggle.querySelector(".fa-moon");
                const sunIcon = themeToggle.querySelector(".fa-sun");
                if (moonIcon) moonIcon.classList.toggle("hidden", isDark);
                if (sunIcon) sunIcon.classList.toggle("hidden", !isDark);
            }

            function updateChartTheme(theme) {
                if (!currentChartInstance) return; // Only if chart exists
                const isDark = (theme === 'dark');
                // Define colors based on theme
                const gridColor = isDark ? 'rgba(255, 255, 255, 0.15)' : 'rgba(0, 0, 0, 0.08)';
                const labelColor = isDark ? '#a0aec0' : '#4a5568'; // e.g., gray-400 / gray-600
                const tooltipBgColor = isDark ? '#2d3748' : '#ffffff'; // gray-800 / white
                const tooltipFontColor = isDark ? '#e2e8f0' : '#1a202c'; // gray-200 / gray-800

                // Apply new colors to chart options
                const options = currentChartInstance.options;
                if (options.scales.y) { // Check if scales exist (might not for all chart types)
                     options.scales.y.grid.color = gridColor;
                     options.scales.y.ticks.color = labelColor;
                }
                 if (options.scales.x) {
                     options.scales.x.grid.color = gridColor;
                     options.scales.x.ticks.color = labelColor;
                }
                if (options.plugins && options.plugins.legend) {
                    options.plugins.legend.labels.color = labelColor;
                }
                 if (options.plugins && options.plugins.tooltip) {
                    options.plugins.tooltip.backgroundColor = tooltipBgColor;
                    options.plugins.tooltip.titleColor = tooltipFontColor;
                    options.plugins.tooltip.bodyColor = tooltipFontColor;
                 }
                // Redraw the chart with updated options
                currentChartInstance.update();
            }

            // Initialize theme on page load
            // Check localStorage first, then system preference
            let initialTheme = 'light'; // Default to light
            try {
                const storedTheme = localStorage.getItem('theme');
                if (storedTheme) {
                    initialTheme = storedTheme;
                } else if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
                    initialTheme = 'dark';
                }
            } catch(e) {
                console.warn("Could not access localStorage or matchMedia for theme:", e);
            }
            applyTheme(initialTheme); // Apply the determined theme

            // Add event listener for the theme toggle button
            if (themeToggle) {
                themeToggle.addEventListener('click', () => {
                    const newTheme = document.documentElement.classList.contains('dark') ? 'light' : 'dark';
                    applyTheme(newTheme);
                });
            }

            // --- Dynamic Progress Chart ---
            async function fetchAndRenderChart() {
                // Check if chart elements are present
                if (!progressChartCanvas || !chartLoadingMsg || !chartErrorMsg || !chartNoDataMsg) {
                     console.warn("Progress chart canvas or message elements not found.");
                     return;
                }
                // Show loading state
                chartLoadingMsg.classList.remove('hidden');
                chartErrorMsg.classList.add('hidden');
                chartNoDataMsg.classList.add('hidden');
                progressChartCanvas.style.display = 'none'; // Hide canvas while loading

                // Destroy previous chart instance if it exists
                if (currentChartInstance) {
                    currentChartInstance.destroy();
                    currentChartInstance = null;
                }

                try {
                    // Fetch progress data from the backend
                    const response = await fetch('handle_syllabus_actions.php?action=get_teacher_subject_progress');
                    if (!response.ok) {
                        // Handle HTTP errors (like 404, 500)
                        throw new Error(`Network response was not ok (status ${response.status})`);
                    }
                    const data = await response.json();

                    if (data.success && data.progress) {
                         const progressData = data.progress;
                         // Check if there's actually data to display
                         if (Object.keys(progressData).length > 0) {
                              renderChart(progressData); // Call function to draw chart
                              progressChartCanvas.style.display = 'block'; // Show canvas
                         } else {
                             // Show "no data" message if progress object is empty
                             chartNoDataMsg.classList.remove('hidden');
                         }
                    } else {
                         // Handle backend errors reported in the JSON response
                         throw new Error(data.message || 'Failed to fetch progress data from server.');
                    }
                } catch (error) {
                    // Handle network errors or errors during fetch/JSON parsing
                    console.error("Error fetching or rendering chart:", error);
                    chartErrorMsg.textContent = `Could not load chart: ${error.message}`;
                    chartErrorMsg.classList.remove('hidden');
                } finally {
                     // Always hide the loading message when done
                     chartLoadingMsg.classList.add('hidden');
                }
            }

            function renderChart(progressData) {
                 if (!progressChartCanvas) return; // Double check canvas exists

                 const chartLabels = Object.keys(progressData);
                 const chartValues = Object.values(progressData);

                 // Define color palette (add more colors if needed)
                 const colors = [
                        'rgba(79, 70, 229, 0.7)',  // indigo-600
                        'rgba(16, 185, 129, 0.7)', // green-500
                        'rgba(217, 70, 239, 0.7)', // fuchsia-500
                        'rgba(245, 158, 11, 0.7)', // amber-500
                        'rgba(59, 130, 246, 0.7)', // blue-500
                        'rgba(239, 68, 68, 0.7)',  // red-500
                        'rgba(139, 92, 246, 0.7)'  // violet-500
                    ];
                 // Cycle through colors if more bars than colors
                 const backgroundColors = chartLabels.map((_, i) => colors[i % colors.length]);
                 const borderColors = backgroundColors.map(color => color.replace('0.7', '1')); // Make border opaque

                 // Chart.js data configuration
                 const chartConfigData = {
                    labels: chartLabels,
                    datasets: [{
                        label: 'Syllabus Completion', // Tooltip label prefix
                        data: chartValues,
                        backgroundColor: backgroundColors,
                        borderColor: borderColors,
                        borderWidth: 1,
                        barThickness: 'flex', // Adjust bar thickness
                        maxBarThickness: 30, // Max thickness
                    }]
                 };

                 // Chart.js options configuration
                 const chartOptions = {
                    responsive: true,
                    maintainAspectRatio: false, // Important for controlling height via container
                    indexAxis: 'y', // Horizontal bar chart
                    plugins: {
                        legend: { display: false }, // Hide legend; labels are on axis
                        tooltip: {
                            // Customize tooltips
                            callbacks: {
                                label: (context) => `${context.raw}% Completed` // Show percentage
                            }
                        }
                    },
                    scales: {
                        // Y Axis (Subject Names)
                        y: {
                            grid: { drawBorder: false }, // Optionally hide axis line
                            ticks: { font: { size: 10 } } // Adjust font size if needed
                        },
                        // X Axis (Percentage)
                        x: {
                            beginAtZero: true,
                            max: 100, // Percentage scale
                            grid: { drawBorder: false },
                            ticks: {
                                callback: value => value + '%', // Add '%' symbol
                                stepSize: 20 // Define steps on the axis (0, 20, 40...)
                            }
                        }
                    },
                     // Improve hover appearance slightly
                     hover: {
                         mode: 'index', // Highlight items in the same category
                         intersect: false
                     },
                 };

                 // Create the new Chart instance
                 currentChartInstance = new Chart(progressChartCanvas.getContext('2d'), {
                     type: 'bar',
                     data: chartConfigData,
                     options: chartOptions
                 });

                 // After creating the chart, apply the current theme's colors
                 updateChartTheme(document.documentElement.classList.contains('dark') ? 'dark' : 'light');
            }

            // Initial fetch and render of the chart when the page loads
            fetchAndRenderChart();

            // --- Syllabus Management ---
            function showSyllabusLoading() {
                if (!topicsContainer) return;
                // Remove placeholder text if it exists
                const placeholder = document.getElementById('topics-placeholder');
                if (placeholder) placeholder.remove();
                // Add loading spinner
                topicsContainer.innerHTML = '<div class="loading-spinner"></div>';
                // Hide the "Add Topic" form while loading
                if (addTopicSection) addTopicSection.classList.add('hidden');
                // Hide syllabus error message if shown
                if (syllabusErrorMsg) syllabusErrorMsg.classList.add('hidden');
            }

            function showSyllabusError(message) {
                 if (!topicsContainer || !syllabusErrorMsg) return;
                 // Display the error message
                 syllabusErrorMsg.textContent = escapeHtml(message);
                 syllabusErrorMsg.classList.remove('hidden');
                 // Clear the topics container (remove spinner)
                 topicsContainer.innerHTML = '';
                 // Hide the "Add Topic" form
                 if (addTopicSection) addTopicSection.classList.add('hidden');
            }

            function renderTopics(topics) {
                if (!topicsContainer || !addTopicSection) return; // Ensure elements exist
                topicsContainer.innerHTML = ''; // Clear previous content (spinner or error)
                if (syllabusErrorMsg) syllabusErrorMsg.classList.add('hidden'); // Hide error message

                // If no topics, show a message but still allow adding new ones
                if (!topics || topics.length === 0) {
                     topicsContainer.innerHTML = '<p class="text-center text-gray-500 dark:text-gray-400 p-4">No topics created for this syllabus yet. Use the form below to add the first one.</p>';
                     addTopicSection.classList.remove('hidden'); // Show add form
                     return;
                }

                // Create the list element
                const ul = document.createElement('ul');
                ul.className = 'space-y-2';

                // Loop through topics and create list items
                topics.forEach(topic => {
                    const li = document.createElement('li');
                    // Apply base classes and conditional completed class
                    li.className = `topic-item flex items-start justify-between p-3 rounded-md border dark:border-gray-600 shadow-sm ${topic.is_completed ? 'completed' : 'bg-gray-50 dark:bg-gray-700/50'}`;
                    li.dataset.topicId = topic.topic_id; // Store topic ID for later use

                    // Use template literal for cleaner HTML structure
                    li.innerHTML = `
                        <div class="flex items-start flex-grow mr-4">
                            <input type="checkbox"
                                   aria-label="Mark topic ${escapeHtml(topic.topic_name)} as complete"
                                   data-topic-id="${topic.topic_id}"
                                   class="syllabus-topic-checkbox h-4 w-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500 dark:bg-gray-600 dark:border-gray-500 mr-3 mt-1 flex-shrink-0 cursor-pointer"
                                   ${topic.is_completed ? 'checked' : ''}>
                            <div class="flex-grow">
                                <span class="topic-name font-medium text-gray-800 dark:text-gray-200 ${topic.is_completed ? 'completed' : ''}">${escapeHtml(topic.topic_name)}</span>
                                ${topic.description ? `<p class="text-sm text-gray-500 dark:text-gray-400 mt-1">${escapeHtml(topic.description)}</p>` : ''}
                            </div>
                        </div>
                        <div class="text-right flex-shrink-0 ml-2">
                            <span class="completion-date text-xs text-gray-500 dark:text-gray-400 block">
                                ${topic.is_completed && topic.completion_date ? `Completed: ${topic.completion_date}` : ''}
                            </span>
                            <!-- Placeholder for Edit/Delete buttons -->
                            <!-- <button class="text-xs text-blue-500 hover:underline p-1">Edit</button> -->
                        </div>
                    `;
                    ul.appendChild(li);
                });

                // Append the list to the container and show the add topic form
                topicsContainer.appendChild(ul);
                addTopicSection.classList.remove('hidden');
            }

            // Event Listener: Subject Selection Change
            function handleSubjectChange() {
                if (!subjectSelect || !topicsContainer || !addTopicSection || !currentSyllabusIdInput) return;

                const selectedSubjectId = subjectSelect.value;
                // Clear previous state
                topicsContainer.innerHTML = '';
                addTopicSection.classList.add('hidden');
                currentSyllabusIdInput.value = '';
                if(syllabusErrorMsg) syllabusErrorMsg.classList.add('hidden');


                // If no subject selected, show the placeholder
                if (!selectedSubjectId) {
                     topicsContainer.innerHTML = '<p class="text-center text-gray-500 dark:text-gray-400" id="topics-placeholder">Select a subject above to view or manage its syllabus topics.</p>';
                    return;
                }

                // Show loading state for topics
                showSyllabusLoading();

                // Fetch syllabus and topics from backend
                fetch(`handle_syllabus_actions.php?action=get_or_create_syllabus_and_topics&subject_id=${selectedSubjectId}`)
                    .then(response => {
                        if (!response.ok) { throw new Error(`Network response error (${response.status})`); }
                        return response.json();
                    })
                    .then(data => {
                        if (data.success && data.syllabus_id !== undefined && data.topics !== undefined) {
                            // Store the syllabus ID and render the topics
                            currentSyllabusIdInput.value = data.syllabus_id;
                            renderTopics(data.topics);
                        } else {
                            // Show error message from backend or a default one
                            showSyllabusError(data.message || 'Failed to load syllabus data.');
                        }
                    })
                    .catch(error => {
                        // Handle network errors or JSON parsing errors
                        console.error("Error fetching syllabus:", error);
                        showSyllabusError(`Could not load syllabus data. ${error.message}`);
                    });
            }

            // Attach listener if subject select exists
            if (subjectSelect) {
                subjectSelect.addEventListener('change', handleSubjectChange);
            }
            // Also handle the "select all" dropdown if it's used to create a syllabus
            if (subjectSelectAll) {
                 subjectSelectAll.addEventListener('change', (e) => {
                      const selectedSubjectId = e.target.value;
                      if (selectedSubjectId) {
                          // If a real subject is selected in the "create" dropdown
                          // maybe automatically select it in the main dropdown and trigger load?
                          if(subjectSelect) {
                              subjectSelect.value = selectedSubjectId; // Try to select it in the main dropdown
                              handleSubjectChange(); // Trigger the load process
                          }
                      }
                 });
            }


             // Event Listener: Mark Topic Complete (using Event Delegation on the container)
             if (topicsContainer) {
                 topicsContainer.addEventListener('change', (e) => {
                     // Target only the checkboxes within the container
                     if (e.target.classList.contains('syllabus-topic-checkbox')) {
                         const checkbox = e.target;
                         const topicId = checkbox.dataset.topicId;
                         const isCompleted = checkbox.checked;
                         const listItem = checkbox.closest('li.topic-item'); // Find the parent LI

                         // Find elements within the specific LI to update
                         const completionDateSpan = listItem?.querySelector('.completion-date');
                         const topicNameSpan = listItem?.querySelector('.topic-name');

                         // Basic check if elements were found
                         if (!listItem || !completionDateSpan || !topicNameSpan || !topicId) return;

                         checkbox.disabled = true; // Disable checkbox during update

                         // Prepare form data for POST request
                         const formData = new FormData();
                         formData.append('action', 'mark_complete');
                         formData.append('topic_id', topicId);
                         formData.append('is_completed', isCompleted); // Sends 'true' or 'false' string

                         // Send update request to backend
                         fetch('handle_syllabus_actions.php', { method: 'POST', body: formData })
                             .then(response => response.json()) // Assuming backend always returns JSON
                             .then(data => {
                                 if (data.success) {
                                     // Update UI styling on success
                                     listItem.classList.toggle('completed', isCompleted);
                                     listItem.classList.toggle('bg-gray-50', !isCompleted);
                                     listItem.classList.toggle('dark:bg-gray-700/50', !isCompleted);
                                     topicNameSpan.classList.toggle('completed', isCompleted);
                                     // Update completion date text
                                     completionDateSpan.textContent = (isCompleted && data.completion_date) ? `Completed: ${data.completion_date}` : '';
                                     // Refresh the progress chart after status change
                                     fetchAndRenderChart();
                                 } else {
                                     // Revert checkbox state on failure and show error
                                     checkbox.checked = !isCompleted;
                                     alert(`Error: ${data.message || 'Failed to update topic status.'}`);
                                 }
                             })
                             .catch(error => {
                                 // Handle network errors
                                 console.error("Error marking topic complete:", error);
                                 checkbox.checked = !isCompleted; // Revert checkbox
                                 alert('Network error. Could not update topic status.');
                             })
                             .finally(() => {
                                 // Always re-enable the checkbox
                                 checkbox.disabled = false;
                             });
                     }
                 });
             }

            // Event Listener: Add New Topic Form Submission
            if (addTopicForm) {
                addTopicForm.addEventListener('submit', (e) => {
                    e.preventDefault(); // Prevent default page reload

                    // Ensure elements exist before accessing properties
                    if (!addTopicBtn || !addTopicStatus || !currentSyllabusIdInput || !newTopicNameInput ) return;

                    const syllabusId = currentSyllabusIdInput.value;
                    const topicName = newTopicNameInput.value.trim();

                    // Basic validation
                    if (!syllabusId || !topicName) {
                        alert('Please ensure a subject is selected and enter a topic name.');
                        return;
                    }

                    // Disable button and show loading state
                    addTopicBtn.disabled = true;
                    addTopicBtn.innerHTML = `<i class="fas fa-spinner fa-spin mr-2"></i>Adding...`;
                    addTopicStatus.textContent = ''; // Clear previous status

                    // Prepare form data
                    const formData = new FormData(addTopicForm); // Gets all inputs including hidden syllabus_id
                    formData.append('action', 'add_topic'); // Specify the action

                    // Send request to backend
                    fetch('handle_syllabus_actions.php', { method: 'POST', body: formData })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success && data.new_topic) {
                                // Clear the form on success
                                addTopicForm.reset();
                                // Show success message
                                addTopicStatus.textContent = 'Topic added successfully!';
                                addTopicStatus.className = 'ml-3 text-sm text-green-600 dark:text-green-400';

                                // Dynamically add the new topic to the list
                                const ul = topicsContainer.querySelector('ul');
                                // Remove the "No topics" message if it exists
                                const noTopicsMsg = topicsContainer.querySelector('p:not([id])'); // Target P without ID
                                if (noTopicsMsg && noTopicsMsg.textContent.includes("No topics")) noTopicsMsg.remove();

                                if (ul) { // Append to existing list
                                    const newTopic = data.new_topic;
                                    const li = document.createElement('li');
                                    li.className = `topic-item flex items-start justify-between p-3 rounded-md border dark:border-gray-600 bg-gray-50 dark:bg-gray-700/50 animate-fade-in`; // Added fade-in
                                    li.dataset.topicId = newTopic.topic_id;
                                    li.innerHTML = `
                                        <div class="flex items-start flex-grow mr-4">
                                            <input type="checkbox" aria-label="Mark topic ${escapeHtml(newTopic.topic_name)} as complete" data-topic-id="${newTopic.topic_id}" class="syllabus-topic-checkbox h-4 w-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500 dark:bg-gray-600 dark:border-gray-500 mr-3 mt-1 flex-shrink-0 cursor-pointer">
                                            <div class="flex-grow">
                                                <span class="topic-name font-medium text-gray-800 dark:text-gray-200">${escapeHtml(newTopic.topic_name)}</span>
                                                ${newTopic.description ? `<p class="text-sm text-gray-500 dark:text-gray-400 mt-1">${escapeHtml(newTopic.description)}</p>` : ''}
                                            </div>
                                        </div>
                                        <div class="text-right flex-shrink-0 ml-2">
                                             <span class="completion-date text-xs text-gray-500 dark:text-gray-400 block"></span>
                                        </div>
                                    `;
                                    ul.appendChild(li);
                                    // Optional: Scroll the new item into view
                                    li.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
                                } else {
                                    // If no UL existed (e.g., first item), re-render the whole list
                                    handleSubjectChange(); // Trigger a refresh
                                }
                                // Refresh the progress chart after adding a topic
                                fetchAndRenderChart();

                            } else {
                                // Show error message from backend
                                addTopicStatus.textContent = `Error: ${data.message || 'Failed to add topic.'}`;
                                addTopicStatus.className = 'ml-3 text-sm text-red-600 dark:text-red-400';
                            }
                        })
                        .catch(error => {
                            // Handle network errors
                            console.error("Error adding topic:", error);
                            addTopicStatus.textContent = 'Network error. Could not add topic.';
                            addTopicStatus.className = 'ml-3 text-sm text-red-600 dark:text-red-400';
                        })
                        .finally(() => {
                            // Always re-enable button and clear status after a delay
                            addTopicBtn.disabled = false;
                            addTopicBtn.innerHTML = `<i class="fas fa-plus mr-2"></i>Add Topic`;
                            setTimeout(() => { if(addTopicStatus) addTopicStatus.textContent = ''; }, 4000); // Clear message after 4 seconds
                        });
                });
            }

            // --- Chatbot Functionality ---
            // Check if all necessary chatbot elements exist
            if (chatTrigger && chatPopupContainer && chatBody && messageInput && sendButton) {

                 // Function to add a message bubble to the chat body
                 function addChatMessage(text, isUser) {
                     if (!chatBody) return;
                     const messageDiv = document.createElement('div');
                     messageDiv.classList.add('message', isUser ? 'user-message' : 'bot-message');

                     // Apply simple formatting for bot messages
                     if (!isUser) {
                         // Basic Markdown-like replacements (needs improvement for complex cases)
                         text = text.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>'); // Bold
                         text = text.replace(/```([\s\S]*?)```/g, '<pre>$1</pre>');     // Code block
                         text = text.replace(/`(.*?)`/g, '<code>$1</code>');         // Inline code
                         text = text.replace(/^\s*[-*+]\s+(.*)/gm, '<li>$1</li>');      // List items
                         // Wrap list items in <ul> if found (basic detection)
                         if (/<li>/.test(text) && !/<ul>/.test(text)) {
                              text = text.replace(/(<li>.*?<\/li>)/gs, '<ul>$1</ul>').replace(/<\/ul>\s*<ul>/g, '');
                         }
                         text = text.replace(/\n/g, '<br>'); // Convert newlines to <br> *after* other formatting
                         // Remove <br> inside <pre> tags after conversion
                         text = text.replace(/<pre>([\s\S]*?)<\/pre>/g, (match, p1) => {
                             return '<pre>' + p1.replace(/<br\s*\/?>/g, '\n') + '</pre>';
                         });
                         messageDiv.innerHTML = text; // Use innerHTML for bot messages to render formatting
                     } else {
                         // For user messages, set textContent to prevent XSS from user input
                         messageDiv.textContent = text;
                     }

                     chatBody.appendChild(messageDiv);
                     // Scroll to the bottom of the chat body
                     chatBody.scrollTop = chatBody.scrollHeight;
                 }

                 // Function to handle sending a message
                 function sendChatMessage() {
                     if (!messageInput || !sendButton) return;
                     const message = messageInput.value.trim();
                     if (message) {
                         addChatMessage(message, true); // Display user message immediately
                         messageInput.value = ''; // Clear input field
                         // Disable input and show loading indicator
                         messageInput.disabled = true;
                         sendButton.disabled = true;
                         sendButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i>'; // Loading icon

                         // Send message to the backend handler
                         fetch('chatbot_handler.php', { // --- IMPORTANT: Make sure this path is correct ---
                             method: 'POST',
                             headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                             body: 'message=' + encodeURIComponent(message) // Send message URL-encoded
                         })
                         .then(response => {
                             if (!response.ok) throw new Error(`Network error (${response.status})`);
                             return response.json(); // Parse JSON response
                         })
                         .then(data => {
                             // Display bot's response
                             if (data && data.response) {
                                 addChatMessage(data.response, false);
                             } else {
                                 // Handle case where response is missing or malformed
                                 addChatMessage("Sorry, I couldn't process that response. Please try again.", false);
                             }
                         })
                         .catch(error => {
                             // Handle network errors or JSON parsing errors
                             console.error('Chatbot fetch error:', error);
                             addChatMessage("Sorry, I'm having trouble connecting right now. Please check your connection and try again later.", false);
                         })
                         .finally(() => {
                             // Re-enable input and restore button icon, regardless of success/failure
                             messageInput.disabled = false;
                             sendButton.disabled = false;
                             sendButton.innerHTML = '<i class="fas fa-paper-plane"></i>'; // Restore send icon
                             messageInput.focus(); // Focus back on the input field
                         });
                     }
                 }

                 // --- Chatbot Event Listeners ---
                 // Send message on button click
                 sendButton.addEventListener('click', sendChatMessage);

                 // Send message on Enter key press in input field
                 messageInput.addEventListener('keypress', (event) => {
                     if (event.key === 'Enter') {
                         event.preventDefault(); // Prevent default form submission (if inside a form)
                         sendChatMessage();
                     }
                 });

                 // Toggle chat popup visibility on trigger click
                 chatTrigger.addEventListener('click', () => {
                    const isHidden = chatPopupContainer.style.display === 'none' || !chatPopupContainer.style.display;
                    chatPopupContainer.style.display = isHidden ? 'flex' : 'none'; // Use flex for layout
                    chatTrigger.setAttribute('aria-expanded', isHidden);

                    if (isHidden) {
                         messageInput.focus(); // Focus input when opened
                         // Optional: Send initial greeting if chat is empty
                         if (chatBody && chatBody.innerHTML.trim() === '') {
                             // Use setTimeout to allow popup to render before adding message
                             setTimeout(() => addChatMessage("Hello! 👋 How can I help you with your teaching tasks today?", false), 100);
                         }
                    }
                 });

                 // Optional: Close chat popup if clicking outside of it
                 document.addEventListener('click', (event) => {
                      if (chatPopupContainer.style.display !== 'none' &&
                          !chatPopupContainer.contains(event.target) &&
                          !chatTrigger.contains(event.target)) {
                          chatPopupContainer.style.display = 'none';
                           chatTrigger.setAttribute('aria-expanded', 'false');
                      }
                 });

            } else {
                console.warn("Chatbot elements not found. Chatbot functionality will be disabled.");
            }
            // --- END Chatbot Functionality ---


        })(); // End of IIFE encapsulation
    </script>
    <!-- ================== End JavaScript ================== -->

</body>
</html>