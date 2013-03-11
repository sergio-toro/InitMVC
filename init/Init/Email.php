<?php
// Require php mailer library
require_once INIT_ROOT . 'external/PHPMailer/class.phpmailer.php';

/**
 * @author Sergio Toro <yiti007@gmail.com>
 * Handle email submits
 * @method {object} subject()   subject($subject)           Set email subject
 * @method {object} to()        to($email, [$name = ''])    Email recipient
 * @method {object} set_data()  set_data($data)             Set email data
 * @method {bool}   send()      send()                      Sends email
 */
class Init_Email {
    /**
     * For Init_Timer pruposes
     * @var integer
     */
    private static $i = 0;

    private $email_tpl;
    private $email;
    private $data;
    private $to_email;
    private $to_name;
    private $subject = '';

    /**
     * Email class constructor
     * Email class constructor
     * @param array $email_tpl Template to use
     */
    public function __construct($email_tpl) {
        // Template
        $this->email_tpl = $email_tpl;
        // Require PHPMailer library
        $email = new Core_Email();

echo "<pre>"; print_r($email); echo "</pre>";

        die('hellow!');

        /*$this->email->from(NO_REPLY_EMAIL, NO_REPLY_NAME);
        $this->email->to(NOTIFY_EMAIL);
        $this->email->subject($subject);
        $this->email->message($html);
        return $this->email->send();*/


        //$email = new PHPMailer(TRUE); // If TRUE, PHPMailer trigger errors
        // If not SMTP, uses sendmail
        if (EMAIL_SMTP) {
            $email->IsSMTP();
            $email->SMTPDebug  = EMAIL_SMTP_DEBUG;   // enables SMTP debug information (for testing)
            $email->SMTPAuth   = EMAIL_SMTP_AUTH;    // enable SMTP authentication
            if (EMAIL_SMTP_SSL)
                $email->SMTPSecure = 'ssl';          // sets the prefix to the servier
            $email->Host       = EMAIL_SMTP_SERVER;  // sets GMAIL as the SMTP server
            $email->Port       = EMAIL_SMTP_PORT;    // set the SMTP port for the GMAIL server
            $email->Username   = EMAIL_SMTP_USERNAME;// GMAIL username
            $email->Password   = EMAIL_SMTP_PASSWORD;// GMAIL password
        }
        else $email->IsSendmail();
        $this->email =& $email;
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
        $this->to_email = $email;
        $this->to_name = $name;
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
        die('wtf???');
        if (TIMERS) $timer = new Init_Timer('SEND EMAIL '.self::$i++, array('tpl'=> $this->email_tpl, 'to'=> $this->to_email));
        // Obtener contenido del email
        $html = Init_Template::fetch($this->email_tpl, $this->data);
        $subject = $this->subject;
        // Get subject from template
        if (preg_match('#<title>(.*)</title>#', $html, $matches)) {
            $subject = $matches[1];
        }

        if (EMAIL_EMOGRIFY_CSS && file_exists(EMAIL_EMOGRIFY_CSS)) {
            require_once INIT_ROOT . 'external/Emogrifier.php';
            $css = file_get_contents(EMAIL_EMOGRIFY_CSS);
            $emogrifier = new Emogrifier($html, $css);
            $html = @$emogrifier->emogrify(); // Supress warnings
        }

        $this->email->SetFrom(EMAIL_FROM, EMAIL_FROM_NAME);
        $this->email->AddAddress($this->to_email, $this->to_name);
        $this->email->Subject = $subject;
        $this->email->MsgHTML($html);
        try {
            $this->email->Send();
        } catch (phpmailerException $e) {
            throw new Init_Exception(Init_Exception::EMAIL_SEND_ERROR, $e->errorMessage());
        } catch (Exception $e) {
            throw new Init_Exception(Init_Exception::EMAIL_SEND_ERROR, $e->errorMessage());
        }
        if (TIMERS) $timer->end();
        return TRUE;
    }
}