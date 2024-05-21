<?php
$servername = "localhost";
$username = "root"; // default WAMP server username
$password = ""; // default WAMP server password
$dbname = "sba_awards";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get form data
$name = $_POST['name'];
$email = $_POST['email'];
$dob = $_POST['dob'];
$country = $_POST['country'];
$company_name = $_POST['companyName'];
$company_description = $_POST['companyDescription'];
$business_started_year = $_POST['businessStartedYear'];
$facebook_link = $_POST['facebook'];
$twitter_link = $_POST['twitter'];
$instagram_link = $_POST['instagram'];
$website_link = $_POST['website'];
$number_of_employees = $_POST['employees'];
$image = $_FILES['image'];
$ownership_proof = $_FILES['ownershipProof'];

// Handle file uploads
$target_dir = "uploads/";
if (!file_exists($target_dir)) {
    mkdir($target_dir, 0777, true);
}

$image_target_file = $target_dir . basename($image["name"]);
if (move_uploaded_file($image["tmp_name"], $image_target_file)) {
    $image_path = $image_target_file;
} else {
    die("Sorry, there was an error uploading your image file.");
}

$proof_target_file = $target_dir . basename($ownership_proof["name"]);
if (move_uploaded_file($ownership_proof["tmp_name"], $proof_target_file)) {
    $ownership_proof_path = $proof_target_file;
} else {
    die("Sorry, there was an error uploading your proof of ownership file.");
}

// Insert data into database
$sql = "INSERT INTO nominations (name, email, dob, country, company_name, company_description, business_started_year, facebook_link, twitter_link, instagram_link, website_link, number_of_employees, image_path, ownership_proof_path) 
        VALUES ('$name', '$email', '$dob', '$country', '$company_name', '$company_description', '$business_started_year', '$facebook_link', '$twitter_link', '$instagram_link', '$website_link', '$number_of_employees', '$image_path', '$ownership_proof_path')";

if ($conn->query($sql) === TRUE) {
    echo "Nomination submitted successfully";
} else {
    echo "Error: " . $sql . "<br>" . $conn->error;
}

$conn->close();
?>
