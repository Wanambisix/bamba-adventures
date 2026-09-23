<?php
header('Content-Type: application/json');
header('X-Content-Type-Options: nosniff');
// No Access-Control-Allow-Origin header on purpose: this API is only ever
// called same-origin by the site's own forms. A wildcard would let any other
// website drive it from a visitor's browser.

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(200); exit; }

require_once __DIR__ . '/../includes/functions.php';

$action = $_GET['action'] ?? $_POST['action'] ?? '';

// Abuse protection: 10 submissions per action per IP per 10 minutes.
$allowedActions = ['subscribe', 'contact', 'volunteer', 'career_apply'];
if (in_array($action, $allowedActions, true)
    && !rate_limit($pdo, 'api:' . $action . ':' . client_ip(), 10, 10)) {
    http_response_code(429);
    echo json_encode(['success' => false, 'error' => 'Too many requests. Please try again later.']);
    exit;
}

switch ($action) {
    
    // ----- NEWSLETTER SUBSCRIBE -----
    case 'subscribe':
        $email = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
        if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['success' => false, 'error' => 'Invalid email address']);
            exit;
        }
        try {
            $pdo->prepare("INSERT INTO subscribers (email) VALUES (?) ON DUPLICATE KEY UPDATE status = 'active'")->execute([$email]);
            echo json_encode(['success' => true, 'message' => 'Subscribed successfully!']);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => 'Subscription failed']);
        }
        break;
    
    // ----- CONTACT INQUIRY -----
    case 'contact':
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $subject = trim($_POST['subject'] ?? '');
        $message = trim($_POST['message'] ?? '');
        $pageUrl = trim($_POST['page_url'] ?? '');
        
        if (!$name || !$email || !$message) {
            echo json_encode(['success' => false, 'error' => 'Name, email and message are required']);
            exit;
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['success' => false, 'error' => 'Please enter a valid email address']);
            exit;
        }
        
        try {
            $stmt = $pdo->prepare("INSERT INTO inquiries (name, email, phone, subject, message, page_url) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$name, $email, $phone, $subject, $message, $pageUrl]);
            echo json_encode(['success' => true, 'message' => 'Thank you! We have received your message.']);
        } catch (Exception $e) {
            // Never let a PDOException escape: it prints a stack trace with the
            // DB credentials' context into a public JSON endpoint.
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Could not send your message right now. Please email us instead.']);
        }
        break;
    
    // ----- VOLUNTEER APPLICATION -----
    case 'volunteer':
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $motivation = trim($_POST['motivation'] ?? '');
        $availability = trim($_POST['availability'] ?? '');
        $skills = trim($_POST['skills'] ?? '');
        
        if (!$name || !$email || !$motivation) {
            echo json_encode(['success' => false, 'error' => 'Name, email and motivation are required']);
            exit;
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['success' => false, 'error' => 'Please enter a valid email address']);
            exit;
        }
        
        try {
            $stmt = $pdo->prepare("INSERT INTO volunteer_applications (name, email, phone, motivation, availability, skills) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$name, $email, $phone, $motivation, $availability, $skills]);
            echo json_encode(['success' => true, 'message' => 'Application submitted successfully!']);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Could not submit your application right now. Please email us instead.']);
        }
        break;
    
    // ----- CAREER APPLICATION -----
    case 'career_apply':
        $careerId = intval($_POST['career_id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $coverLetter = trim($_POST['cover_letter'] ?? '');
        
        if (!$careerId || !$name || !$email) {
            echo json_encode(['success' => false, 'error' => 'All required fields must be filled']);
            exit;
        }
        
        $resumePath = '';
        $uploadedPath = null;
        if (!empty($_FILES['resume']['tmp_name'])) {
            // Content-checked (PDF/DOC/DOCX magic bytes), size-capped and
            // renamed server-side. This used to trust the client's extension.
            $up = uploadDocument($_FILES['resume'], 'resumes');
            if (!isset($up['success'])) {
                echo json_encode(['success' => false, 'error' => $up['error']]);
                exit;
            }
            $resumePath = $up['path'];
            $uploadedPath = $up['path'];
        }

        try {
            $stmt = $pdo->prepare("INSERT INTO career_applications (career_id, name, email, phone, resume_path, cover_letter) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$careerId, $name, $email, $phone, $resumePath, $coverLetter]);
            echo json_encode(['success' => true, 'message' => 'Application submitted successfully!']);
        } catch (Exception $e) {
            // A bad career_id (or any DB error) used to throw out of the switch
            // and print a PDOException stack trace to the applicant. Roll the
            // uploaded file back too, so a failed application leaves no orphan.
            if ($uploadedPath !== null) {
                @unlink(__DIR__ . '/../' . $uploadedPath);
            }
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Could not submit your application right now. Please try again shortly.']);
        }
        break;
    
    default:
        echo json_encode(['success' => false, 'error' => 'Unknown action']);
        break;
}
