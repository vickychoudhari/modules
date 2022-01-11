<?php
$con =mysqli_connect('localhost','root','root');
mysqli_select_db($con,'crudajax'); 

extract($_POST);

if(isset($_POST['submit'])) {
	$q ="INSERT INTO ajax(username,password)VALUES('$username','$password')";
	$query =mysqli_query($con,$q);
	$con->close();
	header('location:insert.php');
}

?>