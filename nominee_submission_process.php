<?php
require 'vendor/autoload.php'; // Add this line to include Composer's autoloader

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$servername = (getenv('SBA_DB_HOST') ?: 'localhost');
$username = (string) getenv('SBA_DB_USER');
$password = (string) getenv('SBA_DB_PASSWORD');
$dbname = (string) getenv('SBA_DB_NAME');

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
        // Send nomination success email
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host = (string) getenv('SBA_SMTP_HOST'); // Update SMTP host
        $mail->SMTPAuth   = true; // Enable SMTP authentication
        $mail->Username = (string) getenv('SBA_SMTP_USER'); // Update SMTP username
        $mail->Password = (string) getenv('SBA_SMTP_PASSWORD'); // Update SMTP password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Update SMTP encryption
        $mail->Port       = 587; // Update SMTP port
        
        // Sender information
        $mail->setFrom((string) getenv('SBA_SMTP_FROM'), 'SBA Awards');
        
        // Get the recipient's email address from the form data
        $recipient_email = $email;
        
        // Set the recipient email address
        $mail->addAddress($recipient_email);
        
        // Email subject
        $mail->Subject = 'Nomination Success';
        
        // Email body
        $mail->Body = 'Dear ' . $name . ',<br><br>Your nomination has been successfully submitted. Thank you for your participation.<br><br>Regards,<br>SBA Awards';
        
        // Set email format to HTML
        $mail->isHTML(true);
        
        // Send email
        if (!$mail->send()) {
            echo 'Message could not be sent.';
            echo 'Mailer Error: ' . $mail->ErrorInfo;
        } else {
            echo "<style>
                    body {
                        font-family: Arial, sans-serif;
                        background-color: #f8f9fa; /* Light gray background */
                    }
                    
                    .container {
                        max-width: 800px;
                        margin: 0 auto;
                        padding: 20px;
                        text-align: center;
                        margin-top: 20px;
                        background-color: #fff; /* White background */
                        border-radius: 10px;
                        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); /* Soft shadow */
                    }
                    
                    h2 {
                        color: #333;
                    }
                    
                    p {
                        color: #666;
                        margin-bottom: 10px;
                    }
                    
                    .btn {
                        display: inline-block;
                        padding: 10px 20px;
                        background-color: #ffc107; /* Gold color */
                        color: #000; /* Black color */
                        text-decoration: none;
                        border-radius: 5px;
                        transition: background-color 0.3s ease; /* Smooth transition */
                    }
                    
                    .btn:hover {
                        background-color: #ffca28; /* Darker gold color on hover */
                    }
                </style>";
            echo "<div class='container'>";
            echo "<h2>Thank You for Your Nomination!</h2>";
            echo "<p>Your nomination has been successfully submitted.</p>";
            echo "<p><a href='nominate.php' class='btn'>Submit another nomination</a></p>";
            echo "</div>";
        }
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}

$conn->close();
?>
