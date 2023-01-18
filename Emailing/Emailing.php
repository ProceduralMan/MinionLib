<?php

/* 
 * Emailing
 * Support for direct mailing using a procedural wrapper to PHPMailer
 * @author ProceduralMan <proceduralman@gmail.com>
 * @copyright 2021
 * @version 1.0 initial version
 * @package Minion
 * @todo plantilla de newsletter (https://mjml.io/try-it-live/templates/christmas)
 */
//These must be at the top of your script, not inside a function
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

/*
 * AUTOLOADER CARGA MINION Y REDECLARA LAS COSAS
 * DE MOMENTO VOY CON LLAMADA DIRECTA (la alternativa es cambiar las referencias a MinionSetup por llamadas a autoloader)
//Import PHPMailer classes into the global namespace
//These must be at the top of your script, not inside a function
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
echo 'Voy a cargar autoloadder'.PHP_EOL;
//Load Composer's autoloader
require_once __DIR__.'/../vendor/autoload.php';
echo 'Vhe cargado autoloadder'.PHP_EOL;

//Import PHPMailer classes into the global namespace
//use PHPMailer\PHPMailer\PHPMailer;
//use PHPMailer\PHPMailer\SMTP;
 * 
 */
/*
 * ALTERNATE WAY OF LOADING. MANUAL LOADING OF THE MAILER CLASS
set_include_path(__DIR__.'/../vendor');
use PHPMailer\PHPMailer\PHPMailer;

require __DIR__.'/../vendor/phpmailer/phpmailer/src/Exception.php';
require __DIR__.'/../vendor/phpmailer/phpmailer/src/PHPMailer.php';
require __DIR__.'/../vendor/phpmailer/phpmailer/src/SMTP.php';
 */

/**
 * SimpleHTMLEmail
 * Sends a simple email with no images or attachments. Credential are taken from defined constants
 * @param   mixed   $TO         Destination of the message. Either an email address or an array <address> => <name>
 * @param   string  $FromName   Sender name
 * @param   string  $Subject    Subject of the message
 * @param   string  $HTMLBody   Message body in HTML format.
 * @param   string  $TextBody   Message body in simple-text format. Optional
 * @param   mixed   $CC         Optional. Copy of de message. Either an email address or an array <address> => <name>
 * @param   mixed   $BCC        Optional. Carbon-copy of the message. Either an email address or an array <address> => <name>
 * @return  boolean TRUE on success, FALSE in other case
 * @since   0.0.9
 * @see
 * @todo 
 */
function SimpleHTMLEmail($TO, $FromName, $Subject, $HTMLBody, $TextBody = NULL, $CC = NULL, $BCC = NULL)
{
    //If credentials are not defined we have a problem
    if ((defined("MIL_PHPMAILER_USERNAME") === FALSE)||(defined("MIL_PHPMAILER_USERNAME") === FALSE))
    {
        $Message = 'Email credential constants MIL_PHPMAILER_USERNAME and MIL_PHPMAILER_PASSWORD must be defined.';
        ErrorLog($Message, E_USER_ERROR);

        return FALSE;
    }

    //We need email server parameters too!
    if ((defined("MIL_PHPMAILER_SMTPHOST") === FALSE)||(defined("MIL_PHPMAILER_SMTPPORT") === FALSE)||(defined("MIL_PHPMAILER_ENCRYPTION") === FALSE)
            ||(defined("MIL_PHPMAILER_SMTPAUTH") === FALSE))
    {
        $Message = 'The following server parameters must be defined:'.PHP_EOL.
                '    - MIL_PHPMAILER_SMTPHOST as hostname - such as smtp.gmail.com -'.PHP_EOL.
                '    - MIL_PHPMAILER_SMTPPORT as server port - usually 587 -'.PHP_EOL.
                '    - MIL_PHPMAILER_ENCRYPTION as encryption to use - usually PHPMailer::ENCRYPTION_STARTTLS which correspons to string "tls" -'.PHP_EOL.
                '    - MIL_PHPMAILER_SMTPAUTH to use authenticated SMPT -most of time should be TRUE -'.PHP_EOL;
        ErrorLog($Message, E_USER_ERROR);

        return FALSE;
    }

    //Finally, if no debug level is defined we set debugging to FALSE
    if (defined("MIL_PHPMAILER_SMTPDEBUGGING") === FALSE)
    {
        $Message = 'No debug level defined. Setting it to OFF';
        ErrorLog($Message, E_USER_NOTICE);

        define("MIL_PHPMAILER_SMTPDEBUGGING", SMTP::DEBUG_OFF);
    }

    //To parameter validation
    if (empty($TO))
    {
        $Message = 'At least one TO address is needed to send a message';
        ErrorLog($Message, E_USER_ERROR);

        return FALSE;
    }
    else
    {
        if (!is_array($TO))
        {
            //Is it a simple email?
            $Result = filter_var($TO, FILTER_VALIDATE_EMAIL);
            if ($Result === FALSE)
            {
                $Message = 'TO must be an email address or an array address => name';
                ErrorLog($Message, E_USER_ERROR);

                return FALSE;
            }
            else
            {
                //Yes it is. Let's construct the array based on the address
                $TempTO = $TO;
                $TO = array($TempTO => $TempTO);
            }
        }
        else
        {
            //It is an array
            foreach ($TO as $Address => $Name)
            {
                if (!filter_var($Address, FILTER_VALIDATE_EMAIL))
                {
                    $Message = 'Non-valid TO address: '.$Address;
                    ErrorLog($Message, E_USER_ERROR);

                    return FALSE;
                }
            }
        }   //End TO is an array
    }   //End TO is not empty

    //if no from nme we reuse the address
    if (!is_string($FromName)&&empty($FromName))
    {
        $FromName = MIL_PHPMAILER_USERNAME;
    }

    //Subject must have some info
    if (!is_string($Subject))
    {
        $Message = 'Empty message subject not allowed';
        ErrorLog($Message, E_USER_ERROR);

        return FALSE;
    }

    //Body must have some info
    if (!is_string($HTMLBody))
    {
        $Message = 'Empty message body not allowed';
        ErrorLog($Message, E_USER_ERROR);

        return FALSE;
    }

    //Add optional parts
    //Textual body if not present
    if (empty($TextBody))
    {
        //$TextBody = preg_replace( "/\n\s+/", "\n", rtrim(html_entity_decode(strip_tags($HTMLBody))));
        //The one used by PHPMailer
        //@see PHPMailer::html2text()
        $TextBody = html_entity_decode(trim(strip_tags(preg_replace('/<(head|title|style|script)[^>]*>.*?<\/\\1>/si', '', $HTMLBody))),
                ENT_QUOTES);
    }

    //Copied people
    if (!is_null($CC))
    {
        if (!is_array($CC))
        {
            //Is it a simple email?
            $Result = filter_var($CC, FILTER_VALIDATE_EMAIL);
            if ($Result === FALSE)
            {
                $Message = 'CC must be an email address or an array address => name';
                ErrorLog($Message, E_USER_ERROR);

                return FALSE;
            }
            else
            {
                //Yes it is. Let's construct the array based on the address
                $TempCC = $CC;
                $CC = array($TempCC => $TempCC);
            }
        }
        else
        {
            foreach ($CC as $Dir=>$Nombre)
            {
                if (!filter_var($Dir, FILTER_VALIDATE_EMAIL))
                {
                    $Message = 'Non-valid CC address: '.$Address;
                    ErrorLog($Message, E_USER_ERROR);

                    return FALSE;
                }
            }
        }   //End CC is an array
    }   //End CC with content

    //Carbon-copied people
    if (!is_null($BCC))
    {
        if (!is_array($BCC))
        {
            //Is it a simple email?
            $Result = filter_var($BCC, FILTER_VALIDATE_EMAIL);
            if ($Result === FALSE)
            {
                $Message = 'BCC must be an email address or an array address => name';
                ErrorLog($Message, E_USER_ERROR);

                return FALSE;
            }
            else
            {
                //Yes it is. Let's construct the array based on the address
                $TempBCC = $BCC;
                $BCC = array($TempBCC => $TempBCC);
            }
        }
        else
        {
            foreach ($BCC as $Dir=>$Nombre)
            {
                if (!filter_var($Dir, FILTER_VALIDATE_EMAIL))
                {
                    $Message = 'Non-valid BCC address: '.$Address;
                    ErrorLog($Message, E_USER_ERROR);

                    return FALSE;
                }
            }
        }   //End BCC is an array
    }   //End BCC with content

    //Create a new PHPMailer instance
    $Email = new PHPMailer;
    $Email->CharSet = "UTF-8";

    //Set the error languaje
    if (defined("MIL_PHPMAILER_LANG") === TRUE)
    {
        $Email->setLanguage(MIL_PHPMAILER_LANG);
    }

    //Tell PHPMailer to use SMTP
    $Email->isSMTP();

    //Enable SMTP debugging
    // SMTP::DEBUG_OFF = off (for production use)
    // SMTP::DEBUG_CLIENT = client messages
    // SMTP::DEBUG_SERVER = client and server messages
    $Email->SMTPDebug = MIL_PHPMAILER_SMTPDEBUGGING;

    //Set the hostname of the mail server
    //$mail->Host = 'smtp.gmail.com';
    $Email->Host = MIL_PHPMAILER_SMTPHOST;
    // use
    // $mail->Host = gethostbyname('smtp.gmail.com');
    // if your network does not support SMTP over IPv6

    //Set the SMTP port number - 587 for authenticated TLS, a.k.a. RFC4409 SMTP submission
    $Email->Port = MIL_PHPMAILER_SMTPPORT;

    //Set the encryption mechanism to use - STARTTLS or SMTPS
    $Email->SMTPSecure = MIL_PHPMAILER_ENCRYPTION;

    //Whether to use SMTP authentication
    $Email->SMTPAuth = MIL_PHPMAILER_SMTPAUTH;

    //Username to use for SMTP authentication - use full email address for gmail
    $Email->Username = MIL_PHPMAILER_USERNAME;

    //Password to use for SMTP authentication
    $Email->Password = MIL_PHPMAILER_PASSWORD;

    //Set who the message is to be sent from. Will fail if USERNAME <> user email address
    $Email->setFrom(MIL_PHPMAILER_USERNAME, $FromName);

    //Set an alternative reply-to address...PTE DE IMPLEMENTAR
    //$mail->addReplyTo('replyto@example.com', 'First Last');

    //Set who the message is to be sent to
    foreach ($TO as $Dir=>$Nombre)
    {
        $Email->addAddress($Dir, $Nombre);
    }

    //CCs y BCCs
    if (!is_null($CC))
    {
        if (is_array($CC))
        {
            foreach ($CC as $Dir=>$Nombre)
            {
                $Email->addCC($Dir, $Nombre);
            }
        }
    }

    if (!is_null($BCC))
    {
        if (is_array($BCC))
        {
            foreach ($BCC as $Dir=>$Nombre)
            {
                $Email->addBCC($Dir, $Nombre);
            }
        }
    }

    //Set the subject line
    $Email->Subject = $Subject;

    //Set body and alt-body
    $Email->Body = $HTMLBody;
    $Email->AltBody = $TextBody;

    //send the message, check for errors
    if (!$Email->send())
    {
        $Message = 'Message could not be sent. Mailer Error: '.$Email->ErrorInfo;
        ErrorLog($Message, E_USER_ERROR);

        return FALSE;
    }
}


/**
 * SimpleHTMLEmailWC
 * Sends a simple email with no images or attachments. Credentials are passed as parameters
 * @param   string  $Account    Email account
 * @param   string  $Password   Password
 * @param   mixed   $TO         Destination of the message. Either an email address or an array <address> => <name>
 * @param   string  $FromName   Sender name
 * @param   string  $Subject    Subject of the message
 * @param   string  $HTMLBody   Message body in HTML format.
 * @param   string  $TextBody   Message body in simple-text format. Optional
 * @param   mixed   $CC         Optional. Copy of de message. Either an email address or an array <address> => <name>
 * @param   mixed   $BCC        Optional. Carbon-copy of the message. Either an email address or an array <address> => <name>
 * @return  boolean TRUE on success, FALSE in other case
 * @since   0.0.9
 * @see
 * @todo 
 */
function SimpleHTMLEmailWC($Account, $Password, $TO, $FromName, $Subject, $HTMLBody, $TextBody = NULL, $CC = NULL, $BCC = NULL)
{
    //If credentials are not defined we have a problem
    if (empty($Account)||empty($Password))
    {
        $Message = 'Email credentials needed.';
        ErrorLog($Message, E_USER_ERROR);

        return FALSE;
    }

    //We need email server parameters too!
    if ((defined("MIL_PHPMAILER_SMTPHOST") === FALSE)||(defined("MIL_PHPMAILER_SMTPPORT") === FALSE)||(defined("MIL_PHPMAILER_ENCRYPTION") === FALSE)
            ||(defined("MIL_PHPMAILER_SMTPAUTH") === FALSE))
    {
        $Message = 'The following server parameters must be defined:'.PHP_EOL.
                '    - MIL_PHPMAILER_SMTPHOST as hostname - such as smtp.gmail.com -'.PHP_EOL.
                '    - MIL_PHPMAILER_SMTPPORT as server port - usually 587 -'.PHP_EOL.
                '    - MIL_PHPMAILER_ENCRYPTION as encryption to use - usually PHPMailer::ENCRYPTION_STARTTLS which correspons to string "tls" -'.PHP_EOL.
                '    - MIL_PHPMAILER_SMTPAUTH to use authenticated SMPT -most of time should be TRUE -'.PHP_EOL;
        ErrorLog($Message, E_USER_ERROR);

        return FALSE;
    }

    //Finally, if no debug level is defined we set debugging to FALSE
    if (defined("MIL_PHPMAILER_SMTPDEBUGGING") === FALSE)
    {
        $Message = 'No debug level defined. Setting it to OFF';
        AddDebug($Message);

        define("MIL_PHPMAILER_SMTPDEBUGGING", SMTP::DEBUG_OFF);
    }

    //To parameter validation
    if (empty($TO))
    {
        $Message = 'At least one TO address is needed to send a message';
        ErrorLog($Message, E_USER_ERROR);

        return FALSE;
    }
    else
    {
        if (!is_array($TO))
        {
            //Is it a simple email?
            $Result = filter_var($TO, FILTER_VALIDATE_EMAIL);
            if ($Result === FALSE)
            {
                $Message = 'TO must be an email address or an array address => name';
                ErrorLog($Message, E_USER_ERROR);

                return FALSE;
            }
            else
            {
                //Yes it is. Let's construct the array based on the address
                $TempTO = $TO;
                $TO = array($TempTO => $TempTO);
            }
        }
        else
        {
            //It is an array
            foreach ($TO as $Address => $Name)
            {
                if (!filter_var($Address, FILTER_VALIDATE_EMAIL))
                {
                    $Message = 'Non-valid TO address: '.$Address;
                    ErrorLog($Message, E_USER_ERROR);

                    return FALSE;
                }
            }
        }   //End TO is an array
    }   //End TO is not empty

    //if no from nme we reuse the address
    if (!is_string($FromName)&&empty($FromName))
    {
        $FromName = MIL_PHPMAILER_USERNAME;
    }

    //Subject must have some info
    if (!is_string($Subject))
    {
        $Message = 'Empty message subject not allowed';
        ErrorLog($Message, E_USER_ERROR);

        return FALSE;
    }

    //Body must have some info
    if (!is_string($HTMLBody))
    {
        $Message = 'Empty message body not allowed';
        ErrorLog($Message, E_USER_ERROR);

        return FALSE;
    }

    //Add optional parts
    //Textual body if not present
    if (empty($TextBody))
    {
        //$TextBody = preg_replace( "/\n\s+/", "\n", rtrim(html_entity_decode(strip_tags($HTMLBody))));
        //The one used by PHPMailer
        //@see PHPMailer::html2text()
        $TextBody = html_entity_decode(trim(strip_tags(preg_replace('/<(head|title|style|script)[^>]*>.*?<\/\\1>/si', '', $HTMLBody))),
                ENT_QUOTES);
    }

    //Copied people
    if (!is_null($CC))
    {
        if (!is_array($CC))
        {
            //Is it a simple email?
            $Result = filter_var($CC, FILTER_VALIDATE_EMAIL);
            if ($Result === FALSE)
            {
                $Message = 'CC must be an email address or an array address => name';
                ErrorLog($Message, E_USER_ERROR);

                return FALSE;
            }
            else
            {
                //Yes it is. Let's construct the array based on the address
                $TempCC = $CC;
                $CC = array($TempCC => $TempCC);
            }
        }
        else
        {
            foreach ($CC as $Dir=>$Nombre)
            {
                if (!filter_var($Dir, FILTER_VALIDATE_EMAIL))
                {
                    $Message = 'Non-valid CC address: '.$Address;
                    ErrorLog($Message, E_USER_ERROR);

                    return FALSE;
                }
            }
        }   //End CC is an array
    }   //End CC with content

    //Carbon-copied people
    if (!is_null($BCC))
    {
        if (!is_array($BCC))
        {
            //Is it a simple email?
            $Result = filter_var($BCC, FILTER_VALIDATE_EMAIL);
            if ($Result === FALSE)
            {
                $Message = 'BCC must be an email address or an array address => name';
                ErrorLog($Message, E_USER_ERROR);

                return FALSE;
            }
            else
            {
                //Yes it is. Let's construct the array based on the address
                $TempBCC = $BCC;
                $BCC = array($TempBCC => $TempBCC);
            }
        }
        else
        {
            foreach ($BCC as $Dir=>$Nombre)
            {
                if (!filter_var($Dir, FILTER_VALIDATE_EMAIL))
                {
                    $Message = 'Non-valid BCC address: '.$Address;
                    ErrorLog($Message, E_USER_ERROR);

                    return FALSE;
                }
            }
        }   //End BCC is an array
    }   //End BCC with content

    //Create a new PHPMailer instance
    $Email = new PHPMailer;
    $Email->CharSet = "UTF-8";

    //Set the error languaje
    if (defined("MIL_PHPMAILER_LANG") === TRUE)
    {
        $Email->setLanguage(MIL_PHPMAILER_LANG);
    }

    //Tell PHPMailer to use SMTP
    $Email->isSMTP();

    //Enable SMTP debugging
    // SMTP::DEBUG_OFF = off (for production use)
    // SMTP::DEBUG_CLIENT = client messages
    // SMTP::DEBUG_SERVER = client and server messages
    $Email->SMTPDebug = MIL_PHPMAILER_SMTPDEBUGGING;

    //Set the hostname of the mail server
    //$mail->Host = 'smtp.gmail.com';
    $Email->Host = MIL_PHPMAILER_SMTPHOST;
    // use
    // $mail->Host = gethostbyname('smtp.gmail.com');
    // if your network does not support SMTP over IPv6

    //Set the SMTP port number - 587 for authenticated TLS, a.k.a. RFC4409 SMTP submission
    $Email->Port = MIL_PHPMAILER_SMTPPORT;

    //Set the encryption mechanism to use - STARTTLS or SMTPS
    $Email->SMTPSecure = MIL_PHPMAILER_ENCRYPTION;

    //Whether to use SMTP authentication
    $Email->SMTPAuth = MIL_PHPMAILER_SMTPAUTH;

    //Username to use for SMTP authentication - use full email address for gmail
    $Email->Username = $Account;

    //Password to use for SMTP authentication
    $Email->Password = $Password;

    //Set who the message is to be sent from. Will fail if USERNAME <> user email address
    $Email->setFrom($Account, $FromName);

    //Set an alternative reply-to address...PTE DE IMPLEMENTAR
    //$mail->addReplyTo('replyto@example.com', 'First Last');

    //Set who the message is to be sent to
    foreach ($TO as $Dir=>$Nombre)
    {
        $Email->addAddress($Dir, $Nombre);
    }

    //CCs y BCCs
    if (!is_null($CC))
    {
        if (is_array($CC))
        {
            foreach ($CC as $Dir=>$Nombre)
            {
                $Email->addCC($Dir, $Nombre);
            }
        }
    }

    if (!is_null($BCC))
    {
        if (is_array($BCC))
        {
            foreach ($BCC as $Dir=>$Nombre)
            {
                $Email->addBCC($Dir, $Nombre);
            }
        }
    }

    //Set the subject line
    $Email->Subject = $Subject;

    //Set body and alt-body
    $Email->Body = $HTMLBody;
    $Email->AltBody = $TextBody;

    //send the message, check for errors
    if (!$Email->send())
    {
        $Message = 'Message could not be sent. Mailer Error: '.$Email->ErrorInfo;
        ErrorLog($Message, E_USER_ERROR);

        return FALSE;
    }
}

/**
 * CompleteHTMLEmail
 * Sends a simple email with no images or attachments. Credential are taken from defined constants
 * @param   mixed   $TO             Destination of the message. Either an email address or an array <address> => <name>
 * @param   string  $FromName       Sender name
 * @param   string  $Subject        Subject of the message
 * @param   string  $HTMLBody       Message body in HTML format.
 * @param   string  $TextBody       Message body in simple-text format. Optional
 * @param   array   $Images         Optional. A structure containing the images to attach following PHPMailer addEmbeddedImage structure
 *                                      'path'          Path to the attachment
 *                                      'cid'           Content ID of the attachment. To reference the content when using an embedded image in HTML
 *                                      'name'          Overrides the attachment name
 *                                      'encoding'      File encoding: '7bit', '8bit', 'base64' -default-, 'binary' or 'quoted-printable'
 *                                      'type'          File MIME type
 *                                      'disposition'   Disposition to use. In this use it will always be 'inline'
 * @param   array   $Attachments    Optional. Same structure as above. 'cid' is not used and 'disposition' will be 'attachment'
 * @param   mixed   $Tags           Optional. Either a string tag or an array of tags. Unicode chars will be escaped.
 * @param   mixed   $CC             Optional. Copy of de message. Either an email address or an array <address> => <name>
 * @param   mixed   $BCC            Optional. Carbon-copy of the message. Either an email address or an array <address> => <name>
 * @return  boolean TRUE on success, FALSE in other case
 * @since   0.0.9
 * @see     https://github.com/PHPMailer/PHPMailer/wiki/Tutorial#inline-attachments
 * @todo 
 */
function CompleteHTMLEmail($TO, $FromName, $Subject, $HTMLBody, $TextBody = NULL, $Images = NULL, $Attachments = NULL, $Tags = NULL, $CC = NULL, $BCC = NULL)
{
    //If credentials are not defined we have a problem
    if ((defined("MIL_PHPMAILER_USERNAME") === FALSE)||(defined("MIL_PHPMAILER_USERNAME") === FALSE))
    {
        $Message = 'Email credential constants MIL_PHPMAILER_USERNAME and MIL_PHPMAILER_PASSWORD must be defined.';
        ErrorLog($Message, E_USER_ERROR);

        return FALSE;
    }

    //We need email server parameters too!
    if ((defined("MIL_PHPMAILER_SMTPHOST") === FALSE)||(defined("MIL_PHPMAILER_SMTPPORT") === FALSE)||(defined("MIL_PHPMAILER_ENCRYPTION") === FALSE)
            ||(defined("MIL_PHPMAILER_SMTPAUTH") === FALSE))
    {
        $Message = 'The following server parameters must be defined:'.PHP_EOL.
                '    - MIL_PHPMAILER_SMTPHOST as hostname - such as smtp.gmail.com -'.PHP_EOL.
                '    - MIL_PHPMAILER_SMTPPORT as server port - usually 587 -'.PHP_EOL.
                '    - MIL_PHPMAILER_ENCRYPTION as encryption to use - usually PHPMailer::ENCRYPTION_STARTTLS which correspons to string "tls" -'.PHP_EOL.
                '    - MIL_PHPMAILER_SMTPAUTH to use authenticated SMPT -most of time should be TRUE -'.PHP_EOL;
        ErrorLog($Message, E_USER_ERROR);

        return FALSE;
    }

    //Finally, if no debug level is defined we set debugging to FALSE
    if (defined("MIL_PHPMAILER_SMTPDEBUGGING") === FALSE)
    {
        $Message = 'No debug level defined. Setting it to OFF';
        ErrorLog($Message, E_USER_NOTICE);

        define("MIL_PHPMAILER_SMTPDEBUGGING", SMTP::DEBUG_OFF);
    }

    //To parameter validation
    if (empty($TO))
    {
        $Message = 'At least one TO address is needed to send a message';
        ErrorLog($Message, E_USER_ERROR);

        return FALSE;
    }
    else
    {
        if (!is_array($TO))
        {
            //Is it a simple email?
            $Result = filter_var($TO, FILTER_VALIDATE_EMAIL);
            if ($Result === FALSE)
            {
                $Message = 'TO must be an email address or an array address => name';
                ErrorLog($Message, E_USER_ERROR);

                return FALSE;
            }
            else
            {
                //Yes it is. Let's construct the array based on the address
                $TempTO = $TO;
                $TO = array($TempTO => $TempTO);
            }
        }
        else
        {
            //It is an array
            foreach ($TO as $Address => $Name)
            {
                if (!filter_var($Address, FILTER_VALIDATE_EMAIL))
                {
                    $Message = 'Non-valid TO address: '.$Address;
                    ErrorLog($Message, E_USER_ERROR);

                    return FALSE;
                }
            }
        }   //End TO is an array
    }   //End TO is not empty

    //if no name we reuse the address
    if (!is_string($FromName)&&empty($FromName))
    {
        $FromName = MIL_PHPMAILER_USERNAME;
    }

    //Subject must have some info
    if (!is_string($Subject))
    {
        $Message = 'Empty message subject not allowed';
        ErrorLog($Message, E_USER_ERROR);

        return FALSE;
    }

    //Body must have some info
    if (!is_string($HTMLBody))
    {
        $Message = 'Empty message body not allowed';
        ErrorLog($Message, E_USER_ERROR);

        return FALSE;
    }

    //Add optional parts
    //Textual body if not present
    if (empty($TextBody))
    {
        //$TextBody = preg_replace( "/\n\s+/", "\n", rtrim(html_entity_decode(strip_tags($HTMLBody))));
        //The one used by PHPMailer
        //@see PHPMailer::html2text()
        $TextBody = html_entity_decode(trim(strip_tags(preg_replace('/<(head|title|style|script)[^>]*>.*?<\/\\1>/si', '', $HTMLBody))),
                ENT_QUOTES);
    }

    //Copied people
    if (!is_null($CC))
    {
        if (!is_array($CC))
        {
            //Is it a simple email?
            $Result = filter_var($CC, FILTER_VALIDATE_EMAIL);
            if ($Result === FALSE)
            {
                $Message = 'CC must be an email address or an array address => name';
                ErrorLog($Message, E_USER_ERROR);

                return FALSE;
            }
            else
            {
                //Yes it is. Let's construct the array based on the address
                $TempCC = $CC;
                $CC = array($TempCC => $TempCC);
            }
        }
        else
        {
            foreach ($CC as $Dir=>$Nombre)
            {
                if (!filter_var($Dir, FILTER_VALIDATE_EMAIL))
                {
                    $Message = 'Non-valid CC address: '.$Address;
                    ErrorLog($Message, E_USER_ERROR);

                    return FALSE;
                }
            }
        }   //End CC is an array
    }   //End CC with content

    //Carbon-copied people
    if (!is_null($BCC))
    {
        if (!is_array($BCC))
        {
            //Is it a simple email?
            $Result = filter_var($BCC, FILTER_VALIDATE_EMAIL);
            if ($Result === FALSE)
            {
                $Message = 'BCC must be an email address or an array address => name';
                ErrorLog($Message, E_USER_ERROR);

                return FALSE;
            }
            else
            {
                //Yes it is. Let's construct the array based on the address
                $TempBCC = $BCC;
                $BCC = array($TempBCC => $TempBCC);
            }
        }
        else
        {
            foreach ($BCC as $Dir=>$Nombre)
            {
                if (!filter_var($Dir, FILTER_VALIDATE_EMAIL))
                {
                    $Message = 'Non-valid BCC address: '.$Address;
                    ErrorLog($Message, E_USER_ERROR);

                    return FALSE;
                }
            }
        }   //End BCC is an array
    }   //End BCC with content

    //Create a new PHPMailer instance
    $Email = new PHPMailer;
    $Email->CharSet = "UTF-8";

    //Set the error languaje
    if (defined("MIL_PHPMAILER_LANG") === TRUE)
    {
        $Email->setLanguage(MIL_PHPMAILER_LANG);
    }

    //Tell PHPMailer to use SMTP
    $Email->isSMTP();

    //Enable SMTP debugging
    // SMTP::DEBUG_OFF = off (for production use)
    // SMTP::DEBUG_CLIENT = client messages
    // SMTP::DEBUG_SERVER = client and server messages
    $Email->SMTPDebug = MIL_PHPMAILER_SMTPDEBUGGING;

    //Set the hostname of the mail server
    //$mail->Host = 'smtp.gmail.com';
    $Email->Host = MIL_PHPMAILER_SMTPHOST;
    // use
    // $mail->Host = gethostbyname('smtp.gmail.com');
    // if your network does not support SMTP over IPv6

    //Set the SMTP port number - 587 for authenticated TLS, a.k.a. RFC4409 SMTP submission
    $Email->Port = MIL_PHPMAILER_SMTPPORT;

    //Set the encryption mechanism to use - STARTTLS or SMTPS
    $Email->SMTPSecure = MIL_PHPMAILER_ENCRYPTION;

    //Whether to use SMTP authentication
    $Email->SMTPAuth = MIL_PHPMAILER_SMTPAUTH;

    //Username to use for SMTP authentication - use full email address for gmail
    $Email->Username = MIL_PHPMAILER_USERNAME;

    //Password to use for SMTP authentication
    $Email->Password = MIL_PHPMAILER_PASSWORD;

    //Set who the message is to be sent from. Will fail if USERNAME <> user email address
    $Email->setFrom(MIL_PHPMAILER_USERNAME, $FromName);

    //Set an alternative reply-to address...PTE DE IMPLEMENTAR
    //$mail->addReplyTo('replyto@example.com', 'First Last');

    //Set who the message is to be sent to
    foreach ($TO as $Dir=>$Nombre)
    {
        $Email->addAddress($Dir, $Nombre);
    }

    //CCs y BCCs
    if (!is_null($CC))
    {
        if (is_array($CC))
        {
            foreach ($CC as $Dir=>$Nombre)
            {
                $Email->addCC($Dir, $Nombre);
            }
        }
    }

    if (!is_null($BCC))
    {
        if (is_array($BCC))
        {
            foreach ($BCC as $Dir=>$Nombre)
            {
                $Email->addBCC($Dir, $Nombre);
            }
        }
    }

    //Set the subject line
    $Email->Subject = $Subject;

    //Set body and alt-body
    $Email->Body = $HTMLBody;
    $Email->AltBody = $TextBody;

    /*
     * Set image attachments
     * Expected structure
     *    Array
     *    (
     *        [0] => Array
     *            (
     *                [0] => filepath
     *                [1] => cid
     *                [2] => name
     *                [3] => encoding
     *                [4] => type
     *            ),
     *        [1] => Array
     *            (
     *                [0] => filepath
     *                [1] => cid
     *                [2] => name
     *                [3] => encoding
     *                [4] => type
     *            )
     *    )
     */
    if (!empty($Images))
    {
        //print_r($Images);
        foreach ($Images as $Image)
        {
            //print_r($Image);
            $Path = $Image[0];
            if (!is_readable($Path))
            {
                $Message = 'Non-accesible image file: '.$Path;
                ErrorLog($Message, E_USER_ERROR);

                return FALSE;
            }

            $Cid = $Image[1];

            $Name = $Image[2];
            if (empty($Name))
            {
                $Name = MBPathInfo($Path, PATHINFO_BASENAME);
            }

            $Encoding = $Image[3];
            //echo 'Encoding is '.$Image[3].PHP_EOL;
            if (empty($Encoding))
            {
                $Encoding = 'base64';
            }
            if (($Encoding !== '7bit')&&($Encoding !== '8bit')&&($Encoding !== 'base64' )&&($Encoding !== 'binary' )
                    &&($Encoding !== 'quoted-printable'))
            {
                $Message = 'Unknown encoding: '.$Encoding;
                ErrorLog($Message, E_USER_ERROR);

                return FALSE;
            }


            $MimeType = $Image[4];
            if (empty($MimeType))
            {
                $MimeType = MimeType($Path);
            }
            $Disposition = 'inline';
            $Email->AddEmbeddedImage($Path, $Cid, $Name, $Encoding, $MimeType, $Disposition);
        }
    }


    /*
     * Set document attachments
     * Expected structure, same as image attachment (CID has no meaning)
     *    Array
     *    (
     *        [0] => Array
     *            (
     *                [0] => filepath
     *                [1] => cid
     *                [2] => name
     *                [3] => encoding
     *                [4] => type
     *            ),
     *        [1] => Array
     *            (
     *                [0] => filepath
     *                [1] => cid
     *                [2] => name
     *                [3] => encoding
     *                [4] => type
     *            )
     *    )
     */
    if (!empty($Attachments))
    {
        //print_r($Attachments);
        foreach ($Attachments as $Attachment)
        {
            $Path = $Attachment[0];
            if (!is_readable($Path))
            {
                $Message = 'Non-accesible image file: '.$Path;
                ErrorLog($Message, E_USER_ERROR);

                return FALSE;
            }

            $Name = $Attachment[2];
            if (empty($Name))
            {
                $Name = MBPathInfo($Path, PATHINFO_BASENAME);
            }

            $Encoding = $Attachment[3];
            if (empty($Encoding))
            {
                $Encoding = 'base64';
            }
            if (($Encoding !== '7bit')&&($Encoding !== '8bit')&&($Encoding !== 'base64' )&&($Encoding !== 'binary' )
                    &&($Encoding !== 'quoted-printable'))
            {
                $Message = 'Unknown encoding: '.$Encoding;
                ErrorLog($Message, E_USER_ERROR);

                return FALSE;
            }

            $MimeType = $Attachment[4];
            if (empty($MimeType))
            {
                $MimeType = MimeType($Path);
            }
            $Disposition = 'attachment';

            $Email->addAttachment($Path, $Name, $Encoding, $MimeType, $Disposition);
        }
    }

    /*
     * Add TAGS to the email a a JSON array
     * Expected structure
     * $Tags = ["Tag1", "Tag2", ... "Tagn"]
     */
    if (!empty($Tags))
    {
        $TheTags = array('Tags' => $Tags);
        $JSONTags = json_encode($TheTags);
        if ($JSONTags === FALSE)
        {
            //There was some problem with the tags. Raise a warning and continue
            $Message = 'Error encoding TAGS as JSON; No tags will be added to email';
            ErrorLog($Message, E_USER_WARNING);
        }
        else
        {
            //All sweet. Add the tags
            $Email->addCustomHeader('X-SMTPAPI: '.$JSONTags);
        }
    }

    //send the message, check for errors
    if (!$Email->send())
    {
        $Message = 'Message could not be sent. Mailer Error: '.$Email->ErrorInfo;
        ErrorLog($Message, E_USER_ERROR);

        return FALSE;
    }
}

function EnvioDirecto($TOs, $CCs, $BCC, $ElFrom, $ElFromName, $ElSubject,  $ElTexto, $ElHTML, $Categorias, $ImgAttaches, $Adjuntos, $Subuser, $Logger)
{

    //Flag para saber si alguna validacion falla
    $TodoBien = TRUE;
    $QueFallo = '';

    //Validación de parámetros obligatorios
    if (empty($TOs))
    {
        $TextoEvento = 'El To es obligatorio';
        echo $TextoEvento.PHP_EOL;
        $TodoBien = FALSE;
        $QueFallo .= $TextoEvento;
    }

    if (is_array($TOs))
    {
        foreach ($TOs as $Dir=>$Nombre)
        {
            if (!filter_var($Dir, FILTER_VALIDATE_EMAIL))
            {
                $TextoEvento = 'To email '.$Dir.' no es válida';
                echo $TextoEvento.PHP_EOL;
                $TodoBien = FALSE;
                $QueFallo .= $TextoEvento;
            }
            else
            {
                //Filtro direcciones '@contoso.com'
                $CachosEmail = explode('@', $Dir);
                $ElDominio = array_pop($CachosEmail);
                if ($ElDominio === 'contoso.com')
                {
                    $TextoEvento = $Dir.' pertenece a '.$ElDominio;
                    echo $TextoEvento.PHP_EOL;
                    $Logger->debug($TextoEvento, array('App'=>basename(__FILE__), 'F'=>__FUNCTION__));
                    //No lo envío pero no doy error
                    return TRUE;
                }
            }
        }
    }
    else
    {
        $TextoEvento = 'El To ahora es un array';
        echo $TextoEvento.PHP_EOL;
        $TodoBien = FALSE;
        $QueFallo .= $TextoEvento;
    }

    if (!is_null($CCs))
    {
        if (is_array($CCs))
        {
            foreach ($CCs as $Dir=>$Nombre)
            {
                if (!filter_var($Dir, FILTER_VALIDATE_EMAIL))
                {
                    $TextoEvento = 'CC email '.$Dir.' no es válida';
                    echo $TextoEvento.PHP_EOL;
                    $TodoBien = FALSE;
                    $QueFallo .= $TextoEvento;
                }
            }
        }
        else
        {
            $TextoEvento = 'El CC ahora es un array';
            echo $TextoEvento.PHP_EOL;
            $TodoBien = FALSE;
            $QueFallo .= $TextoEvento;
        }
    }

    if (!is_null($BCC))
    {
        if (is_array($BCC))
        {
            foreach ($BCC as $Dir=>$Nombre)
            {
                if (!filter_var($Dir, FILTER_VALIDATE_EMAIL))
                {
                    $TextoEvento = 'BCC email '.$Dir.' no es válida';
                    echo $TextoEvento.PHP_EOL;
                    $TodoBien = FALSE;
                    $QueFallo .= $TextoEvento;
                }
            }
        }
        else
        {
            $TextoEvento = 'El BCC ahora es un array';
            echo $TextoEvento.PHP_EOL;
            $TodoBien = FALSE;
            $QueFallo .= $TextoEvento;
        }
    }

    //El from ha de existir por narices
    if (!is_string($ElFrom)&&empty($ElFrom))
    {
        $TextoEvento = 'El FROM es obligatorio';
        echo $TextoEvento.PHP_EOL;
        $TodoBien = FALSE;
        $QueFallo .= $TextoEvento;
    }

    //El content ha de existir
    if (empty($ElTexto)&&empty($ElHTML))
    {
        $TextoEvento = 'El contenido es obligatorio';
        echo $TextoEvento.PHP_EOL;
        $TodoBien = FALSE;
        $QueFallo .= $TextoEvento;
    }

    //Empiezo con el mail
    if (!$TodoBien)
    {
        $TextoEvento = 'Faltan parámetros : '.$QueFallo;
        echo $TextoEvento.PHP_EOL;
        $Logger->error($TextoEvento, array('App'=>basename(__FILE__), 'F'=>__FUNCTION__));

        return FALSE;
    }

    //Preparo el email
    //Create a new PHPMailer instance
    $mail = new PHPMailer;

    //Tell PHPMailer to use SMTP
    $mail->isSMTP();


    //Desactivamos validación sólo para equipo de javier Aguilera para prueba de envio
    //Si ves este código activo, coméntalo!!!
    /*if (gethostname() === 'LAPTOP-5FEFEL07')
    {
        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer'       => FALSE,
                'verify_peer_name'  => FALSE,
                'allow_self_signed' => TRUE
            )
        );
    }*/
    //Si ves este código activo, coméntalo!!!


    //Enable SMTP debugging
    // SMTP::DEBUG_OFF = off (for production use)
    // SMTP::DEBUG_CLIENT = client messages
    // SMTP::DEBUG_SERVER = client and server messages
    $mail->SMTPDebug = SMTP::DEBUG_OFF;

    //Set the hostname of the mail server
    //$mail->Host = 'smtp.gmail.com';
    $mail->Host = 'smtp.office365.com';
    // use
    // $mail->Host = gethostbyname('smtp.gmail.com');
    // if your network does not support SMTP over IPv6

    //Set the SMTP port number - 587 for authenticated TLS, a.k.a. RFC4409 SMTP submission
    $mail->Port = 587;

    //Set the encryption mechanism to use - STARTTLS or SMTPS
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;

    //Whether to use SMTP authentication
    $mail->SMTPAuth = TRUE;

    //Username to use for SMTP authentication - use full email address for gmail
    $mail->Username = O365ALERTASUSR;

    //Password to use for SMTP authentication
    $mail->Password = O365ALERTASPW;

    //Set who the message is to be sent from creo que no funciona mas que con el mismo de las credenciales
    $mail->setFrom(O365ALERTASUSR, $ElFromName);

    //Set an alternative reply-to address...PTE DE IMPLEMENTAR
    //$mail->addReplyTo('replyto@example.com', 'First Last');

    //Set who the message is to be sent to
    foreach ($TOs as $Dir=>$Nombre)
    {
        $mail->addAddress($Dir, $Nombre);
    }

    //@todo: Faltan los CCs y BCCs
    if (!is_null($CCs))
    {
        if (is_array($CCs))
        {
            foreach ($CCs as $Dir=>$Nombre)
            {
                $mail->addCC($Dir, $Nombre);
            }
        }
    }

    if (!is_null($BCC))
    {
        if (is_array($BCC))
        {
            foreach ($BCC as $Dir=>$Nombre)
            {
                $mail->addBCC($Dir, $Nombre);
            }
        }
    }

    //
    //Set the subject line
    $mail->Subject = $ElSubject;

    //Read an HTML message body from an external file, convert referenced images to embedded,
    //convert HTML into a basic plain-text alternative body
    //$mail->msgHTML(file_get_contents('contents.html'), __DIR__);
    $mail->Body = $ElHTML;

    //Replace the plain text body with one created manually
    //$mail->AltBody = 'This is a plain-text message body';
    $mail->AltBody = $ElTexto;

    //Attach an image file. De SendGrid heredamos la estructura del array
    //Creamos el fichero de cada. En principio
    //https://github.com/PHPMailer/PHPMailer/wiki/Tutorial#inline-attachments
    if (!empty($ImgAttaches))
    {
        /*
        $attachments = [
            [   
                "attachment en base 64",
                "filename",
                "type",
                "disposition",
                "content id"
            ],
            [
                "base64 encoded content3",
                "banner3.gif",
                "image/gif",
                "inline",
                "Banner 3"
            ]
        ];
         * 
         */
        //El de SG $email->addAttachments($ImgAttaches);
        foreach ($ImgAttaches as $Indice => $Valor)
        {
            /*
            *    Array
            *    (
            *        [0] => Array
            *            (
            *                [0] => datosbase64
            *                [1] => image/png
            *                [2] => file
            *                [3] => inline
            *                [4] => elcid
            *            )
            *
            *    )
            */
            $Fichero = '/raid/TEMPO/'.$Indice.$Valor[2];
            file_put_contents($Fichero, base64_decode($Valor[0]));
            $Cid = $Valor[4];
            $mail->AddEmbeddedImage($Fichero, $Cid);
        }
    }

    //$mail->addAttachment('images/phpmailer_mini.png');

    //Los adjuntos no imagen
    if (!empty($Adjuntos))
    {
        foreach ($Adjuntos as $Indice => $Valor)
        {
            /*
            *    Array
            *    (
            *        [0] => Array
            *            (
            *                [0] => datosbase64
            *                [1] => image/png
            *                [2] => file
            *                [3] => inline
            *                [4] => elcid
            *            )
            *
            *    )
            */
            $Fichero = '/raid/TEMPO/'.$Indice.$Valor[2];
            file_put_contents($Fichero, base64_decode($Valor[0]));
            $Cid = $Valor[4];
            $mail->addAttachment($Fichero, $Valor[2], 'base64', $Valor[1]);
        }
    }
    //Adjunto las categorias como cabeceras a medida
    //De sendgriud heredamos un array de categorías
    if (!empty($Categorias))
    {
        /*
        $categories = [
            "Category 2",
            "Category 3"
        ];
         * 
         */
        //$email->addCategories($Categorias);
        $mail->addCustomHeader('X-SMTPAPI: '.json_encode(array('Categoria' => $Categorias)));
    }

    /*$mail->SMTPOptions = array(
        'ssl' => array(
            'verify_peer'       => FALSE,
            'verify_peer_name'  => FALSE,
            'allow_self_signed' => TRUE
        )
    );*/

    //send the message, check for errors
    if (!$mail->send())
    {
        //Control de errores transient
        //Detail: STOREDRV.Storage; mailbox server is too busy
        if (strpos($mail->ErrorInfo, 'Detail: STOREDRV.Storage; mailbox server is too busy') !== FALSE)
        {
            //Error transient
            $TextoEvento = 'Error temporal al enviar correo: '.$mail->ErrorInfo.'. Esperamos 30 segunditos';
            echo $TextoEvento.PHP_EOL;
            $Logger->info($TextoEvento, array('App'=>basename(__FILE__), 'F'=>__FUNCTION__));
            sleep(30);
            if (!$mail->send())
            {
                //echo 'Mailer Error: '.$mail->ErrorInfo;
                $TextoEvento = 'Error al enviar correo: '.$mail->ErrorInfo;
                echo $TextoEvento.PHP_EOL;
                $Logger->error($TextoEvento, array('App'=>basename(__FILE__), 'F'=>__FUNCTION__));

                return FALSE;
            }
            else
            {
                return TRUE;
            }
        }
        elseif (strpos($mail->ErrorInfo, 'Detail: STOREDRV.Submission.Exception:SubmissionQuotaExceededException;') !== FALSE)
        {
            //Ha habido tormenta de errores. Vamos a estar 24 horas sin enviar... devuelvo 666
            echo 'Ha habido tormenta de errores. Vamos a estar 24 horas sin enviar...'.PHP_EOL;

            return 666;
        }
        else
        {
            //echo 'Mailer Error: '.$mail->ErrorInfo;
            $TextoEvento = 'Error al enviar correo: '.$mail->ErrorInfo;
            echo $TextoEvento.PHP_EOL;
            $Logger->error($TextoEvento, array('App'=>basename(__FILE__), 'F'=>__FUNCTION__));

            return FALSE;
        }
    }
    else
    {
        //echo 'Message sent!';
        return TRUE;
        //Section 2: IMAP
    //Uncomment these to save your message in the 'Sent Mail' folder.
    //if (save_mail($mail)) {
    //    echo "Message saved!";
    //}
    }
}
