<?php
	require 'preparePayment.php';
	$phoneNum = $POST["phone"];
	$id = $POST["user_id"];
	$username = $POST["user_name"];
	merchantAccountParams = [
	  'individual' => [
	    'firstName' => 'Jane',
	    'lastName' => 'Doe',
	    'email' => 'jane@14ladders.com',
	    'phone' => '5553334444',
	    'dateOfBirth' => '1981-11-19',
	    'address' => [
	      'streetAddress' => '111 Main St',
	      'locality' => 'Chicago',
	      'region' => 'IL',
	      'postalCode' => '60622'
	    ]
	  ],
	  'funding' => [
	    'descriptor' => $username,
	    'destination' => Braintree_MerchantAccount::FUNDING_DESTINATION_MOBILE_PHONE,
	    'mobilePhone' => $phoneNum
	  ],
	  'tosAccepted' => true,
	  'masterMerchantAccountId' => "xcw69y6y437ct7mw",
	  'id' => $id
	]
	$result = Braintree_MerchantAccount::create(merchantAccountParams);

?>