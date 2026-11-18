<?php
// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Start session
session_start();

// Insert Header
include 'header.php';

// Include dbConnect.php
include 'dbConnect.php';

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit;
}

// Assuming you have a database connection $conn
include 'config.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $newUsername = $_POST['username'];
    $newPassword = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Use prepared statements to prevent SQL injection
    $sql = "UPDATE users SET username = ?, password = ? WHERE username = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("sss", $newUsername, $newPassword, $_SESSION['username']);
        
        if ($stmt->execute()) {
            $_SESSION['username'] = $newUsername;
            $message = "Profile updated successfully";
        } else {
            $message = "Error updating profile: " . $stmt->error;
        }

        $stmt->close();
    } else {
        $message = "Error preparing statement: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile Page</title>
    <link rel="stylesheet" href="styles.css"> <!-- Link to the external CSS file -->
    <script src="https://kit.fontawesome.com/87ab693041.js" crossorigin="anonymous"></script>
    <style>
        /* Basic reset and styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: Arial, sans-serif;
            background-color: black;
            color: gold;
            margin: 0;
            padding: 0;
        }
        .container {
            width: 100%;
            max-width: 1200px; /* Adjust max-width as per your design */
            margin: 0 auto; /* Center align the container */
            padding: 20px;
        }
        .profile-info {
            margin-bottom: 20px;
        }
        .update-form {
            background-color: #111;
            color: gold;
            padding: 20px;
            border-radius: 10px;
        }
        .update-form input[type="text"],
        .update-form input[type="password"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid gold;
            background-color: #222;
            color: gold;
            border-radius: 5px;
        }
        .update-form button {
            padding: 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            background-color: gold;
            color: black;
        }
        .update-form button:hover {
            background-color: #f7d32e;
        }
        footer {
            background-color: gold;
            color: black;
            padding: 10px;
            text-align: center;
            width: 100%; /* Ensure footer spans full width */
            position: fixed;
            bottom: 0;
            left: 0;
        }
    </style>
</head>
<body>

<div class="container">
    <h1>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h1>
    <div class="profile-info">
        <h2>Your Profile</h2>
        <p>Username: <?php echo htmlspecialchars($_SESSION['username']); ?></p>
    </div>

    <div class="update-form">
        <h2>Update Profile</h2>
        <form method="POST" action="profile.php">
            <input type="text" name="username" placeholder="New Username" required>
            <input type="password" name="password" placeholder="New Password" required>
            <button type="submit">Update Profile</button>
        </form>
        <?php if ($message) { echo "<p>$message</p>"; } ?>
    </div>
</div>

<!-- Insert Footer -->
<?php include 'footer.php'; ?>

</body>
</html>
