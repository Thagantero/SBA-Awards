<?php
// Include database connection
include 'dbConnect.php';

// Retrieve non-hashed passwords from the users table
$sqlUsers = "SELECT id, password FROM users";
$resultUsers = $conn->query($sqlUsers);

if ($resultUsers->num_rows > 0) {
    while ($row = $resultUsers->fetch_assoc()) {
        // Hash the password
        $hashedPassword = password_hash($row['password'], PASSWORD_DEFAULT);

        // Update the password in the users table
        $updateSql = "UPDATE users SET password='$hashedPassword' WHERE id=" . $row['id'];
        if ($conn->query($updateSql) === TRUE) {
            echo "Password updated successfully for user ID: " . $row['id'] . "\n";
        } else {
            echo "Error updating password for user ID: " . $row['id'] . " - " . $conn->error . "\n";
        }
    }
} else {
    echo "No users found in the users table.\n";
}

// Retrieve non-hashed passwords from the admins table
$sqlAdmins = "SELECT id, password FROM admins";
$resultAdmins = $conn->query($sqlAdmins);

if ($resultAdmins->num_rows > 0) {
    while ($row = $resultAdmins->fetch_assoc()) {
        // Hash the password
        $hashedPassword = password_hash($row['password'], PASSWORD_DEFAULT);

        // Update the password in the admins table
        $updateSql = "UPDATE admins SET password='$hashedPassword' WHERE id=" . $row['id'];
        if ($conn->query($updateSql) === TRUE) {
            echo "Password updated successfully for admin ID: " . $row['id'] . "\n";
        } else {
            echo "Error updating password for admin ID: " . $row['id'] . " - " . $conn->error . "\n";
        }
    }
} else {
    echo "No users found in the admins table.\n";
}

// Close database connection
$conn->close();
?>
