<?php
interface Animal {
  public function makeSound();
}

class Cat implements Animal {
  public function makeSound() {
    echo "Meow";
  }
}
class Dog implements Animal {
  public function makeSound() {
    echo "bowbow";
  }
}
class Cow implements Animal {
  public function makeSound() {
    echo "maaaa";
  }
}

   $cat = new Cat();
// $cat->makeSound();
// echo "<br>";
   $dog = new Dog();
// $dog->makeSound();
// echo "<br>";
   $cow = new Cow();
// $cow->makeSound();
$animals =array($cat,$dog,$cow,$cat,);
// echo "<pre>";
// print_r($animals);
// die();
foreach ($animals as $animal) {
  echo "<pre>";
  print_r($animals);
  die();
	 $animal->makeSound();
	

}

?>