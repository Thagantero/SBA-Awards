<?php

// M-Pesa API Credentials
$consumerKey = (string) getenv('SBA_MPESA_KEY');
$consumerSecret = (string) getenv('SBA_MPESA_SECRET');
$shortCode = (string) getenv('SBA_MPESA_SHORTCODE');
$passkey = (string) getenv('SBA_MPESA_PASSKEY');

// Callback URL
$callbackUrl = (string) getenv('SBA_MPESA_CALLBACK_URL');

// Receive POST data from Donate.php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postData = json_decode(file_get_contents('php://input'), true);

    if (isset($postData['phone']) && isset($postData['amount'])) {
        $phone = $postData['phone'];
        $amount = $postData['amount'];

        // Validate and sanitize phone number and amount
        $phone = filter_var($phone, FILTER_SANITIZE_STRING);
        $amount = filter_var($amount, FILTER_SANITIZE_NUMBER_INT);

        // Prepare STK Push request
        $url = 'https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest';
        $timestamp = date('YmdHis');
        $password = base64_encode($shortCode . $passkey . $timestamp);

        $postData = [
            'BusinessShortCode' => $shortCode,
            'Password' => $password,
            'Timestamp' => $timestamp,
            'TransactionType' => 'CustomerPayBillOnline',
            'Amount' => $amount,
            'PartyA' => $phone,
            'PartyB' => $shortCode,
            'PhoneNumber' => $phone,
            'CallBackURL' => $callbackUrl,
            'AccountReference' => 'SBA Awards',
            'TransactionDesc' => 'Payment for voting credits'
        ];

        $headers = [
            'Content-Type: application/json',
            'Authorization: Basic ' . base64_encode($consumerKey . ':' . $consumerSecret)
        ];

        // Send STK Push request using cURL
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        curl_close($ch);

        // Process response
        $result = json_decode($response, true);

        if (isset($result['errorCode'])) {
            $output = [
                'status' => 'error',
                'message' => 'Failed to initiate payment. Please try again later.'
            ];
        } else {
            $output = [
                'status' => 'success',
                'message' => 'Payment initiated successfully.'
            ];
        }

        header('Content-Type: application/json');
        echo json_encode($output);
    } else {
        // Invalid or missing data
        $output = [
            'status' => 'error',
            'message' => 'Invalid request. Please provide phone number and amount.'
        ];
        header('Content-Type: application/json');
        echo json_encode($output);
    }
}
?>
