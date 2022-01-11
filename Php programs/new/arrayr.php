	
<!-- 
// 1. Anuj works at Gai.
// 2. VISHAL works at Google. 
// 3. Mukesh works at tcs. -->

<?php
  $name = array("anuj", "vishal", "mukesh");
$company = array("Gai", "Google", "TCS");
foreach($name as $key => $value) {
    echo "". $key+1 .  "." .ucfirst($name[$key]). "&nbsp;works at &nbsp;" .$company[$key]. "." . "<br>";
}
echo '<pre>';
print_r($name);

echo '<pre>';
print_r($company);


?>
<?php
$name1=array("sahil","sunny","sumit");
$arrlength=count($name1);
for ($i=0; $i <= $arrlength ; $i++) { 
	echo $i;
	// echo'<pre>';
	// print_r($i);
	}

?>
<?php
$array = [ 'one', 'two', 'three', 'four', 'five' ];

foreach( $array as $value ){
    if( $value == 'three' ){
        echo "Number three was found!";
        break;
    }
    echo '<pre>';
    print_r($array[2]);

}

?>
<?php 
$name2=array("hima","rima","sima");
$name3=array("sham","gyan","hemraj");

echo'<pre>';
print_r($name2);
print_r($name3);
?>
<?php
$var=5+9;
echo $var .  "<br>";


$var2=array('vishal',"ram",'sahil');
$var1=count($var2);
for ($i=0; $i < $var1 ; $i++) { 
	echo $i . "<br>" ;
	// echo '<pre>';
	// print_r($var2[2]); 
	
}
echo "<br>";
?>
<?php
$var3=array("vishal",'mukesh','sahil','sumit');
$var4=count($var3);
for ($i=0; $i < $var4; $i++)  { 
	if ($var3[$i]== "mukesh" || $var3[$i]== "sumit" || $var3[$i]=="vishal" || $var3[$i]=="sahil") {
		echo "name:" . $var3[$i] ."<br>" .  "INDEX: &nbsp;" . $i . "<br>" ;
	}
}
?>

