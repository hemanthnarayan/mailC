<?php
	require_once('class.phpmailer.php'); 
	$mail = new PHPMailer();
	$mail->IsSMTP();                 // set mailer to use SMTP
	$mail->Host = "localhost";       // specify main and backup server
	$mail->SMTPAuth = true;          // turn on SMTP authentication
	$mail->Username = "newtester";   // SMTP username
	$mail->Password = 'root@domain'; // SMTP password
     //
    $mail->DKIM_Array = array();

	$mail->From = "dkimiiits@domain.in";
	$mail->FromName = "Mailer";
    $domain1 = 'domain.in';
    $key1="-----BEGIN RSA PRIVATE KEY-----
MIICXAIBAAKBgQDnvtmCU6j8+Wv6On1qYj4rAgAnTL47TsGb62ec1SXCY37pRKAD
p3XSJEtUdDEhzuGaqD2j8eKAPslv/5FcCYp+SEi+Pgsq2O6gr1PsPawleJh9MOuH
m/GO9HJm0m4FKQ3x4iEIgQaaMbZw0rP3TU3boLQ6e+uHzWIWznnFkpJBFQIDAQAB
AoGAKP54qXY1GXLhp+T61HvGdYMoFcuchw86bmNo87Q8trM0+vyZtavEysSC0tCu
9EUNYXdLWBnssDTrGdfdggdfgdfgdgdgdgdZk6IT0YfTjIUBYhilfyCczcj+wHJH
8uNMgsdkZg22xRZ5HrTRv4TStO7YnmeK/y47qDeCPt/+r60CQQDz6EnXZvTo8mxO
JqDeHaKe9+cwUu/odLP7VpWZKJVLZtMdj+wrVul1n0NAUS1qiqA4poSt3VMGualH
O86dFOFzAkEA8zwzZycf4Y5NjnKigQgSMqVd2WxfR//9/op4aHPOWblyX5/wrO/0
R3KBo/QPf6b+g2xkvLvvA1JRrcmayDryrhrwvttvcr2d23rasR23TTERYERHdhFF
8Uyss0yioUB3mxB5Q2iHkmQHDOxtGD6PaU2bC0GxZb+UddU2dj3RNkAOW5MCQE2j
nqjDdUqy+ET34T34+BIQdNds3zEFRlbUbkCxDmHXbumVDOa2pRuVMkpaIgRWEpFy
R34R34+pgT/exTg6mnsCQDfd6R1JH8NWGe6I2FjHGeg16yWFLWvLyyl5OlVH7iNI
VESP513WDG+/FFERGWBTC324F2T343C34+djNXCN4=
-----END RSA PRIVATE KEY-----";
	$selector1 = 'default';
	$mail->DKIM_passphrase = '';

  array_push($mail->DKIM_Array, array('domain'=>$domain1,'selector'=>$selector1,'key'=>$key1));


  $key2="-----BEGIN RSA PRIVATE KEY-----
MIICXAIBAAKBgQDnvtmCU6j8+Wv6On1qYj4rAgAnTL47TsGb62ec1SXCY37pRKAD
p3XSJEtUdDEhzuGaqD2j8eKAPslv/5FcCYp+SEi+Pgsq2O6gr1PsPawleJh9MOuH
m/GO9HJm0m4FKQ3x4iEIgQaaMbZw0rP3TU3boLQ6e+uHzWIWznnFkpJBFQIDAQAB
AoGAKP54qXY1GXLhp+ERTERTERTERVR3245KNGKEVGWJG4YY+vyZtavEysSC0tCu
9EUNYXdLWBnssDTrGzXBN9+0MKZabGkFboZk6IT0YfTjIUBYhilfyCczcj+cwHJH
8uNMgsdkZg22xRZ5HrTRv4TStO7YnmeK/y47qDeCPt/+r60CQQDz6EnXZvTo8mxO
JqDeHaKe9+cwUu/odLP7VpWZKJVLZtMdj+wrVul1n0NAUS1qiqA4poSt3VMGualH
O86dFOFzAkEA8zwzZycf4Y5NjnKigQgSMqVd2WxfR//9/op4aHPOWblyX5/wrO/0
R3KBo/QPf6b+g2xkvLvvA1JRrcmayDsRVwJBAPGVxfXty20kGTR7PGFktxt/7mTF
8Uyss0yioUB3mxB5Q2iHkmQHDOxtGD6PaU2bC0GxZb+UddU2dj3RNkAOW5MCQE2j
nqjDdUqy+JCKiNJ0+BIQdNds3zEFRlbUbkCxDmHXbumVDOa2pRuVMkpaIgRWEpFy
rOkNd+pwgT/exTg6mnsCQDfd6R1JH8NWGe6I2FjHGeg16yWFLWvLyyl5OlVH7iNI
VESP513WDG+/8K45u0gWwdZfvVDpU4LAJkO+djNXCN4=
-----END RSA PRIVATE KEY-----
";
	$selector2='default';
	$domain2="clientdomain.in";
	array_push($mail->DKIM_Array, array('domain'=>$domain2,'selector'=>$selector2,'key'=>$key2));

	$mail->AddAddress("EMAILADDRESS@gmail.com");                  // name is optional
	$mail->AddReplyTo("info@example.com", "Information");

	$mail->WordWrap = 50;                                 // set word wrap to 50 characters
	//$mail->AddAttachment("/var/tmp/file.tar.gz");         // add attachments
	//$mail->AddAttachment("/tmp/image.jpg", "new.jpg");    // optional name
	$mail->IsHTML(true);                                  // set email format to HTML
	$mail->charSet = "UTF-8"; 
	$mail->Subject = "Here is the subjectASCII";
	$mail->Body    = ' <html>
	<body>
	test mail
	</body>
	</html>';

	$mail->AltBody = "This is the body in plain text for non-HTML mail clients";
	$mail->Encoding = "base64";
	$mail->addCustomHeader("X-vinmail-version: 3.2.1");
            #$mail->addCustomHeader("X-vinmail-queue: No");
      $mail->addCustomHeader("X-ListMember:werrrrr ");
    $mail->Encoding = "base64";
    //echo $mail->DKIM_private;
	if(!$mail->Send())
	{
	   echo "Message could not be sent. <p>";
	   echo "Mailer Error: " . $mail->ErrorInfo;
	   exit;
	}

	echo "Message has been sent";

?>
