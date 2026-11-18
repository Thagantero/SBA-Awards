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

// Initialize arrays
$nominees = [];
$admins = [];
$users = [];

// Fetch nominees data
$sql = "SELECT * FROM nominees";
$result = $mysqli->query($sql);
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $nominees[] = $row;
    }
} else {
    echo "Error fetching nominees: " . $mysqli->error;
}

// Fetch admins data
$sql = "SELECT * FROM admins";
$result = $mysqli->query($sql);
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $admins[] = $row;
    }
} else {
    echo "Error fetching admins: " . $mysqli->error;
}

// Fetch users data
$sql = "SELECT * FROM users";
$result = $mysqli->query($sql);
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $users[] = $row;
    }
} else {
    echo "Error fetching users: " . $mysqli->error;
}

$mysqli->close();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Users</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" integrity="sha512-..." crossorigin="anonymous" />
    <script src="https://kit.fontawesome.com/87ab693041.js" crossorigin="anonymous"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 20px;
            background-color: #fff;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            border-radius: 8px;
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
        header .user-info {
            display: flex;
            align-items: center;
        }
        header .user-info a {
            margin-left: 10px;
        }
        h1 {
            color: #333;
        }
        h2 {
            color: #333;
            margin-top: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table, th, td {
            border: 1px solid #ccc;
        }
        th, td {
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #333;
            color: gold;
        }
        a.edit, a.delete {
            color: blue;
            text-decoration: none;
            margin-right: 5px;
        }
        a.delete {
            color: red;
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
        form {
            margin-top: 20px;
            background-color: #f9f9f9;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 5px rgba(0,0,0,0.1);
        }
        form label {
            display: block;
            margin-bottom: 10px;
            color: #333;
        }
        form input[type="text"], form textarea {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ccc;
            border-radius: 4px;
            box-sizing: border-box;
        }
        form input[type="file"] {
            margin-bottom: 20px;
        }
        form input[type="submit"] {
            background-color: #333;
            color: #fff;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        form input[type="submit"]:hover {
            background-color: #555;
        }
        .file-drop-area {
            position: relative;
            border: 2px dashed #ccc;
            padding: 20px;
            text-align: center;
            cursor: pointer;
            margin-bottom: 20px;
        }
        .file-drop-area input[type="file"] {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            opacity: 0;
            cursor: pointer;
        }
        .file-drop-area.dragover {
            background-color: #f0f0f0;
        }
    </style>
</head>
<body>
    <header>
        <div>
            <a href="home.php"><i class="fas fa-home"></i> Home</a>
            <a href="add_post"><i class="fa-solid fa-circle-plus"></i> Add Post</a>
            <a href="posts_page.php"><i class="fa-solid fa-pen-to-square"></i> Edit Post</a>
            <a href="home.php"><i class="fa-solid fa-upload"></i> Upload</a>
        </div>
        <div class="user-info">
            <a href="profile.php"><i class="fas fa-user-circle"></i> <?php echo $_SESSION['username']; ?></a>
            <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </header>

    <div class="container">
        <h1>Users</h1>

      

        <h2>Nominees</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($nominees as $nominee): ?>
                    <tr>
                        <td><?php echo $nominee['id']; ?></td>
                        <td><?php echo $nominee['name']; ?></td>
                        <td><?php echo $nominee['email']; ?></td>
                        <td>
                            <a href="edit_nominee.php?id=<?php echo $nominee['id']; ?>" class="edit"><i class="fas fa-edit"></i> Edit</a>
                            <a href="delete_nominee.php?id=<?php echo $nominee['id']; ?>" class="delete" onclick="return confirm('Are you sure you want to delete this nominee?');"><i class="fas fa-trash-alt"></i> Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <h2>Admins</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($admins as $admin): ?>
                    <tr>
                        <td><?php echo $admin['id']; ?></td>
                        <td><?php echo $admin['username']; ?></td>
                        <td><?php echo $admin['email']; ?></td>
                        <td>
                            <a href="edit_admin.php?id=<?php echo $admin['id']; ?>" class="edit"><i class="fas fa-edit"></i> Edit</a>
                            <a href="delete_admin.php?id=<?php echo $admin['id']; ?>" class="delete" onclick="return confirm('Are you sure you want to delete this admin?');"><i class="fas fa-trash-alt"></i> Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <h2>Users</h2>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Email</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?php echo $user['id']; ?></td>
                        <td><?php echo $user['username']; ?></td>
                        <td><?php echo $user['email']; ?></td>
                        <td>
                            <a href="edit_user.php?id=<?php echo $user['id']; ?>" class="edit"><i class="fas fa-edit"></i> Edit</a>
                            <a href="delete_user.php?id=<?php echo $user['id']; ?>" class="delete" onclick="return confirm('Are you sure you want to delete this user?');"><i class="fas fa-trash-alt"></i> Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <footer>
        &copy; <?php echo date('Y'); ?> SBA Awards. All Rights Reserved.
    </footer>
</body>
</html>
