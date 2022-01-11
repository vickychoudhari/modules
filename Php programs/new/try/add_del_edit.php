<!DOCTYPE html>
<html>
<head>
	<title>ADD DELETE AND EDIT FORM IN PHP AND MYSQL</title>
	
</head>
<style type="text/css"></style>
<body>
	
<br>
<br>

	<form action="actionpage.php" method="post" style="display:flex; flex-direction:column;">
		
<label for="name" style=" margin-bottom:10px; display:flex; color:blue;">NAME</label>
<input type="text" name="name" id="name"></br>

<label for="email" style=" margin-bottom:10px; display:flex; color:green;">EMAIL</label>
<input type="email" name="email" id="email"></br>

<label for="countries" style="margin-bottom:10px; display:flex; color:purple;">COUNTRIES</label>
<input type="text" name="countries" id="country"></br>


<label for="contact" style="margin-bottom:10px; display:flex; color:red">CONTACT</label>
<input type="number" name="contact" id="contact"></br>

<button type="submit" value="ADD">ADD</button>

</form>
<table>
	<tr>
		<th>ID</th>
		<th>NAME</th>
		<th>EMAIL</th>
		<th>country</th>
		<th>contact</th>
		<th colspan="2">ACTION</th>
	</tr>
	<!-- confiq of database -->
	<?php
	$servername ="localhost";
	$username ="root";
	$password ="root";
	$dbname ="add_del_edit";

	 // create connection
	$conn = new mysqli('$servername','$username','$password','$dbname');
     //check connection
	if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
} 
// select fields in users table

   $sql = "SELECT id, name, email, country, contact FROM users";
   $result = $conn->query($sql);
   // output data of each row

   while($row = $result->fetch_assoc()) {
        <tr>
        <td> <?php echo $row['id']?> </td>
        <td> <?php echo $row['name']?> </td>
        <td> <?php echo $row['email']?> </td>
        <td> <?php echo $row['country']?> </td>
        <td> <?php echo $row['contact']?> </td>

	?>

</table>	
</body>
</html>