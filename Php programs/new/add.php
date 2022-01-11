<!DOCTYPE html>
<html>
<head>
	<title>ADD DELETE AND EDIT FORM IN PHP AND MYSQL</title>
	<link href="https://stackpath.bootstrapcdn.com/bootstrap/4.2.1/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-GJzZqFGwb1QTTN6wy59ffF1BuGJpLSa9DkKMp0DgiMDm4iYMj70gZWKYbI706tWS" crossorigin="anonymous">
</head>
<script type="text/javascript">
  

  function validateForm() {
        //intilize field variable
            var name = document.forms["Myform"]["name"].value;
            var email = document.forms["Myform"]["email"].value;
            var countries = document.forms["Myform"]["country"].value;
            var contact  =document.forms["Myform"]["contact"].value;

            //intilize the error message
            
            var error_name = document.getElementById('err_name');
            var error_email = document.getElementById('err_email');
            var error_countries =document.getElementById('err_countries');
            var error_contact =document.getElementById('err_contact');

            //intilize the validation

            var contact_validate=/^\+?([0-9]{2})\)?[-. ]?([0-9]{4})[-. ]?([0-9]{4})$/;
            var email_validate=/^([a-zA-Z0-9_\-\.]+)@([a-zA-Z0-9_\-\.]+)\.([a-zA-Z]{2,5})$/;
            var name_validation=/^[a-zA-Z][a-zA-Z\\s]+$/;

            //intilize the error message

            
            var empty=" ALL FIELD ARE REQUIRED";
            var email_msg ="Email field is valid";
            var name_msg ="Name is not valid";
            var contact_msg ="Contact no at least must character ";
            var countries_msg ="Countries is Required field";

             //if all fields are empty
            if(!name && !email && !countries && !contact) {
                error_name.innerHTML = error_email.innerHTML = error_countries.innerHTML = error_contact.innerHTML = empty;
                return false;
            }
            else if(!name || name ==' '){
                error_name.innerHTML = name_msg;
                return false;
            }
            else if(!email.match(email_validate) || email ==' '){
                error_email.innerHTML = email_msg;
                return false;
            }
            else if(!countries || countries == ' ' ){
                error_countries.innerHTML = countries_msg;
                return false;
            }
            else if(!contact.match(contact_validate) || contact == ' '){
                error_contact.innerHTML = contact_msg;
                return false;
            }
             else {
                 return true;
            }
        }
        function myfunction() {
                document.getElementById("err_name").innerHTML = document.getElementById("err_email").innerHTML = document.getElementById("err_countries").innerHTML = document.getElementById("err_contact").innerHTML ="  ";
            }



</script>

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
}

</style>
<body>
	
<br>
<br>
<div class="container">
  <div class="col-lg-8 m-auto">
	<form action="register.php" method="POST" name="Myform"   onsubmit="return validateForm()" style="display:flex; flex-direction:column;" >
		
    <div class="form-group">
<label for="name" style=" margin-bottom:10px; display:flex; color:blue; ">NAME</label>
<input type="text" name="name" class="form-control" oninput="myfunction()"></br>
<small id="err_name"></small>
</div>

<div class="form-group">
<label for="email" style=" margin-bottom:10px; display:flex; color:green;">EMAIL</label>
<input type="email" name="email" class="form-control" oninput="myfunction()"></br>
<small id="err_email"></small>
</div>

<div class="form-group">
<label for="countries" style="margin-bottom:10px; display:flex; color:purple;">COUNTRIES</label>
<input type="text" name="country" class="form-control" oninput="myfunction()"></br>
<small id="err_countries"></small><br>

<div class="form-group"><br>
<label for="contact" style="margin-bottom:10px; display:flex; color:green;">CONTACT</label><br>
<input type="text" name="contact" class="form-control" oninput="myfunction()"></br>
<small id="err_contact"></small>
<div class="form-group">
</div>
<input class="btn-btn-primary" type="submit" value="add" name="action">																								

</form>
</div>
</div>
            

<table width='80%' border=0>
	<tr bgcolor='#CCCCCC'>
		<th>ID</th>
		<th>NAME</th>
		<th>EMAIL</th>
		<th>COUNTRY</th>
		<th>CONTACT</th>
		<th colspan="2">ACTION</th>
	</tr>
	
 
  <?php


//   error_reporting(E_ALL);
// ini_set('display_errors', 1);


  // config with database 

  $servername="localhost";
  $username="root";
  $dbpass="root";
  $dbname="add_edit_del";

// create connection

  $conn = new mysqli($servername,$username,$dbpass,$dbname);
  //check connection

  if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);


}
// use select query in table 
 
 $sql = "SELECT id, name, email, country, contact FROM users";
   $result = $conn->query($sql);
   $conn->close(); 

   // use associative array and for  loop
      while( $row= $result->fetch_assoc()) { ?>
    

     

     	
     	
     
   
        <tr>
        <td> <?php echo $row['id']?> </td>
        <td> <?php echo $row['name']?> </td>
        <td> <?php echo $row['email']?> </td>
        <td> <?php echo $row['country']?> </td>
        <td> <?php echo $row['contact']?> </td>
        <td><a href="register.php?action=update&id=<?php echo $row['id'];?>"onclick="return confirm('Are you sure to update')">update</a>&nbsp;&nbsp;
        	<a href="register.php?action=del&id=<?php echo $row['id'];?>  "onclick="return confirm('Are you sure to delete this field......')">delete</a></td>

<?php    }
      ?>


</body>
</html>