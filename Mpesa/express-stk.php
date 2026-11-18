<?php
session_start();

$errors = array();
$errmsg = '';

$config = array(
    "env"              => "sandbox", // Change to "live" for production environment
    "BusinessShortCode" => (string) getenv('SBA_MPESA_SHORTCODE'), // Replace with your Business Shortcode
    "key" => (string) getenv('SBA_MPESA_KEY'), // Replace with your Consumer Key
    "secret" => (string) getenv('SBA_MPESA_SECRET'), // Replace with your Consumer Secret
    "username" => (string) getenv('SBA_MPESA_INITIATOR_NAME'),
    "TransactionType"  => "CustomerPayBillOnline",
    "passkey" => (string) getenv('SBA_MPESA_PASSKEY'),

    "CallBackURL" => (string) getenv('SBA_MPESA_CALLBACK_URL'), // Update with your callback URL
    "AccountReference" => "SBA Awards",
    "TransactionDesc"  => "Payment for voting credits"
);

if (isset($_POST['phone_number']) && isset($_POST['orderNo'])) {
    $phone = $_POST['phone_number'];
    $orderNo = $_POST['orderNo'];
    $amount = 1; // Adjust this as per your actual payment amount

    // Format phone number to Safaricom's requirement
    $phone = (substr($phone, 0, 1) == "+") ? str_replace("+", "", $phone) : $phone;
    $phone = (substr($phone, 0, 1) == "0") ? preg_replace("/^0/", "254", $phone) : $phone;
    $phone = (substr($phone, 0, 1) == "7") ? "254{$phone}" : $phone;

    // Get access token
   $access_token_url = ($config['env'] == "live") ? "https://api.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials" : "https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials";
$credentials = base64_encode($config['key'] . ':' . $config['secret']);

$ch = curl_init($access_token_url);
curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Basic " . $credentials]);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
$response = curl_exec($ch);
curl_close($ch);

$result = json_decode($response, true);
$token = isset($result['access_token']) ? $result['access_token'] : null;

if (!$token) {
    // Handle error, perhaps log it or display an error message
    die("Failed to retrieve access token.");
}

    // Prepare STK Push request
    $timestamp = date("YmdHis");
    $password = base64_encode($config['BusinessShortCode'] . $config['passkey'] . $timestamp);

    $stk_push_url = ($config['env'] == "live") ? "https://api.safaricom.co.ke/mpesa/stkpush/v1/processrequest" : "https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest";

    $stk_push_data = array(
        "BusinessShortCode" => $config['BusinessShortCode'],
        "Password" => $password,
        "Timestamp" => $timestamp,
        "TransactionType" => $config['TransactionType'],
        "Amount" => $amount,
        "PartyA" => $phone,
        "PartyB" => $config['BusinessShortCode'],
        "PhoneNumber" => $phone,
        "CallBackURL" => $config['CallBackURL'],
        "AccountReference" => $config['AccountReference'],
        "TransactionDesc" => $config['TransactionDesc']
    );

    $stk_push_json = json_encode($stk_push_data);

    $ch = curl_init($stk_push_url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Authorization: Bearer ' . $token));
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $stk_push_json);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);
    curl_close($ch);

    $result = json_decode($response, true);

    if (isset($result['ResponseCode']) && $result['ResponseCode'] == '0') {
        // STK Push request successful, save order details to database
        $MerchantRequestID = $result['MerchantRequestID'];
        $CheckoutRequestID = $result['CheckoutRequestID'];

        // Save to your database using prepared statements (example using MySQLi)
        $servername = (getenv('SBA_DB_HOST') ?: 'localhost');
        $username = (string) getenv('SBA_DB_USER');
        $password = (string) getenv('SBA_DB_PASSWORD');
        $database = (string) getenv('SBA_DB_NAME');

        $conn = mysqli_connect($servername, $username, $password, $database);

        if (!$conn) {
            die("Connection failed: " . mysqli_connect_error());
        }

        $sql = "INSERT INTO orders (OrderNo, Amount, Phone, CheckoutRequestID, MerchantRequestID) 
                VALUES (?, ?, ?, ?, ?)";

        $stmt = mysqli_prepare($conn, $sql);
        mysqli_stmt_bind_param($stmt, "sissi", $orderNo, $amount, $phone, $CheckoutRequestID, $MerchantRequestID);

        if (mysqli_stmt_execute($stmt)) {
            $_SESSION["MerchantRequestID"] = $MerchantRequestID;
            $_SESSION["CheckoutRequestID"] = $CheckoutRequestID;
            $_SESSION["phone"] = $phone;
            $_SESSION["orderNo"] = $orderNo;

            // Redirect to confirmation page
            header('Location: confirm-payment.php');
            exit;
        } else {
            $errmsg = "Error: " . mysqli_error($conn);
        }

        mysqli_stmt_close($stmt);
        mysqli_close($conn);
    } else {
        // STK Push request failed
        $errors['mpesastk'] = isset($result['errorMessage']) ? $result['errorMessage'] : 'Failed to initiate payment. Please try again later.';
    }

    // Handle errors
    if (!empty($errors)) {
        foreach ($errors as $error) {
            $errmsg .= $error . '<br />';
        }
    }
}

// Display errors or other messages as needed
if (!empty($errmsg)) {
    echo $errmsg;
}
?>
