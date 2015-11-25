<?php
// Need to Modify the Proxi Verification Address
// Need to Encrypt the password

require_once 'database.php';

//NEED TO MODIFY USERINFO()

class User{
	/* REGISTRATION DATA PACKAGE
	 */
	function index($method,$data){
		switch ($method){
			case "register":
				$this->register($data);
				//DATA: "email","password","phone"
				break;
			case "login":
				$this->login($data);
				//DATA: "email","password"
				break;
			case "logout":
				$this->logout();
				break;
			case "alter":
				$this->alter($data);
				//DATA: "email","password","phone"
				break;
			case "retreivePassword":
				$this->retrievePassword($data);
				//DATA: "email","phone"
				break;
			case "userInfo":
				$this->userInfo($data);
				//DATA: "email"
				break;
			default:
				echo "Cannot Find Appropriate Method";
		}
	}
	
	
	function register($data){
		//Password, Email, Phone
		
		//Encrypt
		
		// Check Validation of Email
		if(!eregi("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$", $data["email"])){
			die("Email Address Is Not Validate.");
		}
		// Encrypt Password !!!   use base64_encode(string data) && base64_decode(string data)
		$encrypted_pwd = base64_encode($data["password"]);
		$user_active_key = md5(rand(0,1000));
		$email = $data['email'];
		$password= $data['password'];
		$phone = $data['phone'];
		
		$db = IDB::connection();
		$sql_email= "Select * from User where user_email = '$email'";
		//If Email Exists
		if ($query=$db->query($sql_email)){
			if (count($row = $query->fetchAll())!=0){
				die("Email already Exists");
			}
		}
		
		// Sequence need to be modified
		$sql = "Insert into User (user_email, user_password, user_active_key, user_phone, user_register_date, user_last_login)
				 values ( '$email', '$password', '$user_active_key', '$phone',NOW(),NOW()
				)";
				
		if ($query = $db->query($sql)){
			
			echo "successfully create user";
			/*$to = $email; // Send email to our user
			$subject = 'Activation For Your Proxi Account'; // Give the email a subject
			$message = '
		
						Thanks for signing up!
						Your account has been created, you can login with the following credentials after you have activated your account by pressing the url below.
					
						------------------------
						Username: '.$email.'
						Password: '.$password.'
						------------------------
					
						Please click this link to activate your account:
						http://www.proximarketplace.com/verify.php?email='.$email.'&hash='.$user_active_key.'
					
						'; // Our message above including the link
		
			// Need to be Modified, If Doesn't work, Use SMTP with Proxi gmail account.
			$headers = 'From:noreply@proximarketplace.com' . "\r\n"; // Set from headers
			*/
			
				
		}
		else{
			echo "cannot create user";
		}
		
	}//check
	
	
	// data: email, password
	function login($data){
		//Email, Password
		
		$email = $data['email'];
		$password= $data['password'];
		$db= IDB::connection();
		$sql= "select user_id, user_password, user_active from User where user_email = '$email'";
		if ($result = $db->query($sql)){
			$row= $result->fetch(PDO::FETCH_OBJ);
		}
		if ($row->user_password != $password||$row->user_active == "1") {
		echo "login error";
		}else{
		$id = $row->user_id;
		$sql_update = "update User set user_last_login= NOW() where user_id = '$id'";
		
		echo "success login";
		}
	}//check
	
	
	// setcookie (session_name(), '', time()-300) <---- Reset Session
	
	function logout(){
		
		session_destroy();
		echo "session_destroy";
		echo "session_id: ";
		echo print_r($_SESSION['id']); 
	}
	
	//@return true/false
	function alter($data){
		//Phone, Password
		
		$phone= $data['phone'];
		$password = $data['password'];
		$id = $_SESSION["id"];
		$db= IDB::connection();
		$sql= "update User set user_phone='$phone', user_password = '$password' where user_id = '$id'";
		//query->prepare.
		if ($result = $db->query($sql)) return true or die(false);
	}
	
	function retrievePassword($data){
		//Email, Phone
		
		// malefunctional.
		$email = $data['email'];
		$phone = $data['phone'];
		$db = IDB::connection();
		$sql = "update User set user_password = '' where user_email = ? and user_phone = ?";
		$query = $db->prepare($sql);
		if($result = $query->execute(array($email,$phone))){
			
			//send EMAIL to user to reset password
		}else{
			echo "failed to connect";
		}
	}
	
	//NEED MODIFIED
	function userInfo($data){
		$db = IDB::connection();
		$sql = "select * from user where user_email = ?";
		$query = $db->prepare($sql);
		$email = $data['email'];
		$query->execute(array($email));
		$row = $query->fetch(PDO::FETCH_OBJ);
		$data_array = array(
				"user_email" => $row->user_email,
				"user_phone" => $row->user_phone
				// userInfo
		);
		print_r($data_array);
		
	}
	
}

?>