<?php

class Handler{
	function operate($object,$method,$data){
		switch ($object){
			case "User":
				require 'user.php';
				$user = new User();
				$user->index($method,$data);
				break;
			case "Item":
				require 'item.php';
				$item = new Item();
				$item->index($method, $data);
				break;
			case "Like":
				require 'like.php';
				$like = new Like();
				$like->index($method, $data);
				break;
			case "Manager":
				require 'manager.php';
				$manager = new Manager();
				$manager->index($method, $data);
				break;
			case "Transaction":
				require 'transaction.php';
				$transaction = new Transaction();
				$transaction->index($method, $data);
				break;
			case "Search":
				require 'search.php';
				$search = new Search();
				$search->index($method, $data);
				break;
			case "Transaction_Test":
				require	'test_transaction.php';
				$transaction_test= new Transaction_Test();
				$transaction_test->index($method,$data);
				break;
			case "Event":
				require 'event.php';
				$event = new Event();
				$event ->index($method,$data);
		}
	}
}//check

?>