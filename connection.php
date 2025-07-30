<?php 

$hostname ="localhost:3310";
$username = "root";
$password ="";
$database= "pos";

$connection =mysqli_connect($hostname, $username, $password, $database) or die("Cannot connect to database successfully".mysqli_connect_error());

?>