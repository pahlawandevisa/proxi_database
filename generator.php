<?php
	require 'preparePayment.php';
	echo($clientToken = Braintree_ClientToken::generate());

?>