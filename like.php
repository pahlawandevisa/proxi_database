<?php
require 'database.php';
//malfunctional


class Like{
	function index($method,$data){
		switch ($method){
			case "createLike":
				$this->createLike($data);
				//DATA:item_id, user_id
				break;
			case "dropLike":
				$this->dropLike($data);
				//DATA: item_id
				break;
			default:
					echo "Cannot Find Appropriate Method";
		}
	}//check
	
	
	 function createLike($data){
	 	$db= IDB::connection();
	 	$sql = "insert into proximar_proxi.Like (User_user_id, Item_item_id) values
	 		(8,14)";
	 	$query = $db->prepare($sql);
	 	if ($query->execute(array($data["user_id"],$data["item_id"]))){
	 		echo "success";
	 	}else{
	 		echo "failure";
	 	}
	 	
	 }//check
	
	 function dropLike($data){
		$db = IDB::connection();
		$sql = "delete from proximar_proxi.Like where Item_item_id = ?";
		$query =$db->prepare($sql);
		if($result = $query->execute(array($data["item_id"]))){
			echo "success";
		}else{
			echo "failure";
		}
		
	}//check
	
}

?>