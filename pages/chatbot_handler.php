<?php
// --- Optional: Session Access (if you need user context later) ---
session_start();
// $user_id = $_SESSION['user_id'] ?? null; // Example: Get user ID if needed

// --- Configuration ---
// IMPORTANT: Load your API Key securely (e.g., from environment variables or a config file)
// DO NOT HARDCODE YOUR API KEY HERE IN PRODUCTION
// $geminiApiKey = getenv('GEMINI_API_KEY');
$geminiApiKey = 'api keyyyyy'; // Replace temporarily for testing, but remove before committing/deploying

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

// --- Function to interact with Gemini (Placeholder) ---
function getGeminiResponse(string $prompt): string {
    global $geminiApiKey; // Access the API key if defined globally

    // --- !!! GEMINI API INTEGRATION WILL GO HERE !!! ---
    // You will need to:
    // 1. Install the Google AI PHP Client (e.g., using Composer: `composer require google-gemini-php/client`)
    // 2. Include the Composer autoloader: `require 'vendor/autoload.php';`
    // 3. Configure the client with your API key: `Gemini::client($geminiApiKey)`
    // 4. Create a model instance: `->geminiPro()`
    // 5. Call the generate content method: `->generateContent($prompt)`
    // 6. Extract the text response.
    // 7. Add error handling (try-catch blocks).

    // For now, return a placeholder:
    if (empty($geminiApiKey)) {
        return "Sorry, the AI service is not configured correctly (API key missing).";
    }

    // Placeholder response indicating Gemini would be called
    return "DEBUG: No predefined match found. I would normally ask Gemini about: '" . htmlspecialchars($prompt) . "'";

    // --- Example structure for actual Gemini call (requires library install) ---
    
    require 'vendor/autoload.php'; // Make sure Composer autoloader is included

    if (empty($geminiApiKey)) {
        return "AI Error: API Key not configured.";
    }

    try {
        $client = Gemini::client($geminiApiKey);
        $result = $client->geminiPro()->generateContent($prompt);
        return $result->text(); // Or handle potential lack of text response
    } catch (\Exception $e) {
        // Log the error: error_log("Gemini API Error: " . $e->getMessage());
        return "Sorry, I encountered an error trying to reach the AI. Please try again later.";
    }
    
}

// --- Main Logic ---
$response = ['response' => "Sorry, something went wrong."]; // Default error

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message'])) {
    $userMessage = trim($_POST['message']);
    $lowerUserMessage = strtolower($userMessage); // Normalize input for matching

    if (!empty($userMessage)) {
        $foundResponse = null;

        // Check for exact match in predefined responses (case-insensitive)
        if (array_key_exists($lowerUserMessage, $predefinedResponses)) {
            $foundResponse = $predefinedResponses[$lowerUserMessage];
        }
        // Optional: Add partial matching logic here if desired, similar to the JS version
        // else {
        //    foreach ($predefinedResponses as $key => $value) {
        //        if (str_contains($lowerUserMessage, $key)) { // Simple keyword check
        //            $foundResponse = $value;
        //            break;
        //        }
        //    }
        // }


        if ($foundResponse !== null) {
            // Use predefined response
            $response['response'] = $foundResponse;
        } else {
            // No predefined match, call Gemini (or the placeholder)
            $response['response'] = getGeminiResponse($userMessage);
        }
    } else {
        $response['response'] = "Please enter a message.";
    }
} else {
     $response['response'] = "Invalid request method or missing message.";
}

// --- Output ---
header('Content-Type: application/json');
echo json_encode($response);
exit; // Stop script execution

?>