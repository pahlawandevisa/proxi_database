<?php
require 'handler.php';


	$object = $_POST['object'];
	$method = $_POST['method'];
	$data = $_POST['data'];

	
/*Testing Phase
 * //Register: "email","password","phone"//Login: "email","password"//Alter: "email","password","phone"//userInfo: "email"
 * //PostItem:item_title, item_description,item_low_price, item_high_price, owner_id //retrieveIMG: item_id
 * //fetchAllItem: offset, number //searchItemName: name, offset, number
 * 
 */
	
	
	$data_item = array (
			
			"item_id"=>51
	);
	
	$data_search = array(
			"offset" =>'0',
			"number"=>'20',
			"name"=>'hand'
	);
	
	$data_like = array(
			"user_id" =>20,
			"item_id" =>14
			
	);
	$data_transaction = array(
			"user_id" =>2,
			"item_id" =>36
				
	);
	$data_manager = array(
			"user_email"=>"michael.liu@my.wheaton.edu",
			"offset"=>"0",
			"number"=>"20"
	);


$handler = new Handler();
//$handler->operate("Item","drop",$data_item);

$handler->operate($object, $method, $data);
//check

?>