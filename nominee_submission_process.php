<?php
$servername = "localhost";
$username = "root"; // default WAMP server username
$password = "starbecky"; // default WAMP server password
$dbname = "sba-awards";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get form data
$name = $_POST['Name'];
$email = $_POST['Email'];
$dob = $_POST['Dob'];
$category = $_POST['Category'];
$country = $_POST['Country'];
$company_name = $_POST['CompanyName'];
$company_description = $_POST['CompanyDescription'];
$business_started_year = $_POST['BusinessStartedYear'];
$facebook_link = $_POST['Facebook'];
$twitter_link = $_POST['Twitter'];
$instagram_link = $_POST['Instagram'];
$website_link = $_POST['Website'];
$number_of_employees = $_POST['Employees'];

// Handle file uploads
$target_dir = "uploads/";
$image_target_file = $target_dir . basename($_FILES["Image"]["name"]);
$proof_target_file = $target_dir . basename($_FILES["OwnershipProof"]["name"]);
$image_upload_ok = move_uploaded_file($_FILES["Image"]["tmp_name"], $image_target_file);
$proof_upload_ok = move_uploaded_file($_FILES["OwnershipProof"]["tmp_name"], $proof_target_file);

if (!$image_upload_ok || !$proof_upload_ok) {
    die("Sorry, there was an error uploading your files.");
}

// Insert data into database using prepared statements
$sql = "INSERT INTO candidate (FullName, Email, BirthDate,Category, Country, CompanyName, CompanyDescription, InitiationYear, FacebookLink, TwitterLink, InstagramLink, WebsiteLink, EmployeeNumber, Photo, CompanyProof) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?,?)";

$stmt = $conn->prepare($sql);
$stmt->bind_param("sssssssssssssss", $name, $email, $dob, $category, $country, $company_name, $company_description, $business_started_year, $facebook_link, $twitter_link, $instagram_link, $website_link, $number_of_employees, $image_target_file, $proof_target_file);

if ($stmt->execute()) {
    echo "Nomination submitted successfully";
} else {
    echo "Error: " . $sql . "<br>" . $conn->error;
}

$stmt->close();
$conn->close();
