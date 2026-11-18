<?php
require 'dbConnect.php';
// Include the header file
include 'header.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Ticket - SBA Awards</title>
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
        }
        input[type="text"] {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid gold;
            border-radius: 5px;
            background-color: #222;
            color: gold;
        }
        button {
            background-color: gold;
            color: black;
            border: none;
            border-radius: 5px;
            padding: 10px 20px;
            cursor: pointer;
        }
        button:hover {
            background-color: #f7d32e;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Verify Ticket</h2>
        <form method="post" action="">
            <input type="text" name="ticket_id" placeholder="Enter Ticket Number" required>
            <button type="submit">Verify</button>
        </form>
        <?php
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $ticket_id = htmlspecialchars($_POST['ticket_id']);

            // Fetch ticket details from the database
            $stmt = $mysqli->prepare("SELECT * FROM tickets WHERE ticket_id = ?");
            $stmt->bind_param("s", $ticket_id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $ticket = $result->fetch_assoc();
                echo "<h3>Ticket Information</h3>";
                echo "<p><strong>Ticket ID:</strong> " . htmlspecialchars($ticket['ticket_id']) . "</p>";
                echo "<p><strong>Name:</strong> " . htmlspecialchars($ticket['name']) . "</p>";
                echo "<p><strong>Phone:</strong> " . htmlspecialchars($ticket['phone']) . "</p>";
                echo "<p><strong>Email:</strong> " . htmlspecialchars($ticket['email']) . "</p>";
                echo "<p><strong>Number of Tickets:</strong> " . htmlspecialchars($ticket['tickets']) . "</p>";
                echo "<p><strong>Amount:</strong> " . htmlspecialchars($ticket['amount']) . "</p>";
                echo "<p><strong>Status:</strong> " . htmlspecialchars($ticket['status']) . "</p>";

                if ($ticket['status'] == 'valid') {
                    echo '<form method="post" action="confirm_attendance.php" style="display:inline;">
                            <input type="hidden" name="ticket_id" value="' . htmlspecialchars($ticket['ticket_id']) . '">
                            <button type="submit">Confirm Attendance</button>
                          </form>';
                } else {
                    echo "<p>This ticket is already used.</p>";
                }
            } else {
                echo "<p>Ticket not found.</p>";
            }
        }
        ?>
    </div>
</body>
</html>
