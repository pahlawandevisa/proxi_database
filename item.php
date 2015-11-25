<?php
require_once 'database.php';
//test
//Need to modify AlterItem()

class Item{
	
	function index($method,$data){
		switch ($method){
			case "postItem":
				$this->postItem($data);
				//DATA: item_title, item_description,item_low_price, item_high_price, owner_id
				break;
			case "retrieveIMG":
				$this->retrieveIMG($data);
				//DATA:item_id
				break;
			case "alter":
				$this->alterItem($data);
				//DATA:item_title, item_description,item_low_price, item_high_price, owner_id
				break;
			case "drop":
				$this->dropItem($data);
				//DATA:item_id
				break;
			default:
				echo "Cannot Find Appropriate Method";
		}
	}
	
	
	
	//need modified
	function postItem($data){
		//item_low_price, item_high_price, item_title, item_description, user_email
		$db= IDB::connection();
		$item_title = $data["item_title"];
		$item_description = $data["item_description"];
		$item_low_price = $data["item_low_price"];
		$item_high_price= $data["item_high_price"];
		//need modified
		$owner_email = $data["user_email"];
		$sql_2 = "select user_id from proximar_proxi.User where user_email = '$owner_email'";
		$result_2 = $db->query($sql_2);
		$row_2 = $result_2->fetch(PDO::FETCH_ASSOC);
		$owner_id = $row_2["user_id"];
		$category_name = $data["item_category"];
		$rand_price = rand($data['item_low_price'],$data['item_high_price']);
		$target_dir = "images/";
			$target_file = $target_dir.basename($_FILES["item_image"]["tmp_name"]);
			$imageFileType = pathinfo($target_file,PATHINFO_EXTENSION);
			if (move_uploaded_file($_FILES["item_image"]["tmp_name"], $target_file)){
				$image = basename($_FILES["item_image"]["tmp_name"]);
			}
		
		$sql = "insert into proximar_proxi.Item (item_title, item_description,item_img_url, item_high_price, item_low_price, item_current_price,
				item_yesterday_price, item_price_update_date, 
				item_post_date,item_owner_id) values( ?,?,?,?,?,?,?,Now(),Now(),?)
				";
		
		$data_array = array($item_title, $item_description,$image,
				$item_high_price, $item_low_price, 
				$rand_price, $rand_price, $owner_id
			);
		
		$query= $db->prepare($sql);
		if($result = $query->execute($data_array)){
			echo "success";
			$sql_1 = "select item_id from proximar_proxi.Item where item_title = '$item_title' and item_description = 
			'$item_description' and item_owner_id = '$owner_id' and item_post_date = NOW()";
			$result_1 = $db->query($sql_1);
			$row_1 = $result_1->fetch(PDO::FETCH_ASSOC);
			$item_id = $row_1["item_id"]; 
			$this->item_category_relation($item_id, $category_name, $db);
		}else{
			echo "failure";
		}
	}//check
	
	
	function alterItem($data){
		//item_title, item_description,item_low_price, item_high_price, owner_id
		
		
		
	}
	function dropItem($data){
		$db = IDB::connection();
		$sql_1 = "delete from proximar_proxi.Item_Category where item_id = ?";
		$query_1 = $db->prepare($sql_1);
		$query_1->execute(array($data['item_id']));
		$sql_2 = "select item_img_url from proximar_proxi.Item where item_visibility = 1 and item_id= ?";
		$query_2 = $db->prepare($sql_2);
		if($result = $query_2->execute(array($data['item_id']))){
			$row = $query_2->fetch(PDO::FETCH_ASSOC);
			$item_img_url = $row['item_img_url'];
			$value = realpath('.').'/images/'.$item_img_url;
			if(file_exists($value)){
				unlink($value);
				$sql = "delete from proximar_proxi.Item where item_visibility =1 and item_id = ?";
				$query =$db->prepare($sql);
				if($result = $query->execute(array($data['item_id']))){
					echo "success";
				}else{
					echo "failure";
				}
			}else{
			echo "file not found";
			}
		}else{
			echo "failure";
		}
		
	}
	
	function item_category_relation($item_id,$category_name,$db){
		$sql = "select category_id from proximar_proxi.Category where category_name = ?";
		$query = $db->prepare($sql);
		$query->execute(array($category_name));
		$row= $query->fetch(PDO::FETCH_ASSOC);
		$category_id = $row['category_id'];
		$sql_1= "Insert into proximar_proxi.Item_Category (item_id, category_id) values (?,?)";
		$query_1 = $db->prepare($sql_1);
		if ($query_1->execute(array($item_id,$category_id))){

		}else{
			echo "category fail";
		}
	}
	
	
}