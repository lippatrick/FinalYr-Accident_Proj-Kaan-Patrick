<?php

function getMyIP()
{
    $url = "https://collecto.cissytech.com/get-my-ip";

    // Initialize cURL session
    $ch = curl_init();

    // Set cURL options
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Optional: Ignore SSL verification if necessary
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);

    // Execute the request
    $response = curl_exec($ch);

    // Check for errors
    if (curl_errno($ch)) {
        echo "cURL Error: " . curl_error($ch);
    } else {
        echo "Response: " . $response;
    }

    // Close cURL session
    curl_close($ch);
}

// Call the function
getMyIP();
