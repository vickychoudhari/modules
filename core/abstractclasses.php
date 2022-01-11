<!DOCTYPE html>
<html>
<body>

<?php
// Parent class
abstract class Car {
  public $name;
  
  public function __construct($name) {
    $this->name = $name;
  } 
}

// Child classes
class Audi extends Car {
  public function intro()  {
    return "Choose German quality! I'm an {$this->name}!"; 
  }
}

class Volvo extends Car {
  public function intro() {
    return "Proud to be Swedish! I'm a {$this->name}!"; 
  }
}

class Citroen extends Car {
  public function intro() {
    return "French extravagance! I'm a {$this->name}!"; 
  }
}

// Create objects from the child classes
$audi = new Audi("Audi");
echo $audi->intro();
echo "<br>";

$volvo = new Volvo("Volvo");
echo $volvo->intro();
echo "<br>";

$citroen = new Citroen("Citroen");
echo $citroen->intro();
?>
 
</body>
</html>
