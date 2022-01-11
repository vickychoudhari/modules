	<!DOCTYPE html>
<html>
<head>
<style type="text/css">
.button{
    margin : 10px;
    padding: 10px;
    display: flex;
    flex-direction: column;
    justify-content: center;

}
.num1{
	margin : 10px;
	padding: 10px;
	display: flex;
	flex-direction: column;
	justify-content: center;

}
.num2{
	margin : 10px;
	padding: 10px;
	display: flex;
	flex-direction: column;
	justify-content: center;
}
.operator{
	margin : 5px;
	padding: 5px;
	display: flex;
	flex-direction: column;
	justify-content: center;
}
</style>
	<title>calculator</title>
</head>
<body>
	<form method="POST">
		<div class="num1"><strong>NUMBER 1</strong><input type="text" name="num1" placeholder="number1"></div>
		<br>
		<div class="num2"><strong>NUMBER 2</strong><input type="text" name="num2" placeholder="number2"></div>
		<br>
		<div class="operator">
		<select name="operator">
			<option><strong>none</strong></option>
			<option><strong>add</strong></option>
			<option><strong>substraction</strong></option>
			<option><strong>Multiplication</strong></option>
			<option><strong>divide</strong></option>
			<option><strong>module</strong></option>
		</select>
	</div>
		<br><br><br>
		<div class="button">
		<button name="submit" type="submit"><strong>calculate</strong></button>
	</div>
	</form>
		<br>
		<p align="center"><strong>Answer is :</strong></p>
		<center><strong>
		<?php
		
		if(isset($_POST['submit'])){
			$result1=$_POST['num1'];
			$result2=$_POST['num2'];
			$operator=$_POST['operator'];
			switch ($operator) {
				case "none":
					echo "you need to select method";
					break;
				case "add":
					echo $result1 + $result2;
					break;
				case "substraction":
					echo $result1 - $result2;
					break;
				case "Multiplication":
					echo $result1 * $result2;
					break;
				case "divide":
					echo  $result1 / $result2;
					break;
				case "module":
					echo $result1 % $result2;
					break;
				
			}
		}
?>
</strong>
</center>
</body>
</html>
<?php
$i = 016;
echo $i / 2;
$str = "PHP";

$$str = " Programming"; //declaring variable variables
echo $$str;

echo "$str {$$str}"; //It will print "PHP programming"

// echo "$PHP"; 
?>

