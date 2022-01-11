
<!DOCTYPE html>
<html>
<head>
	<title>myform</title>
	<link href="https://stackpath.bootstrapcdn.com/bootstrap/4.2.1/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-GJzZqFGwb1QTTN6wy59ffF1BuGJpLSa9DkKMp0DgiMDm4iYMj70gZWKYbI706tWS" crossorigin="anonymous">

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
</head>
<body>
	
<br>
<br>
<div class="container">
  <div class="col-lg-8 m-auto">
	<form action="myform2.php" method="POST" name="Myform"   onsubmit="return validateForm()" style="display:flex; flex-direction:column;" >
		
    <div class="form-group">
<label for="name" style=" margin-bottom:10px; display:flex; color:blue;">NAME</label>
<input type="text" name="fname" class="form-control" oninput="myfunction()"></br>
<small id="err_fname"></small>
</div>
 <div class="form-group">
<label for="lname" style=" margin-bottom:10px; display:flex; color:blue;">LASTNAME</label>
<input type="text" name="lname" class="form-control" oninput="myfunction()"></br>
<small id="err_lname"></small>
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
            var fname = document.forms["Myform"]["fname"].value;
            var lname = document.forms["Myform"]["lname"].value;
            

            //intilize the error message
            
            var error_fname = document.getElementById('err_fname');
            var error_lname = document.getElementById('err_lname');
            
            //intilize the error message

            
            var empty ="ALL FIELD ARE REQUIRED";
            var name_msg= "name is not valid";
            var lname_msg="last name is not valid";
            
             //if all fields are empty
            if(!fname && !lname) {
                error_fname.innerHTML = error_lname.innerHTML = empty;
                return false;
                }
         
          else {
          	return true;
          }
        }
   function myfunction() {
                document.getElementById("err_fname").innerHTML = document.getElementById("err_lname").innerHTML =" ";
            }
	</script>
</body>
</html>
