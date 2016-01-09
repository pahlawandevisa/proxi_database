<?php
require_once 'database.php';

//!!!!!!!!!!MODIFY: myLikes return data   &&    mySells return data   && myOrders Return data


class Manager{
	function index($method,$data){
		switch ($method){
			case "myItems":
				$this->myItems($data);
				break;
			case "myOrders":
				$this->myOrders($data);
				break;
			case "myTransactions":
				$this->myTransactions($data);
				break;
			case "myLikes":
				$this->myLikes($data);
				break;
			case "mySells":
				$this->mySells($data);
				break;
			default:
				echo "Cannot Find Appropriate Method";
		}
	}
	
	
	
	function myItems($data){
		//DATA: number, user_email
		$offset =$data['offset'];
		$num = $data['number'];
		$email = $data['user_email'];
		
		$db= IDB::connection();
		$sql_1 = "select * from User where user_email = ?";
		$query_1 = $db->prepare($sql_1);
		$query_1->execute(array($email));
		$row_1= $query_1->fetch(PDO::FETCH_ASSOC);
		$user_id = $row_1["user_id"];
		$sql= "select * from Item where item_owner_id = ?";
		$query = $db->prepare($sql);
		$query->execute(array($user_id));
		$json_array = array();
		$rows= $query->fetchAll(PDO::FETCH_OBJ);
		foreach ($rows as $row){
			$item_array= array(
					//return data package need to be modified
					"item_id"=>$row->item_id,
					"user_id"=>$row->item_owner_id,
					"item_title"=>$row->item_title,
					"item_price"=>$row->item_current_price,
					"item_description"=>$row->item_description,
					"item_img_url"=>$row->item_img_url,
					"item_date"=>$row->item_post_date,
					"item_status"=>$row->item_status
			);			
			$item_status = $row->item_status;	
			//if($item_status === "unordered"){
				array_push($json_array, $item_array);
			//}
		}
		header('Content-type: application/json');
		
			// return all items from offset to num
		echo json_encode($json_array);
			
	}
	function myOrders($data){
		require 'preparePayment.php';
		$email = $data['user_email'];
		$db= IDB::connection();
		$sql_1 = "select * from User where user_email = ?";
		$query_1 = $db->prepare($sql_1);
		$query_1->execute(array($email));
		$row_1= $query_1->fetch(PDO::FETCH_ASSOC);
		$user_id = $row_1["user_id"];
		$sql= "select * from proximar_proxi.Order,proximar_proxi.Item where Order.User_user_id = ? and Order.Item_item_id = Item.item_id";
		$query = $db->prepare($sql);
		$query->execute(array($user_id));
		$json_array = array();
		$rows= $query->fetchAll(PDO::FETCH_OBJ);
		foreach ($rows as $row){
			$row_seller_id = $row->User_user_id;
			$escrow_id = $row->escrow_id;
			if($result= Braintree_Transaction::find($escrow_id)){
				$order_status = $result->escrowStatus;
			}else{
				$order_status = "Payment Error";
			}
			$sql_seller_id = "select * from User where user_id = ?";
			$query_seller_id = $db->prepare($sql_seller_id);				
			$query_seller_id->execute(array($row_seller_id));
			$row_seller_id = $query_seller_id->fetch(PDO::FETCH_ASSOC);
			$item_array= array(
				//return data package need to be modified
					"item_id"=>$row->Item_item_id,
					"user_id"=>$row->User_user_id,
					"user_info"=> array(
						"seller_first_name"=>$row_seller_id["user_first_name"],
						"seller_last_name"=>$row_seller_id["user_last_name"],
						"seller_email"=>$row_seller_id["user_email"],
						"seller_phone"=>$row_seller_id["user_phone"]
						),
					"order_date"=>$row->order_date,
					"order_price"=>$row->order_price,
					"order_id"=>$row->order_id,
					"order_status"=>$order_status,
					"item_img_url"=>$row->item_img_url,
					"item_title"=>$row->item_title,
					"item_description"=>$row->item_description
					);				
			array_push($json_array, $item_array);			
			
		}
		header('Content-type: application/json');
		
			// return all items from offset to num
		echo json_encode($json_array);
	}
	function myTransactions($data){

		$email = $data['user_email'];
		$db= IDB::connection();
		$sql_1 = "select * from User where user_email = ?";
		$query_1 = $db->prepare($sql_1);
		$query_1->execute(array($email));
		$row_1= $query_1->fetch(PDO::FETCH_ASSOC);
		$user_id = $row_1["user_id"];
		$sql= "select * from proximar_proxi.Transaction where item_buyer_id = '$user_id'";
		$query = $db->query($sql);
		$rows = $query->fetchAll(PDO::FETCH_OBJ);
		$json_array = array();
		foreach ($rows as $row){
			$item_array= array(
					//return data package need to be modified
					"owner_id"=>$row->User_user_id,
						"sold_date"=>$row->item_sold_date,
						"item_title"=>$row->item_title,
						"item_price"=>$row->item_price,
						"transaction_status"=>$row->transaction_status
			);				
			array_push($json_array, $item_array);
		}
		header('Content-type: application/json');
		
			// return all items from offset to num
		echo json_encode($json_array);
	}
	function myLikes($data){
		//DATA: user_email
		$email = $data['user_email'];
		$db= IDB::connection();
		$sql= "select * from Item join (Like, User) on (like.User_user_id = user.user_id and item.item_id = like.Item_item_id)
		 where user_email = $email";
		$json_array = array();
		if ($result = $db->query($sql)){
			while($row= $result->fetchAll(PDO::FETCH_OBJ)){
				$item_array= array(
						//need modified
						"user_id"=>$row->User_user_id,
						"sold_date"=>$row->item_sold_date,
						"item_title"=>$row->item_title,
						"item_price"=>$row->item_price
				);
				array_push($json_array, $item_array);
			}
			// return all items from offset to num
			echo json_encode($json_array);
		
		}else{
			exit;
		}
		
	}
	function mySells($data){
		require 'preparePayment.php';
		$email = $data['user_email'];
		$db= IDB::connection();
		$sql_1 = "select * from User where user_email = ?";
		$query_1 = $db->prepare($sql_1);
		$query_1->execute(array($email));
		$row_1= $query_1->fetch(PDO::FETCH_ASSOC);
		$user_id = $row_1["user_id"];
		$sql= "select * from proximar_proxi.Order, proximar_proxi.Item where Item.item_owner_id = ? and Order.Item_item_id = Item.item_id";
		$query = $db->prepare($sql);
		$query->execute(array($user_id));
		$json_array = array();
		$rows= $query->fetchAll(PDO::FETCH_OBJ);
		foreach ($rows as $row){
			$escrow_id = $row->escrow_id;
			if($result= Braintree_Transaction::find($escrow_id)){
			$row_buyer_id = $row->User_user_id;
			$sql_buyer_id = "select * from User where user_id = ?";
			$query_buyer_id = $db->prepare($sql_buyer_id);
			$query_buyer_id->execute(array($row_buyer_id));
			$row_buyer_id = $query_buyer_id->fetch(PDO::FETCH_ASSOC);
			$item_array= array(
					//return data package need to be modified
					"item_id"=>$row->Item_item_id,
					"user_id"=>$row->User_user_id,
					"user_info"=> array(
						"user_first_name"=>$row_buyer_id["user_first_name"],
						"user_last_name"=>$row_buyer_id["user_last_name"],
						"user_email"=>$row_buyer_id["user_email"],
						"user_phone"=>$row_buyer_id["user_phone"]
						),
					"order_date"=>$row->order_date,
					"order_price"=>$row->order_price,
					"order_id"=>$row->order_id,
					"order_status"=>$result->escrowStatus,
					"item_img_url"=>$row->item_img_url,
					"item_title"=>$row->item_title,
					"item_description"=>$row->item_description
			);				
			array_push($json_array, $item_array);
			}else{
			
			}
		}
		header('Content-type: application/json');
		
			// return all items from offset to num
		echo json_encode($json_array);
	}
	
}