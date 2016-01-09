<?php
	require 'database.php';
//malfunctional


class Event{
	function index($method,$data){
		switch ($method){
			case "fetchEvents":
				$this->fetchEvents();
				//DATA:item_id, user_id
				break;
			default:
				echo "Cannot Find Appropriate Method";
		}
	}//check
	
	
	 function fetchEvents(){
	 	$db= IDB::connection();
	 	$sql = "select * from proximar_proxi.Event";
	 	$query = $db->prepare($sql);
	 	$query->execute();
	 	$json_array = array();
	 	$rows = $query->fetchAll(PDO::FETCH_OBJ);
	 	foreach ($rows as $row) {
				$event= array(
					
					"event_title"=>$row->title,
					"event_url"=>$row->url,
					"img_url"=>$row->img_url,
					"event_time"=>$row->event_time,
					"event_place"=>$row->place,
					"event_description"=>$row->description

			);		
				array_push($json_array, $event);	
		}

		header('Content-type: application/json');

		echo json_encode($json_array);
	}

	
}





?>