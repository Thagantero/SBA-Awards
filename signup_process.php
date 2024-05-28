<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php'; // Ensure this path is correct

// Database connection code
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "sba_awards";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

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
        // Server settings
        $mail->SMTPDebug = 0; // Disable verbose debug output
        $mail->isSMTP(); // Send using SMTP
        $mail->Host       = 'lon105.truehost.cloud'; // Set the SMTP server to send through
        $mail->SMTPAuth   = true; // Enable SMTP authentication
        $mail->Username   = 'sba@sols.co.ke'; // SMTP username
        $mail->Password   = 'sba_awards2024'; // SMTP password
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
        $mail->setFrom('sba@sols.co.ke', 'SBA Awards');
        $mail->addAddress($email, $username); // Add a recipient

        // Content
        $mail->isHTML(true); // Set email format to HTML
        $mail->Subject = 'Your OTP Code';
        $mail->Body    = "Your OTP code is <b>$otp</b>";
        $mail->AltBody = "Your OTP code is $otp";

        $mail->send();
        return "OTP has been sent to $email";
    } catch (Exception $e) {
        return "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }
}

// Check if the form has been submitted
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['username'], $_POST['email'], $_POST['password'])) {
    // Get form data
    $username = $conn->real_escape_string($_POST['username']);
    $email = $conn->real_escape_string($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Check if email already exists
    $checkEmailQuery = "SELECT * FROM users WHERE email = '$email'";
    $checkEmailResult = $conn->query($checkEmailQuery);
    if ($checkEmailResult->num_rows > 0) {
        echo "<div style='color: red;'>Error: This email is already registered</div>";
    } else {
        // Generate OTP
        $otp = generateOTP();

        // Insert data into database
        $sql = "INSERT INTO users (username, email, password, otp) VALUES ('$username', '$email', '$password', '$otp')";

        if ($conn->query($sql) === TRUE) {
            $otpSentMessage = sendOTPEmail($email, $username, $otp);

            // Display OTP verification form
            echo "<div class='otp-container'>";
            echo "<h2>Verify OTP</h2>";
            echo "<p class='otp-sent'>$otpSentMessage</p>";
            echo "<form action='verify_otp.php' method='POST'>";
            echo "<input type='hidden' name='email' value='$email'>";
            echo "<div class='form-group'>";
            echo "<label for='otp'>Enter OTP:</label>";
            echo "<input type='text' class='form-control' id='otp' name='otp' required>";
            echo "</div>";
            echo "<button type='submit' class='btn btn-primary'>Verify OTP</button>";
            echo "</form>";
            echo "<p id='countdown'>Resend OTP in 60 seconds</p>";
            echo "<button id='resend-otp' class='btn btn-secondary' onclick='resendOTP()' style='display: none;'>Resend OTP</button>";
            echo "<p id='resend-confirmation' style='display: none; color: gold;'>OTP has been resent.</p>";
            echo "</div>";
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error;
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
            // Here we would typically make an AJAX request to resend the OTP
            // For demonstration, we just reset the countdown and show a confirmation message
            countdown = 60;
            countdownElement.textContent = `Resend OTP in ${countdown} seconds`;
            countdownElement.style.display = 'block';
            resendOTPButton.style.display = 'none';
            resendConfirmationElement.style.display = 'block';
            startCountdown();

            // Hide the confirmation message after a few seconds
            setTimeout(() => {
                resendConfirmationElement.style.display = 'none';
            }, 5000);
        }

        document.addEventListener('DOMContentLoaded', (event) => {
            startCountdown();
        });
    </script>
</head>
<body>

</body>
</html>
