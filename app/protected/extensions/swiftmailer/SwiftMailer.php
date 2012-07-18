<?php
/**
 * Swift Mailer wrapper class.
 *
 * @author Sergii 'b3atb0x' hello@webkadabra.com
 * @author Jason Green - http://www.zurmo.org - Cleaned up this class a bit.
 */
class SwiftMailer extends Mailer
{
    const ADDRESS_TYPE_TO  = 1;

    const ADDRESS_TYPE_CC  = 2;

    const ADDRESS_TYPE_BCC = 3;
    /**
     * smtp, sendmail or mail
     */
    public $mailer = 'smtp';
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
     * SMTP security
     */
    public $security = null;
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

    public $attachments = array();

    protected $toAddressesAndNames = array();

    protected $ccAddressesAndNames = array();

    protected $bccAddressesAndNames = array();

    public $sendmailCommand = '/usr/bin/sendmail -t';

    public function init()
    {
        require_once(dirname(__FILE__) . '/lib/swift_required.php');
    }

    public function addAddressByType($address, $name, $type)
    {
        if($type == self::ADDRESS_TYPE_TO)
        {
            if(!isset($this->toAddressesAndNames[$address]))
            {
                $this->toAddressesAndNames[$address] = $name;
            }
        }
        elseif($type == self::ADDRESS_TYPE_CC)
        {
            if(!isset($this->ccAddressesAndNames[$address]))
            {
                $this->ccAddressesAndNames[$address] = $name;
            }
        }
        elseif($type == self::ADDRESS_TYPE_BCC)
        {
            if(!isset($this->bccAddressesAndNames[$address]))
            {
                $this->bccAddressesAndNames[$address] = $name;
            }
        }
        else
        {
            throw new NotSupportedException();
        }
    }

    public function msgHTML($body)
    {
        $this->body = $body;
        if($this->altBody == null)
        {
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
    public function send()
    {
        //Create the Transport
        $transport = $this->loadTransport();

        //Create the Mailer using your created Transport
        $mailer = Swift_Mailer::newInstance($transport);

        //Create a message
        $message = Swift_Message::newInstance($this->Subject);
        $message->setFrom($this->From);
        foreach($this->toAddressesAndNames as $address => $name)
        {
            $message->addTo($address, $name);
        }

        if($this->body) {
            $message->addPart($this->body, 'text/html');
        }
        if($this->altBody) {
            $message->setBody($this->altBody);
        }

        if (!empty($this->attachments))
        {
            foreach ($this->attachments as $attachment)
            {
                $message->attach($attachment);
            }
        }

        $result = $mailer->send($message);
        $this->clearAddresses();
        return $result;
    }

    public function clearAddresses()
    {
        $this->toAddressesAndNames  = array();
        $this->ccAddressesAndNames  = array();
        $this->bccAddressesAndNames = array();
    }

    /* Helpers */
    public function preferences() {
        return	Swift_Preferences;
    }

    public function attachment() {
        return Swift_Attachment;
    }

    public function newMessage($subject)
    {
        return Swift_Message::newInstance($subject);
    }

    public function mailer($transport = null)
    {
        return Swift_Mailer::newInstance($transport);
    }

    public function image()
    {
        return Swift_Image;
    }

    public function smtpTransport($host = null, $port = null, $security = null)
    {
        return Swift_SmtpTransport::newInstance($host, $port, $security);
    }

    public function sendmailTransport($command = null)
    {
        return Swift_SendmailTransport::newInstance($command);
    }

    public function mailTransport()
    {
        return Swift_MailTransport::newInstance();
    }

    protected function loadTransport()
    {
        if($this->mailer == 'smtp')
        {
            $transport = static::smtpTransport($this->host, $this->port, $this->security);
            $transport->setUsername($this->username)->setPassword($this->password)->setEncryption($this->security);
        }
        elseif($this->mailer == 'mail')
        {
            $transport = static::mailTransport();
        }
        elseif($this->mailer == 'sendmail')
        {
            $transport = static::sendmailTransport($this->sendmailCommand);
        }
        return $transport;
    }

    /**
    * Create Swift_Attachment based on dynamic content(for example when content
    * is stored in database), filename and type.
    *
    * @param binary $content
    * @param string $filename, for example 'image.png'
    * @param string $contentType, for example 'application/octet-stream'
    * @see SwiftMailer::attachment()
    */
    public function attachDynamicContent($content, $filename, $contentType)
    {
        $attachment = Swift_Attachment::newInstance($content, $filename, $contentType);
        $this->attachments[] = $attachment;
        return $attachment;
    }

    /**
     * Create Swift_Attachment based on file.
     * @param string $path
     * @return Swift_Mime_Attachment
     */
    public function attachFromPath($path)
    {
        $attachment = Swift_Attachment::fromPath($path);
        $this->attachments[] = $attachment;
        return $attachment;
    }
}