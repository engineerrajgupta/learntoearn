<?php
 // --- Authentication and Session Management ---
 session_start(); // Must be the very first thing

 // Check if the user is logged in, otherwise redirect to login page
 if (!isset($_SESSION['user_id'])) {
     header("Location: /learntoearn/index.php"); // Adjust if your login path is different
     exit; // Stop script execution
 }

 // Get user information from session
 $user_id = $_SESSION['user_id'];
 $username = isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'User';

 // --- End Authentication ---
?>
<!DOCTYPE html>
<html lang="en" class=""> 
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Assistant - LearnToEarn</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
      tailwind.config = {
        darkMode: 'class',
        theme: {
          extend: {
            // Optional: Add custom scrollbar styling if desired
            // scrollbar: ['rounded']
          }
        }
      }
       // Apply theme right away before rendering body
       const storedTheme = localStorage.getItem('theme') || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
       if (storedTheme === 'dark') {
           document.documentElement.classList.add('dark');
       } else {
           document.documentElement.classList.remove('dark');
       }
    </script>

    <style>
        /* Custom scrollbar for webkit browsers (optional) */
        ::-webkit-scrollbar {
            width: 8px;
        }
        ::-webkit-scrollbar-track {
            background: transparent; /* Or a very subtle color */
        }
        ::-webkit-scrollbar-thumb {
            background-color: rgba(156, 163, 175, 0.5); /* gray-400 with opacity */
            border-radius: 4px;
            border: 2px solid transparent; /* Creates padding around thumb */
            background-clip: content-box;
        }
        .dark ::-webkit-scrollbar-thumb {
            background-color: rgba(107, 114, 128, 0.5); /* gray-500 with opacity */
        }
        ::-webkit-scrollbar-thumb:hover {
            background-color: rgba(156, 163, 175, 0.7);
        }
        .dark ::-webkit-scrollbar-thumb:hover {
            background-color: rgba(107, 114, 128, 0.7);
        }

        /* Chat message styling (can reuse much from previous CSS) */
        .message {
            padding: 10px 15px; /* Slightly more padding */
            margin-bottom: 12px;
            border-radius: 10px; /* More rounded */
            max-width: 80%; /* Max width relative to container */
            word-wrap: break-word;
            line-height: 1.5;
            box-shadow: 0 1px 2px rgba(0,0,0,0.07);
        }
        /* Ensure message container allows messages to align correctly */
        #chat-messages {
            display: flex;
            flex-direction: column;
            gap: 5px; /* Small gap between messages */
        }

        .user-message {
            background-color: #dbeafe; /* Tailwind blue-100 */
            color: #1e3a8a; /* Tailwind blue-900 */
            align-self: flex-end; /* Pushes to the right */
            margin-left: auto; /* Works with align-self */
        }
         .dark .user-message {
             background-color: #312e81; /* Tailwind indigo-900 */
             color: #e0e7ff; /* Tailwind indigo-100 */
         }

        .bot-message {
            background-color: #f3f4f6; /* Tailwind gray-100 */
            color: #1f2937; /* Tailwind gray-800 */
            align-self: flex-start; /* Pushes to the left */
            margin-right: auto; /* Works with align-self */
        }
         .dark .bot-message {
             background-color: #374151; /* Tailwind gray-700 */
             color: #f3f4f6; /* Tailwind gray-100 */
         }

        /* Code block styling */
        .bot-message pre {
             background-color: #e5e7eb; /* Lighter than bot message bg */
             padding: 12px;
             border-radius: 6px;
             overflow-x: auto;
             margin-top: 8px;
             margin-bottom: 5px;
             font-family: 'Courier New', Courier, monospace; /* Classic monospace */
             font-size: 0.9em;
             border: 1px solid #d1d5db;
             box-shadow: inset 0 1px 2px rgba(0,0,0,0.05);
        }
        .dark .bot-message pre {
             background-color: #1f2937; /* Darker than bot message bg */
             color: #d1d5db;
             border: 1px solid #4b5563;
             box-shadow: inset 0 1px 2px rgba(255,255,255,0.05);
        }
         .bot-message pre code {
            background: none; /* Remove background from inline code style if any */
            padding: 0;
            border-radius: 0;
            font-size: inherit; /* Inherit size from pre */
         }

        .bot-message code:not(pre code) { /* Inline code */
            background-color: rgba(0,0,0,0.06);
            padding: 2px 5px;
            border-radius: 4px;
            font-size: 0.9em;
            color: #b91c1c; /* dark red */
        }
        .dark .bot-message code:not(pre code) {
            background-color: rgba(255,255,255,0.1);
            color: #fda4af; /* Light red */
        }
        /* List styling */
        .bot-message ul, .bot-message ol {
            margin-left: 25px; /* Indent lists more */
            padding-left: 5px;
            margin-top: 8px;
            margin-bottom: 8px;
        }
        .bot-message li {
            margin-bottom: 5px;
        }
        .bot-message strong {
            font-weight: 600; /* Tailwind semibold */
        }
        .bot-message em {
            font-style: italic;
        }

        /* Input area styling */
        #chat-input-area {
            flex-shrink: 0; /* Prevent shrinking */
            position: relative; /* For potential absolute elements inside like send button */
        }
         /* Style for the input itself */
         #message-input {
             resize: none; /* Prevent manual resize if using textarea */
             height: 52px; /* Initial height */
             max-height: 200px; /* Max height before scroll */
             padding-right: 50px; /* Space for send button */
         }
         #send-button {
            position: absolute;
            right: 1rem; /* Adjust based on padding of container */
            bottom: 0.75rem; /* Adjust based on padding of container */
            height: 36px; /* Match input visual height */
            width: 36px;
            padding: 0;
            display: flex;
            align-items: center;
            justify-content: center;
         }
         #send-button i {
             font-size: 1rem; /* Adjust icon size */
         }


    </style>
</head>
<body class="bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 flex flex-col h-screen overflow-hidden">

    <!-- Header (Minimal) -->
    <header class="bg-gray-50 dark:bg-gray-900 shadow-sm border-b border-gray-200 dark:border-gray-700 px-4 py-2 flex items-center justify-between flex-shrink-0 z-10">
       <a href="dashboard.php"> <h1 class="text-lg font-semibold text-indigo-600 dark:text-indigo-400">LearnToEarn</h1> </a>
        <div class="flex items-center space-x-3">
            <span class="text-sm text-gray-600 dark:text-gray-400 hidden sm:inline"><?php echo $username; ?></span>
            <button id="theme-toggle" title="Toggle dark mode" class="p-2 rounded-full text-gray-500 dark:text-gray-400 hover:bg-gray-200 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-900">
                <i class="fas fa-moon text-lg dark:hidden"></i>
                <i class="fas fa-sun text-lg hidden dark:block text-yellow-400"></i>
            </button>
            <a href="/learntoearn/pages/logout.php" title="Logout" class="p-2 rounded-full text-gray-500 dark:text-gray-400 hover:text-red-500 dark:hover:text-red-400 hover:bg-gray-200 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 dark:focus:ring-offset-gray-900">
                <i class="fas fa-sign-out-alt text-lg"></i>
            </a>
        </div>
    </header>

    <!-- Chat Messages Area -->
    <main id="chat-messages-container" class="flex-grow overflow-y-auto p-4 md:p-6">
        <!-- Messages will be appended here by JS --> 
        <div id="chat-messages" class="max-w-4xl mx-auto w-full"> 
        </div>
    </main>

    <!-- Input Area -->
    <footer id="chat-input-area" class="bg-gray-100 dark:bg-gray-900 border-t border-gray-200 dark:border-gray-700 p-3 md:p-4 flex-shrink-0">
        <div class="max-w-4xl mx-auto w-full relative"> 
            <textarea id="message-input"
                   class="w-full p-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition duration-150 ease-in-out"
                   placeholder="Ask me anything..."
                   rows="1"
                   autocomplete="off"></textarea>
             <button id="send-button"
                     class="absolute right-4 bottom-3 p-2 rounded-md bg-indigo-600 hover:bg-indigo-700 text-white disabled:opacity-50 disabled:cursor-not-allowed focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-900"
                     title="Send Message"
                     disabled>
                <i class="fas fa-paper-plane"></i>
            </button>
        </div>
    </footer>


    <!-- SCRIPTS -->
    <script>
        // --- Theme Toggle Logic ---
        const themeToggle = document.getElementById('theme-toggle');
        const htmlElement = document.documentElement;

        function applyTheme(theme) {
            if (theme === 'dark') {
                htmlElement.classList.add('dark');
                localStorage.setItem('theme', 'dark');
            } else {
                htmlElement.classList.remove('dark');
                localStorage.setItem('theme', 'light');
            }
            updateThemeIcon();
        }

        function updateThemeIcon() {
            const isDark = htmlElement.classList.contains('dark');
            const moonIcon = themeToggle?.querySelector(".fa-moon");
            const sunIcon = themeToggle?.querySelector(".fa-sun");
            if (moonIcon) moonIcon.classList.toggle("dark:hidden", !isDark); // Control via dark:hidden
            if (sunIcon) sunIcon.classList.toggle("hidden", !isDark);
            if (sunIcon) sunIcon.classList.toggle("dark:block", isDark); // Control via dark:block
        }

        // Apply theme already done via inline script in <head>

        // Toggle theme when button is clicked
        if (themeToggle) {
            themeToggle.addEventListener('click', () => {
                const newTheme = htmlElement.classList.contains('dark') ? 'light' : 'dark';
                applyTheme(newTheme);
            });
            updateThemeIcon(); // Ensure icon is correct on initial load
        }


        // --- CHATBOT JAVASCRIPT START ---
        const chatMessagesContainer = document.getElementById('chat-messages-container'); // The scrollable element
        const chatMessages = document.getElementById('chat-messages');         // Where messages are appended
        const messageInput = document.getElementById('message-input');       // Textarea input
        const sendButton = document.getElementById('send-button');           // Send button

        // Ensure chatbot elements exist
        if (chatMessagesContainer && chatMessages && messageInput && sendButton) {

            // Function to sanitize HTML (basic) - IMPORTANT for security if rendering HTML from bot
             function sanitizeHTML(str) {
                const temp = document.createElement('div');
                temp.textContent = str;
                return temp.innerHTML;
             }

             // Function to add messages to the chat window
             function addMessage(text, isUser, isHtml = false) {
                const messageDiv = document.createElement('div');
                messageDiv.classList.add('message', isUser ? 'user-message' : 'bot-message');

                if (isUser) {
                    messageDiv.textContent = text; // Always use textContent for user messages
                } else {
                     if (isHtml) {
                        // Basic Markdown-like formatting handled here before setting innerHTML
                        text = text.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
                        text = text.replace(/`([^`]+?)`/g, (match, code) => `<code>${sanitizeHTML(code)}</code>`); // Inline code first
                        text = text.replace(/```([\s\S]*?)```/gs, (match, code) => {
                             // Preserve indentation within code blocks roughly
                             const cleanedCode = sanitizeHTML(code.trim('\n')); // Basic sanitize + trim leading/trailing newlines
                             return `<pre>${cleanedCode}</pre>`; // Note: No <code> wrapper here, style <pre> directly
                         });
                        text = text.replace(/^\s*[\*\-]\s+(.*)/gm, '<li>$1</li>');
                        text = text.replace(/^(<li>.*?<\/li>\s*)+/gm, (match) => `<ul>${match}</ul>`); // Wrap consecutive LIs in UL
                         // Add basic numbered list support (simple)
                         text = text.replace(/^\s*\d+\.\s+(.*)/gm, '<li>$1</li>'); // Treat as <li> for simplicity, use CSS counters if needed
                         text = text.replace(/^(<li>.*?<\/li>\s*)+/gm, (match) => { // Check if it was already wrapped
                            if (match.startsWith('<ul>')) return match;
                            // Basic check if numbers were likely intended (could be improved)
                            if (/^\s*<li>\d+\./.test(match)) { // Heuristic: if first item starts like "1."
                                return `<ol>${match}</ol>`
                            }
                            return `<ul>${match}</ul>`; // Default to UL
                         });

                        // Convert newlines to <br> AFTER other formatting, avoiding inside PRE
                        text = text.replace(/<pre>[\s\S]*?<\/pre>|(\n)/gs, (match, newline) => {
                            return newline ? '<br>' : match;
                        });
                        // Remove leading/trailing <br> potentially added around blocks
                        text = text.replace(/^<br\s*\/?>|<br\s*\/?>$/g, '');

                        messageDiv.innerHTML = text; // Use innerHTML for bot messages containing formatted HTML
                     } else {
                         messageDiv.textContent = text; // Fallback if no HTML formatting needed
                     }
                }

                chatMessages.appendChild(messageDiv);
                // Scroll to the bottom of the container
                // Use requestAnimationFrame for potentially smoother scroll after render
                 requestAnimationFrame(() => {
                    chatMessagesContainer.scrollTo({ top: chatMessagesContainer.scrollHeight, behavior: 'smooth' });
                 });
             }

            // Function to handle sending a message
            function sendMessage() {
    const message = messageInput.value.trim();
    if (message) {
        addMessage(message, true); // Show user message
        messageInput.value = '';
        messageInput.style.height = 'auto';
        sendButton.disabled = true;
        messageInput.disabled = true;

        // Add "Thinking..." indicator
        const thinkingDiv = document.createElement('div');
        thinkingDiv.classList.add('message', 'bot-message', 'italic', 'text-gray-500', 'dark:text-gray-400');
        thinkingDiv.textContent = 'Thinking...';
        thinkingDiv.id = 'thinking-indicator';
        chatMessages.appendChild(thinkingDiv);
        chatMessagesContainer.scrollTo({ top: chatMessagesContainer.scrollHeight, behavior: 'smooth' });

        // ✅ Prepend MCQ generation instruction
        const finalPrompt = "Generate 20 MCQs on this topic: " + message;

        fetch('chatbot_handler.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: 'message=' + encodeURIComponent(finalPrompt)
        })
        .then(response => {
            document.getElementById('thinking-indicator')?.remove();
            if (!response.ok) {
                return response.text().then(text => {
                    throw new Error(`HTTP error ${response.status}: ${text || 'Server error'}`);
                });
            }
            return response.json();
        })
        .then(data => {
            if (data && data.response) {
                addMessage(data.response, false, true); // Show bot response with HTML formatting
            } else {
                addMessage("Sorry, I received an empty or invalid response.", false);
            }
        })
        .catch(error => {
            document.getElementById('thinking-indicator')?.remove();
            console.error('Chatbot fetch error:', error);
            addMessage(`Sorry, there was an error: ${error.message}. Please try again later.`, false);
        })
        .finally(() => {
            messageInput.disabled = false;
            messageInput.focus();
            adjustTextareaHeight();
        });
    }
}


            // Auto-adjust textarea height
             function adjustTextareaHeight() {
                messageInput.style.height = 'auto'; // Temporarily shrink
                let scrollHeight = messageInput.scrollHeight;
                const maxHeight = parseInt(window.getComputedStyle(messageInput).maxHeight, 10) || 200; // Get max-height from CSS

                if (scrollHeight > maxHeight) {
                    messageInput.style.height = `${maxHeight}px`;
                    messageInput.style.overflowY = 'auto'; // Show scrollbar if needed
                } else {
                    messageInput.style.height = `${scrollHeight}px`;
                    messageInput.style.overflowY = 'hidden'; // Hide scrollbar
                }
             }

            // --- Event Listeners ---
            sendButton.addEventListener('click', sendMessage);

            messageInput.addEventListener('input', () => {
                 adjustTextareaHeight();
                 // Enable/disable send button based on input content
                 sendButton.disabled = messageInput.value.trim().length === 0;
            });

            messageInput.addEventListener('keydown', (event) => {
                // Send on Enter key, but allow Shift+Enter for newlines
                if (event.key === 'Enter' && !event.shiftKey) {
                    event.preventDefault(); // Prevent default newline insertion
                    if (!sendButton.disabled) { // Only send if button is enabled
                        sendMessage();
                    }
                }
            });

             // Initial Greeting Message on load
             window.addEventListener('load', () => {
                 setTimeout(() => {
                     addMessage("Hello! 👋 I'm your LearnToEarn AI MCQ generator. just type your topic here.. ", false, true);
                     messageInput.focus(); // Focus input after greeting
                     sendButton.disabled = true; // Ensure button starts disabled
                 }, 300); // Slight delay
                 adjustTextareaHeight(); // Initial height adjustment
             });

        } else {
            console.error("Chatbot HTML elements not found. Chatbot functionality disabled.");
        }
        // --- CHATBOT JAVASCRIPT END ---

    </script>

</body>
</html>