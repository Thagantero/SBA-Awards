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

// Handle file uploads
$target_dir = "uploads/";
$image_target_file = $target_dir . basename($_FILES["image"]["name"]);
$proof_target_file = $target_dir . basename($_FILES["ownershipProof"]["name"]);
$image_upload_ok = move_uploaded_file($_FILES["image"]["tmp_name"], $image_target_file);
$proof_upload_ok = move_uploaded_file($_FILES["ownershipProof"]["tmp_name"], $proof_target_file);

if (!$image_upload_ok || !$proof_upload_ok) {
    die("Sorry, there was an error uploading your files.");
}

// Insert data into database using prepared statements
$sql = "INSERT INTO nominations (name, email, dob, country, company_name, company_description, business_started_year, facebook_link, twitter_link, instagram_link, website_link, number_of_employees, image_path, ownership_proof_path) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ssssssssssssss", $name, $email, $dob, $country, $company_name, $company_description, $business_started_year, $facebook_link, $twitter_link, $instagram_link, $website_link, $number_of_employees, $image_target_file, $proof_target_file);

if ($stmt->execute()) {
    echo "Nomination submitted successfully";
} else {
    echo "Error: " . $sql . "<br>" . $conn->error;
}

$stmt->close();
$conn->close();
?>
