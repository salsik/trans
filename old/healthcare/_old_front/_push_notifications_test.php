<?php

$testAndroid = false;
$testIOS = true;

//define('ANDROID_API_KEY', 'AIzaSyBe5SIEJhiXsEnUIqpBbbSY7nttJqhlKuU'); // Android Key
define('ANDROID_API_KEY', 'AIzaSyD1qmdOpc_TNqIibdxU9Uq0b3cTsM0e0Fg'); // Server Key
//define('ANDROID_API_KEY', 'AIzaSyBelQc3n7fXiM6awh525QAE1VJ9FcZBazM'); // BROWSER  Key

$registatoin_ids = array(
	'APA91bGjuWeaX70t1w-CY0yAQfJXDhveymcO5iCcrtDRTSZ6ZxEdubeTIIeqhc5EcvXdeT7lqcZhPkoyeSwtPM1hCHtdTOxNzkhDOufgQcvmzZcYVh1uIDZe4UQlavYmj_uc1UuG-vLGgB8qkNf6jXToi-KgLN-Hnw',
	'APA91bGFR8XTwA-Fh9DsNSeWZJsq4x0_xjNiF_QVv1OlHMlhJnj6okIm06z1LJbUiMqg_-TkS4F3c4JbFGQf4mRlu8SQTwsrYLaTndxuV1a29bsxterNR80e_JHOsORbTnqmgH2kHMaNWP-TXaZRcKSZCMmdF96eUg',
);


// Put your private key's passphrase here:
$passphrase = '123456';
$pem = 'apns-dev.pem';
$token = '2a334ad4501b7e33b615f4346084efc4bf05935e1a3b49cc0b59e2739502c86b';
//$payload = '{"aps":{"alert":"You have new updates!!","badge":1,"sound":"default"}}';
$payload = '{"aps":{"alert":"test","badge":1,"sound":"default"}}';

if( $testIOS ) {
	sendIOSNotification($passphrase, $pem, $token, $payload);
}



if( $testAndroid ) {
	sendAndroidNotification($registatoin_ids);
}


function sendIOSNotification($passphrase, $pem, $token, $payload = '') {

	$ctx = stream_context_create();
	stream_context_set_option($ctx, 'ssl', 'local_cert', $pem);
	stream_context_set_option($ctx, 'ssl', 'passphrase', $passphrase);
	
	// Open a connection to the APNS server
	$fp = stream_socket_client(
//	        'ssl://feedback.sandbox.push.apple.com:2196', $err,
//	        $errstr, 60, STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT, $ctx);
	        'ssl://gateway.sandbox.push.apple.com:2195', $err,
	        $errstr, 60, STREAM_CLIENT_CONNECT, $ctx);
	
	if (!$fp)
	        exit("Failed to connect: $err $errstr" . PHP_EOL);
	
	echo 'Connected to APNS' . PHP_EOL;
	
	
	$apnsMessage = chr(0) . chr(0) . chr(32) . pack('H*', $token) . chr(0) . chr(strlen($payload)) . $payload;
	$result = fwrite($fp, $apnsMessage);
	var_dump($result);
	
	
//	while (!feof($fp)) {
//	        $data = fgets($fp, 1024);
//	        var_dump( $data );
//	        var_dump(unpack("N1timestamp/n1length/H*devtoken", $data));
//	        break;
//	}
	// Close the connection to the server
	fclose($fp);
}

function sendAndroidNotification($registatoin_ids, $message = '') {

    // Replace with real BROWSER API key from Google APIs
    $apiKey = ANDROID_API_KEY;


// Message to be sent
    //$message = "testing";

// Set POST variables
    $url = 'https://android.googleapis.com/gcm/send';

    $fields = array(
        'registration_ids' => $registatoin_ids,
        'data' => array(
        	"message" => 'You have 2 new messages',
    		'replies' => 2,
//    		'documents' => 2,
//    		'news' => 2,
    	),
    );

    $headers = array(
        'Authorization: key=' . $apiKey,
        'Content-Type: application/json'
    );

// Open connection
    $ch = curl_init();

// Set the url, number of POST vars, POST data
    curl_setopt($ch, CURLOPT_URL, $url);

    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));

// Execute post
    $result = curl_exec($ch);

// Close connection
    curl_close($ch);

    echo $result;
}


