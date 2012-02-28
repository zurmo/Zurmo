<?php 
/**
 * Swift Mailer wrapper class.
 *
 * @author Sergii 'b3atb0x' hello@webkadabra.com
 */
class SwiftMailer extends CComponent {
	
	/**
	 * smtp, sendmail or mail
	 */
	public $mailer = 'sendmail'; // 
	/**
	 * SMTP outgoing mail server host
	 */
	public $host;
	/**
	 * Outgoing SMTP server port
	 */
	public $port=25;
	/** 
	 * SMTP Password
	 */
	public $username;
	/**
	 * SMTP email
	 */
	public $password;
	/**
	 * @param string Message Subject
	 */
	public $Subject;
	/**
	 * @param mixed Email addres messages are going to be sent "from"
	 */
	public $From;
	/**
	 * @param string HTML Message Body
	 */
	public $body;
	/**
	 * @param string Alternative message body (plain text)
	 */
	public $altBody=null;
	
	protected $_addresses=array();
	
	public function init() {
		require_once(dirname(__FILE__).'/lib/swift_required.php');
	}
	
	public function AddAddress($address) {
		if(!in_array($address, $this->_addresses))
			$this->_addresses[] = $address;
	}
	public function MsgHTML($body) {
		$this->body = $body;
		if($this->altBody == null) {
			$this->altBody = strip_tags($this->body);
		}
	}
	/**
	 * Helper function to send emails like this:
	 * <code>
	 *		Yii::app()->mailer->AddAddress($email);
	 *		Yii::app()->mailer->Subject = $newslettersOne['name'];
	 * 		Yii::app()->mailer->MsgHTML($template['content']);
	 *		Yii::app()->mailer->Send();
	 * </code>
	 * @return boolean Whether email has been sent or not
	 */
	public function Send() {
		//Create the Transport
		$transport = $this->loadTransport();
		
		//Create the Mailer using your created Transport
		$mailer = Swift_Mailer::newInstance($transport);
		
		//Create a message
		$message = Swift_Message::newInstance($this->Subject)
			  ->setFrom($this->From)
			  ->setTo($this->_addresses);
		if($this->body) {
			$message->addPart($this->body, 'text/html');
		}
		if($this->altBody) {
			$message->setBody($this->altBody);
		}
		
		$result = $mailer->send($message);
		
		$this->ClearAddresses();
	}
	public function ClearAddresses() {
		$this->_addresses = array();
	}
	
	/* Helpers */
	public function preferences() {
		return	Swift_Preferences;
	}
	
	public function attachment() {
		return Swift_Attachment;
	}
	
	public function newMessage($subject) {
		return Swift_Message::newInstance($subject);
	}
	
	public function mailer($transport=null) {
		return Swift_Mailer::newInstance($transport);
	}
	
	public function image() {
		return Swift_Image;
	}
	public $sendmailCommand = '/usr/bin/sendmail -t';
	public function smtpTransport($host=null, $port=null) {
		return Swift_SmtpTransport::newInstance($host, $port);
	}
	
	public function sendmailTransport($command=null) {
		return Swift_SendmailTransport::newInstance($command);
	}
	
	public function mailTransport() {
		return Swift_MailTransport::newInstance();
	}
	
	protected function loadTransport() {
		if($this->mailer == 'smtp') {
			$transport = self::smtpTransport($this->host, $this->port);
			$transport->setUsername($this->username)->setPassword($this->password);
		} elseif($this->mailer == 'mail') {
			$transport = self::mailTransport();
		} elseif($this->mailer == 'sendmail') {
			$transport = self::sendmailTransport($this->sendmailCommand);
		}
		
		return $transport;
	}
	
	
}