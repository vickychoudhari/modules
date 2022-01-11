<?php session_start(); ?>

<?php
if(!isset($_SESSION['valid'])) {
	header('Location: login.php');
}
?>

<?php
//including the database connection file
include_once("config.php");

//fetching data in descending order (lastest entry first)
$result = mysqli_query($mysqli, "SELECT * FROM products WHERE login_id=".$_SESSION['id']." ORDER BY id DESC");
?>

<!DOCTYPE html>
<html>
<head>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="stylesheet" type="text/css" href="style3.css">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
	<title>services</title>
</head>
<body  style="align-content: center;">
	<div class="top1">
		<div class="logo1">
			<img src="image/head.png">
		</div>
	</div>
	<div class="bar1">
		<h5>CMC Patient Portal</h5>
	</div>
	<br>
	<body>
		<a href="index.php" style="text-decoration: none; margin-left:400px;">Home</a> &nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;   <a href="add.html" style="text-decoration: none; ">Add New Medicine And Medical Product &nbsp;&nbsp;&nbsp;</a> &nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp; <a href="logout.php" style="text-decoration: none;">Logout</a>
		<br/><br/>

		<table width='80%' border=0 align="center">
			<tr bgcolor='#CCCCCC'>
				<td> Medicine Name</td>
				<td>Quantity</td>
				<td>Price (RUPEES)</td>
				<td>Update/Delete</td>
			</tr>
			<?php

			

			while($res = mysqli_fetch_array($result)) {		
				echo "<tr>";
				echo "<td>".$res['name']."</td>";
				echo "<td>".$res['qty']."</td>";
				echo "<td>".$res['price']."</td>";	
				echo "<td><a href=\"edit.php?id=$res[id]\">Edit</a> | <a href=\"delete.php?id=$res[id]\" onClick=\"return confirm('Are you sure you want to delete?')\">Delete</a></td>";

			}
			?>

		</table>	
	</body>
	</html>
