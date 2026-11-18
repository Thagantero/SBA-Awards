<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Require database connection and PHPMailer
require 'dbConnect.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Include the header file
include 'header.php';

require_once __DIR__ . '/vendor/autoload.php';



// Function to send success email
function sendSuccessEmail($email, $name, $ticket_id, $amount, $tickets, $event_details) {
    $mail = new PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = (string) getenv('SBA_SMTP_HOST');
        $mail->SMTPAuth   = true;
        $mail->Username = (string) getenv('SBA_SMTP_USER');
        $mail->Password = (string) getenv('SBA_SMTP_PASSWORD');
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
        $mail->setFrom((string) getenv('SBA_SMTP_FROM'), 'SBA Awards');
        $mail->addAddress($email, $name);

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Ticket Purchase Confirmation';
        $mail->Body    = "Dear $name,<br><br>Thank you for your purchase!<br>Your Ticket ID: $ticket_id<br>Total Amount: $$amount<br>Number of Tickets: $tickets<br>Event Details: $event_details<br><br>Sincerely,<br>SBA Awards Team";

        $mail->send();

        return true; // Return true on successful sending
    } catch (Exception $e) {
        // Handle the error appropriately
        error_log("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
        return false; // Return false on failure
    }
}

// Initialize variables
$name = '';
$phone = '';
$email = '';
$tickets = 0;
$amount = 0;
$ticket_id = '';

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Validate and sanitize inputs
    $name = isset($_POST['name']) ? htmlspecialchars($_POST['name']) : '';
    $phone = isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : '';
    $email = isset($_POST['email']) ? htmlspecialchars($_POST['email']) : '';
    $tickets = isset($_POST['tickets']) ? intval($_POST['tickets']) : 0;

    // Validate inputs
    $errors = [];
    if (empty($name)) {
        $errors[] = "Name is required";
    }
    if (empty($phone)) {
        $errors[] = "Phone number is required";
    }
    if (empty($email)) {
        $errors[] = "Email is required";
    }
    if ($tickets <= 0) {
        $errors[] = "Number of tickets must be greater than zero";
    }

    // If no errors, proceed with ticket purchase
    if (empty($errors)) {
        // Calculate amount
        $amount = $tickets * 5; // $5 per ticket

        // Generate unique ticket ID (8 characters alphanumeric)
        $ticket_id = substr(md5(uniqid(mt_rand(), true)), 0, 8);

        // Insert ticket into database
        $stmt = $mysqli->prepare("INSERT INTO tickets (ticket_id, name, phone, email, tickets, amount) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssid", $ticket_id, $name, $phone, $email, $tickets, $amount);
        $stmt->execute();

        // Event details
        $event_details = "SBA Trophy Awarding Ceremony"; // Replace with actual event details

        // Send success email
        if (sendSuccessEmail($email, $name, $ticket_id, $amount, $tickets, $event_details)) {
            // Email sent successfully
            $success_message = "$tickets purchased successfully. Ticket ID: $ticket_id. An email has been sent to $email with ticket details.";
        } else {
            // Email sending failed
            $success_message = "$tickets purchased successfully. Ticket ID: $ticket_id.. However, there was an issue sending the email. Please contact support.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Purchase Tickets - SBA Awards</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: black;
            color: gold;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .container {
            width: 80%;
            max-width: 600px;
            padding: 20px;
            background-color: #111;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.2);
        }
        .container h2 {
            text-align: center;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
        }
        .form-group input {
            width: 100%;
            padding: 8px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        .form-group .error {
            color: red;
            font-size: 14px;
        }
        .form-group button {
            background-color: gold;
            color: black;
            border: none;
            border-radius: 5px;
            padding: 10px 15px;
            cursor: pointer;
        }
        .form-group button:hover {
            background-color: #f7d32e;
        }
        .success-message {
            margin-top: 20px;
            padding: 10px;
            background-color: #4CAF50;
            color: white;
            text-align: center;
            border-radius: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Purchase Tickets</h2>
        <?php if (isset($success_message)): ?>
            <div class="success-message">
                <?php echo $success_message; ?>
            </div>
        <?php else: ?>
            <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">
                <div class="form-group">
                    <label for="name">Name:</label>
                    <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($name); ?>">
                </div>
                <div class="form-group">
                    <label for="phone">Phone:</label>
                    <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($phone); ?>">
                </div>
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>">
                </div>
                <div class="form-group">
                    <label for="tickets">Number of Tickets:</label>
                    <input type="number" id="tickets" name="tickets" value="<?php echo htmlspecialchars($tickets); ?>" min="1">
                </div>
                <div class="form-group">
                    <button type="submit">Buy Tickets</button>
                </div>
            </form>
        <?php endif; ?>
    </div>
</body>
</html>
