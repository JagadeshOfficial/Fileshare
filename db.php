<?php
$host = "localhost";
$user = "root"; // your DB username
$pass = "Sivani@123";     // #_ZIEmf42(^k your DB password 
$dbname = "fileshare"; // judicial_newdbfile your DB name       jwjwjqhzrukymdkc

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
