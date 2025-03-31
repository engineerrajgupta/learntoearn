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
</head>
<body class="bg-gray-100 dark:bg-gray-900 min-h-screen">
    <div class="flex flex-col h-screen">
        <!-- Header -->
        <header class="bg-white dark:bg-gray-800 shadow-md">
            <div class="container mx-auto px-4 py-3 flex items-center justify-between">
                <div class="flex items-center">
                    <img src="assets/images/logo.svg" alt="LearnIt Tandem Logo" class="h-10 w-auto mr-3">
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
                                <img src="assets/images/user-avatar.jpg" alt="User Avatar" class="w-8 h-8 rounded-full">
                                <span class="text-gray-700 dark:text-gray-300 hidden md:block">Neeraj</span>
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
                                <h2 class="text-2xl font-semibold text-gray-800 dark:text-gray-200">Welcome, John!</h2>
                                <span class="text-sm text-gray-500 dark:text-gray-400">Last login: March 18, 2025 - 9:42 AM</span>
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

                        <!-- Recent Activity -->
                        <section class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
                            <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200 mb-4">Recent Activity</h2>
                            <div class="space-y-4">
                                <div class="flex items-start">
                                    <div class="flex-shrink-0 bg-blue-100 dark:bg-blue-900 p-2 rounded-full">
                                        <i class="fas fa-user-edit text-blue-600 dark:text-blue-400"></i>
                                    </div>
                                    <div class="ml-4">
                                        <p class="text-gray-700 dark:text-gray-300">You updated the syllabus for <strong>Advanced Physics</strong></p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">Today at 8:30 AM</p>
                                    </div>
                                </div>
                                <div class="flex items-start">
                                    <div class="flex-shrink-0 bg-green-100 dark:bg-green-900 p-2 rounded-full">
                                        <i class="fas fa-comment-dots text-green-600 dark:text-green-400"></i>
                                    </div>
                                    <div class="ml-4">
                                        <p class="text-gray-700 dark:text-gray-300"><strong>Emma Wilson</strong> sent you a message</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">Yesterday at 3:45 PM</p>
                                    </div>
                                </div>
                                <div class="flex items-start">
                                    <div class="flex-shrink-0 bg-purple-100 dark:bg-purple-900 p-2 rounded-full">
                                        <i class="fas fa-file-alt text-purple-600 dark:text-purple-400"></i>
                                    </div>
                                    <div class="ml-4">
                                        <p class="text-gray-700 dark:text-gray-300">You generated 15 new MCQs for <strong>Organic Chemistry</strong></p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">March 17, 2025 at 11:20 AM</p>
                                    </div>
                                </div>
                                <div class="flex items-start">
                                    <div class="flex-shrink-0 bg-orange-100 dark:bg-orange-900 p-2 rounded-full">
                                        <i class="fas fa-calendar-check text-orange-600 dark:text-orange-400"></i>
                                    </div>
                                    <div class="ml-4">
                                        <p class="text-gray-700 dark:text-gray-300">You completed teaching <strong>Linear Algebra</strong> topic</p>
                                        <p class="text-xs text-gray-500 dark:text-gray-400">March 16, 2025 at 2:15 PM</p>
                                    </div>
                                </div>
                            </div>
                            <a href="activity.php" class="block mt-4 text-indigo-600 dark:text-indigo-400 hover:underline">View all activity →</a>
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
                        <p class="text-sm text-gray-600 dark:text-gray-300">&copy; 2025 LearnToearn. All rights reserved.</p>
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
        
        userMenu.addEventListener('click', (event) => {
            event.stopPropagation(); // Prevent click event from bubbling up
            userDropdown.classList.toggle('hidden');
        });
    
        // Close dropdown when clicking outside
        document.addEventListener('click', (event) => {
            if (!userMenu.contains(event.target) && !userDropdown.contains(event.target)) {
                userDropdown.classList.add('hidden');
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
        }
    
        // Load theme on page load
        const storedTheme = localStorage.getItem('theme') || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
        applyTheme(storedTheme);
    
        // Toggle theme when button is clicked
        themeToggle.addEventListener('click', () => {
            const newTheme = document.documentElement.classList.contains('dark') ? 'light' : 'dark';
            applyTheme(newTheme);
        });
    
        // Ensure correct icon is shown
        function updateThemeIcon() {
            const isDark = document.documentElement.classList.contains('dark');
            document.querySelector(".fa-moon").classList.toggle("hidden", isDark);
            document.querySelector(".fa-sun").classList.toggle("hidden", !isDark);
        }
    
        updateThemeIcon(); // Run on load
    
        themeToggle.addEventListener('click', updateThemeIcon);
    
        // Initialize progress chart
        const progressChart = document.getElementById('progressChart');
        if (progressChart) {
            new Chart(progressChart, {
                type: 'bar',
                data: {
                    labels: ['Mathematics', 'Physics', 'Chemistry', 'Biology', 'Literature'],
                    datasets: [{
                        label: 'Completion Rate (%)',
                        data: [85, 72, 78, 65, 90],
                        backgroundColor: [
                            'rgba(79, 70, 229, 0.6)',
                            'rgba(16, 185, 129, 0.6)',
                            'rgba(245, 158, 11, 0.6)',
                            'rgba(239, 68, 68, 0.6)',
                            'rgba(139, 92, 246, 0.6)'
                        ],
                        borderColor: [
                            'rgb(79, 70, 229)',
                            'rgb(16, 185, 129)',
                            'rgb(245, 158, 11)',
                            'rgb(239, 68, 68)',
                            'rgb(139, 92, 246)'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 100
                        }
                    }
                }
            });
        }
    </script>
    
</body>
</html>