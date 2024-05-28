<?php
// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Database connection code
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "sba_awards";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Check if form data is set
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $conn->real_escape_string($_POST['username']);
    $password = $_POST['password'];

    // Retrieve user data from database
    $sql = "SELECT * FROM users WHERE username='$username'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if (password_verify($password, $row['password'])) {
            // Successful login, redirect to thank you page
            header("Location: thank_you.php");
            exit();
        } else {
            echo "<div style='text-align: center; margin-top: 20px;'>";
            echo "<h2>Login Failed</h2>";
            echo "<p>Incorrect username or password. Please try again.</p>";
            echo "<p><a href='login.html'>Go back to login page</a></p>";
            echo "</div>";
        }
    } else {
        echo "<div style='text-align: center; margin-top: 20px;'>";
        echo "<h2>Login Failed</h2>";
        echo "<p>Incorrect username or password. Please try again.</p>";
        echo "<p><a href='login.php'>Go back to login page</a></p>";
        echo "</div>";
    }
}

$conn->close();
?>


