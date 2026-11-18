<?php
// Enable error reporting (remove in production)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Include PHPMailer autoload.php path
require 'vendor/autoload.php'; // Adjust the path as per your project structure

// Include database connection file
include 'dbConnect.php';

// Initialize variables
$email = "";
$message = "";
$error = "";

// Check if form was submitted to send reset email
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['email'])) {
    $email = $_POST['email'];

    // Generate a secure token for password reset
    $token = bin2hex(random_bytes(32)); // Example: Random 32-byte token

    // Update database with reset token
    $sql = "UPDATE users SET reset_token = ? WHERE email = ?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("ss", $token, $email);

    if ($stmt->execute()) {
        // Fetch username for email content
        $query = "SELECT username FROM users WHERE email = ?";
        $stmt_username = $mysqli->prepare($query);
        $stmt_username->bind_param("s", $email);
        $stmt_username->execute();
        $stmt_username->bind_result($username);
        $stmt_username->fetch();
        $stmt_username->close();

        // Send password reset email
        $mail = new PHPMailer(true);

        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host = (string) getenv('SBA_SMTP_HOST'); // SMTP server
            $mail->SMTPAuth   = true;                     // Enable SMTP authentication
            $mail->Username = (string) getenv('SBA_SMTP_USER');         // SMTP username
            $mail->Password = (string) getenv('SBA_SMTP_PASSWORD');         // SMTP password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Enable TLS encryption
            $mail->Port       = 587;                      // TCP port to connect to

            // Disable SSL verification (if needed)
            $mail->SMTPOptions = array(
                'ssl' => array(
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true
                )
            );

            // Recipients
            $mail->setFrom((string) getenv('SBA_SMTP_FROM'), 'SBA Awards');
            $mail->addAddress($email); // Add recipient email address

            // Content
            $mail->isHTML(true);                                  // Set email format to HTML
            $mail->Subject = 'Password Reset Request';
            $mail->Body    = "<p>Hello $username,</p>
                              <p>You have requested a password reset. Click the link below to reset your password:</p>
                              <p><a href='http://localhost/SBA-awards/reset_password.php?token=$token'>Reset Password</a></p>
                              <p>If you did not request this, please ignore this email.</p>
                              <p>Best regards,<br>SBA Awards Team</p>";
            $mail->AltBody = 'Please enable HTML email to view this message';

            // Send email
            $mail->send();
            $message = 'Password reset email sent. Check your inbox (and spam folder) for further instructions.';
        } catch (Exception $e) {
            $error = "Error sending email: {$mail->ErrorInfo}";
        }
    } else {
        $error = "Error updating database: " . $mysqli->error;
    }

    $stmt->close();
}

// Check if token is present in URL for password reset form
if (isset($_GET['token'])) {
    $token = $_GET['token'];

    // Handle password reset form submission
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['password'], $_POST['confirm_password'])) {
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];

        // Validate password
        if (empty($password)) {
            $error = "Password is required";
        } elseif (strlen($password) < 6) {
            $error = "Password must be at least 6 characters";
        } elseif ($password !== $confirm_password) {
            $error = "Passwords do not match";
        } else {
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Update password in database and remove reset token
            $sql = "UPDATE users SET password = ?, reset_token = NULL WHERE reset_token = ?";
            $stmt = $mysqli->prepare($sql);
            $stmt->bind_param("ss", $hashed_password, $token);

            if ($stmt->execute()) {
                $message = "Password reset successfully. You can now <a href='login.php'>login here</a>.";
            } else {
                $error = "Error updating password: " . $mysqli->error;
            }

            $stmt->close();
        }
    }
}

// Close connection
$mysqli->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <style>
        body {
            background-color: white;
            color: black;
            font-family: Arial, sans-serif;
            text-align: center;
            padding: 20px;
        }
        .otp-container {
            background-color: black;
            color: gold;
            border: 2px solid gold;
            border-radius: 10px;
            padding: 20px;
            display: inline-block;
            margin-top: 20px;
            width: 300px;
            text-align: left;
        }
        h2 {
            color: gold;
            text-align: center;
        }
        .otp-sent {
            background-color: #333;
            color: gold;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 5px;
            text-align: center;
        }
        .form-group {
            margin: 15px 0;
            text-align: left;
        }
        .form-control {
            padding: 10px;
            width: calc(100% - 22px); /* Adjusted to account for padding and border */
            border: 1px solid gold;
            border-radius: 5px;
            margin-top: 5px;
        }
        .btn-primary {
            background-color: gold;
            color: black;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 10px;
            width: 100%;
        }
        .btn-primary:hover {
            background-color: darkgoldenrod;
        }
        .btn-secondary {
            background-color: #ccc;
            color: black;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            margin-top: 10px;
            width: 100%;
        }
        .btn-secondary:hover {
            background-color: #aaa;
        }
    </style>
</head>
<body>
    <div class="otp-container">
        <h2>Reset Password</h2>
        <?php
        if (!empty($message)) {
            echo "<p class='otp-sent'>$message</p>";
        }
        if (!empty($error)) {
            echo "<p style='color: red;'>$error</p>";
        }
        ?>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="form-group">
                <label for="email">Enter your email:</label>
                <input type="email" id="email" name="email" class="form-control" value="<?php echo $email; ?>" placeholder="Enter your email" required>
            </div>
            <button type="submit" class="btn-primary">Reset Password</button>
        </form>

        <!-- Password Reset Form -->
        <?php if (isset($_GET['token'])): ?>
            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . "?token=$token"; ?>" method="post">
                <div class="form-group">
                    <label for="password">New Password:</label>
                    <input type="password" id="password" name="password" class="form-control" placeholder="Enter your new password" required>
                </div>
                <div class="form-group">
                    <label for="confirm_password">Confirm Password:</label>
                    <input type="password" id="confirm_password" name="confirm_password" class="form-control" placeholder="Confirm your new password" required>
                </div>
                <button type="submit" class="btn-primary">Reset Password</button>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>
