<?php
$host ='localhost';
$user ='root';
$password = 'root';
$dbname ='SHOWIMAGE';

$conn = mysqli($host,$user,$password,$dbname);
echo "connected suucess";
if ($conn->connect_error){
	die("Connection failed: " . $db->connect_error);
}