<?php
$servername = "skibidi";
$username = "skibidi";
$password = "skibidi";
$dbname = "skibidi";
$token = "your_token_here";  // Replace with your actual token
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
