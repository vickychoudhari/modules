<?php
$con =mysqli_connect('localhost','root','root');
mysqli_select_db($con,'crudajax'); 

$q= "select * from ajax";	
$query=mysqli_query($con,$q);
echo'<pre>';
print_r($query);
die();
if(mysqli_num_rows($query) > 0){
while( $result= mysqli_fetch_array($query) ){ ?>
		<tr>
		    <td><?php echo $result['id']?></td>
			<td><?php echo $result['username']?></td>
			<td><?php echo $result['password']?></td>
		</tr>

	


<?php
      } 
}
?>