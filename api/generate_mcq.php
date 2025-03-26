<?php
session_start();
include '../includes/db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $topic = $_POST['topic'];
    $difficulty = $_POST['difficulty'];

    // Call AI API here (e.g., OpenAI, Gemini, etc.)
    $generated_mcqs = "Sample Question 1?\nA) Option 1\nB) Option 2\nC) Option 3\nD) Option 4";

    echo json_encode(["mcqs" => $generated_mcqs]);
}
?>
