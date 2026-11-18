<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Check if user is logged in and is admin
    if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
        header("Location: login.php");
        exit;
    }

    // Validate file exists
    if (!isset($_POST['file'])) {
        $_SESSION['error'] = "File not specified.";
        header("Location: uploads.php");
        exit;
    }

    $file = $_POST['file'];
    $uploadDir = "uploads/";

    // Check if file exists
    if (!file_exists($uploadDir . $file)) {
        $_SESSION['error'] = "File not found.";
        header("Location: uploads.php");
        exit;
    }

    // Prepare updated file details
    $newTitle = $_POST['title'];
    $altText = $_POST['alt_text'];
    $caption = $_POST['caption'];
    $description = $_POST['description'];

    // Example: Update metadata in database or metadata file
    // For demonstration purposes, assume updating a metadata file

    // Define metadata file
    $metadataFile = "metadata.json";

    // Read existing metadata
    $metadata = [];
    if (file_exists($metadataFile)) {
        $metadata = json_decode(file_get_contents($metadataFile), true);
    }

    // Update metadata for the file
    $metadata[$file] = [
        'title' => $newTitle,
        'alt_text' => $altText,
        'caption' => $caption,
        'description' => $description,
    ];

    // Save updated metadata
    file_put_contents($metadataFile, json_encode($metadata, JSON_PRETTY_PRINT));

    // Debugging: Dump metadata for verification
    var_dump($metadata);

    // Redirect back to uploads.php with success message
    $_SESSION['success'] = "File details updated successfully.";
    header("Location: uploads.php");
    exit;
} else {
    header("Location: uploads.php");
    exit;
}
?>
