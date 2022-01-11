<?php

//create Class

 class Fruit{
 
 //set properties
public $name;
public $color;

//set methods of construct and destruct

function __construct($name,$color){
    $this->name=$name;
    $this->color=$color;

}
function __destruct(){
    echo "the fruit name is {$this->name} and the fruit color is {$this->color}";
    // return $this->name;
}

}

//create a object of name and color 

$apple = new Fruit('apple', 'red');
// $apple->__destruct();


