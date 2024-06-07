<?php
// Database connection parameters
$servername = "localhost"; // Change this if your database is hosted elsewhere
$username = "root"; // Change this to your database username
$password = "starbecky"; // Change this to your database password
$dbname = "sba-awards"; // Change this to your database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Retrieve form data
    $firstname = $_POST['FirstName'];
    $secondname = $_POST['SecondName'];
    $username = $_POST['Username'];
    $email = $_POST['Email'];
    $password = $_POST['Password'];

    // SQL query to insert data into the database
    $sql = "INSERT INTO voters (FirstName, SecondName, UserName, Email, Password) VALUES ('$firstname', '$secondname','$username', '$email', '$password')";

    // Execute the SQL query
    if ($conn->query($sql) === TRUE) {
        echo "New record created successfully";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}

// Close the database connection
$conn->close();
