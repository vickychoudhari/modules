

<?php session_start(); ?>
<!DOCTYPE html>
<html>
<head>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="stylesheet" type="text/css" href="style2.css">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
	<title>services</title>
</head>
<body>
	<div class="top1">
		<div class="logo1">
			<img src="image/head.png">
		</div>
	</div>
	<div class="bar1">
		<h5>CMC Patient Portal</h5>
	</div>
	<div class="body1">
		<div id="header" style="margin:20px 00 00 19px; text-align:center; font-size:30px;">
			Welcome CMC Portral
		</div>
		<br>
		<br>
		<br>
		<br>
		<?php
		if(isset($_SESSION['valid'])) {			
			include("config.php");					
			$result = mysqli_query($mysqli, "SELECT * FROM login");
			?>
			<br>	
			<div style="text-align:center; font-size:20px;">Welcome to CMC Hospital ....&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;       <?php echo $_SESSION['name'] ?> ! <a href='logout.php'>Logout</a><br/></div>
			<br/>
			<div style="text-align: center;"><a href='view.php' style="font-size:25px;">Enter the all Medicine and OT Products</a></div>
			<br/><br/>
			<?php	
		} else {
			echo "<center><h4>You must be logged in to view this page.</h4><br/><br/>";
			echo "<a href='login.php'>Login</a> | <a href='register.php'>Register</a>";
		}
		?>
		<br>
		<br>
		<div id="footer" style="text-align:center;">
			Created by <a href="https://www.drupal.org/u/vishal-choudhary" title="vishal choudhary">vishal choudhary</a>
		</div>
		<div class="new" style="text-align:right; margin: 10px;"><a href="home.html">BACK TO HOME</a></div>
	</body>
	</html>
