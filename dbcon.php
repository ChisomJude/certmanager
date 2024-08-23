<?php
$conn = new mysqli("localhost", "root", "", "certmanager");

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
