<?php
$name    = $_POST['name'];
$email   = $_POST['email'];
$country = $_POST['country'];
$contact = $_POST['contact'];
$servername= "localhost";
$username  = "root";
$password  = "root";
$dbname    ="add_del_edit";
$id =$_GET['id'];
if (!action) {
	$action=$_GET['action'];
}
// create connection
$conn = new mysqli('$servername','$username','$password','$dbname');
// check connection
if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
} 
if (action =='ADD') {
	$sql="INSERT into users(name,email,country,contact)
	values('".$name."','".$email."','".$country."','".$contact."')";
}

?>
