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

// Check if user_id is set in the session
if (!isset($_SESSION['user_id'])) {
    die('User ID not set in session. Please log in again.');
}

// Get user_id from session
$user_id = $_SESSION['user_id'];

// Initialize variables for user credits
$user_credits = ['voting_credits' => 0]; // Default value

// Fetch user's voting credits
$credits_query = $mysqli->prepare("SELECT voting_credits FROM users WHERE id = ?");
$credits_query->bind_param('i', $user_id);
$credits_query->execute();
$credits_result = $credits_query->get_result();
if ($credits_result->num_rows > 0) {
    $user_credits = $credits_result->fetch_assoc();
}

// Initialize message variables
$vote_message = "";
$error_message = "";

// Default value for number of votes
$default_votes = 1;

// Validate and sanitize nominee_id from $_GET
$nominee_id = null;
if (isset($_GET['nominee_id']) && is_numeric($_GET['nominee_id'])) {
    $nominee_id = intval($_GET['nominee_id']);

    // Fetch nominee details
    $stmt = $mysqli->prepare("SELECT * FROM nominees WHERE id = ?");
    $stmt->bind_param("i", $nominee_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $nominee = $result->fetch_assoc();

    if (!$nominee) {
        // Handle case where nominee_id does not exist in database
        $error_message = 'Nominee not found';
    }
} else {
    // Handle case where nominee_id is missing or invalid
    $error_message = 'Invalid nominee ID';
}

// Check if the form is submitted and process the vote
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['num_votes'])) {
    // Sanitize input
    $num_votes = intval($_POST['num_votes']);

    // Validate number of votes and user's voting credits
    if ($num_votes > 0 && $user_credits['voting_credits'] >= $num_votes && $nominee) {
        // Proceed with voting process
        $update_votes_query = $mysqli->prepare("UPDATE nominees SET votes = votes + ? WHERE id = ?");
        $update_votes_query->bind_param('ii', $num_votes, $nominee_id);
        $update_votes_query->execute();

        $deduct_credits_query = $mysqli->prepare("UPDATE users SET voting_credits = voting_credits - ? WHERE id = ?");
        $deduct_credits_query->bind_param('ii', $num_votes, $user_id);
        $deduct_credits_query->execute();

        // Set success message
        $vote_message = "Thank you for voting for {$nominee['name']}. You casted {$num_votes} vote(s)!";
        // Refresh the user's credits
        $credits_query->execute();
        $credits_result = $credits_query->get_result();
        if ($credits_result->num_rows > 0) {
            $user_credits = $credits_result->fetch_assoc();
        }
    } else {
        // Set error message if validation fails
        $error_message = 'Invalid number of votes or insufficient voting credits';
    }
} else {
    // If not submitted, set default value
    $num_votes = $default_votes;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Voting Page</title>
    <style>
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
        label, input {
            display: block;
            margin-bottom: 10px;
        }
        .vote-button {
            background-color: green;
            color: #fff;
            border: none;
            padding: 8px 16px;
            border-radius: 5px;
            cursor: pointer;
        }
        .credits-info {
            margin-top: 20px;
            color: #ffd700; /* gold color */
        }
        .message {
            margin-top: 20px;
            padding: 10px;
            border-radius: 5px;
        }
        .success {
            background-color: #4CAF50;
        }
        .error {
            background-color: #f44336;
        }
        .footer {
            margin-top: 20px;
            text-align: center;
        }
        .footer a {
            color: #ffd700;
            margin: 0 10px;
            text-decoration: none;
        }
        .footer a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<?php include 'header.php'; ?>

<div class="container">
    <h1>Voting Page</h1>
    <?php if (isset($nominee) && $nominee) { ?>
        <h2><?php echo htmlspecialchars($nominee['name']); ?></h2>
        <p>Company Name: <?php echo htmlspecialchars($nominee['companyName']); ?></p>
        <p>Category: <?php echo htmlspecialchars($nominee['category']); ?></p>
        <!-- Add more details as needed -->
    <?php } else { ?>
        <p><?php echo $error_message; ?></p>
    <?php } ?>

    <?php if (isset($nominee) && $nominee) { ?>
        <form action="" method="post">
            <label for="num_votes">Number of Votes:</label>
            <input type="number" id="num_votes" name="num_votes" min="1" max="10" value="<?php echo $num_votes; ?>" required>
            <button type="submit" class="vote-button">Vote</button>
        </form>

        <div class="credits-info">
            <p>Available Voting Credits: <?php echo htmlspecialchars($user_credits['voting_credits']); ?></p>
        </div>

        <?php if (!empty($vote_message)) { ?>
            <div class="message success"><?php echo $vote_message; ?></div>
        <?php } ?>

        <?php if (!empty($error_message)) { ?>
            <div class="message error"><?php echo $error_message; ?></div>
        <?php } ?>
    <?php } ?>

</div>

<?php include 'footer.php'; ?>

</body>
</html>
