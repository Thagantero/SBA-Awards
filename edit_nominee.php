<?php
// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Start session and check authentication
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header("Location: login.php");
    exit;
}

// Include database connection
require_once "dbConnect.php";

// Initialize variables
$nomineeId = null;
$nominee = null;

// Fetch nominee data
if (isset($_GET['id'])) {
    $nomineeId = $_GET['id'];
    $sql = "SELECT * FROM nominees WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $nomineeId);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows == 1) {
        $nominee = $result->fetch_assoc();
    } else {
        echo "Nominee not found.";
        exit;
    }
    $stmt->close();
} else {
    echo "Invalid request.";
    exit;
}

// Update nominee data
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_nominee'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    // Add more fields as needed

    $sql = "UPDATE nominees SET name = ?, email = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssi", $name, $email, $nomineeId);
    if ($stmt->execute()) {
        echo "Nominee updated successfully.";
    } else {
        echo "Error updating nominee.";
    }
    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Admin</title>
    <script src="https://kit.fontawesome.com/87ab693041.js" crossorigin="anonymous"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        header {
            background-color: #333;
            color: gold;
            padding: 10px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        header a {
            color: gold;
            text-decoration: none;
            margin-left: 15px;
        }
        .container {
            padding: 20px;
        }
        h1 {
            color: #333;
        }
        form {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        label {
            display: block;
            margin-bottom: 10px;
            color: #333;
        }
        input[type="text"], input[type="email"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        input[type="submit"] {
            background-color: #333;
            color: gold;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        button[type="button"] {
            background-color: #333;
            color: gold;
            padding: 10px 15px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-right: 10px;
        }
        footer {
            background-color: #333;
            color: gold;
            text-align: center;
            padding: 10px 0;
            position: fixed;
            bottom: 0;
            width: 100%;
        }
    </style>
</head>
<body>
    <header>
        <div>
            <a href="home.php"><i class="fas fa-home"></i> Home</a>
        </div>
        <div>
            <a href="profile.php"><i class="fas fa-user-circle"></i> <?php echo $_SESSION['username']; ?></a>
            <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </header>

    <div class="container">
        <h1>Edit Nominee</h1>
        <form action="edit_nominee.php?id=<?php echo $nomineeId; ?>" method="post">
            <label for="name">Name:</label>
            <input type="text" id="name" name="name" value="<?php echo isset($nominee['name']) ? htmlspecialchars($nominee['name']) : ''; ?>" required>

            <label for="email">Email:</label>
            <input type="email" id="email" name="email" value="<?php echo isset($nominee['email']) ? htmlspecialchars($nominee['email']) : ''; ?>" required>

            <!-- Add more fields as needed -->

            <input type="submit" name="update_nominee" value="Update Nominee">
            <a href="admin_dashboard.php">Back to Dashboard</a>
        </form>
    </div>

    <footer>
        &copy; <?php echo date('Y'); ?> SBA Awards. All Rights Reserved.
    </footer>
</body>
</html>
