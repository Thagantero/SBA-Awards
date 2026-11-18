<?php
session_start();
ob_start();
include('dbcon.php');

$error_message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $UserName = $_POST['UserName'] ?? '';
    $Password = $_POST['Password'] ?? '';

    $voterId = null;
    $unvotedCount = 0;
    $hasVoted = false;

    if (is_string($UserName) && is_string($Password) && $UserName !== '' && $Password !== '') {
        $login = mysqli_prepare(
            $conn,
            "SELECT VoterID, Status = 'Unvoted', Status = 'Voted'
             FROM voters WHERE Username = ? AND Password = ? AND Status IN ('Unvoted', 'Voted')"
        );
        mysqli_stmt_bind_param($login, 'ss', $UserName, $Password);
        mysqli_stmt_execute($login);
        mysqli_stmt_bind_result($login, $id, $isUnvoted, $isVoted);

        while (mysqli_stmt_fetch($login)) {
            if ($isUnvoted) {
                $voterId = $id;
                $unvotedCount++;
            }
            $hasVoted = $hasVoted || $isVoted;
        }
        mysqli_stmt_close($login);
    }

    if ($unvotedCount === 1) {
        session_regenerate_id(true);
        $_SESSION['id'] = $voterId;
        header('Location: voting.php');
        exit;
    } elseif ($hasVoted) {
        $error_message = "You’ve already cast your vote.";
    } else {
        $error_message = "Check your username and password and try again.";
    }
}
ob_end_flush();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Voter sign-in | SBA Awards</title>
    <link rel="icon" href="admin/images/your_logo.png" type="image/png">
    <style>
        *, *::before, *::after { box-sizing: border-box; }

        body {
            margin: 0;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            background: #111b18;
            color: #f5f0e3;
            font: 1rem/1.55 system-ui, sans-serif;
        }

        .shell { width: min(100% - 2.5rem, 70rem); margin-inline: auto; }

        .site-header {
            padding-block: 1.5rem;
            border-bottom: 1px solid #3e4b41;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: .75rem;
            font-weight: 700;
            letter-spacing: .04em;
        }

        .brand img { object-fit: contain; border-radius: .5rem; }

        main {
            flex: 1;
            display: grid;
            grid-template-columns: 1fr minmax(20rem, 26rem);
            gap: clamp(2rem, 7vw, 7rem);
            align-items: center;
            padding-block: clamp(3rem, 9vw, 8rem);
        }

        .intro h1 {
            margin: 0;
            color: #f4d891;
            font: 400 clamp(2.75rem, 6.4vw, 5.5rem)/1.03 Georgia, serif;
            letter-spacing: -.045em;
            max-width: 12ch;
            text-wrap: balance;
        }

        .intro p { max-width: 36ch; color: #c8d0c2; margin: 1.25rem 0 0; }

        .signin {
            padding: clamp(1.25rem, 4vw, 2.5rem);
            background: #f5f0e3;
            color: #17251f;
            border-radius: 1rem;
        }

        .signin h2 { margin: 0; font: 400 2rem/1.15 Georgia, serif; }
        .signin p { margin: .75rem 0 1.5rem; color: #46534b; }
        .signin label { display: block; font-weight: 650; margin: 1rem 0 .375rem; }

        .signin input, .signin button {
            width: 100%;
            min-height: 3rem;
            border-radius: .5rem;
            padding: .65rem .8rem;
            font: inherit;
        }

        .signin input { background: #fffdf7; color: inherit; border: 1px solid #657469; }

        .signin button {
            margin-top: 1.5rem;
            border: 0;
            background: #243d31;
            color: #fffdf7;
            cursor: pointer;
            font-weight: 700;
        }

        .signin button:hover { background: #355445; }
        :focus-visible { outline: 3px solid #836019; outline-offset: 3px; }

        .signin .error {
            margin: 1rem 0 0;
            padding: .75rem;
            border-radius: .5rem;
            background: #f9e4df;
            color: #80261b;
        }

        footer { padding-block: 1.25rem; color: #c8d0c2; border-top: 1px solid #3e4b41; }

        @media (max-width: 45rem) {
            main { grid-template-columns: minmax(0, 1fr); }
        }
    </style>
</head>
<body>
    <header class="site-header">
        <div class="shell brand">
            <img src="admin/images/your_logo.png" width="44" height="44" alt="">
            <span>SBA Awards</span>
        </div>
    </header>
    <main class="shell">
        <section class="intro" aria-labelledby="page-title">
            <h1 id="page-title">Vote in the SBA Awards</h1>
            <p>Recognizing the power of startups to transform economies.</p>
        </section>
        <section class="signin" aria-labelledby="signin-title">
            <h2 id="signin-title">Voter sign-in</h2>
            <p>Use your voter credentials to open the ballot.</p>
            <?php if ($error_message !== ''): ?>
                <p class="error" role="alert"><?= htmlspecialchars($error_message, ENT_QUOTES, 'UTF-8') ?></p>
            <?php endif; ?>
            <form method="post">
                <label for="username">Username</label>
                <input id="username" name="UserName" autocomplete="username" required>

                <label for="password">Password</label>
                <input id="password" name="Password" type="password" autocomplete="current-password" required>

                <button type="submit" name="Login">Continue to ballot</button>
            </form>
        </section>
    </main>
    <footer>
        <div class="shell">&copy; <?= date('Y') ?> SBA Awards</div>
    </footer>
</body>
</html>
