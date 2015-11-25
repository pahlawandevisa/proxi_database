<?php
require_once 'database.php';


// order. USER_user_id is the buyer_id
// transaction, User_user_id is the owner_id
//NEED TO SEND CONFIRMATION EMAIL

class Transaction{
	
	function index($method,$data){
		switch ($method){
			case "checkout":
				$this->checkout($data);
				//DATA: item_id, user_id
				break;
			case "cancelOrder":
				$this->cancelOrder($data);
				//DATA: item_id
				break;
			case "finish":
				$this->finish($data);
				//DATA:item_id
				break;
			default:
				echo "Cannot Find Appropriate Method";
		}
	}
	
	function checkout($data){
		//Check Item Current Price
		//Item.visibility= false
		//Create Order
		//item_id
		
		$db= IDB::connection();
		$sql_3 = "select item_current_price from Item where item_id = ?";
		$query_3 = $db->prepare($sql_3);
		$query_3->execute(array($data['item_id']));
		$result_3 = $query_3->fetch(PDO::FETCH_ASSOC);
		$order_price = $result_3["item_current_price"];
		$sql_dropLike = "delete from proximar_proxi.Like where Item_item_id = ?";
		$query_dropLike = $db->prepare($sql_dropLike);
		$query_dropLike->execute(array($data['item_id']));
		
		$sql_4 = "select user_id from User where user_email = ?";
		$query_4 = $db->prepare($sql_4);
		$query_4->execute(array($data['user_email']));
		$result_4 = $query_4->fetch(PDO::FETCH_ASSOC);
		$user_id = $result_4["user_id"];
		
		$sql_1 = "update Item set item_visibility = 0 where item_id = ? and item_visibility = 1";
		$query_1 = $db->prepare($sql_1);
		$query_1->execute(array($data['item_id']));
		
		$sql_2 = "Insert into proximar_proxi.Order (order_price, order_date, Item_item_id, User_user_id) values (?,NOW(),?,?) ";
		$query_2 = $db->prepare($sql_2);
		if($result = $query_2->execute(array($order_price,$data['item_id'],$user_id))){
			echo "success";
		}else{
			echo "failed";
		}	
		
		$messageToSeller = $data['message'];
		//Send Confirmation Email
	}//check
	
	
	function cancelOrder($data){
		//Item.visibility = true
		//Drop Order
		$db = IDB::connection();
		$sql_1= "update Item set item_visibility = 1 where item_id = ?";
		$query_1 = $db->prepare($sql_1);
		$query_1 ->execute(array($data['item_id']));
		$sql_2= "Delete from proximar_proxi.Order where Item_item_id = ?";
		$query_2 = $db->prepare($sql_2);
		if ($result = $query_2->execute(array($data['item_id']))){
			echo "success";
		}else{
			echo "failed";
		}
	}//check
	
	
	
	function finish($data){
		$db = IDB::connection();
		$sql_2 = "select User_user_id, order_price, item_owner_id, item_title from proximar_proxi.Order 
				join proximar_proxi.Item on Item_item_id = Item.item_id where Item_item_id = ?";
		$query_2 = $db->prepare($sql_2);
		$query_2->execute(array($data['item_id']));
		$row_2 = $query_2->fetch(PDO::FETCH_ASSOC);
		$buyer_id = $row_2['User_user_id'];
		$owner_id = $row_2['item_owner_id'];
		$item_title = $row_2['item_title'];
		$item_price = $row_2['order_price'];
		//Create Transaction
		$sql_3 = "insert into proximar_proxi.Transaction (User_user_id, item_buyer_id, item_title, item_sold_date ) 
				values ( ?, ?, ?, NOW() )";
		$query_3 = $db->prepare($sql_3);
		if($query_3->execute(array($owner_id, $buyer_id, $item_title))){
		
		}else{
			echo "fail to Create Transaction";
		}
		//Drop Order, Item
		$sql_4= "delete from proximar_proxi.Order where Item_item_id = ?";
		$query_4 = $db->prepare($sql_4);
		$query_4 ->execute(array($data['item_id']));
		
		$sql_6= "delete from proximar_proxi.Item_Category where item_id = ?";
		$query_6 = $db->prepare($sql_6);
		$query_6 ->execute(array($data['item_id']));
		$sql_5= "delete from proximar_proxi.Item where item_id = ?";
		$query_5 = $db->prepare($sql_5);
		if ($result = $query_5->execute(array($data['item_id']))){
			echo "success";
		}else{
			echo "failed to drop";
		}

		//Send Confirmation Email
	}//check
	
}

?>