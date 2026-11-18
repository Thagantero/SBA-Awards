<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buy Voting Credits - SBA Awards</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f0f0f0;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .container {
            background-color: #fff;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            width: 80%;
            max-width: 600px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 5px;
        }
        .form-group input {
            width: 100%;
            padding: 8px;
            font-size: 16px;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box;
        }
        .form-group button {
            background-color: #007bff;
            color: #fff;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }
        .form-group button:hover {
            background-color: #0056b3;
        }
        .confirmation {
            display: none;
            border: 1px solid #ccc;
            padding: 10px;
            margin-top: 10px;
            background-color: #f9f9f9;
            border-radius: 5px;
        }
        .confirmation p {
            margin: 0;
        }
        .confirmation button {
            background-color: #28a745;
            color: #fff;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            margin-top: 10px;
        }
        .confirmation button:hover {
            background-color: #218838;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Buy Voting Credits</h2>
        <div class="form-group">
            <label for="phone">Phone Number:</label>
            <input type="text" id="phone" name="phone" placeholder="Enter your phone number" required>
        </div>
        <div class="form-group">
            <label for="votes">Number of Votes:</label>
            <input type="number" id="votes" name="votes" placeholder="Enter number of votes" min="1" required>
        </div>
        <div class="form-group">
            <button type="button" onclick="confirmPayment()">Buy Credits</button>
        </div>
        <div class="confirmation" id="confirmation">
            <p>Confirm Purchase:</p>
            <p>Phone Number: <span id="confirmPhone"></span></p>
            <p>Number of Votes: <span id="confirmVotes"></span></p>
            <p>Total Amount: KES <span id="confirmAmount"></span></p>
            <button type="button" onclick="makePayment()">Confirm Payment</button>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
    <script>
        function confirmPayment() {
            const phone = document.getElementById('phone').value;
            const votes = document.getElementById('votes').value;

            if (phone.trim() === '' || votes.trim() === '') {
                alert('Please fill in all fields.');
                return;
            }

            const totalAmount = votes * 10; // Each vote costs KES 10

            // Display confirmation details
            document.getElementById('confirmPhone').textContent = phone;
            document.getElementById('confirmVotes').textContent = votes;
            document.getElementById('confirmAmount').textContent = totalAmount;

            // Show confirmation section
            document.getElementById('confirmation').style.display = 'block';
        }

        function makePayment() {
            const phone = document.getElementById('phone').value;
            const votes = document.getElementById('votes').value;
            const totalAmount = votes * 10; // Each vote costs KES 10

            axios.post('process_payments.php', { phone, amount: totalAmount })
                .then(response => {
                    console.log(response.data);
                    alert('Payment successful! Credits purchased: ' + votes);
                    window.location.reload(); // Reload page after successful payment
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('Payment failed. Please try again later.');
                });
        }
    </script>
</body>
</html>
