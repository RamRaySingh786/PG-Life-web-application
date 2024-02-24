<?php
$razorpay_payment_id = $_POST['razorpay_payment_id'];
$amount = $_POST['amount'];
$user_id = $_POST['user_id'];
$property_id = $_POST['property_id'];
$payment_status = "Success"; // You may adjust this based on your logic

// Connect to your MySQL database
$conn = new mysqli("127.0.0.1:3306", "root", "", "pglife");

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Insert payment details into the database
$sql = "INSERT INTO payment_details (user_id, property_id, razorpay_payment_id, amount, payment_status) VALUES ('$user_id', '$property_id', '$razorpay_payment_id', '$amount', '$payment_status')";

if ($conn->query($sql) === TRUE) {
    $response = array("status" => "success", "message" => "Payment details inserted successfully");
} else {
    $response = array("status" => "error", "message" => "Error: " . $sql . "<br>" . $conn->error);
}

// Close the database connection
$conn->close();

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);
?>
