<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Start session
session_start();

// Include dbConnect.php if not already included
include 'dbConnect.php';

// Check if user is not logged in, redirect to login page
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit;
}

// Get user_id from session
$user_id = $_SESSION['user_id'];

// Fetch user's profile information and voting credits
$user_query = $mysqli->prepare("SELECT username, email, voting_credits FROM users WHERE id = ?");
$user_query->bind_param('i', $user_id);
$user_query->execute();
$user_result = $user_query->get_result();
$user_data = $user_result->fetch_assoc();

if ($user_data) {
    $username = htmlspecialchars($user_data['username']);
    $email = htmlspecialchars($user_data['email']);
    $voting_credits = htmlspecialchars($user_data['voting_credits']);
}

// Initialize message variable
$message = "";

// Handle form submission to buy more credits
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['num_credits'])) {
    // Sanitize input
    $num_credits = intval($_POST['num_credits']);

    // Validate input
    if ($num_credits > 0) {
        // Update user's voting credits
        $update_credits_query = $mysqli->prepare("UPDATE users SET voting_credits = voting_credits + ? WHERE id = ?");
        $update_credits_query->bind_param('ii', $num_credits, $user_id);

        if ($update_credits_query->execute()) {
            // Set success message
            $message = "Successfully added {$num_credits} credits!";
            // Refresh user data after update
            $user_query->execute();
            $user_result = $user_query->get_result();
            $user_data = $user_result->fetch_assoc();
            if ($user_data) {
                $voting_credits = htmlspecialchars($user_data['voting_credits']);
            }
        } else {
            // Set error message if update fails
            $message = "Error: Unable to add credits.";
        }
    } else {
        // Set error message for invalid input
        $message = "Please enter a valid number of credits.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Credits Profile</title>
    <link rel="stylesheet" href="styles.css"> <!-- Include your CSS file -->
    <style>
        /* Additional inline styles for demo purposes */
        body {
            font-family: Arial, sans-serif;
            background-color: #111;
            color: #fff;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 800px;
            margin: 70px auto 20px; /* Adjust top margin to account for the fixed header */
            padding: 20px;
            background-color: #222;
            border-radius: 10px;
        }
        .profile-info, .credits-info, .buy-credits-form {
            margin-bottom: 20px;
        }
        .profile-info h2, .credits-info h2, .buy-credits-form h2 {
            color: #ffd700; /* gold color */
        }
        .buy-credits-link {
            display: inline-block;
            text-decoration: none;
            color: #fff;
            background-color: #007bff;
            padding: 8px 16px;
            border-radius: 5px;
            cursor: pointer;
        }
        .buy-credits-link:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>

<?php include 'header.php'; ?> <!-- Include header content -->

<div class="container">
    <h1>Credits Profile</h1>
    
    <?php if (!empty($message)) { ?>
        <div class="message <?php echo isset($error) ? 'error' : 'success'; ?>"><?php echo $message; ?></div>
    <?php } ?>

    <div class="profile-info">
        <h2>User Information</h2>
        <p><strong>Username:</strong> <?php echo $username; ?></p>
        <p><strong>Email:</strong> <?php echo $email; ?></p>
    </div>

    <div class="credits-info">
        <h2>Voting Credits</h2>
        <p><strong>Available Credits:</strong> <?php echo $voting_credits; ?></p>
    </div>

    <div class="buy-credits-form">
        <h2>Buy More Credits</h2>
        <p>If you need more voting credits, you can purchase them via M-Pesa.</p>
        <a href="buy_credits.php" class="buy-credits-link">Buy Credits via M-Pesa</a>
    </div>

</div>

<?php include 'footer.php'; ?> <!-- Include footer content -->

</body>
</html>
