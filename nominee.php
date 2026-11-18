<?php
// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Start session
session_start();

// Insert Header
include 'header.php';

// Include dbConnect.php
include 'dbConnect.php';

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("Location: login.php");
    exit;
}

$nominee_id = $_GET['id'];

// Fetch nominee details
$stmt = $mysqli->prepare("SELECT * FROM nominees WHERE id = ?");
$stmt->bind_param("i", $nominee_id);
$stmt->execute();
$result = $stmt->get_result();
$nominee = $result->fetch_assoc();

$image = isset($nominee['image']) && !empty($nominee['image']) 
         ? htmlspecialchars($nominee['image']) 
         : 'path/to/default/profile_photo.png'; // Replace with your default image path

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nominee Details</title>
    <script src="https://kit.fontawesome.com/87ab693041.js" crossorigin="anonymous"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #111;
            color: #ffd700; /* gold color */
            margin: 0;
            padding: 0;
        }
        header, footer {
            background-color: gold;
            color: black;
            padding: 10px;
            text-align: center;
        }
        .container {
            max-width: 600px;
            margin: 20px auto;
            padding: 20px;
            background-color: #222;
            border-radius: 10px;
            text-align: center;
        }
        .container h1 {
            margin-bottom: 10px;
        }
        .nominee-image {
            width: 150px; /* Adjust as needed */
            height: 150px; /* Adjust as needed */
            border-radius: 50%;
            margin-bottom: 10px;
        }
        .nominee-details {
            margin-bottom: 20px;
        }
        .nominee-details p {
            margin: 5px 0;
        }
        .vote-button {
            background-color: green;
            color: #fff;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            text-decoration: none;
            cursor: pointer;
        }
        .vote-button:hover {
            background-color: #4CAF50;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1><?php echo htmlspecialchars($nominee['name']); ?></h1>
        <img src="<?php echo $image; ?>" class="nominee-image" alt="Profile Photo">
        <div class="nominee-details">
            <p><strong>Company Name:</strong> <?php echo htmlspecialchars($nominee['companyName']); ?></p>
            <p><strong>Company Description:</strong> <?php echo htmlspecialchars($nominee['companyDescription']); ?></p>
        </div>
        <a href="credits.php?nominee_id=<?php echo $nominee_id; ?>" class="vote-button">Vote</a>
    </div>

    <?php include 'footer.php'; ?>
</body>
</html>
