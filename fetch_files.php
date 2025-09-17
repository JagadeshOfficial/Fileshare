<?php
// Include your database connection file
include('db.php'); // Adjust the path as needed

// Set the response type to JSON
header('Content-Type: application/json');

// Fetch the list of uploaded files from the database, excluding deleted files
$query = "SELECT * FROM files WHERE is_deleted = 0";
$result = $conn->query($query);

if ($result->num_rows > 0) {
    $fileList = [];
    while ($row = $result->fetch_assoc()) {
        $fileList[] = [
            'id' => $row['id'],
            'file_name' => $row['file_name'],
            'file_size' => number_format($row['file_size'] / 1024, 2) . ' KB',
            'file_type' => $row['file_type'],
            'upload_date' => $row['upload_date'],
        ];
    }
    echo json_encode(['status' => 'success', 'files' => $fileList]);
} else {
    echo json_encode(['status' => 'error', 'message' => 'No files found.']);
}

$conn->close();
?>