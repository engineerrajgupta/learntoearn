<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>LearnToEarn</title>
    <!-- Tailwind CSS (CDN) -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
    <!-- Font Awesome (for icons) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" />
    <!-- Dark mode logic (Optional) -->
    <script>
        // On page load, apply saved theme
        if (
            localStorage.theme === 'dark' ||
            (!('theme' in localStorage) &&
                window.matchMedia('(prefers-color-scheme: dark)').matches)
        ) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }
    </script>
</head>

<body class="bg-gray-100 dark:bg-gray-900 min-h-screen flex flex-col">
    <!-- Header -->
    <header class="bg-white dark:bg-gray-800 shadow-md">
        <div class="container mx-auto px-4 py-3 flex items-center justify-between">
            <div class="flex items-center">
                <!-- Logo -->
                <img src="assets/images/logo.svg" alt="LearnToEarn Logo" class="h-10 w-auto mr-3" />
                <h1 class="text-2xl font-bold text-indigo-600 dark:text-indigo-400">
                    LearnToEarn
                </h1>
            </div>

            <!-- Right Section of Header -->
            <div class="hidden md:flex items-center space-x-4">
                <nav>
                    <ul class="flex space-x-6 text-gray-600 dark:text-gray-300">
                        <!-- Show navigation only if logged in -->
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <li><a href="pages/dashboard.php" class="hover:text-indigo-600 dark:hover:text-indigo-400">Dashboard</a></li>
                            <li><a href="pages/subjects.php" class="hover:text-indigo-600 dark:hover:text-indigo-400">Subjects</a></li>
                            <li><a href="pages/messages.php" class="hover:text-indigo-600 dark:hover:text-indigo-400">Messages</a></li>
                            <li><a href="pages/ai-tools.php" class="hover:text-indigo-600 dark:hover:text-indigo-400">AI Tools</a></li>
                        <?php endif; ?>
                    </ul>
                </nav>

                <!-- Theme Toggle -->
                <button id="theme-toggle" class="p-2 rounded-full bg-gray-200 dark:bg-gray-700">
                    <i class="fas fa-moon text-gray-600 dark:hidden"></i>
                    <i class="fas fa-sun text-yellow-400 hidden dark:block"></i>
                </button>

                <!-- User Menu -->
                <div class="relative">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <button id="user-menu" class="flex items-center space-x-2 focus:outline-none">
                            <img src="assets/images/user-avatar.jpg" alt="User Avatar" class="w-8 h-8 rounded-full" />
                            <span class="text-gray-700 dark:text-gray-300 hidden md:block">
                                <?php echo $_SESSION['user_name']; ?>
                            </span>
                            <i class="fas fa-chevron-down text-gray-500"></i>
                        </button>
                        <div id="user-dropdown" class="absolute right-0 mt-2 w-48 bg-white dark:bg-gray-800 rounded-md shadow-lg py-1 hidden z-10">
                            <a href="pages/profile.php" class="block px-4 py-2 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">Profile</a>
                            <a href="pages/settings.php" class="block px-4 py-2 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">Settings</a>
                            <a href="pages/logout.php" class="block px-4 py-2 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700">Logout</a>
                        </div>
                    <?php else: ?>
                        <!-- If not logged in, show Login button -->
                        <a href="pages/login.php" class="px-4 py-2 bg-blue-500 text-white rounded">Login</a>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Mobile Menu Button -->
            <button id="mobile-menu-button" class="md:hidden focus:outline-none">
                <i class="fas fa-bars text-gray-600 dark:text-gray-300"></i>
            </button>
        </div>

        <!-- Mobile Menu -->
        <div id="mobile-menu" class="hidden md:hidden bg-white dark:bg-gray-800 shadow-inner">
            <nav class="container mx-auto px-4 py-3">
                <ul class="space-y-2 text-gray-600 dark:text-gray-300">
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li><a href="pages/dashboard.php" class="block py-2 hover:text-indigo-600 dark:hover:text-indigo-400">Dashboard</a></li>
                        <li><a href="pages/subjects.php" class="block py-2 hover:text-indigo-600 dark:hover:text-indigo-400">Subjects</a></li>
                        <li><a href="pages/messages.php" class="block py-2 hover:text-indigo-600 dark:hover:text-indigo-400">Messages</a></li>
                        <li><a href="pages/ai-tools.php" class="block py-2 hover:text-indigo-600 dark:hover:text-indigo-400">AI Tools</a></li>
                        <li><a href="pages/profile.php" class="block py-2 hover:text-indigo-600 dark:hover:text-indigo-400">Profile</a></li>
                        <li><a href="pages/settings.php" class="block py-2 hover:text-indigo-600 dark:hover:text-indigo-400">Settings</a></li>
                        <li><a href="pages/logout.php" class="block py-2 hover:text-indigo-600 dark:hover:text-indigo-400">Logout</a></li>
                    <?php else: ?>
                        <li><a href="pages/login.php" class="block py-2 hover:text-indigo-600 dark:hover:text-indigo-400">Login</a></li>
                        <li><a href="pages/register.php" class="block py-2 hover:text-indigo-600 dark:hover:text-indigo-400">Sign Up</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>

    <!-- Main Content -->
    <main class="flex-grow overflow-y-auto">
        <div class="container mx-auto px-4 py-6">
            <?php if (isset($_SESSION['user_id'])): ?>
                <!-- If logged in, show personalized content -->
                <section class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 mb-6">
                    <h2 class="text-2xl font-semibold text-gray-800 dark:text-gray-200">
                        Welcome back, <?php echo $_SESSION['user_name']; ?>!
                    </h2>
                    <p class="text-gray-600 dark:text-gray-300 mt-2">
                        Here’s your personalized dashboard. Use the navigation to explore your subjects, AI tools, and messages.
                    </p>
                </section>
            <?php else: ?>
                <!-- If not logged in, show default content -->
                <section class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 mb-6">
                    <h2 class="text-2xl font-semibold text-gray-800 dark:text-gray-200">
                        Welcome to LearnToEarn!
                    </h2>
                    <p class="text-gray-600 dark:text-gray-300 mt-2">
                        Please <a href="pages/login.php" class="text-indigo-600 dark:text-indigo-400">login</a> or <a href="pages/register.php" class="text-indigo-600 dark:text-indigo-400">sign up</a> to access the full platform.
                    </p>
                </section>
            <?php endif; ?>

            <!-- Additional sections or placeholders for your homepage -->
            <section class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200 mb-4">Platform Highlights</h2>
                <p class="text-gray-600 dark:text-gray-300">
                    LearnToEarn offers a comprehensive suite of tools to make teaching and learning more effective, including:
                </p>
                <ul class="list-disc list-inside mt-2 text-gray-600 dark:text-gray-300">
                    <li>Syllabus Management</li>
                    <li>AI-Powered MCQ Generation</li>
                    <li>Student Messaging System</li>
                    <li>Progress Tracking &amp; Reporting</li>
                </ul>
            </section>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-white dark:bg-gray-800 shadow-md mt-auto">
        <div class="container mx-auto px-4 py-4">
            <div class="flex flex-col md:flex-row justify-between items-center">
                <div class="text-center md:text-left mb-4 md:mb-0">
                    <p class="text-sm text-gray-600 dark:text-gray-300">&copy; <?php echo date('Y'); ?> LearnToEarn. All rights reserved.</p>
                </div>
                <div class="flex space-x-4">
                    <a href="pages/about.php" class="text-sm text-gray-600 dark:text-gray-300 hover:text-indigo-600 dark:hover:text-indigo-400">About</a>
                    <a href="pages/privacy.php" class="text-sm text-gray-600 dark:text-gray-300 hover:text-indigo-600 dark:hover:text-indigo-400">Privacy Policy</a>
                    <a href="pages/terms.php" class="text-sm text-gray-600 dark:text-gray-300 hover:text-indigo-600 dark:hover:text-indigo-400">Terms of Service</a>
                    <a href="pages/contact.php" class="text-sm text-gray-600 dark:text-gray-300 hover:text-indigo-600 dark:hover:text-indigo-400">Contact</a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Scripts -->
    <script>
        // Mobile menu toggle
        const mobileMenuButton = document.getElementById('mobile-menu-button');
        const mobileMenu = document.getElementById('mobile-menu');

        mobileMenuButton.addEventListener('click', () => {
            mobileMenu.classList.toggle('hidden');
        });

        // User dropdown toggle
        const userMenu = document.getElementById('user-menu');
        const userDropdown = document.getElementById('user-dropdown');

        if (userMenu) {
            userMenu.addEventListener('click', (e) => {
                e.stopPropagation();
                userDropdown.classList.toggle('hidden');
            });
        }

        // Close dropdown when clicking outside
        document.addEventListener('click', (event) => {
            if (userDropdown && !userMenu.contains(event.target) && !userDropdown.contains(event.target)) {
                userDropdown.classList.add('hidden');
            }
        });

        // Theme toggle
        const themeToggle = document.getElementById('theme-toggle');

        function updateThemeIcon() {
            const isDark = document.documentElement.classList.contains('dark');
            document.querySelector('.fa-moon').classList.toggle('hidden', isDark);
            document.querySelector('.fa-sun').classList.toggle('hidden', !isDark);
        }

        if (themeToggle) {
            themeToggle.addEventListener('click', () => {
                document.documentElement.classList.toggle('dark');
                if (document.documentElement.classList.contains('dark')) {
                    localStorage.setItem('theme', 'dark');
                } else {
                    localStorage.setItem('theme', 'light');
                }
                updateThemeIcon();
            });
        }

        // Set initial icon state
        updateThemeIcon();
    </script>
</body>
</html>
