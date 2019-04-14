<?php
include('_startup.php');

	$error = array();
	if( $_SERVER['REQUEST_METHOD'] == 'POST' )
	{
		if( ! stripos($_SERVER['HTTP_REFERER'], str_replace('www.', '', strtolower($_SERVER['HTTP_HOST'])) )) //. '/contact'
		{
			$error['error'] = 'Error!';
		}
		else
		{
			clearPost();
		
			if ( empty( $_POST['name'] ) || strtolower( $_POST['name'] ) == 'name' )
			{
				$error['name'] = 'Name is required!';
			}
			if( !isEmail( $_POST['email'] ) )
			{
				$error['email'] = 'E-Mail is required!';
			}
			if ( empty( $_POST['message'] ) || strtolower( $_POST['message'] ) == 'message' )
			{
				$error['message'] = 'Message is required!';
			}
		}

		if( !empty( $error ))
		{
			echo json_encode(array(
				'status' => 'error',
				'error' => $error
			));
			exit;
		}
		else
		{
			include '_emails.php';
			$status = send_contact_email( $_POST, 'rewaq.com@gmail.com' );

$Msg = <<<EOF
		Thank you for visiting our website,<br />
		Your message has been sent successfully !!!<br />
		We will get back to you as soon as possible<br />
		Regards,
EOF;

		echo json_encode(array(
				'status' => 'sent',
				'val' => $status,
				'msg' => "$Msg",
//				'msg' => str_replace("\n", "", "$Msg"),
			));
			exit;
		}
	}

