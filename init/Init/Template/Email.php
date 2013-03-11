<?php


/**
 * @author Sergio Toro <yiti007@gmail.com>
 * Handle email submits
 * @method {object} subject()   subject($subject)           Set email subject
 * @method {object} to()        to($email, [$name = ''])    Email recipient
 * @method {object} set_data()  set_data($data)             Set email data
 * @method {bool}   send()      send()                      Sends email
 */
class Init_Template_Email {
    /**
     * For Init_Timer pruposes
     * @var integer
     */
    private static $i = 0;

    private $email_transport;

    private $email_tpl;
    private $data;
    private $toEmail;
    private $toName;
    private $subject = '';

    /**
     * Email class constructor
     * Email class constructor
     * @param array $email_tpl Template to use
     */
    public function __construct($email_tpl, $data = FALSE) {
        // Require swift loader
        require_once INIT_ROOT . 'external/swift/swift_required.php';
        // Template
        $this->email_tpl = $email_tpl;
        $this->data = $data;
    }
    
    /**
     * Email subject
     * @param  string $subject 
     * @return object $this
     */
    public function subject($subject) {
        $this->subject = $subject;
        return $this;
    }

    /**
     * Email recipient
     * @param  string $email Recipient email
     * @param  string $name  Recipient name
     * @return object $this
     */
    public function to($email, $name = '') {
        $this->toEmail = $email;
        $this->toName = $name;
        return $this;
    }

    /**
     * Set email data
     * @param array $data data to send
     * @return object $this
     */
    public function setData($data) {
        $this->data = $data;
        return $this;
    }

    /**
     * Sends email
     * @return bool
     */
    public function send() {
        if (TIMERS) $timer = new Init_Timer('SEND_EMAIL '.self::$i++, array( 'tpl'=> $this->email_tpl, 'to'=> $this->toEmail ));
        // Obtener contenido del email
        $html = Init_Template::fetch($this->email_tpl, $this->data);
        $subject = $this->subject;
        // Get subject from template
        if (preg_match('#<title>(.*)</title>#', $html, $matches)) {
            $subject = $matches[1];
        }

        if (EMAIL_EMOGRIFY && file_exists(EMAIL_EMOGRIFY)) {
            require_once INIT_ROOT . 'external/Emogrifier.php';
            $css = file_get_contents(EMAIL_EMOGRIFY);
            $emogrifier = new Emogrifier($html, $css);
            $html = @$emogrifier->emogrify(); // Supress warnings
        }

        $message = Swift_Message::newInstance($subject)
        ->setFrom(array(EMAIL_FROM => EMAIL_FROM_NAME))
        ->setTo(array($this->toEmail => $this->toName))
        ->setBody($html, EMAIL_TYPE);

        if (!self::mailer()->send($message, $failures)) throw new Init_Exception(Init_Exception::EMAIL_SEND_ERROR);
        if (TIMERS) $timer->end();

        return TRUE;
    }

    /**
     * Get mailer with appropiate transport
     * @return Object Swift_Mailer
     */
    private static function mailer() {
        switch(EMAIL_PROTOCOL) {
            case 'smtp':
                $transport = Swift_SmtpTransport::newInstance(SMTP_HOST, SMTP_PORT, SMTP_CRYPTO)
                    ->setUsername(SMTP_USER)->setPassword(SMTP_PASS);
                break;
            case 'sendmail':
                $transport = Swift_SendmailTransport::newInstance(SENDMAIL_PATH);
                break;
            case 'mail':
            default:
                $transport = Swift_MailTransport::newInstance();
                break;
        }
        return Swift_Mailer::newInstance($transport);
    }
}