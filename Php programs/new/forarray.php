<?php
$var3=array("vishal",'mukesh','sahil','sumit');
$var4=count($var3);
for ($i=0; $i < $var4; $i++) { 
	if ($var3[$i]== "mukesh") {
		echo "name:" . $var3[$i] ."<br>" .  "INDEX" . $i . "<br>" ;
	}
}
?>