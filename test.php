<?php
/**
 * Copyright 2016
*/

//////////////////////////////////////
// Custom Email API //
//////////////////////////////////////

	// Display Errors and Output as text/plain
	ini_set('display_errors','on');
  header('Content-type: text/plain; charset=utf-8');
	
	include_once 'emailapi.php';
	include_once 'class.mailDomainSigner.php';

  //smtp connection
  $smtp_server               =   '192.168.1.1';
  $smtp_uname                 =   'username';
  $smtp_pwd                   =   'password';
  $smtp_port                  =   '25';

	$emailapi 					= 	new Email_API();

	$body			              =    '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>
<title>BSLI</title>
</head>

<body>
<table style="border: 1px solid #cccccc;" width="578" height="1281" align="center" border="0" cellpadding="0" cellspacing="0">
<tbody>
<tr>
<td><a href="http://insurance.birlasunlife.com/buy-term-insurance-online/Calculator/CalculatePremium?UID=37&amp;utm_source=Display&amp;utm_medium=Mailers&amp;utm_campaign=Vinam" target="_blank"><img alt="BSLI" src="http://vinam.in/email-images/aug-15/single-image/1.jpg" style="display: block;" title="BSLI" width="578" height="1281" /></a></td>
</tr>
</tbody>
</table>
<div style="font-size: 11px; text-align: center;">If you do not wish to receive such mailers, please <a href="#">click here.</a></div>
</body>
</html>';

    $domain_d                   =   'test.emaila.domain.in';
    $domain_s                   =   'a1'; 
    $domain_priv                =   "-----BEGIN RSA PRIVATE KEY-----
MIICXAIBAAKBgQC3321kFINMowW0GKRnN1Cpwxf5vDv2+fDTyiyswmzWMZKHZ+0U
pr8RWkR3aGehJZYhTFG/kt0WCtoQCttUl0OQBwbPddferrgwegergregwh5h65he
rgtrhew34gq3erthrtkmmhkntrnjsrthkrhrthekrjhnrhtntrhjm2gegdgdgrrg
Cs2out0YJ35UcvecStFhd2PTggergrewgrtlmthno565mbtngnbbno5b6borbtbt
GI49WVe8v9IiDRrsQfxd4O9ZaHfVk1P6hQJARiTdGBGTCnOLSNxDePPoaI82+Mhm
4R+kAvOea9Kk3tslLlWdUvsMNxH9IClUMc7F0qLJGgvzQ2I+DQPLhUIGcQJARstk
HukcRfhRBUEk24cQwBmaokV64rOQAer34Ugmwl10+jZ6ZvtcdJyCj/A7/tfB14m0
L6tUEM7t94UdrVSffQJBAJ5E9ygjedpyONnZNDoys00XtS8sh3Q4QB9/+ZW5q7wy
dxeI3ajA2vMgP+BQSRT9/RO5aE9TI/2s2bopp9pf0tc=
-----END RSA PRIVATE KEY-----";

    $domain_d2                  = 'test.domain.in';
    $domain_s2                  = 'jt'; 
    $domain_priv2               =   "-----BEGIN RSA PRIVATE KEY-----
MIICXAIBAAKBgQCm0+V2U0w4nDE39LB6D2ase0IWn4gZwgrewrewgegewgewrgge
wgregeghikGTeRBMlNNupDfbQIFbaNkP3A/hETVxNmQCcKssY0NRsFvLSLqFeerge
rgrwegLS9pYtVhN+aOJwIM/+ggep6rrO5apz5UEw1zRRVj/NsNYOVdM+jRjKf/imA
7q+UhBJjnZuywKo7vj0AB8rPf0PQ6/QIDAQABgergrregewrhreherherhehegege
AoGAPbTpDjBXZCUZ63mdgTiLIMditZ2CqPY/3G4ZPKhRudGFXq8ibUGVizIP4d6Z
23w5gRarJFd0NfHzAgertgrtrgwergwergwgrgrwegewgegNHsQEYOUs4dbnLDFX4nE2
NSOpt8ckOi40O2Dr421uLIsTmUw4TeQSsIh88E9YdEg=
-----END RSA PRIVATE KEY-----";


    $emailapi->SetSmtp($smtp_server, $smtp_uname, $smtp_pwd, $smtp_port);
    $from                       =  "newsletter@sender_domain.com";
    $emailapi->AddRecipient('testuser@reciver_mail.com', 'reciver mail id');
    $subject                    =   "PHP Mail Domain Signer";
    $emailapi->Set('FromAddress', $from);
    $emailapi->Set('FromName', "Dkim test");
    $emailapi->Set('ReplyTo', $from);
    $emailapi->Set('Subject', $subject);
    $emailapi->Set('MessageId', sha1(microtime(true))."@{$domain_d}");
    $emailapi->Set('Multipart', true);
    $emailapi->Set('ReturnPath', "news-101-24c-jitha@{$domain_d}");
    $emailapi->Set('Sendername', 'news-101-24c-jitha');
    $emailapi->Set('Sender', "news-101-24c-user-name@{$domain_d}");
    $emailapi->Set('extra_headers', array(
        "X-Mailer"=>"X-Mailer: Vinmail Mailer: CIDa042cbf7b4841b034c3d",
        "X-Campaign"=>"X-Campaign: vmailera48bbdab3f4ac10b4678251ae.a042cbf7b4", 
        "X-campaignid"=>"X-campaignid: vmailera48bbdab3f4ac10b4678251ae.a042cbf7b4",
        "X-Report-Abuse"=>"X-Report-Abuse: Please report abuse for this campaign here: http://www.google.com/", 
        "X-MC-User"=>"X-MC-User: a48bbdab3f4ac10b4678251ae",
        "X-Feedback-ID"=>"X-Feedback-ID: 27502947:27502947.541261:us3:mc",
        "List-ID"=>"List-ID: a48bbdab3f4ac10b4678251aemc list <a48bbdab3f4ac10b4678251ae.365669.list-id.mailtest.net>", 
        "X-Accounttype"=>"X-Accounttype: pd",
        "List-Unsubscribe"=>"List-Unsubscribe: <mailto:unsubscribe-mc.us3_a48bbdab3f4ac10b4678251ae.a042cbf7b4-841b034c3d@test.emaila.domain.in?subject=unsubscribe>"
        )
    );

    $emailapi->Set('DKIM_domain', $domain_d);
    $emailapi->Set('DKIM_selector', $domain_s);
    $emailapi->Set('DKIM_private', $domain_priv);
    $emailapi->Set('DDKIM_domain', $domain_d2);
    $emailapi->Set('DDKIM_selector', $domain_s2);
    $emailapi->Set('DDKIM_private', $domain_priv2);

    $emailapi->Set('OrgBody', $body);

    // QP the Body
    $body = quoted_printable_encode($body);
    $emailapi->Set('Body', $body);
	
    $result = $emailapi->send();
    print_r($result);

?>
