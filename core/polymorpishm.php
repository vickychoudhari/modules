<?php  
class Shap  
{  
function draw(){}  
} 

class Circle extends Shap  {  
function draw() {  
print "Circle has been drawn.</br>";  
}  
}  
class Triangle extends Shap  
{  
function draw()  
{  
print "Triangle has been drawn.</br>";  
}  
}  
class Ellipse extends Shap  
{  
function draw()  
{  
print "Ellipse has been drawn.";  
}  
}  
    
  
$Val[0]= new Circle();  
$Val[1]= new Triangle();  
$Val[2]= new Ellipse();  
  
for($i=0;$i<3;$i++)  
{  
echo ""$Val[$i]->draw();  
}  
?>  
