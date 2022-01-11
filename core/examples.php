<?php
class MCA{

	//declare variables and properties....
	 public $name;
	 public $class;
	 public $rollno;
	 public $address;

	 //declare function
	public function __construct($name,$class,$rollno,$address){
        $this->name = $name;
		$this->class = $class;
		$this->rollno = $rollno;
		$this->address = $address;

     }
		public function set_name(){
			$this->name = $name;
		}
		public function set_class(){
			$this->class = $class;
		}
		public function set_rollno(){
			$this->rollno = $rollno;
		}
		public function set_address(){
			$this->address = $address;
		}
		public function get_name(){
			return $this->name;
		}
		public function get_class(){
			return $this->class;
		}
		public function get_rollno(){
			return $this->rollno;
		}
		public function get_address(){
			return $this->address;
		}
	}

class vishal extends MCA{
	private $teacher;
	public function __construct($name,$class,$rollno,$address,$teacher){
		parent::__construct($name,$class,$rollno,$address);
		$this->teacher = $teacher;
	}
}
$vishal = new vishal("vishal",22,"mca","kangra","sachin");
// echo "<br>";
echo "<pre>";
print_r($vishal);
die();	
// $vishal = new vishal();


?>