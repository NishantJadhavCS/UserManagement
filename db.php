<?php
$servername = "localhost";
$username = "nishant"; // Your MySQL username
$password = "Nishant@10";
$dbname = "users"; // Your database name

// Create connection
$con = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($con->connect_error) {
    die("Connection failed: " . $con->connect_error);
}

// echo "Connected successfully to the database!";

?>