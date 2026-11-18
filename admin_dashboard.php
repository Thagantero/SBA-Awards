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
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" integrity="sha512-..." crossorigin="anonymous" />
    <script src="https://kit.fontawesome.com/87ab693041.js" crossorigin="anonymous"></script>

    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            display: flex;
        }

        header {
            background-color: #333;
            color: gold;
            padding: 10px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 100%;
            position: fixed;

        }
        header a {
            color: gold;
            text-decoration: none;
            margin-left: 15px;
            margin-right: 20px;
        }
        header .user-info {
            display: flex;
            align-items: center;
        }
        header .user-info a {
            margin-left: 10px;
        }
        header .logo img {
            max-width: 60px;
            height: auto;
        }
        .side-nav {
            width: 250px;
            background-color: #333;
            color: #fff;
            position: fixed;
            top: 60px; /* Adjusted to start below the header */
            height: calc(100% - 60px);
            overflow-y: auto;
            transition: width 0.3s;
            z-index: 1000; /* Ensure it stays on top */
        }
        .side-nav a {
            padding: 15px 20px;
            text-decoration: none;
            color: #fff;
            display: block;
        }
        .side-nav a:hover, .side-nav .menu-item.active > a {
            background-color: #575757;
        }
        .side-nav .menu-item {
            position: relative;
        }
        .side-nav .submenu {
            display: none;
            background-color: #444;
            position: absolute;
            left: 250px;
            top: 0;
            width: 200px;
        }
        .side-nav .submenu a {
            padding-left: 20px;
        }
        .side-nav .menu-item.active > .submenu,
        .side-nav .menu-item:hover > .submenu {
            display: block;
        }
        .side-nav .collapse-btn {
            text-align: right;
            padding: 10px 20px;
            cursor: pointer;
            background-color: #222;
        }
        .side-nav.collapsed {
            width: 80px;
        }
        .side-nav.collapsed a {
            text-align: center;
        }
        .side-nav.collapsed a span {
            display: none;
        }
        .side-nav.collapsed .submenu {
            left: 80px;
        }
        .container {
            flex: 1;
            margin: 80px 20px 20px 270px; /* Adjusted to start below the header and beside the side navigation */
            padding: 20px;
            background-color: #fff;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            border-radius: 8px;
        }
        footer {
            background-color: #333;
            color: gold;
            text-align: center;
            padding: 10px 0;
            position: fixed;
            bottom: 0;
            width: 100%;
            z-index: 1000; /* Ensure it stays on top */
        }
    </style>
</head>
<body>

<header>
    <div class="logo">
        <img src="http://localhost/SBA-awards/uploads/logo1.png" alt="Logo">
    </div>
    <div class="user-info">
        <a href="home.php"><i class="fas fa-home"></i> Home</a>
        <a href="admin_dashboard.php"><i class="fas fa-dashboard"></i> Dashboard</a>
        <a href="new_post.php"><i class="fa-solid fa-circle-plus"></i> Add Post</a>
        <a href="posts_page.php"><i class="fa-solid fa-pen-to-square"></i> Edit Post</a>
        <a href="upload.php"><i class="fa-solid fa-upload"></i> Upload</a>
        <a href="uploads.php"><i class="fa-solid fa-file"></i> Uploads</a>
        <a href="stats.html"><i class="fa-solid fa-chart-simple"></i> Stats</a>
        <a href="profile.php"><i class="fas fa-user-circle"></i> <?php echo htmlspecialchars($_SESSION['username']); ?></a>
        <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </div>
</header>

<div class="side-nav" id="sideNav">
    <div class="collapse-btn" onclick="toggleNav()">☰</div>
    <a href="home.php" class="menu-item"><i class="fas fa-home"></i> <span>Home</span></a>
    <a href="admin_dashboard.php" class="menu-item"><i class="fas fa-dashboard"></i> <span>Dashboard</span></a>
    <div class="menu-item">
        <a href="#"><i class="fa-solid fa-pen-to-square"></i> <span>Posts</span></a>
        <div class="submenu">
            <a href="new_post.php">Add Post</a>
            <a href="posts_page.php">Edit Post</a>
        </div>
    </div>
    <div class="menu-item">
        <a href="#"><i class="fa-solid fa-file"></i> <span>Pages</span></a>
        <div class="submenu">
            <a href="add_page.php">Add Page</a>
            <a href="all_pages.php">All Pages</a>
        </div>
    </div>
    <div class="menu-item">
        <a href="#"><i class="fa-solid fa-upload"></i> <span>Media</span></a>
        <div class="submenu">
            <a href="upload.php">Add Media</a>
            <a href="uploads.php">All Media</a>
        </div>
    </div>
    <div class="menu-item">
        <a href="#"><i class="fa-solid fa-user"></i> <span>Users</span></a>
        <div class="submenu">
            <a href="add_user.php">Add User</a>
            <a href="all_users.php">All Users</a>
        </div>
    <div class="menu-item">
    <a href="#"><i class="fas fa-ticket-alt"></i> <span>Tickets</span></a>
    <div class="submenu">
        <a href="add_ticket.php">Add Ticket</a>
        <a href="all_tickets.php">All Tickets</a>
    </div>
</div>
<div class="menu-item">
    <a href="#"><i class="fas fa-calendar-alt"></i> <span>Events</span></a>
    <div class="submenu">
        <a href="add_event.php">Add Event</a>
        <a href="all_events.php">All Events</a>
    </div>
</div>


    </div>
    <a href="comments.php" class="menu-item"><i class="fas fa-comments"></i> <span>Comments</span></a>
    <a href="contacts.php" class="menu-item"><i class="fas fa-address-book"></i> <span>Contacts</span></a>
    <a href="tools.php" class="menu-item"><i class="fas fa-tools"></i> <span>Tools</span></a>
    <a href="stats.html" class="menu-item"><i class="fa-solid fa-chart-simple"></i> <span>Stats</span></a>
</div>

<div class="container">
    <h1>Admin Dashboard</h1>
    <!-- Your content here -->
</div>

<footer>
    &copy; <?php echo date('Y'); ?> SBA Awards. All Rights Reserved.
</footer>

<script>
    function toggleNav() {
        var sideNav = document.getElementById('sideNav');
        sideNav.classList.toggle('collapsed');
    }

    document.querySelectorAll('.side-nav .menu-item > a').forEach(function(element) {
        element.addEventListener('click', function(event) {
            var parent = event.target.parentElement;
            if (parent.classList.contains('active')) {
                parent.classList.remove('active');
            } else {
                document.querySelectorAll('.side-nav .menu-item').forEach(function(menuItem) {
                    menuItem.classList.remove('active');
                });
                parent.classList.add('active');
            }
        });
    });
</script>

</body>
</html>
