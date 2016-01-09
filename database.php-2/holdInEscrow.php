<?php
	require "BraintreeLibrary/braintree-php-3.7.0/lib/Braintree.php";
	$merchantId = "xcw69y6y437ct7mw";
	
	Braintree_Configuration::environment('sandbox');
	Braintree_Configuration::merchantId($merchantId);
	Braintree_Configuration::publicKey('mzd9yptqz6dd7ym9');
	Braintree_Configuration::privateKey('9c86283aeb4a127ccb9999f20cb0c451');
	$transaction_id = "j96qfy";
	$result = Braintree_Transaction::releaseFromEscrow($transaction_id);
	echo $result->success;
	echo $result;



?>