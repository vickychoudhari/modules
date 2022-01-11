<!DOCTYPE html>
<html>
<body>

<?php
$servername = "localhost";
$username = "root";
$password = "root";
$dbname = "oop";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$sql = "SELECT uid, uname, upass,fullname,uemail FROM users";
$result = $conn->query($sql);
// echo "<pre>";
// print_r($result);
// die();

 if ($result->num_rows > 1) {
    // output data of each row
    while($row = $result->fetch_assoc()){
    	  echo "-id: " . $row["uid"]. "<br>".  " - UserName: " . $row["uname"]. "<br>". "- Fullname: " . $row['fullname']. "<br>". "-Email: " . $row['uemail']. "<br>". "<br>";
    	  // echo "<pre>";
    	  // print_r($row);
    	  // die();
 }       
} else {
    echo "0 results";
}
?>	

</body>
</html>