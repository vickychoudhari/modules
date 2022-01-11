
<?php
class MainClass {
public function ShowTitle($parameter1) {
echo "Best Interview Question";
}
public function ShowTitle($parameter1, $parameter2) {
echo "BestInterviewQuestion.com";
}
}
$object = new MainClass;
$object->ShowTitle('Hello');
?>