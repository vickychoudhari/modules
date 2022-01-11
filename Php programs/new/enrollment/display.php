<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>Untitled Document</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>

<body>
<form name="form1" method="post" action="">
  <table width="304" border="1">
    <tr>
      <td colspan="3"><div align="center">Student Records </div></td>
    </tr>
    <tr>
      <td width="84"><div align="center">Full Name </div></td>
      <td width="84"><div align="center">Department</div></td>
      <td width="114"><div align="center">School Year</div></td>
    </tr>
	
	
	<?php
		include ('connect.php');//connect pirmi
		$lname = $_POST['sname'];
		$result1 = mysql_query("select sname,rollno,regno from students where sname = '$lname' ");
		while ($row = mysql_fetch_array($result1))
		{
			
	   	echo'
		<tr>
      		<td>'.$row['sname'].'</td>
      		<td>'.$row['rollno'].'</td>
     	 	<td>'.$row['regno'].'</td>
    	</tr>';
		}

    ?>
  </table>
</form>
</body>
</html>
