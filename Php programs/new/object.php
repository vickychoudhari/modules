<!DOCTYPE html>
<html>
<head>
	<title>Demo</title>
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
	<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
</head>
<body>
	<div class="container">

		<div id="loaddata"> <h2> THIS IS GOING TO CHANGE........</h2></div>
		<button class="btn-btn-success" id="btnclick">Click On To Show Data</button>
	</div>
	<script type="text/javascript">
		$(document).ready(function(){
         $('#btnclick').click(function(){
          $('#loaddata').load('register.php');
         })
		});
	</script>

</body>
</html
