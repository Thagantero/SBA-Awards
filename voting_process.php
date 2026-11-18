<?php
// Start the session
session_start();

// Check if user is logged in
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    $_SESSION['message'] = 'Please log in to vote';
    header('Location: login.php');
    exit;
}

// Validate and sanitize nominee_id and num_votes
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['nominee_id'], $_POST['num_votes'])) {
    $nominee_id = intval($_POST['nominee_id']);
    $num_votes = intval($_POST['num_votes']);

    // Check for valid input
    if ($nominee_id <= 0 || $num_votes <= 0) {
        $_SESSION['message'] = 'Invalid nominee or number of votes';
        header('Location: voting.php');
        exit;
    }

    // Connect to the database
    $mysqli = new mysqli((getenv('SBA_DB_HOST') ?: 'localhost'), (string) getenv('SBA_DB_USER'), (string) getenv('SBA_DB_PASSWORD'), (string) getenv('SBA_DB_NAME'));

    // Check for connection errors
    if ($mysqli->connect_error) {
        die("Connection failed: " . $mysqli->connect_error);
    }

    // Get user's credits
    $user_id = $_SESSION['user_id'];
    $credits_query = $mysqli->prepare("SELECT voting_credits FROM users WHERE id = ?");
    $credits_query->bind_param('i', $user_id);
    $credits_query->execute();
    $credits_result = $credits_query->get_result();

    if ($credits_result->num_rows === 1) {
        $user_credits = $credits_result->fetch_assoc();

        // Check if user has sufficient voting credits
        if ($user_credits['voting_credits'] >= $num_votes) {
            // Proceed with voting
            $mysqli->begin_transaction();

            try {
                // Update votes for the nominee
                $update_votes_query = $mysqli->prepare("UPDATE nominees SET votes = votes + ? WHERE id = ?");
                $update_votes_query->bind_param('ii', $num_votes, $nominee_id);
                if (!$update_votes_query->execute()) {
                    throw new Exception('Error updating votes');
                }

                // Deduct the number of votes from user's voting credits
                $deduct_credits_query = $mysqli->prepare("UPDATE users SET voting_credits = voting_credits - ? WHERE id = ?");
                $deduct_credits_query->bind_param('ii', $num_votes, $user_id);
                if (!$deduct_credits_query->execute()) {
                    throw new Exception('Error deducting voting credits');
                }

                $mysqli->commit();
                $_SESSION['message'] = 'Votes cast successfully';
                header('Location: success.php');
                exit;

            } catch (Exception $e) {
                $mysqli->rollback();
                $_SESSION['message'] = $e->getMessage();
                header('Location: voting.php');
                exit;
            }
        } else {
            $_SESSION['message'] = 'Insufficient voting credits';
            header('Location: voting.php');
            exit;
        }
    } else {
        $_SESSION['message'] = 'User not found';
        header('Location: voting.php');
        exit;
    }

    // Close the prepared statement and database connection
    $credits_query->close();
    $mysqli->close();
} else {
    $_SESSION['message'] = 'Invalid request';
    header('Location: voting.php');
    exit;
}
?>
