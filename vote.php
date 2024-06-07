<?php
include('session.php');
include('dbcon.php');
include('header.php');
?>
<?php
if (isset($_POST['vote'])) {
    $Ngo = $_POST['Ngo'];
    mysqli_query($conn, "INSERT INTO votes (CandidateID) VALUES ('$Ngo')") or die(mysqli_error($conn));

    $Tech = $_POST['Tech'];
    mysqli_query($conn, "INSERT INTO votes (CandidateID) VALUES ('$Tech')") or die(mysqli_error($conn));

    $Telcom = $_POST['Telcom'];
    mysqli_query($conn, "INSERT INTO votes (CandidateID) VALUES ('$Telcom')") or die(mysqli_error($conn));

    $Publishing = $_POST['Publishing'];
    mysqli_query($conn, "INSERT INTO votes (CandidateID) VALUES ('$Publishing')") or die(mysqli_error($conn));

    mysqli_query($conn, "UPDATE voters SET Status='Voted' WHERE VoterID='$session_id'") or die(mysqli_error($conn));

    header('Location: thankyou.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Voting</title>
    <link rel="stylesheet" type="text/css" href="admin/css/style.css">
    <script src="jquery.iphone-switch.js" type="text/javascript"></script>
    <style>
        .ballot {
            margin: 20px;
        }

        .category {
            border: 1px solid #ddd;
            padding: 15px;
            margin-bottom: 10px;
            background-color: #f9f9f9;
        }

        .category p {
            font-size: 18px;
            color: #333;
        }

        .category .candidate-name {
            font-size: 16px;
            color: #555;
        }

        .modal .modal-body p {
            font-size: 16px;
            color: #666;
        }

        .btn-info,
        .btn-success {
            margin-top: 20px;
        }
    </style>
</head>

<body>
    <div class="navbar navbar-fixed-top">
        <div class="navbar-inner">
            <div class="container">
                <a class="brand">
                    <img src="admin/images/your_logo.png" width="60" height="60">
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
    <div class="wrapper">
        <div class="hero-body-voting">
            <div class="vote_wise1">
                <font color="white" size="6">"Official Ballot"</font>
            </div>
            <div class="back">
                <a class="btn btn-info" id="bak" href="voting.php"><i class="icon-arrow-left icon-large"></i>&nbsp;Back</a>
            </div>
        </div>

        <div class="hero-body-456"></div>

        <form method="POST">
            <?php
            if (isset($_POST['save'])) {
                $Ngo = $_POST['Ngo'];
                $Tech = $_POST['Tech'];
                $Telcom = $_POST['Telcom'];
                $Publishing = $_POST['Publishing'];
            }

            // Fetch Ngo candidate details
            $name = "NO Candidate Selected";
            $result = mysqli_query($conn, "SELECT * FROM candidate WHERE Category='$Ngo'") or die(mysqli_error($conn));
            if ($row = mysqli_fetch_array($result)) {
                $name = $row['FullName'];
            }
            ?>

            <div class="ballot">
                <div class="category">
                    <p>Ngo:</p>
                    <div class="candidate-name"><?php echo $name; ?></div>
                    <input type="hidden" name="Ngo" value="<?php echo $Ngo; ?>" />
                </div>

                <?php
                // Fetch Tech candidate details
                $name1 = "NO Candidate Selected";
                $vice = mysqli_query($conn, "SELECT * FROM candidate WHERE Category='$Tech'") or die(mysqli_error($conn));
                if ($row_vice = mysqli_fetch_array($vice)) {
                    $name1 = $row_vice['FullName'];
                }
                ?>

                <div class="category">
                    <p>Tech:</p>
                    <div class="candidate-name"><?php echo $name1; ?></div>
                    <input type="hidden" name="Tech" value="<?php echo $Tech; ?>" />
                </div>

                <?php
                // Fetch Telcom candidate details
                $name2 = "NO Candidate Selected";
                $Representative1 = mysqli_query($conn, "SELECT * FROM candidate WHERE Category='$Telcom'") or die(mysqli_error($conn));
                if ($Representative1_row = mysqli_fetch_array($Representative1)) {
                    $name2 = $Representative1_row['FullName'];
                }
                ?>

                <div class="category">
                    <p>Telcom:</p>
                    <div class="candidate-name"><?php echo $name2; ?></div>
                    <input type="hidden" name="Telcom" value="<?php echo $Telcom; ?>" />
                </div>

                <?php
                // Fetch Publishing candidate details
                $name3 = "NO Candidate Selected";
                $Representative2 = mysqli_query($conn, "SELECT * FROM candidate WHERE Category='$Publishing'") or die(mysqli_error($conn));
                if ($Representative2_row = mysqli_fetch_array($Representative2)) {
                    $name3 = $Representative2_row['FullName'];
                }
                ?>

                <div class="category">
                    <p>Publishing:</p>
                    <div class="candidate-name"><?php echo $name3; ?></div>
                    <input type="hidden" name="Publishing" value="<?php echo $Publishing; ?>" />
                </div>
            </div>

            <div class="hero-body-456">
                <div class="ok_vote">
                    <a class="btn btn-success" id="logout" data-toggle="modal" href="#myModal"><i class="icon-off"></i>&nbsp;Submit Final Votes</a>
                    <div class="modal hide fade" id="myModal">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal">×</button>
                            <h3> </h3>
                        </div>
                        <div class="modal-body">
                            <p>
                                <font color="gray">Are You Sure you Want to Submit Final Votes?</font>
                            </p>
                        </div>
                        <div class="modal-footer">
                            <a href="#" class="btn" data-dismiss="modal">No</a>
                            <button id="save_voter" class="btn btn-success" name="vote"><i class="icon-save icon-large"></i>&nbsp;Yes</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
        <?php include('footer1.php') ?>
    </div>
</body>

</html>