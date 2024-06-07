<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nomination - SBA Awards</title>
    <link rel="stylesheet" href="admin/css/bootstrap.min.css">
    <style>
    body {
        font-family: Arial, sans-serif;
        background-color: black;
        color: gold;
        margin: 0;
        padding: 0;
        display: flex;
        flex-direction: column;
        min-height: 100vh;
    }

    header {
        background-color: gold;
        color: black;
        padding: 10px;
        text-align: center;
    }

    header img {
        max-width: 200px;
        height: auto;
        margin: 10px auto;
        display: block;
    }

    nav {
        background-color: #222;
        padding: 10px;
    }

    nav ul {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    nav ul li {
        display: inline;
        margin-right: 10px;
    }

    nav ul li a {
        text-decoration: none;
        color: gold;
        padding: 5px 10px;
        border-radius: 5px;
    }

    nav ul li a:hover {
        background-color: gold;
        color: black;
    }

    .container {
        max-width: 600px;
        margin: 20px auto;
        padding: 20px;
        background-color: #111;
        border-radius: 10px;
        color: gold;
        flex: 1;
    }

    form label {
        display: block;
        margin-bottom: 5px;
    }

    form input[type="text"],
    form input[type="email"],
    form textarea,
    form select,
    form button,
    form input[type="number"],
    form input[type="file"],
    form input[type="date"] {
        width: 100%;
        padding: 8px;
        margin-bottom: 10px;
        border: 1px solid gold;
        border-radius: 5px;
        background-color: black;
        color: gold;
    }

    form button[type="submit"] {
        padding: 10px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        background-color: gold;
        color: black;
    }

    form button[type="submit"]:hover {
        background-color: #f7d32e;
    }

    footer {
        background-color: gold;
        color: black;
        padding: 10px;
        text-align: center;
        width: 100%;
        margin-top: auto;
    }
    </style>
</head>

<body>

    <header>
        <img src="admin/images/your_logo.png" alt="SBA Awards Logo">
    </header>

    <nav>
        <ul>
            <li><a href="voting_website.html">Home</a></li>
            <li><a href="about.html">About</a></li>
            <li><a href="contact.html">Contact</a></li>
            <li><a href="login.html">Login</a></li>
            <li><a href="signup.html">Signup</a></li>
            <li><a href="nominate.html">Nominate</a></li> <!-- Add the Nominate page link -->
        </ul>
    </nav>

    <div class="container">
        <h2>Nominate a Contestant</h2>
        <form action="nominee_submission_process.php" method="POST" enctype="multipart/form-data">
            <label for="name">Full Name:</label>
            <input type="text" id="Name" name="Name" required>
            <label for="email">Email:</label>
            <input type="email" id="Email" name="Email" required>
            <label for="dob">Date of Birth:</label>
            <input type="date" id="Dob" name="Dob" required>

            <fieldset>
                <legend>Choose a category:</legend>
                <select id="Category" name="Category" required>
                    <option value="Ngo">NGO</option>
                    <option value="Tech" selected>Tech</option>
                    <option value="Telcom">Telcom</option>
                    <option value="Publishing">Publishing</option>
                    <option value="Music&Arts">Music & Arts</option>
                    <option value="Events">Events</option>
                </select>
            </fieldset>
            <label for="country">Country:</label>
            <input type="text" id="Country" name="Country" required>
            <label for="companyName">Company Name:</label>
            <input type="text" id="CompanyName" name="CompanyName" required>
            <label for="companyDescription">Company Description:</label>
            <textarea id="CompanyDescription" name="CompanyDescription" rows="4" required></textarea>
            <label for="businessStartedYear">Year the Business Started:</label>
            <input type="number" id="BusinessStartedYear" name="BusinessStartedYear" min="1900" max="2100" required>
            <label for="facebook">Facebook Link:</label>
            <input type="text" id="Facebook" name="Facebook" placeholder="Enter Facebook link">
            <label for="twitter">Twitter Link:</label>
            <input type="text" id="Twitter" name="Twitter" placeholder="Enter Twitter link">
            <label for="instagram">Instagram Link:</label>
            <input type="text" id="Instagram" name="Instagram" placeholder="Enter Instagram link">
            <label for="website">Website Link:</label>
            <input type="text" id="Website" name="Website" placeholder="Enter website link">
            <label for="employees">Number of Employees:</label>
            <input type="number" id="Employees" name="Employees" min="1" required>
            <label for="image">Image:</label>
            <input type="file" id="Image" name="Image" accept="image/*" required>
            <label for="ownershipProof">Proof of Company Ownership:</label>
            <input type="file" id="OwnershipProof" name="OwnershipProof" accept="image/*,application/pdf" required>
            <p>Company ownership details may include registration certificates, receipts and invoices, contracts, etc.
            </p>
            <button type="submit">Submit Nomination</button>
        </form>
    </div>

    <footer>
        <p>&copy; 2024 SBA Awards</p>
    </footer>
    <script src="admin/js/bootstrap.bundle.min.js"></script>

</body>

</html>