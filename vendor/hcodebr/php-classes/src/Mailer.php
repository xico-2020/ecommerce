<?php 

namespace Hcode;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

use Rain\Tpl;

class Mailer {

	const USERNAME = "xicopaulo2015@gmail.com";
	//const USERNAME = "fpaulocgd@outlook.pt";
	const PASSWORD = "Urtiga11";
	/*const PASSWORD = "<?password?>";*/
	const NAME_FROM = "Hcode Store";

	private $mail;

	public function __construct($toAdress, $toName, $subject, $tplName, $data = array())

	{
		$config = array(           // tpl (templates) precisa de uma pasta para os arquivos HTML e outra para a cache. 
			"tpl_dir"       => $_SERVER["DOCUMENT_ROOT"]."/views/email/",      //  A  partir do nosso diretorio de root do nosso projeto vai procurar a pasta ..., Para isso uso varivavel de ambiente no Server que é a "DOCUMENT_ROOT" que traz essa pasta. Depois indico a pasta onde estão os templates. A seguir o mesmo para cache.
			"cache_dir"     => $_SERVER["DOCUMENT_ROOT"]."/views-cache/",
			"debug"         => false // set to false to improve the speed
		);

		Tpl::configure( $config );

		$tpl = new Tpl;

		foreach ($data as $key => $value) {  // Passar dados para o template que estão na variavel $data. assign é o metodo que está na classe Page metodo setData.
			$tpl->assign($key, $value);   // cria as variaveis dentro do template.
		}

		$html = $tpl->draw($tplName, true);   // true é para colocar o valor de $tplName na variavel $html e nao no ecran.


		$this->mail = new \PHPMailer;

		//Tell PHPMailer to use SMTP
		$this->mail->isSMTP();

		$this->mail->SMTPOptions = array(
    		'ssl' => array(
       	 	'verify_peer' => false,
     		'verify_peer_name' => false,
        	'allow_self_signed' => true
    		)
		);

		//Enable SMTP debugging
		//SMTP::DEBUG_OFF = off (for production use)
		//SMTP::DEBUG_CLIENT = client messages
		//SMTP::DEBUG_SERVER = client and server messages
		$this->mail->SMTPDebug = false;

		$this->mail->Debugoutput = 'html';

		//Set the hostname of the mail server
		$this->mail->Host = 'smtp.gmail.com';
		//Use `$mail->Host = gethostbyname('smtp.gmail.com');`
		//if your network does not support SMTP over IPv6,
		//though this may cause issues with TLS

		//Set the SMTP port number:
		// - 465 for SMTP with implicit TLS, a.k.a. RFC8314 SMTPS or
		// - 587 for SMTP+STARTTLS
		// $this->mail->Port = 465;
		$this->mail->Port = 587;

		//Set the encryption mechanism to use:
		// - SMTPS (implicit TLS on port 465) or
		// - STARTTLS (explicit TLS on port 587)
		$this->mail->SMTPSecure = 'tls';

		//Whether to use SMTP authentication
		$this->mail->SMTPAuth = true;

		//Username to use for SMTP authentication - use full email address for gmail
		$this->mail->Username = Mailer::USERNAME;

		//Password to use for SMTP authentication
		$this->mail->Password = Mailer::PASSWORD;

		//Set who the message is to be sent from
		//Note that with gmail you can only use your account address (same as `Username`)
		//or predefined aliases that you have configured within your account.
		//Do not use user-submitted addresses in here
		$this->mail->setFrom(Mailer::USERNAME, Mailer::NAME_FROM);

		//Set an alternative reply-to address
		//This is a good place to put user-submitted addresses
		//$mail->addReplyTo('replyto@example.com', 'First Last');

		//Set who the message is to be sent to
		$this->mail->addAddress($toAdress, $toName);

		$this->mail->CharSet = 'UTF-8';

		//Set the subject line
		$this->mail->Subject = $subject;

		//Read an HTML message body from an external file, convert referenced images to embedded,
		//convert HTML into a basic plain-text alternative body
		$this->mail->msgHTML($html);        // $html - variavel preenchida com o template acima no código.
		//$this->mail->msgHTML($html);

		//Replace the plain text body with one created manually

		$this->mail->AltBody = 'This is a plain-text message body';

		//Attach an image file
		//$mail->addAttachment('images/phpmailer_mini.png');

		//send the message, check for errors
		//if (!$mail->send()) {
		//echo 'Mailer Error: ' . $mail->ErrorInfo;
		//} else {
		//echo 'Message sent!';
		//}

		/*

		//Section 2: IMAP
		//IMAP commands requires the PHP IMAP Extension, found at: https://php.net/manual/en/imap.setup.php
		//Function to call which uses the PHP imap_*() functions to save messages: https://php.net/manual/en/book.imap.php
		//You can use imap_getmailboxes($imapStream, '/imap/ssl', '*' ) to get a list of available folders or labels, this can
		//be useful if you are trying to get this working on a non-Gmail IMAP server.
		function save_mail($mail)
		{
		    //You can change 'Sent Mail' to any other folder or tag
		    $path = '{imap.gmail.com:993/imap/ssl}[Gmail]/Sent Mail';

		    //Tell your server to open an IMAP connection using the same username and password as you used for SMTP
		    $imapStream = imap_open($path, $mail->Username, $mail->Password);

		    $result = imap_append($imapStream, $path, $mail->getSentMIMEMessage());
		    imap_close($imapStream);

		    return $result;
		}
		*/

$this->mail->Username = Mailer::USERNAME;

	}


	public function send()
	{
		return $this->mail->send();
	}



}



 ?>