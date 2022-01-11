
<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
?>
<?php

// creating class name fruit


class Fruit {
	// properties of a class
   public $name;
   public $color;

   // Methods of a class set name and get name in a class

   function set_name($name){
   	$this->name =$name;
   }

   function get_name(){
   	return $this->name;
   }
   function set_color($color){
   	$this->color =$color;
   }
   function get_color(){
   	return $this->color;
   }
}

   //create a object in a class that is apple and banana
   $apple = new Fruit();
   $banana = new Fruit();
   $grapes = new Fruit();

   //set value of a apple
   $apple->set_name('apple');
   echo $apple->get_name();

   echo "<br>";

   //set value of a banana
   $banana->set_name('banana');
   echo $banana->get_name();

   //set value of a orange

   echo "<br>";
   $grapes->set_name('grapes');
   echo $grapes->get_name();
   echo "<br>";

  //create a object of a class fruit color ..

  $red = new Fruit();
  $yellow = new Fruit();
  $green = new Fruit();
  
  //set color of apple

  $red->set_color('Apple have a red color');
  echo $red->get_color();
  echo "<br>";
  
  //set  color of banana
  $yellow->set_color('banana have yellow color');
  echo $yellow->get_color();
  echo "<br>";

 //set color of a grapes
  $green->set_color('grapes have green color');
  echo $green->get_color();
  echo "<br>";




?>
