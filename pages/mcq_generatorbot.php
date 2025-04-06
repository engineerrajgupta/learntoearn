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
    http_response_code(500); // Internal Server Error is appropriate here
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
    "give me a quick revision on recursion" => "Okay, here’s a quick revision on **Recursion**:\n\n*   Recursion is when a function calls itself to solve a smaller version of the problem.\n*   It needs a **base case** to stop the calls and prevent infinite loops.\n*   Think of it like Russian nesting dolls, each doll contains a smaller one until the smallest.\n*   Common examples: Factorial (`n!`), Fibonacci sequence, tree traversals (like in data structures).\n*   Uses the call stack for memory, which can lead to a 'Stack Overflow' error if the recursion is too deep.\n\nWant a code example or more details on a specific part? Let me know! 🚀"
    // Note: We remove the specific "generate mcq on operating system(s)" from predefined
    // because we now handle it dynamically below.
];


// --- Function to interact with Gemini using cURL ---
// This function remains the same as the last working version
function askGemini(string $message): string {
    global $geminiApiKey; // Access the globally defined API key
    $logPrefix = "[askGemini Generic] "; // Adjusted log prefix for clarity

    if (empty($geminiApiKey)) {
        error_log($logPrefix."Error: API Key missing inside function.");
        return "Internal Configuration Error: API Key missing.";
    }
    if (!function_exists('curl_init')) {
        error_log($logPrefix."FATAL ERROR: PHP cURL extension is not installed or enabled.");
        return "Server Configuration Error: Required cURL library not found.";
    }

    $model = 'gemini-1.5-flash'; // Or 'gemini-pro'
    $url = 'https://generativelanguage.googleapis.com/v1beta/models/' . $model . ':generateContent?key=' . $geminiApiKey;

    $data = ['contents' => [['parts' => [['text' => $message]]]]];
    $jsonData = json_encode($data);
    if ($jsonData === false) {
         error_log($logPrefix."Error encoding JSON request data: " . json_last_error_msg());
         return "Internal Error: Failed preparing request data.";
    }
     error_log($logPrefix . "Payload: " . $jsonData);

    $headers = ['Content-Type: application/json'];
    $ch = curl_init();
    if ($ch === false) { error_log($logPrefix."Error: Failed to initialize cURL handle."); return "Internal Error: Could not initialize connection library."; }

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15);
    curl_setopt($ch, CURLOPT_TIMEOUT, 45);

    $apiResponse = curl_exec($ch);
    $curlError = curl_error($ch);
    $curlErrno = curl_errno($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

     error_log($logPrefix . "cURL finished. Errno: $curlErrno, HTTP Code: $httpCode");

    // Handle cURL & HTTP Errors
    if ($curlErrno !== 0) {
        error_log($logPrefix."cURL Error ($curlErrno): $curlError");
        // Avoid setting HTTP code here as this function might be used internally
        return "Connection Error: Could not reach AI service (cURL Errno: $curlErrno). Please try again later.";
    }
    if ($httpCode >= 400) {
        error_log($logPrefix."Gemini API HTTP Error: Status=$httpCode. Response: " . $apiResponse);
        $errorMsg = "API Error (HTTP $httpCode).";
        $responseData = json_decode($apiResponse, true);
        if (isset($responseData['error']['message'])) { $errorMsg .= " Message: " . htmlspecialchars($responseData['error']['message']); }
        // Don't set HTTP code here either, let the main logic handle it if needed
        return $errorMsg;
    }
    if ($apiResponse === false || $apiResponse === '') {
         error_log($logPrefix."Error: Empty response body despite HTTP $httpCode.");
         return "API Error: Received an empty response from the AI service.";
    }

    // Process Successful Response
    $result = json_decode($apiResponse, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log($logPrefix."Error decoding JSON response: " . json_last_error_msg() . ". Raw: " . $apiResponse);
        return "API Error: Invalid response format received from AI.";
    }
    $text = $result['candidates'][0]['content']['parts'][0]['text'] ?? null;
    if ($text !== null) { return trim($text); }
    else {
        error_log($logPrefix."Could not extract text from response. Structure: " . print_r($result, true));
        $finishReason = $result['candidates'][0]['finishReason'] ?? 'Unknown';
        if ($finishReason === 'SAFETY') { return "Content Moderation: Response blocked due to safety filters."; }
        if(isset($result['error']['message'])) { return "API Error in Response: " . htmlspecialchars($result['error']['message']); }
        return "API Error: Unexpected response structure (text missing, Reason: $finishReason).";
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
                $response['response'] = $foundResponse;
                 error_log("[Chatbot] Found predefined response for: '$lowerUserMessage'");
            } else {
                // 2. No predefined match - check if it's an MCQ request
                error_log("[Chatbot] No predefined response for: '$userMessage'. Checking for MCQ request.");
                $topic = null;
                $promptToSend = $userMessage; // Default prompt is the raw user message

                // Define trigger phrases (lowercase) - order potentially matters if one is a substring of another
                $mcqTriggers = [
                    "generate an mcq on ",
                    "generate a mcq on ",
                    "generate mcq on ",
                    "create an mcq about ",
                    "create a mcq about ",
                    "create mcq about ",
                    "mcq about ",
                    "mcq for ",
                    "mcq on ",
                    "quiz me on " // Add more variations if needed
                ];

                 // Check if the message starts with any trigger (case-insensitive)
                 foreach ($mcqTriggers as $trigger) {
                     // Use strncasecmp for case-insensitive comparison of the start of the string
                     if (strncasecmp($lowerUserMessage, $trigger, strlen($trigger)) === 0) {
                         // Found a trigger. Extract topic using the original case user message
                         $topic = trim(substr($userMessage, strlen($trigger)));
                         error_log("[Chatbot] Detected MCQ trigger: '$trigger'. Extracted topic: '$topic'");
                         break; // Use the first matching trigger
                     }
                 }


                 // If a topic was extracted AND it's not empty, engineer the prompt
                if ($topic !== null && !empty($topic)) {
                     error_log("[Chatbot] Constructing engineered prompt for topic: '$topic'");

                      // *** The Engineered Prompt for MCQ Generation ***
                      $engineeredPrompt = "Generate one clear multiple-choice question (MCQ) based on the following topic:\n\n"
                                        . "Topic: \"" . htmlspecialchars($topic) . "\"\n\n" // htmlspecialchars is good practice here
                                        . "Instructions:\n"
                                        . "- Create a relevant question about the core concept of the topic.\n"
                                        . "- Provide 4 distinct options labeled A, B, C, D.\n"
                                        . "- Ensure only ONE option is clearly the correct answer.\n"
                                        . "- Make the other options plausible but incorrect distractors.\n"
                                        . "- Clearly indicate the CORRECT ANSWER (e.g., 'Correct Answer: B').\n"
                                        . "- Provide a brief EXPLANATION for why the correct answer is right (e.g., 'Explanation: ...').\n\n"
                                        . "Format the output cleanly with clear separation between question, options, answer, and explanation.";

                     $promptToSend = $engineeredPrompt; // Use the engineered prompt
                } else {
                     // Not identified as an MCQ request with a valid topic, proceed with original message
                     error_log("[Chatbot] Not an MCQ request or topic empty. Sending original message to AI.");
                     // $promptToSend remains $userMessage (the default)
                }


                // 3. Call the Gemini API via cURL using the determined prompt ($promptToSend)
                $apiResponse = askGemini($promptToSend);
                 // Check if the response indicates an error that should return a specific HTTP code
                 // Basic check: Look for "API Error" or "Configuration Error" at the start
                if (str_starts_with($apiResponse, "API Error") || str_starts_with($apiResponse, "Connection Error") || str_starts_with($apiResponse, "Internal Error") || str_starts_with($apiResponse, "Server Configuration Error")) {
                    http_response_code(503); // Service Unavailable or specific code if derivable
                 } elseif (str_starts_with($apiResponse, "Content Moderation")) {
                      http_response_code(400); // Bad Request due to safety
                 }
                 $response['response'] = $apiResponse;

            } // End of 'else' (no predefined match)

        } else {
            error_log("[Chatbot] Received empty message after trimming.");
            http_response_code(400); // Bad Request
            $response['response'] = "Please enter a message."; // Handle empty input
        }
    } else {
         error_log("[Chatbot] Error: Missing 'message' parameter in POST data.");
         http_response_code(400); // Bad Request
         $response['response'] = "Error: Missing 'message' parameter in POST request."; // Missing data
    }
} else {
     error_log("[Chatbot] Error: Invalid request method - " . $_SERVER['REQUEST_METHOD']);
     http_response_code(405); // Method Not Allowed
     $response['response'] = "Error: Invalid request method. Only POST is accepted."; // Wrong method
}

// --- Output ---
// Ensure no accidental output before the header
if (!headers_sent()) {
    header('Content-Type: application/json');
} else {
    // Log if headers were already sent (indicates an earlier error or output)
    error_log("[Chatbot] Warning: Headers already sent before outputting JSON response.");
}

echo json_encode($response);
exit; // Stop script execution cleanly
?>