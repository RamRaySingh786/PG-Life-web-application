<?php
require __DIR__ . '/vendor/autoload.php';

// Include the Stripe PHP SDK
require_once(stripe-php/init.php);

// Set your Stripe secret key
\Stripe\Stripe::setApiKey('sk_test_51O3ixHSHD5qJV7G04XTLTJBDcPBPxrexQVeqKPS9sIVcLDiDTt2ycEmkeIp1KioHerNFQezdjeawUjUm9s3DbX2p0090jNGyBD');

// Get the property ID and other necessary information from the URL or session
$property_id = $_GET["property_id"];

// Handle the payment (e.g., charge the customer)
try {
    // Create a Stripe Payment Intent or perform a charge
    $paymentIntent = \Stripe\PaymentIntent::create([
        'amount' => $property['rent'] * 100, // Amount in cents
        'currency' => 'usd',
    ]);

    // Handle successful payment (e.g., update your database)
    // You can use $paymentIntent to retrieve payment details

    // Redirect to a thank you page or display a success message
    header("Location: thank_you.php");
} catch (\Stripe\Exception\CardException $e) {
    // Handle card-related errors
} catch (\Stripe\Exception\RateLimitException $e) {
    // Handle rate limit-related errors
} catch (\Stripe\Exception\InvalidRequestException $e) {
    // Handle invalid request errors
} catch (\Stripe\Exception\AuthenticationException $e) {
    // Handle authentication errors
} catch (\Stripe\Exception\ApiConnectionException $e) {
    // Handle API connection errors
} catch (\Stripe\Exception\ApiErrorException $e) {
    // Handle generic API errors
} catch (Exception $e) {
    // Handle other errors
}
