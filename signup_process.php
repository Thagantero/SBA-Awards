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

// Check if the form has been submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $username = $conn->real_escape_string($_POST['username']);
    $email = $conn->real_escape_string($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Generate OTP
    $otp = generateOTP();

    // Insert data into database
    $sql = "INSERT INTO users (username, email, password, otp) VALUES ('$username', '$email', '$password', '$otp')";

    if ($conn->query($sql) === TRUE) {
        // Send OTP email using PHPMailer
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
            echo 'OTP has been sent';

            // Display OTP verification form
            echo "<div style='text-align: center; margin-top: 20px;'>";
            echo "<h2>Verify OTP</h2>";
            echo "<form action='verify_otp.php' method='POST'>";
            echo "<input type='hidden' name='email' value='$email'>";
            echo "<div class='form-group'>";
            echo "<label for='otp'>Enter OTP:</label>";
            echo "<input type='text' class='form-control' id='otp' name='otp' required>";
            echo "</div>";
            echo "<button type='submit' class='btn btn-primary'>Verify OTP</button>";
            echo "</form>";
            echo "</div>";
            
        } catch (Exception $e) {
            echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }

    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}

$conn->close();
?>
