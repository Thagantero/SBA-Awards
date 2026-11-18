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


// Initialize variables for form inputs and error messages
$name = $description = $date = $time = $location = $google_location = $dress_code = $theme = '';
$image_upload_message = '';

// Check for form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $description = $_POST['description'];
    $date = $_POST['date'];
    $time = $_POST['time'];
    $location = $_POST['location'];
    $google_location = $_POST['google_location'];
    $dress_code = $_POST['dress_code'];
    $theme = $_POST['theme'];

    // Handle the event image upload
    $target_dir = "uploads/";
    $target_file = $target_dir . basename($_FILES["event_image"]["name"]);
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    // Check if image file is an actual image or fake image
    $check = getimagesize($_FILES["event_image"]["tmp_name"]);
    if ($check === false) {
        $image_upload_message = "File is not an image.";
    }

    // Check file size (limit to 2MB)
    elseif ($_FILES["event_image"]["size"] > 2000000) {
        $image_upload_message = "Sorry, your file is too large.";
    }

    // Allow certain file formats
    elseif ($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif") {
        $image_upload_message = "Sorry, only JPG, JPEG, PNG & GIF files are allowed.";
    }

    // Upload the file if no errors
    else {
        if (move_uploaded_file($_FILES["event_image"]["tmp_name"], $target_file)) {
            // Insert event into the database
            $stmt = $mysqli->prepare("INSERT INTO events (name, description, date, time, location, google_location, event_image, dress_code, theme) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sssssssss", $name, $description, $date, $time, $location, $google_location, $target_file, $dress_code, $theme);

            if ($stmt->execute()) {
                $image_upload_message = "Event created successfully.";
                // Clear form inputs after successful submission
                $name = $description = $date = $time = $location = $google_location = $dress_code = $theme = '';
            } else {
                $image_upload_message = "Error: " . $stmt->error;
            }
        } else {
            $image_upload_message = "Sorry, there was an error uploading your file.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Event - SBA Awards</title>
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
            width: 50%;
            margin: 70px auto 20px;
            padding: 20px;
            background-color: #111;
            border-radius: 10px;
            flex: 1;
        }

        form {
            display: flex;
            flex-direction: column;
        }

        label {
            margin-bottom: 10px;
            color: gold;
        }

        input, textarea {
            margin-bottom: 20px;
            padding: 10px;
            border-radius: 5px;
            border: none;
            outline: none;
            background-color: #333;
            color: gold;
        }

        button {
            padding: 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            background-color: gold;
            color: black;
            font-size: 16px;
        }

        button:hover {
            background-color: #f7d32e;
        }

        .message {
            margin-top: 10px;
            color: gold;
        }
    </style>
</head>
<body>

<div class="container">
    <h2>Create Event</h2>
    <?php if (!empty($image_upload_message)): ?>
        <p class="message"><?php echo htmlspecialchars($image_upload_message); ?></p>
    <?php endif; ?>
    <form method="POST" action="" enctype="multipart/form-data">
        <label for="name">Event Name:</label>
        <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($name); ?>" required>

        <label for="description">Description:</label>
        <textarea id="description" name="description" rows="4" required><?php echo htmlspecialchars($description); ?></textarea>

        <label for="date">Date:</label>
        <input type="date" id="date" name="date" value="<?php echo htmlspecialchars($date); ?>" required>

        <label for="time">Time:</label>
        <input type="time" id="time" name="time" value="<?php echo htmlspecialchars($time); ?>" required>

        <label for="location">Location:</label>
        <input type="text" id="location" name="location" value="<?php echo htmlspecialchars($location); ?>" required>

        <label for="google_location">Google Maps Location (Link):</label>
        <input type="text" id="google_location" name="google_location" value="<?php echo htmlspecialchars($google_location); ?>" placeholder="Enter Google Maps link" required>

        <label for="event_image">Event Image:</label>
        <input type="file" id="event_image" name="event_image" accept="image/*" required>

        <label for="dress_code">Dress Code:</label>
        <input type="text" id="dress_code" name="dress_code" value="<?php echo htmlspecialchars($dress_code); ?>" required>

        <label for="theme">Theme:</label>
        <input type="text" id="theme" name="theme" value="<?php echo htmlspecialchars($theme); ?>" required>

        <button type="submit">Create Event</button>
    </form>
</div>

<?php include 'footer.php'; ?>

</body>
</html>
