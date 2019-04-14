<?php

//{
//	"aps":
//	{
//		"alert":
//		{
//			"action-loc-key": "Open",
//			"body": "Hello, world!"
//		},
//		"badge": 2,
//		"sound":"default"
//	}
//}










class APNS {

	private $sandbox = 'ssl://gateway.sandbox.push.apple.com:2195';
	private $gateway = 'ssl://gateway.push.apple.com:2195';
	private $certificate = '';
	private $passphrase = '';
	private $flags = array();
	private $testMode = false;
	private $apnsConnection;
	private $connected;
	private $debug;
	private $pconnect = false;
	private $expire = 0;
	public $error = '';


	// constructor
	function __construct($certificate = '', $pconnect = false) {
		$this->certificate = $certificate;
		$this->pconnect = $pconnect;
		$this->expire = time() + (90 * 24 * 60 * 60); //Keep push alive (waiting for delivery) for 90 days
	}
	function setDebug( $debug ) {
		$this->debug = $debug;
	}
	function setPassphrase($passphrase ) {
		$this->passphrase = $passphrase;
	}
	function setTestMode($test = true) {
		$delay = (bool) $delay;
		$this->testMode = ($test) ? true : false;
	}
	//	function setDelay($delay = false) {
	//		$delay = (bool) $delay;
	//		$this->_setFlag('delay_while_idle', $delay);
	//	}

	private function _setFlag($flag, $value) {
		if( !$value ) {
			unset( $this->flags[ $flag ] );
		}
		else {
			$this->flags[ $flag ] = $value;
		}
	}

	/**
	 * Sending Push Notification
	 */
	public function send_notification($deviceToken, $data, $identifier = 0) {

		$this->error = '';
		if( !($token = $this->isDeviceTooken($deviceToken)) ) {
			$this->error = "DeviceToken is not valid!}\n";
			return false;
		}

		if( $this->testMode ) {
			$apnsHost = $this->sandbox;
		}
		else {
			$apnsHost = $this->gateway;
		}

		$aps = array();
		$data = (array) $data;
		if( $data['alert'] ) {
			$aps['alert'] = $data['alert'];
			unset( $data['alert'] );
		}
		if( $data['badge'] ) {
			$aps['badge'] = $data['badge'];
			unset( $data['badge'] );
		}
		if( !$aps ) {
			$this->error = "No data to send\n";
			return false;
		}
		if( !isset($data['sound']) ) {
			$aps['sound'] = 'default';
		}
		
		if( $data['sound'] ) {
			$aps['sound'] = 'default';
			unset( $data['sound'] );
		}

		$payload = array(
			'aps' => $aps,
		);
		if( $data ) {
			$payload['data'] = $data;
		}
		unset($payload['data']);
		$payload = json_encode( $payload );
		if( $this->debug ) {
			print_r($payload);
		}

		if( !$this->connected || !$this->pconnect) {
			$this->connected = true;

			$streamContext = stream_context_create();

			stream_context_set_option($streamContext, 'ssl', 'local_cert', $this->certificate);
			if($this->passphrase) {
				stream_context_set_option($streamContext, 'ssl', 'passphrase', $this->passphrase);
			}
			
			$this->apnsConnection = stream_socket_client($apnsHost, $error, $errorString, 2, STREAM_CLIENT_CONNECT, $streamContext);
			@stream_set_blocking ($this->apnsConnection, 0);
		}
		
		if($this->apnsConnection == false) {
			$this->error = "Failed to connect {$error} {$errorString}\n";
//			$this->closeConnections();
			return false;
		}

		$apnsMessage = pack("C", 1) . pack("N", intval($identifier)) . pack("N", $this->expire) . pack("n", 32) . pack('H*', $token) . pack("n", strlen($payload)) . $payload; //Enhanced Notification
//		$apnsMessage = chr(0) . chr(0) . chr(32) . pack('H*', $token) . chr(0) . chr(strlen($payload)) . $payload;
//		$apnsMessage = chr(0) . pack('n', 32) . pack('H*', $token) . pack('n', strlen($payload)) . $payload;

		@fwrite($this->apnsConnection, $apnsMessage);
//		@fwrite($this->apnsConnection, $apnsMessage, strlen($apnsMessage));

		usleep(500000);
		if( ($error = $this->checkErrorResponse()) ) { //We can check if an error has been returned while we are sending, but we also need to check once more after we are done sending in case there was a delay with error response.
			$this->error = 'Message not delivered';
			$this->error = $error['error'];
		}
		if( !$this->pconnect ) {
			$this->closeConnections();
		}
//		if ( !$result ) {
//			$this->error = 'Message not delivered';
//			return false;
//		}

		// Close connection
		return $result;
	}

	function closeConnections()
	{
		@socket_close($this->apnsConnection);
		@fclose($this->apnsConnection);
	}

	function isDeviceTooken($token) {
		$token = str_replace(' ', '', $token);
		return (@preg_match("/^[a-fA-F0-9]+$/i", $token)) ? $token : false;
	}
	
	
	function checkErrorResponse() {

	   $response = fread($this->apnsConnection, 6); //byte1=always 8, byte2=StatusCode, bytes3,4,5,6=identifier(rowID). Should return nothing if OK.
	   //NOTE: Make sure you set stream_set_blocking($this->apnsConnection, 0) or else fread will pause your script and wait forever when there is no response to be sent.

	   if (!$response) {
	 	  return false;
	   }
		$error_response = unpack('Ccommand/Cstatus_code/Nidentifier', $response); //unpack the error response (first byte 'command" should always be 8)
		switch( $error_response['status_code'] ) {
			case '0':
				$error_response['error'] = '0-No errors encountered';
				break;
			case '1':
				$error_response['error'] = '1-Processing error';
				break;
			case '2':
				$error_response['error'] = '2-Missing device token';
				break;
			case '3':
				$error_response['error'] = '3-Missing topic';
				break;
			case '4':
				$error_response['error'] = '4-Missing payload';
				break;
			case '5':
				$error_response['error'] = '5-Invalid token size';
				break;
			case '6':
				$error_response['error'] = '6-Invalid topic size';
				break;
			case '7':
				$error_response['error'] = '7-Invalid payload size';
				break;
			case '8':
				$error_response['error'] = '8-Invalid token';
				break;
			case '255':
				$error_response['error'] = '255-None (unknown)';
				break;
			default:
				$error_response['error'] = $error_response['status_code'].'-Not listed';
				break;
		}
		return $error_response;
	}
}



