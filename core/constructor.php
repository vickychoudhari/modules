
<?php 
class Fruit {
	
	//set properties
	public $name;
	public $color;

	//set methods

	 public function __construct($name,$color = " "){
		$this->name=$name;
		$this->color=$color;
	}
	function get_name(){
		return $this->name;
	}
	function get_color(){
		return $this->color;
	}
}
//creating objects 
$apple = new Fruit("Apple","red");
 echo $apple->get_name();
 echo "<br>";
 echo $apple->get_color();

// $red = new Fruit("red");
// echo $red->get_color();
   

?>
