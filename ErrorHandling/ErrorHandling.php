<?php

/*
 * ErrorHandling
 * Error Handling the Minion way
 *
 * Logic Flow, -> means call; events marked by <>
 * CASE 1:  CONTROLLED ERRORS
 *          a()->b()
 *              b()->c()
 *                  c()executes... 
 *                      <controlled error> c()->ProcessError()
 *                      c()->Returns error code
 *              b()->ProcessError()
 *              b()->Returns error code
 *          a()->ProcessError()
 *          a()->NotifyError()
 * CASE 2:  UNCONTROLLED ERRORS
 *          a()->b()
 *              b()->c()
 *                  c()executes... 
 *                      <uncontrolled error> c()->CustomErrorProcessor()
 *                          CustomErrorProcessor()->ProcessError()
 *                          CustomErrorProcessor()->NotifyError()
 *                          <program continues>
 * CASE 3:  FATAL ERRORS
 *          a()->b()
 *              b()->c()
 *                  c()executes... 
 *                      <fatal error> c()->FatalErrorProcessor()
 *                          FatalErrorProcessor()->ProcessError()
 *                          FatalErrorProcessor()->NotifyError()
 *                          exit
 * @author ProceduralMan <proceduralman@gmail.com>
 * @copyright 2021
 * @version 1.0 initial version
 * @package Minion
 * @todo 
 * @see  
 */

//ERROR LEVEL DEFINES AND GLOBAL OutData for fatal error
define("DEBUG", 100);       //Debug-level messages
define("INFO", 200);        //Informational messages
define("NOTICE", 250);      //Normal but significant conditions
define("WARNING", 300);     //Warning conditions
define("ERROR", 400);       //Error conditions
define("CRITICAL", 500);    //Critical conditions
define("ALERT", 550);       //Action must be taken immediately
define("EMERGENCY", 600);   //System is unusable

//To disappear
$GLOBALS['OutData'] = NULL;
//Logic heap unsettable everywhere, as per https://www.php.net/manual/es/function.unset.php
$GLOBALS['LogicHeap'] = NULL;
//Registered logger info
$GLOBALS['Loggers'] = NULL;
//Combined helpers
$GLOBALS['CombinedHelpers'] = NULL;

/*
 * Error handlers
 * Due to the wealth of available options it's clear that we would run out of flags on 64 Bytes systems pretty soon
 * Thus 3 level of flags are defined. A tuple of Channel, Destination, Processor and Helpers are defined via RegisterLogger
 * Any number of Loggers can be registered and will be honored
 * "Channel" flags denotes the way to log. Cannot be combined. Currently FILES, EMAIL, SMS, IM, FLOWAUTO, NETLOGGING, QUEUES, DB, DEVLOGGING
 * "Processor" flags for each channel sets a processor.
 * There are three constants, ...PROCESSOR1, ...PROCESSOR2 and ...PROCESSOR3 allow to fix up to three processors for each Channel
 * A "Strategy" flag for each channel allows to fix the working strategy
 * Current strategies are:
 *  - ALL       writes to all processors defined (non empty)
 *  - PRIORITY  writes to first one, and only if it fails tryes second or third one
 */
//Channel Flags
define("MIL_FILES",         "FILES");       // Logs records to Files
define("MIL_EMAIL",         "EMAIL");       // Logs records to Email
define("MIL_SMS",           "SMS");         // Logs records to SMS
define("MIL_IM",            "IM");          // Logs records to Instant Messengers
define("MIL_FLOWAUTO",      "FLOWAUTO");    // Logs records to FlowAuto
define("MIL_NETLOGGING",    "NETLOGGING");  // Logs records to Net services
define("MIL_QUEUES",        "QUEUES");      // Logs records to queues
define("MIL_DB",            "DB");          // Logs records to database
define("MIL_DEVLOGGING",    "DEVLOGGING");  // Logs records to dev tools

//Flags per channel FILES, EMAIL, SMS, IM, FLOWAUTO, NETLOGGING, QUEUES, DB, DEVLOGGING
//Muchos de https://github.com/Seldaek/monolog/blob/main/doc/02-handlers-formatters-processors.md
//Destinations
//Files
define("MIL_FILE",         0);  // Logs records into a FILE, use this for log files
define("MIL_SYSLOG",       10); // Logs records to the syslog
define("MIL_ERROLOG",      20); // Logs records to PHP's error_log() function
define("MIL_PROCESSLOG",   30); // Logs records to the STDIN of any process, specified by a command
//Email
define("MIL_NATIVEMAILER", 0b0000000000000001); // 1   Sends emails using PHP's mail() function
define("MIL_SWIFTMAILER",  0b0000000000000010); // 2   Sends emails using a Swift_Mailer instance
define("MIL_SENDGRID",     0b0000000000000100); // 4   Sends emails using SendGrid API V3
define("MIL_MAILJET",      0b0000000000001000); // 8   Sends emails using MailJet API Vx
define("MIL_MANDRILL",     0b0000000000001000); // 16  Sends emails via the Mandrill API using a Swift_Message instance
//SMS
define("MIL_AGILE",        0b0000000000000001); // 1   Sends SMS via Agile API
//Notificaciones PUSH
define("MIL_PUSHOVER",     0b0000000000000001); // 1   Sends mobile notifications via the Pushover API
//IM
//@todo aggregate using two defines: IM + IMprocessor
define("MIL_FLOWDOC",      0b0000000000000001); // 1   Logs records to FlowDock
define("MIL_SLACK",        0b0000000000000010); // 2   Logs records to Slack
define("MIL_FLEEP",        0b0000000000000100); // 4   Logs records to Fleep
define("MIL_TELEGRAM",     0b0000000000001000); // 8   Logs records to Telegram
define("MIL_LINE",         0b0000000000010000); // 16  Logs records to Line
define("MIL_WECHAT",       0b0000000000100000); // 32  Logs records to WeChat
define("MIL_DISCORD",      0b0000000001000000); // 64  Logs records to Discord
//Flow Automation
define("MIL_IFTT",         0b0000000000000001); // 1   Notifies an IFTTT trigger with the log channel, level name and message
define("MIL_ZAPIER",       0b0000000000000010); // 2   Notifies an Zapier trigger, TBD
define("MIL_INTEGRATELY",  0b0000000000000100); // 4   Notifies an Integrately trigger, TBD
define("MIL_MULESOFT",     0b0000000000001000); // 8   Notifies an Mulesoft trigger, TBD
//Netlogging
define("MIL_SOCKET",       0b0000000000000010); // 1       Logs records to sockets, use this for UNIX and TCP sockets
define("MIL_GRAYLOG",      0b0000000000000100); // 2       Logs records to a Graylog2 server
define("MIL_CUBE",         0b0000000000001000); // 4       Logs records to a Cube server.
define("MIL_ZENDMONITOR",  0b0000000000010000); // 8       Logs records to the Zend Monitor present in Zend Server
define("MIL_NEWRELIC",     0b0000000000100000); // 16      Logs records to a NewRelic application
define("MIL_LOGGLY",       0b0000000001000000); // 32      Logs records to a Loggly account
define("MIL_ROLLBAR",      0b0000000010000000); // 64      Logs records to a Rollbar account
define("MIL_SYSLOGD",      0b0000000100000000); // 128     Logs records to a remote Syslogd server.
define("MIL_LOGENTRIES",   0b0000001000000000); // 256     Logs records to a LogEntries account
define("MIL_INSIGTHOPS",   0b0000010000000000); // 512     Logs records to an InsightOps account.
define("MIL_LOGMATIC",     0b0000100000000000); // 1024    Logs records to a Logmatic account
define("MIL_LOGSTASH",     0b0001000000000000); // 2048    Logs records to a LogStash account

//Queues
define("MIL_AWSSQS",       0b0000000000000001); // 1   Logs records to an AWS SQS queue
define("MIL_AZSTQUEUE",    0b0000000000000010); // 2   Logs records to an Azure Storage Queue TBD
define("MIL_AZSRVBUS",     0b0000000000000100); // 4   Logs records to an Azure Service Bus Queue TBD
//DBs
define("MIL_REDISRPUSH",   0b0000000000000001); // 1   Logs records to a redis server's key via RPUSH
define("MIL_REDISPUBLISH", 0b0000000000000010); // 2   Logs records to a redis server's channel via PUBLISH
define("MIL_MONGODB",      0b0000000000000100); // 4   Logs records to a MongoDB server.
define("MIL_COUCHDB",      0b0000000000001000); // 8   Logs records to a CouchDB server.
define("MIL_ELASTICS",     0b0000000000010000); // 16  Logs records to an Elasticsearch server
define("MIL_DYNAMODB",     0b0000000000100000); // 32  Logs records to an DynamoDB server
//Dev
define("MIL_CONSOLE",      0b0000000000000001); // 1   Send logs to browser's Javascript console with no browser extension required
define("MIL_FIREPHP",      0b0000000000000010); // 2   Send logs to FirePHP/FireBug
define("MIL_CHROMELGR",    0b0000000000000100); // 4   Send logs to ChromeLogger (aka ChromePHP), providing inline console messages within Chrome

//Formatters are special to each Handler
define("MIL_ONELINEFORMATTER",      0);     // Formats a log record into a one-line string. Eliminates EOLs
define("MIL_LINEFORMATTER",         10);    // Formats a log record into a one-line string. Keeps EOLs
define("MIL_HTMLFORMATTER",         20);    // Used to format log records into a human readable html table, mainly suitable for emails.
define("MIL_NORMALIZERFORMATTER",   30);    // Normalizes objects/resources down to strings so a record can easily be serialized/encoded.
define("MIL_SCALARFORMATTER",       40);    // Used to format log records into an associative array of scalar values.
define("MIL_JSONFORMATTER",         50);    // Encodes a log record into json.
define("MIL_WILDFIREFORMATTER",     60);    // Used to format log records into the Wildfire/FirePHP protocol, only useful for the FirePHPHandler.
define("MIL_CHROMELOGGERFORMATTER", 70);    // Used to format log records into the ChromeLogger format, only useful for the CHROMELGR.
define("MIL_GELFMESSAGEFORMATTER",  80);    // Used to format log records into Gelf message instances, only useful for the MIL_GRAYLOG.
define("MIL_LOGSTASHFORMATTER",     90);    // Used to format log records into logstash event json, useful for MIL_LOGSTASH.
define("MIL_ELASTICAFORMATTER",     100);   // Used to format log records into an Elastica\Document object, only useful for the MIL_ELASTICS.
define("MIL_LOGGLYFORMATTER",       110);   // Used to format log records into Loggly messages, only useful for the MIL_LOGGLY.
define("MIL_FLOWDOCFORMATTER",      120);   // Used to format log records into Flowdock messages, only useful for the MIL_FLOWDOC.
define("MIL_MONGODBFORMATTER",      130);   // Converts \DateTime instances to \MongoDate and objects recursively to arrays, only useful with the MIL_MONGODB.
define("MIL_LOGMATICFORMATTER",     140);   // Used to format log records to Logmatic messages, only useful for the MIL_LOGMATIC.
define("MIL_FLUENTDFORMATTER",      150);   // Used to format log records to Fluentd logs, only useful with the MIL_SOCKET.

//Separators
define("MIL_TABSEPARATOR", "\t");      //Fields separed by TAB
define("MIL_CMMSEPARATOR", ",");       //Fields separated by comma
define("MIL_SCNSEPARATOR", ";");       //Fields separated by semicolon
define("MIL_HSHSEPARATOR", "#");       //Fields separated by hash sign

//Context Helpers (Processors en Monolog)
define("MIL_CODECONTEXT",              0b0000000000000001); // 1   Adds the line/file/class/method from which the log call originated.
define("MIL_WEBCONTEXT",               0b0000000000000010); // 2   Adds the current request URI, request method and client IP to a log record.
define("MIL_MEMUSE",                   0b0000000000000100); // 4   Adds the current memory usage to a log record.
define("MIL_MEMPEAK",                  0b0000000000001000); // 8   Adds the peak memory usage to a log record.
define("MIL_PIDCONTEXT",               0b0000000000010000); // 16  Adds the process id to a log record.
define("MIL_ADDUID",                   0b0000000000100000); // 32  Adds a unique identifier to a log record.
define("MIL_GITCONTEXT",               0b0000000001000000); // 64  Adds the current git branch and commit to a log record.
define("MIL_TAGCONTEXT",               0b0000000010000000); // 128 Adds an array of predefined tags to a log record.
define("MIL_HOSTNAMECONTEXT",          0b0000000100000000); // 256 Adds the current hostname to a log record.

/**
 * RegisterLogger registers a Logger
 * A logger is a combination of a Channel with a number of destinations, formatters and helpers
 * Some destinations "force" the format to work properly
 * @param  string $Channel     The chosen channel: FILES, EMAIL, SMS, IM, FLOWAUTO, NETLOGGING, QUEUES, DB or DEVLOGGING
 * @param  int    $Destination The chosen destination
 * @param  int    $Helper      The chosen context helper(s)
 * @param  int    $Formatter   The chosen formatter
 * @param  array  $Options     Destination Options
 *                             Depending on the destination:
 *                             MIL_FILE    $Options['LogPath']     File Path
 *                             MIL_FILE    $Options['Separator']   Log register separator
 * @param  int    $ErrorLevel  The error level to log
 * @return void
 * @since 1.0
 * @todo prepare option for having inline headers on files
 */
function RegisterLogger($Channel, $Destination, $Helper, $Formatter, $Options = NULL, $ErrorLevel = NULL)
{
    if (DEBUGMODE)
    {
        echo date("Y-m-d H:i:s").' -> RegisterLogger '.PHP_EOL;
    }
    $InData['Channel'] = $Channel;
    $InData['Destination'] = $Destination;
    $InData['Helper'] = $Helper;
    $InData['Formatter'] = $Formatter;
    $InData['Options'] = $Options;
    if (is_null($ErrorLevel))
    {
        if (DEBUGMODE)
        {
            echo 'No ErrorLevel! Setting DEBUG as ErrorLevel'.PHP_EOL;
        }
        $InData['ErrorLevel'] = DEBUG;
    }
    else
    {
        $InData['ErrorLevel'] = $ErrorLevel;
    }

    $OutData = array();
    $SanityCheck = ErrorHandlingSanityCheck($InData, $OutData);
    if ($SanityCheck === FALSE)
    {
        echo 'Error registering logger. Review parameters. Message:'.$OutData['ReturnValue'].PHP_EOL;

        exit(1);
    }
    //Register the logger
    if (isset($GLOBALS['Loggers']))
    {
        $FreeCell = count( $GLOBALS['Loggers']);
    }
    else
    {
        $FreeCell = 0;
    }
    $GLOBALS['Loggers'][$FreeCell]['Channel'] = $Channel;
    $GLOBALS['Loggers'][$FreeCell]['Destination'] = $Destination;
    $GLOBALS['Loggers'][$FreeCell]['Helper'] = $Helper;
    $GLOBALS['Loggers'][$FreeCell]['Formatter'] = $Formatter;
    $GLOBALS['Loggers'][$FreeCell]['Options'] = $Options;
    $GLOBALS['Loggers'][$FreeCell]['ErrorLevel'] = $InData['ErrorLevel'];

    //Combine all available helpers
    if ($FreeCell === 0)
    {
        $Helpers = 0;
    }
    foreach ($GLOBALS['Loggers'] as $Logger)
    {
        $Helpers = $Helpers|$Logger['Helper'];
        if (DEBUGMODE)
        {
            echo date("Y-m-d H:i:s").' Helpers change from '.$FreeCell.' to '.$Helpers.PHP_EOL;
        }
    }
    //var_dump($Helpers);
    $GLOBALS['CombinedHelpers'] = $Helpers;
    //var_dump($GLOBALS['CombinedHelpers']);
    $BSLOutData = array();
    if (BootStrapLogger($GLOBALS['Loggers'][$FreeCell], $BSLOutData) === FALSE)
    {
        //Falta el echo de error y el exit
        echo 'Error bootstraping logger: '.$BSLOutData['ReturnValue'].EOL;

        exit(1);
    }

    //Set the handlers for MINION Error Processing
    set_error_handler('CustomErrorProcessor');
    register_shutdown_function('FatalErrorProcessor');
}

/**
 * BootStrapLogger bootstrap required options
 * @param  array   $InData
 *                          'Channel'       string The chosen channel
 *                          'Destination'   int    The chosen destination(s)
 *                          'Helper'        int    The chosen context helper
 *                          'Formatter'     int    The chosen formatter;
 *                          'Options'       array  The channel options;
 * @param  array   $OutData
 * @return boolean TRUE for OK, FALSE for problems
 * @return array   $OutData by reference
 *                         'Success'       boolean TRUE for success, FALSE for fail
 *                         'ReturnValue'   int     Any return value that needs to be sent home.
 * @since 1.0
 * @todo
 */
function BootStrapLogger($InData, &$OutData)
{
    if (DEBUGMODE)
    {
        echo date("Y-m-d H:i:s").' -> BootStrapLogger '.PHP_EOL;
    }
    //Depending on Channel and Destination
    //We start only with FILE
    switch ($InData['Channel'])
    {
        case "FILES":
            switch ($InData['Destination'])
            {
                case MIL_FILE:
                    $File = $InData['Options']['LogPath'];
                    //If file do not exists, we create it
                    if (file_exists($File) === FALSE)
                    {
                        //Let's try to create it
                        if (!touch($File))
                        {
                            $MessageToCaller = "Couldn't create ".$File.' See that the location is writeable by the code executor.'.PHP_EOL;
                            $OutData['Success'] = TRUE;
                            $OutData['ReturnValue'] = $MessageToCaller;

                            return FALSE;
                        }
                        //Write headers
                        $HdOutData = array();
                        FileHeader($InData, $HdOutData);
                        $Resultado = file_put_contents($File, $HdOutData['ReturnValue']);
                        if ($Resultado === FALSE)
                        {
                            $MessageToCaller = 'Fatal error writing Log Record to File'.PHP_EOL;
                            $OutData['Success'] = TRUE;
                            $OutData['ReturnValue'] = $MessageToCaller;

                            return FALSE;
                        }
                        else
                        {
                            $OutData['Success'] = TRUE;
                            $OutData['ReturnValue'] = $Resultado;

                            return TRUE;
                        }
                    }

                    $OutData['Success'] = TRUE;
                    $OutData['ReturnValue'] = TRUE;

                    return TRUE;
                    break;
                case MIL_SYSLOG:
                case MIL_ERROLOG:
                case MIL_PROCESSLOG:
                    //Unimplemented
                    $MessageToCaller = 'Uninplemented destination. Open an issue at <github project home> and we will see to it';
                    $OutData['Success'] = TRUE;
                    $OutData['ReturnValue'] = $MessageToCaller;

                    return FALSE;
                    break;
                default:
                    $MessageToCaller = 'Unknown destination'.PHP_EOL;
                    $OutData['Success'] = TRUE;
                    $OutData['ReturnValue'] = $MessageToCaller;

                    return FALSE;
                    break;
            }
            break;
        case "EMAIL":
        case "SMS":
        case "IM":
        case "FLOWAUTO":
        case "NETLOGGING":
        case "QUEUES":
        case "DB":
        case "DEVLOGGING":
            $OutData['Success'] = TRUE;
            $OutData['ReturnValue'] = 'Unimplemented channel. Open an issue in https://github.com/ProceduralMan/MinionLib if you need it';

            return FALSE;
        default:
            $OutData['Success'] = TRUE;
            $OutData['ReturnValue'] = 'Odd channel: '.$InData['Channel'];

            return FALSE;
            break;
    }
}

/**
 * ChannelSanityCheck checks that channel is OK
 * @param  array   $InData
 *                          'Channel'       string The chosen channel
 *                          'Destination'   int    The chosen destination(s)
 *                          'Helper'        int    The chosen context helper
 *                          'Formatter'     int    The chosen formatter;
 *                          'Options'       array  The channel options;
 * @param  array   $OutData
 * @return boolean TRUE for OK, FALSE for problems
 * @return array   $OutData by reference
 *                         'Success'       boolean TRUE for success, FALSE for fail
 *                         'ReturnValue'   int     Any return value that needs to be sent home.
 * @since 1.0
 * @todo
 */
function ChannelSanityCheck($InData, &$OutData)
{
    if (DEBUGMODE)
    {
        echo date("Y-m-d H:i:s").' -> ChannelSanityCheck '.PHP_EOL;
    }
    switch ($InData['Channel'])
    {
        case "FILES":
            $OutData['Success'] = TRUE;
            $OutData['ReturnValue'] = TRUE;

            return TRUE;
        case "EMAIL":
        case "SMS":
        case "IM":
        case "FLOWAUTO":
        case "NETLOGGING":
        case "QUEUES":
        case "DB":
        case "DEVLOGGING":
            $OutData['Success'] = TRUE;
            $OutData['ReturnValue'] = 'Unimplemented channel. Open an issue in https://github.com/ProceduralMan/MinionLib if you need it';

            return FALSE;
        default:
            $OutData['Success'] = TRUE;
            $OutData['ReturnValue'] = 'Odd channel: '.$InData['Channel'];

            return FALSE;
    }
}

/**
 * DevLoggingDestinationSanityCheck checks that destinations are OK and according to channel
 * also, bootstraps needed constants
 * @param array $InData
 *                       'Channel'       string The chosen channel
 *                       'Destination'   int    The chosen destination(s)
 *                       'Helper'        int    The chosen context helper
 *                       'Formatter'     int    The chosen formatter;
 *                       'Options'       array  The channel options;
 * @param array $OutData
 * 
 * @return boolean TRUE for OK, FALSE for problems
 * @return array   $OutData by reference
 *                 'Success'       boolean TRUE for success, FALSE for fail
 *                 'ReturnValue'   int     Any return value that needs to be sent home.
 * @since 1.0
 * @todo
 */
function DevLoggingDestinationSanityCheck($InData, &$OutData)
{
    if (DEBUGMODE)
    {
        echo date("Y-m-d H:i:s").' -> DevLoggingDestinationSanityCheck '.PHP_EOL;
    }
    //Unimplemented
    $OutData['Success'] = TRUE;
    $OutData['ReturnValue'] = 'Unimplemented Destination. Open an issue in https://github.com/ProceduralMan/MinionLib if you need it';

    return FALSE;
}

/**
 * DBDestinationSanityCheck checks that destinations are OK and according to channel
 * also, bootstraps needed constants
 * @param array $InData
 *                       'Channel'       string The chosen channel
 *                       'Destination'   int    The chosen destination(s)
 *                       'Helper'        int    The chosen context helper
 *                       'Formatter'     int    The chosen formatter;
 *                       'Options'       array  The channel options;
 * @param array $OutData
 * 
 * @return boolean TRUE for OK, FALSE for problems
 * @return array   $OutData by reference
 *                 'Success'       boolean TRUE for success, FALSE for fail
 *                 'ReturnValue'   int     Any return value that needs to be sent home.
 * @since 1.0
 * @todo
 */
function DBDestinationSanityCheck($InData, &$OutData)
{
    if (DEBUGMODE)
    {
        echo date("Y-m-d H:i:s").' -> DBDestinationSanityCheck '.PHP_EOL;
    }
    //Unimplemented
    $OutData['Success'] = TRUE;
    $OutData['ReturnValue'] = 'Unimplemented Destination. Open an issue in https://github.com/ProceduralMan/MinionLib if you need it';

    return FALSE;
}

/**
 * QueuesDestinationSanityCheck checks that destinations are OK and according to channel
 * also, bootstraps needed constants
 * @param array $InData
 *                       'Channel'       string The chosen channel
 *                       'Destination'   int    The chosen destination(s)
 *                       'Helper'        int    The chosen context helper
 *                       'Formatter'     int    The chosen formatter;
 *                       'Options'       array  The channel options;
 * @param array $OutData
 * 
 * @return boolean TRUE for OK, FALSE for problems
 * @return array   $OutData by reference
 *                 'Success'       boolean TRUE for success, FALSE for fail
 *                 'ReturnValue'   int     Any return value that needs to be sent home.
 * @since 1.0
 * @todo
 */
function QueuesDestinationSanityCheck($InData, &$OutData)
{
    if (DEBUGMODE)
    {
        echo date("Y-m-d H:i:s").' -> QueuesDestinationSanityCheck '.PHP_EOL;
    }
    //Unimplemented
    $OutData['Success'] = TRUE;
    $OutData['ReturnValue'] = 'Unimplemented Destination. Open an issue in https://github.com/ProceduralMan/MinionLib if you need it';

    return FALSE;
}

/**
 * NetLoggingDestinationSanityCheck checks that destinations are OK and according to channel
 * also, bootstraps needed constants
 * @param array $InData
 *                       'Channel'       string The chosen channel
 *                       'Destination'   int    The chosen destination(s)
 *                       'Helper'        int    The chosen context helper
 *                       'Formatter'     int    The chosen formatter;
 *                       'Options'       array  The channel options;
 * @param array $OutData
 * 
 * @return boolean TRUE for OK, FALSE for problems
 * @return array   $OutData by reference
 *                 'Success'       boolean TRUE for success, FALSE for fail
 *                 'ReturnValue'   int     Any return value that needs to be sent home.
 * @since 1.0
 * @todo
 */
function NetLoggingDestinationSanityCheck($InData, &$OutData)
{
    if (DEBUGMODE)
    {
        echo date("Y-m-d H:i:s").' -> NetLoggingDestinationSanityCheck '.PHP_EOL;
    }
    //Unimplemented
    $OutData['Success'] = TRUE;
    $OutData['ReturnValue'] = 'Unimplemented Destination. Open an issue in https://github.com/ProceduralMan/MinionLib if you need it';

    return FALSE;
}

/**
 * FlowAutoDestinationSanityCheck checks that destinations are OK and according to channel
 * also, bootstraps needed constants
 * @param array $InData
 *                       'Channel'       string The chosen channel
 *                       'Destination'   int    The chosen destination(s)
 *                       'Helper'        int    The chosen context helper
 *                       'Formatter'     int    The chosen formatter;
 *                       'Options'       array  The channel options;
 * @param array $OutData
 * 
 * @return boolean TRUE for OK, FALSE for problems
 * @return array   $OutData by reference
 *                 'Success'       boolean TRUE for success, FALSE for fail
 *                 'ReturnValue'   int     Any return value that needs to be sent home.
 * @since 1.0
 * @todo
 */
function FlowAutoDestinationSanityCheck($InData, &$OutData)
{
    if (DEBUGMODE)
    {
        echo date("Y-m-d H:i:s").' -> FlowAutoDestinationSanityCheck '.PHP_EOL;
    }
    //Unimplemented
    $OutData['Success'] = TRUE;
    $OutData['ReturnValue'] = 'Unimplemented Destination. Open an issue in https://github.com/ProceduralMan/MinionLib if you need it';

    return FALSE;
}

/**
 * IMDestinationSanityCheck checks that destinations are OK and according to channel
 * also, bootstraps needed constants
 * @param array $InData
 *                       'Channel'       string The chosen channel
 *                       'Destination'   int    The chosen destination(s)
 *                       'Helper'        int    The chosen context helper
 *                       'Formatter'     int    The chosen formatter;
 *                       'Options'       array  The channel options;
 * @param array $OutData
 * 
 * @return boolean TRUE for OK, FALSE for problems
 * @return array   $OutData by reference
 *                 'Success'       boolean TRUE for success, FALSE for fail
 *                 'ReturnValue'   int     Any return value that needs to be sent home.
 * @since 1.0
 * @todo
 */
function IMDestinationSanityCheck($InData, &$OutData)
{
    if (DEBUGMODE)
    {
        echo date("Y-m-d H:i:s").' -> IMDestinationSanityCheck '.PHP_EOL;
    }
    //Unimplemented
    $OutData['Success'] = TRUE;
    $OutData['ReturnValue'] = 'Unimplemented Destination. Open an issue in https://github.com/ProceduralMan/MinionLib if you need it';

    return FALSE;
}

/**
 * SMSDestinationSanityCheck checks that destinations are OK and according to channel
 * also, bootstraps needed constants
 * @param array $InData
 *                       'Channel'       string The chosen channel
 *                       'Destination'   int    The chosen destination(s)
 *                       'Helper'        int    The chosen context helper
 *                       'Formatter'     int    The chosen formatter;
 *                       'Options'       array  The channel options;
 * @param array $OutData
 * 
 * @return boolean TRUE for OK, FALSE for problems
 * @return array   $OutData by reference
 *                 'Success'       boolean TRUE for success, FALSE for fail
 *                 'ReturnValue'   int     Any return value that needs to be sent home.
 * @since 1.0
 * @todo
 */
function SMSDestinationSanityCheck($InData, &$OutData)
{
    if (DEBUGMODE)
    {
        echo date("Y-m-d H:i:s").' -> SMSDestinationSanityCheck '.PHP_EOL;
    }
    //Unimplemented
    $OutData['Success'] = TRUE;
    $OutData['ReturnValue'] = 'Unimplemented Destination. Open an issue in https://github.com/ProceduralMan/MinionLib if you need it';

    return FALSE;
}

/**
 * EmailDestinationSanityCheck checks that destinations are OK and according to channel
 * also, bootstraps needed constants
 * @param array $InData
 *                       'Channel'       string The chosen channel
 *                       'Destination'   int    The chosen destination(s)
 *                       'Helper'        int    The chosen context helper
 *                       'Formatter'     int    The chosen formatter;
 *                       'Options'       array  The channel options;
 * @param array $OutData
 * 
 * @return boolean TRUE for OK, FALSE for problems
 * @return array   $OutData by reference
 *                 'Success'       boolean TRUE for success, FALSE for fail
 *                 'ReturnValue'   int     Any return value that needs to be sent home.
 * @since 1.0
 * @todo
 */
function EmailDestinationSanityCheck($InData, &$OutData)
{
    if (DEBUGMODE)
    {
        echo date("Y-m-d H:i:s").' -> EmailDestinationSanityCheck '.PHP_EOL;
    }
    //Unimplemented
    $OutData['Success'] = TRUE;
    $OutData['ReturnValue'] = 'Unimplemented Destination. Open an issue in https://github.com/ProceduralMan/MinionLib if you need it';

    return FALSE;
}

/**
 * FilesDestinationSanityCheck checks that destinations are OK and according to channel
 * also, bootstraps needed constants
 * @param array $InData
 *                       'Channel'       string The chosen channel
 *                       'Destination'   int    The chosen destination(s)
 *                       'Helper'        int    The chosen context helper
 *                       'Formatter'     int    The chosen formatter;
 *                       'Options'       array  The channel options;
 * @param array $OutData
 * 
 * @return boolean TRUE for OK, FALSE for problems
 * @return array   $OutData by reference
 *                 'Success'       boolean TRUE for success, FALSE for fail
 *                 'ReturnValue'   int     Any return value that needs to be sent home.
 * @since 1.0
 * @todo
 */
function FilesDestinationSanityCheck($InData, &$OutData)
{
    if (DEBUGMODE)
    {
        echo date("Y-m-d H:i:s").' -> FilesDestinationSanityCheck '.PHP_EOL;
    }
    $Destination = $InData['Destination'];
    switch ($Destination)
    {
        case MIL_FILE:
            $File = $InData['Options']['LogPath'];
            //File exists?
            if (file_exists($File))
            {
                //Is it realy a file?
                if (!is_file($File))
                {
                    //Falta el message to caller
                    $MessageToCaller = $File.' does not seem to be a log file'.PHP_EOL;
                    $OutData['Success'] = TRUE;
                    $OutData['ReturnValue'] = $MessageToCaller;

                    return FALSE;
                }
                else
                {
                    //Is it writeable
                    if (!is_writeable($File))
                    {
                        $MessageToCaller = $File.' does not seem to be writeable';
                        $OutData['Success'] = TRUE;
                        $OutData['ReturnValue'] = $MessageToCaller;

                        return FALSE;
                    }
                    else
                    {
                        //Warn if separator is null
                        $Separator = $InData['Options']['Separator'];
                        if (is_null($Separator))
                        {
                            echo "**WARNING** NULL separator will be substituted for an empty string. Set Options['Separator'] to avoid this message".PHP_EOL;
                        }
                        $OutData['Success'] = TRUE;
                        $OutData['ReturnValue'] = TRUE;

                        return TRUE;
                    }
                }
            }
            break;
        case MIL_SYSLOG:
        case MIL_ERROLOG:
        case MIL_PROCESSLOG:
            //Unimplemented
            $OutData['Success'] = TRUE;
            $OutData['ReturnValue'] = 'Unimplemented destination. Open an issue at https://github.com/ProceduralMan/MinionLib if you need it.';

            return FALSE;
            break;
        default:
            echo 'Unknown destination'.PHP_EOL;
            $OutData['Success'] = TRUE;
            $OutData['ReturnValue'] = 'Unknown destination: '.$Destination;

            return FALSE;
            break;
    }
}

/**
 * DestinationSanityCheck checks that destinations are OK and according to channel
 * @param array $InData
 *                       'Channel'       string The chosen channel
 *                       'Destination'   int    The chosen destination(s)
 *                       'Helper'        int    The chosen context helper
 *                       'Formatter'     int    The chosen formatter;
 *                       'Options'       array  The channel options;
 * @param array $OutData
 * 
 * @return boolean TRUE for OK, FALSE for problems
 * @return array   $OutData by reference
 *                 'Success'       boolean TRUE for success, FALSE for fail
 *                 'ReturnValue'   int     Any return value that needs to be sent home.
 * @since 1.0
 * @todo
 */
function DestinationSanityCheck($InData, &$OutData)
{
    if (DEBUGMODE)
    {
        echo date("Y-m-d H:i:s").' -> DestinationSanityCheck '.PHP_EOL;
    }
    switch ($InData['Channel'])
    {
        case "FILES":
            return FilesDestinationSanityCheck($InData, $OutData);
        case "EMAIL":
            return EmailDestinationSanityCheck($InData, $OutData);
        case "SMS":
            return SMSDestinationSanityCheck($InData, $OutData);
        case "IM":
            return IMDestinationSanityCheck($InData, $OutData);
        case "FLOWAUTO":
            return FlowAutoDestinationSanityCheck($InData, $OutData);
        case "NETLOGGING":
            return NetLoggingDestinationSanityCheck($InData, $OutData);
        case "QUEUES":
            return QueuesDestinationSanityCheck($InData, $OutData);
        case "DB":
            return DBDestinationSanityCheck($InData, $OutData);
        case "DEVLOGGING":
            return DevLoggingDestinationSanityCheck($InData, $OutData);
        default:
            $OutData['Success'] = TRUE;
            $OutData['ReturnValue'] = 'Uncontrolled channel: '.$InData['Channel'];

            return FALSE;
            break;
    }
}

/**
 * FormatterSanityCheck checks that formatters are OK and according to channel
 * also, bootstraps needed constants
 * @param array $InData
 *                       'Channel'       string The chosen channel
 *                       'Destination'   int    The chosen destination(s)
 *                       'Helper'        int    The chosen context helper
 *                       'Formatter'     int    The chosen formatter;
 *                       'Options'       array  The channel options;
 * @param array $OutData
 * 
 * @return boolean TRUE for OK, FALSE for problems
 * @return array   $OutData by reference
 *                 'Success'       boolean TRUE for success, FALSE for fail
 *                 'ReturnValue'   int     Any return value that needs to be sent home.
 * @since 1.0
 * @todo explain and bubble up the explanation of the checking
 */
function FormatterSanityCheck($InData, &$OutData)
{
    if (DEBUGMODE)
    {
        echo date("Y-m-d H:i:s").' -> FormatterSanityCheck '.PHP_EOL;
    }
    //One-Line, Line, HTML, Normal, Scalar and JSON can be used everywhere
    if ($InData['Formatter']&MIL_ONELINEFORMATTER)
    {
        $OutData['Success'] = TRUE;
        $OutData['ReturnValue'] = TRUE;

        return TRUE;
    }
    if ($InData['Formatter']&MIL_LINEFORMATTER)
    {
        $OutData['Success'] = TRUE;
        $OutData['ReturnValue'] = TRUE;

        return TRUE;
    }
    if ($InData['Formatter']&MIL_HTMLFORMATTER)
    {
        $OutData['Success'] = TRUE;
        $OutData['ReturnValue'] = TRUE;

        return TRUE;
    }
    if ($InData['Formatter']&MIL_NORMALIZERFORMATTER)
    {
        $OutData['Success'] = TRUE;
        $OutData['ReturnValue'] = TRUE;

        return TRUE;
    }
    if ($InData['Formatter']&MIL_SCALARFORMATTER)
    {
        $OutData['Success'] = TRUE;
        $OutData['ReturnValue'] = TRUE;

        return TRUE;
    }
    if ($InData['Formatter']&MIL_JSONFORMATTER)
    {
        $OutData['Success'] = TRUE;
        $OutData['ReturnValue'] = TRUE;

        return TRUE;
    }
    //MIL_WILDFIREFORMATTER it's only for FirePHP
    if ($InData['Formatter']&MIL_WILDFIREFORMATTER)
    {
        if ($InData['Destination']&MIL_FIREPHP)
        {
            if ($InData['Channel'] === "DEVLOGGING")
            {
                $OutData['Success'] = TRUE;
                $OutData['ReturnValue'] = TRUE;

                return TRUE;
            }
            else
            {
                $OutData['Success'] = TRUE;
                $OutData['ReturnValue'] = FALSE;

                return FALSE;
            }
        }
        else
        {
            $OutData['Success'] = TRUE;
            $OutData['ReturnValue'] = FALSE;

            return FALSE;
        }
    }

    //MIL_CHROMELOGGERFORMATTER it's only for ChromeLogger
    if ($InData['Formatter']&MIL_CHROMELOGGERFORMATTER)
    {
        if ($InData['Destination']&MIL_CHROMELGR)
        {
            if ($InData['Channel'] === "DEVLOGGING")
            {
                $OutData['Success'] = TRUE;
                $OutData['ReturnValue'] = TRUE;

                return TRUE;
            }
            else
            {
                $OutData['Success'] = TRUE;
                $OutData['ReturnValue'] = FALSE;

                return FALSE;
            }
        }
        else
        {
            $OutData['Success'] = TRUE;
            $OutData['ReturnValue'] = FALSE;

            return FALSE;
        }
    }

    //MIL_GELFMESSAGEFORMATTER it's only for Gelf
    if ($InData['Formatter']&MIL_GELFMESSAGEFORMATTER)
    {
        if ($InData['Destination']&MIL_GRAYLOG)
        {
            if ($InData['Channel'] === "NETLOGGING")
            {
                $OutData['Success'] = TRUE;
                $OutData['ReturnValue'] = TRUE;

                return TRUE;
            }
            else
            {
                $OutData['Success'] = TRUE;
                $OutData['ReturnValue'] = FALSE;

                return FALSE;
            }
        }
        else
        {
            $OutData['Success'] = TRUE;
            $OutData['ReturnValue'] = FALSE;

            return FALSE;
        }
    }

    //MIL_LOGSTASHFORMATTER it's only for Logstash
    if ($InData['Formatter']&MIL_LOGSTASHFORMATTER)
    {
        if ($InData['Destination']&MIL_LOGSTASH)
        {
            if ($InData['Channel'] === "NETLOGGING")
            {
                $OutData['Success'] = TRUE;
                $OutData['ReturnValue'] = TRUE;

                return TRUE;
            }
            else
            {
                $OutData['Success'] = TRUE;
                $OutData['ReturnValue'] = FALSE;

                return FALSE;
            }
        }
        else
        {
            $OutData['Success'] = TRUE;
            $OutData['ReturnValue'] = FALSE;

            return FALSE;
        }
    }

    //MIL_ELASTICAFORMATTER it's only for ElasticSearch
    if ($InData['Formatter']&MIL_ELASTICAFORMATTER)
    {
        if ($InData['Destination']&MIL_ELASTICS)
        {
            if ($InData['Channel'] === "DB")
            {
                $OutData['Success'] = TRUE;
                $OutData['ReturnValue'] = TRUE;

                return TRUE;
            }
            else
            {
                $OutData['Success'] = TRUE;
                $OutData['ReturnValue'] = FALSE;

                return FALSE;
            }
        }
        else
        {
            $OutData['Success'] = TRUE;
            $OutData['ReturnValue'] = FALSE;

            return FALSE;
        }
    }

    //MIL_LOGGLYFORMATTER it's only for Loggly
    if ($InData['Formatter']&MIL_LOGGLYFORMATTER)
    {
        if ($InData['Destination']&MIL_LOGGLY)
        {
            if ($InData['Channel'] === "NETLOGGING")
            {
                $OutData['Success'] = TRUE;
                $OutData['ReturnValue'] = TRUE;

                return TRUE;
            }
            else
            {
                $OutData['Success'] = TRUE;
                $OutData['ReturnValue'] = FALSE;

                return FALSE;
            }
        }
        else
        {
            $OutData['Success'] = TRUE;
            $OutData['ReturnValue'] = FALSE;

            return FALSE;
        }
    }

    //MIL_FLOWDOCFORMATTER it's only for FlowDoc
    if ($InData['Formatter']&MIL_FLOWDOCFORMATTER)
    {
        if ($InData['Destination']&MIL_FLOWDOC)
        {
            if ($InData['Channel'] === "IM")
            {
                $OutData['Success'] = TRUE;
                $OutData['ReturnValue'] = TRUE;

                return TRUE;
            }
            else
            {
                $OutData['Success'] = TRUE;
                $OutData['ReturnValue'] = FALSE;

                return FALSE;
            }
        }
        else
        {
            $OutData['Success'] = TRUE;
            $OutData['ReturnValue'] = FALSE;

            return FALSE;
        }
    }

    //MIL_MONGODBFORMATTER it's only for Mongo DB
    if ($InData['Formatter']&MIL_MONGODBFORMATTER)
    {
        if ($InData['Destination']&MIL_MONGODB)
        {
            if ($InData['Channel'] === "DB")
            {
                $OutData['Success'] = TRUE;
                $OutData['ReturnValue'] = TRUE;

                return TRUE;
            }
            else
            {
                $OutData['Success'] = TRUE;
                $OutData['ReturnValue'] = FALSE;

                return FALSE;
            }
        }
        else
        {
            $OutData['Success'] = TRUE;
            $OutData['ReturnValue'] = FALSE;

            return FALSE;
        }
    }

    //MIL_LOGMATICFORMATTER it's only for LogMatic
    if ($InData['Formatter']&MIL_LOGMATICFORMATTER)
    {
        if ($InData['Destination']&MIL_LOGMATIC)
        {
            if ($InData['Channel'] === "NETLOGGING")
            {
                $OutData['Success'] = TRUE;
                $OutData['ReturnValue'] = TRUE;

                return TRUE;
            }
            else
            {
                $OutData['Success'] = TRUE;
                $OutData['ReturnValue'] = FALSE;

                return FALSE;
            }
        }
        else
        {
            $OutData['Success'] = TRUE;
            $OutData['ReturnValue'] = FALSE;

            return FALSE;
        }
    }

    //MIL_FLUENTDFORMATTER it's only for sockets
    if ($InData['Formatter']&MIL_FLUENTDFORMATTER)
    {
        if ($InData['Destination']&MIL_SOCKET)
        {
            if ($InData['Channel'] === "NETLOGGING")
            {
                $OutData['Success'] = TRUE;
                $OutData['ReturnValue'] = TRUE;

                return TRUE;
            }
            else
            {
                $OutData['Success'] = TRUE;
                $OutData['ReturnValue'] = FALSE;

                return FALSE;
            }
        }
        else
        {
            $OutData['Success'] = TRUE;
            $OutData['ReturnValue'] = FALSE;

            return FALSE;
        }
    }
}


/**
 * ErrorHandlingSanityCheck checks that all needed configuration is in place
 * @param array $InData
 *                       'Channel'       string The chosen channel
 *                       'Destination'   int    The chosen destination(s)
 *                       'Helper'        int    The chosen context helper
 *                       'Formatter'     int    The chosen formatter;
 *                       'Options'       array  The channel options;
 * @param array $OutData
 * 
 * @return boolean TRUE for OK, FALSE for problems
 * @return array   $OutData by reference
 *                 'Success'       boolean TRUE for success, FALSE for fail
 *                 'ReturnValue'   int     Any return value that needs to be sent home.
 * @since 1.0
 * @todo
 */
function ErrorHandlingSanityCheck($InData, &$OutData)
{
    if (DEBUGMODE)
    {
        echo date("Y-m-d H:i:s").' -> ErrorHandlingSanityCheck '.PHP_EOL;
    }
    $ChannelOK = ChannelSanityCheck($InData, $OutData);
    if ($ChannelOK === FALSE)
    {
        $MessageToCaller = 'Incorrect Channel. Must be one of these strings: FILES. Unimplemented: EMAIL, SMS, IM, FLOWAUTO, NETLOGGING, QUEUES, DB, DEVLOGGING';
        $OutData['Success'] = FALSE;
        $OutData['ReturnValue'] = $MessageToCaller;

        return FALSE;
    }
    $DestinationOK = DestinationSanityCheck($InData, $OutData);
    if ($DestinationOK === FALSE)
    {
        $MessageToCaller = 'Incorrect Destination. See flags in ErrorHandling.php. A max of three destinations are allowed per channel. Message: '.$OutData['ReturnValue'];
        $OutData['Success'] = FALSE;
        $OutData['ReturnValue'] = $MessageToCaller;

        return FALSE;
    }
    $FormatterOK = FormatterSanityCheck($InData, $OutData);
    if ($FormatterOK === FALSE)
    {
        $MessageToCaller = 'Incorrect Formatter. See flags in ErrorHandling.php. A max of three destinations are allowed per channel';
        $OutData['Success'] = FALSE;
        $OutData['ReturnValue'] = $MessageToCaller;

        return FALSE;
    }
    $OutData['Success'] = TRUE;
    $OutData['ReturnValue'] = TRUE;

    return TRUE;
}

/**
 * ProcessError keeps the calling heap and error logic coherent
 * Is called allway by a helper function AddXXX (AddDebug, AddInfo)
 * @param array $InData
 *                      'LogLevel'      string  DEBUG, INFO, WARNING, ERROR, CRITICAL, ALERT o EMERGENCY
 *                      'FileName'      string  script name, -only used on FatalErrorProcessor-
 *                      'Line'          int     line number  -only used on FatalErrorProcessor-
 *                      'Function'      string  The function name -only used on FatalErrorProcessor-
 *                      'EventText'     string  The text of the event as sent by the Caller
 * 
 *      Will conform a register of the kind:
 *      aaaa-mm-ddThh:mm:ss\tErrorLevel\tFileName\tFunction\tLine\tEventText 
 * 
 * @param  array $OutData
 * @return array $OutData by reference
 *                       'Success'       boolean TRUE for success, FALSE for fail
 *                       'ReturnValue'   array   array of return values. Single values as arrays of one element. 
 *                       ProcessError should 'pass' the return suggested by caller
 * 
 * @return array   $GLOBALS['LogicHeap']    arrayed and organized secuence of Notices, Warnings or Errors
 * @return boolean a simple TRUE/FALSE indicating success or failure
 *                 LOG LEVELS (RFC 5424)
 *                 DEBUG (100): Detailed debug information.
 *                 INFO (200): Interesting events. Examples: User logs in, SQL logs.
 *                 NOTICE (250): Normal but significant events.
 *                 WARNING (300): Exceptional occurrences that are not errors. Examples: Use of deprecated APIs, poor use of an API, undesirable things that are not necessarily wrong.
 *                 ERROR (400): Runtime errors that do not require immediate action but should typically be logged and monitored.
 *                 CRITICAL (500): Critical conditions. Example: Application component unavailable, unexpected exception.
 *                 ALERT (550): Action must be taken immediately. Example: Entire website down, database unavailable, etc. This should trigger the SMS alerts and wake you up.
 *                 EMERGENCY (600): Emergency: system is unusable.
 * @since v1.0
 * @todo revisar https://github.com/filp/whoops
 */
function ProcessError($InData,&$OutData)
{
    if (DEBUGMODE)
    {
        echo date("Y-m-d H:i:s").' -> ProcessError '.PHP_EOL;
    }
    //InData checking
    if (!isset($InData['LogLevel'])||!isset($InData['EventText']))
    {
        $OutData['Success'] = FALSE;
        $MessageToCaller = 'Corrupt/Missing Indata';
        $OutData['ReturnValue'] = $MessageToCaller; //Not treated upstream. Thus the echo below
        echo 'Corrupt/Missing Indata'.PHP_EOL;

        return FALSE;
    }

    //Prepare Dates
    $ServerDate = date_create();                            //ServerDate in the server TimeZone
    $LogRecord['ServerDate'] = date_format($ServerDate,"Y-m-d H:i:s T");
    date_timezone_set($ServerDate, timezone_open(LOCALTZ));                //Change to localTZ
    $LogRecord['LocalDate'] = date_format($ServerDate,"Y-m-d H:i:s T");
    //Add info to the Heap. No matter if it is set already or not. Somebody must do it first.
    $LogRecord['LogLevel'] = $InData['LogLevel'];
    $LogRecord['EventText'] = $InData['EventText'];
    if (DEBUGMODE)
    {
        echo 'LogRecord'.PHP_EOL;
        print_r($LogRecord);
    }
    //Add context Helpers
    $Helpers = $GLOBALS['CombinedHelpers'];
    if ($Helpers&MIL_CODECONTEXT)
    {
        //echo "You passed CODECONTEXT!<br>\n";
        $Trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        //Eliminate the helper functions AddX
        if (DEBUGMODE)
        {
            echo 'Trace (pre-shift)'.PHP_EOL;
            print_r($Trace);
        }
        array_shift($Trace);
        if (DEBUGMODE)
        {
            echo 'Trace'.PHP_EOL;
            print_r($Trace);
        }
        //How deep is the trace
        $Levels = count($Trace);
        //Need at least a 2-level one
        if ($Levels>1)
        {
            if ($Trace[1]['function'] === 'CustomErrorProcessor')
            {
                //Custom Errors
                if ($Levels === 2)
                {
                    //Comes from main
                    $LogRecord['File'] = $Trace[1]['file'];
                    $LogRecord['Function'] = "Main";
                    $LogRecord['Line'] = $Trace[1]['line'];
                }
                else
                {
                    //Comes from function
                    $LogRecord['File'] = $Trace[1]['file'];
                    $LogRecord['Function'] = $Trace[2]['function'];
                    $LogRecord['Line'] = $Trace[1]['line'];
                }
            }
            elseif (($Trace[0]['function'] === 'FatalErrorProcessor')||($Trace[0]['function'] === 'CustomErrorProcessor'))
            {
                //Fatal Errors. There are always 2 levels, but the array_shift destroys one
                $LogRecord['File'] = $InData['FileName'];
                $LogRecord['Function'] = $InData['Function'];
                $LogRecord['Line'] = $InData['Line'];
            }
            else
            {
                //Self-induced errors (calls to Process Error, trace[1] function not CustomErrorProcessor)
                //On those errors, best line is at trace[0]
                if (isset($Trace[0]['line']))
                {
                    $LogRecord['Line'] = $Trace[0]['line'];
                }
                else
                {
                    $LogRecord['Line'] = "***INFO UNAVAILABLE***";
                }
                //But function that holds the error is at trace[1]
                if (isset($Trace[1]['function']))
                {
                    $LogRecord['Function'] = $Trace[1]['function'];
                }
                else
                {
                    $LogRecord['Function'] = "***INFO UNAVAILABLE***";
                }
                //So the file to report should be got from trace[1] also
                if (isset($Trace[1]['file']))
                {
                    $LogRecord['File'] = $Trace[1]['file'];
                }
                else
                {
                    $LogRecord['File'] = "***INFO UNAVAILABLE***";
                }
            }
        }
        elseif ($Levels === 1)
        {
            if ($Trace[0]['function'] === 'FatalErrorProcessor')
            {
                //Repeated code... not very clean
                //Fatal Errors. There are always 2 levels, but the array_shift destroys one
                $LogRecord['File'] = $InData['FileName'];
                $LogRecord['Function'] = $InData['Function'];
                $LogRecord['Line'] = $InData['Line'];
            }
            else
            {
                //On self-induced errors, this is the main call... must get the info from trace[0]
                $LogRecord['File'] = $Trace[0]['file'];
                $LogRecord['Function'] = "Main";
                $LogRecord['Line'] = $Trace[0]['line'];
            }
        }
    } //End Code Context helper

    if ($Helpers&MIL_WEBCONTEXT)
    {
        if (DEBUGMODE)
        {
            echo "You passed WEBCONTEXT!<br>\n";
        }
    }

    if ($Helpers&MIL_MEMUSE)
    {
        if (DEBUGMODE)
        {
            echo "You passed MEMUSE!<br>\n";
        }
    }

    if ($Helpers&MIL_MEMPEAK)
    {
        if (DEBUGMODE)
        {
            echo "You passed MEMPEAK!<br>\n";
        }
    }

    if ($Helpers&MIL_PIDCONTEXT)
    {
        if (DEBUGMODE)
        {
            echo "You passed PIDCONTEXT!<br>\n";
        }
    }

    if ($Helpers&MIL_ADDUID)
    {
        if (DEBUGMODE)
        {
            echo "You passed ADDUID!<br>\n";
        }
    }

    if ($Helpers&MIL_GITCONTEXT)
    {
        if (DEBUGMODE)
        {
            echo "You passed GITCONTEXT!<br>\n";
        }
    }

    if ($Helpers&MIL_TAGCONTEXT)
    {
        if (DEBUGMODE)
        {
            echo "You passed TAGCONTEXT!<br>\n";
        }
    }

    if ($Helpers&MIL_HOSTNAMECONTEXT)
    {
        if (DEBUGMODE)
        {
            echo "You passed HOSTNAMECONTEXT!<br>\n";
        }
    }

    $OutData['Success'] = TRUE;
    $OutData['ReturnValue'] = TRUE;
    $GLOBALS['LogicHeap'][] = $LogRecord; //HA DE IR AL FINAL
    if (DEBUGMODE)
    {
        echo '->exits OK'.PHP_EOL;
    }

    return TRUE;
}

/**
 * AddDebug adds an DEBUG level log record
 * @param  type    $Message
 * @return boolean TRUE on success, FALSE on failure
 */
function AddDebug($Message)
{
    if (DEBUGMODE)
    {
        echo date("Y-m-d H:i:s").' -> AddDebug '.PHP_EOL;
    }
    $Indata = array();
    $OutData = array();
    $Indata['LogLevel'] = DEBUG;
    $Indata['EventText'] = $Message;
    $PE = ProcessError($Indata, $OutData);
    if ($PE === FALSE)
    {
        AddPEError($OutData);

        return FALSE;
    }
    //Log on each Add to enable tail -f monitoring
    NotifyError();

    return TRUE;
}

/**
 * AddInfo adds an INFO level log record
 * @param  type    $Message
 * @return boolean TRUE on success, FALSE on failure
 */
function AddInfo($Message)
{
    if (DEBUGMODE)
    {
        echo date("Y-m-d H:i:s").' -> AddInfo '.PHP_EOL;
    }
    $Indata = array();
    $OutData = array();
    $Indata['LogLevel'] = INFO;
    $Indata['EventText'] = $Message;
    $PE = ProcessError($Indata, $OutData);
    if ($PE === FALSE)
    {
        AddPEError($OutData);

        return FALSE;
    }
    //Log on each Add to enable tail -f monitoring
    NotifyError();

    return TRUE;
}

/**
 * AddNotice adds an NOTICE level log record
 * @param  type    $Message
 * @return boolean TRUE on success, FALSE on failure
 */
function AddNotice($Message)
{
    if (DEBUGMODE)
    {
        echo date("Y-m-d H:i:s").' -> AddNotice '.PHP_EOL;
    }
    $Indata = array();
    $OutData = array();
    $Indata['LogLevel'] = NOTICE;
    $Indata['EventText'] = $Message;
    $PE = ProcessError($Indata, $OutData);
    if ($PE === FALSE)
    {
        AddPEError($OutData);

        return FALSE;
    }
    //Log on each Add to enable tail -f monitoring
    NotifyError();

    return TRUE;
}

/**
 * AddWarning adds an WARNING level log record
 * @param  type    $Message
 * @return boolean TRUE on success, FALSE on failure
 */
function AddWarning($Message)
{
    if (DEBUGMODE)
    {
        echo date("Y-m-d H:i:s").' -> AddWarning '.PHP_EOL;
    }
    $Indata = array();
    $OutData = array();
    $Indata['LogLevel'] = WARNING;
    $Indata['EventText'] = $Message;
    $PE = ProcessError($Indata, $OutData);
    if ($PE === FALSE)
    {
        AddPEError($OutData);

        return FALSE;
    }
    //Log on each Add to enable tail -f monitoring
    NotifyError();

    return TRUE;
}

/**
 * AddError adds an ERROR level log record
 * @param  type    $Message
 * @return boolean TRUE on success, FALSE on failure
 */
function AddError($Message)
{
    if (DEBUGMODE)
    {
        echo date("Y-m-d H:i:s").' -> AddError '.PHP_EOL;
    }
    $Indata = array();
    $OutData = array();
    $Indata['LogLevel'] = ERROR;
    $Indata['EventText'] = $Message;
    $PE = ProcessError($Indata, $OutData);
    if ($PE === FALSE)
    {
        AddPEError($OutData);

        return FALSE;
    }
    //Log on each Add to enable tail -f monitoring
    NotifyError();

    return TRUE;
}

/**
 * AddCritical adds an CRITICAL level log record
 * @param  type    $Message
 * @return boolean TRUE on success, FALSE on failure
 */
function AddCritical($Message)
{
    if (DEBUGMODE)
    {
        echo date("Y-m-d H:i:s").' -> AddCritical '.PHP_EOL;
    }
    $Indata = array();
    $OutData = array();
    $Indata['LogLevel'] = CRITICAL;
    $Indata['EventText'] = $Message;
    $PE = ProcessError($Indata, $OutData);
    if ($PE === FALSE)
    {
        AddPEError($OutData);

        return FALSE;
    }
    //Log on each Add to enable tail -f monitoring
    NotifyError();

    return TRUE;
}

/**
 * AddAlert adds an ALERT level log record
 * @param  type    $Message
 * @return boolean TRUE on success, FALSE on failure
 */
function AddAlert($Message)
{
    if (DEBUGMODE)
    {
        echo date("Y-m-d H:i:s").' -> AddAlert '.PHP_EOL;
    }
    $Indata = array();
    $OutData = array();
    $Indata['LogLevel'] = ALERT;
    $Indata['EventText'] = $Message;
    $PE = ProcessError($Indata, $OutData);
    if ($PE === FALSE)
    {
        AddPEError($OutData);

        return FALSE;
    }
    //Log on each Add to enable tail -f monitoring
    NotifyError();

    return TRUE;
}

/**
 * AddEmergency adds an EMERGENCY level log record
 * @param  type    $Message
 * @return boolean TRUE on success, FALSE on failure
 */
function AddEmergency($Message)
{
    if (DEBUGMODE)
    {
        echo date("Y-m-d H:i:s").' -> AddEmergency '.PHP_EOL;
    }
    $Indata = array();
    $OutData = array();
    $Indata['LogLevel'] = EMERGENCY;
    $Indata['EventText'] = $Message;
    $PE = ProcessError($Indata, $OutData);
    if ($PE === FALSE)
    {
        AddPEError($OutData);

        return FALSE;
    }
    //Log on each Add to enable tail -f monitoring
    NotifyError();

    return TRUE;
}


/**
 * AddNEError adds NotifyError problems
 * to logic heap for further processing
 * @param  array $OutData
 * @return void
 * @return array $OutData by reference
 *                       'Success'       boolean TRUE for success, FALSE for fail
 *                       'ReturnValue'   int     Any return value that needs to be sent home.
 * @since 1.0
 * @todo
 */
function AddNotifyError(&$OutData)
{
    if (DEBUGMODE)
    {
        echo date("Y-m-d H:i:s").' -> AddNotifyError '.PHP_EOL;
    }
    //Add info to the Heap.
    $ServerDate = date_create();                            //ServerDate in the server TimeZone
    $LogRecord['ServerDate'] = date_format($ServerDate,"YmdTH:i:s");
    date_timezone_set($ServerDate, timezone_open(LOCALTZ));                //Change to localTZ
    $LogRecord['LocalDate'] = date_format($ServerDate,"YmdTH:i:s");
    $LogRecord['LogLevel'] = ERROR;
    $LogRecord['EventText'] = "Error while notifying the error: ".$OutData['ReturnValue'];

    //Add context Helpers
    if (HELPERS&CODECONTEXT)
    {
        $Trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        //How deep is the trace
        $Levels = count($Trace);
        if ($Levels>1)
        {
            //Called form a function
            $LogRecord['File'] = $Trace[1]['file'];
            $LogRecord['Function'] = $Trace[1]['function'];
            $LogRecord['Line'] = $Trace[1]['line'];
        }
        else
        {
            //Called from main
            $LogRecord['File'] = $Trace[0]['file'];
            $LogRecord['Function'] = "Main";
            $LogRecord['Line'] = $Trace[0]['line'];
        }
    }   //FIN add context
    $OutData['Success'] = TRUE;
    $OutData['ReturnValue'] = TRUE;
    $GLOBALS['LogicHeap'][] = $LogRecord; //HA DE IR AL FINAL
}

/**
 * AddPEError adds ProcessError problems
 * to logic heap for notifying
 * @param  array $OutData
 * @return void
 * @return array $OutData by reference
 *                       'Success'       boolean TRUE for success, FALSE for fail
 *                       'ReturnValue'   int     Any return value that needs to be sent home.
 * @since 1.0
 * @todo
 */
function AddPEError(&$OutData)
{
    if (DEBUGMODE)
    {
        echo date("Y-m-d H:i:s").' -> AddPEError '.PHP_EOL;
    }
    //Add info to the Heap.
    $ServerDate = date_create();                            //ServerDate in the server TimeZone
    $LogRecord['ServerDate'] = date_format($ServerDate,"YmdTH:i:s");
    date_timezone_set($ServerDate, timezone_open(LOCALTZ));                //Change to localTZ
    $LogRecord['LocalDate'] = date_format($ServerDate,"YmdTH:i:s");
    $LogRecord['LogLevel'] = ERROR;
    $LogRecord['EventText'] = "Error while processing error on ProcessError";

    //Add context Helpers
    if (HELPERS&CODECONTEXT)
    {
        $Trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        //How deep is the trace
        $Levels = count($Trace);
        if ($Levels>1)
        {
            //Called form a function
            $LogRecord['File'] = $Trace[1]['file'];
            $LogRecord['Function'] = $Trace[1]['function'];
            $LogRecord['Line'] = $Trace[1]['line'];
        }
        else
        {
            //Called from main
            $LogRecord['File'] = $Trace[0]['file'];
            $LogRecord['Function'] = "Main";
            $LogRecord['Line'] = $Trace[0]['line'];
        }
    }   //FIN add context
    $OutData['Success'] = TRUE;
    $OutData['ReturnValue'] = TRUE;
    $GLOBALS['LogicHeap'][] = $LogRecord; //HA DE IR AL FINAL
}

/**
 * FileHeader sets the file header
 * @param array $InData
 *                       'Channel'       string The chosen channel
 *                       'Destination'   int    The chosen destination(s)
 *                       'Helper'        int    The chosen context helper
 *                       'Formatter'     int    The chosen formatter;
 *                       'Options'       array  The channel options;
 * @param array $OutData
 * 
 * @return boolean TRUE for OK, FALSE for problems
 * @return array   $OutData by reference
 *                 'Success'       boolean TRUE for success, FALSE for fail
 *                 'ReturnValue'   int     Any return value that needs to be sent home.
 * @since 1.0
 * @todo
 */
function FileHeader($InData, &$OutData)
{
    if (DEBUGMODE)
    {
        echo date("Y-m-d H:i:s").' -> FileHeader '.PHP_EOL;
    }
    if (!empty($InData['Options']['Separator']))
    {
        $Separator = $InData['Options']['Separator'];
    }
    else
    {
        //Null separator, use empty string
        $Separator = '';
    }

    //prepare header
    $Helpers = $GLOBALS['CombinedHelpers'];

    $OneLiner = 'Server Date'.$Separator.'Local Date'.$Separator.'Log Level'.$Separator.'Event Text';
    //Faltan los datos de los helpers
    if ($Helpers&MIL_CODECONTEXT)
    {
        $OneLiner .= $Separator.'File'.$Separator.'Function'.$Separator.'Line';
    }
    $OneLiner .= PHP_EOL;
    $OutData['Success'] = TRUE;
    $OutData['ReturnValue'] = $OneLiner;

    return TRUE;
}

/**
 * LineFormatter Formats a log record into a one-line string. Keeps EOLs
 * @param array $InData  input data. In this case, the record info
 *                       Logger Data:
 *                       'Channel'      the channel info
 *                       'Destination'  the destination info
 *                       'Helper'       the context info
 *                       'Formatter'    the formatter info
 *                       'Options'       array  The channel options;
 *                       Standard Error Data:
 *                       'ServerDate'    the date in UTC
 *                       'LocalDate'     the date in local time
 *                       'LogLevel'      the log level
 *                       'EventText'     the event text
 *                       Context Data:
 *                       MIL_CODECONTEXT
 *                       'File'          the file name
 *                       'Function'      the function
 *                       'Line'          the line
 * @param array $OutData return data
 *                       'Success'       TRUE/FALSE
 *                       'ReturnValue'   The error record, as one line, or NULL if success is FALSE 
 * 
 * @return boolean TRUE for OK, FALSE for problems
 * @return array   $OutData by reference
 *                 'Success'       boolean TRUE for success, FALSE for fail
 *                 'ReturnValue'   int     Any return value that needs to be sent home.
 * @since 1.0
 * @todo
 */
function LineFormatter($InData, &$OutData)
{
    if (DEBUGMODE)
    {
        echo date("Y-m-d H:i:s").' -> LineFormatter '.PHP_EOL;
    }
    if (empty($InData))
    {
        //Need a record to format
        $MessageToCaller = 'Empty in data. Need a record to format';
        $OutData['Success'] = FALSE;
        $OutData['ReturnValue'] = $MessageToCaller;

        return FALSE;
    }
    else
    {
        if (!isset($InData['ServerDate'])||!isset($InData['LocalDate'])||!isset($InData['LogLevel'])||!isset($InData['EventText']))
        {
            //No standard data
            $MessageToCaller = 'No standard data. Review call';
            $OutData['Success'] = FALSE;
            $OutData['ReturnValue'] = $MessageToCaller;

            return FALSE;
        }
    }

    if (!empty($InData['Options']['Separator']))
    {
        $Separator = $InData['Options']['Separator'];
    }
    else
    {
        //Null separator, use empty string
        $Separator = '';
    }

    //Got standard data, at least
    $Helpers = $GLOBALS['CombinedHelpers'];

    $OneLiner = $InData['ServerDate'].$Separator.$InData['LocalDate'].$Separator.$InData['LogLevel'].$Separator.$InData['EventText'];
    //Faltan los datos de los helpers
    if ($Helpers&MIL_CODECONTEXT)
    {
        $OneLiner .= $Separator.$InData['File'].$Separator.$InData['Function'].$Separator.$InData['Line'];
    }
    $OneLiner .= PHP_EOL;
    $OutData['Success'] = TRUE;
    $OutData['ReturnValue'] = $OneLiner;

    return TRUE;
}

/**
 * function FormatErrorLog applier a formatter to an error log
 * @param  array   $InData
 * @param  array   $InData  input data. In this case, the record info and logger info
 *                          Logger Data:
 *                          'Channel'      the channel info
 *                          'Destination'  the destination info
 *                          'Helper'       the context info
 *                          'Formatter'    the formatter info
 *                          'Options'      the options info @see RegisterLogger
 *                          Standard Error Data:
 *                          'ServerDate'    the date in UTC
 *                          'LocalDate'     the date in local time
 *                          'LogLevel'      the log level
 *                          'EventText'     the event text
 *                          Context Error Data:
 *                          MIL_CODECONTEXT
 *                          'File'          the file name
 *                          'Function'      the function
 *                          'Line'          the line
 * @param  mixed   $OutData
 * @return boolean TRUE for OK, FALSE for problems
 * @return array   $OutData by reference
 *                         'Success'       boolean TRUE for success, FALSE for fail
 *                         'ReturnValue'   int     Any return value that needs to be sent home.
 * @since 1.0
 * @todo
 */
function FormatErrorLog($InData, &$OutData)
{
    if (DEBUGMODE)
    {
        echo date("Y-m-d H:i:s").' -> FormatErrorLog '.PHP_EOL;
    }
    $Formatter = $InData['Formatter'];
    switch ($Formatter)
    {
        case MIL_LINEFORMATTER:
            $Salida = LineFormatter($InData, $OutData);
            break;
        default:
            echo 'Unknown formatter: '.$Formatter.'. Defaulting to LINEFORMATTER';
            $Salida = LineFormatter($InData, $OutData);
            break;
    }
    //There goes the message, also
    return $Salida;
}

/**
 * Register2File register the log error to a MIL_FILE destination
 * @param  array   $InData
 *                          'FormattedLog'  Formatted error log
 *                          'Channel'       string  The chosen channel
 *                          'Destination'   int     The chosen destination(s)
 *                          'Helper'        int     The chosen context helper
 *                          'Formatter'     int     The chosen formatter;
 *                          'Options'       array   The channel options:
 *                          'LogPath'   string  The file path
 *                          'Separator' string  The field separator
 * @param  array   $OutData
 * @return boolean TRUE for OK, FALSE for problems
 * @return array   $OutData by reference
 *                         'Success'       boolean TRUE for success, FALSE for fail
 *                         'ReturnValue'   int     Any return value that needs to be sent home.
 * @since 1.0
 * @todo
 */
function Register2File($InData, &$OutData)
{
    if (DEBUGMODE)
    {
        echo date("Y-m-d H:i:s").' -> Register2File '.PHP_EOL;
    }
    //All checked... just write
    $Resultado = file_put_contents($InData['Options']['LogPath'], $InData['FormattedLog'], FILE_APPEND);
    if ($Resultado === FALSE)
    {
        $MessageToCaller = 'Fatal error writing ErrorRecord to File';
        $OutData['Success'] = FALSE;
        $OutData['ReturnValue'] = $MessageToCaller;

        return FALSE;
    }
    else
    {
        $OutData['Success'] = TRUE;
        $OutData['ReturnValue'] = $Resultado;

        return TRUE;
    }
}

/**
 * RegisterError routes the message to a destination through a channel
 * @param  array   $InData
 *                          'FormattedLog'  Formatted error log
 *                          'Channel'       string The chosen channel
 *                          'Destination'   int    The chosen destination(s)
 *                          'Helper'        int    The chosen context helper
 *                          'Formatter'     int    The chosen formatter;
 *                          'Options'       array  The channel options;
 * @param  array   $OutData
 * @return boolean TRUE for OK, FALSE for problems
 * @return array   $OutData by reference
 *                         'Success'       boolean TRUE for success, FALSE for fail
 *                         'ReturnValue'   int     Any return value that needs to be sent home.
 * @since 1.0
 * @todo
 */
function RegisterError($InData, &$OutData)
{
    if (DEBUGMODE)
    {
        echo date("Y-m-d H:i:s").' -> RegisterError '.PHP_EOL;
    }
    //There has already been a sanity check, so the Logger is coherent
    switch ($InData['Channel'])
    {
        case "FILES":
            switch ($InData['Destination'])
            {
                case MIL_FILE:
                    return Register2File($InData, $OutData);
                    break;
                case MIL_SYSLOG:
                case MIL_ERROLOG:
                case MIL_PROCESSLOG:
                    //Unimplemented
                    $MessageToCaller = 'Unimplemented destination. You can open an issue at https://github.com/ProceduralMan/MinionLib to notify your need.';
                    $OutData['Success'] = TRUE;
                    $OutData['ReturnValue'] = $MessageToCaller;

                    return FALSE;
                    break;
                default:
                    $MessageToCaller = 'Uncontrolled destination when registering log.';
                    $OutData['Success'] = TRUE;
                    $OutData['ReturnValue'] = $MessageToCaller;

                    return FALSE;
                    break;
            }
            break;
        case "EMAIL":
        case "SMS":
        case "IM":
        case "FLOWAUTO":
        case "NETLOGGING":
        case "QUEUES":
        case "DB":
        case "DEVLOGGING":
        default:
            $MessageToCaller = 'Uncontrolled channel when registering log.';
            $OutData['Success'] = FALSE;
            $OutData['ReturnValue'] = $MessageToCaller;

            return FALSE;
            break;
    }
}

/**
 * PreprocessErrorData prepares the combined error + logger info record for notificvation
 * @param  array   $Indata  has logger and error data
 *                          $InData['Logger']['Channel']                Channel Info
 *                          $InData['Logger']['Destination']            Destination Info
 *                          $InData['Logger']['Helper']                 Helper Info
 *                          $InData['Logger']['Formatter']              Formatter Info
 *                          $InData['Logger']['Options']                Options Info
 *                          $InData['Logger']['ErrorLevel']             ErrorLevel Info
 *                          $InData['ErrorRecord']['ServerDate']        ServerDate Info
 *                          $InData['ErrorRecord']['LocalDate']         LocalDate Info
 *                          $InData['ErrorRecord']['LogLevel']          LogLevel Info
 *                          $InData['ErrorRecord']['EventText']         EventText Info
 *                          if Helpers include MIL_CODECONTEXT
 *                          $InData['ErrorRecord']['File']          File Info
 *                          $InData['ErrorRecord']['Function']      Function Info
 *                          $InData['ErrorRecord']['Line']          Line Info
 * @param  array   $OutData
 * @param  mixed   $InData
 * @return boolean TRUE for OK, FALSE for problems
 * @return array   $OutData by reference
 *                         'Success'       boolean TRUE for success, FALSE for fail
 *                         'ReturnValue'   int     Any return value that needs to be sent home.
 * @since   v1.0
 * @todo
 */
function PreprocessErrorData($InData, &$OutData)
{
    if (DEBUGMODE)
    {
        echo date("Y-m-d H:i:s").' -> PreprocessErrorData '.PHP_EOL;
    }
    //Get Helpers info
    $Helpers = $GLOBALS['CombinedHelpers'];

    //Prepare data
    $SyncreticValue = array();
    $SyncreticValue['Channel'] = $InData['Logger']['Channel'];
    $SyncreticValue['Destination'] = $InData['Logger']['Destination'];
    $SyncreticValue['Helper'] = $InData['Logger']['Helper'];
    $SyncreticValue['Formatter'] = $InData['Logger']['Formatter'];
    $SyncreticValue['Options'] = $InData['Logger']['Options'];
    $SyncreticValue['ErrorLevel'] = $InData['Logger']['ErrorLevel'];
    $SyncreticValue['ServerDate'] = $InData['ErrorRecord']['ServerDate'];
    $SyncreticValue['LocalDate'] = $InData['ErrorRecord']['LocalDate'];
    switch ($InData['ErrorRecord']['LogLevel'])
    {
        case 100:
            $LogLevel = "DEBUG";
            break;
        case 200:
            $LogLevel = "INFO";
            break;
        case 250:
            $LogLevel = "NOTICE";
            break;
        case 300:
            $LogLevel = "WARNING";
            break;
        case 400:
            $LogLevel = "ERROR";
            break;
        case 500:
            $LogLevel = "CRITICAL";
            break;
        case 550:
            $LogLevel = "ALERT";
            break;
        case 600:
            $LogLevel = "EMERGENCY";
            break;
    }

    $SyncreticValue['LogLevel'] = $LogLevel;
    $SyncreticValue['EventText'] = $InData['ErrorRecord']['EventText'];
    if ($Helpers&MIL_CODECONTEXT)
    {
        $SyncreticValue['File'] = $InData['ErrorRecord']['File'];
        $SyncreticValue['Function'] = $InData['ErrorRecord']['Function'];
        $SyncreticValue['Line'] = $InData['ErrorRecord']['Line'];
    }
    $OutData['Success'] = TRUE;
    $OutData['ReturnValue'] = $SyncreticValue;

    return TRUE;
}

/**
 * NotifyError
 * Notifies the error on available handlers using available formatters
 * @param  array $OutData
 * @return void
 * @return array $OutData by reference
 *                       'Success'       boolean TRUE for success, FALSE for fail
 *                       'ReturnValue'   mixed   Any return value that needs to be sent home.
 * @return array $GLOBALS['LogicHeap']    NULL, if all goes OK
 * @since 1.0
 * @todo
 */
function NotifyError()
{
    if (DEBUGMODE)
    {
        echo date("Y-m-d H:i:s").' -> NotifyError '.PHP_EOL;
    }
    //Get LogData
    $ErrorLog = $GLOBALS['LogicHeap'];
    //IS it NULL?
    if (empty($ErrorLog))
    {
        if (DEBUGMODE)
        {
            echo date("Y-m-d H:i:s")." -> NotifyError: Empty GLOBALS['LogicHeap'] ".PHP_EOL;
        }
    }
    else
    {
        $MyOutData = array();

        if (DEBUGMODE)
        {
            if (isset($GLOBALS['Loggers']))
            {
                echo 'For '.count($GLOBALS['Loggers']).' logger'.PHP_EOL;
            }
            else
            {
                echo 'For no loggers, no notifications'.PHP_EOL;

                return;
            }
        }
        //For each logger
        foreach ($GLOBALS['Loggers'] as $Logger)
        {
            //Foreach error on the Heap
            if (DEBUGMODE)
            {
                echo 'For '.count($ErrorLog).' Errors'.PHP_EOL;
            }
            foreach ($ErrorLog as $ErrorRecord)
            {
                //Prepare syncretic record
                $PEDInData['Logger'] = $Logger;
                $PEDInData['ErrorRecord'] = $ErrorRecord;
                $PEDOutData = array();
                $PED = PreprocessErrorData($PEDInData, $PEDOutData);
                if ($PED === FALSE)
                {
                    AddNotifyError($MyOutData);
                }
                else
                {
                    if ($ErrorRecord['LogLevel']>=$Logger['ErrorLevel'])
                    {
                        $FELOutcome = FormatErrorLog($PEDOutData['ReturnValue'], $MyOutData);
                        if ($FELOutcome === FALSE)
                        {
                            AddNotifyError($MyOutData);
                        }
                        else
                        {
                            //Process the error
                            $InData = $Logger;
                            $InData['FormattedLog'] = $MyOutData['ReturnValue'];
                            $REOutcome = RegisterError($InData, $MyOutData);
                            if ($REOutcome === FALSE)
                            {
                                echo $MyOutData['ReturnValue'].PHP_EOL;

                                exit(2);
                            }
                        }
                    }
                }
                //Cleanse temporal variables
                unset($PEDOutData, $PEDInData);
            }
        }
        //All OK, Unset LogicHeap
        unset($GLOBALS['LogicHeap']);
        $GLOBALS['LogicHeap'] = NULL;
    }
}

/**
 * CustomErrorProcessor 
 * Custom Error Processor following PHP directives
 * @param  int     $ErrorLevel The level of the error raised, as an integer
 * @param  string  $Message    The error message, as a string
 * @param  string  $File       The filename that the error was raised in, as a string 
 * @param  int     $Line       The line number where the error was raised, as an integer
 * @param  array   $Context    An array of every variable that existed in the scope the error was triggered in
 * @return array   $GLOBALS['OutData'] by_reference
 * @return boolean FALSE to enable standard error processor
 * @since   v1.0
 * @todo treat $ResPE values
 */
function CustomErrorProcessor($ErrorLevel, $Message, $File, $Line, $Context)
{
    if (DEBUGMODE)
    {
        echo date("Y-m-d H:i:s").' -> CustomErrorProcessor->File: '.basename($File).' Line: '.$Line.' Msg: '.$Message.PHP_EOL;
    }
    $InData = array();
    $MyOutData = array();
    $InData['FileName'] = basename($File);
    $InData['Line'] = $Line;
    $InData['EventText'] = $Message;
    //Who called me?
    $Trace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
    if (isset($Trace[1]['function']))
    {
        $InData['Function'] = $Trace[1]['function'];
    }
    else
    {
        $InData['Function'] = '**Main**';
    }
    //$InData['Context'] = $Context; con el contexto de momento no hacemos nada

    switch ($ErrorLevel)
    {
        case E_ERROR:
        case E_USER_ERROR:
            $InData['LogLevel'] = ERROR;
            $ResPE = ProcessError($InData, $MyOutData);
            break;

        case E_WARNING:
        case E_USER_WARNING:
            $InData['LogLevel'] = WARNING;
            $ResPE = ProcessError($InData, $MyOutData);
            break;

        case E_NOTICE:
        case E_USER_NOTICE:
            $InData['LogLevel'] = NOTICE;
            $ResPE = ProcessError($InData, $MyOutData);
            break;

        default:
            $InData['LogLevel'] = ERROR;
            $InData['EventText'] = '¡¡OJO!! ERRORLEVEL '.$ErrorLevel.' NO CONTROLADO. '.$Message;
            $ResPE = ProcessError($InData, $MyOutData);
            break;
    }
    //Write the error ASAP
    NotifyError();
    //FALSE to enable standard error processing => THS FIRES FATALERRORPROCESSOR
    //return FALSE;
}

/**
 * FatalErrorProcessor 
 * Processes fatal errors the Minion way
 * @return void
 * @since   v1.0
 * @todo
 */
function FatalErrorProcessor()
{
    if (DEBUGMODE)
    {
        echo date("Y-m-d H:i:s").' -> FatalErrorProcessor '.PHP_EOL;
    }
    $LastError = error_get_last();
    if (is_null($LastError))
    {
        //No error... just a DIE or EXIT
        if (DEBUGMODE)
        {
            echo '-> No error, just a die or exit'.PHP_EOL;
        }
        //In case there are logs pending to notify
        //But only if there is Heap to Notify... maybe NotifyError() was called before process ending
        if (!empty($GLOBALS['LogicHeap']))
        {
            NotifyError();
        }

        return;
    }
    else
    {
        $InData = array();
        $MyOutData = array();
        $InData['LogLevel'] = CRITICAL;
        $InData['FileName'] = $LastError['file'];
        $InData['Line'] = $LastError['line'];
        //Get function from Error Message
        //If there is only a #0 function is main
        if (strpos($LastError['message'], '#1') === FALSE)
        {
            $TheFunction = 'Main';
        }
        else
        {
            $TheFunction = substr($LastError['message'], strpos($LastError['message'], ':', strpos($LastError['message'], '#0'))+2,
                    strpos($LastError['message'], '()', strpos($LastError['message'], '#0'))-
                    strpos($LastError['message'], ':', strpos($LastError['message'], '#0')));
        }
        if (DEBUGMODE)
        {
            echo 'Function is '.$TheFunction.PHP_EOL;
        }
        $InData['Function'] = $TheFunction;
        //$InData['EventText'] = $LastError['message'];
        $InData['EventText'] = trim(preg_replace('/\s+/', ' ', $LastError['message']));
        if (DEBUGMODE)
        {
            echo '-> File:'.basename($LastError['file']).' Line:'.$LastError['line'].' Msg:'.$LastError['message'].PHP_EOL;
        }
        //On runtime errors Loggers will be registered and bootstraped
        if (!is_null($GLOBALS['Loggers']))
        {
            $ResPE = ProcessError($InData, $MyOutData);
            if ($ResPE === FALSE)
            {
                AddPEError($MyOutData);
            }
            NotifyError();
        }
        else
        {
            //No registered loggers means there is a sintax error
            //@todo:do all the register->bootstrap->log process.
            echo '**CRITICAL ERROR** on '.basename($LastError['file']).' line: '.$LastError['line'].' msg:'.$LastError['message'].PHP_EOL;
        }
    }
}
