<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Welcome to Student Information Center:: NEMCO Surigao City</title>
<style type="text/css">

</style>
</head>

<body bgcolor="000000">

<br />
<br />
<br />

<table align="center" cellpadding="0" bgcolor="#FFFFFF" width="800" border="0">
  <tr>
    <td align="left"><h1 align="right" class="heading"><a href="logout.php"><img border="0" src="admin/images/cooltext457955210.png" onmouseover="this.src='admin/images/cooltext457955210MouseOver.png';" onmouseout="this.src='admin/images/cooltext457955210.png';" /></a></h1>
      <h1 align="center" class="heading"><img src="admin/images/cooltext457954659.png" width="830" height="51" alt="Welcome to Site" /></h1>
      <p align="center">
	   <a href="home.php"><img border="0" src="admin/images/cooltext457951462.png" alt="Go Back" onmouseover="this.src='admin/images/cooltext457951462MouseOver.png';" onmouseout="this.src='admin/images/cooltext457951462.png';" /><br><br></a>
        <?php
 include ('connect.php');
 $search = $_POST['txtsearch'];
 
 $sql ="select * from students where id='$search' or sname='$search'";
 
 $x =mysql_query( $sql);
 $i=mysql_fetch_array($x);
 
 if ($x)
 {
 
 $id =$i['id'];
 $fname =$i['sname'];

 $course =$i['dname'];
echo " <strong>Record Found <br><br>";
echo "ID No. : $id<br><br>";
echo "Name of Student: $fname<br><br>";
echo "Department: $course<br><br>";



 }

 else
 {
 echo" No record found";
 }
 exit();
 ?>
      </p>
	  <p align="right"><a href="home.php"></tr>
      	</table>
  </tr>
  <tr>
    <td>&nbsp;</td>
  </tr>
</table>
<h1 align="center" class="heading">&nbsp;</h1>
</body>
</html>
