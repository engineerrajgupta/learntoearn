<?php
 // --- Authentication and Session Management ---
 session_start(); // Must be the very first thing

 // Check if the user is logged in, otherwise redirect to login page
 if (!isset($_SESSION['user_id'])) {
     header("Location: login.php");
     exit; // Stop script execution
 }

 // Get user information from session (set during login)
 $user_id = $_SESSION['user_id'];
 $username = isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'User'; // Use htmlspecialchars for security
 $role = isset($_SESSION['role']) ? $_SESSION['role'] : ''; // Optional: Get role if needed later

 // --- End Authentication ---
?>
<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LearnToEarn- Modern Educational Platform</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.7.0/chart.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/dragula/3.7.2/dragula.min.js"></script>

    <!-- CHATBOT CSS START -->
    <style>
        /* Chatbot styles */
        #chat-trigger {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background-color: #3b82f6; /* Tailwind indigo-500 */
            color: white;
            padding: 10px 15px;
            border-radius: 5px;
            cursor: pointer;
            z-index: 1000; /* Ensure it's on top */
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
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
            z-index: 1000; /* Ensure it's on top */
            width: 90%; /* Responsive width */
            max-width: 350px; /* Max width for the popup */
            background-color: #ffffff; /* Tailwind white */
            /* Dark mode styles for chatbot */
            /* Apply dark mode styles directly if needed, or use JS to toggle classes */
        }
        .dark #chat-popup-container {
             background-color: #1f2937; /* Tailwind gray-800 */
             border: 1px solid #4b5563; /* Tailwind gray-600 */
        }

        .chat-header {
            background-color: #3b82f6; /* Tailwind indigo-500 */
            color: white;
            padding: 12px 16px; /* Adjusted padding */
            text-align: center;
            font-weight: bold;
            font-size: 1rem; /* Adjusted font size */
        }
         /* No specific dark mode needed for header as background is distinct */

        .chat-body {
            padding: 16px;
            height: 300px; /* Fixed height for the chat area */
            overflow-y: auto;
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
            border-radius: 8px; /* Slightly more rounded */
            clear: both;
            max-width: 80%; /* Prevent messages from being too wide */
            word-wrap: break-word; /* Ensure long words break */
            line-height: 1.4;
        }
        .user-message {
            background-color: #dbeafe; /* Tailwind blue-100 */
            color: #1e40af; /* Tailwind blue-800 */
            align-self: flex-end;
            margin-left: 20%; /* Push user messages to the right */
        }
         .dark .user-message {
             background-color: #3730a3; /* Tailwind indigo-800 */
             color: #e0e7ff; /* Tailwind indigo-100 */
         }

        .bot-message {
            background-color: #e5e7eb; /* Tailwind gray-200 */
            color: #1f2937; /* Tailwind gray-800 */
            align-self: flex-start;
            margin-right: 20%; /* Push bot messages to the left */
        }
         .dark .bot-message {
             background-color: #4b5563; /* Tailwind gray-600 */
             color: #f3f4f6; /* Tailwind gray-100 */
         }

        /* Make code blocks look better */
        .bot-message pre {
             background-color: #f3f4f6;
             padding: 8px;
             border-radius: 4px;
             overflow-x: auto;
             margin-top: 5px;
             font-family: monospace;
             font-size: 0.85em;
        }
        .dark .bot-message pre {
             background-color: #374151; /* Darker code block background */
             color: #d1d5db; /* Lighter code text */
        }
        /* Style bullet points */
        .bot-message ul {
            list-style: disc;
            margin-left: 20px;
            padding-left: 5px;
        }
         .bot-message li {
            margin-bottom: 4px;
         }

        .chat-input-container {
            display: flex;
            padding: 8px;
            border-top: 1px solid #e5e7eb; /* Tailwind gray-200 */
            background-color: #ffffff; /* Tailwind white */
        }
         .dark .chat-input-container {
             border-top: 1px solid #4b5563; /* Tailwind gray-600 */
             background-color: #1f2937; /* Tailwind gray-800 */
         }

        .chat-input {
            flex-grow: 1;
            padding: 10px; /* More padding */
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
        }
        .send-button:hover {
            background-color: #2563eb; /* Tailwind indigo-600 */
        }
    </style>
    <!-- CHATBOT CSS END -->

</head>
<body class="bg-gray-100 dark:bg-gray-900 min-h-screen">
    <div class="flex flex-col h-screen">
        <!-- Header -->
        <header class="bg-white dark:bg-gray-800 shadow-md">
            <div class="container mx-auto px-4 py-3 flex items-center justify-between">
                <div class="flex items-center">
                    <img src="/learntoearn/assets/images/learntoearn.png" alt="LearnToEarnLogo" class="h-20 w-20 mr-3"> <!-- Make sure this path is correct -->
                    <h1 class="text-2xl font-bold text-indigo-600 dark:text-indigo-400">LearnToEarn</h1>
                </div>
                <div class="hidden md:flex items-center space-x-4">
                    <nav>
                        <ul class="flex space-x-6 text-gray-600 dark:text-gray-300">
                            <li><a href="index.php" class="hover:text-indigo-600 dark:hover:text-indigo-400">Dashboard</a></li>
                            <li><a href="subjects.php" class="hover:text-indigo-600 dark:hover:text-indigo-400">Subjects</a></li>
                            <li><a href="messages.php" class="hover:text-indigo-600 dark:hover:text-indigo-400">Messages</a></li>
                            <li><a href="ai-tools.php" class="hover:text-indigo-600 dark:hover:text-indigo-400">AI Tools</a></li>
                        </ul>
                    </nav>
                    <div class="flex items-center space-x-4">
                        <button id="theme-toggle" class="p-2 rounded-full bg-gray-200 dark:bg-gray-700" >
                            <i class="fas fa-moon text-gray-600 dark:hidden"></i>
                            <i class="fas fa-sun text-yellow-400 hidden dark:block"></i>
                        </button>
                        <div class="relative">
                            <button id="user-menu" class="flex items-center space-x-2 focus:outline-none">
                                <img src="/learntoearn/assets/images/user.jpg" alt="User Avatar" class="w-8 h-8 rounded-full"> <!-- Make sure this path is correct -->
                                <span class="text-gray-700 dark:text-gray-300 hidden md:block"><?php echo $username ?></span>
                                <i class="fas fa-chevron-down text-gray-500"></i>
                            </button>
                            <div id="user-dropdown" class="absolute right-0 mt-2 w-48 bg-white dark:bg-gray-800 rounded-md shadow-lg py-1 hidden z-10">
                                <a href="profile.php" class="block px-4 py-2 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">Profile</a>
                                <a href="settings.php" class="block px-4 py-2 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">Settings</a>
                                <a href="/learntoearn/pages/logout.php" class="block px-4 py-2 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">Logout</a>
                            </div>
                        </div>
                    </div>
                </div>
                <button id="mobile-menu-button" class="md:hidden focus:outline-none">
                    <i class="fas fa-bars text-gray-600 dark:text-gray-300"></i>
                </button>
            </div>
            <div id="mobile-menu" class="hidden md:hidden bg-white dark:bg-gray-800 shadow-inner">
                <nav class="container mx-auto px-4 py-3">
                    <ul class="space-y-2 text-gray-600 dark:text-gray-300">
                        <li><a href="index.php" class="block py-2 hover:text-indigo-600 dark:hover:text-indigo-400">Dashboard</a></li>
                        <li><a href="subjects.php" class="block py-2 hover:text-indigo-600 dark:hover:text-indigo-400">Subjects</a></li>
                        <li><a href="messages.php" class="block py-2 hover:text-indigo-600 dark:hover:text-indigo-400">Messages</a></li>
                        <li><a href="ai-tools.php" class="block py-2 hover:text-indigo-600 dark:hover:text-indigo-400">AI Tools</a></li>
                        <li><a href="profile.php" class="block py-2 hover:text-indigo-600 dark:hover:text-indigo-400">Profile</a></li>
                        <li><a href="settings.php" class="block py-2 hover:text-indigo-600 dark:hover:text-indigo-400">Settings</a></li>
                        <li><a href="/learntoearn/pages/login.php" class="block py-2 hover:text-indigo-600 dark:hover:text-indigo-400">Logout</a></li>
                    </ul>
                </nav>
            </div>
        </header>

        <!-- Main Content -->
        <main class="flex-grow overflow-y-auto">
            <div class="container mx-auto px-4 py-6">
                <div class="flex flex-col md:flex-row gap-6">
                    <!-- Sidebar (visible on desktop) -->
                    <aside class="w-full md:w-64 bg-white dark:bg-gray-800 rounded-lg shadow-md p-4 hidden md:block h-fit">
                        <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200 mb-4">Quick Access</h2>
                        <nav>
                            <ul class="space-y-2">
                                <li>
                                    <a href="syllabus.php" class="flex items-center space-x-2 p-2 rounded-md hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300">
                                        <i class="fas fa-book"></i>
                                        <span>Syllabus Management</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="progress.php" class="flex items-center space-x-2 p-2 rounded-md hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300">
                                        <i class="fas fa-chart-line"></i>
                                        <span>Progress Tracking</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="mcq-generator.php" class="flex items-center space-x-2 p-2 rounded-md hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300">
                                        <i class="fas fa-question-circle"></i>
                                        <span>MCQ Generator</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="teaching-history.php" class="flex items-center space-x-2 p-2 rounded-md hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300">
                                        <i class="fas fa-history"></i>
                                        <span>Teaching History</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="revision-tool.php" class="flex items-center space-x-2 p-2 rounded-md hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300">
                                        <i class="fas fa-brain"></i>
                                        <span>Revision Tool</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="question-bank.php" class="flex items-center space-x-2 p-2 rounded-md hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300">
                                        <i class="fas fa-database"></i>
                                        <span>Question Bank</span>
                                    </a>
                                </li>
                                <li>
                                    <a href="ai-experts.php" class="flex items-center space-x-2 p-2 rounded-md hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-300">
                                        <i class="fas fa-robot"></i>
                                        <span>AI Subject Experts</span>
                                    </a>
                                </li>
                            </ul>
                        </nav>
                    </aside>

                    <!-- Main Content Area -->
                    <div class="flex-grow">
                        <!-- Welcome Section -->
                        <section class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 mb-6">
                            <div class="flex items-center justify-between mb-4">
                                <h2 class="text-2xl font-semibold text-gray-800 dark:text-gray-200">Welcome, <?php echo $username; ?> !</h2>
                                <!-- <span class="text-sm text-gray-500 dark:text-gray-400">Last login: March 18, 2025 - 9:42 AM</span> -->
                            </div>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">Ready to make learning more effective today? Here's what's happening in your teaching world.</p>
                            <div class="flex flex-wrap gap-4">
                                <div class="flex items-center bg-indigo-100 dark:bg-indigo-900 p-3 rounded-lg">
                                    <i class="fas fa-check-circle text-indigo-600 dark:text-indigo-400 text-xl mr-3"></i>
                                    <div>
                                        <h3 class="font-medium text-gray-800 dark:text-gray-200">Course Completion</h3>
                                        <p class="text-gray-600 dark:text-gray-400">78% of your courses are complete</p>
                                    </div>
                                </div>
                                <div class="flex items-center bg-green-100 dark:bg-green-900 p-3 rounded-lg">
                                    <i class="fas fa-comment text-green-600 dark:text-green-400 text-xl mr-3"></i>
                                    <div>
                                        <h3 class="font-medium text-gray-800 dark:text-gray-200">New Messages</h3>
                                        <p class="text-gray-600 dark:text-gray-400">You have 5 unread messages</p>
                                    </div>
                                </div>
                                <div class="flex items-center bg-yellow-100 dark:bg-yellow-900 p-3 rounded-lg">
                                    <i class="fas fa-calendar-alt text-yellow-600 dark:text-yellow-400 text-xl mr-3"></i>
                                    <div>
                                        <h3 class="font-medium text-gray-800 dark:text-gray-200">Upcoming Sessions</h3>
                                        <p class="text-gray-600 dark:text-gray-400">3 sessions scheduled this week</p>
                                    </div>
                                </div>
                            </div>
                        </section>

                        <!-- Progress Overview -->
                        <section class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 mb-6">
                            <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200 mb-4">Progress Overview</h2>
                            <div class="flex flex-col md:flex-row gap-6">
                                <div class="w-full md:w-1/2">
                                    <canvas id="progressChart" width="400" height="200"></canvas>
                                </div>
                                <div class="w-full md:w-1/2">
                                    <h3 class="text-lg font-medium text-gray-800 dark:text-gray-200 mb-2">Recently Completed Topics</h3>
                                    <ul class="space-y-2">
                                        <li class="flex items-center justify-between border-b border-gray-200 dark:border-gray-700 pb-2">
                                            <span class="text-gray-700 dark:text-gray-300">Introduction to Calculus</span>
                                            <span class="text-xs text-gray-500 dark:text-gray-400">March 15, 2025</span>
                                        </li>
                                        <li class="flex items-center justify-between border-b border-gray-200 dark:border-gray-700 pb-2">
                                            <span class="text-gray-700 dark:text-gray-300">Cell Biology Fundamentals</span>
                                            <span class="text-xs text-gray-500 dark:text-gray-400">March 12, 2025</span>
                                        </li>
                                        <li class="flex items-center justify-between border-b border-gray-200 dark:border-gray-700 pb-2">
                                            <span class="text-gray-700 dark:text-gray-300">Shakespeare's Sonnets</span>
                                            <span class="text-xs text-gray-500 dark:text-gray-400">March 10, 2025</span>
                                        </li>
                                        <li class="flex items-center justify-between border-b border-gray-200 dark:border-gray-700 pb-2">
                                            <span class="text-gray-700 dark:text-gray-300">Newton's Laws of Motion</span>
                                            <span class="text-xs text-gray-500 dark:text-gray-400">March 8, 2025</span>
                                        </li>
                                    </ul>
                                    <a href="progress.php" class="block mt-4 text-indigo-600 dark:text-indigo-400 hover:underline">View full progress report →</a>
                                </div>
                            </div>
                        </section>

                        <!-- Quick Actions -->
                        <section class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
                            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                                <div class="flex items-center mb-4">
                                    <i class="fas fa-file-alt text-blue-500 text-xl mr-3"></i>
                                    <h3 class="text-lg font-medium text-gray-800 dark:text-gray-200">Create New Syllabus</h3>
                                </div>
                                <p class="text-gray-600 dark:text-gray-300 mb-4">Start planning your new course or update existing ones.</p>
                                <a href="syllabus-create.php" class="block w-full px-4 py-2 text-center bg-blue-500 hover:bg-blue-600 text-white rounded-md">Create Now</a>
                            </div>
                            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                                <div class="flex items-center mb-4">
                                    <i class="fas fa-lightbulb text-yellow-500 text-xl mr-3"></i>
                                    <h3 class="text-lg font-medium text-gray-800 dark:text-gray-200">Generate MCQs</h3>
                                </div>
                                <p class="text-gray-600 dark:text-gray-300 mb-4">Create AI-assisted multiple choice questions for your topics.</p>
                                <a href="mcq-generator.php" class="block w-full px-4 py-2 text-center bg-yellow-500 hover:bg-yellow-600 text-white rounded-md">Generate MCQs</a>
                            </div>
                            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                                <div class="flex items-center mb-4">
                                    <i class="fas fa-comments text-green-500 text-xl mr-3"></i>
                                    <h3 class="text-lg font-medium text-gray-800 dark:text-gray-200">Message Students</h3>
                                </div>
                                <p class="text-gray-600 dark:text-gray-300 mb-4">Communicate with your students through the messaging system.</p>
                                <a href="messages.php" class="block w-full px-4 py-2 text-center bg-green-500 hover:bg-green-600 text-white rounded-md">Open Messages</a>
                            </div>
                        </section>

                        <!-- Recent Activity (Placeholder Content) -->
                        <section class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 mb-6">
                           <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200 mb-4">More Page Content</h2>
                           <p class="text-gray-600 dark:text-gray-300">This section represents other content you might have on your dashboard page.</p>
                           <!-- Add more dashboard widgets or content here -->
                        </section>

                    </div>
                </div>
            </div>
        </main>

        <!-- Footer -->
        <footer class="bg-white dark:bg-gray-800 shadow-md mt-auto">
            <div class="container mx-auto px-4 py-4">
                <div class="flex flex-col md:flex-row justify-between items-center">
                    <div class="text-center md:text-left mb-4 md:mb-0">
                        <p class="text-sm text-gray-600 dark:text-gray-300">© 2025 LearnToearn. All rights reserved.</p>
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
    </div>

    <!-- CHATBOT HTML START -->
    <button id="chat-trigger">
       <i class="fas fa-comment-dots mr-2"></i> Chat with AI
    </button>

    <div id="chat-popup-container">
        <div class="chat-header">
            LearnToEarn AI Helper
        </div>
        <div id="chat-body" class="chat-body">
            <!-- Messages will be added here by JS -->
        </div>
        <div class="chat-input-container">
            <input type="text" id="message-input" class="chat-input" placeholder="Ask a question...">
            <button id="send-button" class="send-button">
                <i class="fas fa-paper-plane"></i> <!-- Optional: Send Icon -->
            </button>
        </div>
    </div>
    <!-- CHATBOT HTML END -->


    <!-- Scripts -->
    <script>
        // --- LearnToEarn Existing Scripts ---
        // Mobile menu toggle
        const mobileMenuButton = document.getElementById('mobile-menu-button');
        const mobileMenu = document.getElementById('mobile-menu');

        mobileMenuButton.addEventListener('click', () => {
            mobileMenu.classList.toggle('hidden');
        });

        // User dropdown toggle
        const userMenu = document.getElementById('user-menu');
        const userDropdown = document.getElementById('user-dropdown');

        // Check if userMenu exists before adding listener (robustness)
        if (userMenu) {
            userMenu.addEventListener('click', (event) => {
                event.stopPropagation(); // Prevent click event from bubbling up
                if(userDropdown) userDropdown.classList.toggle('hidden');
            });
        }

        // Close dropdown when clicking outside
        document.addEventListener('click', (event) => {
            if (userDropdown && !userDropdown.classList.contains('hidden')) {
                if (userMenu && !userMenu.contains(event.target) && !userDropdown.contains(event.target)) {
                    userDropdown.classList.add('hidden');
                }
            }
        });

        // Theme toggle logic
        const themeToggle = document.getElementById('theme-toggle');

        function applyTheme(theme) {
            if (theme === 'dark') {
                document.documentElement.classList.add('dark');
                localStorage.setItem('theme', 'dark');
            } else {
                document.documentElement.classList.remove('dark');
                localStorage.setItem('theme', 'light');
            }
            updateThemeIcon(); // Update icon whenever theme changes
             // Also update chart colors if needed when theme changes
             // updateChartTheme(theme); // Example function call
        }

        // Load theme on page load
        const storedTheme = localStorage.getItem('theme') || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
        applyTheme(storedTheme);

        // Toggle theme when button is clicked
        if (themeToggle) {
            themeToggle.addEventListener('click', () => {
                const newTheme = document.documentElement.classList.contains('dark') ? 'light' : 'dark';
                applyTheme(newTheme);
            });
        }

        // Ensure correct icon is shown
        function updateThemeIcon() {
            const isDark = document.documentElement.classList.contains('dark');
            const moonIcon = document.querySelector("#theme-toggle .fa-moon");
            const sunIcon = document.querySelector("#theme-toggle .fa-sun");
            if (moonIcon) moonIcon.classList.toggle("hidden", isDark);
            if (sunIcon) sunIcon.classList.toggle("hidden", !isDark);
        }

        // Initialize progress chart
        const progressChartCanvas = document.getElementById('progressChart');
        let progressChartInstance = null; // Keep track of the chart instance

        function createOrUpdateChart() {
             if (!progressChartCanvas) return; // Don't run if canvas not found

             const isDark = document.documentElement.classList.contains('dark');
             const gridColor = isDark ? 'rgba(255, 255, 255, 0.2)' : 'rgba(0, 0, 0, 0.1)';
             const labelColor = isDark ? '#d1d5db' : '#374151'; // Tailwind gray-300 / gray-700

             const chartData = {
                labels: ['Mathematics', 'Physics', 'Chemistry', 'Biology', 'Literature'],
                datasets: [{
                    label: 'Completion Rate (%)',
                    data: [85, 72, 78, 65, 90],
                    backgroundColor: [
                        'rgba(79, 70, 229, 0.7)', // indigo-600
                        'rgba(16, 185, 129, 0.7)', // green-500
                        'rgba(245, 158, 11, 0.7)', // yellow-500
                        'rgba(239, 68, 68, 0.7)',  // red-500
                        'rgba(139, 92, 246, 0.7)'  // violet-500
                    ],
                    borderColor: [ // Optional: Add borders if desired
                        'rgb(79, 70, 229)',
                        'rgb(16, 185, 129)',
                        'rgb(245, 158, 11)',
                        'rgb(239, 68, 68)',
                        'rgb(139, 92, 246)'
                    ],
                    borderWidth: 1
                }]
            };

             const chartOptions = {
                responsive: true,
                maintainAspectRatio: false, // Allow chart to fill container height better
                plugins: {
                    legend: {
                         labels: { color: labelColor }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        grid: { color: gridColor },
                        ticks: { color: labelColor }
                    },
                    x: {
                         grid: { color: gridColor },
                         ticks: { color: labelColor }
                    }
                }
            };

            if (progressChartInstance) {
                // If chart exists, update its data and options
                progressChartInstance.data = chartData;
                progressChartInstance.options = chartOptions;
                progressChartInstance.update();
            } else {
                // Otherwise, create a new chart
                progressChartInstance = new Chart(progressChartCanvas, {
                    type: 'bar',
                    data: chartData,
                    options: chartOptions
                });
            }
        }

         createOrUpdateChart(); // Initial chart creation

         // Optional: Update chart on theme change (add this inside applyTheme or listen separately)
         // e.g., inside applyTheme: createOrUpdateChart();

              // --- CHATBOT JAVASCRIPT START ---
              const chatTrigger = document.getElementById('chat-trigger');
        const chatPopupContainer = document.getElementById('chat-popup-container');
        const chatBody = document.getElementById('chat-body');
        const messageInput = document.getElementById('message-input');
        const sendButton = document.getElementById('send-button');

        // Ensure chatbot elements exist before adding listeners
        if (chatTrigger && chatPopupContainer && chatBody && messageInput && sendButton) {

            // Function to add messages to the chat window
            function addMessage(text, isUser) {
                const messageDiv = document.createElement('div');
                messageDiv.classList.add('message', isUser ? 'user-message' : 'bot-message');

                // Basic Markdown-like formatting for bot messages (received from backend)
                if (!isUser) {
                    // Convert **text** to <strong>text</strong>
                    text = text.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
                    // Convert *text* to <em>text</em> (simple version)
                    // text = text.replace(/\*(.*?)\*/g, '<em>$1</em>');
                    // Convert ```code``` to <pre><code>code</code></pre>
                    text = text.replace(/```([\s\S]*?)```/g, '<pre><code>$1</code></pre>');
                    // Convert `code` to <code>code</code>
                    text = text.replace(/`(.*?)`/g, '<code>$1</code>');
                    // Convert bullet points (* item) to <li>item</li> (basic)
                    text = text.replace(/^\s*\*\s+(.*)/gm, '<li>$1</li>');
                    // Wrap list items in <ul> if detected (needs refinement for robustness)
                    if (text.includes('<li>')) {
                        text = '<ul>' + text.replace(/<\/li>\s*<li>/g, '</li><li>') + '</ul>';
                        // Clean up potential extra <br> tags introduced by newline conversion around lists
                        text = text.replace(/<br>\s*<ul>/g, '<ul>');
                        text = text.replace(/<\/ul>\s*<br>/g, '</ul>');
                    }
                     // Convert newlines to <br> AFTER list/code formatting
                     text = text.replace(/\n/g, '<br>');
                     // Remove <br> inside <pre> tags
                     text = text.replace(/<pre><code>([\s\S]*?)<\/code><\/pre>/g, (match, p1) => {
                        return '<pre><code>' + p1.replace(/<br\s*\/?>/g, '\n') + '</code></pre>';
                     });
                } else {
                    // Sanitize user input before displaying (simple version)
                    const tempDiv = document.createElement('div');
                    tempDiv.textContent = text;
                    text = tempDiv.innerHTML; // Converts <, > etc. to HTML entities
                }

                messageDiv.innerHTML = text; // Use innerHTML *after* formatting/sanitization
                chatBody.appendChild(messageDiv);
                chatBody.scrollTop = chatBody.scrollHeight; // Scroll to bottom
            }

            // Function to handle sending a message
            function sendMessage() {
                 const message = messageInput.value.trim();
                 if (message) {
                     addMessage(message, true); // Display user message immediately
                     messageInput.value = '';
                     // Disable input while waiting for bot response
                     messageInput.disabled = true;
                     sendButton.disabled = true;
                     sendButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i>'; // Show loading indicator

                     // Send message to the backend PHP script
                     fetch('chatbot_handler.php', { // Make sure this path is correct!
                         method: 'POST',
                         headers: {
                             'Content-Type': 'application/x-www-form-urlencoded',
                         },
                         body: 'message=' + encodeURIComponent(message) // Send message as form data
                     })
                     .then(response => {
                         if (!response.ok) {
                             // Handle HTTP errors (like 404, 500)
                             throw new Error(`HTTP error! status: ${response.status}`);
                         }
                         return response.json(); // Parse the JSON response from PHP
                     })
                     .then(data => {
                         // Display the response from the backend
                         if (data && data.response) {
                             addMessage(data.response, false);
                         } else {
                             // Handle cases where backend response is missing or malformed
                             addMessage("Sorry, I received an unexpected response. Please try again.", false);
                         }
                     })
                     .catch(error => {
                         // Handle network errors or errors during fetch/parsing
                         console.error('Chatbot fetch error:', error);
                         addMessage("Sorry, I couldn't connect to the AI assistant right now. Please check your connection and try again.", false);
                     })
                     .finally(() => {
                         // Re-enable input regardless of success or failure
                         messageInput.disabled = false;
                         sendButton.disabled = false;
                         sendButton.innerHTML = '<i class="fas fa-paper-plane"></i>'; // Restore send icon
                         messageInput.focus(); // Focus back on input
                     });
                 }
            }

            // --- Event Listeners ---
            sendButton.addEventListener('click', sendMessage);

            messageInput.addEventListener('keypress', (event) => {
                if (event.key === 'Enter') {
                    event.preventDefault(); // Prevent default form submission
                    sendMessage();
                }
            });

            chatTrigger.addEventListener('click', () => {
                const isHidden = chatPopupContainer.style.display === 'none' || chatPopupContainer.style.display === '';
                chatPopupContainer.style.display = isHidden ? 'flex' : 'none'; // Use flex for container layout
                chatPopupContainer.style.flexDirection = 'column'; // Ensure vertical layout

                if (isHidden && chatBody.innerHTML.trim() === '') {
                    // Initial bot greeting when the popup is opened for the first time or is empty
                    setTimeout(() => {
                        addMessage("Hello! 👋 How can I help you with your studies today?", false); // Static initial greeting
                        messageInput.focus(); // Focus input when opened
                    }, 300);
                } else if (isHidden) {
                     messageInput.focus(); // Focus input field whenever opened
                }
            });

            // Close chat popup if clicking outside of it
            document.addEventListener('click', (event) => {
                 // Ensure the click is not on the trigger or inside the popup itself
                if (!chatPopupContainer.contains(event.target) && !chatTrigger.contains(event.target)) {
                    if (chatPopupContainer.style.display !== 'none') {
                         chatPopupContainer.style.display = 'none';
                    }
                }
            });

        } else {
            console.error("Chatbot HTML elements not found. Chatbot functionality disabled.");
        }
        // --- CHATBOT JAVASCRIPT END ---

    </script>

</body>
</html>