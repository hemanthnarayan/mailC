<?php
//////////////////////////////////////
// Custom Email API //
//////////////////////////////////////
include_once 'html2text.php';
class Email_API
{

	/**
	* An array containing the text and html versions of the body. This is before any replacements are made so we don't have to keep fetching the same content from the database before sending it through.
	*
	* If either is null, it is not included in the outgoing email.
	*
	* @see AddBody
	* @see AppendBody
	* @see JoinBody
	* @see _SetupHeaders
	* @see _SetupBody
	* @see ForgetEmail
	*
	* @var Array
	*/
	var $OrgBody = '';

	/**
	* _AttachmentBody
	* This holds the attachments in 'memory' so it only has to load them up once if multiple emails have to be sent.
	*
	* @var String $_AttachmentBody
	*
	* @see _SetupBody
	* @see _SetupAttachments
	*/
	var $_AttachmentBody = '';

	/**
	* _ImageBody
	* This holds the images for embedding in 'memory' so it only has to load them up once if multiple emails have to be sent.
	*
	* @var String $_ImageBody
	*
	* @see _SetupBody
	* @see _SetupImages
	*/
	var $_ImageBody = null;

	/**
	* _AssembledEmail
	* The email all put together ready for sending.
	*
	* @see _SetupBody
	* @see _SetupHeaders
	*/
	var $_AssembledEmail = array(
			'Headers' => array(
				't' => null,
				'h' => null,
				'm' => null
			),
			'Body' => array(
				't' => null,
				'h' => null,
				'm' => null
			)
		);

	/**
	* An array containing attachment information. This is used to temporarily store paths and names for the attachments we're going to add.
	*
	* If it's empty, nothing is added to the outgoing email.
	*
	* @see _SetupHeaders
	* @see _SetupBody
	* @see ForgetEmail
	* @see _SetupAttachments
	* @see Send
	*
	* @var Array
	*/
	var $_Attachments = array();

	/**
	* The newline character to use between headers, boundaries and so on.
	*
	* @var String
	*/
	var $_newline = "\n";

	/**
	* Boundary between parts. Used with multipart emails and also if there are any attachments.
	*
	* @see _SetupBody
	*
	* @var String
	*/
	var $_Boundaries = array();

	/**
	* Whether to embed images in the email or not. This is off by default.
	* This is used to work out boundaries and whether we need to fetch images that need embedding.
	*
	* @var Boolean
	*/
	var $EmbedImages = false;

	/**
	* Images to embed in the content.
	*
	* @see GetImages
	*
	* @var Array
	*/
	var $_EmbeddedImages = array();

	/**
	* The base href found in the email body.
	*
	* @see _GetBaseHref
	* @var String
	*/
	var $_basehref = null;

	/**
	* The temporary location for storing images that need embedding in an email.
	*
	* @see CleanupImages
	* @see _SaveImages
	*
	* @var String
	*/
	var $imagedir = null;

	/**
	* The From email address.
	*
	* @var String
	*/
	var $FromAddress = '';

	/**
	* The To email address.
	*
	* @var String
	*/
	var $ToAddress = '';

	/**
	* The From name.
	*
	* @var String
	*/
	var $FromName = '';

	/**
	* The bounce email address (used if safe-mode is off).
	*
	* @var String
	*/
	var $BounceAddress = '';

	/**
	* The ReturnPath
	*
	* @var String
	*/
	var $ReturnPath = '';

	/**
	* The Sender
	*
	* @var String
	*/
	var $Sender = '';

	/**
	* The Sendername
	*
	* @var String
	*/
	var $Sendername = '';

	/**
	* The reply-to email address.
	*
	* @var String
	*/
	var $ReplyTo = '';

	/**
	* The subject of the email.
	*
	* @var String
	*/
	var $Subject = '';

	/**
	* The character set of the email.
	*
	* @var String
	*/
	var $CharSet = 'utf-8';

	/**
	* The content encoding of the email.
	*
	* @var String
	*/
	var $ContentEncoding = 'quoted-printable';

	/**
	* SMTP Server Information. The server name to connect to.
	*
	* @see SetSmtp
	* @see _Send_SMTP
	* @see _Put_Smtp_Connection
	* @see _get_response
	* @see _Get_Smtp_Connection
	* @see _Close_Smtp_Connection
	*
	* @var String
	*/
	var $SMTPServer = false;

	/**
	* SMTP Server Information. The smtp username used for authentication.
	*
	* @see SetSmtp
	* @see _Send_SMTP
	* @see _Put_Smtp_Connection
	* @see _get_response
	* @see _Get_Smtp_Connection
	* @see _Close_Smtp_Connection
	*
	* @var String
	*/
	var $SMTPUsername = false;

	/**
	* SMTP Server Information. The smtp password used for authentication.
	*
	* @see SetSmtp
	* @see _Send_SMTP
	* @see _Put_Smtp_Connection
	* @see _get_response
	* @see _Get_Smtp_Connection
	* @see _Close_Smtp_Connection
	*
	* @var String
	*/
	var $SMTPPassword = false;

	/**
	* SMTP Server Information. The smtp port number.
	*
	* @see SetSmtp
	* @see _Send_SMTP
	* @see _Put_Smtp_Connection
	* @see _get_response
	* @see _Get_Smtp_Connection
	* @see _Close_Smtp_Connection
	*
	* @var Int
	*/
	var $SMTPPort = 25;

	/**
	* Whether to use SMTP Pipelining or not. Pipelining is described in RFC 2920.
	*
	* @see _Get_Smtp_Connection
	* @see _Send_SMTP
	*
	* @var Boolean
	*/
	var $_SMTPPipeline = false;

	/**
	* An array of recipients to send the email to. You go through this one by one and send emails individually.
	*
	* @var Array
	*/
	var $_Recipients = array();

	/**
	* Sendmail parameters is used to temporarily store the sendmail-from information.
	* Should only be set up once per sending session.
	*
	* @see _Send_Email
	*
	* @var String
	*/
	var $_sendmailparameters = null;

	/**
	* SMTP connection to see if we are connected to the smtp server. By default this is null.
	* When you reach _smtp_max_email_count it will drop the connection and re-establish it.
	*
	* @see _Send_SMTP
	* @see _Put_Smtp_Connection
	* @see _get_response
	* @see _Get_Smtp_Connection
	* @see _Close_Smtp_Connection
	* @see _smtp_max_email_count
	*
	* @var String
	*/
	var $_smtp_connection = null;

	/**
	* Max number of emails to send per smtp connection.
	*
	* @see _Send_SMTP
	* @see _Put_Smtp_Connection
	* @see _get_response
	* @see _Get_Smtp_Connection
	* @see _Close_Smtp_Connection
	*
	* @var Int
	*/
	var $_smtp_max_email_count = 50;

	/**
	* Number of emails that have been sent with this particular smtp connection. Gets reset after a set number of emails.
	*
	* @see _smtp_max_email_count
	* @see _Send_SMTP
	* @see _Put_Smtp_Connection
	* @see _get_response
	* @see _Get_Smtp_Connection
	* @see _Close_Smtp_Connection
	*
	* @var Int
	*/
	var $_smtp_email_count = 0;

	/**
	* Newline characters for the smtp servers to use.
	*
	* @see _smtp_max_email_count
	* @see _Send_SMTP
	* @see _Put_Smtp_Connection
	* @see _get_response
	* @see _Get_Smtp_Connection
	* @see _Close_Smtp_Connection
	*
	* @var String
	*/
	var $_smtp_newline = "\r\n";

	/**
	* Debug
	*
	* Whether to log decisions about how the email is put together and how the email is sent.
	*
	* @see LogFile
	*
	* @var Boolean
	*/
	var $Debug = false;

	/**
	* TestMode
	*
	* If testmode is enabled, everything works as per normal but no actual emails are sent out.
	* It is the same as commenting out the mail() call.
	* Using testmode also bypasses smtp-settings.
	*
	* This is mainly used by sendstudio for testing purposes.
	*
	* @see _Send_Email
	*
	* @var Boolean
	*/
	var $TestMode = false;

	/**
	* Whether this email is multipart or not.
	*
	* @var Boolean
	*/
	var $Multipart = false;

	/**
	* safe_mode
	*
	* Stores whether safe-mode is on for the server or not.
	*
	* @see Email_API
	* @see Send
	* @see CleanupImages
	*
	* @var Boolean
	*/
	var $safe_mode = false;

	/**
	* use_curl
	* Whether curl functions are available or not.
	*
	* @see GetImage
	* @see Email_API
	*/
	var $use_curl = false;

	/**
	* allow_fopen
	* Whether allow_url_fopen is on or not.
	*
	* @see GetImage
	* @see Email_API
	*/
	var $allow_fopen = false;

	/**
	* wrap_length
	* The number of characters to wrap the emails at.
	* RFC 2822 says lines can be longer than 72 characters but no more than 988 characters (under "2.1.1. Line Length Limits").
	*
	* @var Int
	*
	* @see _SetupBody
	* @see http://www.faqs.org/rfcs/rfc2822.html
	*/
	var $wrap_length = 75;

	/**
	* extra_headers
	* In case we need any extra email headers.
	* These are without any newlines.
	*
	* @var Array
	*
	* @see _SetupHeaders
	*/
	var $extra_headers=array();

	/**
	* message_id_server
	* The server the message is coming from.
	* This defaults to 'localhost.localdomain', but should be overwritten where possible.
	*
	* @var String
	*
	* @see _SetupHeaders
	*/
	var $message_id_server = 'localhost.localdomain';

	/**
	* memory_limit
	* Whether the server has 'memory_get_usage' available or not.
	* This is only used for debugging.
	*/
	var $memory_limit = false;

	/**
	 * ServerRootDirectory
	 * The server root directory
	 */
	var $ServerRootDirectory = '';

	/**
	 * ServerURL
	 * The server url
	 */
	var $ServerURL = '';

	/**
	 * Holds error description from a failed sending
	 * @var String
	 */
	var $Error = '';

	/**
	 * Holds error code from a failed sending
	 * @var String
	 */
	var $ErrorCode = false;

	/**
	 * New "enhanced" SMTP status code were defined in RFC5248
	 * @var String
	 */
	var $ErrorCodeSMTPEnhanced = false;

	/**
	 * MessageId
	 * 
	 */
	var $MessageId = '';

	/**
	 * Encoding
	 * 
	 */
	var $Encoding = '';

	/**
     * DKIM selector.
     * @type string
     */
    public $DKIM_selector = '';

    /**
     * DKIM signing domain name.
     * @example 'example.com'
     * @type string
     */
    public $DKIM_domain = '';

    /**
     * DKIM private key file path.
     * @type string
     */
    public $DKIM_private = '';

    /**
     * DKIM2 selector.
     * @type string
     */
    public $DDKIM_selector = '';

    /**
     * DKIM2 signing domain name.
     * @example 'example.com'
     * @type string
     */
    public $DDKIM_domain = '';

    /**
     * DKIM2 private key file path.
     * @type string
     */
    public $DDKIM_private = '';

    public $Body = '';


    /**
	* Set
	* This sets the class var to the value passed in.
	* If the variable doesn't exist in the object, this will return false.
	*
	* @param String $varname Name of the class var to set.
	* @param Mixed $value The value to set the class var (this can be an array, string, int, float, object).
	*
	* @return Boolean True if it works, false if the var isn't present.
	*/
	function Set($varname='', $value='')
	{
		if ($varname == '') {
			return false;
		}

		// make sure we're setting a valid variable.
		$my_vars = array_keys(get_object_vars($this));
		if (!in_array($varname, $my_vars)) {
			return false;
		}

		$this->$varname = $value;
		return true;
	}

	/**
	* _Send_SMTP
	* Send an email through an smtp server instead of through the php mail function.
	* This handles all of the commands that need to be sent and return code checking for each part of the process.
	*
	* @param String $rcpt_to The 'receipt to' address to send the email to. This is a bare email address only.
	* @param String $to The 'to' address to send this to. This can contain a name / email address in the standard format ("Name" <email@address>)
	* @param String $subject The subject of the email to send.
	* @param String $body The body of the email to send.
	* @param String $headers The headers of the email to send.
	*
	* @see _Get_Smtp_Connection
	* @see _Put_Smtp_Connection
	* @see _Close_Smtp_Connection
	* @see ErrorCode
	* @see Error
	* @see _get_response
	* @see _smtp_email_count
	*
	* @return Array Returns an array including whether the email was sent and a possible error message (for logging).
	*/
	function _Send_SMTP(&$rcpt_to, &$to, &$subject, &$body, &$headers)
	{
		$connection = $this->_Get_Smtp_Connection();

		// echo     'Connection is ' . gettype($connection)."<br>";


		if (!$connection) {
			// echo     'No connection'."<br>";
			return array(false, $this->Error);
		}

		if ($this->_SMTPPipeline) {
			$cmds = array();
			$cmds[] = "MAIL FROM:<" . $this->ReturnPath . ">";
			$cmds[] = "RCPT TO:<" . $rcpt_to . ">";
			$data = implode($cmds, $this->_smtp_newline);
			if (!$this->_Put_Smtp_Connection($data)) {
				$this->ErrorCode = 5;
				$this->ErrorCodeSMTPEnhanced = false;
				$this->Error = 'Unable to send multiple commands in pipeline mode';
				$this->_Close_Smtp_Connection();
				return array(false, $this->Error);
			}
			$response_count = sizeof($cmds);
			for ($response_check = 1; $response_check <= $response_count; $response_check++) {
				$response = $this->_get_response();
				$responsecode = substr($response, 0, 3);
				if ($responsecode != '250') {
					$this->ErrorCodeSMTPEnhanced = $this->_GetSMTPEnhancedErrorCode($response);
					$this->ErrorCode = $responsecode;
					$this->Error = $response;
					$this->_Close_Smtp_Connection();

					// echo     'Got error ' . $this->Error."<br>";
					return array(false, $this->Error);
				}
			}
			return $this->_Send_SmtpData($rcpt_to, $to, $subject, $body, $headers);
		}

		$data = "MAIL FROM:<" . $this->ReturnPath . ">";

		// echo     'Trying to put ' . $data."<br>";

		if (!$this->_Put_Smtp_Connection($data)) {
			$this->ErrorCode = 10;
			$this->ErrorCodeSMTPEnhanced = false;
			//$this->Error = GetLang('UnableToSendEmail_MailFrom');
			$this->_Close_Smtp_Connection();

			// echo    'Got error ' . $this->Error."<br>";

			return array(false, $this->Error);
		}

		$response = $this->_get_response();

		// echo    'Got response ' . $response."<br>";

		$responsecode = substr($response, 0, 3);
		if ($responsecode != '250') {
			$this->ErrorCodeSMTPEnhanced = $this->_GetSMTPEnhancedErrorCode($response);
			$this->ErrorCode = $responsecode;
			$this->Error = $response;
			$this->_Close_Smtp_Connection();

			// echo    'Got error ' . $this->Error."<br>";

			return array(false, $this->Error);
		}

		$data = "RCPT TO:<" . $rcpt_to . ">";

		// echo    'Trying to put ' . $data."<br>";

		if (!$this->_Put_Smtp_Connection($data)) {
			$this->ErrorCode = 11;
			$this->ErrorCodeSMTPEnhanced = false;
			//$this->Error = GetLang('UnableToSendEmail_RcptTo');
			$this->_Close_Smtp_Connection();

			// echo    'Got error ' . $this->Error."<br>";

			return array(false, $this->Error);
		}

		$response = $this->_get_response();

		// echo    'Got response ' . $response."<br>";

		$responsecode = substr($response, 0, 3);

		if ($responsecode != '250') {
			$this->ErrorCodeSMTPEnhanced = $this->_GetSMTPEnhancedErrorCode($response);
			$this->ErrorCode = $responsecode;
			$this->Error = $response;
			$this->_Close_Smtp_Connection();

			// echo    'Got error ' . $this->Error."<br>";

			return array(false, $this->Error);
		}

		return $this->_Send_SmtpData($rcpt_to, $to, $subject, $body, $headers);
	}

	/**
	* _Send_SmtpData
	* Handles the SMTP negotiation for sending the email header and body.
	*
	* @param String $rcpt_to The 'receipt to' address to send the email to. This is a bare email address only.
	* @param String $to The 'to' address to send this to. This can contain a name / email address in the standard format ("Name" <email@address>)
	* @param String $subject The subject of the email to send.
	* @param String $body The body of the email to send.
	* @param String $headers The headers of the email to send.
	*
	* @see _Send_SMTP
	*
	* @return Array Returns an array including whether the email was sent and a possible error message (for logging).
	*/
	function _Send_SmtpData(&$rcpt_to, &$to, &$subject, &$body, &$headers)
	{

		$data = "DATA";

		// echo    'Trying to put ' . $data."<br>";

		if (!$this->_Put_Smtp_Connection($data)) {
			$this->ErrorCode = 12;
			$this->ErrorCodeSMTPEnhanced = false;
			//$this->Error = GetLang('UnableToSendEmail_Data');
			$this->_Close_Smtp_Connection();

			// echo    'Got error ' . $this->Error."<br>";

			return array(false, $this->Error);
		}

		$response = $this->_get_response();

		// echo    'Got response ' . $response."<br>";

		$responsecode = substr($response, 0, 3);

		if ($responsecode != '354') {
			$this->ErrorCode = $responsecode;
			$this->ErrorCodeSMTPEnhanced = $this->_GetSMTPEnhancedErrorCode($response);
			$this->Error = $response;
			$this->_Close_Smtp_Connection();

			// echo    'Got error ' . $this->Error."<br>";

			return array(false, $this->Error);
		}

		$msg = $headers . preg_replace_callback('/^\.(\r|\n)/m', function(){
				$body 					=	 ' .${1}';
			}, $body);

		//$msg = $headers . preg_replace_callback('/^\.(\r|\n)/m', ' .${1}', $body);
		//."To: " . $to . $this->_smtp_newline . "Subject: " . '=?utf-8?B?'.$subject.'?=' . $this->_smtp_newline 
		//"To: " . $to . $this->_smtp_newline . "Subject: " . $subject . $this->_smtp_newline . 

		$msg = str_replace("\r\n","\n",$msg);
		$msg = str_replace("\r","\n",$msg);
		$lines = explode("\n",$msg);
		foreach ($lines as $no => $line) {// echo    "--------------";// echo    $line."<br>";
			// we need to rtrim here so we don't get rid of tabs before the start of the line.
			// the tab is extremely important for boundaries (eg sending multipart + attachment)
			// so it needs to stay.
			$data = rtrim($line);

			// echo    'Trying to put ' . $data."<br>";

			if (!$this->_Put_Smtp_Connection($data)) {
				$this->ErrorCode = 13;
				$this->ErrorCodeSMTPEnhanced = false;
				//$this->Error = GetLang('UnableToSendEmail_DataWriting');
				$this->_Close_Smtp_Connection();

				// echo    'Got error ' . $this->Error."<br>";

				return array(false, $this->Error);
			}
		}

		$data = $this->_smtp_newline . ".";

		// echo    'Trying to put ' . $data."<br>";

		if (!$this->_Put_Smtp_Connection($data)) {
			$this->ErrorCode = 14;
			$this->ErrorCodeSMTPEnhanced = false;
			//$this->Error = GetLang('UnableToSendEmail_DataFinished');
			$this->_Close_Smtp_Connection();

			// echo     'Got error ' . $this->Error."<br>";

			return array(false, $this->Error);
		}

		$response = $this->_get_response();

		// echo     'Got response ' . $response."<br>";

		$responsecode = substr($response, 0, 3);
		if ($responsecode != '250') {
			$this->ErrorCodeSMTPEnhanced = $this->_GetSMTPEnhancedErrorCode($response);
			$this->ErrorCode = $responsecode;
			$this->Error = $response;
			$this->_Close_Smtp_Connection();

			// echo     'Got error ' . $this->Error."<br>";

			return array(false, $this->Error);
		}

		// echo     'Mail accepted '."<br>";

		/**
		 * We got this far, this means we didn't encounter any errors.
		 * Cleanup previous error codes and variables since they are no longer relevant
		 * with the current process iteration.
		 */
		$this->Error = '';
		$this->ErrorCode = false;
		$this->ErrorCodeSMTPEnhanced = false;

		$this->_smtp_email_count++;
		return array(true, false);
	}

	/**
	 * Return "enhanced" SMTP error code
	 *
	 * This method will only return an error code.
	 * It does not attempt to categorized the error code.
	 *
	 * It is the responsibility of the called to make use of this new
	 * "enhanced" error code.
	 *
	 * NOTE: The enhanced error code is defined in RFC5248
	 *
	 * @param String $response SMTP Response
	 * @return Mixed Returns error code string in this format x.x.x if found, FALSE otherwise
	 */
	function _GetSMTPEnhancedErrorCode($response)
	{
		if (!preg_match('/^\d{3} (\d+\.\d+\.\d+)/', $response, $matches)) {
			return false;
		}

		if (!isset($matches[1])) {
			return false;
		}

		return $matches[1];
	}

	/**
	* SetSmtp
	* Sets smtp server information
	* If the servername is set to false, then this will "forget" the current smtp information by setting the class variables back to their defaults.
	*
	* @param String $servername SMTP servername to use to send emails through
	* @param String $username SMTP username to authenticate with when sending through the smtp server
	* @param String $password SMTP password to authenticate with when sending through the smtp server
	* @param Int $port The SMTP port number to use when sending
	*
	* @see SMTPServer
	* @see SMTPUsername
	* @see SMTPPassword
	* @see SMTPPort
	*
	* @return True Always returns true.
	*/
	function SetSmtp($servername=false, $username=false, $password=false, $port=25)
	{
		if (!$servername) {
			$this->SMTPServer = false;
			$this->SMTPUsername = false;
			$this->SMTPPassword = false;
			$this->SMTPPort = 25;
			return true;
		}

		$this->SMTPServer = $servername;
		$this->SMTPUsername = $username;
		$this->SMTPPassword = $password;
		$this->SMTPPort = (int)$port;
		return true;
	}

	/**
	* _Put_Smtp_Connection
	* This puts data through the smtp connection.
	* If a valid connection isn't passed in, the _smtp_connection is used instead.
	*
	* @param String $data The data to put through the connection. A newline is automatically added here, there is no need to do it before calling this function.
	* @param Resource $connection The connection to send the data through. If not specified, the resource _smtp_connection is used instead.
	*
	* @see _smtp_newline
	* @see _smtp_connection
	*
	* @return Mixed Returns whether the 'fputs' works to the connection resource.
	*/
	function _Put_Smtp_Connection($data='', $connection=null)
	{
		$data .= $this->_smtp_newline;
		if (is_null($connection)) {
			$connection = $this->_smtp_connection;
		}

		return fputs($connection, $data, strlen($data));
	}

	/**
	* SMTP_Logout
	* A wrapper for the _Close_Smtp_Connection function
	*
	* @see _Close_Smtp_Connection
	*
	* @return Void Doesn't return anything.
	*/
	function SMTP_Logout()
	{
		$this->_Close_Smtp_Connection();
	}

	/**
	* _Get_Smtp_Connection
	* This fetches the smtp connection stored in _smtp_connection
	* If that isn't valid, this will attempt to set it up and authenticate (if necessary).
	* If the number of emails sent through the connection has reached the maximum (most smtp servers will only let you send a certain number of emails per connection), the connection will be reset.
	* If the connection is not available or has been reset, this will then attempt to re-set up the connection socket.
	*
	* @see _smtp_connection
	* @see _smtp_email_count
	* @see _smtp_max_email_count
	* @see SMTPServer
	* @see SMTPUsername
	* @see SMTPPassword
	* @see SMTPPort
	* @see ErrorCode
	* @see Error
	* @see _Put_Smtp_Connection
	* @see _get_response
	*
	* @return False|Resource If the connection in _smtp_connection is valid, this will return that connection straight away. If it's not valid it will try to re-establish the connection. If it can't be done, this will return false. If it can be done, the connection will be stored in _smtp_connection and returned.
	*/
	function _Get_Smtp_Connection()
	{
		if ($this->_smtp_email_count > $this->_smtp_max_email_count) {
			$this->_Close_Smtp_Connection();
			$this->_smtp_email_count = 0;
		}

		if (!is_null($this->_smtp_connection)) {
			return $this->_smtp_connection;
		}

		$server = $this->SMTPServer;
		$username = $this->SMTPUsername;
		$password = $this->SMTPPassword;
		$port = (int)$this->SMTPPort;

		if ($port <= 0) {
			$port = 25;
		}

		// echo     'smtp details: server: ' . $server . '; username: ' . $username . '; password: ' . $password . '; port: ' . $port;

		$timeout = 10;

		$socket = @fsockopen($server, $port, $errno, $errstr, $timeout);
		if (!$socket) {
			$this->ErrorCode = 1;
			// echo     'Unable To Connect To Email Server';
			return false;
		}

		$response = $this->_get_response($socket);

		// echo     'Got response ' . $response."<br>";

		$responsecode = substr($response, 0, 3);

		if ($responsecode != '220') {
			$this->ErrorCode = $responsecode;
			$this->Error = $response;
			fclose($socket);
			return false;
		}

		// say hi!
		//$data = 'EHLO ' . $this->message_id_server;
		$data = 'EHLO ' . $this->serverHostname();
		// echo     'Trying to put ' . $data;

		if (!$this->_Put_Smtp_Connection($data, $socket)) {
			$this->ErrorCode = 2;
			//$this->Error = GetLang('UnableToConnectToMailServer_EHLO');
			fclose($socket);

			// echo     'Got error ' . $this->Error."<br>";

			return false;
		}

		$response = $this->_get_response($socket);

		// echo     'Got response ' . $response . "<br>";

		$responses = explode($this->_smtp_newline, $response);
		$response = array_shift($responses);

		$responsecode = substr($response, 0, 3);
		if ($responsecode == '501') {
			// echo     'Got responsecode ' . $responsecode."<br>";
			$this->ErrorCode = 7;
			//$this->Error = GetLang('UnableToConnectToMailServer_EHLO');
			return false;
		}

		$this->_SMTPPipeline = false;

		// before we check for authentication, put the first response at the start of the stack.
		// just in case the first line is 250-auth login or something
		// if we didn't do this i'm sure it would happen ;)
		array_unshift($responses, $response);

		$requireauth = false;

		foreach ($responses as $line) {
			// echo     'checking line ' . $line . "<br>";

			if (preg_match('%250[\s|-]auth(.*?)login%i', $line)) {
				$requireauth = true;
			}

			if (preg_match('%250[\s-]pipelining%i', $line)) {
				$this->_SMTPPipeline = true;
			}
		}

		if ($this->Debug) {
			echo 'Line ' . __LINE__ . '; time ' . time() . '; require authentication: ' . (int)$requireauth . "\n";
			echo 'Line ' . __LINE__ . '; time ' . time() . '; server supports pipelining: ' . (int)$this->_SMTPPipeline . "\n";
			if ($this->memory_limit) {
				echo basename(__FILE__) . "\t" . __LINE__ . "\t" . __FUNCTION__ . "\t" . number_format((memory_get_usage()/1024), 5) . "\n";
			}
		}

		if ($requireauth && $username) {
			if (!$password) {
				$this->ErrorCode = 3;
				//$this->Error = GetLang('UnableToConnectToMailServer_RequiresAuthentication');
				fclose($socket);

				// echo     'Got error ' . $this->Error."<br>";

				return false;
			}
			$data = "AUTH LOGIN";

			// echo     'Trying to put ' . $data."<br>";

			if (!$this->_Put_Smtp_Connection($data, $socket)) {
				$this->ErrorCode = 4;
				//$this->Error = GetLang('UnableToConnectToMailServer_AuthLogin');
				fclose($socket);

				// echo     'Got error ' . $this->Error."<br>";

				return false;
			}

			$response = $this->_get_response($socket);

			// echo     'Got response ' . $response."<br>";

			$responsecode = substr($response, 0, 3);
			if ($responsecode != '334') {
				$this->ErrorCode = 5;
				//$this->Error = GetLang('UnableToConnectToMailServer_AuthLoginNotSupported');
				fclose($socket);

				// echo     'Got error ' . $this->Error."<br>";

				return false;
			}

			$data = base64_encode(rawurldecode($username));

			// echo     'Trying to put ' . $data."<br>";

			if (!$this->_Put_Smtp_Connection($data, $socket)) {
				$this->ErrorCode = 6;
				//$this->Error = GetLang('UnableToConnectToMailServer_UsernameNotWritten');
				fclose($socket);

				// echo     'Got error ' . $this->Error."<br>";

				return false;
			}

			$response = $this->_get_response($socket);

			// echo     'Got response ' . $response."<br>";

			$responsecode = substr($response, 0, 3);
			if ($responsecode != '334') {
				$this->ErrorCode = $responsecode;
				$this->Error = $response;
				fclose($socket);

				// echo     'Got error ' . $this->Error."<br>";

				return false;
			}

			$data = base64_encode($password);

			// echo     'Trying to put ' . $data."<br>";

			if (!$this->_Put_Smtp_Connection($data, $socket)) {
				$this->ErrorCode = 7;
				//$this->Error = GetLang('UnableToConnectToMailServer_PasswordNotWritten');
				fclose($socket);

				// echo     'Got error ' . $this->Error."<br>";

				return false;
			}

			$response = $this->_get_response($socket);

			// echo     'Got response ' . $response."<br>";

			$responsecode = substr($response, 0, 3);
			if ($responsecode != '235') {
				$this->ErrorCode = $responsecode;
				$this->Error = 'Login failed, please check the username and password and try again.';
				fclose($socket);

				// echo     'Got error ' . $this->Error."<br>";

				return false;
			}
		}

		$this->_smtp_connection = $socket;
		return $this->_smtp_connection;
	}

	/**
	* _Close_Smtp_Connection
	* Closes the smtp connection by issuing a 'QUIT' command and then forgets the smtp server connection.
	* If the smtp connection isn't valid, this will return straight away.
	*
	* @see _smtp_connection
	* @see _Put_Smtp_Connection
	*
	* @return Void Doesn't return anything.
	*/
	function _Close_Smtp_Connection()
	{
		if (is_null($this->_smtp_connection)) {
			return;
		}

		$this->_Put_Smtp_Connection('QUIT');
		fclose($this->_smtp_connection);
		$this->_smtp_connection = null;
	}

	/**
	* _get_response
	* Gets the response from the last message sent to the smtp server.
	* This is only used by smtp sending. If the connection passed in is not valid, this will return nothing.
	*
	* @param Resource $connection The smtp server connection we're trying to fetch information from. If this is not passed in, we check the _smtp_connection to see if that's available.
	*
	* @see _smtp_connection
	*
	* @return String Returns the response from the smtp server.
	*/
	function _get_response($connection=null)
	{
		if (is_null($connection)) {
			$connection 			= 	$this->_smtp_connection;
		}

		if (is_null($connection)) {
			return;
		}

		$data = "";
		while ($str = fgets($connection,515)) {
			$data .= $str;
			# if the 4th character is a space then we are done reading
			# so just break the loop
			if (substr($str,3,1) == " " || $str == "") {
				break;
			}
		}
		return trim($data);
	}

	function send()
	{
		foreach ($this->_Recipients as $p => $details) {
			$headers 				=	$this->_setupHeaders($details['format'], $details['address'],$details['name']);
			$body 					=	$this->_setupBody();
			$rcpt_to 				= 	$details['address'];
			$to 					= 	$details['address'];
			if ($details['name']) {
				$to 				= 	'"=?utf-8?Q?' . $details['name'] . '?=" <' . $to . '>';
			}
			$this->ToAddress 		=	$rcpt_to;
			if (!$this->TestMode) {
				if ($this->SMTPServer) {
					$result[] =  $this->_Send_SMTP($to, $rcpt_to,$this->Subject,$body,$headers);
				}
			}
		}
		return $result;
	}

	/**
	* Utf 8 encode
	*/
	function email_escape($text)
	{
		if (preg_match('/[^a-z ]/i', $text)) {
			preg_replace_callback('/([^a-z ])/i', function(){
				$text 				=	sprintf("=%02x",ord(StripSlashes("\\1")));
			}, $text);
		}
		$text 						= 	str_replace (" ", "_", $text);
		return "=?utf-8?Q?$text?=";
	}

	/**
     * Ensure consistent line endings in a string.
     * Changes every end of line from CRLF, CR or LF to $this->LE.
     * @access public
     * @param string $str String to fixEOL
     * @return string
     */
    public function fixEOL($str)
    {
        // Normalise to \n
        $nstr = str_replace(array("\r\n", "\r"), "\n", $str);
        // Now convert LE as needed
        if ($this->_newline !== "\n") {
            $nstr = str_replace("\n", $this->_newline, $nstr);
        }
        return $nstr;
    }

    /**
     * Find the last character boundary prior to $maxLength in a utf-8
     * quoted (printable) encoded string.
     * Original written by Colin Brown.
     * @access public
     * @param string $encodedText utf-8 QP text
     * @param integer $maxLength   find last character boundary prior to this length
     * @return integer
     */
    public function utf8CharBoundary($encodedText, $maxLength)
    {
        $foundSplitPos = false;
        $lookBack = 3;
        while (!$foundSplitPos) {
            $lastChunk = substr($encodedText, $maxLength - $lookBack, $lookBack);
            $encodedCharPos = strpos($lastChunk, '=');
            if ($encodedCharPos !== false) {
                // Found start of encoded character byte within $lookBack block.
                // Check the encoded byte value (the 2 chars after the '=')
                $hex = substr($encodedText, $maxLength - $lookBack + $encodedCharPos + 1, 2);
                $dec = hexdec($hex);
                if ($dec < 128) { // Single byte character.
                    // If the encoded char was found at pos 0, it will fit
                    // otherwise reduce maxLength to start of the encoded char
                    $maxLength = ($encodedCharPos == 0) ? $maxLength :
                        $maxLength - ($lookBack - $encodedCharPos);
                    $foundSplitPos = true;
                } elseif ($dec >= 192) { // First byte of a multi byte character
                    // Reduce maxLength to split at start of character
                    $maxLength = $maxLength - ($lookBack - $encodedCharPos);
                    $foundSplitPos = true;
                } elseif ($dec < 192) { // Middle byte of a multi byte character, look further back
                    $lookBack += 3;
                }
            } else {
                // No encoded character found
                $foundSplitPos = true;
            }
        }
        return $maxLength;
    }

	/**
     * Word-wrap message.
     * For use with mailers that do not automatically perform wrapping
     * and for quoted-printable encoded messages.
     * Original written by philippe.
     * @param string $message The message to wrap
     * @param integer $length The line length to wrap to
     * @param boolean $qp_mode Whether to run in Quoted-Printable mode
     * @access public
     * @return string
     */
    public function wrapText($message, $length, $qp_mode = false)
    {
        $soft_break = ($qp_mode) ? sprintf(' =%s', $this->_newline) : $this->_newline;
        // If utf-8 encoding is used, we will need to make sure we don't
        // split multibyte characters when we wrap
        $is_utf8 = (strtolower($this->CharSet) == 'utf-8');
        $lelen = strlen($this->_newline);
        $crlflen = strlen("\r\n");

        $message = $this->fixEOL($message);
        if (substr($message, -$lelen) == $this->_newline) {
            $message = substr($message, 0, -$lelen);
        }

        $line = explode($this->_newline, $message); // Magic. We know fixEOL uses $LE
        $message = '';
        for ($i = 0; $i < count($line); $i++) {
            $line_part = explode(' ', $line[$i]);
            $buf = '';
            for ($e = 0; $e < count($line_part); $e++) {
                $word = $line_part[$e];
                if ($qp_mode and (strlen($word) > $length)) {
                    $space_left = $length - strlen($buf) - $crlflen;
                    if ($e != 0) {
                        if ($space_left > 20) {
                            $len = $space_left;
                            if ($is_utf8) {
                                $len = $this->utf8CharBoundary($word, $len);
                            } elseif (substr($word, $len - 1, 1) == '=') {
                                $len--;
                            } elseif (substr($word, $len - 2, 1) == '=') {
                                $len -= 2;
                            }
                            $part = substr($word, 0, $len);
                            $word = substr($word, $len);
                            $buf .= ' ' . $part;
                            $message .= $buf . sprintf('=%s', "\r\n");
                        } else {
                            $message .= $buf . $soft_break;
                        }
                        $buf = '';
                    }
                    while (strlen($word) > 0) {
                        if ($length <= 0) {
                            break;
                        }
                        $len = $length;
                        if ($is_utf8) {
                            $len = $this->utf8CharBoundary($word, $len);
                        } elseif (substr($word, $len - 1, 1) == '=') {
                            $len--;
                        } elseif (substr($word, $len - 2, 1) == '=') {
                            $len -= 2;
                        }
                        $part = substr($word, 0, $len);
                        $word = substr($word, $len);

                        if (strlen($word) > 0) {
                            $message .= $part . sprintf('=%s', "\r\n");
                        } else {
                            $buf = $part;
                        }
                    }
                } else {
                    $buf_o = $buf;
                    $buf .= ($e == 0) ? $word : (' ' . $word);

                    if (strlen($buf) > $length and $buf_o != '') {
                        $message .= $buf_o . $soft_break;
                        $buf = $word;
                    }
                }
            }
            $message .= $buf . "\r\n";
        }

        return $message;
    }

    /**
     * Encode a string in quoted-printable format.
     */
	function encodeQP($string, $qp = true)
	{
		if($qp) { 
			$string 				=	quoted_printable_encode(html2text($string));
			$string 				= 	preg_replace_callback('/^\s+|\n|\r|\s+$/m', function(){
				$string 			=	 '';
			}, $string);

		} /*else {
			$string 				= 	preg_replace_callback('/^\s+|\n|\r|\s+$/m', function(){
				$string 			=	 '';
			}, $string);
		}*/
		$maxlen 					= 	75 - 7 - strlen($this->CharSet);
		$string 					= 	$this->wrapText($string, $maxlen, true);
		return $string;
	}

	/**
	* _SetupHeaders
	* We also set up all of the boundaries here.
	* Each type is slightly different with different requirements for boundaries and content-type's.
	*/
	function _setupHeaders($format, $toAddress, $toname = '')
	{
		// HEADERS
		$headers = array();
		$email_data 				=	'';

		$semi_rand 					= 	md5(uniqid('ssb', true)); // 'ssb' = sendstudio boundary :)
		$mime_boundary 				= 	'b1_'.$semi_rand;
		$this->_Boundaries 			= 	array($mime_boundary);
		$this->_Boundaries[] 		= 	str_replace('b1_', 'b2_', $mime_boundary);
		$this->_Boundaries[] 		= 	str_replace('b1_', 'b3_', $mime_boundary);

		$headers['Return-Path']		= 	"Return-Path: " . $this->ReturnPath;
		$headers['from']			= 	"From: " . $this->email_escape($this->FromName) . " <".$this->FromAddress.">";
		$headers['Reply-To']		= 	"Reply-To: " . $this->email_escape($this->FromName) ." <".$this->ReplyTo.">";
		$headers['to']				= 	"To: " . $this->email_escape($toname) . " <".$toAddress.">";
		$headers['date'] 			= 	"Date: ".date('r');
		if($this->MessageId) {
			$headers['mid1']		= 	"Message-ID: <". $this->MessageId .">";
		} else { 
			$headers['mid1']		= 	"Message-ID: <".sha1(microtime(true))."@{$this->DKIM_domain}>";
		}

		$headers['mimever'] 		= 	"MIME-Version: 1.0";

		if($this->Multipart) {
			$add_boundary 			= 	true;
			$headers['ctype']		= 	"Content-Type: multipart/alternative; charset=".$this->CharSet;
		} else {
			$headers['ctype']		= 	"Content-Type: text/html; charset=".$this->CharSet;
		}
		$headers['cencod']			= 	"Content-Transfer-Encoding: quoted-printable";

		if ($add_boundary) {
			$headers['ctype']  		.= 	"; boundary=" . '"' . $mime_boundary . '"';
		}

		$sub    					= 	'Subject: '.$this->email_escape($this->Subject);
		$dkim_sign 					=	'';
		$dkim_sign2 				=	'';
		if(isset($this->DKIM_domain) && $this->DKIM_domain != '') {
			if($this->DKIM_selector != '' && $this->DKIM_private != '') 
				$dkim_sign 				= 	$this->_setupDKIMHeaders($headers, $sub);
		}
		if(isset($this->DDKIM_domain) && $this->DDKIM_domain != '') { 
			if($this->DDKIM_selector != '' && $this->DDKIM_private != '') 
    			$dkim_sign2 			= 	$this->_setupDoubleDKIMHeaders($headers, $sub);
    	}

    	// Create Email Data, First Headers was DKIM and DDKIM
    	if(isset($dkim_sign) && $dkim_sign != '') {
			$email_data 			.= 	"{$dkim_sign}\r\n";
		}
		if(isset($dkim_sign2) && $dkim_sign2 != '') {
			$email_data 			.= 	"{$dkim_sign2}\r\n";
		}

		$email_data 				.=	'Subject: '.$this->email_escape($this->Subject) . "\r\n"; 

		$conHeader 					=	array();
		$conHeader['sender'] 		=	"Sender: " . $this->email_escape($this->Sendername) ." <".$this->Sender.">";
		$conHeader['ctype'] 		=	$headers['ctype'];
		$conHeader['cencod'] 		=	$headers['cencod'];
		$conHeader['mimever'] 		=	$headers['mimever'];

		unset($headers['ctype']);
		unset($headers['cencod']);
		unset($headers['mimever']);

		// Include Other Headers
		foreach($headers as $val){
			$email_data 			.= 	"{$val}\r\n";
		}

		//Include extra headers
		if (!empty($this->extra_headers)) {
			foreach ($this->extra_headers as $p => $header) {
				$email_data .= $header . "\r\n";
			}
		}

		// Include Content type headers
		foreach($conHeader as $val){
			$email_data 			.= 	"{$val}\r\n";
		}

		return $email_data;
	}

	/**
	* _SetupBody
	* Sets up the html, text, and multipart bodies ready to send.
	*/
	function _setupBody()
	{
		$headbody 					=	'';
		if($this->Multipart) {
			$add_boundary 			= 	true;
		}
		if($add_boundary) { 
			$hbody 					= 	'';
			$hbody 					.= 	'--' . $this->_Boundaries[0] . "\r\n";

			$hbody 					.= 	'Content-Type: text/plain; format=flowed; charset="' . $this->CharSet . '"' . "\r\n";
			$hbody 					.= 	"Content-Transfer-Encoding:  quoted-printable" . "\r\n" . "\r\n";
			$hbody 					.=	$this->encodeQP( $this->OrgBody ). "\r\n" . "\r\n";
			$hbody 					.= 	'--' . $this->_Boundaries[0] . "\r\n";

			$hbody 					.= 	'Content-Type: text/html; charset="' . $this->CharSet . '"' . "\r\n";
			$hbody 					.= 	"Content-Transfer-Encoding:  quoted-printable" . "\r\n" . "\r\n";
			$hbody 					.=	$this->encodeQP( $this->Body, false) . "\r\n" . "\r\n";
			$hbody 					.= 	'--' . $this->_Boundaries[0] . '--' . "\r\n";


			$headbody             	.= 	"{$hbody}\r\n";
		}
		return $headbody;
	}

	/**
	* _setupDKIMHeaders
	* Dkim set up 
	*/
	function _setupDKIMHeaders($headers, $sub)
	{
		$body 						=	$this->_setupBody();
		// Create DKIM-Signature Object
		$mds 						= 	new mailDomainSigner($this->DKIM_private,$this->DKIM_domain,$this->DKIM_selector,'','','');
		$dkim_sign 					= 	$mds->getDKIM(
			"from:to:subject:mime-version:date:message-id:content-type:content-transfer-encoding",
			array(
				$headers['from'],
				$headers['to'],			
				$sub,
				$headers['mimever'],
				$headers['date'],
				$headers['mid1'],
				$headers['ctype'],
				$headers['cencod']
			),
			$body
		);
		return $dkim_sign;
	}

	/**
	* _setupDoubleDKIMHeaders
	* Double Dkim set up 
	*/
	function _setupDoubleDKIMHeaders($headers, $sub)
	{
		$body 						=	$this->_setupBody();
		$mds2 						= 	new mailDomainSigner('','','',$this->DDKIM_private,$this->DDKIM_domain,$this->DDKIM_selector);

		// Create DKIM-Signature Header
		$dkim_sign2 = $mds2->getDKIM2(
			"from:to:subject:mime-version:date:message-id:content-type:content-transfer-encoding",
			array(
				$headers['from'],
				$headers['to'],			
				$sub,
				$headers['mimever'],
				$headers['date'],
				$headers['mid1'],
				$headers['ctype'],
				$headers['cencod']
			),
			$body
		);
		return $dkim_sign2;
	}

	/**
	* ForgetEmail
	* Forgets the email settings ready for another send.
	*
	* @return Void Doesn't return anything.
	*/
	function ForgetEmails()
	{
		$this->Body 								= 	null;
		$this->ToAddress 							= 	null;
		$this->Subject 								= 	null;
		$this->extra_headers 						= 	array();
		$this->_sendmailparameters 					= 	null;
		$this->_AttachmentBody 						= 	'';
		$this->_ImageBody 							= 	null;
		//$this->ClearAttachments();
		$this->EmbedImages 							= 	false;
		$this->_EmbeddedImages 						= 	Array();
	}

	/**
	* ClearRecipients
	* Clears out all recipients for the email. Useful if you want to send emails one by one.
	*
	* @see _Recipients
	* @see _RecipientsCustomFields
	*
	* @return Void Doesn't return anything - just empties out the recipients information.
	*/
	function ClearRecipients()
	{
		$this->_Recipients 							= 	array();
	}

	/**
	* Adds an address and name to the list of recipients to email.
	*
	* @param String $address Email Address to add.
	* @param String $name Their name (if applicable). This is checked before constructing the email to make sure it's available.
	* @param String $format Which format the recipient wants to receive. Either 'h' or 't'.
	*
	* @see _Recipients
	*
	* @return Void Doesn't return anything - just adds the information to the _Recipients array.
	*/
	function AddRecipient($address, $name = '', $format='h')
	{
		$curr = count($this->_Recipients);
		$this->_Recipients[$curr]['address'] 		= 	trim($address);
		$this->_Recipients[$curr]['name'] 			= 	$name;
		$this->_Recipients[$curr]['format'] 		= 	strtolower($format);
	}

	/**
     * Get the server hostname.
     * Returns 'localhost.localdomain' if unknown.
     * @access protected
     * @return string
     */
    protected function serverHostname()
    {
        $result 									= 	$this->message_id_server;
        if (!empty($this->Hostname)) {
            $result 								= 	$this->Hostname;
        } elseif (isset($_SERVER) and array_key_exists('SERVER_NAME', $_SERVER) and !empty($_SERVER['SERVER_NAME'])) {
            $result 								= 	$_SERVER['SERVER_NAME'];
        } elseif (function_exists('gethostname') && gethostname() !== false) {
            $result 								= 	gethostname();
        } elseif (php_uname('n') !== false) {
            $result 								= 	php_uname('n');
        }
        return $result;
    }
}

?>
