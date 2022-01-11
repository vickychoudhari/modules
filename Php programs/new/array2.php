<?php 
  
// Creating an associative array 

echo "<strong><u>Creating associative array:</u></strong>";
$name_one = array("ram"=>"sita", "anthony"=>"sahil","Ram"=>"seema", "Salim"=>"aara","Raghav"=>"eavina");
ksort($name_one);

echo '<pre>';

print_r($name_one);


$name_two=
array("sahm"=>"deep", "vikk"=>"stardam","standup"=>"geeta", "rahim"=>"baba","itnloo"=>"deepu","jeep"=>"weep"); 
echo '<pre>';

print_r($name_two);


  //foreach loop are using the associative array 


echo "<strong><u>Looping using foreach  in first array:</u></strong> <br><br><br>\n"; 
foreach ($name_one as $val => $val_value){ 
    echo "Husband is &nbsp;" . $val.  "&nbsp; Wife is "  .$val_value."&nbsp; <strong>This is a new line</strong><br>"; 
} 

echo "<strong><u>looping using foreach loop in second array:</u></strong><br><br>";
foreach ($name_two as $key =>$value2){
	echo "Husband is &nbsp;" .$key.  "&nbsp; wife is "  .$value2. "<br>";
}
?>
<a href="array1.php">back to page</a>