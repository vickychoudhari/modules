<?php
class Computersystem{
	public $on;
	public $off;

	function on($on,$off){
		echo"the system is " . $on;
		echo " the system is " . $off;
	}
	function off($off){
		echo " the system is " . $off;
	}
}
$obj = new Computersystem();
$obj->on("press onButton","press offButton");
$obj->off("press off button");
?>