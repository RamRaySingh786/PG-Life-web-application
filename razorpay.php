<?php
session_start();
require "includes/database_connect.php";

// Assume you have the property_id in the session or as a parameter
$property_id = $_GET["property_id"];

// Fetch the rent amount from the database based on the selected PG
$sql = "SELECT rent FROM properties WHERE id = $property_id";
$result = mysqli_query($conn, $sql);

if (!$result) {
    echo "Error fetching rent amount";
    return;
}

$property = mysqli_fetch_assoc($result);
$rent_amount = $property['rent'];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Razorpay Demo</title>
</head>

<body>
    <input type="number" id="amount" placeholder="Enter amount" value="<?= $rent_amount ?>" />
    <input type="button" value="Pay Now" onclick="PayNow()" />
    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
    <script>
        const PayNow = () => {
            let amount = document.getElementById("amount").value;

            let options = {
                key: "rzp_test_dRWiKHS7zr2Gki",
                amount: amount * 100,
                name: "Online PG Accommodation",
                description: "Payment Gateway",
                image: "https://cdn3.vectorstock.com/i/1000x1000/98/22/logo-for-grocery-store-vector-21609822.jpg",
                handler: function (response) {
                    RazorPayResponse(response, amount);
                },
                prefill: {
                    name: "",
                    email: "",
                },
                notes: {
                    address: "",
                },
                theme: {
                    color: "#942436",
                },
            };

            var rzp1 = new Razorpay(options);
            rzp1.open();
        };

        const RazorPayResponse = (response, amount) => {
            if (response.razorpay_payment_id !== "") {
                console.log(response.razorpay_payment_id);
                alert("Payment Successful");

                // Insert payment details into MySQL database
                let formData = new FormData();
                formData.append("razorpay_payment_id", response.razorpay_payment_id);
                formData.append("amount", amount);
                formData.append("user_id", <?php echo $_SESSION['user_id']; ?>); // Use the actual user ID
                formData.append("property_id", <?php echo $property_id; ?>); // Use the actual property ID

                fetch("insert_payment_details.php", {
                    method: "POST",
                    body: formData,
                })
                    .then(response => response.json())
                    .then(data => {
                        // Handle server response if needed
                        console.log(data);
                    })
                    .catch(error => {
                        console.error("Error:", error);
                    });
            } else {
                alert("Payment Failed");
            }
        };
    </script>
</body>

</html>
