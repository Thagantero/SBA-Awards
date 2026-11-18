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

// Initialize variables
$id = $title = $excerpt = $content = $category = $tags = $keyphrase = $featured_image = "";

// Get post ID from URL
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Fetch post data
    $sql = "SELECT * FROM blog_posts WHERE id = ?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $post = $result->fetch_assoc();
        $title = $post['title'];
        $excerpt = $post['excerpt'];
        $content = $post['content'];
        $category = $post['category'];
        $tags = $post['tags'];
        $keyphrase = $post['keyphrase'];
        $featured_image = $post['featured_image'];
    } else {
        echo "Post not found.";
        exit;
    }
} else {
    echo "Invalid post ID.";
    exit;
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = filter_input(INPUT_POST, 'title', FILTER_SANITIZE_SPECIAL_CHARS);
    $excerpt = filter_input(INPUT_POST, 'excerpt', FILTER_SANITIZE_SPECIAL_CHARS);
    $content = filter_input(INPUT_POST, 'content', FILTER_SANITIZE_SPECIAL_CHARS);
    $category = filter_input(INPUT_POST, 'category', FILTER_SANITIZE_SPECIAL_CHARS);
    $tags = filter_input(INPUT_POST, 'tags', FILTER_SANITIZE_SPECIAL_CHARS);
    $keyphrase = filter_input(INPUT_POST, 'keyphrase', FILTER_SANITIZE_SPECIAL_CHARS);
    $featured_image = filter_input(INPUT_POST, 'featured_image', FILTER_SANITIZE_SPECIAL_CHARS);

    // Update the post
    $sql = "UPDATE blog_posts SET title = ?, excerpt = ?, content = ?, category = ?, tags = ?, keyphrase = ?, featured_image = ? WHERE id = ?";
    $stmt = $mysqli->prepare($sql);
    $stmt->bind_param("sssssssi", $title, $excerpt, $content, $category, $tags, $keyphrase, $featured_image, $id);

    if ($stmt->execute()) {
        header("Location: posts_page.php");
        exit;
    } else {
        echo "Error updating post: " . $stmt->error;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://kit.fontawesome.com/87ab693041.js" crossorigin="anonymous"></script>
    <title>Edit Post</title>
</head>
<body>
    <h1>Edit Post</h1>
    <form action="edit_post.php?id=<?php echo $id; ?>" method="post">
        <label for="title">Title:</label><br>
        <input type="text" id="title" name="title" value="<?php echo $title; ?>"><br><br>
        
        <label for="excerpt">Excerpt:</label><br>
        <textarea id="excerpt" name="excerpt"><?php echo $excerpt; ?></textarea><br><br>
        
        <label for="content">Content:</label><br>
        <textarea id="content" name="content"><?php echo $content; ?></textarea><br><br>
        
        <label for="category">Category:</label><br>
        <input type="text" id="category" name="category" value="<?php echo $category; ?>"><br><br>
        
        <label for="tags">Tags:</label><br>
        <input type="text" id="tags" name="tags" value="<?php echo $tags; ?>"><br><br>
        
        <label for="keyphrase">Keyphrase:</label><br>
        <input type="text" id="keyphrase" name="keyphrase" value="<?php echo $keyphrase; ?>"><br><br>
        
        <label for="featured_image">Featured Image URL:</label><br>
        <input type="text" id="featured_image" name="featured_image" value="<?php echo $featured_image; ?>"><br><br>
        
        <input type="submit" value="Update Post">
    </form>
</body>
</html>
