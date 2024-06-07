<?php
session_start();
ob_start();
include('dbcon.php');

$error_message = '';

if (isset($_POST['Login'])) {
    $UserName = $_POST['UserName'];
    $Password = $_POST['Password'];

    $login_query = mysqli_query($conn, "SELECT * FROM voters WHERE Username='$UserName' AND Password='$Password' AND Status='Unvoted'") or die(mysqli_error($conn));
    $login_query1 = mysqli_query($conn, "SELECT * FROM voters WHERE Username='$UserName' AND Password='$Password' AND Status='Voted'");

    $count = mysqli_num_rows($login_query);
    $row = mysqli_fetch_array($login_query);

    if ($count == 1) {
        $_SESSION['id'] = $row['VoterID'];
        header('Location: voting.php');
        exit;
    } elseif (mysqli_num_rows($login_query1) > 0) {
        $error_message = "You Can Only Vote Once";
    } else {
        $error_message = "Please check your username and password";
    }
}
ob_end_flush();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <link rel="stylesheet" type="text/css" href="admin/css/style.css" />
    <?php include('header.php'); ?>
</head>

<body>
    <div class="navbar navbar-fixed-top">
        <div class="navbar-inner">
            <div class="container">
                <a class="brand">
                    <img src="admin/images/your_logo.png" width="54" height="54">
                </a>
                <a class="brand">
                    <h2>SBA Awards</h2>
                    <div class="chmsc_nav">
                        <font size="4" color="white">The power of startups to transform economies.</font>
                    </div>
                </a>
                <?php include('head.php'); ?>
            </div>
        </div>
    </div>
    <div class="wrapper_admin">
        <br><br><br>
        <div id="element" class="hero-body-index">
            <p>
                <font color="white">
                    <h2>Voter Login</h2>
                </font>
            </p>
            <form method="POST">
                <table>
                    <tr>
                        <td>
                            <font color="white">UserName:</font>&nbsp;&nbsp;
                        </td>
                        <td><input type="text" name="UserName" class="UserName_hover"></td>
                    </tr>
                    <tr>
                        <td>...</td>
                    </tr>
                    <tr>
                        <td>
                            <font color="white">Password:</font>&nbsp;&nbsp;
                        </td>
                        <td><input type="password" name="Password" class="Password_hover"></td>
                    </tr>
                    <tr>
                        <td>...</td>
                    </tr>
                    <tr>
                        <td></td>
                        <td><button class="btn btn-primary" name="Login"><i class="icon-ok icon-large"></i>&nbsp;Login</button></td>
                    </tr>
                </table>
            </form>

            <br>
            <div class="error">
                <?php
                if (!empty($error_message)) {
                    echo '<div class="alert alert-error"><button class="close" data-dismiss="alert">×</button>' . $error_message . '</div>';
                }
                ?>
            </div>
        </div>
        <br><br><br><br><br>
        <?php include('footer.php'); ?>
    </div>
</body>

</html>