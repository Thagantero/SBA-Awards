<?php

echo '<a href="home.php">Home<br /></a>';

$content = file_get_contents('php://input'); // Receives the JSON Result from Safaricom
$res = json_decode($content, true); // Converts the JSON to an array

$dataToLog = array(
    date("Y-m-d H:i:s"), // Date and time
    " MerchantRequestID: " . $res['Body']['stkCallback']['MerchantRequestID'],
    " CheckoutRequestID: " . $res['Body']['stkCallback']['CheckoutRequestID'],
    " ResultCode: " . $res['Body']['stkCallback']['ResultCode'],
    " ResultDesc: " . $res['Body']['stkCallback']['ResultDesc'],
);

$data = implode(" - ", $dataToLog);
$data .= PHP_EOL;
file_put_contents('transaction_log.txt', $data, FILE_APPEND); // Logs the results to our log file

// Database connection details
$servername = (getenv('SBA_DB_HOST') ?: 'localhost');
$username = (string) getenv('SBA_DB_USER');
$password = (string) getenv('SBA_DB_PASSWORD');
$database = (string) getenv('SBA_DB_NAME');

// Saves the result to the database
try {
    $conn = new PDO("mysql:host=$servername;dbname=$database", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $merchantRequestId = $res['Body']['stkCallback']['MerchantRequestID'];
    $resultCode = $res['Body']['stkCallback']['ResultCode'];

    // Retrieve the corresponding order details
    $stmt = $conn->prepare("SELECT * FROM orders WHERE MerchantRequestID = :merchantRequestId LIMIT 1");
    $stmt->bindParam(':merchantRequestId', $merchantRequestId, PDO::PARAM_STR);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row) {
        $ID = $row['ID'];
        if ($resultCode == '1032') {
            // Payment was cancelled
            $sql = $conn->prepare("UPDATE orders SET Status = 'CANCELLED' WHERE ID = :ID");
        } else {
            // Payment was successful
            $sql = $conn->prepare("UPDATE orders SET Status = 'SUCCESS' WHERE ID = :ID");
        }
        $sql->bindParam(':ID', $ID, PDO::PARAM_INT);
        $rs = $sql->execute();

        if ($rs) {
            file_put_contents('error_log.txt', "Records Updated" . PHP_EOL, FILE_APPEND);
        } else {
            file_put_contents('error_log.txt', "Failed to update Records" . PHP_EOL, FILE_APPEND);
        }
    } else {
        file_put_contents('error_log.txt', "No order found for MerchantRequestID: " . $merchantRequestId . PHP_EOL, FILE_APPEND);
    }
} catch (PDOException $e) {
    file_put_contents('error_log.txt', "Database Error: " . $e->getMessage() . PHP_EOL, FILE_APPEND);
}

?>
