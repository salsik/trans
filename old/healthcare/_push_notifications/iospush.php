<?php

class IOS {

    function send_push($deviceToken, $data) {
        
        // Put your device token here (without spaces):
        //$deviceToken = 'a37ec69a3521becaa0f4a6f47e68ff1cc0b39c8c00424c6933f535fccb7d2c97';
        
        
        $passphrase = '';
        $ctx = stream_context_create();
        stream_context_set_option($ctx, 'ssl', 'local_cert', '/var/www/previsionpro/data/www/www.previsionpro.com/healthcare/_push_notifications/production_com.advances.previsionpro.pem');
        stream_context_set_option($ctx, 'ssl', 'passphrase', $passphrase);

        // Open a connection to the APNS server
        $fp = stream_socket_client('ssl://gateway.push.apple.com:2195', $err, $errstr, 60, STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT, $ctx);

        if (!$fp){
            $msg = "Failed to connect: $err $errstr";
        }     
        
        $message = $data[0];
        $url = $data[1];

        // Create the payload body
        $body['aps'] = array(
            'alert' => $message,
            'sound' => 'true',
            'link_url' => $url,
        );
        
        // Encode the payload as JSON
        $payload = json_encode($body);
        
        // Build the binary notification
        $msg = chr(0) . pack('n', 32) . pack('H*', $deviceToken) . pack('n', strlen($payload)) . $payload;

        // Send it to the server
        $result = fwrite($fp, $msg, strlen($msg));

        if (!$result){
            $msg = 'Message not delivered';
        }else{
            $msg = 'Message successfully delivered';
        }         
        // Close the connection to the server
        fclose($fp);
        
        return $msg;
    }

}
