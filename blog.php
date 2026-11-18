<?php
// Include your database connection
include 'dbConnect.php';

// Check for a successful database connection
if ($mysqli->connect_error) {
    die("Database connection failed: " . $mysqli->connect_error);
}

// Fetch only published blog posts from the database
$query = "SELECT * FROM blog_posts WHERE status = 'published' ORDER BY created_at DESC";
$result = mysqli_query($mysqli, $query);

// Check if the query was successful
if (!$result) {
    die("Query failed: " . mysqli_error($mysqli));
}

// Include the header file at the beginning of the script
include 'header.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SBA Awards - Blog</title>
    <link rel="stylesheet" href="styles.css"> <!-- Assuming you have a styles.css file for styling -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css"> <!-- Font Awesome -->
    <style>
        /* Additional styles specific to this page can be added here */
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .container {
            display: grid;
            grid-template-columns: 2fr 1fr; /* Two main columns with a sidebar */
            gap: 20px;
            width: 80%; /* Adjust width as needed */
            margin: auto;
            padding: 20px;
        }
        .blog-posts {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); /* Responsive grid */
            gap: 20px;
        }
        .blog-post {
            background: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .blog-post h2 {
            color: #333;
        }
        .blog-post img {
            max-width: 100%;
            height: auto;
            margin-bottom: 15px;
            border-radius: 5px;
        }
        .blog-post .excerpt {
            color: #666;
        }
        .read-more {
            display: inline-block;
            margin-top: 10px;
            padding: 10px 15px;
            background-color: #007BFF;
            color: #fff;
            text-decoration: none;
            border-radius: 5px;
        }
        .read-more:hover {
            background-color: #0056b3;
        }
        .post-meta {
            margin-top: 15px;
            color: #999;
        }
        .post-meta .category,
        .post-meta time,
        .post-meta .author {
            margin-left: 10px;
            display: inline-block;
        }
        .post-meta .category i,
        .post-meta time i,
        .post-meta .author i {
            margin-right: 5px;
        }
        .tags {
            margin-top: 10px;
            color: #777;
        }
        .social-share {
            margin-top: 10px;
        }
        .social-share a {
            margin-right: 10px;
            color: #666;
        }
        .sidebar {
            background: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .sidebar h3 {
            margin-top: 0;
        }
        .recent-posts,
        .popular-posts,
        .newsletter {
            margin-bottom: 20px;
        }
        .newsletter input[type="email"] {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        .newsletter button {
            display: inline-block;
            padding: 10px 15px;
            background-color: #007BFF;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .newsletter button:hover {
            background-color: #0056b3;
        }
        @media (max-width: 1024px) {
            .container {
                grid-template-columns: 1fr; /* Single column on smaller screens */
                width: 90%; /* Adjust width for smaller screens */
            }
        }
        @media (max-width: 768px) {
            .sidebar {
                order: -1; /* Place sidebar first on smaller screens */
                margin-top: 20px; /* Add space above sidebar */
            }
        }
    </style>
</head>
<body>
    <?php include 'header.php'; ?> <!-- Your header content -->

    <div class="container">
        <div class="blog-posts">
            <?php if (mysqli_num_rows($result) > 0): ?>
                <?php while($post = mysqli_fetch_assoc($result)): ?>
                    <article class="blog-post">
                        <h2><?php echo $post['title']; ?></h2>
                        <?php if($post['featured_image']): ?>
                            <img src="<?php echo $post['featured_image']; ?>" alt="Featured Image">
                        <?php endif; ?>
                        <p class="excerpt"><?php echo $post['excerpt']; ?></p>
                        <a class="read-more" href="blog_post.php?id=<?php echo $post['id']; ?>">Read More</a>
                        <footer class="post-meta">
                            <span class="category"><i class="fas fa-folder"></i> <?php echo $post['category']; ?></span>
                            <time datetime="<?php echo date('c', strtotime($post['created_at'])); ?>"><i class="fas fa-calendar-alt"></i> <?php echo date('F j, Y', strtotime($post['created_at'])); ?></time>
                            <span class="author"><i class="fas fa-user"></i> By <?php echo isset($post['author']) ? $post['author'] : 'Unknown'; ?></span>
                            <?php if($post['tags']): ?>
                                <div class="tags">Tags: <?php echo $post['tags']; ?></div>
                            <?php endif; ?>
                        </footer>
                        <div class="social-share">
                            <a href="#"><i class="fab fa-facebook"></i> Share</a>
                            <a href="#"><i class="fab fa-twitter"></i> Tweet</a>
                            <a href="#"><i class="fab fa-linkedin"></i> Share</a>
                        </div>
                    </article>
                <?php endwhile; ?>
            <?php else: ?>
                <p>No blog posts found.</p>
            <?php endif; ?>
        </div>
        <aside class="sidebar">
            <section class="recent-posts">
                <h3>Recent Posts</h3>
                <?php
                $recentQuery = "SELECT * FROM blog_posts WHERE status = 'published' ORDER BY created_at DESC LIMIT 5";
                $recentResult = mysqli_query($mysqli, $recentQuery);
                if ($recentResult):
                    if (mysqli_num_rows($recentResult) > 0):
                        while($recentPost = mysqli_fetch_assoc($recentResult)): ?>
                            <div>
                                <a href="post.php?id=<?php echo $recentPost['id']; ?>"><?php echo $recentPost['title']; ?></a>
                            </div>
                        <?php endwhile;
                    else:
                        echo "<p>No recent posts found.</p>";
                    endif;
                else:
                    echo "Error fetching recent posts: " . mysqli_error($mysqli);
                endif; ?>
            </section>
            <section class="popular-posts">
                <h3>Popular Posts</h3>
                <?php
                $popularQuery = "SELECT * FROM blog_posts WHERE status = 'published' ORDER BY views DESC LIMIT 5";
                $popularResult = mysqli_query($mysqli, $popularQuery);
                if ($popularResult):
                    if (mysqli_num_rows($popularResult) > 0):
                        while($popularPost = mysqli_fetch_assoc($popularResult)): ?>
                            <div>
                                <a href="post.php?id=<?php echo $popularPost['id']; ?>"><?php echo $popularPost['title']; ?></a>
                            </div>
                        <?php endwhile;
                    else:
                        echo "<p>No popular posts found.</p>";
                    endif;
                else:
                    echo "Error fetching popular posts: " . mysqli_error($mysqli);
                endif; ?>
            </section>
            <section class="newsletter">
                <h3>Newsletter</h3>
                <form action="subscribe.php" method="POST">
                    <input type="email" name="email" placeholder="Your email address" required>
                    <button type="submit">Subscribe</button>
                </form>
            </section>
        </aside>
    </div>

    <?php include 'footer.php'; ?> <!-- Your footer content -->

    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/js/all.min.js"></script>
</body>
</html>
