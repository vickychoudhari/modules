<?php
$servername = 'localhost';
$username = 'root';
$password = 'root';
$dbname = 'drupal8';


//create connection

$conn = new mysqli($servername,$username,$password,$dbname);
if ($conn->connect_error) {
	die("Connection failed: " . $conn->connect_error);
}
// echo "<prE>";
// print_r($conn);
// die();
 
// Attempt insert query execution
$sql = "INSERT INTO node_field_data(status,title) VALUES (01,'third comment')";
if(mysqli_query($conn, $sql)){
    echo "Records inserted successfully.";
} else{
    echo "ERROR: Could not able to execute $sql. " . mysqli_error($conn);
}
 
// Close connection
mysqli_close($link);
?>