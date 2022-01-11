            <?php
// define variables and set to empty values


$name = $email = $gender = $comment = $number = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $name = test_input($_POST["name"]);
  if(!preg_match("/[a-zA-Z'-]/",$name)) { die ("invalid first name");}
  $email = test_input($_POST["email"]);
 if (!preg_match("/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/",$email)) {die ("invalid email address");}
  $number = test_input($_POST["number"]);
  $comment = test_input($_POST["comment"]);
} 

function test_input($data) {
  $data = trim($data);
  $data = stripslashes($data);
  $data = htmlspecialchars($data);
  return $data;
}

?>
<?php
echo "<h2>Your Input:</h2>";
echo $name;
echo "<br>";
echo $email;
echo "<br>";
echo $number;
echo "<br>";
echo $comment;
echo "<br>";
echo $gender;
?>