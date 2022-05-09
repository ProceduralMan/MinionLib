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

//**REQUIRED**
require_once 'Configuration/Configuration.php';
require_once 'ErrorHandling/ErrorLogging.php';
//***ERROR LOGGING-RELATED CONFIGURATION CONSTANTS***
//Timezone to convert dates to. Any of PHP's supported ones https://www.php.net/manual/en/timezones.php
Defaults("LOCALTZ", "Europe/Madrid");

//Uncomment to activate
require_once 'ErrorHandling/ErrorHandling.php';
//Uncomment to activate
require_once 'TextAndString/TextAndString.php';
require_once 'DateAndTime/DateAndTime.php';
require_once 'Mathematical/Mathematical.php';
require_once 'DataStructures/DataStructures.php';
//Uncomment to activate. Requires TextAndString and DataStructures
require 'Database/SQLDB.php';
//Uncomment to activate
require_once 'DataValidation/DataValidation.php';
require_once 'DataPlugs/Internet.php';
//***DATA PLUGS-RELATED CONFIGURATION CONSTANTS***
//Location to store Data Plug CSVs
Defaults("DATA_PLUG_STORAGE", "/home/masmanda");


//Uncomment to activate. Requires DateAndTime
require_once 'Cache/Caching.php';
//***CACHE-RELATED CONFIGURATION CONSTANTS***
//Use of ACPU or REDIS caches..
//ACPU uses an auto-setter function when included
define("MIL_REDIS", FALSE);             //STILL unsuported... uneffectual
define("MIL_APCUPERSISTLAPSE", 600);   //10 minutes to persist caches into the database

//Do not forget to set the required values -see ErrorHandling.php for explanation-
//Channel Option...
//define("CHANNEL", "FILES"); //Can be FILES, EMAIL, SMS, IM, FLOWAUTO, NETLOGGING, QUEUES, DB, DEVLOGGING

//Choosen Processors for each channel. Uncomment as needed and set one of the Channel flags present on ErrorHandling.php
//File
//define("FILEPROCESSOR",FILE);               //FILE||SYSLOG||ERRORLOG||PROCESSLOG
//Email
//define ("EMAILPROCESSOR","NATIVEMAILER"); //NATIVEMAILER||SWIFTMAILER||SENDGRID||MAILJET||MANDRILL
//SMS
//define ("SMSPROCESSOR","AGILE");          //AGILE
//Push
//define ("PUSHPROCESSOR","PUSHOVER");      //PUSHOVER
//IM
//define ("IMPROCESSOR","FLOWDOC");         //FLOWDOC||SLACK||FLEEP||TELEGRAM||LINE||WECHAT||DISCORD
//Flow Automation
//define ("FLOWPROCESSOR","IFTT");          //IFTT||ZAPIER||INTEGRATELY||MULESOFT
//Netlogging
//define ("NETPROCESSOR","SOCKET");         //SOCKET||GRAYLOG||CUBE||ZENDMONITOR||NEWRELIC||LOGGLY||ROLLBAR||SYSLOGD||LOGENTRIES||INSIGTHOPS||LOGMATIC
//Queues
//define ("QUEUEPROCESSOR","AWSSQS");       //AWSSQS||AZSTQUEUE||AZSRVBUS
//DBs
//define ("DBPROCESSOR","AWSSQS");          //REDISRPUSH||REDISPUBLISH||MONGODB||COUCHDB||ELASTICS||DYNAMODB
//Dev
//define ("DEVPROCESSOR","CONSOLE");        //CONSOLE||CHROMELGR



//Bootstraping
//This contravenes PSR rules but alas...
//$ActiveModules = get_included_files(); no hace falta de momento
//Error handling bootstraping
//if (in_array('ErrorHandling.php', $ActiveModules))
//{
    //Bootstraping de errorhandling... esto corre en el include por lo que no puedo chequear path del fichero
    //Debo hacer un registerchannel que haga el sanitycheck a demanda del usuario (en su código) y quitarme los defines del setup
    //      aditivo... RegisterChannel($Channel, $Processor, $Formatter)
    //      Y sanity check en las funciones principales -Process y Notify para avisar de mala configuración
//}
