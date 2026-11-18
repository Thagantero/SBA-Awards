<?php
// Check if session is not started, then start it
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Post</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" integrity="sha512-..." crossorigin="anonymous" />
    <script src="https://kit.fontawesome.com/87ab693041.js" crossorigin="anonymous"></script>

    <style>
        header a {
            color: gold;
            text-decoration: none;
            margin-left: 15px;
        }
        header {
            background-color: #333;
            color: gold;
            padding: 10px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }
        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background-color: #333;
            padding: 10px 20px;
            color: #fff;
        }
        header div {
            display: flex;
            align-items: center;
        }
        header a {
            color: gold;
            text-decoration: none;
            margin-right: 20px;
            display: flex;
            align-items: center;
        }
        header a i {
            margin-right: 8px;
        }
        header a:hover {
            text-decoration: underline;
        }
        .user-info {
            display: flex;
            align-items: center;
        }
        .user-info a {
            margin-left: 20px;
        }
        .logo img {
            max-width: 60px; /* Example: Adjust the max-width as needed */
            height: auto; /* Maintain aspect ratio */
            display: block; /* Ensure proper alignment and spacing */
        }
    </style>
</head>
<body>

<header>
    <div class="logo">
        <img src="http://localhost/SBA-awards/uploads/logo1.png" alt="Logo">
    </div>
    <div>
        <a href="home.php"><i class="fas fa-home"></i> Home</a>
        <a href="admin_dashboard.php"><i class="fas fa-dashboard"></i> Dashboard</a>
        <a href="new_post.php"><i class="fa-solid fa-circle-plus"></i> Add Post</a>
        <a href="posts_page.php"><i class="fa-solid fa-pen-to-square"></i> Edit Post</a>
        <a href="upload.php"><i class="fa-solid fa-upload"></i> Upload</a>
        <a href="uploads.php"><i class="fa-solid fa-file"></i> Uploads</a>
    </div>
    <div class="user-info">
        <a href="profile.php"><i class="fas fa-user-circle"></i> <?php echo htmlspecialchars($_SESSION['username']); ?></a>
        <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>
</header>

</body>
</html>
