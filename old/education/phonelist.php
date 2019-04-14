<?php								
// leave this blank - we may never implement an authentication key
$smsarc_API_key = ''; 

// enter the user's 10 digit cell phone number. 
// example format: $smsarc_to = '5556667777';
$smsarc_number = '9613114135'; 

// lookup carrier
$ch = curl_init();	curl_setopt ($ch, CURLOPT_URL, 'http://www.smsarc.com/api-carrier-lookup.php?sa_number='.$smsarc_number.'&sa_key='.$smsarc_API_key); 	$AskApache_result = curl_exec ($ch); 		$smsarc_message_status =  $AskApache_result; curl_close($ch);

// print the carrier lookup results
echo $smsarc_carrier;
?>