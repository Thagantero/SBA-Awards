<?php
// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Start session and check authentication
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header("Location: login.php");
    exit;
}

// Include database connection
require_once "dbConnect.php";

// Initialize variables with default values
$successMessage = null;
$fileUrls = [];

// Directory where files will be uploaded
$targetDir = "uploads/";

// Function to sanitize file name
function sanitizeFileName($fileName) {
    // Remove spaces and special characters from file name
    $fileName = preg_replace("/[^a-zA-Z0-9\-_.]/", "", $fileName);
    return $fileName;
}

// Check if files are uploaded
if (!empty($_FILES['files']['name'][0])) {
    $files = $_FILES['files'];

    foreach ($files['name'] as $index => $fileName) {
        $fileType = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $targetFile = $targetDir . sanitizeFileName(basename($fileName));

        // Check if file already exists
        if (file_exists($targetFile)) {
            $_SESSION['error'] = "Sorry, file '{$fileName}' already exists.";
            continue; // Skip current file and proceed with next
        }

        // Check file size (limit to 50MB)
        if ($files['size'][$index] > 50 * 1024 * 1024) {
            $_SESSION['error'] = "Sorry, your file '{$fileName}' is too large. Max size is 50MB.";
            continue; // Skip current file and proceed with next
        }

        // Allow certain file formats
        $allowedExtensions = array("jpg", "jpeg", "png", "gif", "webp", "pdf", "doc", "docx", "zip");
        if (!in_array($fileType, $allowedExtensions)) {
            $_SESSION['error'] = "Sorry, only JPG, JPEG, PNG, GIF, WebP, PDF, DOC, DOCX, ZIP files are allowed.";
            continue; // Skip current file and proceed with next
        }

        // Try to upload the file
        if (move_uploaded_file($files['tmp_name'][$index], $targetFile)) {
            // Success, prepare link for copy
            $fileUrl = $targetDir . basename($fileName);
            $fileUrls[] = $fileUrl; // Store file URL in array
        } else {
            $_SESSION['error'] = "Sorry, there was an error uploading your file '{$fileName}'.";
        }
    }

    // Set success message and file URLs in session
    if (empty($_SESSION['error'])) {
        $_SESSION['success'] = "File(s) uploaded successfully.";
        $_SESSION['fileUrls'] = $fileUrls;
    }

    // Redirect to upload.php to display messages and file links
    header("Location: upload.php");
    exit;
} else {
    $_SESSION['error'] = "No files uploaded.";
    header("Location: upload.php");
    exit;
}
?>
