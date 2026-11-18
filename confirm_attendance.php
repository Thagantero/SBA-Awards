<?php
require 'dbConnect.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/vendor/autoload.php';



// Function to send confirmation email
function sendConfirmationEmail($email, $name, $ticket_id, $event_details) {
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
        $mail->Subject = 'Attendance Confirmation';
        $mail->Body    = "Dear $name,<br><br>Your attendance has been confirmed successfully.<br>Ticket ID: $ticket_id<br>Event Details: $event_details<br><br>Welcome and enjoy the event!<br><br>Sincerely,<br>SBA Awards Team";

        $mail->send();
    } catch (Exception $e) {
        // Handle the error appropriately
        error_log("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
    }
}

include 'header.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirm Attendance - SBA Awards</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: black;
            color: gold;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #111;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.5);
            text-align: center;
        }
        p {
            margin: 10px 0;
        }
        button {
            background-color: gold;
            color: black;
            border: none;
            border-radius: 5px;
            padding: 10px 20px;
            cursor: pointer;
            margin-top: 20px;
        }
        button:hover {
            background-color: #f7d32e;
        }
        a {
            color: black;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <?php
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $ticket_id = htmlspecialchars($_POST['ticket_id']);

            // Check if ticket exists and is valid
            $stmt = $mysqli->prepare("SELECT name, email, status FROM tickets WHERE ticket_id = ?");
            $stmt->bind_param("s", $ticket_id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $ticket = $result->fetch_assoc();
                if ($ticket['status'] == 'valid') {
                    // Mark ticket as used
                    $update_stmt = $mysqli->prepare("UPDATE tickets SET status = 'used' WHERE ticket_id = ?");
                    $update_stmt->bind_param("s", $ticket_id);
                    $update_stmt->execute();

                    $name = $ticket['name'];
                    $email = $ticket['email'];
                    $event_details = "SBA Awards Event Details Here"; // Replace with actual event details

                    // Send confirmation email
                    sendConfirmationEmail($email, $name, $ticket_id, $event_details);

                    echo "<p>Attendance confirmed. Ticket ID: " . htmlspecialchars($ticket_id) . " is now marked as used. An email has been sent to $email with the event details.</p>";
                } else {
                    echo "<p>This ticket is already used.</p>";
                }
            } else {
                echo "<p>Ticket not found.</p>";
            }
        } else {
            echo "<p>Invalid request.</p>";
        }
        ?>
        <button onclick="window.location.href='verify_ticket.php'">Verify Another Ticket</button>
    </div>
</body>
</html>
