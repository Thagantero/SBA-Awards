<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "sba_awards";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get form data
    $name = $conn->real_escape_string($_POST['name']);
    $email = $conn->real_escape_string($_POST['email']);
    $dob = $conn->real_escape_string($_POST['dob']);
    $country = $conn->real_escape_string($_POST['country']);
    $companyName = $conn->real_escape_string($_POST['companyName']);
    $companyDescription = $conn->real_escape_string($_POST['companyDescription']);
    $businessStartedYear = $conn->real_escape_string($_POST['businessStartedYear']);
    $facebook = $conn->real_escape_string($_POST['facebook']);
    $twitter = $conn->real_escape_string($_POST['twitter']);
    $instagram = $conn->real_escape_string($_POST['instagram']);
    $website = $conn->real_escape_string($_POST['website']);
    $employees = $conn->real_escape_string($_POST['employees']);
    $category = $conn->real_escape_string($_POST['category']); // Retrieve category from form data
    
    // Handle file uploads
    $image = $_FILES['image'];
    $ownershipProof = $_FILES['ownershipProof'];
    
    // Check and create directories if they don't exist
    $imageDirectory = 'uploads/images/';
    $ownershipProofDirectory = 'uploads/ownership_proofs/';
    
    if (!file_exists($imageDirectory)) {
        mkdir($imageDirectory, 0777, true); // Create the directory recursively
    }
    
    if (!file_exists($ownershipProofDirectory)) {
        mkdir($ownershipProofDirectory, 0777, true); // Create the directory recursively
    }
    
    // Check and move image file
    $imagePath = $imageDirectory . basename($image['name']);
    if (!move_uploaded_file($image['tmp_name'], $imagePath)) {
        die("Failed to upload image.");
    }
    
    // Check and move ownership proof file
    $ownershipProofPath = $ownershipProofDirectory . basename($ownershipProof['name']);
    if (!move_uploaded_file($ownershipProof['tmp_name'], $ownershipProofPath)) {
        die("Failed to upload ownership proof.");
    }
    
    // Insert data into database
    $sql = "INSERT INTO nominees (name, email, dob, country, companyName, companyDescription, businessStartedYear, facebook, twitter, instagram, website, employees, image, ownershipProof, category) 
            VALUES ('$name', '$email', '$dob', '$country', '$companyName', '$companyDescription', '$businessStartedYear', '$facebook', '$twitter', '$instagram', '$website', '$employees', '$imagePath', '$ownershipProofPath', '$category')"; // Include category in the INSERT statement
    
    if ($conn->query($sql) === TRUE) {
        echo "<div style='text-align: center; margin-top: 20px;'>";
        echo "<h2>Thank You for Your Nomination!</h2>";
        echo "<p>Your nomination has been successfully submitted.</p>";
        echo "<p><a href='nominate.html'>Submit another nomination</a></p>";
        echo "</div>";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}

$conn->close();
?>
