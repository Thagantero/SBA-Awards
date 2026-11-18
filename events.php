<?php
// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Start or resume session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Include the header file
include 'header.php';

// Database connection
require 'dbConnect.php';

// Initialize variables
$events = []; // Array to store fetched events

// Fetch events from the database
$stmt = $mysqli->prepare("SELECT id, name, description, date, time, location, google_location, event_image, dress_code, theme FROM events ORDER BY date DESC");
$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $events[] = $row;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Events - SBA Awards</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: black;
            color: gold;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        header, footer {
            background-color: gold;
            color: black;
            padding: 10px;
            text-align: center;
        }

        .container {
            width: 80%;
            margin: 70px auto 20px;
            padding: 20px;
            background-color: #111;
            border-radius: 10px;
            flex: 1;
        }

        .event {
            border-bottom: 1px solid gold;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }

        .event img {
            max-width: 100px;
            height: auto;
            margin-right: 20px;
            border-radius: 5px;
        }

        .event .details {
            flex: 1;
        }

        .event .details h3 {
            margin-top: 0;
            margin-bottom: 5px;
        }

        .event .details p {
            margin: 5px 0;
        }

        .location-link {
            color: blue; /* Style link as per your design */
            text-decoration: underline;
            cursor: pointer;
            display: block;
            margin-bottom: 10px;
        }

        .location-link:hover {
            text-decoration: none;
        }

        .buy-ticket {
            background-color: gold;
            color: black;
            border: none;
            border-radius: 5px;
            padding: 8px 15px;
            cursor: pointer;
        }

        .buy-ticket:hover {
            background-color: #f7d32e;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Events</h2>
    <?php foreach ($events as $event): ?>
        <div class="event">
            <img src="<?php echo htmlspecialchars($event['event_image']); ?>" alt="Event Image">
            <div class="details">
                <h3><?php echo htmlspecialchars($event['name']); ?></h3>
                <p><strong>Date:</strong> <?php echo htmlspecialchars($event['date']); ?></p>
                <p><strong>Time:</strong> <?php echo htmlspecialchars($event['time']); ?></p>
                <p><strong>Location:</strong> <?php echo htmlspecialchars($event['location']); ?></p>
                <p><strong>Description:</strong> <?php echo htmlspecialchars($event['description']); ?></p>
                <p><strong>Dress Code:</strong> <?php echo htmlspecialchars($event['dress_code']); ?></p>
                <p><strong>Theme:</strong> <?php echo htmlspecialchars($event['theme']); ?></p>
                <?php if (!empty($event['google_location'])): ?>
                    <p><strong>Google Location:</strong> 
                        <a href="<?php echo htmlspecialchars($event['google_location']); ?>" target="_blank" class="location-link">
                            View on Google Maps
                        </a>
                    </p>
                <?php endif; ?>
<form action="purchase_ticket.php" method="post">
    <input type="hidden" name="event_id" value="<?php echo htmlspecialchars($event['id']); ?>">
    <button type="submit" class="buy-ticket">Buy Ticket</button>
</form>

            </div>
        </div>
    <?php endforeach; ?>
</div>

<?php include 'footer.php'; ?>

</body>
</html>
