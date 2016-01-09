<?php
require_once 'database.php';



// order. USER_user_id is the buyer_id
// transaction, User_user_id is the nener_id
//NEED TO SEND CONFIRMATION EMAIL

class Transaction_Test{
	
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
			case "refund":
				$this->refund($data);
				break;
			default:
				echo "Cannot Find Appropriate Method";
		}
	}
	
	function checkout($data){
		//Check Item Current Price
		//Item.visibility= false
		//Data [ item_id item_current_price user_email paymentMethodNonce ]
		//Create Order
		//item_id
		require 'preparePayment.php';
		

		$db= IDB::connection();
		//check if item is available
		$sql_3 = "select * from Item where item_id = ?";
		$query_3 = $db->prepare($sql_3);
		$query_3->execute(array($data['item_id']));
		$result_3 = $query_3->fetch(PDO::FETCH_ASSOC);
		$item_status = $result_3["item_status"];
		//if ($item_status==="unordered") {
			$order_price = $result_3["item_current_price"];
			$sql_4 = "select user_id from User where user_email = ?";
			$query_4 = $db->prepare($sql_4);
			$query_4->execute(array($data['user_email']));
			$result_4 = $query_4->fetch(PDO::FETCH_ASSOC);
			$user_id = $result_4["user_id"];
			$sql_1 = "update Item set item_visibility = 0,item_status='ordered' where item_id = ? and item_visibility = 1";
			$query_1 = $db->prepare($sql_1);
			$query_1->execute(array($data['item_id']));
			//change item_status to ordered

			$sql_2 = "Insert into proximar_proxi.Order (order_price, order_date, Item_item_id, User_user_id) values (?,NOW(),?,?) ";
			$query_2 = $db->prepare($sql_2);
			if($result = $query_2->execute(array($order_price,$data['item_id'],$user_id))){
				//finished purchase
				$item_id = $data["item_id"];
				$sql_item_owner = "select * from Item where item_id = '$item_id'";
				if (!$result_item_owner = $db->query($sql_item_owner)) {
					die("error");
				}
				$row = $result_item_owner->fetch(PDO::FETCH_ASSOC);
				$item_owner_id = $row["item_owner_id"];
				$sql_owner_id = "select * from User where user_id = '$item_owner_id'";
				if (!$result_owner_id = $db->query($sql_owner_id)) {
					die("error");
				}
				$row_owner = $result_owner_id->fetch(PDO::FETCH_ASSOC);
				$merchantAccountId = $row_owner["merchantAccount_id"];
				$resultPayment = Braintree_Transaction::sale([
					'amount' => $order_price,
 					'merchantAccountId' => $merchantAccountId,
 					//MerchantAccountID will be the seller Merchant Account ID
  					'paymentMethodNonce' => 'fake-valid-visa-nonce',
   					'options' => [
     					'submitForSettlement' => true,
    					'holdInEscrow' => true,
   					],
    				'serviceFeeAmount' => '2.00'
				]);
				
				if ($resultPayment->success) {
					//Save Escrow Transaction ID
					//Order New Column
					$transaction_id = $resultPayment->transaction->id;
					$update = "update proximar_proxi.Order set status = 'paid', escrow_id='$transaction_id' 
					where Item_item_id=?";
					$update_query = $db->prepare($update);
					$update_result = $update_query->execute(array($data['item_id']));
					if ($update_result) {
						echo "success";	
						//final protocol
					}else{
						echo "fail to update order";
					}
				}else{
					echo $resultPayment;
					echo "payment fails";
				}
			}else{
				echo "failed to insert order";
			}	
		
		//}else{
		//	echo $item_status;
		//	echo "item has already been ordered";
		//}
		$messageToSeller = $data['message'];
		//Send Confirmation Email
	}//check
	
	//admin php
	function cancelOrder($data){
		//Item.visibility = true
		//Drop Order
		$db = IDB::connection();
		$sql_1= "update Item set item_visibility = 1, item_status='unordered' where item_id = ?";
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
		require 'preparePayment.php';
		
		$db = IDB::connection();
		$item_sql = "select * from proximar_proxi.Item where item_id=?";
		$item_query = $db->prepare($item_sql);
		$item_query->execute(array($data["item_id"]));
		$item_row = $item_query->fetch(PDO::FETCH_ASSOC);
		$item_owner_id = $item_row["item_owner_id"];
		$item_title = $item_row["item_title"];

		$order_sql = "select * from proximar_proxi.Order where Item_item_id=?";
		$order_query = $db->prepare($order_sql);
		$order_query->execute(array($data["item_id"]));
		$order_row = $order_query->fetch(PDO::FETCH_ASSOC);
		$status = $order_row["status"];
		$buyer_id = $order_row["User_user_id"];
		$transaction_id = $order_row["escrow_id"];
		if (isset($transaction_id)) {
			//escrow reinburse

			$order_price = $order_row["order_price"];
			$update_order = "update proximar_proxi.Order set status='finished'";
			$update_order_query = $db->prepare($update_order);
			$update_result = $update_order_query->execute();
			if ($update_result) {
				$payment_result = Braintree_Transaction::releaseFromEscrow("$transaction_id");
				if ($payment_result->success) {
					$sql_3 = "insert into proximar_proxi.Transaction (User_user_id, item_buyer_id, item_title, 
					item_sold_date,item_price) 
				values ( ?, ?, ?, NOW(),? )";
					$query_3 = $db->prepare($sql_3);
					if($query_3->execute(array($item_owner_id, $buyer_id, $item_title,$order_price))){
						$sql_4= "delete from proximar_proxi.Order where Item_item_id = ?";
						$query_4 = $db->prepare($sql_4);
						$result_4= $query_4 ->execute(array($data['item_id']));
						
						$sql_6= "delete from proximar_proxi.Item_Category where item_id = ?";
						$query_6 = $db->prepare($sql_6);
						$result_6= $query_6 ->execute(array($data['item_id']));

						$sql_5= "delete from proximar_proxi.Item where item_id = ?";
						$query_5 = $db->prepare($sql_5);
						$result_5= $query_5->execute(array($data['item_id']));

						if ($result_5&&$result_6&&$result_4){
							echo "success";
						}else{
							echo "failed to drop";
						}
					}else{
						echo "fail to Create Transaction";
					}
				}else{
					echo $payment_result;
					echo "payment didn't get through";
					// print payment failure message and send to proxi email.
				}
			}else{
				echo "failed to update order";
			}

		}else{
			echo "fail to check order";
			}//Send Confirmation Email
		}//check
		
	function refund($data){
		require 'preparePayment.php';
		
		$db = IDB::connection();
		$item_sql = "select * from proximar_proxi.Item where item_id=?";
		$item_query = $db->prepare($item_sql);
		$item_query->execute(array($data["item_id"]));
		$item_row = $item_query->fetch(PDO::FETCH_ASSOC);
		$item_owner_id = $item_row["item_owner_id"];
		$item_title = $item_row["item_title"];

		$order_sql = "select * from proximar_proxi.Order where Item_item_id=?";
		$order_query = $db->prepare($order_sql);
		$order_query->execute(array($data["item_id"]));
		$order_row = $order_query->fetch(PDO::FETCH_ASSOC);
		$status = $order_row["status"];
		$buyer_id = $order_row["User_user_id"];
		$transaction_id = $order_row["escrow_id"];
		if (isset($transaction_id)) {
			//escrow reinburse

			$order_price = $order_row["order_price"];
			$update_order = "update proximar_proxi.Order set status='finished'";
			$update_order_query = $db->prepare($update_order);
			$update_result = $update_order_query->execute();
			if ($update_result) {
				$payment_result = Braintree_Transaction::refund("$transaction_id");
				$transaction_status= "refunded";
				if ($payment_result->success) {
					$sql_3 = "insert into proximar_proxi.Transaction (User_user_id, item_buyer_id, item_title, 
					item_sold_date,item_price,transaction_status) 
				values ( ?, ?, ?, NOW(),?,? )";
					$query_3 = $db->prepare($sql_3);
					if($query_3->execute(array($item_owner_id, $buyer_id, $item_title,$order_price,$transaction_status))){
						$sql_4= "delete from proximar_proxi.Order where Item_item_id = ?";
						$query_4 = $db->prepare($sql_4);
						$result_4= $query_4 ->execute(array($data['item_id']));
						
						$sql_6= "delete from proximar_proxi.Item_Category where item_id = ?";
						$query_6 = $db->prepare($sql_6);
						$result_6= $query_6 ->execute(array($data['item_id']));

						$sql_5= "delete from proximar_proxi.Item where item_id = ?";
						$query_5 = $db->prepare($sql_5);
						$result_5= $query_5->execute(array($data['item_id']));

						if ($result_5&&$result_6&&$result_4){
							echo "success";
						}else{
							echo "failed to drop";
						}
					}else{
						echo "fail to Create Transaction";
					}
				}else{
					echo $payment_result;
					echo "payment didn't get through";
					// print payment failure message and send to proxi email.
				}
			}else{
				echo "failed to update order";
			}

		}else{
			echo "fail to check order";
			}//Send Confirmation Email
		}//check
		
		
}
	function query($sql, $dataArray){
		$db = IDB::connection;
		$query = $db->prepare($sql);
		$query->execute(array($dataArray));
		$row = $query->fetch(PDO::FETCH_ASSOC);
		return $row;
	} // return only one row




?>