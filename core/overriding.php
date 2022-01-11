<?php
   class Base {
      function display() {
         echo "<br>" . "Base class function declared final!";
      }
      function demo() {
         echo "\nBase class function!";
      }
   }
   class Derived extends Base {
      function demo() {
         echo "\nDerived class function!";
      }
   }
   $ob = new Base;
   $ob->demo();
   $ob->display();
   $ob2 = new Derived;
   $ob2->demo();
   $ob2->display();
   echo"<br>";
?>
<?php 
// PHP program to implement 
// function overriding 

// This is parent class 
class P { 
   
   // Function geeks of parent class 
   function geeks() { 
      echo "Parent"; 
   } 
} 

// This is child class 
class C extends P { 
   
   // Overriding geeks method 
   function geeks() { 
      echo "\nChild"; 
   } 
} 

// Reference type of parent 
$p = new P; 

// Reference type of child 
$c= new C; 

// print Parent 
$p->geeks(); 

// Print child 
$c->geeks(); 
?> 
