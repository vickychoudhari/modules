<html>
<head>
	<link href="https://stackpath.bootstrapcdn.com/bootstrap/4.2.1/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-GJzZqFGwb1QTTN6wy59ffF1BuGJpLSa9DkKMp0DgiMDm4iYMj70gZWKYbI706tWS" crossorigin="anonymous">
<body>
	<div class="container">
  <div class="col-lg-20 m-auto">
	<?php
	session_start();
	
	if (isset($_POST['submit'])) {
		$_SESSION['fname'] = $_POST['fname'];
		$_SESSION['lname'] = $_POST['lname'];
	}
	echo "MY FIRST NAME IS :" . $_SESSION['fname']."<br/>" ;
	echo "MY LAST NAME IS :" . $_SESSION['lname'];
	


	 ?>

	 <br>
	 <br>
	 
	 	<title>secondform</title>
	 	

	 	<style type="text/css">
	 		th,td{
		border:1px solid black;
		text-align: center;

	}
	table{
		margin :0 auto;
		margin-top:20px;
		width:100%;
	}
  small {
       width:100%;
       display: inline-block;
       color:red;
}	 	</style>
	 </head>
	 <body>
	 	<br>
<div class="container">
  <div class="col-lg-8 m-auto">
	 	<form action="myform3.php" method="POST" name="Myform"   onsubmit="return validateForm()" style="display:flex; flex-direction:column;" >
		
	 	<div class="form-group">
<label for="email" style=" margin-bottom:10px; display:flex; color:blue;">EMAIL ADDRESS</label>
<input type="email" name="email" class="form-control" oninput="myfunction()"></br>
<small id="err_email"></small><br>
</div>
 <div class="form-group">
<label for="phone" style=" margin-bottom:10px; display:flex; color:blue;">CONTACT</label>
<input type="number" name="phone" class="form-control" oninput="myfunction()"></br>
<small id="err_phone"></small>
</div>
<div class="form-group">
	</div>
<input type="submit" name="submit" value="continue" name="action">
</form>
</div>
</div>

	   <script type="text/javascript">
		function validateForm() {
        //intilize fie ld variable
            var email = document.forms["Myform"]["email"].value;
            var phone = document.forms["Myform"]["phone"].value;
            

            //intilize the error message
            
            var error_email = document.getElementById('err_email');
            var error_phone = document.getElementById('err_phone');
            
            //intilize the error message

            
            var empty ="ALL FIELD ARE REQUIRED";
            var email_msg= "EMAIL FORMAT IS NOT CORRECT";
            var phone_msg="CONTACT NUMBER IS NOT VALID";
            
            // validation
              var contact_validate=/^\+?([0-9]{2})\)?[-. ]?([0-9]{4})[-. ]?([0-9]{4})$/;
            var email_validate=/^([a-zA-Z0-9_\-\.]+)@([a-zA-Z0-9_\-\.]+)\.([a-zA-Z]{2,5})$/;

            
             //if all fields are empty
            if(!email && !phone) {
                error_email.innerHTML = error_phone.innerHTML = empty;
                return false;
            }
            else if(!email.match(email_validate) || email ==' '){
                error_email.innerHTML = email_msg;
                return false;
            }
            else if(!phone.match(contact_validate) || phone ==' '){
                error_phone.innerHTML = phone_msg;
                return false;
            }
            
         
          else {
          	return true;
          }
        }
   function myfunction() {
                document.getElementById("err_email").innerHTML = document.getElementById("err_phone").innerHTML =" ";
            }
	</script>
	 
	 </body>
	 </html>

