<?php

/*
 * MinionSetup
 * Minion Library Bootstrap and Settings
 * The library is a true swiss knife and it is so structured in blocks
 * All blocks are disabled by default, you must uncomment the block requires
 *  and decide on the define values you want
 * @author ProceduralMan <proceduralman@gmail.com>
 * @copyright 2021
 * @version 1.0 initial version
 * @package Minion
 * @todo 
 */

// General settings
// Debug mode: shows informative messages through the logic path
define("DEBUGMODE", FALSE);
// Credentials keeping
//  INCODE      Credentials are kept in the code (Unsafe!!)
//  AZUREHSM    Credentials are kept in an Azure KeyVault (Much safer!!)
define("CC", "INCODE");

/*
 * Error Handling
 * This block sets up a number of error handling functions
 * aimed to get comprehensive error info on one place.
 * Although it is not forced -you can notify the error whenever you like-
 * It is thought to define a bubble-up error handling strategy
 * on which errors are registered as they appear and notified on the upper level
 * That way it is easier to avoid the classical error storm that happens when 
 * something goes wrong deep inside our nested logic 
 * To use: 
 *  1)  register the logger using RegisterLogger($Channel, $Destination, $Formatter, $Helper)
 *      Do not combine destination and formatter flags -you can find them in ErrorHandling.php
 *      Helper flags can be combined to add contextual info to the record
 *  2)  Call LogData($LogLevel, $EventText) @todo HACER LOGDATA... aplicar logdata y re-probar trazado
 */

//**ALWAYS REQUIRED**
require_once 'Configuration/Configuration.php';
require_once 'ErrorHandling/ErrorLogging.php';

// **RUNTIME CONFIGURATION** Uncomment to activate.
require_once 'Configuration/RuntimeConfig.php';



//***ERROR LOGGING-RELATED CONFIGURATION CONSTANTS***
//Timezone to convert dates to. Any of PHP's supported ones https://www.php.net/manual/en/timezones.php
Defaults("MIL_LOCALTZ", "Europe/Madrid");

// **ERROR HANDLING** Uncomment to activate
require_once 'ErrorHandling/ErrorHandling.php';

// **HELPER FUNCTIONS** Uncomment to activate
require_once 'TextAndString/TextAndString.php';
require_once 'DateAndTime/DateAndTime.php';
require_once 'Mathematical/Mathematical.php';
require_once 'DataStructures/DataStructures.php';

// **SQL CONNECTION SUPPORT** Uncomment to activate. Requires TextAndString and DataStructures
require 'Database/SQLDB.php';

// **DATA VALIDATION SUPPORT** Uncomment to activate
require_once 'DataValidation/DataValidation.php';
require_once 'DataPlugs/Internet.php';

//***DATA PLUGS-RELATED CONFIGURATION CONSTANTS***
//Location to store Data Plug CSVs
Defaults("MIL_DATA_PLUG_STORAGE", "/home/masmanda");

// **FILESYSTEM FUNCTIONS** Uncomment to activate
require_once 'FileSystem/FileSystem.php';

// **CACHING SUPPORT** Uncomment to activate. Requires DateAndTime
require_once 'Cache/Caching.php';
//***CACHE-RELATED CONFIGURATION CONSTANTS***
//Use of ACPU or REDIS caches..
//ACPU uses an auto-setter function when included
define("MIL_REDIS", FALSE);             //STILL unsuported... uneffectual
define("MIL_APCUPERSISTLAPSE", 600);   //10 minutes to persist caches into the database

// **EMAILING SUPPORT** Uncomment to activate. Requires FileSystem
require_once 'Emailing/Emailing.php';
//***EMAILING-RELATED CONFIGURATION CONSTANTS***
//Enable SMTP debugging
// Defaults("MIL_PHPMAILER_SMTPDEBUGGING", 0); SMTP::DEBUG_OFF or 0 for no output
// Defaults("MIL_PHPMAILER_SMTPDEBUGGING", 1); SMTP::DEBUG_CLIENT or 1 to show client -> server messages
// Defaults("MIL_PHPMAILER_SMTPDEBUGGING", 2); SMTP::DEBUG_SERVER or 2 to show client -> server and server -> client messages
// Defaults("MIL_PHPMAILER_SMTPDEBUGGING", 3); SMTP::DEBUG_CONNECTION or 3 to show connection status, client -> server and server -> client messages
// Defaults("MIL_PHPMAILER_SMTPDEBUGGING", 4); SMTP::DEBUG_LOWLEVEL or 4 to all messages
Defaults("MIL_PHPMAILER_SMTPDEBUGGING", 0);

//Set the hostname of the mail server
//GMAIL                                     'smtp.gmail.com';
//MICROSOFT365, OUTLOOK, HOTMAIL, LIVE.COM  'smtp.office365.com';
//MSN                                       'smtp-mail.outlook.com';
//YAHOO                                     'smtp.mail.yahoo.com';
//GODADDY                                   'smtpout.secureserver.net';
//GANDI                                     'mail.gandi.net';
//IONOS                                     'smtp.ionos.es';
//Defaults("MIL_PHPMAILER_SMTPHOST", "smtp.gmail.com");
//Defaults("MIL_PHPMAILER_SMTPHOST", "smtp.office365.com");
//Defaults("MIL_PHPMAILER_SMTPHOST", "smtp-mail.outlook.com");
//Defaults("MIL_PHPMAILER_SMTPHOST", "smtp.mail.yahoo.com");
//Defaults("MIL_PHPMAILER_SMTPHOST", "smtpout.secureserver.net");
//Defaults("MIL_PHPMAILER_SMTPHOST", "mail.gandi.net");
Defaults("MIL_PHPMAILER_SMTPHOST", "smtp.ionos.es");

//Set the SMTP port number
//587 for authenticated TLS, a.k.a. RFC4409 SMTP submission. UNIVERSALLY SUPPORTED. Must use ENCRYPTION_STARTTLS as MIL_PHPMAILER_ENCRYPTION
//465 for authenticated SSL (YAHOO, GODADDY, GANDI). Must use ENCRYPTION_SMTPS as MIL_PHPMAILER_ENCRYPTION
//Defaults("MIL_PHPMAILER_SMTPPORT", 465);
Defaults("MIL_PHPMAILER_SMTPPORT", 587);

//Set the encryption mechanism to use - STARTTLS or SMTPS
//Defaults("MIL_PHPMAILER_ENCRYPTION", PHPMailer::ENCRYPTION_SMTPS); //or 'ssl' for SSL
//Defaults("MIL_PHPMAILER_ENCRYPTION", PHPMailer::ENCRYPTION_STARTTLS); //or 'tls' for TLS
Defaults("MIL_PHPMAILER_ENCRYPTION", "tls");

//Whether to use SMTP authentication. Nowadays all publics servers are authenticated, so this should always be TRUE
//Defaults("MIL_PHPMAILER_SMTPAUTH", FALSE);
Defaults("MIL_PHPMAILER_SMTPAUTH", TRUE);

//User and password. Set those defines somewhere on your code to use the "Default" functions (citar)
//DO NOT ENABLE HERE OR THEY WILL BE OVERWRITTEN ON LIBRARY UPDATES
//You can alternatively use the "Cred" functions that carry username and password as parameter. (citar)
//Remember google and yahoo uses not the acount password but a secondary one you must set on the platform
//  Google: https://support.google.com/mail/answer/185833?hl=en
//  Yahoo:  https://www.saleshandy.com/smtp/yahoo-smtp-settings/
//define ("MIL_PHPMAILER_USERNAME", "myusername");
//define ("MIL_PHPMAILER_PASSWORD", "mypassword");

//Localized error messages. Set that also somewhere in your multi-language code so you can adapt the messages to your audience
//define("MIL_PHPMAILER_LANG", 'af');       //Afrikaans
//define("MIL_PHPMAILER_LANG", 'ar');       //Arab
//define("MIL_PHPMAILER_LANG", 'az');       //Azeri
//define("MIL_PHPMAILER_LANG", 'ba');       //Bashkir
//define("MIL_PHPMAILER_LANG", 'be');       //Belarusian
//define("MIL_PHPMAILER_LANG", 'bg');       //Bulgarian
//define("MIL_PHPMAILER_LANG", 'ca');       //Catalan
//define("MIL_PHPMAILER_LANG", 'ch');       //Chamorro
//define("MIL_PHPMAILER_LANG", 'cs');       //Czech
//define("MIL_PHPMAILER_LANG", 'da');       //Danish
//define("MIL_PHPMAILER_LANG", 'de');       //German
//define("MIL_PHPMAILER_LANG", 'el');       //Greek
//define("MIL_PHPMAILER_LANG", 'eo');       //Esperanto
//define("MIL_PHPMAILER_LANG", 'es');       //Spanish
//define("MIL_PHPMAILER_LANG", 'et');       //Estonian
//define("MIL_PHPMAILER_LANG", 'fa');       //Persian
//define("MIL_PHPMAILER_LANG", 'fi');       //Finnish
//define("MIL_PHPMAILER_LANG", 'fo');       //Faroese
//define("MIL_PHPMAILER_LANG", 'fr');       //French
//define("MIL_PHPMAILER_LANG", 'gl');       //Galician
//define("MIL_PHPMAILER_LANG", 'he');       //Hebrew
//define("MIL_PHPMAILER_LANG", 'hi');       //Hindi
//define("MIL_PHPMAILER_LANG", 'hr');       //Croatian
//define("MIL_PHPMAILER_LANG", 'hu');       //Hungarian
//define("MIL_PHPMAILER_LANG", 'hy');       //Armenian
//define("MIL_PHPMAILER_LANG", 'id');       //Indonesian
//define("MIL_PHPMAILER_LANG", 'it');       //Italian
//define("MIL_PHPMAILER_LANG", 'ja');       //Japanese
//define("MIL_PHPMAILER_LANG", 'ka');       //Georgian
//define("MIL_PHPMAILER_LANG", 'ko');       //Korean
//define("MIL_PHPMAILER_LANG", 'lt');       //Lithuanian
//define("MIL_PHPMAILER_LANG", 'lv');       //Latvian
//define("MIL_PHPMAILER_LANG", 'mg');       //Malagasy
//define("MIL_PHPMAILER_LANG", 'ms');       //Malay
//define("MIL_PHPMAILER_LANG", 'nb');       //Norwegian Bokmål
//define("MIL_PHPMAILER_LANG", 'nl');       //Dutch, Flemish
//define("MIL_PHPMAILER_LANG", 'pt');       //Portuguese
//define("MIL_PHPMAILER_LANG", 'pt_br');    //Portuguese (Brazil)
//define("MIL_PHPMAILER_LANG", 'ro');       //Romanian, Moldavian, Moldovan
//define("MIL_PHPMAILER_LANG", 'ru');       //Russian
//define("MIL_PHPMAILER_LANG", 'sk');       //Slovak
//define("MIL_PHPMAILER_LANG", 'sl');       //Slovenian
//define("MIL_PHPMAILER_LANG", 'sr');       //Serbian
//define("MIL_PHPMAILER_LANG", 'sr_latn');  //Serbian Latin
//define("MIL_PHPMAILER_LANG", 'sv');       //Swedish
//define("MIL_PHPMAILER_LANG", 'tl');       //Tagalog
//define("MIL_PHPMAILER_LANG", 'tr');       //Turkish
//define("MIL_PHPMAILER_LANG", 'uk');       //Ukrainian
//define("MIL_PHPMAILER_LANG", 'vi');       //Vietnamese
//define("MIL_PHPMAILER_LANG", 'zh');       //Chinese
//define("MIL_PHPMAILER_LANG", 'zh_cn');    //Chinese (PRC)

// **WEBSERVICE CALLING** Uncomment to activate. Requires Runtime Configuration and DataValidation
require_once 'Interconnection/cURL.php';
//***CURL-RELATED CONFIGURATION CONSTANTS***
Defaults("CURLOPT_POSTFIELDSIZE", 60);
Defaults("CURLOPT_CURLU", 282);
Defaults("CURLOPT_MIME_OPTIONS", 315);
Defaults("CURLOPT_WS_OPTIONS", 320);
Defaults("CURLOPT_CA_CACHE_TIMEOUT", 321);
Defaults("CURLOPT_QUICK_EXIT", 322);
Defaults("CURLOPT_WRITEDATA", 10001);
Defaults("CURLOPT_ERRORBUFFER", 10010);
Defaults("CURLOPT_HTTPPOST", 10024);
Defaults("CURLOPT_HEADERDATA", 10029);
Defaults("CURLOPT_PROGRESSDATA", 10057);
Defaults("CURLOPT_XFERINFODATA", 10057);
Defaults("CURLOPT_DEBUGDATA", 10095);
Defaults("CURLOPT_SSL_CTX_DATA", 10109);
Defaults("CURLOPT_IOCTLDATA", 10131);
Defaults("CURLOPT_SOCKOPTDATA", 10149);
Defaults("CURLOPT_OPENSOCKETDATA", 10164);
Defaults("CURLOPT_POSTREDIR", 10165);
Defaults("CURLOPT_SEEKDATA", 10168);
Defaults("CURLOPT_SSH_KEYDATA", 10185);
Defaults("CURLOPT_INTERLEAVEDATA", 10195);
Defaults("CURLOPT_CHUNK_DATA", 10201);
Defaults("CURLOPT_FNMATCH_DATA", 10202);
Defaults("CURLOPT_CLOSESOCKETDATA", 10209);
Defaults("CURLOPT_STREAM_DEPENDS", 10240);
Defaults("CURLOPT_STREAM_DEPENDS_E", 10241);
Defaults("CURLOPT_MIMEPOST", 10269);
Defaults("CURLOPT_RESOLVER_START_DATA", 10273);
Defaults("CURLOPT_TRAILERDATA", 10284);
Defaults("CURLOPT_HSTSREADDATA", 10302);
Defaults("CURLOPT_HSTSWRITEDATA", 10304);
Defaults("CURLOPT_PREREQDATA", 10313);
Defaults("CURLOPT_SSH_HOSTKEYFUNCTION", 10316);
Defaults("CURLOPT_SSH_HOSTKEYDATA", 10317);
Defaults("CURLOPT_PROTOCOLS_STR", 10318);
Defaults("CURLOPT_REDIR_PROTOCOLS_STR", 10319);
Defaults("CURLOPT_DEBUGFUNCTION", 20094);
Defaults("CURLOPT_SSL_CTX_FUNCTION", 20108);
Defaults("CURLOPT_IOCTLFUNCTION", 20130);
Defaults("CURLOPT_CONV_FROM_NETWORK_FUNCTION", 20142);
Defaults("CURLOPT_CONV_TO_NETWORK_FUNCTION", 20143);
Defaults("CURLOPT_CONV_FROM_NETWORK_FUNCTION", 20144);
Defaults("CURLOPT_SOCKOPTFUNCTION", 20148);
Defaults("CURLOPT_OPENSOCKETFUNCTION", 20163);
Defaults("CURLOPT_SEEKFUNCTION", 20167);
Defaults("CURLOPT_SSH_KEYFUNCTION", 20184);
Defaults("CURLOPT_INTERLEAVEFUNCTION", 20196);
Defaults("CURLOPT_CHUNK_BGN_FUNCTION", 20198);
Defaults("CURLOPT_CHUNK_END_FUNCTION", 20199);
Defaults("CURLOPT_CLOSESOCKETFUNCTION", 20208);
Defaults("CURLOPT_RESOLVER_START_FUNCTION", 20272);
Defaults("CURLOPT_TRAILERFUNCTION", 20283);
Defaults("CURLOPT_HSTSREADFUNCTION", 20301);
Defaults("CURLOPT_HSTSWRITEFUNCTION", 20303);
Defaults("CURLOPT_PREREQFUNCTION", 20312);
Defaults("CURLOPT_INFILESIZE_LARGE", 30115);
Defaults("CURLOPT_RESUME_FROM_LARGE", 30116);
Defaults("CURLOPT_POSTFIELDSIZE_LARGE", 30120);

// **RENEWABLES MONITORING** Uncomment to activate. Requires Interconnection (WEBSERVICE CALLING)
require_once 'Renewables/SolarEdge.php';
Defaults("MIL_SEBASEURL", 'https://monitoringapi.solaredge.com/');
