<?php
$name    = $_POST['name'];
$email   = $_POST['email'];
$country = $_POST['country'];
$contact = $_POST['contact'];
$action  =$_POST['action'];
$servername = "localhost";
$username  = "root";
$dbpass = "root";
$dbname    ="add_edit_del";
$id = $_GET['id'];
if(! $action) {
	$action = $_GET['action'];
}
// create connection
$conn = new mysqli($servername,$username,$dbpass,$dbname);
// check connection
if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
} 
if ($action == 'add') {
	$sql="INSERT INTO users (name,email,country,contact)
	values('".$name."','".$email."','".$country."','".$contact."')";
}
if($action == 'del') {
	$sql = "DELETE fROM users WHERE id=$id";
}
if($action == 'update'){
	 $sql ="UPDATE `users` SET `name`=[".$name."],`email`=[".$email."],`country`=[".$country."],`contact`=[".$contact."] WHERE 'id' = $id";
 	$result =mysql_query($conn, $sql);
}
if ($conn->query($sql) === TRUE) {
    echo "New record created successfully";
} else {
    echo "Error: " . $sql . "<br>" . $conn->error;
}

$conn->close();
header("Location: add.php");
?>
<!-- $sql ="UPDATE `users` SET `name`=[".$name."],`email`=[".$email."],`country`=[".$country."],`contact`=[".$contact."] WHERE 'id' = $id"; -->
 	<!-- $result =mysql_query($conn, $sql); -->