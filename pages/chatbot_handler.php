<?php
// --- Optional: Session Access (if you need user context later) ---
// session_start(); // Uncomment ONLY if you actually use $_SESSION variables later
// $user_id = $_SESSION['user_id'] ?? null; // Example: Get user ID if needed

// --- Configuration ---
// IMPORTANT: Load your API Key securely in production (e.g., from environment variables)
// DO NOT HARDCODE YOUR API KEY IN PRODUCTION CODE OR COMMIT IT TO GIT
$geminiApiKey = getenv('GEMINI_API_KEY'); // Try to get from environment first
if (empty($geminiApiKey)) {
    // Fallback for local testing ONLY - REPLACE WITH YOUR ACTUAL KEY
    $geminiApiKey = 'AIzaSyCG6coSGkxY0Ming4eEZXFa57Eft8uX0W0'; // <--- PUT YOUR REAL TEST KEY HERE
    // You might want to log a warning if the fallback is used
    // error_log("Warning: Using hardcoded fallback Gemini API Key for testing.");
}

// **Critical Check: Ensure API Key is set**
if (empty($geminiApiKey)) {
    // Log the error server-side
    error_log("FATAL ERROR: Gemini API Key is missing.");
    // Send a JSON error response
    header('Content-Type: application/json');
    echo json_encode(['response' => 'Configuration Error: API Key is missing.']);
    exit;
}


// --- Predefined Responses (Case-Insensitive Matching) ---
// Store keys in lowercase for easier comparison
$predefinedResponses = [
    "hi" => "Hey there! 👋 I'm LearnToEarn AI, your personal study companion. Need help with coding, quick revisions, or tackling tough subjects? Just ask, and I’ll do my best to assist you!",
    "hello" => "Hey there! 👋 I'm LearnToEarn AI, your personal study companion. Need help with coding, quick revisions, or tackling tough subjects? Just ask, and I’ll do my best to assist you!",
    "hlw" => "Hey there! 👋 I'm LearnToEarn AI, your personal study companion. Need help with coding, quick revisions, or tackling tough subjects? Just ask, and I’ll do my best to assist you!",
    "hlww" => "Hey there! 👋 I'm LearnToEarn AI, your personal study companion. Need help with coding, quick revisions, or tackling tough subjects? Just ask, and I’ll do my best to assist you!",
    "who is your creator" => "I was created by the LearnToEarn team to help students like you!",
    "tell me a fun fact" => "Sure! Did you know that the first computer programmer was a woman named Ada Lovelace? 👩‍💻",
    "who are you?" => "Yo! 🚀 I’m LearnToEarn AI, your study assistant! Struggling with a subject? I got your back! Let’s crush those doubts together!",
    "give me a quick revision on recursion" => "Okay, here’s a quick revision on **Recursion**:\n\n*   Recursion is when a function calls itself to solve a smaller version of the problem.\n*   It needs a **base case** to stop the calls and prevent infinite loops.\n*   Think of it like Russian nesting dolls, each doll contains a smaller one until the smallest.\n*   Common examples: Factorial (`n!`), Fibonacci sequence, tree traversals (like in data structures).\n*   Uses the call stack for memory, which can lead to a 'Stack Overflow' error if the recursion is too deep.\n\nWant a code example or more details on a specific part? Let me know! 🚀",
    "generate an mcq on operating systems" => "**OS MCQ Question:**\n\nWhich memory management technique allows processes to be moved between main memory and disk during execution?\n\n**A.** Paging\n**B.** Swapping\n**C.** Segmentation\n**D.** Fragmentation\n\n✅ **Correct Answer:** B. Swapping\n\n**Explanation:** Swapping is the process of bringing in and out of processes from disk to main memory for execution.",
    "generate an mcq on operating system" => "**OS MCQ Question:**\n\nWhat is the main purpose of an Operating System?\n\n**A.** To run applications\n**B.** To manage hardware resources\n**C.** To provide a user interface\n**D.** All of the above\n\n✅ **Correct Answer:** D. All of the above\n\n**Explanation:** An OS manages hardware, provides a platform for applications, and offers a way for users to interact with the computer."
    // Add more predefined Q&A pairs here...
];


// --- Function to interact with Gemini using cURL ---
// This function replaces the old getGeminiResponse
function askGemini(string $message): string {
    global $geminiApiKey; // Access the globally defined API key

    // API key check is done globally now, but good practice to keep a check here too
    if (empty($geminiApiKey)) {
        error_log("[askGemini] Error: API Key missing inside function."); // Log server-side
        return "Internal Configuration Error: API Key missing."; // User message
    }

    // Check if cURL extension is available
    if (!function_exists('curl_init')) {
        error_log("[askGemini] FATAL ERROR: PHP cURL extension is not installed or enabled.");
        return "Server Configuration Error: Required cURL library not found.";
    }

    // --- Prepare API Request ---
    // Verify the model name ('gemini-1.5-flash' is common, 'gemini-pro' is another option)
    $model = 'gemini-1.5-flash';
    $url = 'https://generativelanguage.googleapis.com/v1beta/models/' . $model . ':generateContent?key=' . $geminiApiKey;

    $data = [
        'contents' => [
            ['parts' => [['text' => $message]]]
        ]
        // Optional: Add safety settings or generation config here if needed
        // 'safetySettings' => [ ... ],
        // 'generationConfig' => [ 'temperature' => 0.7, ... ],
    ];

    $jsonData = json_encode($data);
    if ($jsonData === false) {
         error_log("[askGemini] Error encoding JSON request data: " . json_last_error_msg());
         return "Internal Error: Failed preparing request data.";
    }

    $headers = [
        'Content-Type: application/json'
    ];

    // --- Initialize and Execute cURL ---
    $ch = curl_init();
    if ($ch === false) {
        error_log("[askGemini] Error: Failed to initialize cURL handle.");
        return "Internal Error: Could not initialize connection library.";
    }

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15); // Connection timeout (seconds)
    curl_setopt($ch, CURLOPT_TIMEOUT, 45);      // Total request timeout (seconds)
    // curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Uncomment TEMPORARILY for local testing ONLY if you have SSL certificate issues

    $apiResponse = curl_exec($ch);
    $curlError = curl_error($ch);
    $curlErrno = curl_errno($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    // --- Handle cURL & HTTP Errors ---
    if ($curlErrno !== 0) {
        error_log("[askGemini] cURL Error ($curlErrno) calling Gemini: $curlError");
        return "Connection Error: Could not reach the AI service (cURL Errno: $curlErrno). Please check server connectivity or try again later.";
    }

    if ($httpCode >= 400) {
        error_log("[askGemini] Gemini API HTTP Error: Status=$httpCode. Response: " . $apiResponse);
        $errorMsg = "API Error (HTTP $httpCode).";
        // Try to get more specific error from response body
        $responseData = json_decode($apiResponse, true);
        if (isset($responseData['error']['message'])) {
             $errorMsg .= " Message: " . htmlspecialchars($responseData['error']['message']);
        }
        // Specific common errors
        if ($httpCode == 400) $errorMsg = "API Error: Bad Request (400). Check input or model compatibility. " . ($responseData['error']['message'] ?? '');
        if ($httpCode == 401 || $httpCode == 403) $errorMsg = "API Error: Authentication Failed ($httpCode). Please check your API Key.";
        if ($httpCode == 429) $errorMsg = "API Error: Rate Limit Exceeded (429). Please try again later.";
        if ($httpCode >= 500) $errorMsg = "API Error: Gemini service unavailable or internal error ($httpCode). Please try again later.";
        return $errorMsg; // Return the specific or generic HTTP error
    }

    if ($apiResponse === false || $apiResponse === '') {
         error_log("[askGemini] Error: Empty or false response received despite HTTP $httpCode.");
         return "API Error: Received an empty response from the AI service.";
    }

    // --- Process Successful Response ---
    $result = json_decode($apiResponse, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log("[askGemini] Error decoding JSON response: " . json_last_error_msg() . ". Raw Response: " . $apiResponse);
        return "API Error: Invalid response format received from AI.";
    }

    // Safely extract the text
    $text = $result['candidates'][0]['content']['parts'][0]['text'] ?? null;

    if ($text !== null) {
        return $text; // Success!
    } else {
        // Log why text might be missing
        error_log("[askGemini] Could not extract text from response. Structure: " . print_r($result, true));
        $finishReason = $result['candidates'][0]['finishReason'] ?? null;
        if ($finishReason === 'SAFETY') {
             return "Content Moderation: The response was blocked due to safety filters.";
        }
        // Check for embedded error messages
        if(isset($result['error']['message'])) {
             return "API Error in Response: " . htmlspecialchars($result['error']['message']);
        }
        return "API Error: Unexpected response structure from AI (text missing).";
    }
}


// --- Main Logic ---
$response = ['response' => "Error: Request could not be processed."]; // Default error

// Check request method and presence of 'message' parameter
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['message'])) {
        $userMessage = trim($_POST['message']);

        if (!empty($userMessage)) {
            $lowerUserMessage = strtolower($userMessage); // Normalize for matching
            $foundResponse = null;

            // 1. Check predefined responses (case-insensitive)
            if (array_key_exists($lowerUserMessage, $predefinedResponses)) {
                $foundResponse = $predefinedResponses[$lowerUserMessage];
            }
            // Optional: Add partial matching here if needed
            // else { ... }

            // 2. Determine final response
            if ($foundResponse !== null) {
                // Use predefined response
                $response['response'] = $foundResponse;
            } else {
                // No predefined match, call the Gemini API via cURL
                $response['response'] = askGemini($userMessage); // <--- Use the new function
            }
        } else {
            $response['response'] = "Please enter a message."; // Handle empty input
        }
    } else {
         $response['response'] = "Error: Missing 'message' parameter in POST request."; // Missing data
    }
} else {
     $response['response'] = "Error: Invalid request method. Only POST is accepted."; // Wrong method
}

// --- Output ---
// Ensure no accidental output before the header
if (!headers_sent()) {
    header('Content-Type: application/json');
} else {
    // Log if headers were already sent (indicates an earlier error or output)
    error_log("Warning: Headers already sent before outputting JSON response.");
}

echo json_encode($response);
exit; // Stop script execution cleanly
?>