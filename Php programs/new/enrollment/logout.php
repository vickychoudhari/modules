<html>
<head>

<title>
</title>
</head>
<body   background="1.gif" text="white" >
<br />
<br />
<br />
<br />
<br />
<br /><center>
<table border=0 CellPadding=5  border="black" background="AquaLoop Wallpaper Bk.jpg" WIDTH=10% text="blue" ><center>
<tr><td>
<table  align="center" border=0 CellPadding=5  border="black" background="3438593196_e91fcc6316.jpg" WIDTH=10% text="blue" >
<tr  font face ="Britannic Bold" border="0" color="Red" size="500" ALIGN="CENTER">  <TH COLSPAN=4> <fieldset> <p font size=0>Log-Out Process</fieldset></TH>
<th bgcolor="" colspan=2 align="right"><img src="indicator.gif"  width="150" height="150" border="0"></th></TR>

 
 <tr>
   
	    	<th>			










<center>





<?php

// expire cookie
setcookie ("loggedin", "", time() - 3600);

echo "You are now logged out.<br>";
echo "<a href=\"index.html\">Log in</a>";

?>
</th>
</tr>
</td>
</tr>
</body>
</html>