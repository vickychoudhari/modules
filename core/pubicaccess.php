
<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
?>
<?php
class Fruit {
  public $name;
  public $color;
  public $weight;

  function set_name($name) { // a public function (default)
    $this->name = $name;
  }
  function get_name(){
    return $this->name;
  }
  function set_color($color) { // a protected function
    $this->color = $color;
  }
  function get_color(){
    return $this->color;
  }
  
 function set_weight($weight) { 
    $this->weight = $weight;
  }
  function get_weight(){
    return $this->weight;
  }
}

$mango = new Fruit();
$mango->set_name('Mango'); 
echo $mango->get_name();// OK
$mango->set_color('Yellow'); // ERROR
echo $mango->get_color();
$mango->set_weight('300');
echo $mango->get_weight(); // ERROR
?>
 

