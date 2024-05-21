<?php
// Database connection parameters
$host = "localhost";
$username = "your_username";
$password = "your_password";
$database = "signup_db";

// Create connection
$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Sample user data
$username = "john_doe";
$email = "john@example.com";
$password = password_hash("password123", PASSWORD_DEFAULT); // Hash the password before storing

// SQL query to insert user data
$sql = "INSERT INTO users (username, email, password) VALUES ('$username', '$email', '$password')";

// Execute query
if ($conn->query($sql) === TRUE) {
    echo "New record created successfully";
} else {
    echo "Error: " . $sql . "<br>" . $conn->error;
}

// Close connection
$conn->close();
?>
