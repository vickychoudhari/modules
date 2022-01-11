<!DOCTYPE html>
<html>
<head>
	<title>INSERT DATA INTO AJAX PHP AND MYSQL</title>
	<!-- <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.2.1/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-GJzZqFGwb1QTTN6wy59ffF1BuGJpLSa9DkKMp0DgiMDm4iYMj70gZWKYbI706tWS" crossorigin="anonymous">
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script> -->
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css">
	
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js"></script>
		<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.1.3/js/bootstrap.min.js"></script>
		<script type="text/javascript">
	$(document).ready(function(){
		var form = $('#myform');
		$('#submit').click(function(){
			$.ajax({
				url:form.attr("action"),
				type:'post',
				data: $("#myform input").serialize(),
				 success: function(data){
				 	console.log(data);
				 }
			});

		});
	});
	$(document).ready(function(){
$('#displaydata').click(function() {
    
     $.ajax({
    	url: 'displayajax.php',
    	type: 'post',
    	
    	success:function(responsedata){
    		$('#response').html(responsedata);
    	}
    });
});
});



	
</script> 

</head>
<body>
	<div class="container">
		<div class="col-lg-8 m-auto">
			<BR>
			<br>
		<h1 class="text-center">INSERT DATA INTO AJAX PHP AND MYSQL </h1>
		<form id="myform" action="inserphp.php" method="post">
			<div class="form-group">
				<label> USERNAME:</label>
				<input type="text" name="username" class="form-control">
			</div>
			<div class="form-group">
				<label> PASSWORD:</label>
				<input type="text" name="password" class="form-control">
				</div> 
				<input type="submit" name="submit" id="submit" value="submit" class="btn-btn-success">

	

</form>
</div></BR></br>
<div>
	<h1 class="text-center bg-dark text-white"> Display Data using ajax</h1>
	<br>
	<button id="displaydata" class="btn-btn-danger">DISPLAY</button>
	<table class="table-table-striped table-bordered text-center">
		<thead>
			<th>id</th>
			<th> Name</th>
			<th> Password</th>
		</thead>
		<tbody id="response">
			
		</tbody>
	</table>
</div>
</div>

</body>
</html>