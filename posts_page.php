<?php
// Start the session
session_start();

// Check if the user is logged in and is an admin
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true || !isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header("Location: login.php");
    exit;
}

// Include database connection
require_once "dbConnect.php";

// Fetch all blog posts
$sql = "SELECT * FROM blog_posts";
$result = $mysqli->query($sql);

// Check if posts are found
$posts = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $posts[] = $row;
    }
}
// Include admin Header
include "admin_header.php"
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blog Posts</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" integrity="sha512-..." crossorigin="anonymous" />
    <script src="https://kit.fontawesome.com/87ab693041.js" crossorigin="anonymous"></script>
    <style>
        /* Your existing styles */
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
        a.edit {
            color: blue;
            text-decoration: none;
            margin-right: 5px;
        }
        a.delete {
            color: red;
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
            margin-left: 20px;
        }
        .user-info {
            display: flex;
            align-items: center;
        }
        .user-info a {
            margin-left: 20px;
        }
    </style>
</head>
<body>
    <header>
        <div>
            <a href="home.php"><i class="fas fa-home"></i> Home</a>
            <a href="add_post"><i class="fa-solid fa-circle-plus"></i> Add Post</a>
            <a href="posts_page.php"><i class="fa-solid fa-pen-to-square"></i> Edit Post</a>
            <a href="upload.php"><i class="fa-solid fa-upload"></i> Upload</a>
        </div>
        <div class="user-info">
            <a href="profile.php"><i class="fas fa-user-circle"></i> <?php echo $_SESSION['username']; ?></a>
            <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </header>
    <div class="container">
        <h1>Blog Posts</h1>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Title</th>
                    <th>Excerpt</th>
                    <th>Content</th>
                    <th>Category</th>
                    <th>Tags</th>
                    <th>Keyphrase</th>
                    <th>Featured Image</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($posts as $post): ?>
                    <tr>
                        <td><?php echo $post['id']; ?></td>
                        <td><?php echo $post['title']; ?></td>
                        <td><?php echo $post['excerpt']; ?></td>
                        <td><?php echo $post['content']; ?></td>
                        <td><?php echo $post['category']; ?></td>
                        <td><?php echo $post['tags']; ?></td>
                        <td><?php echo $post['keyphrase']; ?></td>
                        <td><img src="<?php echo $post['featured_image']; ?>" alt="Featured Image" width="100"></td>
                        <td>
                            <a href="edit_post.php?id=<?php echo $post['id']; ?>" class="edit"><i class="fas fa-edit"></i> Edit</a>
                            <a href="delete_post.php?id=<?php echo $post['id']; ?>" class="delete" onclick="return confirm('Are you sure you want to delete this post?');"><i class="fas fa-trash-alt"></i> Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
