<?php
session_start();
// Adjust path to your actual database connection script
require_once '../includes/db.php'; // <--- IMPORTANT: Update this path

// --- Security Checks ---
if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'teacher') {
    http_response_code(403); // Forbidden
    echo json_encode(['success' => false, 'message' => 'Unauthorized access.']);
    exit;
}
$teacher_id = $_SESSION['user_id'];

// --- Get Action and Data ---
$action = $_POST['action'] ?? $_GET['action'] ?? null;
$response = ['success' => false, 'message' => 'Invalid action specified.']; // Default response

if (!$pdo) {
     http_response_code(500);
     echo json_encode(['success' => false, 'message' => 'Database connection error.']);
     exit;
}

// --- Action Handling ---
try {
    switch ($action) {
        case 'get_or_create_syllabus_and_topics':
            // (Keep this case exactly as in the previous answer)
            if (isset($_GET['subject_id'])) {
                $subject_id = filter_input(INPUT_GET, 'subject_id', FILTER_VALIDATE_INT);
                if (!$subject_id) { $response['message'] = 'Invalid Subject ID.'; break; }

                $stmtCheck = $pdo->prepare("SELECT syllabus_id FROM syllabi WHERE subject_id = ? AND teacher_id = ?");
                $stmtCheck->execute([$subject_id, $teacher_id]);
                $syllabus_id = $stmtCheck->fetchColumn();

                if (!$syllabus_id) {
                    $stmtCreate = $pdo->prepare("INSERT INTO syllabi (subject_id, teacher_id, title) VALUES (?, ?, ?)");
                    $default_title = "Syllabus for Subject " . $subject_id;
                    if ($stmtCreate->execute([$subject_id, $teacher_id, $default_title])) {
                        $syllabus_id = $pdo->lastInsertId();
                    } else { $response['message'] = 'Failed to automatically create syllabus.'; break; }
                }

                $stmtTopics = $pdo->prepare("SELECT * FROM syllabus_topics WHERE syllabus_id = ? ORDER BY order_index ASC, topic_id ASC");
                $stmtTopics->execute([$syllabus_id]);
                $topics = $stmtTopics->fetchAll(PDO::FETCH_ASSOC);
                $response = ['success' => true, 'syllabus_id' => $syllabus_id, 'topics' => $topics];
            } else { $response['message'] = 'Subject ID not provided.'; }
            break;

        case 'add_topic':
             // (Keep this case exactly as in the previous answer)
            if (isset($_POST['syllabus_id'], $_POST['topic_name'])) {
                $syllabus_id = filter_input(INPUT_POST, 'syllabus_id', FILTER_VALIDATE_INT);
                $topic_name = trim(htmlspecialchars($_POST['topic_name']));
                $description = isset($_POST['description']) ? trim(htmlspecialchars($_POST['description'])) : null;

                 $stmtOwnerCheck = $pdo->prepare("SELECT COUNT(*) FROM syllabi WHERE syllabus_id = ? AND teacher_id = ?");
                 $stmtOwnerCheck->execute([$syllabus_id, $teacher_id]);
                 if ($stmtOwnerCheck->fetchColumn() == 0) { http_response_code(403); $response['message'] = 'Access denied.'; break; }

                if ($syllabus_id && !empty($topic_name)) {
                    $stmtOrder = $pdo->prepare("SELECT MAX(order_index) FROM syllabus_topics WHERE syllabus_id = ?");
                    $stmtOrder->execute([$syllabus_id]);
                    $maxOrder = $stmtOrder->fetchColumn() ?? -1; $newOrder = $maxOrder + 1;

                    $stmt = $pdo->prepare("INSERT INTO syllabus_topics (syllabus_id, topic_name, description, order_index) VALUES (?, ?, ?, ?)");
                    if ($stmt->execute([$syllabus_id, $topic_name, $description, $newOrder])) {
                        $new_topic_id = $pdo->lastInsertId();
                        $stmtNew = $pdo->prepare("SELECT * FROM syllabus_topics WHERE topic_id = ?");
                        $stmtNew->execute([$new_topic_id]); $newTopicData = $stmtNew->fetch(PDO::FETCH_ASSOC);
                        $response = ['success' => true, 'message' => 'Topic added.', 'new_topic' => $newTopicData];
                    } else { $response['message'] = 'Database error: Failed to add topic.'; }
                } else { $response['message'] = 'Missing data.'; }
            } else { $response['message'] = 'Required data not sent.'; }
            break;

        case 'mark_complete':
             // (Keep this case exactly as in the previous answer)
            if (isset($_POST['topic_id'], $_POST['is_completed'])) {
                $topic_id = filter_input(INPUT_POST, 'topic_id', FILTER_VALIDATE_INT);
                $is_completed = filter_var($_POST['is_completed'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);

                if ($topic_id && $is_completed !== null) {
                     $stmtOwnerCheck = $pdo->prepare("SELECT s.teacher_id FROM syllabus_topics st JOIN syllabi s ON st.syllabus_id = s.syllabus_id WHERE st.topic_id = ?");
                     $stmtOwnerCheck->execute([$topic_id]); $owner_teacher_id = $stmtOwnerCheck->fetchColumn();
                     if ($owner_teacher_id !== $teacher_id) { http_response_code(403); $response['message'] = 'Access denied.'; break; }

                    $completion_date = $is_completed ? date('Y-m-d') : null;
                    $completed_by = $is_completed ? $teacher_id : null;
                    $stmt = $pdo->prepare("UPDATE syllabus_topics SET is_completed = ?, completion_date = ?, completed_by_teacher_id = ? WHERE topic_id = ?");
                    if ($stmt->execute([$is_completed ? 1 : 0, $completion_date, $completed_by, $topic_id])) {
                        $response = ['success' => true, 'message' => 'Status updated.', 'completion_date' => $completion_date];
                        // **Trigger chart update? Maybe not needed here, fetch fresh on page load/subject change**
                    } else { $response['message'] = 'Database error: Failed update.'; }
                 } else { $response['message'] = 'Invalid data.'; }
            } else { $response['message'] = 'Required data not sent.'; }
            break;

        // *** NEW CASE for Chart Data ***
        case 'get_teacher_subject_progress':
            $progress_data = [];
            // Find all syllabi (and their subjects) managed by this teacher
            $stmtSyllabi = $pdo->prepare("
                SELECT sy.syllabus_id, s.name as subject_name
                FROM syllabi sy
                JOIN subjects s ON sy.subject_id = s.subject_id
                WHERE sy.teacher_id = ?
            ");
            $stmtSyllabi->execute([$teacher_id]);
            $syllabi_list = $stmtSyllabi->fetchAll(PDO::FETCH_ASSOC);

            foreach ($syllabi_list as $syllabus) {
                $syllabus_id = $syllabus['syllabus_id'];
                $subject_name = $syllabus['subject_name'];

                // Count total and completed topics for this syllabus
                $stmtTopics = $pdo->prepare("
                    SELECT
                        COUNT(*) as total_topics,
                        SUM(CASE WHEN is_completed = 1 THEN 1 ELSE 0 END) as completed_topics
                    FROM syllabus_topics
                    WHERE syllabus_id = ?
                ");
                $stmtTopics->execute([$syllabus_id]);
                $counts = $stmtTopics->fetch(PDO::FETCH_ASSOC);

                $percentage = 0;
                if ($counts && $counts['total_topics'] > 0) {
                    $percentage = round(($counts['completed_topics'] / $counts['total_topics']) * 100);
                }
                $progress_data[$subject_name] = $percentage; // Key = Subject Name, Value = Percentage
            }

            $response = ['success' => true, 'progress' => $progress_data];
            break;

        default:
            $response['message'] = 'Unknown action requested.';
            break;
    }
} catch (PDOException $e) {
    error_log("Database Error in handle_syllabus_actions: " . $e->getMessage()); // Log actual error
    http_response_code(500);
    $response = ['success' => false, 'message' => 'An internal database error occurred.'];
} catch (Exception $e) {
    error_log("General Error in handle_syllabus_actions: " . $e->getMessage()); // Log actual error
    http_response_code(500);
    $response = ['success' => false, 'message' => 'An unexpected error occurred.'];
}

// --- Output JSON Response ---
header('Content-Type: application/json');
echo json_encode($response);
exit;
?>