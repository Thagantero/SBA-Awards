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
        $mail->isSMTP(); 
        $mail->Host       = 'lon105.truehost.cloud'; 
        $mail->SMTPAuth   = true; 
        $mail->Username   = 'sba@sols.co.ke'; 
        $mail->Password   = 'sba_awards2024'; 
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; 
        $mail->Port       = 587; 
        
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
        $mail->addAddress($email, $username); 

        // Content
        $mail->isHTML(true); 
        $mail->Subject = 'Your OTP Code';
        $mail->Body    = "Your OTP code is <b>$otp</b>";
        $mail->AltBody = "Your OTP code is $otp";

        $mail->send();
        return "OTP has been sent to $email";
    } catch (Exception $e) {
        return "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['email'])) {
    $email = $conn->real_escape_string($_POST['email']);

    // Fetch user details
    $userQuery = "SELECT * FROM users WHERE email='$email'";
    $userResult = $conn->query($userQuery);

    if ($userResult->num_rows > 0) {
        $user = $userResult->fetch_assoc();
        $username = $user['username'];

        // Generate a new OTP
        $otp = generateOTP();

        // Update the OTP in the database
        $updateOtpQuery = "UPDATE users SET otp='$otp' WHERE email='$email'";
        if ($conn->query($updateOtpQuery) === TRUE) {
            // Send the new OTP to the user's email
            $otpSentMessage = sendOTPEmail($email, $username, $otp);
            echo json_encode(["message" => $otpSentMessage, "success" => true]);
        } else {
            echo json_encode(["message" => "Error updating OTP", "success" => false]);
        }
    } else {
        echo json_encode(["message" => "Email not found", "success" => false]);
    }
}

$conn->close();
?>
