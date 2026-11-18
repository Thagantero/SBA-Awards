<?php
// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php'; // Ensure this path is correct

session_start();

// Include database connection
require_once "dbConnect.php";

// Function to generate OTP
function generateOTP($length = 6) {
    $otp = "";
    for ($i = 0; $i < $length; $i++) {
        $otp .= mt_rand(0, 9);
    }
    return $otp;
}

// Function to send OTP email
function sendOTPEmail($email, $username, $otp) {
    $mail = new PHPMailer(true);

    try {
        // PHPMailer configuration
        $mail->SMTPDebug = 0;
        $mail->isSMTP();
        $mail->Host = (string) getenv('SBA_SMTP_HOST'); // Your SMTP host
        $mail->SMTPAuth   = true;
        $mail->Username = (string) getenv('SBA_SMTP_USER'); // Your SMTP username
        $mail->Password = (string) getenv('SBA_SMTP_PASSWORD'); // Your SMTP password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );

        // Set email sender and recipient
        $mail->setFrom((string) getenv('SBA_SMTP_FROM'), 'SBA Awards');
        $mail->addAddress($email, $username);

        // Email content
        $mail->isHTML(true);
        $mail->Subject = 'SBA Awards - Your OTP Code';
        $mail->Body    = "<p>Hello <b>$username</b>,</p>
                          <p>Your OTP code is <b>$otp</b></p>
                          <p>Best regards,<br>SBA Awards Team</p>";
        $mail->AltBody = "Hello $username,\n\nYour OTP code is $otp\n\nBest regards,\nSBA Awards Team";

        // Send email
        $mail->send();
        return true;
    } catch (Exception $e) {
        return false;
    }
}

// Handle form submission for login
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['email'], $_POST['password'])) {
    // Validate and sanitize input
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
        header("Location: login.php?error=" . urlencode($error));
        exit();
    }

    // Query to check if the email belongs to an admin
    $queryAdmin = "SELECT * FROM admins WHERE email='$email'";
    $resultAdmin = $mysqli->query($queryAdmin);

    // Check if there are any results in admins table
    if ($resultAdmin->num_rows > 0) {
        $row = $resultAdmin->fetch_assoc();

        // Verify password
        if (password_verify($password, $row['password'])) {
            // Admin login successful
            $_SESSION['username'] = $row['username'];
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['loggedin'] = true;
            $_SESSION['is_admin'] = true;
            header("Location: admin_dashboard.php");
            exit();
        } else {
            // Incorrect password
            $error = "Incorrect password.";
            header("Location: login.php?error=" . urlencode($error));
            exit();
        }
    } else {
        // Query to check if the email belongs to a user
        $queryUser = "SELECT * FROM users WHERE email='$email'";
        $resultUser = $mysqli->query($queryUser);

        // Check if there are any results in users table
        if ($resultUser->num_rows > 0) {
            $row = $resultUser->fetch_assoc();

            // Verify password
            if (password_verify($password, $row['password'])) {
                // User login successful
                if ($row['status'] == 'pending') {
                    // Generate OTP for pending users
                    $otp = generateOTP();
                    if (sendOTPEmail($row['email'], $row['username'], $otp)) {
                        // Update OTP in the database
                        $queryUpdate = "UPDATE users SET otp='$otp' WHERE email='$email'";
                        $mysqli->query($queryUpdate);

                        // Set session variables
                        $_SESSION['username'] = $row['username'];
                        $_SESSION['user_id'] = $row['id'];
                        $_SESSION['otp'] = $otp;
                        header("Location: verify_otp.php?email=" . urlencode($row['email']));
                        exit();
                    } else {
                        // Failed to send OTP
                        $error = "Failed to send OTP.";
                        header("Location: login.php?error=" . urlencode($error));
                        exit();
                    }
                } else {
                    // Regular user login
                    $_SESSION['username'] = $row['username'];
                    $_SESSION['user_id'] = $row['id'];
                    $_SESSION['loggedin'] = true;
                    $_SESSION['is_admin'] = false;
                    header("Location: home.php");
                    exit();
                }
            } else {
                // Incorrect password
                $error = "Incorrect password.";
                header("Location: login.php?error=" . urlencode($error));
                exit();
            }
        } else {
            // Email not found in either users or admins table
            $error = "Email not found.";
            header("Location: login.php?error=" . urlencode($error));
            exit();
        }
    }
} else {
    // Redirect to login page if accessed directly
    header("Location: login.php");
    exit();
}

// Close database connection
$conn->close();
?>
