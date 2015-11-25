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
					"item_date"=>$row->item_post_date
			);				
			array_push($json_array, $item_array);
		}
		header('Content-type: application/json');
		
			// return all items from offset to num
		echo json_encode($json_array);
			
	}
	function myOrders($data){
		
		$email = $data['user_email'];
		$db= IDB::connection();
		$sql_1 = "select * from User where user_email = ?";
		$query_1 = $db->prepare($sql_1);
		$query_1->execute(array($email));
		$row_1= $query_1->fetch(PDO::FETCH_ASSOC);
		$user_id = $row_1["user_id"];
		$sql= "select * from proximar_proxi.Order left join proximar_proxi.Item on item_id = Item.item_id where User_user_id = ?";
		$query = $db->prepare($sql);
		$query->execute(array($user_id));
		$json_array = array();
		$rows= $query->fetchAll(PDO::FETCH_OBJ);
		foreach ($rows as $row){
			$item_array= array(
					//return data package need to be modified
					"item_id"=>$row->Item_item_id,
					"user_id"=>$row->User_user_id,
					"order_date"=>$row->order_date,
					"order_price"=>$row->order_price,
					"order_id"=>$row->order_id,
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
						"item_price"=>$row->item_price
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
		
		$email = $data['user_email'];
		$db= IDB::connection();
		$sql_1 = "select * from User where user_email = ?";
		$query_1 = $db->prepare($sql_1);
		$query_1->execute(array($email));
		$row_1= $query_1->fetch(PDO::FETCH_ASSOC);
		$user_id = $row_1["user_id"];
		$sql= "select * from proximar_proxi.Order left join proximar_proxi.Item on item_id = Item.item_id where Item.item_owner_id = ?";
		$query = $db->prepare($sql);
		$query->execute(array($user_id));
		$json_array = array();
		$rows= $query->fetchAll(PDO::FETCH_OBJ);
		foreach ($rows as $row){
			$item_array= array(
					//return data package need to be modified
					"item_id"=>$row->Item_item_id,
					"user_id"=>$row->User_user_id,
					"order_date"=>$row->order_date,
					"order_price"=>$row->order_price,
					"order_id"=>$row->order_id,
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
	
}