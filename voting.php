<?php
include('session.php');
include('dbcon.php');
include('header.php');
?>
<link rel="stylesheet" type="text/css" href="admin/css/new_style.css" />
<script src="jquery.iphone-switch.js" type="text/javascript"></script>
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
                    <div class="nav-caption">
                        <font size="4" color="white">The power of startups to transform economies.</font>
                    </div>
                </a>

                <?php include('head.php'); ?>

            </div>
        </div>
    </div>
    <div class="main-wrapper">

        <div class="hero-section">
            <div class="voting-message">
                <font color="gold" size="6">"Please Vote Wisely"</font>
            </div>
            <div class="help-button">
                <a class="btn btn-info" id="help" href="help.php"><i class="icon-info-sign icon-large"></i>&nbsp;Help</a>
            </div>
        </div>
        <form method="post" action="vote.php">
            <div class="candidate-category">

                <!-- NGO Candidates Section -->
                <div class="hero-candidate-section">
                    <font color="white">Candidate for NGO</font>
                </div>
                <div class="candidate-list">
                    <div class="candidate-margin">
                        <?php
                        $Ngo = mysqli_query($conn, "select * from candidate where Category='Ngo'") or die(mysqli_error($conn));
                        while ($row = mysqli_fetch_array($Ngo)) {
                            $Ngo_id = $row['CandidateID']; ?>
                            <img class="candidate-image" src="<?php echo $row['Photo']; ?>" width="150" height="150" onmouseover="showtrail('<?php echo $row['Photo']; ?>','<?php echo $row['FullName'] ?>',200,5)" onmouseout="hidetrail()">
                            &nbsp;&nbsp;&nbsp;&nbsp;
                        <?php } ?>
                    </div>
                </div>
                <div class="select-candidate">
                    <div class="select-margin">
                        <select name="Ngo" class="dropdown">
                            <option class="dropdown-option">--Select Candidate--</option>
                            <?php
                            $Ngo = mysqli_query($conn, "select * from candidate where Category='Ngo'") or die(mysqli_error($conn));
                            while ($row = mysqli_fetch_array($Ngo)) {
                                $Ngo_id = $row['CandidateID']; ?>
                                <option value="<?php echo $Ngo_id; ?>" class="dropdown-option">
                                    <?php echo $row['FullName']; ?></option>
                            <?php } ?>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Tech Candidates Section -->
            <div class="candidate-category">
                <div class="hero-candidate-section">
                    <font color="white">Candidate for Tech</font>
                </div>
                <div class="candidate-list">
                    <div class="candidate-margin">
                        <?php
                        $Tech = mysqli_query($conn, "select * from candidate where Category='Tech'") or die(mysqli_error($conn));
                        while ($row = mysqli_fetch_array($Tech)) {
                            $Tech_id = $row['CandidateID']; ?>
                            <img class="candidate-image" src="<?php echo $row['Photo']; ?>" width="150" height="150" onmouseover="showtrail('<?php echo $row['Photo']; ?>','<?php echo $row['FullName']; ?>',200,5)" onmouseout="hidetrail()">
                            &nbsp;&nbsp;&nbsp;&nbsp;
                        <?php } ?>
                    </div>
                </div>
                <div class="select-candidate">
                    <div class="select-margin">
                        <select name="Tech" class="dropdown">
                            <option class="dropdown-option">--Select Candidate--</option>
                            <?php
                            $Tech = mysqli_query($conn, "select * from candidate where Category='Tech'") or die(mysqli_error($conn));
                            while ($row = mysqli_fetch_array($Tech)) {
                                $Tech_id = $row['CandidateID']; ?>
                                <option value="<?php echo $Tech_id; ?>" class="dropdown-option">
                                    <?php echo $row['FullName']; ?></option>
                            <?php } ?>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Telcom Candidates Section -->
            <div class="candidate-category">
                <div class="hero-candidate-section">
                    <font color="white">Candidate for Telcom</font>
                </div>
                <div class="candidate-list">
                    <div class="candidate-margin">
                        <?php
                        $Telcom = mysqli_query($conn, "select * from candidate where Category='Telcom'") or die(mysqli_error($conn));
                        while ($row = mysqli_fetch_array($Telcom)) {
                            $Telcom_id = $row['CandidateID']; ?>
                            <img class="candidate-image" src="<?php echo $row['Photo']; ?>" width="70" height="70" onmouseover="showtrail('<?php echo $row['Photo']; ?>','<?php echo $row['FullName']; ?>',200,5)" onmouseout="hidetrail()">
                            &nbsp;&nbsp;&nbsp;&nbsp;
                        <?php } ?>
                    </div>
                </div>
                <div class="select-candidate">
                    <div class="select-margin">
                        <select name="Telcom" class="dropdown">
                            <option class="dropdown-option">--Select Candidate--</option>
                            <?php
                            $Telcom = mysqli_query($conn, "select * from candidate where Category='Telcom'") or die(mysqli_error($conn));
                            while ($row = mysqli_fetch_array($Telcom)) {
                                $Telcom_id = $row['CandidateID']; ?>
                                <option value="<?php echo $Telcom_id; ?>" class="dropdown-option">
                                    <?php echo $row['FullName']; ?></option>
                            <?php } ?>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Publishing Candidates Section -->
            <div class="candidate-category">
                <div class="hero-candidate-section">
                    <font color="white">Candidate for Publishing</font>
                </div>
                <div class="candidate-list">
                    <div class="candidate-margin">
                        <?php
                        $Publishing = mysqli_query($conn, "select * from candidate where Category='Publishing'") or die(mysqli_error($conn));
                        while ($row = mysqli_fetch_array($Publishing)) {
                            $Publishing_id = $row['CandidateID']; ?>
                            <img class="candidate-image" src="<?php echo $row['Photo']; ?>" width="70" height="70" onmouseover="showtrail('<?php echo $row['Photo']; ?>','<?php echo $row['FullName'] ?>',200,5)" onmouseout="hidetrail()">
                            &nbsp;&nbsp;&nbsp;&nbsp;
                        <?php } ?>
                    </div>
                </div>
                <div class="select-candidate">
                    <div class="select-margin">
                        <select name="Publishing" class="dropdown">
                            <option class="dropdown-option">--Select Candidate--</option>
                            <?php
                            $Publishing = mysqli_query($conn, "select * from candidate where Category='Publishing'") or die(mysqli_error($conn));
                            while ($row = mysqli_fetch_array($Publishing)) {
                                $Publishing_id = $row['CandidateID']; ?>
                                <option value="<?php echo $Publishing_id; ?>" class="dropdown-option">
                                    <?php echo $row['FullName']; ?></option>
                            <?php } ?>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Submit Vote Button -->
            <div class="submit-section">
                <button id="submit-vote" class="btn btn-success" name="save"><i class="icon-thumbs-up icon-large"></i>&nbsp;Submit Vote</button>
            </div>

            <!-- Vote Later Button -->
            <div class="vote-later-section">
                <a class="btn" id="vote-later" data-toggle="modal" href="#myModal"><i class="icon-circle-arrow-left icon-large"></i>&nbsp;Vote later</a>
            </div>
        </form>

        <div class="footer">
            <?php include('footer1.php') ?>
        </div>
    </div>

    <div class="modal hide fade" id="myModal">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal">�</button>
            <h3> </h3>
        </div>
        <div class="modal-body">
            <p>
                <font color="gray">Are You Sure you Want to Vote Later?</font>
            </p>
        </div>
        <div class="modal-footer">
            <a href="#" class="btn" data-dismiss="modal">No</a>
            <a href="logout_back.php" class="btn btn-success">Yes</a>
        </div>
    </div>

</body>

</html>