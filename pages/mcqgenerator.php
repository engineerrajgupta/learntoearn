<?php
 // --- Authentication and Session Management ---
 session_start(); // Must be the very first thing

 // Check if the user is logged in, otherwise redirect to login page
 if (!isset($_SESSION['user_id'])) {
     header("Location: login.php"); // Make sure login.php exists
     exit; // Stop script execution
 }

 // Get user information from session
 $user_id = $_SESSION['user_id'];
 $username = isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'User';
?>
<!DOCTYPE html>
<html lang="en" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MCQ Generator - LearnToEarn</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/tailwindcss/2.2.19/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        /* Minimal styles specific to the MCQ generator chat interface */
        html, body {
            height: 100%;
        }
        body {
             display: flex;
             flex-direction: column;
        }
        main {
             flex-grow: 1; /* Allow main content to fill space */
             display: flex;
             flex-direction: column;
        }
         #chat-container {
            flex-grow: 1; /* Make chat container fill available vertical space */
            display: flex;
            flex-direction: column;
            padding: 1rem; /* Add padding */
            max-width: 900px; /* Limit width for better readability */
            width: 100%;
            margin: 0 auto; /* Center the container */
        }
        #chat-output {
            flex-grow: 1;
            overflow-y: auto; /* Enable scrolling for messages */
            padding: 1rem;
            border: 1px solid #e5e7eb; /* Tailwind gray-200 */
            border-radius: 8px;
            margin-bottom: 1rem;
             background-color: #f9fafb; /* Tailwind gray-50 */
             display: flex;
             flex-direction: column;
             gap: 0.75rem; /* Space between messages */
        }
        .dark #chat-output {
             border-color: #4b5563; /* Tailwind gray-600 */
             background-color: #374151; /* Tailwind gray-700 */
        }

        .message {
            padding: 0.75rem 1rem; /* Adjusted padding */
            border-radius: 0.75rem; /* Rounded corners */
            max-width: 85%; /* Max width of message bubbles */
            word-wrap: break-word;
            line-height: 1.5;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1); /* Subtle shadow */
        }

        .user-message {
            background-color: #dbeafe; /* Tailwind blue-100 */
            color: #1e40af; /* Tailwind blue-800 */
            align-self: flex-end; /* Align user message to the right */
            border-bottom-right-radius: 0.25rem; /* Flat corner */
        }
        .dark .user-message {
             background-color: #3730a3; /* Tailwind indigo-800 */
             color: #e0e7ff; /* Tailwind indigo-100 */
        }

        .bot-message {
             background-color: #e5e7eb; /* Tailwind gray-200 */
             color: #1f2937; /* Tailwind gray-800 */
             align-self: flex-start; /* Align bot message to the left */
             border-bottom-left-radius: 0.25rem; /* Flat corner */
        }
        .dark .bot-message {
             background-color: #4b5563; /* Tailwind gray-600 */
             color: #f3f4f6; /* Tailwind gray-100 */
        }
        /* Preformatted text (like code blocks or MCQs) */
         .bot-message pre {
             background-color: rgba(0, 0, 0, 0.05);
             padding: 0.75rem;
             border-radius: 0.375rem; /* rounded-md */
             overflow-x: auto;
             margin-top: 0.5rem;
             font-family: Consolas, Monaco, 'Andale Mono', 'Ubuntu Mono', monospace;
             font-size: 0.9em;
             white-space: pre-wrap; /* Allow wrapping within pre */
             word-wrap: break-word;
         }
         .dark .bot-message pre {
             background-color: rgba(255, 255, 255, 0.1);
             color: #e5e7eb; /* Tailwind gray-200 */
         }
        .bot-message ul { list-style: disc; margin-left: 20px; padding-left: 5px; margin-top: 0.5rem;}
        .bot-message li { margin-bottom: 0.25rem; }


        #input-area {
            display: flex;
            align-items: center; /* Align items vertically */
            padding: 0.5rem 1rem;
            border-top: 1px solid #e5e7eb; /* Tailwind gray-200 */
             background-color: #ffffff;
        }
         .dark #input-area {
             border-color: #4b5563; /* Tailwind gray-600 */
             background-color: #1f2937; /* Tailwind gray-800 */
         }

         #topic-input {
             flex-grow: 1;
             padding: 0.75rem;
             border: 1px solid #d1d5db; /* Tailwind gray-300 */
             border-radius: 0.375rem; /* rounded-md */
             resize: none; /* Prevent manual resize */
             height: 50px; /* Initial height, can grow */
             font-size: 1rem;
              background-color: #ffffff;
              color: #111827;
             margin-right: 0.75rem;
             transition: border-color 0.2s ease;
         }
          .dark #topic-input {
              border-color: #4b5563;
              background-color: #374151;
              color: #f9fafb;
          }
         #topic-input:focus {
             outline: none;
             border-color: #3b82f6; /* Tailwind indigo-500 */
             box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.3);
         }

         #send-button {
            padding: 0.6rem 1.2rem; /* Adjust padding */
            background-color: #3b82f6; /* Tailwind indigo-500 */
            color: white;
            border: none;
            border-radius: 0.375rem; /* rounded-md */
            cursor: pointer;
            font-size: 1rem; /* Match input font size */
            transition: background-color 0.2s ease;
            white-space: nowrap;
         }
          #send-button:hover {
              background-color: #2563eb; /* Tailwind indigo-600 */
          }
         #send-button:disabled {
              background-color: #9ca3af; /* Tailwind gray-400 */
              cursor: not-allowed;
          }

         .loading-spinner {
             display: inline-block;
             width: 1em;
             height: 1em;
             border: 2px solid rgba(255, 255, 255, 0.3);
             border-radius: 50%;
             border-top-color: #fff;
             animation: spin 1s ease-in-out infinite;
         }
          @keyframes spin {
              to { transform: rotate(360deg); }
          }

    </style>
</head>
<body class="bg-gray-100 dark:bg-gray-900 text-gray-900 dark:text-gray-100">

    <!-- Minimal Header -->
    <header class="bg-white dark:bg-gray-800 shadow-sm sticky top-0 z-10">
        <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-3 flex justify-between items-center">
            <h1 class="text-xl font-bold text-indigo-600 dark:text-indigo-400">AI MCQ Generator</h1>
             <div class="flex items-center space-x-3">
                 <span class="text-sm text-gray-600 dark:text-gray-400">Welcome, <?php echo $username; ?>!</span>
                  <button id="theme-toggle" class="p-2 rounded-full bg-gray-200 dark:bg-gray-700 text-gray-600 dark:text-yellow-400">
                      <i class="fas fa-moon dark:hidden"></i>
                      <i class="fas fa-sun hidden dark:block"></i>
                  </button>
                   <a href="/learntoearn/pages/logout.php" class="text-sm text-gray-600 dark:text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400" title="Logout">
                       <i class="fas fa-sign-out-alt"></i>
                   </a>
             </div>
        </div>
    </header>

    <!-- Main Content - Chat Interface -->
    <main class="flex-grow flex flex-col">
        <div id="chat-container">
            <!-- Chat Messages Area -->
            <div id="chat-output">
                <!-- Messages will be appended here -->
                <div class="message bot-message">
                    Please enter a topic in the box below, and I will generate a Multiple Choice Question (MCQ) for you. For example: "Newton's First Law" or "Photosynthesis process".
                </div>
            </div>

             <!-- Input Area -->
             <form id="mcq-form" class="mt-auto">
                 <div id="input-area">
                     <textarea id="topic-input" rows="1" placeholder="Enter topic for MCQ..."></textarea>
                     <button id="send-button" type="submit">
                         <i class="fas fa-paper-plane mr-1"></i> Send
                     </button>
                 </div>
            </form>
        </div>
    </main>

<script>
    // --- Theme Toggle Logic (copied from index.php) ---
     const themeToggle = document.getElementById('theme-toggle');
     function applyTheme(theme) {
         if (theme === 'dark') {
             document.documentElement.classList.add('dark');
             localStorage.setItem('theme', 'dark');
         } else {
             document.documentElement.classList.remove('dark');
             localStorage.setItem('theme', 'light');
         }
         // Ensure icon visibility matches the current theme
         const moonIcon = document.querySelector("#theme-toggle .fa-moon");
         const sunIcon = document.querySelector("#theme-toggle .fa-sun");
         if (moonIcon) moonIcon.classList.toggle("hidden", theme === 'dark');
         if (sunIcon) sunIcon.classList.toggle("hidden", theme === 'light');
     }
     const storedTheme = localStorage.getItem('theme') || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
     applyTheme(storedTheme); // Apply theme on initial load
     if (themeToggle) {
         themeToggle.addEventListener('click', () => {
             const newTheme = document.documentElement.classList.contains('dark') ? 'light' : 'dark';
             applyTheme(newTheme);
         });
     }
     // --- End Theme Toggle Logic ---


     // --- MCQ Generator Chat Logic ---
    const chatOutput = document.getElementById('chat-output');
    const mcqForm = document.getElementById('mcq-form');
    const topicInput = document.getElementById('topic-input');
    const sendButton = document.getElementById('send-button');

    // Function to display messages
     function displayMessage(text, sender) { // sender: 'user' or 'bot'
         const messageDiv = document.createElement('div');
         messageDiv.classList.add('message');
         if (sender === 'user') {
             messageDiv.classList.add('user-message');
             // Simple text sanitization for user input before displaying
             const tempDiv = document.createElement('div');
             tempDiv.textContent = text;
             messageDiv.innerHTML = tempDiv.innerHTML; // Use innerHTML to render entities correctly
         } else {
              messageDiv.classList.add('bot-message');
              // Convert newlines to <br> tags for bot messages, potentially preserving formatting
              // Note: More sophisticated markdown rendering could be added here
               let formattedText = text.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>'); // Bold
               formattedText = formattedText.replace(/\n/g, '<br>');
               // Handle potential pre-formatted blocks from Gemini carefully
               formattedText = formattedText.replace(/```([\s\S]*?)```/g, (match, p1) => {
                 return '<pre>' + p1.replace(/<br\s*\/?>/g, '\n').trim() + '</pre>'; // Preserve newlines inside pre, remove surrounding breaks
               });
              messageDiv.innerHTML = formattedText; // Use innerHTML to render formatting
         }
         chatOutput.appendChild(messageDiv);
         chatOutput.scrollTop = chatOutput.scrollHeight; // Scroll to the latest message
     }

     // Auto-resize textarea
     topicInput.addEventListener('input', () => {
         topicInput.style.height = 'auto'; // Reset height
         topicInput.style.height = (topicInput.scrollHeight) + 'px'; // Set to scroll height
          if (topicInput.scrollHeight > 200) { // Max height example
              topicInput.style.overflowY = 'auto';
              topicInput.style.height = '200px';
          } else {
              topicInput.style.overflowY = 'hidden';
          }
     });

     // Handle form submission
     mcqForm.addEventListener('submit', (e) => {
        e.preventDefault(); // Prevent page reload
        const topic = topicInput.value.trim();

         if (topic) {
             displayMessage(topic, 'user'); // Display user's topic
             topicInput.value = ''; // Clear input
             topicInput.style.height = '50px'; // Reset height after sending
             topicInput.disabled = true;
             sendButton.disabled = true;
             sendButton.innerHTML = '<span class="loading-spinner"></span>'; // Show loading state

            // Send topic to the backend
             fetch('mcq_generatorbot.php', { // <--- Backend handler file
                 method: 'POST',
                 headers: {
                     'Content-Type': 'application/x-www-form-urlencoded',
                 },
                 body: 'topic=' + encodeURIComponent(topic) // Send topic as form data
             })
             .then(response => {
                 if (!response.ok) {
                      // Try to get error message from response if possible
                      return response.json().then(errData => {
                           throw new Error(errData.response || `HTTP error! Status: ${response.status}`);
                      }).catch(() => {
                           // Fallback if response wasn't JSON or JSON parsing failed
                           throw new Error(`HTTP error! Status: ${response.status}`);
                      });
                 }
                 return response.json();
             })
             .then(data => {
                 if (data && data.response) {
                      displayMessage(data.response, 'bot'); // Display bot's response (MCQ)
                 } else {
                     displayMessage('Sorry, I received an empty or invalid response.', 'bot');
                 }
             })
             .catch(error => {
                 console.error('Error generating MCQ:', error);
                 displayMessage(`Sorry, an error occurred: ${error.message}`, 'bot');
             })
             .finally(() => {
                  // Re-enable input and button
                  topicInput.disabled = false;
                  sendButton.disabled = false;
                  sendButton.innerHTML = '<i class="fas fa-paper-plane mr-1"></i> Send'; // Restore button text/icon
                 topicInput.focus();
             });
        }
     });

    // Allow Enter key to submit (Shift+Enter for newline)
    topicInput.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' && !e.shiftKey) {
             e.preventDefault();
             mcqForm.requestSubmit(); // Programmatically submit the form
        }
    });

     topicInput.focus(); // Focus on the input field on page load

</script>

</body>
</html>