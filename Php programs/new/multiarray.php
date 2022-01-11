<?php
// error_reporting(E_ALL);
// ini_set('display_errors', 1);

class tv{
  
public $model='xyz';
public $volume=1;
  


public function volumeUp($one)
{
  //$this->volume=$one;
  return $this->volume;
}
public function volumeDown()
{
  $this->volume--;
}
}

$tv_one=new TV;
echo $tv_one->volume;
echo '<pre>';
print_r($tv_one->volumeUp(1));
?>


Encapsulation is just wrapping some data in an object. The term "encapsulation" is often used interchangeably with "information hiding". Wikipedia has a pretty through article.

Here's an example from the first link in a Google search for 'php encapsulation':

<?php

class App {
     private static $_user;

     public function User() {
          if( $this->_user == null ) {
               $this->_user = new User();
          }
          return $this->_user;
     }

}

class User {
     private $_name;

     public function __construct() {
          $this->_name = "Joseph Crawford Jr.";
     }

     public function GetName() {
          return $this->_name;
     }
}

$app = new App();

echo $app->User()->GetName();

?>