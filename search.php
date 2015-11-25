<?php
require_once 'database.php';

class Search{
	
	function index($method,$data){
		switch ($method){
			case "fetchAllItems":
				$this->fetchAllItems($data);
				//DATA: offset, number
				break;
			case "searchItemName":
				$this->searchItemName($data);
				//DATA: name, offset, number
				break;
			case "searchItemCategory":
				$this->searchItemCategory($data);
				//DATA: category_name, offset, number
				break;
			default:
				echo "Cannot Find Appropriate Method";
		}
	}//check
	
	function fetchAllItems($data){
		$offset = $data["offset"];
		$num = $data["number"];		
		
		$db = IDB::connection();
		$sql= "select * from Item where item_visibility = 1 order by UNIX_TIMESTAMP(item_post_date) asc limit $offset,$num";
		$query = $db->prepare($sql);
		$query->execute(array());
		$json_array= array();
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
	}//check
	
	function searchItemName($data){
		$name = $data['name'];
		$offset= $data['offset'];
		$num = $data['number'];
		
		$db = IDB::connection();
		$sql= "select * from Item where item_visibility = 1 and item_title like '%$name%' limit $offset, $num";
		$json_array = array();
		$query = $db->prepare($sql);
		$query->execute(array());
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
			// return all items from offset to num
		echo json_encode($json_array);
	}//check
	
	function searchItemCategory($data){
		$category_name = $data['category_name'];
		$num = $data['number'];
		//JOIN TABLES
		
		$db = IDB::connection();
		$sql= "select * from Item join (Item_Category, Category) 
				on (Item.item_id = Item_Category.item_id and Item_Category.category_id = Category.category_id)
		 	 	where Item.item_visibility = 1 and Category.category_name = ? order by item_post_date desc limit $num";
		$query = $db->prepare($sql);
		$query->execute(array($category_name));
		$json_array= array();
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
		
			// return all items from offset to num
		echo json_encode($json_array);

	}//check
	
}
?>