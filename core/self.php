<?php 

// class GFG { 
// 	private static $static_member ="hello"; 

// 	function __construct() { 
// 		echo self::$static_member; 
// 		// Accessing static variable 
// 	} 
// } 

// new GFG(); 


  
class GFG { 
    function prnt() { 
        echo 'Parent Class'; 
    } 
  
    function bar() { 
        self::prnt(); 
    } 
} 
  
class Child extends GFG { 
    function prnt() { 
        echo 'Child Class'; 
    } 
} 
  
$parent = new Child(); 
$parent->bar(); 
?>


<?php
echo "<br>";
    class Job {
        // opening for position
        public $name;
        // description for the job;
        public $desc;
        // company name - as the company name stays the same
        public static $company;
        
        // public function to get job name
        public function getName() {
            return $this->name;
        }
        
        // public function to get job description
        public function getDesc() {
            return $this->desc;
        }
        
        // static function to get the company name
        public static function getCompany() {
            return self::$company;
        }
        
        // non-static function to get the company name
        public function getCompany_nonStatic() {
            return self::getCompany();
        }
    }
    
    $objJob = new Job();
    // setting values to non-static variables
    $objJob->name = "Data Scientist";
    $objJob->desc = "You must know Data Science";
    
    /* 
        setting value for static variable.
        done using the class name
    */
    Job::$company = "Studytonight";
    
    // calling the methods
    echo "Job Name: " .$objJob->getName()."<br/>";
    echo "Job Description: " .$objJob->getDesc()."<br/>";
    echo "Company Name: " .Job::getCompany_nonStatic();

?>