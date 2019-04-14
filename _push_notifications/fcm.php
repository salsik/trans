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

class FCM {

    public function sendMessage($data, $target) {
        //FCM api URL
        $url = 'https://fcm.googleapis.com/fcm/send';
        //api_key available in Firebase Console -> Project Settings -> CLOUD MESSAGING -> Server key
        $server_key = 'AAAABkwOL2U:APA91bFz1AxXa8TscdQpKlqe9T0yUSa0Hi4MuzmLYtaUhgkZbzDNuhLFpoEQh3gbdI8uyZtk3A23nRNLgH9qhIllMBlAArhHS0PvuoVGEjmzBK3KbxnGUAskiVN1sklyHYOfv8LhYzyZ';

        $fields = array();
        $fields['data'] = $data;
        if (is_array($target)) {
            $fields['registration_ids'] = $target;
        } else {
            $fields['to'] = $target;
        }
        //header with content_type api key
        $headers = array(
            'Content-Type:application/json',
            'Authorization:key=' . $server_key
        );

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($fields));
        $result = curl_exec($ch);
        if ($result === FALSE) {
            die('FCM Send Error: ' . curl_error($ch));
        }
        curl_close($ch);
        return $result;
    }

}
