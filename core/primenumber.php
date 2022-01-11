<?php
// define(a, 7);
// for ($i=0; $i <= 20; $i++) { 
// 	echo $i+a;
// 	echo "<br>";
// }
// $a= 11;
// for ($i=0; $i <= 20 ; $i++) { 
// 	echo $a--;
// 	echo "<br>";
// }

class greeting {
  public static function welcome() {
    echo "Hello World!";
  }

  public function __construct() {
    self::welcome();
  }
}

$var = new greeting();
$var->welcome();

namespace Html;
class Table {
  public $title = "";
  public $numRows = 0;
  public function message() {
    echo "<p>Table '{$this->title}' has {$this->numRows} rows.</p>";
  }
}
$table = new Table();
$table->title = "My table";
$table->message();
$table->numRows = 5;

// namespace Html;
// class Table {
//   public $title = "";
//   public $numRows = 0;

//   public function message() {
//     echo "<p>Table '{$this->title}' has {$this->numRows} rows.</p>";
//   }
// }

// class Row {
//   public $numCells = 0;
//   public function message() {
//     echo "<p>The row has {$this->numCells} cells.</p>";
//   }
// }
?>