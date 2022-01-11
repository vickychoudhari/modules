<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>HOME</title>
<style type="text/css">

</style>
</head>

<body  background="1.gif" text="white">

<br />
<br />
<br />

<table align="center" cellpadding="0" background="mdgraphs-ocean-breeze.jpg" width="800" border="3">
  <tr>
   <td align="left"><h1 align="right" class="heading"><a href="admin/index.php"><img width="95" height="50" border="0" src="cooltext457954941MouseOver.png" onmouseover="this.src='cooltext457954941.png';" onmouseout="this.src='cooltext457954941MouseOver.png';" /></a>
   <a href="about.php"><img src="cooltext457r.PNG" onmouseover="this.src='cooltext457954859.png';" onmouseout="this.src='cooltext457r.PNG';" width="95" height="50" border="0" align="bottom" /></a>
   <a href="logout.php"><img src="cooltext4579.PNG" onmouseover="this.src='cooltext457955210.png';" onmouseout="this.src='cooltext4579.PNG';"width="95" height="50" border="0" align="bottom" /></a></h1>
<!--    <td align="left"><h1 align="right" class="heading"><a href="admin/index.php"><img src="coolt.png" width="95" height="50" border="0" align="bottom" /></a><a href="about.php"><img src="cooltext457r.PNG" width="95" height="50" border="0" align="bottom" /></a><a href="logout.php"><img src="cooltext4579.PNG" width="95" height="50" border="0" align="bottom" /></a></h1>-->
<!-- <img border=\"0\" src=\"images/cooltext457952800.png\" onmouseover=\"this.src='images/cooltext457952800MouseOver.png';\"  onmouseout=\"this.src='images/cooltext457952800.png';\" /></a>-->
   <h1 align="center" class="heading">Information Area </h1>
      <p align="center">
	<form name="form1" method="post" action="search2.php">
	</form>
      </p>
	    				
     <!-- <p align="center"><a href="about.php">About</a><center><a href="admin/index.php">Control Panel</a></p></center>-->
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
       <!-- <a href="about.php"><img src="cooltext458256871.PNG" width="395" height="100" border="0" align="bottom" /></a>-->
      </center>
   <p align="left">&nbsp;</p></td>
  </tr>
 <!-- <tr>
    <td>&nbsp;</td>
  </tr>-->
</table>
<h1 align="center" class="heading">&nbsp;</h1>

</body>
</html>
