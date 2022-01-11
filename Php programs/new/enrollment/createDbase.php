<?php
	include('connect.php');
	$sql="CREATE TABLE tblStudent
	(
		studID int NOT NULL AUTO_INCREMENT,
		PRIMARY KEY(studID),
		FirstName varchar(50),
		LastName varchar(50),
		Age int
	)
	";
		$i=mysql_query($sql);
		if($i)
		{
			echo"Table is created successfully!";
			}
			else
			{
			echo"Failed to create a Table";
		}		
?>