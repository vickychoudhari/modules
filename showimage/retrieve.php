
<!DOCTYPE html>
<html>
<head>
	<style type="text/css">
		img{
			width:200px;
		}
	</style>
	<title>show images</title>
</head>
<body>

</body>
</html><?php 
// Include the database configuration file  
require_once 'config.php'; 
 
// Get image data from database 
$result = $db->query("SELECT image FROM images ORDER BY uploaded DESC"); 
?>

<?php if($result->num_rows > 0){ ?> 
    <div class="gallery"> 
        <?php while($row = $result->fetch_assoc()){ ?> 
            <img src="data:image/jpg;charset=utf8;base64,<?php echo base64_encode($row['image']); ?>" /> 
        <?php } ?> 
    </div> 
<?php }else{ ?> 
    <p class="status error">Image(s) not found...</p> 
<?php }
echo "<br>";
echo "<a href='home.php'><button> CLICK WAY TO HOME PAGE</button>"; 
?>
</body>
</html>