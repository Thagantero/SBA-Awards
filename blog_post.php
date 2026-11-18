<?php
// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Start a session (if needed)
session_start();

include 'header.php';

// Include necessary files (adjust as per your file structure)
include 'dbConnect.php'; // Ensure dbConnect.php is correctly included

// Function to sanitize HTML input
function sanitize($data) {
    return htmlspecialchars($data);
}

// Check if the 'id' parameter is set in the URL
if (isset($_GET['id'])) {
    $post_id = $_GET['id'];

    // Fetch blog post details from the database
    $query = "SELECT id, title, content, created_at, author, tags, category, featured_image FROM blog_posts WHERE id = ?";
    if ($stmt = $mysqli->prepare($query)) {
        $stmt->bind_param("i", $post_id);
        if ($stmt->execute()) {
            $stmt->bind_result($id, $title, $content, $created_at, $author, $tags, $category, $featured_image);
            if ($stmt->fetch()) {
                // Set default author if not provided
                if (empty($author)) {
                    $author = "SBA Awards"; // Default author name
                }

                // Display blog post details
                $title = sanitize($title);
                $content = sanitize($content);
                $author = sanitize($author);
                $tags = sanitize($tags);
                $category = sanitize($category);
                $created_at = date('F j, Y', strtotime($created_at));
                ?>

                <!DOCTYPE html>
                <html lang="en">
                <head>
                    <meta charset="UTF-8">
                    <meta name="viewport" content="width=device-width, initial-scale=1.0">
                    <link rel="stylesheet" href="styles.css"> <!-- Adjust path as needed -->
                    <title><?php echo $title; ?></title>
                    <style>
                        body {
                            font-family: Arial, sans-serif;
                            background-color: #f0f0f0;
                            margin: 0;
                            padding: 0;
                            color: #333;
                        }
                        .container {
                            max-width: 800px;
                            margin: 20px auto;
                            background-color: #fff;
                            padding: 20px;
                            border-radius: 10px;
                            box-shadow: 0 0 10px rgba(0,0,0,0.1);
                        }
                        .post-header {
                            text-align: center;
                            margin-bottom: 20px;
                        }
                        .post-header h1 {
                            margin-top: 0;
                            color: #333;
                        }
                        .post-meta {
                            display: flex;
                            justify-content: space-between;
                            align-items: center;
                            color: #777;
                            font-size: 14px;
                        }
                        .post-meta span {
                            margin-right: 10px;
                        }
                        .post-content {
                            line-height: 1.6;
                        }
                        .post-image {
                            margin-bottom: 20px;
                            text-align: center;
                        }
                        .post-image img {
                            max-width: 100%;
                            border-radius: 5px;
                            box-shadow: 0 0 5px rgba(0,0,0,0.1);
                        }
                        .sidebar {
                            background-color: #f0f0f0;
                            padding: 10px;
                            border-radius: 5px;
                            margin-top: 20px;
                        }
                        .sidebar h3 {
                            margin-top: 0;
                            font-size: 18px;
                            color: #333;
                        }
                        .sidebar ul {
                            list-style: none;
                            padding: 0;
                            margin: 0;
                        }
                        .sidebar li {
                            margin-bottom: 5px;
                        }
                        .share-buttons {
                            margin-top: 20px;
                        }
                        .share-buttons a {
                            display: inline-block;
                            padding: 8px 12px;
                            background-color: #007bff;
                            color: #fff;
                            border-radius: 5px;
                            text-decoration: none;
                            margin-right: 10px;
                        }
                        .share-buttons a:hover {
                            background-color: #0056b3;
                        }
                        .footer {
                            text-align: center;
                            margin-top: 20px;
                            padding: 10px;
                            background-color: #f0f0f0;
                            border-top: 1px solid #ccc;
                        }
                    </style>
                </head>
                <body>

                <div class="container">
                    <div class="post-header">
                        <h1><?php echo $title; ?></h1>
                        <div class="post-meta">
                            <span>Published on: <?php echo $created_at; ?></span>
                            <span>Category: <?php echo $category; ?></span>
                        </div>
                    </div>

                    <?php if (!empty($featured_image)): ?>
                    <div class="post-image">
                        <img src="<?php echo htmlspecialchars($featured_image); ?>" alt="<?php echo $title; ?>">
                    </div>
                    <?php endif; ?>

                    <div class="post-content">
                        <?php echo $content; ?>
                    </div>

                    <div class="sidebar">
                        <h3>Post Details</h3>
                        <ul>
                            <li><strong>Author:</strong> <?php echo $author; ?></li>
                            <li><strong>Category:</strong> <?php echo $category; ?></li>
                            <li><strong>Tags:</strong> <?php echo $tags; ?></li>
                        </ul>
                        <div class="share-buttons">
                            <a href="#">Facebook</a>
                            <a href="#">Twitter</a>
                            <a href="#">LinkedIn</a>
                        </div>
                    </div>
                </div>

<?php include 'footer.php'; ?>

                </body>
                </html>

                <?php
            } else {
                echo "Blog post not found.";
            }
            $stmt->close();
        } else {
            echo "Error executing query: " . $mysqli->error;
        }
    } else {
        echo "Error preparing query: " . $mysqli->error;
    }
} else {
    echo "No post ID provided.";
}
?>
