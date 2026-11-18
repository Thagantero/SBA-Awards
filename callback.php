<?php
// Handle callback from Safaricom
$response = file_get_contents('php://input');
$logFile = "stk_push_callback_response.json";

$log = fopen($logFile, "a");
fwrite($log, $response);
fclose($log);

// Parse and process the response here
// Update user's voting credits in the database based on the callback details
?>
