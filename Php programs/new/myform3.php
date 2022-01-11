
<?php
	session_start();
	echo "MY FIRST NAME IS :" . $_SESSION['fname']."<br/>" ;
	echo "MY LAST NAME IS :" . $_SESSION['lname']."<br/>";
	
	
	
	if (isset($_POST['submit'])) {
		
		$_SESSION['email'] = $_POST['email'];
		$_SESSION['phone'] = $_POST['phone'];
	}
	echo "MY EMAIL ID  IS :" . $_SESSION['email'] ."<br/>" ;
	echo "MY PHONE NUMBER  IS :" . $_SESSION['phone']. "<br/>";

	
	
	


	 ?>
	 <br>

	 <!DOCTYPE html>
	 <html>
	 <head>
	 	<title>last page</title>
	 </head>
	 <body>
	 	<form align="">
	 	<input type="checkbox" name="agree">I AGREE<br>
	 	<br>
	 		<input type="submit" name="submit" value="register" onclick="return confirm('THANKS FOR REGISTRATION')">
	 	</form>


	 
	 </body>
	 </html>
	 