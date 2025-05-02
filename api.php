
<?php
session_start();
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

// Database connection
$host = 'localhost';
$username = 'root';
$password = 'Sivani@123';
$database = 'fileshare';

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die(json_encode(['error' => 'Database connection failed: ' . $conn->connect_error]));
}

// Log user activity
function logActivity($conn, $userId, $action, $fileName) {
    $stmt = $conn->prepare('INSERT INTO user_activities (user_id, action, file_name, created_at) VALUES (?, ?, ?, NOW())');
    $stmt->bind_param('iss', $userId, $action, $fileName);
    $stmt->execute();
    $stmt->close();
}

// Handle API requests
$action = isset($_GET['action']) ? $_GET['action'] : '';

switch ($action) {
    // Get all users (for admin)
    case 'get_users':
        $result = $conn->query('SELECT id, name FROM users');
        $users = [];
        while ($row = $result->fetch_assoc()) {
            $users[] = $row;
        }
        echo json_encode($users);
        break;

    // Get all documents (for admin)
    case 'get_documents':
        $result = $conn->query('SELECT id, file_name FROM files');
        $documents = [];
        while ($row = $result->fetch_assoc()) {
            $documents[] = $row;
        }
        echo json_encode($documents);
        break;

    // Assign documents to user (admin only)
    case 'assign_documents':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = json_decode(file_get_contents('php://input'), true);
            $userId = isset($data['userId']) ? intval($data['userId']) : null;
            $documentIds = isset($data['documentIds']) ? $data['documentIds'] : [];
            $adminId = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : null;

            error_log("Assign Documents: userId=$userId, adminId=$adminId, documentIds=" . json_encode($documentIds));
            error_log('Session data: ' . print_r($_SESSION, true));

            if (!$adminId) {
                echo json_encode(['error' => 'Admin not authenticated. Please log in.']);
                http_response_code(401);
                break;
            }
            if (!$userId || $userId <= 0) {
                echo json_encode(['error' => 'Invalid or missing user ID']);
                http_response_code(400);
                break;
            }
            if (empty($documentIds)) {
                echo json_encode(['error' => 'No documents selected']);
                http_response_code(400);
                break;
            }

            $stmt = $conn->prepare('SELECT id FROM admins WHERE id = ?');
            $stmt->bind_param('i', $adminId);
            $stmt->execute();
            if ($stmt->get_result()->num_rows === 0) {
                echo json_encode(['error' => 'Invalid admin ID']);
                http_response_code(400);
                $stmt->close();
                break;
            }
            $stmt->close();

            $stmt = $conn->prepare('SELECT id FROM users WHERE id = ?');
            $stmt->bind_param('i', $userId);
            $stmt->execute();
            if ($stmt->get_result()->num_rows === 0) {
                echo json_encode(['error' => 'Invalid user ID']);
                http_response_code(400);
                $stmt->close();
                break;
            }
            $stmt->close();

            $conn->begin_transaction();
            try {
                $stmt = $conn->prepare("
                    INSERT INTO documents (file_name, file_path, admin_id, user_id, uploaded_at)
                    SELECT f.file_name, f.file_path, ?, ?, NOW()
                    FROM files f
                    WHERE f.id = ?
                ");

                foreach ($documentIds as $docId) {
                    $docId = intval($docId);
                    $stmt->bind_param('iii', $adminId, $userId, $docId);
                    if (!$stmt->execute()) {
                        throw new Exception('Failed to assign document ID: ' . $docId);
                    }
                }

                $conn->commit();
                echo json_encode(['message' => 'Documents assigned successfully']);
            } catch (Exception $e) {
                $conn->rollback();
                echo json_encode(['error' => 'Failed to assign documents: ' . $e->getMessage()]);
                http_response_code(500);
            }
            $stmt->close();
        }
        break;

    // Get assigned documents for a user (accessible by user or admin)
    case 'get_assigned_documents':
        $userId = isset($_GET['userId']) ? intval($_GET['userId']) : 0;
        if ($userId <= 0) {
            echo json_encode(['error' => 'Invalid user ID']);
            http_response_code(400);
            break;
        }

        $stmt = $conn->prepare("
            SELECT d.id, d.file_name, d.file_path, d.uploaded_at, u.id as user_id, u.name as user_name, a.name as admin_name
            FROM documents d
            JOIN users u ON d.user_id = u.id
            JOIN admins a ON d.admin_id = a.id
            WHERE d.user_id = ?
        ");
        $stmt->bind_param('i', $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $documents = [];
        while ($row = $result->fetch_assoc()) {
            $documents[] = $row;
        }
        
        echo json_encode($documents);
        $stmt->close();
        break;

    // Get all assigned documents (admin only)
    case 'get_all_assigned_documents':
        $adminId = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : null;
        if (!$adminId) {
            echo json_encode(['error' => 'Admin not authenticated. Please log in.']);
            http_response_code(401);
            break;
        }

        $result = $conn->query("
            SELECT d.id, d.file_name, d.uploaded_at, u.id as user_id, u.name as user_name, a.name as admin_name
            FROM documents d
            JOIN users u ON d.user_id = u.id
            JOIN admins a ON d.admin_id = a.id
        ");
        $documents = [];
        while ($row = $result->fetch_assoc()) {
            $documents[] = $row;
        }
        echo json_encode($documents);
        break;

    // Delete a document assignment (admin only)
    case 'delete_document':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = json_decode(file_get_contents('php://input'), true);
            $docId = isset($data['docId']) ? intval($data['docId']) : null;
            $adminId = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : null;

            if (!$adminId) {
                echo json_encode(['error' => 'Admin not authenticated. Please log in.']);
                http_response_code(401);
                break;
            }
            if (!$docId) {
                echo json_encode(['error' => 'Invalid document ID']);
                http_response_code(400);
                break;
            }

            // Fetch file_name before deletion for activity logging
            $stmt = $conn->prepare('SELECT file_name FROM documents WHERE id = ? AND admin_id = ?');
            $stmt->bind_param('ii', $docId, $adminId);
            $stmt->execute();
            $result = $stmt->get_result();
            if ($row = $result->fetch_assoc()) {
                $fileName = $row['file_name'];
                $stmt->close();

                // Delete the document
                $stmt = $conn->prepare('DELETE FROM documents WHERE id = ? AND admin_id = ?');
                $stmt->bind_param('ii', $docId, $adminId);
                if ($stmt->execute()) {
                    // Log the deletion activity
                    logActivity($conn, $adminId, 'Deleted', $fileName);
                    echo json_encode(['message' => 'Document assignment deleted successfully']);
                } else {
                    echo json_encode(['error' => 'Failed to delete document assignment']);
                    http_response_code(500);
                }
                $stmt->close();
            } else {
                echo json_encode(['error' => 'Document not found or unauthorized']);
                http_response_code(404);
                $stmt->close();
            }
        }
        break;

    // Get recent activities for a user
    case 'get_recent_activities':
        $userId = isset($_GET['userId']) ? intval($_GET['userId']) : 0;
        $filterAction = isset($_GET['filterAction']) ? $_GET['filterAction'] : null;
        if ($userId <= 0) {
            echo json_encode(['error' => 'Invalid user ID']);
            http_response_code(400);
            break;
        }

        // Ensure the requesting user is authorized
        $sessionUserId = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : null;
        if (!$sessionUserId || ($sessionUserId !== $userId && !$conn->query("SELECT id FROM admins WHERE id = $sessionUserId")->num_rows)) {
            echo json_encode(['error' => 'Unauthorized access']);
            http_response_code(403);
            break;
        }

        $query = "SELECT action, file_name, created_at FROM user_activities WHERE user_id = ?";
        if ($filterAction) {
            $query .= " AND action = ?";
        }
        $query .= " ORDER BY created_at DESC LIMIT 10";
        $stmt = $conn->prepare($query);
        if ($filterAction) {
            $stmt->bind_param('is', $userId, $filterAction);
        } else {
            $stmt->bind_param('i', $userId);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        
        $activities = [];
        while ($row = $result->fetch_assoc()) {
            $activities[] = [
                'action' => $row['action'],
                'file_name' => $row['file_name'],
                'created_at' => $row['created_at']
            ];
        }
        
        echo json_encode($activities);
        $stmt->close();
        break;

    // Download a document (logs activity)
    case 'download_document':
        $docId = isset($_GET['docId']) ? intval($_GET['docId']) : 0;
        $userId = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : null;

        if (!$userId) {
            echo json_encode(['error' => 'User not authenticated. Please log in.']);
            http_response_code(401);
            break;
        }
        if ($docId <= 0) {
            echo json_encode(['error' => 'Invalid document ID']);
            http_response_code(400);
            break;
        }

        $stmt = $conn->prepare("
            SELECT file_name, file_path
            FROM documents
            WHERE id = ? AND user_id = ?
        ");
        $stmt->bind_param('ii', $docId, $userId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            // Log the download activity
            logActivity($conn, $userId, 'Downloaded', $row['file_name']);
            // Return file details (front-end can use file_path for download)
            echo json_encode([
                'file_name' => $row['file_name'],
                'file_path' => $row['file_path']
            ]);
        } else {
            echo json_encode(['error' => 'Document not found or unauthorized']);
            http_response_code(404);
        }
        $stmt->close();
        break;

    default:
        echo json_encode(['error' => 'Invalid action']);
        http_response_code(400);
}

$conn->close();
?>