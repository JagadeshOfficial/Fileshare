<?php
include('db.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    $fileId = isset($data['id']) ? intval($data['id']) : null;

    if ($fileId) {
        $stmt = $conn->prepare("UPDATE files SET is_deleted = 1 WHERE id = ?");
        $stmt->bind_param("i", $fileId);
        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'File marked as deleted.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to delete file: ' . $conn->error]);
        }
        $stmt->close();
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid file ID.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
}

$conn->close();
?>