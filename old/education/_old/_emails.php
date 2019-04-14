<?php

// ###################################################################################
// ### Users Emails
// ###################################################################################

function send_contact_email( $info, $To )
{
	$BASE_URL = BASE_URL;

	$Message = nl2br( $info['message'] );

	$msg = <<<EOF
<div dir="ltr">
	<div class="gmail_quote">
		<div style="padding:0px 20px 20px 20px">
			<h2 style="font-size:22px;min-height:30px;color:#cc6600;border-bottom:dashed 1px gray">New contact</h2>
			<br>

			<p>Dear Admin,</p>

			<p>A new contact sent to you from {$info['name']} &laquo;{$info['email']}&raquo; :</p>
			<p>Name: {$info['name']}</p>
			<p>E-mail: {$info['email']}</p>
			<p>Phone: {$info['phone']}</p>
			<p>Company Name: {$info['company_name']}</p>
			<p>~~~~~~~~~~~~~~~~~~~~</p>

			<p>{$Message}</p>
			<p style="margin-top:12px">
				<br><a rel="nofollow" href="{$BASE_URL}" target="_blank"><span>{$BASE_URL}</span></a>
			</p>
		</div>
	</div>
</div>

EOF;

	return mailer( $To, 'New contact!', $msg ); // BASE_EMAIL_CONTACT
}
//	mailerFormat($info['email'], '', $msg );

// ###################################################################################
// ### Mail Functions
// ###################################################################################

function mailerFormat( $to, $subject, $msg )
{
	$EmailContent = file_get_contents( BASE_DIR . 'common/tpl.email.php'  );
	$replace = array(
		'{domain}' => BASE_URL,
		'{title}' => $subject,
		'{message}' => $msg,
	);

	$msg = str_replace(array_keys( $replace ), $replace, $EmailContent);
	return mailer( $to, $subject, $msg );
}

function mailer( $to, $subject, $msg )
{
	$headers = '';
	$headers .= "MIME-Version: 1.0\r\n";
	$headers .= "Content-type: text/html; charset=utf-8\r\n";
	$headers .= "Return-Path: \"".BASE_EMAIL_FROM_name."\" <". BASE_EMAIL_FROM .">\r\n";
	$headers .= "Reply-To: \"".BASE_EMAIL_FROM_name."\" <". BASE_EMAIL_FROM .">\r\n";
	$headers .= "From: \"".BASE_EMAIL_FROM_name."\" <". BASE_EMAIL_FROM .">\r\n";
	// $headers .= "From: =?UTF-8?B?". base64_encode(BASE_EMAIL_FROM_name)."?= <". BASE_EMAIL_FROM .">\r\n";
	$headers .= "X-Priority: 1\r\n";
	$headers .= "X-Mailer: PHP/".phpversion()."\r\n";
	$headers .= "Content-Transfer-Encoding: 8bit\r\n";
//	$headers .= "Priority: Urgent\r\n";
//	$headers .= "Importance: high";
	
	//."From: =?UTF-8?B?". base64_encode($from_name) ."?= <$from_address>\r\n"

	return @mail($to, $subject, $msg, $headers);
}