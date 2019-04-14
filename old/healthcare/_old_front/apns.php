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
	private $gateway = 'ssl://gateway.sandbox.push.apple.com:2195';
	private $certificate = '';
	private $passphrase = '';
	private $flags = array();
	private $testMode = false;
	private $apnsConnection;
	private $connected;
	private $pconnect = false;
	public $error = '';


	// constructor
	function __construct($certificate = '', $pconnect = false) {
		$this->certificate = $certificate;
		$this->pconnect = $pconnect;
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
	public function send_notification($deviceToken, $data ) {

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
		$payload = json_encode( $payload );

		if( !$this->connected || $this->pconnect) {
			$this->connected = true;

			$streamContext = stream_context_create();

			stream_context_set_option($streamContext, 'ssl', 'local_cert', $this->certificate);
			if($this->passphrase) {
				stream_context_set_option($streamContext, 'ssl', 'passphrase', $this->passphrase);
			}
			$this->apnsConnection = stream_socket_client($apnsHost, $error, $errorString, 2, STREAM_CLIENT_CONNECT, $streamContext);
		}
		if($this->apnsConnection == false) {
			$this->error = "Failed to connect {$error} {$errorString}\n";
//			$this->closeConnections();
			return false;
		}

		$apnsMessage = chr(0) . chr(0) . chr(32) . pack('H*', $token) . chr(0) . chr(strlen($payload)) . $payload;
//		$apnsMessage = chr(0) . pack('n', 32) . pack('H*', $token) . pack('n', strlen($payload)) . $payload;

		$result = @fwrite($this->apnsConnection, $apnsMessage);
//		$result = @fwrite($this->apnsConnection, $apnsMessage, strlen($apnsMessage));
		if( !$this->pconnect ) {
			$this->closeConnections();
		}
		if ( !$result ) {
			$this->error = 'Message not delivered';
			return false;
		}

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
}



