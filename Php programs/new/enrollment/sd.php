<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>Untitled Document</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>

<body>
  <?php
  
    include ('connect.php');
	$search = $_POST['txtsearch'];
	$sql ="SELECT *FROM tblstudent where studid='$search' OR CONCAT (lname,',',fname) ='$search'";
	
  $x =mysql_query($sql);
  $i =mysql_fetch_array($x);
  
    if($x)
	{
	
    $id= $i['studid'];
    $fname= $i['fname'];
    $lname= $i['lname'];
    $course= $i['course'];
     }
	 
	 else
	 
	 {
	 
	 echo "No record found";
	 exit();
	 }
 ?>
	 

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<title>Untitled Document</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>

<body>
<form method="post"action="file:///F|/php/pHp_-_exerCxe/delete.php">
<input type="hidden"name="txtid"value="<?php echo $ id;?>">
<table border="0">

<tr><td colspan="2"><center>DELETE RECORD</center></td></tr>
</tr>
  <td>Firstname</td>
</td><input type="text"name="txtfname"value="<?php echo $fname;?>"></td>
</tr>
</tr>
  <td>Lastname</td>
</td><input type="text"name="txtlname"value="<?php echo $lname;?>"></td>
</tr>
  <td>Course</td>
</td><input type="text"name="course"value="<?php echo $course;?>"></td>
</tr>
<tr>
<td>&nbsp;</td>
<td>
  <input type="reset"value="cancel">
  <input type="submit"value="delete">
</td>
</tr>
</table>

</form>
</body>
</html>


