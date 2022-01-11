<?php session_start(); ?>
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
	<a href="index.php" style="text-decoration: none; margin-left:10px;">Home</a> <br /><br /><br /><br />
	<?php
	include("config.php");

	if(isset($_POST['submit'])) {
		$user = mysqli_real_escape_string($mysqli, $_POST['username']);
		$pass = mysqli_real_escape_string($mysqli, $_POST['password']);

		if($user == "" || $pass == "") {
			echo "Either username or password field is empty.";
			echo "<br/>";
			echo "<a href='login.php'>Go back</a>";
			echo "<br/>";

			echo "<br/>";
			echo "<br/>";
			echo "<br/>";
			echo "<br>";"<a href='login.php'>Go back</a>";
		} else {
			$result = mysqli_query($mysqli, "SELECT * FROM login WHERE username='$user' AND password=md5('$pass')")
			or die("Could not execute the select query.");
			
			$row = mysqli_fetch_assoc($result);
			
			if(is_array($row) && !empty($row)) {
				$validuser = $row['username'];
				$_SESSION['valid'] = $validuser;
				$_SESSION['name'] = $row['name'];
				$_SESSION['id'] = $row['id'];	
			} else {
				echo "Invalid username or password.";
				echo "<br/>";
				echo "<a href='login.php'>Go back</a>";
			}

			if(isset($_SESSION['valid'])) {
				header('Location: index.php');			
			}
		}
	} else {
		?>
		<p align="center"><font size="+2">Login TO CMC Portral</font></p>
		<form name="form1" method="post" action="">
			<center><table width="75%" border="0" style="margin-left:500px;">
				<tr> 
					<td width="10%">Username</td>
					<td><input type="text" name="username"></td>
				</tr>
				<tr> 
					<td>Password</td>
					<td><input type="password" name="password"></td>
				</tr>
				<tr> 
					<td>&nbsp;</td>
					<td><input type="submit" name="submit" value="Submit"></td>
				</tr>
			</table>
		</form>
	</center>
	<?php
}
?>
</body>
</html>
