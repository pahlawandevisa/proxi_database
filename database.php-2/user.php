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
		require "./BraintreeLibrary/braintree-php-3.7.0/lib/Braintree.php";
		Braintree_Configuration::environment('sandbox');
		Braintree_Configuration::merchantId('xcw69y6y437ct7mw');
		Braintree_Configuration::publicKey('mzd9yptqz6dd7ym9');
		Braintree_Configuration::privateKey('9c86283aeb4a127ccb9999f20cb0c451');
		if(!eregi("^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$", $data["email"])){
			die("Email Address Is Not Validate.");
		}
		$user_active_key = md5(rand(0,1000));
		$email = $data['email'];
		$password= $data['password'];
		$phone = $data['phone'];
		$firstName = $data['firstName'];
		$lastName = $data['lastName'];
		
		$db = IDB::connection();
		$sql_email= "Select * from User where user_email = '$email'";
		//If Email Exists
		if ($query=$db->query($sql_email)){
			if (count($row = $query->fetchAll())!=0){
				die("Email already Exists");
			}
		}
		$merchantAccountParams = [
	 	 'individual' => [
	  	  'firstName' => $data["firstName"],
	  	  'lastName' => $data["lastName"],
	 	   'email' => $data["email"],
	 	   'phone' => $data["phone"],
	 	   'dateOfBirth' => $data["dateOfBirth"],
	 	   'address' => [
	 	     'streetAddress' => '501 College Ave.',
	  	    'locality' => 'Wheaton',
	  	    'region' => 'IL',
	 	     'postalCode' => '60187'
	 	   ]
		  ],
		  'funding' => [
		    'destination' => Braintree_MerchantAccount::FUNDING_DESTINATION_MOBILE_PHONE,
		    'mobilePhone' => $data["venmoPhone"]
		  ],
		  'tosAccepted' => true,
		  'masterMerchantAccountId' => "proximarketplaceinc"
		];
		$result = Braintree_MerchantAccount::create($merchantAccountParams);
		if ($result->success) {
			$transaction_id = $result->merchantAccount->id;
			if (empty($transaction_id)) {
				die("failed");
			}
			$sql = "Insert into User (user_email, user_password, user_active_key, user_phone, user_register_date, user_last_login, merchantAccount_id,user_first_name,user_last_name)
				 values ( '$email', '$password', '$user_active_key', '$phone',NOW(),NOW(),'$transaction_id','$firstName','$lastName'
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
				//email to proxi about all user info.
				echo "cannot create user";
			}
			
		}else{
			echo $result;
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