<?php
// Modify Sequence and Datatype


require_once 'database.php';
$db = IDB::connection();

if(isset($_GET['email']) && !empty($_GET['email']) AND isset($_GET['hash']) && !empty($_GET['hash'])){
	// Verify data
	$email = mysqli_real_escape_string($_GET['email']);
	$hash = mysqli_real_escape_string($_GET['hash']);
	$sql= "update User set user_active = 1 WHERE user_email= ? AND user_active_key= ? AND user_active=0";
	$query = $db->prepare($sql);
	if (!$result= $query->execute(array($email,$hash))) die("Invalid Link!");
	 
}else{
	echo "Invalid Link!";
}
?>