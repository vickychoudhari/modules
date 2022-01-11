<?php






$var =array('vishal','balu','rahul');
echo"<pre>";
print_r($var);
// die();

function setHeight($minheight = 50) {
    echo "The height is : $minheight <br>";
}

setHeight(350);
setHeight(); // will use the default value of 50
setHeight();
setHeight();



function myfunction() {
	echo" vishal";

}
myfunction();
echo "<br>";
myfunction();
echo "<br>";
myfunction();
echo "<br>";
myfunction();
echo "<br>";



function myfunction2($firstname, $lastname) {
	echo "$firstname and $lastname.<br>";
	// echo "$anothername and $anotheritem.<br>";


}
myfunction2("vishal","choudhary");
myfunction2("ram", "sham");
?>