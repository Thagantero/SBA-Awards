<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);


// Database connection code
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "sba_awards";

// Establish database connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check if connection was successful
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if the form has been submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $email = $conn->real_escape_string($_POST['email']);
    $otp = $conn->real_escape_string($_POST['otp']);

    // Verify OTP
    $sql = "SELECT * FROM users WHERE email='$email' AND otp='$otp'";
    $result = $conn->query($sql);

    // Check if query executed successfully
    if (!$result) {
        die("Error executing query: " . $conn->error);
    }

    // Check if OTP verification succeeded
    if ($result->num_rows > 0) {
        // Update user status to activated
        $sql_update = "UPDATE users SET status='activated', otp=NULL WHERE email='$email'";
        if ($conn->query($sql_update) === TRUE) {
            echo "Account activated successfully. You can now login.";
        } else {
            echo "Error updating record: " . $conn->error;
        }
    } else {
        echo "Invalid OTP. Please try again.";
    }
}

// Close database connection
$conn->close();
?>
