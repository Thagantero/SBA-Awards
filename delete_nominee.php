<?php
// Start session and enable error reporting as needed
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Check if admin is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header("Location: login.php");
    exit;
}

// Include database connection
require_once "dbConnect.php";

// Check if nominee id is provided
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Delete nominee data
    $sql = "DELETE FROM nominees WHERE id='$id'";
    if ($conn->query($sql) === TRUE) {
        header("Location: admin_dashboard.php");
        exit;
    } else {
        echo "Error deleting record: " . $conn->error;
    }
} else {
    header("Location: admin_dashboard.php");
    exit;
}

$conn->close();
?>
