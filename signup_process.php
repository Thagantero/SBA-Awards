<?php

// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php'; // Ensure this path is correct


// Include dbConnect.php
include 'dbConnect.php';


// Function to generate OTP
function generateOTP($length = 6) {
    $otp = "";
    for ($i = 0; $i < $length; $i++) {
        $otp .= mt_rand(0, 9);
    }
    return $otp;
}

// Function to send OTP email
function sendOTPEmail($email, $otp) {
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->SMTPDebug = 0; // Disable verbose debug output
        $mail->isSMTP(); // Send using SMTP
        $mail->Host = (string) getenv('SBA_SMTP_HOST'); // Set the SMTP server to send through
        $mail->SMTPAuth   = true; // Enable SMTP authentication
        $mail->Username = (string) getenv('SBA_SMTP_USER'); // SMTP username
        $mail->Password = (string) getenv('SBA_SMTP_PASSWORD'); // SMTP password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Enable TLS encryption
        $mail->Port       = 587; // TCP port to connect to
        
        // Disable SSL verification
        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );

        // Recipients
        $mail->setFrom((string) getenv('SBA_SMTP_FROM'), 'SBA Awards');
        $mail->addAddress($email); // Add a recipient

        // Content
        $mail->isHTML(true); // Set email format to HTML
        $mail->Subject = 'Welcome to SBA Awards - Your OTP Code';
        $mail->Body    = "<p>Hello,</p>
                          <p>Welcome to SBA Awards! We're excited to have you join our community.</p>
                          <p>Your OTP code is <b>$otp</b></p>
                          <p>Best regards,<br>SBA Awards Team</p>";
        $mail->AltBody = "Hello,\n\nWelcome to SBA Awards! We're excited to have you join our community.\n\nYour OTP code is $otp\n\nBest regards,\nSBA Awards Team";

        $mail->send();
        return true;
    } catch (Exception $e) {
        return false;
    }
}

// Check if the form has been submitted
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['username'], $_POST['firstname'], $_POST['lastname'], $_POST['email'], $_POST['phone'], $_POST['password'], $_POST['confirm_password'])) {
    // Get form data
    $username = $conn->real_escape_string($_POST['username']);
    $firstname = $conn->real_escape_string($_POST['firstname']);
    $lastname = $conn->real_escape_string($_POST['lastname']);
    $email = $conn->real_escape_string($_POST['email']);
    $phone = $conn->real_escape_string($_POST['phone']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $confirm_password = $_POST['confirm_password'];

    // Check if email already exists
    $checkEmailSql = "SELECT * FROM users WHERE email='$email'";
    $result = $conn->query($checkEmailSql);

    if ($result->num_rows > 0) {
        echo "<div style='color: red;'>Error: Email already exists. Please try with a different email.</div>";
    } else {
        // Check if passwords match
        if ($_POST['password'] !== $_POST['confirm_password']) {
            echo "<div style='color: red;'>Error: Passwords do not match</div>";
        } else {
            // Generate OTP
            $otp = generateOTP();

            // Insert data into database with status 'pending'
            $sql = "INSERT INTO users (username, first_name, last_name, email, phone_number, password, otp, status) VALUES ('$username', '$firstname', '$lastname', '$email', '$phone', '$password', '$otp', 'pending')";

            if ($conn->query($sql) === TRUE) {
                if (sendOTPEmail($email, $otp)) {
                    // Redirect to OTP verification page with email parameter
                    header("Location: verify_otp.php?email=" . urlencode($email));
                    exit();
                } else {
                    echo "<div style='color: red;'>Error: Failed to send OTP email. Please try again.</div>";
                }
            } else {
                echo "<div style='color: red;'>Error: " . $sql . "<br>" . $conn->error . "</div>";
            }
        }
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SBA Awards</title>
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
    <script>
        let countdown = 60;
        const countdownElement = document.getElementById('countdown');
        const resendOTPButton = document.getElementById('resend-otp');
        const resendConfirmationElement = document.getElementById('resend-confirmation');

        function startCountdown() {
            const interval = setInterval(() => {
                countdown--;
                countdownElement.textContent = `Resend OTP in ${countdown} seconds`;

                if (countdown === 0) {
                    clearInterval(interval);
                    countdownElement.style.display = 'none';
                    resendOTPButton.style.display = 'block';
                }
            }, 1000);
        }

        function resendOTP() {
            // AJAX request to resend the OTP
            const email = '<?php echo $email; ?>';
            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'resend_otp.php', true);
            xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
            xhr.onload = function () {
                if (this.status === 200) {
                    // Show confirmation message and restart countdown
                    resendConfirmationElement.style.display = 'block';
                    countdown = 60;
                    countdownElement.textContent = `Resend OTP in ${countdown} seconds`;
                    countdownElement.style.display = 'block';
                    resendOTPButton.style.display = 'none';
                    setTimeout(() => {
                        resendConfirmationElement.style.display = 'none';
                    }, 3000); // Hide confirmation after 3 seconds
                } else {
                    console.error('Error:', this.responseText);
                }
            };
            xhr.send('email=' + encodeURIComponent(email));
        }

        document.addEventListener('DOMContentLoaded', (event) => {
            startCountdown();
        });
    </script>
</head>
<body>

</body>
</html>
