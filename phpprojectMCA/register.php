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
		<a href="index.php" style="text-decoration: none; margin-left:10px;">Home</a> <br />
		<?php
		include("config.php");

		if(isset($_POST['submit'])) {
			$name = $_POST['name'];
			$email = $_POST['email'];
			$user = $_POST['username'];
			$pass = $_POST['password'];

			if($user == "" || $pass == "" || $name == "" || $email == "") {
				echo "All fields should be filled. Either one or many fields are empty.";
				echo "<br/>";
				echo "<a href='register.php'>Go back</a>";
			} else {
				mysqli_query($mysqli, "INSERT INTO login(name, email, username, password) VALUES('$name', '$email', '$user', md5('$pass'))")
				or die("Could not execute the insert query.");
				
				echo "Registration successfully";
				echo "<br/>";
				echo "<a href='login.php'>Login</a>";
			}
		} else {
			?>
			<p align="center"><font size="+2">Register</font></p><br>
			<form name="form1" method="post" action="">
				<table width="75%" border="0" align="center" style="margin-left:450px;">
					<tr> 
						<td width="10%">Full Name</td>
						<td><input type="text" name="name"></td>
					</tr>
					<tr> 
						<td>Email</td>
						<td><input type="text" name="email"></td>
					</tr>			
					<tr> 
						<td>Username</td>
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
			<?php
		}
		?>
	</body>
	</html>
