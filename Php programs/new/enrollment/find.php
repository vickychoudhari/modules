<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Welcome to Student Information Center:: NEMCO Surigao City</title>
<style type="text/css">
<!--
.heading {
	color: #F90;
	font-family: "Comic Sans MS", cursive;
}<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Welcome to Student Information Center:: NEMCO Surigao City</title>
<style type="text/css">
<!--
.heading {
	color: #F90;
	font-family: "Comic Sans MS", cursive;
}
.options {
	font-family: "Comic Sans MS", cursive;
	font-size: 16px;
	font-style: oblique;
	color: #F93;
}
-->
</style>
</head>

<body bgcolor="000000">

<br />
<br />
<br />

<table align="center" cellpadding="0" bgcolor="#FFFFFF" width="800" border="0">
  <tr>
    <td align="left"><h1 align="right" class="heading"><a href="logout.php"><img border="0" src="admin/images/cooltext457955210.png" onMouseOver="this.src='admin/images/cooltext457955210MouseOver.png';" onMouseOut="this.src='admin/images/cooltext457955210.png';" /></a></h1>
      <h1 align="center" class="heading"><img src="admin/images/cooltext457954659.png" width="830" height="51" alt="Welcome to Site" /></h1>
      <p align="center">
	  <form name="form1" method="post" action="" >
       Enter name : <input type="text" name="sname" />
	   <input type="submit" name="Submit" value="Submit" />
      </form>
      </p>
      <p align="center"><a href="about.php"><img src="admin/images/cooltext457954859.png" border="0" align="middle" onMouseOver="this.src='admin/images/cooltext457954859MouseOver.png';" onMouseOut="this.src='admin/images/cooltext457954859.png';" /></a><a href="admin/index.php"><img src="admin/images/cooltext457954941.png" border="0" align="top" onMouseOver="this.src='admin/images/cooltext457954941MouseOver.png';" onMouseOut="this.src='admin/images/cooltext457954941.png';" /></a></p>
      <p align="center"><?php 
	 	 $link=mysql_connect("localhost","root","") or die("Cannot Connect to the database!");
	
	 mysql_select_db("department",$link) or die ("Cannot select the database!");
	 $query="SELECT * FROM students";
		
		  $resource=mysql_query($query,$link);
		  echo "
		<table align=\"center\" border=\"0\" width=\"70%\">
		<tr>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;LIST OF STUDENTS 
		<tr><td></td></tr>
		<tr><td></td></tr>
		<td><b>Name</b></td> <td><b>I.D. No.</b></td><td><b>School Year</b></td><td><b>Deparment</b></td></tr> ";
while($result=mysql_fetch_array($resource))
	{ 
	echo "<tr><td>".$result[1]."</td><td>".$result[2]."</td><td>".$result[3]."</td><td>".$result[4]."</td></tr>";
	} echo "</table>";
	 ?>
      </p>
      <p align="center">&nbsp;</p>
      <p align="center">&nbsp;</p>
      <p align="center">&nbsp;</p>
      <center>
        <a href="about.php"><img src="" alt="4 D's" width="395" height="100" border="0" align="bottom" /></a>
      </center>
    <p align="left">&nbsp;</p></td>
  </tr>
  <tr>
    <td>&nbsp;</td>
  </tr>
</table>
<h1 align="center" class="heading">&nbsp;</h1>
</body>
</html>
.options {
	font-family: "Comic Sans MS", cursive;
	font-size: 16px;
	font-style: oblique;
	color: #F93;
}
-->
</style>
</head>

<body bgcolor="000000">

<br />
<br />
<br />
<table align="center" cellpadding="0" bgcolor="#FFFFFF" width="800" border="0">
  <tr>
    <td align="left"><h1 align="right" class="heading"><a href="logout.php"><img border="0" src="admin/images/cooltext457955210.png" onMouseOver="this.src='admin/images/cooltext457955210MouseOver.png';" onMouseOut="this.src='admin/images/cooltext457955210.png';" /></a></h1>
      <h1 align="center" class="heading"><img src="admin/images/cooltext457954659.png" width="830" height="51" alt="Welcome to Site" /></h1>
      <p align="center">&nbsp;</p>
      <p align="center"><a href="about.php"><img src="admin/images/cooltext457954859.png" border="0" align="middle" onMouseOver="this.src='admin/images/cooltext457954859MouseOver.png';" onMouseOut="this.src='admin/images/cooltext457954859.png';" /></a><a href="admin/index.php"><img src="admin/images/cooltext457954941.png" border="0" align="top" onMouseOver="this.src='admin/images/cooltext457954941MouseOver.png';" onMouseOut="this.src='admin/images/cooltext457954941.png';" /></a></p>
      <p align="center"><?php 
	 	 $link=mysql_connect("localhost","root","") or die("Cannot Connect to the database!");
	
	 mysql_select_db("department",$link) or die ("Cannot select the database!");
	 $query="SELECT * FROM students";
		
		  $resource=mysql_query($query,$link);
		  echo "
		<table align=\"center\" border=\"0\" width=\"70%\">
		<tr>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;LIST OF STUDENTS 
		<tr><td></td></tr>
		<tr><td></td></tr>
		<td><b>Name</b></td> <td><b>I.D. No.</b></td><td><b>School Year</b></td><td><b>Deparment</b></td></tr> ";
while($result=mysql_fetch_array($resource))
	{ 
	echo "<tr><td>".$result[1]."</td><td>".$result[2]."</td><td>".$result[3]."</td><td>".$result[4]."</td></tr>";
	} echo "</table>";
	 ?>
      </p>
      <p align="center">&nbsp;</p>
      <p align="center">&nbsp;</p>
      <p align="center">&nbsp;</p>
      <center>
        <a href="about.php"><img src="" alt="4 D's" width="395" height="100" border="0" align="bottom" /></a>
      </center>
    <p align="left">&nbsp;</p></td>
  </tr>
  <tr>
    <td>&nbsp;</td>
  </tr>
</table>
<h1 align="center" class="heading">&nbsp;</h1>
</body>
</html>
