<?php
// Start the session
session_start();

// Check if the user is logged in and is an admin
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header("Location: login.php");
    exit;
}

// Include database connection
require_once "dbConnect.php";

// Get post ID from URL
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Delete the post
    $sql = "DELETE FROM blog_posts WHERE id = ?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        header("Location: posts_page.php");
        exit;
    } else {
        echo "Error deleting post: " . $stmt->error;
    }
} else {
    echo "Invalid post ID.";
}
?>
