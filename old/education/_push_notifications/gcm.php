<?php

/*
 * time_to_live
 * delay_while_idle
 * collapse_key
 * 
 * collapse_key  will be lemeted to 4 only, If you exceed this number GCM will only keep 4 collapse keys, with no guarantees about which ones they will be.
 * There is a limit on how many messages can be stored without collapsing. That limit is currently 100.
 * 
 * If the device never gets connected again (for instance, if it was factory reset), the message will eventually time out and be discarded from GCM storage. 
 * The default timeout is 4 weeks, unless the time_to_live flag is set.
 * 
 * when GCM attempts to deliver a message to the device and the application was uninstalled, GCM will discard that message right away and invalidate the registration ID. 
 * Future attempts to send a message to that device will get a NotRegistered error.
 * 
 * 
 * {
  "registration_id" : "APA91bHun4MxP5egoKMwt2KZFBaFUH-1RYqx...",
  "data" : {
    "Nick" : "Mario",
    "Text" : "great match!",
    "Room" : "PortugalVSDenmark",
  },
}
 * {
  "collapse_key" : "demo",
  "delay_while_idle" : true,
  "registration_ids" : ["xyz"],
  "data" : {
    "key1" : "value1",
    "key2" : "value2",
  },
  "time_to_live" : 3
}
 * 
 * 
 */




class GCM {
	
	private $key = '';
	private $flags = array();
	public $error = '';
	public $headers = '';
	
 
    //put your code here
    // constructor
    function __construct($key) {
         $this->key = $key;
    }
    function setTimeLife($seconds = 0) {
    	$seconds = (int) $seconds;
    	$this->_setFlag('time_to_live', $seconds);
    }
    function setDelay($delay = false) {
    	$delay = (bool) $delay;
    	$this->_setFlag('delay_while_idle', $delay);
    }
    function setCollapse($key = '') {
    	$this->_setFlag('collapse_key', $key);
    }
    
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
    public function send_notification($registatoin_id, $data) {
         
        // Set POST variables
        $url = 'https://android.googleapis.com/gcm/send';
        
        $fields = $this->flags;
        if( is_array( $registatoin_id ) ) {
        	$fields['registration_ids'] = $registatoin_id;
        }
        else {
        	$fields['registration_ids'] = array( $registatoin_id );
        }
        $fields['data'] = (array)$data;
//print_r($fields);
        $this->headers = array(
            'Authorization: key=' . $this->key,
            'Content-Type: application/json'
        );
print_r($this->headers);
        // Open connection
        $ch = curl_init();
 
        // Set the url, number of POST vars, POST data
        curl_setopt($ch, CURLOPT_URL, $url);
 
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $this->headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
 
        // Disabling SSL Certificate support temporarly
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
 
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
 
        // Execute post
        $result = curl_exec($ch);
        echo 'pushh ';print_r($ch);
        if ($result === FALSE) {
            $this->error = 'Curl failed: ' . curl_error($ch);
            return false;
        }
 
        // Close connection
        curl_close($ch);
        return $result;
    }
 
}
 


