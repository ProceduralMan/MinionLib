<?php

/*
 * SQLDB
 * SQL Database Management the Minion way
 * The initial strategy of keeping an internal 'pool' of connections to reuse on the webpage did not survive reality check.
 * While it worked ok on CLI environments, the use on web sites required to cache the pool and the Databse connection is a 
 * resource object and thus non-cacheable.
 * The new strategy calls for atomic ops -open-make use of-close- and let the system underneath take care of pooling -persistent
 * connection on mysql case, ODBC pooling configuration on sqlsrv...-
 * @author ProceduralMan <proceduralman@gmail.com>
 * @copyright 2021-2022
 * @version 1.0 initial version
 * @package Minion
 * @todo INCLUIR TODAS LAS BASES DE DATOS DE FICHERO TIPO BERKELEYDB https://www.php.net/manual/es/intro.dba.php
 * @see
 * MySQL Info
 *      https://stackoverflow.com/questions/45080641/specifying-socket-option-on-mysqli-connect
 *      https://www.php.net/manual/es/mysqli.real-connect.php
 *      https://www.php.net/manual/es/mysqli.options.php
 *      https://dev.mysql.com/doc/connectors/en/apis-php-mysqli.options.html
 *      https://www.w3schools.com/php/func_mysqli_options.asp
 * SQLSRV Info
 *      https://www.php.net/manual/es/function.sqlsrv-configure.php
 *      https://docs.microsoft.com/en-us/sql/connect/php/how-to-configure-error-and-warning-handling-using-the-sqlsrv-driver?view=sql-server-ver15
 *      https://docs.microsoft.com/en-us/sql/connect/php/logging-activity?redirectedfrom=MSDN&view=sql-server-ver15
 *      https://www.php.net/manual/es/function.sqlsrv-connect.php
 *      https://docs.microsoft.com/en-us/sql/connect/php/connection-options?redirectedfrom=MSDN&view=sql-server-ver15
 *      https://docs.microsoft.com/en-us/sql/connect/php/azure-active-directory?view=sql-server-ver15
 *      https://docs.microsoft.com/en-us/azure/active-directory/managed-identities-azure-resources/overview
 *      https://docs.microsoft.com/en-us/azure/active-directory/develop/app-objects-and-service-principals
 *      https://docs.microsoft.com/en-us/sql/connect/php/php-driver-for-sql-server-support-for-high-availability-disaster-recovery?view=sql-server-ver15
 *      https://docs.microsoft.com/en-us/sql/connect/php/how-to-send-and-retrieve-utf-8-data-using-built-in-utf-8-support?view=sql-server-ver15
 *      https://docs.microsoft.com/en-us/sql/connect/php/connection-pooling-microsoft-drivers-for-php-for-sql-server?view=sql-server-ver15
 *      https://docs.microsoft.com/en-us/sql/connect/php/cursor-types-sqlsrv-driver?view=sql-server-ver15
 *      https://docs.microsoft.com/en-us/sql/t-sql/statements/set-transaction-isolation-level-transact-sql?view=sql-server-ver15
 *      https://docs.microsoft.com/es-es/sql/connect/php/how-to-connect-on-a-specified-port?view=sql-server-ver15
 * SQLITE3
 *      https://www.php.net/manual/es/sqlite3.construct.php (por aqui)
 */

//DB System Flags
define("MIL_CUBRID", "CUBRID");      // CUBRID DB
define("MIL_DBASE", "DBASE");       // DBASE DB
define("MIL_FIREBIRD", "FIREBIRD");    // FIREBIRD DB
define("MIL_INTERBASE", "INTERBASE");   // INTERBASE DB
define("MIL_DB2", "DB2");         // IBM DB2, IBM Cloudscape and Apache Derby DBs
define("MIL_CLOUDSCAPE", "DB2");         // IBM DB2, IBM Cloudscape and Apache Derby DBs
define("MIL_DERBY", "DB2");         // IBM DB2, IBM Cloudscape and Apache Derby DBs
define("MIL_MYSQL", "MYSQL");       // MYSQL DB
define("MIL_ORACLE", "ORACLE");      // ORACLE OCI8 DBs
define("MIL_POSTGRE", "POSTGRE");     // MYSQL DB
define("MIL_SQLITE", "SQLITE");      // SQLITE3 DB
define("MIL_SQLSRV", "MS-SQL-S");    // SQLSRV DB

//Registered DBSystem info
$GLOBALS['DB'] = NULL;

/**
 * RegisterMySQLConnection
 * Front-end function to register a connection to MySQL.
 * When used on web pages it should be caled just once at session creation.
 * The function RegisterDBSystem includes safety mechanisms to avoid create multiple sibling pools on concurrent usage
 * @param string    $ServerName             The hostname
 * @param string    $Database               The database name
 * @param string    $DBUser                 The user
 * @param string    $DBPassword             The password
 * @param boolean   $Persistent             Flag to make a persistent connection
 * @param int       $ConnectionTimeout      Controls connection timeout in seconds
 * @param int       $CommandTimeout         Controls command execution result timeout in seconds
 * @param boolean   $UseLocalInfile         Controls Enable/disable use of LOAD LOCAL INFILE to allow load of big files into a table
 * @param string    $InitCommand            Command to execute after when connecting to MySQL server
 * @param string    $Charset                The charset to use. See function IsValidCharset on DataValidation.php
 * @param string    $OptionsFile            Read options from named option file instead of my.cnf
 * @param string    $DefaultGroup           Read options from the named group from my.cnf or the file specified with MYSQL_READ_DEFAULT_FILE
 * @param string    $ServerPublicKey        RSA public key file used with the SHA-256 based authentication
 * @param boolean   $CompressionProtocol    Use compression protocol on the connection.
 * @param boolean   $FoundRows              Return number of matched rows, not the number of affected rows.
 * @param boolean   $IgnoreSpaces           Allow spaces after function names. Makes all function names reserved words.
 * @param boolean   $InteractiveClient      Allow interactive_timeout seconds (instead of wait_timeout seconds) of inactivity before closing the connection.
 * @param boolean   $UseSSL                 Use SSL (encryption).
 * @param boolean   $DoNotVerifyServerCert  Use SSL while disabling validation of the provided SSL certificate.
 * @param int       $Port                   Sets PORT on network connections
 * @param string    $Socket                 Sets UNIX socket on local connections (localhost)
 * @return mixed    The connection index or FALSE on error
 * @since 0.0.3
 * @see
 * @todo
 */
function RegisterMySQLConnection($ServerName, $Database, $DBUser, $DBPassword, $Persistent = FALSE, $ConnectionTimeout = NULL,
    $CommandTimeout = NULL, $UseLocalInfile = NULL, $InitCommand = NULL, $Charset = NULL, $OptionsFile = NULL, $DefaultGroup = NULL, $ServerPublicKey = NULL,
    $CompressionProtocol = NULL, $FoundRows = NULL, $IgnoreSpaces = NULL, $InteractiveClient = NULL, $UseSSL = NULL, $DoNotVerifyServerCert = NULL,
    $Port = NULL, $Socket = NULL)
{
    if (DEBUGMODE)
    {
        echo date("Y-m-d H:i:s").' -> RegisterMySQLConnection '.PHP_EOL;
    }

    //Prepare compulsory Data
    $InData['System'] = MIL_MYSQL;
    $InData['Database'] = $Database;
    $InData['DBUser'] = $DBUser;
    $InData['DBPassword'] = $DBPassword;
    //var_dump($Persistent);
    $InData['Options']['Persistent'] = $Persistent;
    if ($Persistent === TRUE)
    {
        $InData['ServerName'] = 'p:'.$ServerName;
    }
    else
    {
        $InData['ServerName'] = $ServerName;
    }
    //Prepare optional Data
    if (!is_null($ConnectionTimeout))
    {
        $InData['Options']['ConnectionTimeout'] = $ConnectionTimeout;
    }
    if (!is_null($CommandTimeout))
    {
        $InData['Options']['CommandTimeout'] = $CommandTimeout;
    }
    if (!is_null($UseLocalInfile))
    {
        $InData['Options']['UseLocalInfile'] = $UseLocalInfile;
    }
    if (!is_null($InitCommand))
    {
        $InData['Options']['InitCommand'] = $InitCommand;
    }
    if (!is_null($Charset))
    {
        $InData['Options']['Charset'] = $Charset;
    }
    if (!is_null($OptionsFile))
    {
        $InData['Options']['OptionsFile'] = $OptionsFile;
    }
    if (!is_null($DefaultGroup))
    {
        $InData['Options']['DefaultGroup'] = $DefaultGroup;
    }
    if (!is_null($ServerPublicKey))
    {
        $InData['Options']['ServerPublicKey'] = $ServerPublicKey;
    }
    if (!is_null($CompressionProtocol))
    {
        $InData['Options']['CompressionProtocol'] = $CompressionProtocol;
    }
    if (!is_null($FoundRows))
    {
        $InData['Options']['FoundRows'] = $FoundRows;
    }
    if (!is_null($IgnoreSpaces))
    {
        $InData['Options']['IgnoreSpaces'] = $IgnoreSpaces;
    }
    if (!is_null($InteractiveClient))
    {
        $InData['Options']['InteractiveClient'] = $InteractiveClient;
    }
    if (!is_null($UseSSL))
    {
        $InData['Options']['UseSSL'] = $UseSSL;
    }
    if (!is_null($DoNotVerifyServerCert))
    {
        $InData['Options']['DoNotVerifyServerCert'] = $DoNotVerifyServerCert;
    }
    if (!is_null($Port))
    {
        $InData['Options']['Port'] = $Port;
    }
    if (!is_null($Socket))
    {
        $InData['Options']['Socket'] = $Socket;
    }
    $OutData = array();
    $Result = DBSystemSanityCheck($InData, $OutData);
    if ($Result === FALSE)
    {
        $ErrorMessage = 'Error checking MySQL connection: '.$OutData['ReturnValue'];
        echo $ErrorMessage.PHP_EOL;
        ErrorLog($ErrorMessage, E_USER_ERROR);

        return FALSE;
    }
    else
    {
        $RegisterResult = RegisterDBSystem($InData, $OutData);
        if ($RegisterResult === FALSE)
        {
            $ErrorMessage = 'Error registering MySQL connection: '.$OutData['ReturnValue'];
            echo $ErrorMessage.PHP_EOL;
            ErrorLog($ErrorMessage, E_USER_ERROR);

            return FALSE;
        }
        else
        {
            return $RegisterResult;
        }
    }
}

 /**
  * OldRegisterMySQLConnection
  * Front-end function to register a connection to MySQL.
  * When used on web pages it should be caled just once at session creation.
  * The function RegisterDBSystem includes safety mechanisms to avoid create multiple sibling pools on concurrent usage
  * @since 0.0.3
  * @see
  * @todo
  * @deprecated  will be substituted by an atomic variant
  * @param mixed $ServerName
  * @param mixed $Database
  * @param mixed $DBUser
  * @param mixed $DBPassword
  * @param mixed $Persistent
  * @param null|mixed $ConnectionTimeout
  * @param null|mixed $CommandTimeout
  * @param null|mixed $UseLocalInfile
  * @param null|mixed $InitCommand
  * @param null|mixed $Charset
  * @param null|mixed $OptionsFile
  * @param null|mixed $DefaultGroup
  * @param null|mixed $ServerPublicKey
  * @param null|mixed $CompressionProtocol
  * @param null|mixed $FoundRows
  * @param null|mixed $IgnoreSpaces
  * @param null|mixed $InteractiveClient
  * @param null|mixed $UseSSL
  * @param null|mixed $DoNotVerifyServerCert
  * @param null|mixed $Port
  * @param null|mixed $Socket
  */
function OldRegisterMySQLConnection($ServerName, $Database, $DBUser, $DBPassword, $Persistent = FALSE, $ConnectionTimeout = NULL,
    $CommandTimeout = NULL, $UseLocalInfile = NULL, $InitCommand = NULL, $Charset = NULL, $OptionsFile = NULL, $DefaultGroup = NULL, $ServerPublicKey = NULL,
    $CompressionProtocol = NULL, $FoundRows = NULL, $IgnoreSpaces = NULL, $InteractiveClient = NULL, $UseSSL = NULL, $DoNotVerifyServerCert = NULL,
    $Port = NULL, $Socket = NULL)
{
    if (DEBUGMODE)
    {
        echo date("Y-m-d H:i:s").' -> RegisterMySQLConnection '.PHP_EOL;
    }

    //Prepare compulsory Data
    $InData['System'] = MIL_MYSQL;
    $InData['ServerName'] = $ServerName;
    $InData['Database'] = $Database;
    $InData['DBUser'] = $DBUser;
    $InData['DBPassword'] = $DBPassword;
    $InData['Options']['Persistent'] = $Persistent;

    //Prepare optional Data
    if (!is_null($ConnectionTimeout))
    {
        $InData['Options']['ConnectionTimeout'] = $ConnectionTimeout;
    }
    if (!is_null($CommandTimeout))
    {
        $InData['Options']['CommandTimeout'] = $CommandTimeout;
    }
    if (!is_null($UseLocalInfile))
    {
        $InData['Options']['UseLocalInfile'] = $UseLocalInfile;
    }
    if (!is_null($InitCommand))
    {
        $InData['Options']['InitCommand'] = $InitCommand;
    }
    if (!is_null($Charset))
    {
        $InData['Options']['Charset'] = $Charset;
    }
    if (!is_null($OptionsFile))
    {
        $InData['Options']['OptionsFile'] = $OptionsFile;
    }
    if (!is_null($DefaultGroup))
    {
        $InData['Options']['DefaultGroup'] = $DefaultGroup;
    }
    if (!is_null($ServerPublicKey))
    {
        $InData['Options']['ServerPublicKey'] = $ServerPublicKey;
    }
    if (!is_null($CompressionProtocol))
    {
        $InData['Options']['CompressionProtocol'] = $CompressionProtocol;
    }
    if (!is_null($FoundRows))
    {
        $InData['Options']['FoundRows'] = $FoundRows;
    }
    if (!is_null($IgnoreSpaces))
    {
        $InData['Options']['IgnoreSpaces'] = $IgnoreSpaces;
    }
    if (!is_null($InteractiveClient))
    {
        $InData['Options']['InteractiveClient'] = $InteractiveClient;
    }
    if (!is_null($UseSSL))
    {
        $InData['Options']['UseSSL'] = $UseSSL;
    }
    if (!is_null($DoNotVerifyServerCert))
    {
        $InData['Options']['DoNotVerifyServerCert'] = $DoNotVerifyServerCert;
    }
    if (!is_null($Port))
    {
        $InData['Options']['Port'] = $Port;
    }
    if (!is_null($Socket))
    {
        $InData['Options']['Socket'] = $Socket;
    }
    $OutData = array();
    $Result = DBSystemSanityCheck($InData, $OutData);
    if ($Result === FALSE)
    {
        $ErrorMessage = 'Error checking MySQL connection: '.$OutData['ReturnValue'];
        echo $ErrorMessage.PHP_EOL;
        ErrorLog($ErrorMessage, E_USER_ERROR);

        return FALSE;
    }
    else
    {
        //If there is a pool defined, we persist the pool info using APCU (REDIS to come)
        if (is_int(MIL_POOLEDCONNECTIONS))
        {
            if (MIL_ACPU === TRUE)
            {
                $RegisterResult = RegisterDBSystem($InData, $OutData);
                if ($RegisterResult === FALSE)
                {
                    $ErrorMessage = 'Error registering MySQL connection: '.$OutData['ReturnValue'];
                    echo $ErrorMessage.PHP_EOL;
                    ErrorLog($ErrorMessage, E_USER_ERROR);

                    return FALSE;
                }
                else
                {
                    return $RegisterResult;
                }
            }   //There is ACPU present
            else
            {
                //NO APCU... no pool
                $ErrorMessage = 'No persistence layer to keep the pool on. Please setup APCU and enable MIL_ACPU on MinionSetup.php';
                echo $ErrorMessage.PHP_EOL;
                ErrorLog($ErrorMessage, E_USER_ERROR);

                return FALSE;
            }   //There isn't ACPU present
        }   //There is a pool size defined
    }   //Connection sanity check OK
}

/**
 * DBSystemSanityCheck checks that all needed configuration is in place. Just formal checking, no pre-connection
 * @param array $InData
 *                      'System'                The chosen DB System: CUBRID, DBASE, FIREBIRD, DB2(Including CLOUDSCAPE and DERBY), MYSQL, ORACLE, POSTGRE, SQLITE or SQLSRV
 *                      'ServerName'            The chosen server. 'localhost' or nothing for SQLLite
 *                      'Database'              The Database
 *                      'DBUser'                The User
 *                      'DBPassword'            The Password
 *                      'Options'               System-specific options
 *                                      MySQL
 *                                          'Persistent'                Makes a connection persistent by prepending p: to the hostname
 *                                          'ConnectionTimeout'         Flag MYSQLI_OPT_CONNECT_TIMEOUT, controls connection timeout in seconds
 *                                          'CommandTimeout'            Flag MYSQLI_OPT_READ_TIMEOUT, controls Command execution result timeout in seconds.
 *                                                                          Available as of PHP 7.2.0.
 *                                          'UseLocalInfile'            Flag MYSQLI_OPT_LOCAL_INFILE, controls Enable/disable use of LOAD LOCAL INFILE to allow
 *                                                                          load of big files into a table
 *                                          'InitCommand'               Flag MYSQLI_INIT_COMMAND, command to execute after when connecting to MySQL server
 *                                          'Charset'                   Flag MYSQLI_SET_CHARSET_NAME, the charset to be set as default (duplicado de $CharacterSet)
 *                                          'OptionsFile'               Flag MYSQLI_READ_DEFAULT_FILE, read options from named option file instead of my.cnf
 *                                          'DefaultGroup'              Flag MYSQLI_READ_DEFAULT_GROUP, read options from the named group from my.cnf or the
 *                                                                          file specified with MYSQL_READ_DEFAULT_FILE
 *                                          'ServerPublicKey'           Flag MYSQLI_SERVER_PUBLIC_KEY, RSA public key file used with the SHA-256 based
 *                                                                          authentication
 *                                          'CompressionProtocol'       Sets MYSQLI_CLIENT_COMPRESS on mysqli_real_connect to use compression protocol on the
 *                                                                          connection
 *                                          'FoundRows'                 Sets MYSQLI_CLIENT_FOUND_ROWS on mysqli_real_connect to return number of matched rows,
 *                                                                          not the number of affected rows
 *                                          'IgnoreSpaces'              Sets MYSQLI_CLIENT_IGNORE_SPACE on mysqli_real_connect to allow spaces after function
 *                                                                          names. Makes all function names reserved words.
 *                                          'InteractiveClient'         Sets MYSQLI_CLIENT_INTERACTIVE on mysqli_real_connect to allow interactive_timeout
 *                                                                          seconds (instead of wait_timeout seconds) of inactivity before closing the
 *                                                                          connection
 *                                          'UseSSL'                    Sets MYSQLI_CLIENT_SSL on mysqli_real_connect to use SSL (encryption)
 *                                          'DoNotVerifyServerCert'     Sets MYSQLI_CLIENT_SSL_DONT_VERIFY_SERVER_CERT on mysqli_real_connect to use SSL while
 *                                                                          disabling validation of the provided SSL certificate.
 *                                          'Port'                      Sets PORT on network connections
 *                                          'Socket'                    Sets UNIX socket on local connections (localhost)
 *                  ***THE BELOW ONBES ARE INVALID***
 *                       'VerifySSL'                 Flag MYSQLI_OPT_SSL_VERIFY_SERVER_CERT, used when you want to verify server certificate against well known
 *                                                      authorities to ensure that this is connection to trusted host. Do not use it if you have self-signed
 *                                                      certificate on server.
 *                       'ConnectionTimeout'         Flag MYSQLI_OPT_CONNECT_TIMEOUT, controls connection timeout in seconds (pasar a apartados por sistema)
 *                       'CommandTimeout'            Flag MYSQLI_OPT_READ_TIMEOUT, controls Command execution result timeout in seconds.
 *                       'ServerPublicKey'           Flag MYSQLI_SERVER_PUBLIC_KEY, RSA public key file used with the SHA-256 based authentication
 *                       'UseLocalInfile'            Flag MYSQLI_OPT_LOCAL_INFILE, controls Enable/disable use of LOAD LOCAL INFILE to allow
 *                                                      load of big files into a table
 *                       'OptionsFile'               Flag MYSQLI_READ_DEFAULT_FILE, read options from named option file instead of my.cnf
 *                       'DefaultGroup'              Flag MYSQLI_READ_DEFAULT_GROUP, read options from the named group from my.cnf or the
 *                                                     file specified with MYSQL_READ_DEFAULT_FILE
 *
 *
 *
 *                       'LoginTimeout'           The Timeout
 *                       'Encrypt'                Must the connection be encrypted?
 *                       'TrustServerCertificate' Must the server certificate not be checked?
 *                       'System'       string The chosen channel
 *                       'Destination'   int    The chosen destination(s)
 *                       'Helper'        int    The chosen context helper
 *                       'Formatter'     int    The chosen formatter;
 *                       'Options'       array  The channel options;
 * @param array $OutData
 *
 * @return boolean TRUE for OK, FALSE for problems
 * @return array   $OutData by reference
 *                 'Success'       boolean TRUE for success, FALSE for fail
 *                 'ReturnValue'   mixed   Any return value that needs to be sent home.
 * @since 0.0.3
 * @see
 * @todo support persistent connections = connection pooling on MySQL (by adding 'p:' to the hostname)
 * Evaluar si el socket es necesario... solo vale para localhost...
 */
function DBSystemSanityCheck($InData, &$OutData)
{
    if (DEBUGMODE)
    {
        echo date("Y-m-d H:i:s").' -> DBSystemSanityCheck '.PHP_EOL;
        print_r($InData);
    }

    switch ($InData['System'])
    {
        case "MYSQL":
            //Check Parameters
            $AllGood = TRUE;
            $InvalidParameters = '';
            foreach ($InData as $Key => $Value)
            {
                if ($Key !== 'System')
                {
                    switch ($Key) {
                        case 'ServerName':
                            if ($InData['Options']['Persistent'] === TRUE)
                            {
                                $RealHostname = substr($Value, 2);
                            }
                            else
                            {
                                $RealHostname = $Value;
                            }
                            $TestOption = IsValidHost($RealHostname);
                            if ($TestOption === FALSE)
                            {
                                $AllGood = $TestOption;
                                $InvalidParameters .= 'ServerName:'.$RealHostname.', ';
                            }
                            break;
                        case 'Database':
                            $TestOption = IsValidMySQLName($Value);
                            if ($TestOption === FALSE)
                            {
                                $AllGood = $TestOption;
                                $InvalidParameters .= 'Database:'.$Value.', ';
                            }
                            break;
                        case 'DBUser':
                            $TestOption = IsValidMySQLUser($Value);
                            if ($TestOption === FALSE)
                            {
                                $AllGood = $TestOption;
                                $InvalidParameters .= 'DBUser:'.$Value.', ';
                            }
                            break;
                        case 'DBPassword':
                            //Paswords are text fields so virtually limitless... just check if is pure UTF-8
                            $TestOption = IsValidUTF8Text($Value);
                            if ($TestOption === FALSE)
                            {
                                $AllGood = $TestOption;
                                $InvalidParameters .= 'DBPassword:'.$Value.', ';
                            }
                            break;
                        case 'Options':
                            //Toca un inner foreach para cada una de las opciones
                            foreach ($InData[$Key] as $OptionKey => $OptionValue)
                            {
                                switch ($OptionKey)
                                {
                                    case 'Persistent':
                                        //As here hostname is correct, if persistent is enabled, we prepend the p:
                                        $TestOption = is_bool($OptionValue);
                                        if ($TestOption === FALSE)
                                        {
                                            $AllGood = $TestOption;
                                            $InvalidParameters .= 'Persistent:'.var_dump($OptionValue).', ';
                                        }
                                        /*
                                         * Already included on the registering... is more visible there
                                        else
                                        {
                                            if ($OptionValue === TRUE)
                                            {
                                                $InData['ServerName'] = 'p:'.$InData['ServerName'];
                                            }
                                        }
                                         * 
                                         */
                                        break;
                                    case 'ConnectionTimeout':
                                        //Any int seems valid. The bigger the timeout the sooner server resources might be exhausted
                                        $TestOption = is_int($OptionValue);
                                        if ($TestOption === FALSE)
                                        {
                                            $AllGood = $TestOption;
                                            $InvalidParameters .= 'ConnectionTimeout:'.$OptionValue.', ';
                                        }
                                        break;
                                    case 'CommandTimeout':
                                        //Any int seems valid. The bigger the timeout the sooner server resources might be exhausted
                                        $TestOption = is_int($OptionValue);
                                        if ($TestOption === FALSE)
                                        {
                                            $AllGood = $TestOption;
                                            $InvalidParameters .= 'CommandTimeout:'.$OptionValue.', ';
                                        }
                                        break;
                                    case 'UseLocalInfile':
                                        //Admits TRUE, defaults on FALSE
                                        //Server var local_infile must be set to ON to use local infiles
                                        //@see https://dev.mysql.com/doc/refman/8.0/en/server-system-variables.html#sysvar_local_infile
                                        $TestOption = is_bool($OptionValue);
                                        if ($TestOption === FALSE)
                                        {
                                            $AllGood = $TestOption;
                                            $InvalidParameters .= 'UseLocalInfile:'.var_dump($OptionValue).', ';
                                        }
                                        break;
                                    case 'InitCommand':
                                        //Any string will do
                                        $TestOption = is_string($OptionValue);
                                        if ($TestOption === FALSE)
                                        {
                                            $AllGood = $TestOption;
                                            $InvalidParameters .= 'InitCommand:'.$OptionValue.', ';
                                        }
                                        break;
                                    case 'Charset':
                                        //Check if charset is kosher
                                        $TestOption = IsValidCharset($OptionValue, $InData['System']);
                                        if ($TestOption === FALSE)
                                        {
                                            $AllGood = $TestOption;
                                            $InvalidParameters .= 'Charset:'.$OptionValue.', ';
                                        }
                                        break;
                                    case 'OptionsFile':
                                        //Must be a valid path and exist
                                        $TestOption = is_readable($OptionValue);
                                        if ($TestOption === FALSE)
                                        {
                                            $AllGood = $TestOption;
                                            $InvalidParameters .= 'OptionsFile:'.$OptionValue.', ';
                                        }
                                        break;
                                    case 'DefaultGroup':
                                        //Must be a valid string. Can be a [group] or a server_variable= as in the sample from
                                        //https://www.php.net/manual/es/mysqli.options.php
                                        //See https://dev.mysql.com/doc/refman/8.0/en/option-files.html
                                        $TestOption = is_string($OptionValue);
                                        if ($TestOption === FALSE)
                                        {
                                            $AllGood = $TestOption;
                                            $InvalidParameters .= 'DefaultGroup:'.$OptionValue.', ';
                                        }
                                        break;
                                    case 'ServerPublicKey':
                                        //Must be a valid path and exist
                                        $TestOption = is_readable($Value);
                                        if ($TestOption === FALSE)
                                        {
                                            $AllGood = $TestOption;
                                            $InvalidParameters .= 'ServerPublicKey:'.$Value.', ';
                                        }
                                        break;
                                    case 'CompressionProtocol':
                                        //Admits TRUE, defaults on FALSE
                                        $TestOption = is_bool($OptionValue);
                                        if ($TestOption === FALSE)
                                        {
                                            $AllGood = $TestOption;
                                            $InvalidParameters .= 'CompressionProtocol:'.var_dump($OptionValue).', ';
                                        }
                                        break;
                                    case 'FoundRows':
                                        //Admits TRUE, defaults on FALSE
                                        $TestOption = is_bool($OptionValue);
                                        if ($TestOption === FALSE)
                                        {
                                            $AllGood = $TestOption;
                                            $InvalidParameters .= 'VerifySSL:'.var_dump($OptionValue).', ';
                                        }
                                        break;
                                    case 'IgnoreSpaces':
                                        //Admits TRUE, defaults on FALSE
                                        $TestOption = is_bool($OptionValue);
                                        if ($TestOption === FALSE)
                                        {
                                            $AllGood = $TestOption;
                                            $InvalidParameters .= 'IgnoreSpaces:'.var_dump($OptionValue).', ';
                                        }
                                        break;
                                    case 'InteractiveClient':
                                        //Admits TRUE, defaults on FALSE
                                        $TestOption = is_bool($OptionValue);
                                        if ($TestOption === FALSE)
                                        {
                                            $AllGood = $TestOption;
                                            $InvalidParameters .= 'InteractiveClient:'.var_dump($OptionValue).', ';
                                        }
                                        break;
                                    case 'UseSSL':
                                        //Admits TRUE, defaults on FALSE
                                        $TestOption = is_bool($OptionValue);
                                        if ($TestOption === FALSE)
                                        {
                                            $AllGood = $TestOption;
                                            $InvalidParameters .= 'UseSSL:'.var_dump($OptionValue).', ';
                                        }
                                        break;
                                    case 'DoNotVerifyServerCert':
                                        //Admits TRUE, defaults on FALSE
                                        $TestOption = is_bool($OptionValue);
                                        if ($TestOption === FALSE)
                                        {
                                            $AllGood = $TestOption;
                                            $InvalidParameters .= 'DoNotVerifyServerCert:'.var_dump($OptionValue).', ';
                                        }
                                        break;
                                    case 'Port':
                                        //string value
                                        $TestOption = IsAdequateDatabasePort($InData['System'], $OptionValue);
                                        if ($TestOption['code'] === 'WRONG')
                                        {
                                            $AllGood = FALSE;
                                            $InvalidParameters .= 'Wrong port: '.$OptionValue.', ';
                                        }
                                        break;
                                    case 'Socket':
                                        //Not good if not localhost or not accessible
                                        if ($InData['Options']['Persistent'] === TRUE)
                                        {
                                            $ProperServer = 'p:localhost';
                                        }
                                        else
                                        {
                                            $ProperServer = 'localhost';
                                        }
                                        if ($InData['ServerName'] !== $ProperServer)
                                        {
                                            $AllGood = FALSE;
                                            $InvalidParameters .= 'Socket: '.$OptionValue.' used without specifying "localhost" as server name ('.$InData['ServerName'].')';
                                        }
                                        elseif (is_writable($OptionValue) === FALSE)
                                        {
                                            $AllGood = FALSE;
                                            $InvalidParameters .= 'Socket: '.$OptionValue.' does not exist or is not writeable.';
                                        }
                                        elseif ($InData['Options']['UseSSL'] === TRUE)
                                        {
                                            //If there is set an SSL connection, it will fail over a Socket
                                            $AllGood = FALSE;
                                            $InvalidParameters .= 'SSL not available over a Socket connection. Disable UseSSL.';
                                        }
                                        break;
                                    default:
                                        $OutData['Success'] = FALSE;
                                        $OutData['ReturnValue'] = 'Unknown mysql connection option: '.$OptionKey;

                                        return FALSE;
                                }
                            }
                            break;
                        default:
                            $OutData['Success'] = FALSE;
                            $OutData['ReturnValue'] = 'Unknown mysql connection parameter: '.$Key;

                            return FALSE;

                    }
                }
            }
            if ($AllGood === FALSE)
            {
                $OutData['Success'] = TRUE;
                $OutData['ReturnValue'] = 'Invalid parameters->'.$InvalidParameters;

                return FALSE;
            }

            $OutData['Success'] = TRUE;
            $OutData['ReturnValue'] = TRUE;


            return TRUE;
        case "SQLITE":
            $OutData['Success'] = TRUE;
            $OutData['ReturnValue'] = 'Unimplemented system. Open an issue in https://github.com/ProceduralMan/MinionLib if you need it';

            return FALSE;
        case "SQLSRV":
            $OutData['Success'] = TRUE;
            $OutData['ReturnValue'] = 'Unimplemented system. Open an issue in https://github.com/ProceduralMan/MinionLib if you need it';

            return FALSE;
        case "CUBRID":
        case "DBASE":
        case "FIREBIRD":
        case "INTERBASE":
        case "DB2":
        case "ORACLE":
        case "DB":
        case "DEVLOGGING":
            $OutData['Success'] = TRUE;
            $OutData['ReturnValue'] = 'Unimplemented system. Open an issue in https://github.com/ProceduralMan/MinionLib if you need it';

            return FALSE;
        default:
            $OutData['Success'] = TRUE;
            $OutData['ReturnValue'] = 'Odd system: '.$InData['System'];

            return FALSE;
    }
}

 /**
  * OldDBSystemSanityCheck checks that all needed configuration is in place
  * @param array $OutData
  * @param mixed $InData
  *
  * @return boolean TRUE for OK, FALSE for problems
  * @return array   $OutData by reference
  *                 'Success'       boolean TRUE for success, FALSE for fail
  *                 'ReturnValue'   mixed   Any return value that needs to be sent home.
  * @since 0.0.3
  * @see
  * @todo support persistent connections = connection pooling on MySQL (by adding 'p:' to the hostname)
  * Evaluar si el socket es necesario... solo vale para localhost...
  * @deprecated  will be substituted by an atomic variant
  */
function OldDBSystemSanityCheck($InData, &$OutData)
{
    if (DEBUGMODE)
    {
        echo date("Y-m-d H:i:s").' -> DBSystemSanityCheck '.PHP_EOL;
        print_r($InData);
    }


    switch ($InData['System'])
    {
        case "MYSQL":
            //Check Parameters
            $AllGood = TRUE;
            $InvalidParameters = '';
            foreach ($InData as $Key => $Value)
            {
                if ($Key !== 'System')
                {
                    switch ($Key) {
                        case 'ServerName':
                            $TestOption = IsValidHost($Value);
                            if ($TestOption === FALSE)
                            {
                                $AllGood = $TestOption;
                                $InvalidParameters .= 'ServerName:'.$Value.', ';
                            }
                            break;
                        case 'Database':
                            $TestOption = IsValidMySQLName($Value);
                            if ($TestOption === FALSE)
                            {
                                $AllGood = $TestOption;
                                $InvalidParameters .= 'Database:'.$Value.', ';
                            }
                            break;
                        case 'DBUser':
                            $TestOption = IsValidMySQLUser($Value);
                            if ($TestOption === FALSE)
                            {
                                $AllGood = $TestOption;
                                $InvalidParameters .= 'DBUser:'.$Value.', ';
                            }
                            break;
                        case 'DBPassword':
                            //Paswords are text fields so virtually limitless... just check if is pure UTF-8
                            $TestOption = IsValidUTF8Text($Value);
                            if ($TestOption === FALSE)
                            {
                                $AllGood = $TestOption;
                                $InvalidParameters .= 'DBPassword:'.$Value.', ';
                            }
                            break;
                        case 'Options':
                            //Toca un inner foreach para cada una de las opciones
                            foreach ($InData[$Key] as $OptionKey => $OptionValue)
                            {
                                switch ($OptionKey)
                                {
                                    case 'Persistent':
                                        //As here hostname is correct, if persistent is enabled, we prepend the p:
                                        $TestOption = is_bool($OptionValue);
                                        if ($TestOption === FALSE)
                                        {
                                            $AllGood = $TestOption;
                                            $InvalidParameters .= 'Persistent:'.var_dump($OptionValue).', ';
                                        }
                                        else
                                        {
                                            if ($OptionValue === TRUE)
                                            {
                                                $InData['ServerName'] = 'p:'.$InData['ServerName'];
                                            }
                                        }
                                        break;
                                    case 'ConnectionTimeout':
                                        //Any int seems valid. The bigger the timeout the sooner server resources might be exhausted
                                        $TestOption = is_int($OptionValue);
                                        if ($TestOption === FALSE)
                                        {
                                            $AllGood = $TestOption;
                                            $InvalidParameters .= 'ConnectionTimeout:'.$OptionValue.', ';
                                        }
                                        break;
                                    case 'CommandTimeout':
                                        //Any int seems valid. The bigger the timeout the sooner server resources might be exhausted
                                        $TestOption = is_int($OptionValue);
                                        if ($TestOption === FALSE)
                                        {
                                            $AllGood = $TestOption;
                                            $InvalidParameters .= 'CommandTimeout:'.$OptionValue.', ';
                                        }
                                        break;
                                    case 'UseLocalInfile':
                                        //Admits TRUE, defaults on FALSE
                                        //Server var local_infile must be set to ON to use local infiles
                                        //@see https://dev.mysql.com/doc/refman/8.0/en/server-system-variables.html#sysvar_local_infile
                                        $TestOption = is_bool($OptionValue);
                                        if ($TestOption === FALSE)
                                        {
                                            $AllGood = $TestOption;
                                            $InvalidParameters .= 'UseLocalInfile:'.var_dump($OptionValue).', ';
                                        }
                                        break;
                                    case 'InitCommand':
                                        //Any string will do
                                        $TestOption = is_string($OptionValue);
                                        if ($TestOption === FALSE)
                                        {
                                            $AllGood = $TestOption;
                                            $InvalidParameters .= 'InitCommand:'.$OptionValue.', ';
                                        }
                                        break;
                                    case 'Charset':
                                        //Check if charset is kosher
                                        $TestOption = IsValidCharset($OptionValue, $InData['System']);
                                        if ($TestOption === FALSE)
                                        {
                                            $AllGood = $TestOption;
                                            $InvalidParameters .= 'Charset:'.$OptionValue.', ';
                                        }
                                        break;
                                    case 'OptionsFile':
                                        //Must be a valid path and exist
                                        $TestOption = is_readable($OptionValue);
                                        if ($TestOption === FALSE)
                                        {
                                            $AllGood = $TestOption;
                                            $InvalidParameters .= 'OptionsFile:'.$OptionValue.', ';
                                        }
                                        break;
                                    case 'DefaultGroup':
                                        //Must be a valid string. Can be a [group] or a server_variable= as in the sample from
                                        //https://www.php.net/manual/es/mysqli.options.php
                                        //See https://dev.mysql.com/doc/refman/8.0/en/option-files.html
                                        $TestOption = is_string($OptionValue);
                                        if ($TestOption === FALSE)
                                        {
                                            $AllGood = $TestOption;
                                            $InvalidParameters .= 'DefaultGroup:'.$OptionValue.', ';
                                        }
                                        break;
                                    case 'ServerPublicKey':
                                        //Must be a valid path and exist
                                        $TestOption = is_readable($Value);
                                        if ($TestOption === FALSE)
                                        {
                                            $AllGood = $TestOption;
                                            $InvalidParameters .= 'ServerPublicKey:'.$Value.', ';
                                        }
                                        break;
                                    case 'CompressionProtocol':
                                        //Admits TRUE, defaults on FALSE
                                        $TestOption = is_bool($OptionValue);
                                        if ($TestOption === FALSE)
                                        {
                                            $AllGood = $TestOption;
                                            $InvalidParameters .= 'CompressionProtocol:'.var_dump($OptionValue).', ';
                                        }
                                        break;
                                    case 'FoundRows':
                                        //Admits TRUE, defaults on FALSE
                                        $TestOption = is_bool($OptionValue);
                                        if ($TestOption === FALSE)
                                        {
                                            $AllGood = $TestOption;
                                            $InvalidParameters .= 'VerifySSL:'.var_dump($OptionValue).', ';
                                        }
                                        break;
                                    case 'IgnoreSpaces':
                                        //Admits TRUE, defaults on FALSE
                                        $TestOption = is_bool($OptionValue);
                                        if ($TestOption === FALSE)
                                        {
                                            $AllGood = $TestOption;
                                            $InvalidParameters .= 'IgnoreSpaces:'.var_dump($OptionValue).', ';
                                        }
                                        break;
                                    case 'InteractiveClient':
                                        //Admits TRUE, defaults on FALSE
                                        $TestOption = is_bool($OptionValue);
                                        if ($TestOption === FALSE)
                                        {
                                            $AllGood = $TestOption;
                                            $InvalidParameters .= 'InteractiveClient:'.var_dump($OptionValue).', ';
                                        }
                                        break;
                                    case 'UseSSL':
                                        //Admits TRUE, defaults on FALSE
                                        $TestOption = is_bool($OptionValue);
                                        if ($TestOption === FALSE)
                                        {
                                            $AllGood = $TestOption;
                                            $InvalidParameters .= 'UseSSL:'.var_dump($OptionValue).', ';
                                        }
                                        break;
                                    case 'DoNotVerifyServerCert':
                                        //Admits TRUE, defaults on FALSE
                                        $TestOption = is_bool($OptionValue);
                                        if ($TestOption === FALSE)
                                        {
                                            $AllGood = $TestOption;
                                            $InvalidParameters .= 'DoNotVerifyServerCert:'.var_dump($OptionValue).', ';
                                        }
                                        break;
                                    case 'Port':
                                        //string value
                                        $TestOption = IsAdequateDatabasePort($InData['System'], $OptionValue);
                                        if ($TestOption['code'] === 'WRONG')
                                        {
                                            $AllGood = FALSE;
                                            $InvalidParameters .= 'Wrong port: '.$OptionValue.', ';
                                        }
                                        break;
                                    case 'Socket':
                                        //Not good if not localhost or not accessible
                                        if ($InData['Options']['Persistent'] === TRUE)
                                        {
                                            $ProperServer = 'p:localhost';
                                        }
                                        else
                                        {
                                            $ProperServer = 'localhost';
                                        }
                                        if ($InData['ServerName'] !== $ProperServer)
                                        {
                                            $AllGood = FALSE;
                                            $InvalidParameters .= 'Socket: '.$OptionValue.' used without specifying "localhost" as server name ('.$InData['ServerName'].')';
                                        }
                                        elseif (is_writable($OptionValue) === FALSE)
                                        {
                                            $AllGood = FALSE;
                                            $InvalidParameters .= 'Socket: '.$OptionValue.' does not exist or is not writeable.';
                                        }
                                        elseif ($InData['Options']['UseSSL'] === TRUE)
                                        {
                                            //If there is set an SSL connection, it will fail over a Socket
                                            $AllGood = FALSE;
                                            $InvalidParameters .= 'SSL not available over a Socket connection. Disable UseSSL.';
                                        }
                                        break;
                                    default:
                                        $OutData['Success'] = FALSE;
                                        $OutData['ReturnValue'] = 'Unknown mysql connection option: '.$OptionKey;

                                        return FALSE;
                                }
                            }
                            break;
                        default:
                            $OutData['Success'] = FALSE;
                            $OutData['ReturnValue'] = 'Unknown mysql connection parameter: '.$Key;

                            return FALSE;

                    }
                }
            }
            if ($AllGood === FALSE)
            {
                $OutData['Success'] = TRUE;
                $OutData['ReturnValue'] = 'Invalid parameters->'.$InvalidParameters;

                return FALSE;
            }

            //Now test the connections with the parameters
            $ConnectionLink = MySQLInit($OutData);
            if (!$ConnectionLink)
            {
                $OutData['Success'] = TRUE;
                $OutData['ReturnValue'] = 'MySQLInit test failed';

                return FALSE;
            }

            //Establish options registered
            if (MySQLOptions($InData, $OutData, $ConnectionLink) === FALSE)
            {
                $OutData['Success'] = TRUE;
                $OutData['ReturnValue'] = 'MySQLOptions test failed';

                return FALSE;
            }

            if (MySQLRealConnect($InData, $OutData, $ConnectionLink) === FALSE)
            {
                $OutData['Success'] = TRUE;
                $OutData['ReturnValue'] = 'MySQLRealConnect test failed with code '.mysqli_errno($ConnectionLink).': '.mysqli_error($ConnectionLink);

                return FALSE;
            }
            //Close connection
            if (mysqli_close($ConnectionLink) === FALSE)
            {
                $OutData['Success'] = TRUE;
                $OutData['ReturnValue'] = 'Error closing connection with code '.mysqli_errno($ConnectionLink).': '.mysqli_error($ConnectionLink);

                return FALSE;
            }

            $OutData['Success'] = TRUE;
            $OutData['ReturnValue'] = TRUE;


            return TRUE;
        case "SQLITE":
            $OutData['Success'] = TRUE;
            $OutData['ReturnValue'] = 'Unimplemented system. Open an issue in https://github.com/ProceduralMan/MinionLib if you need it';

            return FALSE;
        case "SQLSRV":
            $OutData['Success'] = TRUE;
            $OutData['ReturnValue'] = 'Unimplemented system. Open an issue in https://github.com/ProceduralMan/MinionLib if you need it';

            return FALSE;
        case "CUBRID":
        case "DBASE":
        case "FIREBIRD":
        case "INTERBASE":
        case "DB2":
        case "ORACLE":
        case "DB":
        case "DEVLOGGING":
            $OutData['Success'] = TRUE;
            $OutData['ReturnValue'] = 'Unimplemented system. Open an issue in https://github.com/ProceduralMan/MinionLib if you need it';

            return FALSE;
        default:
            $OutData['Success'] = TRUE;
            $OutData['ReturnValue'] = 'Odd system: '.$InData['System'];

            return FALSE;
            break;
    }
}

/**
 * RegisterDBSystem registers a DB Connection.
 * It indexes the connection under a database@host key
 * @param mixed $InData
 *      'System'         The chosen DB System: CUBRID, DBASE, FIREBIRD, DB2(Including CLOUDSCAPE and DERBY), MYSQL, ORACLE, POSTGRE, SQLITE or SQLSRV
 *      'ServerName'     The chosen server. 'localhost' or nothing for SQLite
 *      'Database'       The Database. Filename path or :memory: if using in-memory db for SQLite
 *      'DBUser'         The User
 *      'DBPassword'     The Password
 *      'Options'        System-specific options
 *                          MySQL
 *                              'ConnectionTimeout'         Flag MYSQLI_OPT_CONNECT_TIMEOUT, controls connection timeout in seconds
 *                              'CommandTimeout'            Flag MYSQLI_OPT_READ_TIMEOUT, controls Command execution result timeout in seconds.
 *                                                              Available as of PHP 7.2.0.
 *                              'UseLocalInfile'            Flag MYSQLI_OPT_LOCAL_INFILE, controls Enable/disable use of LOAD LOCAL INFILE to allow
 *                                                              load of big files into a table
 *                              'InitCommand'               Flag MYSQLI_INIT_COMMAND, command to execute after when connecting to MySQL server
 *                              'Charset'                   Flag MYSQLI_SET_CHARSET_NAME, the charset to be set as default (duplicado de $CharacterSet)
 *                              'OptionsFile'               Flag MYSQLI_READ_DEFAULT_FILE, read options from named option file instead of my.cnf
 *                              'DefaultGroup'              Flag MYSQLI_READ_DEFAULT_GROUP, read options from the named group from my.cnf or the
 *                                                              file specified with MYSQL_READ_DEFAULT_FILE
 *                              'ServerPublicKey'           Flag MYSQLI_SERVER_PUBLIC_KEY, RSA public key file used with the SHA-256 based
 *                                                              authentication
 *                              'CompressionProtocol'       Sets MYSQLI_CLIENT_COMPRESS on mysqli_real_connect to use compression protocol on the
 *                                                              connection
 *                              'FoundRows'                 Sets MYSQLI_CLIENT_FOUND_ROWS on mysqli_real_connect to return number of matched rows,
 *                                                              not the number of affected rows
 *                              'IgnoreSpaces'              Sets MYSQLI_CLIENT_IGNORE_SPACE on mysqli_real_connect to allow spaces after function
 *                                                              names. Makes all function names reserved words.
 *                              'InteractiveClient'         Sets MYSQLI_CLIENT_INTERACTIVE on mysqli_real_connect to allow interactive_timeout
 *                                                              seconds (instead of wait_timeout seconds) of inactivity before closing the
 *                                                              connection
 *                              'UseSSL'                    Sets MYSQLI_CLIENT_SSL on mysqli_real_connect to use SSL (encryption)
 *                              'DoNotVerifyServerCert'     Sets MYSQLI_CLIENT_SSL_DONT_VERIFY_SERVER_CERT on mysqli_real_connect to use SSL while
 *                                                              disabling validation of the provided SSL certificate.
 *                              'Port'                      Sets PORT on network connections
 *                              'Socket'                    Sets UNIX socket on local connections (localhost)
 *                          SQLSRV
 *                              'WarningsReturnAsErrors'    By default, the SQLSRV driver treats warnings as errors; To disable this behavior,
 *                                                              set a 0 here. To enable it, set a 1
 *                              'LogSubsystems'             What is logged to PHP System Log
 *                                  SQLSRV_LOG_SYSTEM_OFF (0)   Turns logging off.
 *                                  SQLSRV_LOG_SYSTEM_INIT (1)  Turns on logging of initialization activity
 *                                  SQLSRV_LOG_SYSTEM_CONN (2)  Turns on logging of connection activity.
 *                                  SQLSRV_LOG_SYSTEM_STMT (4)  Turns on logging of statement activity.
 *                                  SQLSRV_LOG_SYSTEM_UTIL (8)  Turns on logging of error functions activity (such as handle_error and handle_warning).
 *                                  SQLSRV_LOG_SYSTEM_ALL (-1)  Turns on logging of all subsystems.
 *                              'LogSeverity'               Which errorlevel to log
 *                                  SQLSRV_LOG_SEVERITY_ERROR (1)   Specifies that errors are logged. This is the default.
 *                                  SQLSRV_LOG_SEVERITY_WARNING (2) Specifies that warnings are logged.
 *                                  SQLSRV_LOG_SEVERITY_NOTICE (4)  Specifies that notices are logged.
 *                                  SQLSRV_LOG_SEVERITY_ALL (-1)    Specifies that errors, warnings, and notices are logged.
 *                              'AccessToken'                The Azure AD Access Token extracted from an OAuth JSON response. The connection
 *                                                              string must not contain user ID, password, or the Authentication keyword
 *                                                              (requires ODBC Driver version 17 or above in Linux or macOS). Mutually exclusive
 *                                                              with 'Authentication'
 *                              'Authentication'             Authentication mode determined by other keywords
 *                                  SqlPassword                     Directly authenticate to a SQL Server instance (which may be an Azure instance)
 *                                                                      using a username and password. The username and password must be passed into
 *                                                                      the connection string using the UID and PWD keywords.
 *                                  ActiveDirectoryPassword         Authenticate with an Azure Active Directory identity using a username and
 *                                                                      password. The username and password must be passed into the connection
 *                                                                      string using the UID and PWD keywords.
 *                                  ActiveDirectoryMsi              Authenticate using either a system-assigned managed identity or a user-assigned
 *                                                                      managed identity (requires ODBC Driver version 17.3.1.1 or above)
 *                                  ActiveDirectoryServicePrincipal Authenticate using service principal objects (requires ODBC Driver version 17.7 or above)
 *                              'UID'                        Specifies the User ID to be used when connecting with SQL Server Authentication (duplicado de $DBUser)
 *                              'PWD'                        Specifies the password associated with the User ID to be used when connecting with SQL
 *                                                              Server Authentication.
 *                              'ApplicationIntent'          Declares the application workload type when connecting to a server. Possible values are
 *                                                              ReadOnly and ReadWrite. Default value ReadWrite
 *                              'AttachDBFileName'           Specifies which database file the server should attach.
 *                              'CharacterSet'               Specifies the character set used to send data to the server. Possible values are
 *                                                              SQLSRV_ENC_CHAR and UTF-8. Default value SQLSRV_ENC_CHAR
 *                              'ColumnEncryption'           Specifies whether the Always Encrypted feature is enabled or not. Possible values are
 *                                                              Enabled and Disabled. Default value Disabled
 *                              'ConnectionPooling'          Specifies whether the connection is assigned from a connection pool (1 or true) or not
 *                                                              (0 or false). Default value true (1). Has no effect on Linux and MacOs where should
 *                                                              be set via odbcinst.ini
 *                              'ConnectRetryCount'          The maximum number of attempts to reestablish a broken connection before giving up. By
 *                                                              default, a single attempt is made to reestablish a connection when broken. A value
 *                                                              of 0 means that no reconnection will be attempted. Values: 0-255. Default value 1
 *                              'ConnectRetryInterval'       The time, in seconds, between attempts to reestablish a connection. The application will
 *                                                              attempt to reconnect immediately upon detecting a broken connection, and will then
 *                                                              wait ConnectRetryInterval seconds before trying again. This keyword is ignored if
 *                                                              ConnectRetryCount is equal to 0. Values 0-60. Default value 10
 *                              'Database'                   Specifies the name of the database in use for the connection being established. (duplicado de $Database)
 *                              'FormatDecimals'             Specifies whether to add leading zeroes to decimal strings when appropriate and enables
 *                                                              the DecimalPlaces option for formatting money types (1 or true). If left false (0),
 *                                                              the default behavior of returning exact precision and omitting leading zeroes for
 *                                                              values less than 1 is used. Default value false (0)
 *                              'DecimalPlaces'              Specifies the decimal places when formatting fetched money values. Integer between 0 and
 *                                                              4 (inclusive). This option works only when FormatDecimals is true. Any negative
 *                                                              integer or value more than 4 will be ignored.
 *                              'Driver'                     Specifies the Microsoft ODBC driver used to communicate with SQL Server. Windows only
 *                                  ODBC Driver 11 for SQL Server
 *                                  ODBC Driver 13 for SQL Server
 *                                  ODBC Driver 17 for SQL Server
 *                              'Encrypt'                    Specifies whether the communication with SQL Server is encrypted (1 or true) or
 *                                                              unencrypted (0 or false). Default value false (0)
 *                              'Failover_Partner'           Windows only. Specifies the server and instance of the database's mirror (if enabled and
 *                                                              configured) to use when the primary server is unavailable.
 *                              'KeyStoreAuthentication'     Authentication method for accessing Azure Key Vault. Controls what kind of credentials
 *                                                              are used with KeyStorePrincipalId and KeyStoreSecret.
 *                                  KeyVaultPassword
 *                                  KeyVaultClientSecret
 *                               'KeyStorePrincipalId'       Identifier for the account seeking to access Azure Key Vault. If KeyStoreAuthentication
 *                                                              is KeyVaultPassword, this value must be an Azure Active Directory username. If
 *                                                              KeyStoreAuthentication is KeyVaultClientSecret, this value must be an application
 *                                                              client ID
 *                               'KeyStoreSecret'            Credential secret for the account seeking to access Azure Key Vault. If
 *                                                              KeyStoreAuthentication is KeyVaultPassword, this value must be an Azure Active
 *                                                              Directory password. If KeyStoreAuthentication is KeyVaultClientSecret, this value
 *                                                              must be an application client secret.
 *                               'Language'                  Specifies the language of messages returned by the server. The available languages are
 *                                                              listed in the sys.syslanguages table. Does not affect the language used by the
 *                                                              drivers themselves, as they are currently available only in English, and it does not
 *                                                              affect the language of the underlying ODBC driver, whose language is determined by
 *                                                              the localized version installed on the client system. The default is the language
 *                                                              set in SQL Server.
 *                               'LoginTimeout'              Specifies the number of seconds to wait before failing the connection attempt. Default
 *                                                              value no timeout.
 *                               'MultipleActiveResultSets'  Disables -false (0)- or explicitly enables -true (1)- support for multiple active result
 *                                                              sets (MARS). Default value true (1).
 *                               'MultiSubnetFailover'       Support for AlwaysOn Availability Groups. Possible values are yes and no. Default value
 *                                                              no. Always specify multiSubnetFailover=yes when connecting to the availability group
 *                                                              listener of a SQL Server 2012 (11.x) availability group or a SQL Server 2012 (11.x)
 *                                                              Failover Cluster Instance.
 *                               'TransparentNetworkIPResolution' Affects the connection sequence when the first resolved IP of the hostname does not
 *                                                                  respond and there are multiple IPs associated with the hostname. Possible values
 *                                                                  enabled and disabled. It interacts with MultiSubnetFailover to provide different
 *                                                                  connection sequences.
 *                               'QuotedId'                  Specifies whether to use SQL-92 rules for quoted identifiers (1 or true) or to use
 *                                                              legacy Transact-SQL rules (0 or false). Default value true (1)
 *                               'ReturnDatesAsStrings'      Retrieves date and time types (datetime, smalldatetime, date, time, datetime2, and
 *                                                              datetimeoffset) as strings or as PHP types. 1 or true to return date and time types
 *                                                              as strings. 0 or false to return date and time types as PHP DateTime types. Default
 *                                                              value false (0).
 *                               'Scrollable'                Client-side (buffered) and server-side (unbuffered) cursors. Default value 'forward'
 *                                  SQLSRV_CURSOR_FORWARD           Lets you move one row at a time starting at the first row of the result set
 *                                                                      until you reach the end of the result set. This is the default cursor type.
 *                                                                      type. sqlsrv_num_rows returns an error for result sets created with this
 *                                                                      cursor. 'forward' is the abbreviated form of SQLSRV_CURSOR_FORWARD
 *                                  SQLSRV_CURSOR_STATIC            Lets you access rows in any order but will not reflect changes in the database.
 *                                                                      'static' is the abbreviated form of SQLSRV_CURSOR_STATIC
 *                                  SQLSRV_CURSOR_DYNAMIC           Lets you access rows in any order and will reflect changes in the database.
 *                                                                      sqlsrv_num_rows returns an error for result sets created with this cursor
 *                                                                      type. 'dynamic' is the abbreviated form of SQLSRV_CURSOR_DYNAMIC.
 *                                  SQLSRV_CURSOR_KEYSET            Lets you access rows in any order. However, a keyset cursor does not update the
 *                                                                      row count if a row is deleted from the table (a deleted row is returned with
 *                                                                      no values). keyset is the abbreviated form of SQLSRV_CURSOR_KEYSET.
 *                                  SQLSRV_CURSOR_CLIENT_BUFFERED   Lets you access rows in any order. Creates a client-side cursor query.
 *                                                                      'buffered' is the abbreviated form of SQLSRV_CURSOR_CLIENT_BUFFERED.
 *                               'TraceOn'                   Specifies whether ODBC tracing is enabled (1 or true) or disabled (0 or false) for the
 *                                                              connection being established. Default value false (0)
 *                               'TraceFile'                 Specifies the path for the file used for trace data.
 *                               'APP'                       Specifies the application name used in tracing.
 *                               'WSID'                      Specifies the name of the computer for tracing.
 *                               'TransactionIsolation'      Specifies the transaction isolation level. DSefault value SQLSRV_TXN_READ_COMMITTED
 *                                  SQLSRV_TXN_READ_UNCOMMITTED     Specifies that statements can read rows that have been modified by other
 *                                                                      transactions but not yet committed. Transactions running at the READ
 *                                                                      UNCOMMITTED level do not issue shared locks to prevent other transactions
 *                                                                      from modifying data read by the current transaction. It is possible to make
 *                                                                      dirty reads.
 *                                  SQLSRV_TXN_READ_COMMITTED       Specifies that statements cannot read data that has been modified but not
 *                                                                      committed by other transactions. This prevents dirty reads. Data can be
 *                                                                      changed by other transactions between individual statements within the
 *                                                                      current transaction, resulting in nonrepeatable reads or phantom data.
 *                                                                      This option is the SQL Server default.
 *                                  SQLSRV_TXN_REPEATABLE_READ      Specifies that statements cannot read data that has been modified but not yet
 *                                                                      committed by other transactions and that no other transactions can modify
 *                                                                      data that has been read by the current transaction until the current
 *                                                                      transaction completes. Shared locks are placed on all data read by each
 *                                                                      statement in the transaction and are held until the transaction completes.
 *                                                                      Use this option only when necessary.
 *                                  SQLSRV_TXN_SNAPSHOT             Specifies that data read by any statement in a transaction will be the
 *                                                                      transactionally consistent version of the data that existed at the start of
 *                                                                      the transaction. The transaction can only recognize data modifications that
 *                                                                      were committed before the start of the transaction. Data modifications made
 *                                                                      by other transactions after the start of the current transaction are not
 *                                                                      visible to statements executing in the current transaction. The effect is as
 *                                                                      if the statements in a transaction get a snapshot of the committed data as
 *                                                                      it existed at the start of the transaction.
 *                                  SQLSRV_TXN_SERIALIZABLE         1)Statements cannot read data that has been modified but not yet committed by
 *                                                                      other transactions. 2) No other transactions can modify data that has been
 *                                                                      read by the current transaction until the current transaction completes. 3)
 *                                                                      Other transactions cannot insert new rows with key values that would fall in
 *                                                                      the range of keys read by any statements in the current transaction until
 *                                                                      the current transaction completes. Range locks are placed in the range of
 *                                                                      key values that match the search conditions of each statement executed in a
 *                                                                      transaction. Because concurrency is lower, use this option only when
 *                                                                      necessary.
 *                               'TrustServerCertificate'    Specifies whether the client should trust (1 or true) or reject (0 or false) a
 *                                                              self-signed server certificate. Default value false (0).
 *                          SQLITE3
 *                              'flags'                      How to open SQLite. Default value SQLITE3_OPEN_READWRITE|SQLITE3_OPEN_CREATE
 *                                  SQLITE3_OPEN_READONLY       Open in read-only mode
 *                                  SQLITE3_OPEN_READWRITE      Open in read-write mode
 *                                  SQLITE3_OPEN_CREATE         Create database if it does not exist
 * @param mixed $OutData
 * @return int     The connection index
 * @since 0.0.6
 * @see
 * @todo new parameter might arise from yet unimplemented systems
 */
function RegisterDBSystem($InData, $OutData)
{
    if (DEBUGMODE)
    {
        echo date("Y-m-d H:i:s").' -> RegisterDBSystem '.PHP_EOL;
    }

    //Register as GLOBAL
    $ConnectionName = $InData['Database'].'@'.$InData['ServerName'].uniqid();
    $GLOBALS['DB'][$ConnectionName]['System'] = $InData['System'];
    $GLOBALS['DB'][$ConnectionName]['ServerName'] = $InData['ServerName'];
    $GLOBALS['DB'][$ConnectionName]['Database'] = $InData['Database'];
    $GLOBALS['DB'][$ConnectionName]['DBUser'] = $InData['DBUser'];
    $GLOBALS['DB'][$ConnectionName]['DBPassword'] = $InData['DBPassword'];
    if (isset($InData['Options']))
    {
        $GLOBALS['DB'][$ConnectionName]['Options'] = $InData['Options'];
    }

    //IF APCU is available we cache register on it
    if (MIL_ACPU === TRUE)
    {
        $Resultado = apcu_store('DB', $GLOBALS['DB']);
        if ($Resultado === FALSE)
        {
            $Message = 'Error registering connection over APCU';
            AddError($Message);
            $OutData['Success'] = FALSE;
            $OutData['ReturnValue'] = FALSE;

            return FALSE;
        }
    }
    $OutData['Success'] = TRUE;
    $OutData['ReturnValue'] = $ConnectionName;

    return $ConnectionName;
}

/**
 * OldRegisterDBSystem registers a DB Connection.
 * It indexes the connection under the database name
 * @param mixed $InData
 * @param mixed $OutData
 * @return int     The connection index
 * @since 0.0.3
 * @see
 * @todo new parameter might arise from yet unimplemented systems
 * @deprecated  will be substituted by an atomic variant
 */
function OldRegisterDBSystem($InData, $OutData)
{
    if (DEBUGMODE)
    {
        echo date("Y-m-d H:i:s").' -> RegisterDBSystem '.PHP_EOL;
    }

    //If we support APCU, update APCU KEY
    //If there is a pool defined, we persist the pool info using APCU (REDIS to come)
    if (is_int(MIL_POOLEDCONNECTIONS))
    {
        if (MIL_ACPU === TRUE)
        {
            $PoolExists = TestExistingPool($InData, $OutData);
            if ($PoolExists === FALSE)
            {
                //No previous connection or no similar connection
                //Register as GLOBAL
                $ConnectionName = uniqid();
                $GLOBALS['DB'][$ConnectionName]['System'] = $InData['System'];
                $GLOBALS['DB'][$ConnectionName]['ServerName'] = $InData['ServerName'];
                $GLOBALS['DB'][$ConnectionName]['Database'] = $InData['Database'];
                $GLOBALS['DB'][$ConnectionName]['DBUser'] = $InData['DBUser'];
                $GLOBALS['DB'][$ConnectionName]['DBPassword'] = $InData['DBPassword'];
                if (isset($InData['ConnectionLink']))
                {
                    $GLOBALS['DB'][$ConnectionName]['ConnectionLink'] = $InData['ConnectionLink'];
                    //Test serialization/APCU issue
                    $GLOBALS['ConnLink'][0] = $InData['ConnectionLink'];
                    apcu_store('ConnLink', $InData['ConnectionLink']);
                }
                if (isset($InData['Options']))
                {
                    $GLOBALS['DB'][$ConnectionName]['Options'] = $InData['Options'];
                }
                for ($i = 0; $i<MIL_POOLEDCONNECTIONS; $i++)
                {
                    $ThePool[$ConnectionName][$i] = $GLOBALS['DB'][$ConnectionName];
                }
                $Resultado = apcu_store('DB', $ThePool);
                if ($Resultado === FALSE)
                {
                    $Message = 'Error registering connection over APCU';
                    AddError($Message);
                }
                $OutData['Success'] = TRUE;
                $OutData['ReturnValue'] = $ConnectionName;

                return $ConnectionName;
            }   //End No previous connection or no similar connection
            else
            {
                $OutData['Success'] = TRUE;
                $OutData['ReturnValue'] = $PoolExists;

                return $PoolExists;
            }
        }
    }
    else
    {
        //No pool defined
        //Register as GLOBAL
        $ConnectionName = uniqid();
        $GLOBALS['DB'][$ConnectionName]['System'] = $InData['System'];
        $GLOBALS['DB'][$ConnectionName]['ServerName'] = $InData['ServerName'];
        $GLOBALS['DB'][$ConnectionName]['Database'] = $InData['Database'];
        $GLOBALS['DB'][$ConnectionName]['DBUser'] = $InData['DBUser'];
        $GLOBALS['DB'][$ConnectionName]['DBPassword'] = $InData['DBPassword'];
        if (isset($InData['ConnectionLink']))
        {
            $GLOBALS['DB'][$ConnectionName]['ConnectionLink'] = $InData['ConnectionLink'];
            //Test serialization/APCU issue
            $GLOBALS['ConnLink'][0] = $InData['ConnectionLink'];
            apcu_store('ConnLink', $InData['ConnectionLink']);
        }
        if (isset($InData['Options']))
        {
            $GLOBALS['DB'][$ConnectionName]['Options'] = $InData['Options'];
        }
        if (MIL_ACPU === TRUE)
        {
            $Resultado = apcu_store('DB', $GLOBALS['DB']);
            if ($Resultado === FALSE)
            {
                $Message = 'Error registering connection over APCU';
                AddError($Message);
            }
        }
        $OutData['Success'] = TRUE;
        $OutData['ReturnValue'] = $PoolExists;

        return $ConnectionName;
    }
}

/**
 * TestExistingPool
 * Avoids duplicating pools on concurrent connections
 * @param mixed $InData
 * @param mixed $OutData
 * @return mixed    FALSE if pool does not exist previously... pool indez otherwise
 * @since 0.0.4
 * @see
 * @todo
 * @deprecated  will be substituted by an atomic variant
 */
function TestExistingPool($InData, $OutData)
{
    if (DEBUGMODE)
    {
        echo date("Y-m-d H:i:s").' -> TestExistingPool '.PHP_EOL;
    }

    //Fetch potential previous pools to other databases
    $ThePool = apcu_fetch('DB');
    if ($ThePool === FALSE)
    {
        //No previous pools
        //$ThePool = array();
        return FALSE;
    }
    else
    {
        //Check if we are trying to pool an existing connection
        // A connection will be considered the same when it shares System, ServerName, Database and User
        foreach ($ThePool as $Clave => $Valor)
        {
            if (($Valor[0]['System'] === $InData['System'])&&($Valor[0]['ServerName'] === $InData['ServerName'])
                    &&($Valor[0]['Database'] === $InData['Database'])&&($Valor[0]['DBUser'] === $InData['DBUser']))
            {
                //It is the same. We return the pools index
                $OutData['Success'] = TRUE;
                $OutData['ReturnValue'] = $Clave;

                return $Clave;
            }
        }
    }

    //No similiar connection found
    $OutData['Success'] = TRUE;
    $OutData['ReturnValue'] = FALSE;

    return FALSE;
}

/**
 * MySQLInit performs connection initializacion
 * @param   array $OutData
 * @return  mixed Connection link or FALSE if it fails
 * @since   0.0.3
 * @see     https://www.php.net/manual/es/mysqli.init.php
 * @todo
 */
function MySQLInit(&$OutData)
{
    if (DEBUGMODE)
    {
        echo date("Y-m-d H:i:s").' -> MySQLInit '.PHP_EOL;
    }

    $ConnectionLink = mysqli_init();
    if (!$ConnectionLink)
    {
        $OutData['Success'] = FALSE;
        $OutData['ReturnValue'] = 'mysql_init failure';

        return FALSE;
    }

    return $ConnectionLink;
}

/**
 * MySQLOptions sets connection options
 * @param   array    $InData     registered connection settings. see DBSystemSanityChecks for details
 * @param   array    $OutData    function feedback
 * @param   mixed    $ConnectionLink
 * @return  boolean  TRUE on success, FALSE on error
 * @since   0.0.3
 * @see     https://www.php.net/manual/es/mysqli.options.php
 * @todo
 */
function MySQLOptions($InData, &$OutData, $ConnectionLink)
{
    if (DEBUGMODE)
    {
        echo date("Y-m-d H:i:s").' -> MySQLOptions '.PHP_EOL;
    }

    if (isset($InData['Options']['ConnectionTimeout']))
    {
        $OptionResult = mysqli_options($ConnectionLink, MYSQLI_OPT_CONNECT_TIMEOUT, $InData['Options']['ConnectionTimeout']);
        if ($OptionResult === FALSE)
        {
            $OutData['Success'] = FALSE;
            $OutData['ReturnValue'] = 'mysql_OPTION failure. Invalid MYSQLI_OPT_CONNECT_TIMEOUT = '.$InData['Options']['ConnectionTimeout'];

            return FALSE;
        }
    }
    if (isset($InData['Options']['CommandTimeout']))
    {
        $OptionResult = mysqli_options($ConnectionLink, MYSQLI_OPT_READ_TIMEOUT, $InData['Options']['CommandTimeout']);
        if ($OptionResult === FALSE)
        {
            $OutData['Success'] = FALSE;
            $OutData['ReturnValue'] = 'mysql_OPTION failure. Invalid MYSQLI_OPT_READ_TIMEOUT = '.$InData['Options']['CommandTimeout'];

            return FALSE;
        }
    }
    if (isset($InData['Options']['UseLocalInfile']))
    {
        $OptionResult = mysqli_options($ConnectionLink, MYSQLI_OPT_LOCAL_INFILE, $InData['Options']['UseLocalInfile']);
        if ($OptionResult === FALSE)
        {
            $OutData['Success'] = FALSE;
            $OutData['ReturnValue'] = 'mysql_OPTION failure. Invalid MYSQLI_OPT_LOCAL_INFILE = '.$InData['Options']['UseLocalInfile'];

            return FALSE;
        }
    }
    if (isset($InData['Options']['InitCommand']))
    {
        $OptionResult = mysqli_options($ConnectionLink, MYSQLI_INIT_COMMAND, $InData['Options']['InitCommand']);
        if ($OptionResult === FALSE)
        {
            $OutData['Success'] = FALSE;
            $OutData['ReturnValue'] = 'mysql_OPTION failure. Invalid MYSQLI_INIT_COMMAND = '.$InData['Options']['InitCommand'];

            return FALSE;
        }
    }
    if (isset($InData['Options']['Charset']))
    {
        $OptionResult = mysqli_options($ConnectionLink, MYSQLI_SET_CHARSET_NAME, $InData['Options']['Charset']);
        if ($OptionResult === FALSE)
        {
            $OutData['Success'] = FALSE;
            $OutData['ReturnValue'] = 'mysql_OPTION failure. Invalid MYSQLI_SET_CHARSET_NAME = '.$InData['Options']['Charset'];

            return FALSE;
        }
    }
    if (isset($InData['Options']['OptionsFile']))
    {
        $OptionResult = mysqli_options($ConnectionLink, MYSQLI_READ_DEFAULT_FILE, $InData['Options']['OptionsFile']);
        if ($OptionResult === FALSE)
        {
            $OutData['Success'] = FALSE;
            $OutData['ReturnValue'] = 'mysql_OPTION failure. Invalid MYSQLI_READ_DEFAULT_FILE = '.$InData['Options']['OptionsFile'];

            return FALSE;
        }
    }
    if (isset($InData['Options']['DefaultGroup']))
    {
        $OptionResult = mysqli_options($ConnectionLink, MYSQLI_READ_DEFAULT_GROUP, $InData['Options']['DefaultGroup']);
        if ($OptionResult === FALSE)
        {
            $OutData['Success'] = FALSE;
            $OutData['ReturnValue'] = 'mysql_OPTION failure. Invalid MYSQLI_READ_DEFAULT_GROUP = '.$InData['Options']['DefaultGroup'];

            return FALSE;
        }
    }
    if (isset($InData['Options']['ServerPublicKey']))
    {
        $OptionResult = mysqli_options($ConnectionLink, MYSQLI_SERVER_PUBLIC_KEY, $InData['Options']['ServerPublicKey']);
        if ($OptionResult === FALSE)
        {
            $OutData['Success'] = FALSE;
            $OutData['ReturnValue'] = 'mysql_OPTION failure. Invalid MYSQLI_SERVER_PUBLIC_KEY = '.$InData['Options']['ServerPublicKey'];

            return FALSE;
        }
    }
    if (isset($InData['Options']['VerifySSL']))
    {
        var_dump($InData['Options']['VerifySSL']);
        //$OptionResult = mysqli_options($ConnectionLink,MYSQLI_OPT_SSL_VERIFY_SERVER_CERT, $InData['Options']['VerifySSL']);
        $OptionResult = mysqli_options($ConnectionLink, MYSQLI_OPT_SSL_VERIFY_SERVER_CERT, TRUE);
        if ($OptionResult === FALSE)
        {
            $OutData['Success'] = FALSE;
            $OutData['ReturnValue'] = 'mysql_OPTION failure. Invalid MYSQLI_OPT_SSL_VERIFY_SERVER_CERT = '.$InData['Options']['VerifySSL'];
            echo 'Yo Fallo';

            return FALSE;
        }
    }

    return TRUE;
}

/**
 * MySQLRealConnect performs connection to Database
 * If host is 'localhost' or NULL it will use a socket to connect; using TCP/IP (port) only if its is a hostname or IP
 * Nevertheless we will use the socket path if provided
 * @param   array    $InData     registered connection settings. see DBSystemSanityChecks for details
 * @param   array    $OutData    function feedback
 * @param mixed $ConnectionLink
 * @return  boolean  TRUE on success, FALSE on error
 * @since   0.0.3
 * @see     https://www.php.net/manual/es/mysqli.real-connect.php
 * @todo
 */
function MySQLRealConnect($InData, &$OutData, $ConnectionLink)
{
    if (DEBUGMODE)
    {
        echo date("Y-m-d H:i:s").' -> MySQLRealConnect '.PHP_EOL;
    }

    //Parse connection flags
    $ConnectionFlags = NULL;
    if (isset($InData['Options']['CompressionProtocol'])&&($InData['Options']['CompressionProtocol'] === TRUE))
    {
        if (is_null($ConnectionFlags))
        {
            $ConnectionFlags = MYSQLI_CLIENT_COMPRESS;
        }
        else
        {
            $ConnectionFlags = $ConnectionFlags|MYSQLI_CLIENT_COMPRESS;
        }
    }

    if (isset($InData['Options']['FoundRows'])&&($InData['Options']['FoundRows'] === TRUE))
    {
        if (is_null($ConnectionFlags))
        {
            $ConnectionFlags = MYSQLI_CLIENT_FOUND_ROWS;
        }
        else
        {
            $ConnectionFlags = $ConnectionFlags|MYSQLI_CLIENT_FOUND_ROWS;
        }
    }

    if (isset($InData['Options']['IgnoreSpaces'])&&($InData['Options']['IgnoreSpaces'] === TRUE))
    {
        if (is_null($ConnectionFlags))
        {
            $ConnectionFlags = MYSQLI_CLIENT_IGNORE_SPACE;
        }
        else
        {
            $ConnectionFlags = $ConnectionFlags|MYSQLI_CLIENT_IGNORE_SPACE;
        }
    }

    if (isset($InData['Options']['InteractiveClient'])&&($InData['Options']['InteractiveClient'] === TRUE))
    {
        if (is_null($ConnectionFlags))
        {
            $ConnectionFlags = MYSQLI_CLIENT_INTERACTIVE;
        }
        else
        {
            $ConnectionFlags = $ConnectionFlags|MYSQLI_CLIENT_INTERACTIVE;
        }
    }

    if (isset($InData['Options']['UseSSL'])&&($InData['Options']['UseSSL'] === TRUE))
    {
        if (is_null($ConnectionFlags))
        {
            $ConnectionFlags = MYSQLI_CLIENT_SSL;
        }
        else
        {
            $ConnectionFlags = $ConnectionFlags|MYSQLI_CLIENT_SSL;
        }
    }


    if (isset($InData['Options']['DoNotVerifyServerCert'])&&($InData['Options']['DoNotVerifyServerCert'] === TRUE))
    {
        if (is_null($ConnectionFlags))
        {
            $ConnectionFlags = MYSQLI_CLIENT_SSL_DONT_VERIFY_SERVER_CERT;
        }
        else
        {
            $ConnectionFlags = $ConnectionFlags|MYSQLI_CLIENT_SSL_DONT_VERIFY_SERVER_CERT;
        }
    }

    $ThePort = NULL;
    if (isset($InData['Options']['Port']))
    {
        $ThePort = $InData['Options']['Port'];
    }

    $TheSocket = NULL;
    if (isset($InData['Options']['Socket']))
    {
        $TheSocket = $InData['Options']['Socket'];
    }

    //Connect
    $ConnectResult = mysqli_real_connect(
        $ConnectionLink,
        $InData['ServerName'],
        $InData['DBUser'],
        $InData['DBPassword'],
        $InData['Database'],
        $ThePort,
        $TheSocket,
        $ConnectionFlags
    );

    return $ConnectResult;
}

/**
 * FindFunctionBoundary
 * Returns the right boundary of a function with an arbitrary number of function calls aka (...) as parameters
 * @param string    $Text the query text
 * @param int       $Offset the starting offset
 * @return int      offset of the right parentheses
 */
function FindFunctionBoundary($Text, $Offset)
{
    if (DEBUGMODE)
    {
        echo date("Y-m-d H:i:s").' -> FindFunctionBoundary '.PHP_EOL;
    }

    $i = $Offset-1;
    $Limit = strlen($Text);
    $Encontrado = FALSE;
    while (($i<$Limit)&&($Encontrado === FALSE))
    {
        $i++; //we advance over the first '('
        if ($Text[$i] === '(')
        {
            $i = $i+FindFunctionBoundary($Text, $i);
        }
        if ($Text[$i] === ')')
        {
            $Encontrado = TRUE;

            return $i;
        }
    }
}

/**
 * SmellyStatement
 * Checks if statement tries to hide things
 * @param type $Query
 * @return boolean TRUE if stinks, FALSE if it appears legit
 * @see https://cheatsheetseries.owasp.org/cheatsheets/XSS_Filter_Evasion_Cheat_Sheet.html
 *      https://owasp.org/www-community/attacks/SQL_Injection_Bypassing_WAF
 */
function SmellyStatement($Query)
{
    if (DEBUGMODE)
    {
        echo date("Y-m-d H:i:s").' -> SmellyStatement '.PHP_EOL;
    }

    $UpperQuery = strtoupper($Query);
    //Blind SQL Injection
    // Example: select * from table where id = 1 AND if((ascii(lower(substring((select user()),$i,1))))!=$s,1,benchmark(200000,md5(now())))
    //Disallow USER()
    $NumUsr = substr_count($UpperQuery, 'USER()');
    if ($NumUsr>0)
    {
        return TRUE;
    }

    /*
     * SLEEP(5)--
     * SELECT BENCHMARK(1000000,MD5('A'));
     * id=1 OR SLEEP(25)=0 LIMIT 1--
     * id=1) OR SLEEP(25)=0 LIMIT 1--
     * id=1' OR SLEEP(25)=0 LIMIT 1--
     * id=1') OR SLEEP(25)=0 LIMIT 1--
     * id=1)) OR SLEEP(25)=0 LIMIT 1--
     * id=SELECT SLEEP(25)--
     * Disallow SLEEP();
     */
    $NumSlp = substr_count($UpperQuery, 'SLEEP(');
    if ($NumSlp>0)
    {
        return TRUE;
    }

    //Table guessing
    //SELECT * from sakila.actor where actor_id = 1 union select 1,2,3,4
    //Disallow SELECT 1,2
    $NumSel = substr_count($UpperQuery, 'SELECT 1,2');
    if ($NumSel>0)
    {
        return TRUE;
    }

    //select user from mysql.user where user = ‘user’ OR mid(password,1,1)=’*’
    //select user from mysql.user where user = ‘user’ OR mid(password,1,1)=0x2a
    //select user from mysql.user where user = ‘user’ OR mid(password,1,1)=unhex(‘2a’)
    //select user from mysql.user where user = ‘user’ OR mid(password,1,1) regexp ‘[*]’
    //select user from mysql.user where user = ‘user’ OR mid(password,1,1) like ‘*’
    //select user from mysql.user where user = ‘user’ OR mid(password,1,1) rlike ‘[*]’
    //select user from mysql.user where user = ‘user’ OR ord(mid(password,1,1))=42
    //select user from mysql.user where user = ‘user’ OR ascii(mid(password,1,1))=42
    //select user from mysql.user where user = ‘user’ OR find_in_set(‘2a’,hex(mid(password,1,1)))=1
    //select user from mysql.user where user = ‘user’ OR position(0x2a in password)=1
    //select user from mysql.user where user = ‘user’ OR locate(0x2a,password)=1
    //Disallow info from mysql database
    $NumMy = substr_count($UpperQuery, 'MYSQL.');
    if ($NumMy>0)
    {
        return TRUE;
    }

    //URL_encoded text outside concat
    //Are there spaces encoded (as +)
    $PosUESp = strpos($UpperQuery,'+');
    if ($PosUESp !== FALSE)
    {
        //Is there a CONCAT(
        $PosCon = strpos($UpperQuery,'CONCAT');
        if ($PosCon !== FALSE)
        {
            $LeftBoundary = strpos($UpperQuery, '(', $PosCon);
            $RightBoundary = FindFunctionBoundary($UpperQuery, $LeftBoundary);
            if (($PosUESp<$LeftBoundary)||($PosUESp>$RightBoundary))
            {
                return TRUE;
            }
        }
        else
        {
            //No concat... is outside by definition
            return TRUE;
        }
    }

    //Are there spaces encoded (as %20)
    $PosUESpRaw = strpos($UpperQuery,'%20');
    if ($PosUESpRaw !== FALSE)
    {
        //Is there a CONCAT(
        $PosCon = strpos($UpperQuery,'CONCAT');
        if ($PosCon !== FALSE)
        {
            $LeftBoundary = strpos($UpperQuery, '(', $PosCon);
            $RightBoundary = FindFunctionBoundary($UpperQuery, $LeftBoundary);
            if (($PosUESp<$LeftBoundary)||($PosUESp>$RightBoundary))
            {
                return TRUE;
            }
        }
        else
        {
            //No concat... is outside by definition
            return TRUE;
        }
    }

    return FALSE;
}

/**
 * StatementType
 * returns the statement type
 * @param   string $Query   The query
 * @return  string The statement
 */
function StatementType($Query)
{
    if (DEBUGMODE)
    {
        echo date("Y-m-d H:i:s").' -> StatementType '.PHP_EOL;
    }

    $Cachos = explode(' ', $Query);

    return strtoupper($Cachos[0]);
}

/**
 * StripValuesFromQuery takes out values than can contain SQL Operators
 * @param   string $Query   The query
 * @return  string The stripped query
 */
function StripValuesFromQuery($Query)
{
    if (DEBUGMODE)
    {
        echo date("Y-m-d H:i:s").' -> StripValuesFromQuery '.PHP_EOL;
    }

    $Processed = FALSE;
    $StrippedQuery = '';
    $Index = 0;
    $Position = 0;
    $BegginingEnclosures = array();
    $EndingEnclosures = array();
    while (!$Processed)
    {
        $EnclosurePos = strpos($Query,"'",$Position);
        if ($EnclosurePos === FALSE)
        {
            // No enclosure || No more enclosures
            //echo 'No enclosure || No more enclosures'.PHP_EOL;
            $Processed = TRUE;
        }
        else
        {
            //Found one! IS it a beginning or an end?
            //echo 'Found enclosure at position '.$EnclosurePos.PHP_EOL;
            //We position just AFTER the enclosure
            $Position = $EnclosurePos+1;
            //We update the index
            $Index++;
            if (IsEven($Index)&&(DEBUGMODE === TRUE))
            {
                //echo $Index.' is even. Adding to ending enclosure'.PHP_EOL;
                $EndingEnclosures[] = $EnclosurePos;
            }
            elseif (IsOdd($Index)&&(DEBUGMODE === TRUE))
            {
                //echo $Index.' is odd. Adding to beginning enclosure'.PHP_EOL;
                $BegginingEnclosures[] = $EnclosurePos;
            }
        }
    }

    //If we have processed something, we return it
    if ($Position>0)
    {
        //We must have an equal number of even and odd enclosures
        if (DEBUGMODE)
        {
            echo 'Beginning Enclosures:'.PHP_EOL;
            print_r($BegginingEnclosures);
            echo 'Ending Enclosures:'.PHP_EOL;
            print_r($EndingEnclosures);
        }
        //Add text outsdide the enclosures
        $AddingOffset = 0;
        foreach ($BegginingEnclosures as $Key => $Value)
        {
            //Get everything outside the enclosures
            $CharsToKeep = $Value-$AddingOffset; //-1;
            if (DEBUGMODE)
            {
                var_dump($Value);
                var_dump($AddingOffset);
                echo 'Take '.$CharsToKeep.' chars from position '.$AddingOffset.PHP_EOL;
            }
            $StrippedQuery .= substr($Query, $AddingOffset, $CharsToKeep);
            //Advance offset to the ending enclosure
            $AddingOffset = $EndingEnclosures[$Key]+1;
        }
        //Now from the last enclosure to the end
        $StrippedQuery .= substr($Query, $AddingOffset);
        if (DEBUGMODE)
        {
            echo '|'.$StrippedQuery.'|'.PHP_EOL;
        }

        return $StrippedQuery;
    }
    else
    {
        //No enclosures, can be a SELECT
        return $Query;
    }
}

/**
 * JustOneStatement
 * checks number of statements on the query
 * @param   string  $Query          The query to analyze
 * @return  boolean TRUE if its is just one statement, FALSE otherwise
 */
function JustOneStatement($Query)
{
    if (DEBUGMODE)
    {
        echo date("Y-m-d H:i:s").' -> JustOneStatement '.PHP_EOL;
    }

    //We make sure no UPDATE, DELETE, INSERT or ALTER appears when SELECTing... and the same for the others. No double statement, also
    $NumSelect = substr_count($Query, 'SELECT ');
    $NumUpdates = substr_count($Query, 'UPDATE ');
    $NumDeletes = substr_count($Query, 'DELETE ');
    $NumInserts = substr_count($Query, 'INSERT ');
    $NumAlters = substr_count($Query, 'ALTER ');
    $NumMerges = substr_count($Query, 'ON DUPLICATE KEY UPDATE ');

    $AllStatements = $NumSelect+$NumUpdates+$NumDeletes+$NumInserts+$NumAlters+$NumMerges;

    //echo 'Merges:'.$NumMerges.' ALL: '.$AllStatements.' Inserts:'.$NumInserts;
    if ($NumMerges === 0)
    {
        if ($AllStatements>1)
        {
            return FALSE;
        }
    }
    else
    {
        //UPDATE is contained on ON DUPLICATE KEY UPDATE. So we substract the updates
        $AllStatements-=$NumUpdates;
        if (($AllStatements>2)||($NumInserts !== 1))
        {
            return FALSE;
        }
    }
    //echo '=>ON to PCs'.PHP_EOL;
    //We take out the values so as to count relevant tokens
    $StrippedQuery = StripValuesFromQuery($Query);

    //We count the number of ';'
    $NumPC = substr_count($StrippedQuery, ';');

    //0 or 1 is OK
    if ($NumPC>1)
    {
        return FALSE;
    }

    return TRUE;
}

/**
 * TableToArray
 * Reads a table to an array, same structure as an ARRAY MySQL Query
 * The offset of the initial row is 0
 * @param   array   $Data               The array to populate with data
 * @param   string  $TableName          The table to load
 * @param   array   $ConnectionIndex    The connection to use
 * @param   string  $FilterCondition    A WHERE condition to filter the table
 * @param   int     $Offset             The offset to apply. 0 means from the beggining.
 * @param   int     $NumRows            The number of rows to load. 18446744073709551615 means all rows as MYSQL uses unisgned 64 bit integers. But PHP has no
 *                                          intrinsic support of unsigned integers, so we use PHP_INT_MAX = 9223372036854775807. So please use no more than
 *                                          9 (International System) Trillions of records, even if MySQl allows you to double that
 * @return  array   $Data by reference
 * @since   0.0.7
 * @see     https://dev.mysql.com/doc/refman/8.0/en/select.html
 * @todo
 */
function TableToArray(&$Data, $TableName, $ConnectionIndex, $FilterCondition = "", $Offset = 0, $NumRows = PHP_INT_MAX)
{
    if (DEBUGMODE)
    {
        echo date("Y-m-d H:i:s").' -> TableToArray '.PHP_EOL;
    }

    $Query = "SELECT * FROM ".$TableName." ".$FilterCondition." LIMIT ".$Offset.",".$NumRows;
    $MySQLData = Read($ConnectionIndex, $Query, 'ARRAY');

    if (is_array($MySQLData) === TRUE)
    {
        $Data = $MySQLData['Data'];

        return TRUE;
    }
    else
    {
        return FALSE;
    }
}

//Only Assoc arrays allowed



/**
 * AssocToTable writes an associative array into a DB Table
 * We use MySQL ASSOC structure
 * array(3) {
 *   ["Columns"]=>
 *   int(4)
 *   ["Rows"]=>
 *   int(3)
 *   ["Data"]=>
 *   array(3) {
 *     [0]=>
 *     array(4) {
 *       ["actor_id"]=>
 *       string(1) "1"
 *       ["first_name"]=>
 *       string(8) "PENELOPE"
 *       ["last_name"]=>
 *       string(7) "GUINESS"
 *       ["last_update"]=>
 *       string(19) "2006-02-15 04:34:33"
 *     }
 *     [1]=>
 *     array(4) {
 *       ["actor_id"]=>
 *       string(1) "2"
 *       ["first_name"]=>
 *       string(4) "NICK"
 *       ["last_name"]=>
 *       string(8) "WAHLBERG"
 *       ["last_update"]=>
 *       string(19) "2006-02-15 04:34:33"
 *     }
 *   }
 * }
 * 
 * @param   array     $Data               The data to insert
 * @param   string    $TableName          The table to insert into
 * @param   array     $ConnectionIndex    The connection to use
 * @param   boolean   $FullRewrite        TRUE if the table is to be deleted and rewritten with the array info, false otherwise
 * @return  boolean   TRUE on success, FALSE on failure
 * @since   0.0.7
 * @see     
 * @todo  ***TEST ALL. 
 */
function AssocToTable(&$Data, $TableName, $ConnectionIndex, $FullRewrite = FALSE)
{
    $MySQLDS = Array();
    if (DEBUGMODE)
    {
        echo date("Y-m-d H:i:s").' -> AssocToTable '.PHP_EOL;
    }
    print_r($Data);
    //Check if not empty
    if (empty($Data) === TRUE)
    {
        return FALSE;
    }

    /*Check if it is an associative array.TAKE THIS OUT AS WE USE MULTYARRAYS TO HOLD DIFFERENT RECORDS (numeric+assoc)
    if (IsAssocArray($Data) === FALSE)
    {
        return FALSE;
    }
    */

    //Check if it has MySQL ASSOC structure
    if (IsMySQLAssocDataStructure($Data) === FALSE)
    {
        $MySQLDS = AssocToMySQLAssoc($Data, $TableName, $ConnectionIndex, FALSE);
    }
    else
    {
        $MySQLDS = $Data;
    }
    //print_r($MySQLDS);

    //die();
    //Write data to table
    if ($FullRewrite === TRUE)
    {
        //1.- TruncateTable
        $Result = Truncate($ConnectionIndex, $TableName);
        if ($Result === FALSE)
        {
            return FALSE;
        }

        //2.- Insert values from array
        $Result2 = InsertFromMySQlAssocDataStructure($MySQLDS, $TableName, $ConnectionIndex);
        if ($Result2 === FALSE)
        {
            return FALSE;
        }
    }
    else
    {
        //Merge data into table
        $Result = MergeFromMySQlAssocDataStructure($MySQLDS, $TableName, $ConnectionIndex);
        if ($Result === FALSE)
        {
            return FALSE;
        }
    }
}

/**
 * GetMySQLTableMetadata gets  metadata from a MySQL table/resultset
 * @param   string  $TableName          The table to insert into
 * @param   array   $ConnectionIndex    The connection to use
 * @return  mixed   The metadata or FALSE on failure
 */
function GetMySQLTableMetadata($TableName, $ConnectionIndex)
{
    if (DEBUGMODE)
    {
        echo date("Y-m-d H:i:s").' -> GetMySQLTableMetadata '.PHP_EOL;
    }

    $Query = "SELECT * FROM ".$TableName." LIMIT 1;";

    $ResultSet = Read($ConnectionIndex, $Query, "ASSOC");
    if ($ResultSet === FALSE)
    {
        $ErrorMessage = 'Unable to connect to table: '.$TableName;
        ErrorLog($ErrorMessage, E_USER_ERROR);

        return FALSE;
    }

    return $ResultSet['Metadata'];
}

/**
 * TableToAssoc
 * Reads a table to an array, same structure as an ASSOC MySQL Query
 * The offset of the initial row is 0
 * @param   array   $Data               The array to populate with data
 * @param   string  $TableName          The table to load
 * @param   array   $ConnectionIndex    The connection to use
 * @param   string  $FilterCondition    A WHERE condition to filter the table
 * @param   int     $Offset             The offset to apply. 0 means from the beggining.
 * @param   int     $NumRows            The number of rows to load. 18446744073709551615 means all rows as MYSQL uses unisgned 64 bit integers. But PHP has no
 *                                          intrinsic support of unsigned integers, so we use PHP_INT_MAX = 9223372036854775807. So please use no more than
 *                                          9 (International System) Trillions of records, even if MySQl allows you to double that
 * @return  array   $Data by reference                              
 * @since   0.0.7
 * @see     https://dev.mysql.com/doc/refman/8.0/en/select.html
 * @todo
 */
function TableToAssoc(&$Data, $TableName, $ConnectionIndex, $FilterCondition = "", $Offset = 0, $NumRows = PHP_INT_MAX)
{
    if (DEBUGMODE)
    {
        echo date("Y-m-d H:i:s").' -> TableToAssoc '.PHP_EOL;
    }

    $Query = "SELECT * FROM ".$TableName." ".$FilterCondition." LIMIT ".$Offset.",".$NumRows;
    $Data = Read($ConnectionIndex, $Query, 'ASSOC');
    if (is_array($Data) === TRUE)
    {
        /*echo '*** READ COUNT***'.count($MySQLData).PHP_EOL;
        if (!empty($MySQLData['Data']))
        {
            //$Data = $MySQLData['Data'];
            //echo '*** DATA COUNT***'.count($Data).PHP_EOL;
            //We do keep all the resultset
            $Data = $MySQLData;
        }
        else
        {
            //Comes an empty resultset. Return it
            print_r($MySQLData);
            $Data = NULL;
        }
        */
        return TRUE;
    }
    else
    {
        return FALSE;
    }
}

/**
 * NumericToTable writes a numeric array to a DB Table
 * Just converts itself into assoc array and calls AssocToTable
 * The column names are specified on a lateral parametric array
 * Array type 
 *   array(3) {
 *     [0]=>
 *     array(4) {
 *       [0]=>
 *       string(1) "1"
 *       [1]=>
 *       string(8) "PENELOPE"
 *       [2]=>
 *       string(7) "GUINESS"
 *       [3]=>
 *       string(19) "2006-02-15 04:34:33"
 *     }
 *   }
 * 
 * @param   array   $Data               The data to insert
 * @param   string  $TableName          The table to insert into
 * @param   array   $ConnectionIndex    The connection to use
 * @param   array   $ColumnNames        The array of column names to use. First column *MUST* be the PK!    
 * @param   boolean $FullRewrite        TRUE if the table is to be deleted and rewritten with the array info, false otherwise
 * @return  boolean TRUE on success, FALSE on failure
 * @since   0.0.7
 * @see     
 * @todo 
 */
function NumericToTable($Data, $TableName, $ConnectionIndex, $ColumnNames, $FullRewrite = FALSE)
{
    if (DEBUGMODE)
    {
        echo date("Y-m-d H:i:s").' -> NumericToTable '.PHP_EOL;
    }

    $MySQLAssoc = array();
    $AssocMain = array();
    $AssocSecondary = array();
    $Rows = 0;
    $Columns = 0;
    foreach ($Data['Data'] as $Key => $Value)
    {
        foreach ($Value AS $SecKey => $ColumnValue)
        {
            $AssocSecondary[$ColumnNames[$SecKey]] = $ColumnValue;
            $Columns++;
        }
        $AssocMain[$Key] = $AssocSecondary;
        //Clear secondary array
        unset($AssocSecondary);
        $AssocSecondary = array();
        $Rows++;
    }

    $MySQLAssoc['Columns'] = $Columns;
    $MySQLAssoc['Rows'] = $Rows;
    $MySQLAssoc['Data'] = $AssocMain;

    return AssocToTable($MySQLAssoc, $TableName, $ConnectionIndex, $FullRewrite);
}

/**
 * IsLegitRead
 * Parses SELECT sentence to make sure it is safe and sound
 * @param   string  $Query  The query
 * @return  boolean TRUE if sentence is legit or false otherwise
 * @since   0.0.5
 * @see     https://dev.mysql.com/doc/refman/8.0/en/select.html
 * @todo
 */
function IsLegitRead($Query)
{
    if (DEBUGMODE)
    {
        echo date("Y-m-d H:i:s").' -> IsLegitRead '.PHP_EOL;
    }

    if (SmellyStatement($Query) === TRUE)
    {
        return FALSE;
    }

    $FirstStatement = StatementType($Query);
    if ($FirstStatement !== 'SELECT')
    {
        return FALSE;
    }

    if (JustOneStatement($Query, $FirstStatement) === FALSE)
    {
        return FALSE;
    }

    return TRUE;
}

/**
 * Read 
 * Gets info from the database in form of associative array, combination of assocand numeric array, object or JSON
 * @param   array   $ConnectionIndex    The connection heap
 * @param   string  $Query              The query
 * @param   string  $Output             The output type ("OBJECT", "ARRAY", "ASSOC", "NUMASOC" OR "JSON")
 * @return  mixed   The dataset, NULL if dataset is empty or FALSE if query fails
 * @since   0.0.3
 * @see     
 * @todo now is just mysql... will have to call MySQLRead, SQLSRVRead... and the like
 */
function Read($ConnectionIndex, $Query, $Output)
{
    if (DEBUGMODE)
    {
        echo date("Y-m-d H:i:s").' -> Read '.PHP_EOL;
    }

    $SoundConnection = TestConnection($ConnectionIndex);
    //If there are connection problems, return FALSE
    if ($SoundConnection === FALSE)
    {
        $ErrorMessage = 'Unable to make use of connection registered by index: '.$ConnectionIndex;
        ErrorLog($ErrorMessage, E_USER_ERROR);

        return FALSE;
    }

    $InData = $GLOBALS['DB'][$ConnectionIndex];

    //ValidateQuery
    if (IsLegitRead($Query) === FALSE)
    {
        $ErrorMessage = 'Invalid SELECT query: '.$Query;
        ErrorLog($ErrorMessage, E_USER_ERROR);

        return FALSE;
    }

    //Init connection
    $OutData = array();
    $ConnectionLink = MySQLInit($OutData);
    if (!$ConnectionLink)
    {
        $ErrorMessage = 'MySQLInit failure';
        ErrorLog($ErrorMessage, E_USER_ERROR);

        return FALSE;
    }

    //Establish options registered
    if (MySQLOptions($InData, $OutData, $ConnectionLink) === FALSE)
    {
        $OutData['Success'] = TRUE;
        $OutData['ReturnValue'] = 'MySQLOptions test failed';

        return FALSE;
    }

    //Connect
    if (MySQLRealConnect($InData, $OutData, $ConnectionLink) === FALSE)
    {
        $OutData['Success'] = TRUE;
        $OutData['ReturnValue'] = 'MySQLRealConnect test failed with code '.mysqli_errno($ConnectionLink).': '.mysqli_error($ConnectionLink);

        return FALSE;
    }

    //Now read
    $ResultSet = array();
    //fucks sentence $EscapedQuery = mysqli_real_escape_string($ConnectionToUse, $Query);
    if (mysqli_real_query($ConnectionLink, $Query) === FALSE)
    {
        $ErrorMessage = 'Query error with code '.mysqli_errno($ConnectionLink).': '.mysqli_error($ConnectionLink);
        ErrorLog($ErrorMessage, E_USER_ERROR);

        return FALSE;
    }

    //Get Data
    $TheData = mysqli_store_result($ConnectionLink);
    //var_dump($TheData);
    $ResultSet['Columns'] = mysqli_field_count($ConnectionLink);
    $ResultSet['Rows'] = mysqli_num_rows($TheData);
    $ResultSet['Metadata'] = MySQLTableMetadata($TheData, TRUE);

    //Return and empty resultset if there are no rows
    if ($ResultSet['Rows'] === 0)
    {
        $ResultSet['Data'] = NULL;

        return $ResultSet;
        //return NULL;
    }

    //Get data
    switch ($Output)
    {
        /*
         * OBJECT reading
         * array(3) {
         *   ["Columns"]=>
         *   int(4)
         *   ["Rows"]=>
         *   int(3)
         *   ["Data"]=>
         *   array(3) {
         *     [0]=>
         *     object(stdClass)#6 (4) {
         *       ["actor_id"]=>
         *       string(1) "1"
         *       ["first_name"]=>
         *       string(8) "PENELOPE"
         *       ["last_name"]=>
         *       string(7) "GUINESS"
         *       ["last_update"]=>
         *       string(19) "2006-02-15 04:34:33"
         *     }
         *     [1]=>
         *     object(stdClass)#7 (4) {
         *       ["actor_id"]=>
         *       string(1) "2"
         *       ["first_name"]=>
         *       string(4) "NICK"
         *       ["last_name"]=>
         *       string(8) "WAHLBERG"
         *       ["last_update"]=>
         *       string(19) "2006-02-15 04:34:33"
         *     }
         *   }
         * }
         * 
         */
        case "OBJECT":
            $Index = 0;
            //Return an object array
            while ($Obj = mysqli_fetch_object($TheData))
            {
                $ResultSet['Data'][$Index] = $Obj;
                $Index++;
            }
            break;
        /*
         * ARRAY reading
         * array(3) {
         *   ["Columns"]=>
         *   int(4)
         *   ["Rows"]=>
         *   int(3)
         *   ["Data"]=>
         *   array(3) {
         *     [0]=>
         *     array(4) {
         *       [0]=>
         *       string(1) "1"
         *       [1]=>
         *       string(8) "PENELOPE"
         *       [2]=>
         *       string(7) "GUINESS"
         *       [3]=>
         *       string(19) "2006-02-15 04:34:33"
         *     }
         *     [1]=>
         *     array(4) {
         *       [0]=>
         *       string(1) "2"
         *       [1]=>
         *       string(4) "NICK"
         *       [2]=>
         *       string(8) "WAHLBERG"
         *       [3]=>
         *       string(19) "2006-02-15 04:34:33"
         *     }
         *   }
         * }
         * 
         */
        case "ARRAY":
            //Return an indexed array
            $Index = 0;
            while ($Row = mysqli_fetch_array($TheData, MYSQLI_NUM))
            {
                $ResultSet['Data'][$Index] = $Row;
                $Index++;
            }
            break;
        /*
         * ASSOC reading
         * array(3) {
         *   ["Columns"]=>
         *   int(4)
         *   ["Rows"]=>
         *   int(3)
         *   ["Data"]=>
         *   array(3) {
         *     [0]=>
         *     array(4) {
         *       ["actor_id"]=>
         *       string(1) "1"
         *       ["first_name"]=>
         *       string(8) "PENELOPE"
         *       ["last_name"]=>
         *       string(7) "GUINESS"
         *       ["last_update"]=>
         *       string(19) "2006-02-15 04:34:33"
         *     }
         *     [1]=>
         *     array(4) {
         *       ["actor_id"]=>
         *       string(1) "2"
         *       ["first_name"]=>
         *       string(4) "NICK"
         *       ["last_name"]=>
         *       string(8) "WAHLBERG"
         *       ["last_update"]=>
         *       string(19) "2006-02-15 04:34:33"
         *     }
         *   }
         * }
         * 
         */
        case "ASSOC":
            //Return an assoc array
            $Index = 0;
            while ($Row = mysqli_fetch_array($TheData, MYSQLI_ASSOC))
            {
                $ResultSet['Data'][$Index] = $Row;
                $Index++;
            }
            break;
        /*
         * BOTH reading
         * array(3) {
         *   ["Columns"]=>
         *   int(4)
         *   ["Rows"]=>
         *   int(3)
         *   ["Data"]=>
         *   array(3) {
         *     [0]=>
         *     array(8) {
         *       [0]=>
         *       string(1) "1"
         *       ["actor_id"]=>
         *       string(1) "1"
         *       [1]=>
         *       string(8) "PENELOPE"
         *       ["first_name"]=>
         *       string(8) "PENELOPE"
         *       [2]=>
         *       string(7) "GUINESS"
         *       ["last_name"]=>
         *       string(7) "GUINESS"
         *       [3]=>
         *       string(19) "2006-02-15 04:34:33"
         *       ["last_update"]=>
         *       string(19) "2006-02-15 04:34:33"
         *     }
         *     [1]=>
         *     array(8) {
         *       [0]=>
         *       string(1) "2"
         *       ["actor_id"]=>
         *       string(1) "2"
         *       [1]=>
         *       string(4) "NICK"
         *       ["first_name"]=>
         *       string(4) "NICK"
         *       [2]=>
         *       string(8) "WAHLBERG"
         *       ["last_name"]=>
         *       string(8) "WAHLBERG"
         *       [3]=>
         *       string(19) "2006-02-15 04:34:33"
         *       ["last_update"]=>
         *       string(19) "2006-02-15 04:34:33"
         *     }
         *   }
         * }
         * 
         */
        case "BOTH":
            //Return both indexed and assoc array
            $Index = 0;
            while ($Row = mysqli_fetch_array($TheData, MYSQLI_BOTH))
            {
                $ResultSet['Data'][$Index] = $Row;
                $Index++;
            }
            break;
        case "JSON":
            //JSON = ASSOC + JSON encode
            $Index = 0;
            while ($Row = mysqli_fetch_array($TheData, MYSQLI_ASSOC))
            {
                $JustTheData[$Index] = $Row;
                $Index++;
            }
            $JSONData = json_encode($JustTheData,  JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_NUMERIC_CHECK);
            $ResultSet['Data'] = $JSONData;
            break;
        default:
            $ErrorMessage = 'Unknown formatting option: '.$Output;
            ErrorLog($ErrorMessage, E_USER_ERROR);

            return FALSE;
    }

    //Close connection
    if (mysqli_close($ConnectionLink) === FALSE)
    {
        $ErrorMessage = 'Error closing connection with code '.mysqli_errno($ConnectionLink).': '.mysqli_error($ConnectionLink);
        ErrorLog($ErrorMessage, E_USER_ERROR);

        return FALSE;
    }
    //print_r($ResultSet);

    //die();
    //Return Data
    return $ResultSet;
}


/**
 * MySQLTableMetadata gets the metadata on the fields included in the resultset
 * Seem to be giving back a charsetnr inconsistent with both the DB-defined COLLATION/CHARSET and mysqli_get_charset
 * - which also do not agree between them- so we are ignoring this data
 * @param   array   $ResultSet      The MySQL resultset to check
 * @param   boolean $FullInfo       The flag that marks if all metadata fields should be returned
 * @return  array                   The metadata info
 * @since   0.0.7
 * @see     https://www.php.net/manual/en/mysqli-result.fetch-fields.php
 * @todo
 */
function MySQLTableMetadata($ResultSet, $FullInfo = FALSE)
{
    if (DEBUGMODE)
    {
        echo date("Y-m-d H:i:s").' -> MySQLTableMetadata '.PHP_EOL;
    }

    $Metadata = array();
    //print_r(get_defined_constants(TRUE));
    //$CharsetArray = get_object_vars(mysqli_get_charset($ConnectionLink));
    $MetadataObjArr = mysqli_fetch_fields($ResultSet);
    print_r($MetadataObjArr);
    //print_r($CharsetArray);
    //We prepare a pure assoc metadata array
    foreach ($MetadataObjArr as $FieldKey => $FieldContents)
    {
        $FieldMetadata = get_object_vars($FieldContents);
        $Metadata[$FieldMetadata['orgname']]['FieldName'] = $FieldMetadata['name'];                         //The field name on the resultset. Can be an alias
        $Metadata[$FieldMetadata['orgname']]['ColumnName'] = $FieldMetadata['orgname'];                     //Original column name if an alias was specified
        $Metadata[$FieldMetadata['orgname']]['Type'] = $FieldMetadata['type'];                              //The data type used for this field
        $Metadata[$FieldMetadata['orgname']]['TypeDesc'] = MySQLTypeCodeExplode($FieldMetadata['type']);    //The type constant corresponding to the field type
        if ($FullInfo === TRUE)
        {
            $Metadata[$FieldMetadata['orgname']]['RSTable'] = $FieldMetadata['table'];              //The table name on the resultset. Can be an alias
            $Metadata[$FieldMetadata['orgname']]['DBTable'] = $FieldMetadata['orgtable'];           //Original table name if an alias was specified
            $Metadata[$FieldMetadata['orgname']]['MaxValueSize'] = $FieldMetadata['max_length'];    //The maximum width of the field for the result set
            $Metadata[$FieldMetadata['orgname']]['DefinedSize'] = $FieldMetadata['length'];         //The width of the field, in bytes, as specified in the table definition. Note that this number (bytes) might differ from your table definition value (characters), depending on the character set you use. For example, the character set utf8 has 3 bytes per character, so varchar(10) will return a length of 30 for utf8 (10*3), but return 10 for latin1 (10*1).
            //$Metadata[$FieldMetadata['orgname']]['CharSetConst'] = $FieldMetadata['charsetnr'];     //The character set number (id) for the field.
            $Metadata[$FieldMetadata['orgname']]['Flags'] = $FieldMetadata['flags'];                //An integer representing the bit-flags for the field.
            $Metadata[$FieldMetadata['orgname']]['FlagsDesc'] = MySQLRSFlags($FieldMetadata['flags']);  //Description of the flags that have been set
            $Metadata[$FieldMetadata['orgname']]['Decimals'] = $FieldMetadata['decimals'];          //The number of decimals used (for integer fields)
        }
    }

    return $Metadata;
}

/**
 * MySQLTypeCodeExplode returns the const STRING of a MySQL Type code
 * Those are:
 * - MYSQLI_TYPE_DECIMAL:       Field is defined as DECIMAL
 * - MYSQLI_TYPE_NEWDECIMAL:    Precision math DECIMAL or NUMERIC field (MySQL 5.0.3 and up)
 * - MYSQLI_TYPE_BIT:           Field is defined as BIT (MySQL 5.0.3 and up)
 * - MYSQLI_TYPE_TINY:          Field is defined as TINYINT
 * - MYSQLI_TYPE_SHORT:         Field is defined as SMALLINT
 * - MYSQLI_TYPE_LONG:          Field is defined as INT
 * - MYSQLI_TYPE_FLOAT:         Field is defined as FLOAT
 * - MYSQLI_TYPE_DOUBLE:        Field is defined as DOUBLE
 * - MYSQLI_TYPE_NULL:          Field is defined as DEFAULT NULL
 * - MYSQLI_TYPE_TIMESTAMP:     Field is defined as TIMESTAMP
 * - MYSQLI_TYPE_LONGLONG:      Field is defined as BIGINT
 * - MYSQLI_TYPE_INT24:         Field is defined as MEDIUMINT
 * - MYSQLI_TYPE_DATE:          Field is defined as DATE
 * - MYSQLI_TYPE_TIME:          Field is defined as TIME
 * - MYSQLI_TYPE_DATETIME:      Field is defined as DATETIME
 * - MYSQLI_TYPE_YEAR:          Field is defined as YEAR
 * - MYSQLI_TYPE_NEWDATE:       Field is defined as DATE
 * - MYSQLI_TYPE_INTERVAL:      Field is defined as INTERVAL
 * - MYSQLI_TYPE_ENUM:          Field is defined as ENUM
 * - MYSQLI_TYPE_SET:           Field is defined as SET
 * - MYSQLI_TYPE_TINY_BLOB:     Field is defined as TINYBLOB
 * - MYSQLI_TYPE_MEDIUM_BLOB:   Field is defined as MEDIUMBLOB
 * - MYSQLI_TYPE_LONG_BLOB:     Field is defined as LONGBLOB
 * - MYSQLI_TYPE_BLOB:          Field is defined as BLOB, TEXT, TINYTEXT, MEDIUMTEXT or LONGTEXT
 * - MYSQLI_TYPE_VAR_STRING:    Field is defined as VARCHAR or VARBINARY
 * - MYSQLI_TYPE_STRING:        Field is defined as CHAR or BINARY
 * - MYSQLI_TYPE_CHAR:          Field is defined as TINYINT. For CHAR, see MYSQLI_TYPE_STRING
 * - MYSQLI_TYPE_GEOMETRY:      Field is defined as GEOMETRY
 * - MYSQLI_TYPE_JSON:          Field is defined as JSON. Only valid for mysqlnd and MySQL 5.7.8 and up.
 *
 * @param   int     $MySQLTypeCode  The type code
 * @return  string                  The CONST literal for the code
 * @since   0.0.7
 * @see     https://www.php.net/manual/en/mysqli.constants.php
 * @todo
 */
function MySQLTypeCodeExplode($MySQLTypeCode)
{
    if (DEBUGMODE)
    {
        echo date("Y-m-d H:i:s").' -> MySQLTypeCodeExplode '.PHP_EOL;
    }

    switch ($MySQLTypeCode)
    {
        case 0:
            return 'MYSQLI_TYPE_DECIMAL';       //Field is defined as DECIMAL
        case 1:
            return 'MYSQLI_TYPE_TINY';          //Field is defined as TINYINT. We never return MYSQLI_TYPE_CHAR has has no use.
        case 2:
            return 'MYSQLI_TYPE_SHORT';         //Field is defined as SMALLINT
        case 3:
            return 'MYSQLI_TYPE_LONG';          //Field is defined as INT
        case 4:
            return 'MYSQLI_TYPE_FLOAT';         //Field is defined as FLOAT
        case 5:
            return 'MYSQLI_TYPE_DOUBLE';        //Field is defined as DOUBLE
        case 6:
            return 'MYSQLI_TYPE_NULL';          //Field is defined as DEFAULT NULL
        case 7:
            return 'MYSQLI_TYPE_TIMESTAMP';     //Field is defined as TIMESTAMP
        case 8:
            return 'MYSQLI_TYPE_LONGLONG';      //Field is defined as BIGINT
        case 9:
            return 'MYSQLI_TYPE_INT24';         //Field is defined as MEDIUMINT
        case 10:
            return 'MYSQLI_TYPE_DATE';          //Field is defined as DATE
        case 11:
            return 'MYSQLI_TYPE_TIME';          //Field is defined as TIME
        case 12:
            return 'MYSQLI_TYPE_DATETIME';      //Field is defined as DATETIME
        case 13:
            return 'MYSQLI_TYPE_YEAR';          //Field is defined as YEAR
        case 14:
            return 'MYSQLI_TYPE_NEWDATE';       //Field is defined as DATE
        case 16:
            return 'MYSQLI_TYPE_BIT';           //Field is defined as BIT (MySQL 5.0.3 and up)
        case 245:
            return 'MYSQLI_TYPE_JSON';          //Field is defined as JSON. Only valid for mysqlnd and MySQL 5.7.8 and up.
        case 246:
            return 'MYSQLI_TYPE_NEWDECIMAL';    //Precision math DECIMAL or NUMERIC field (MySQL 5.0.3 and up)
        case 247:
            return 'MYSQLI_TYPE_ENUM';          //Field is defined as ENUM. We never return MYSQLI_TYPE_INTERVAL
        case 248:
            return 'MYSQLI_TYPE_SET';           //Field is defined as SET
        case 249:
            return 'MYSQLI_TYPE_TINY_BLOB';     //Field is defined as TINYBLOB
        case 250:
            return 'MYSQLI_TYPE_MEDIUM_BLOB';   //Field is defined as MEDIUMBLOB
        case 251:
            return 'MYSQLI_TYPE_LONG_BLOB';     //Field is defined as LONGBLOB
        case 252:
            return 'MYSQLI_TYPE_BLOB';          //Field is defined as BLOB, TEXT, TINYTEXT, MEDIUMTEXT or LONGTEXT
        case 253:
            return 'MYSQLI_TYPE_VAR_STRING';    //Field is defined as VARCHAR or VARBINARY
        case 254:
            return 'MYSQLI_TYPE_STRING';        //Field is defined as CHAR or BINARY
        case 255:
            return 'MYSQLI_TYPE_GEOMETRY';      //Field is defined as GEOMETRY
    }
}

/**
 * MySQLProperQuote quotes the field according to the data type
 * @param type $Field
 * @param type $TypeConst
 * @return type
 * @since   0.0.7
 * @see     https://dev.mysql.com/doc/refman/8.0/en/literals.html
 *          https://dev.mysql.com/doc/refman/8.0/en/date-and-time-types.html
 *          https://dev.mysql.com/doc/refman/8.0/en/string-types.html
 * @todo
 */
function MySQLProperQuote($Field, $TypeConst)
{
    if (DEBUGMODE)
    {
        echo date("Y-m-d H:i:s").' -> MySQLProperQuote '.PHP_EOL;
    }

    //Ensure null on null values
    if (is_null($Field))
    {
        return "NULL";
    }

    //Process the rest
    switch ($TypeConst)
    {
        //String types go quoted and properly escaped if there are quotes inside, but only if not empty.
        case 'MYSQLI_TYPE_STRING':
        case 'MYSQLI_TYPE_VAR_STRING':
        case 'MYSQLI_TYPE_BLOB':
        case 'MYSQLI_TYPE_TINY_BLOB':
        case 'MYSQLI_TYPE_TINY_BLOB':
        case 'MYSQLI_TYPE_MEDIUM_BLOB':
        case 'MYSQLI_TYPE_LONG_BLOB':
        case 'MYSQLI_TYPE_ENUM':
        case 'MYSQLI_TYPE_SET':
            if (empty($Field))
            {
                return "NULL";
            }
            else
            {
                $EscapedField = mb_ereg_replace('[\x00\x0A\x0D\x1A\x22\x27\x5C]', '\\\0', $Field);
                //echo $TypeConst."->\'".$EscapedField."\'".PHP_EOL;

                return "'$EscapedField'";
            }

        //All Date and Time types go quoted excep MYSQLI_TYPE_YEAR, but only if not empty.
        case 'MYSQLI_TYPE_DATE':
        case 'MYSQLI_TYPE_TIME':
        case 'MYSQLI_TYPE_DATETIME':
        case 'MYSQLI_TYPE_NEWDATE':
        case 'MYSQLI_TYPE_TIMESTAMP':
            if (empty($Field))
            {
                return "NULL";
            }
            else
            {
                //echo $TypeConst."->\'".$Field."\'".PHP_EOL;

                return "'$Field'";
            }

        //All the rest go unquoted
        default:
            //echo $TypeConst."->".$Field.PHP_EOL;

            return $Field;
    }
}

/**
 * MySQLRSFlags explicits the MySQL flags that have been set
 * @param   int     $FlagNumber     The flag number
 * @return  string                  The set flag strings
 * @since   0.0.7
 * @see     
 * @todo
 */
function MySQLRSFlags($FlagNumber)
{
    if (DEBUGMODE)
    {
        echo date("Y-m-d H:i:s").' -> MySQLRSFlags '.PHP_EOL;
    }

    //Load available FLAGS
    $DefinedConstants = get_defined_constants(TRUE);
    $DefinedFlags = array();
    foreach ($DefinedConstants['mysqli'] as $Key => $Value)
    {
        if (preg_match('/MYSQLI_(.*)_FLAG$/', $Key, $Matches))
        {
            if (!array_key_exists($Value, $DefinedFlags))
            {
                $DefinedFlags[$Value] = $Matches[1];
            }
        }
    }

    //Find FLAGS that have been set
    $JustOne = TRUE;
    $SetFlags = "";
    foreach ($DefinedFlags as $Key => $Value)
    {
        if ($FlagNumber&$Key)
        {
            if ($JustOne === TRUE)
            {
                $SetFlags = $Value;
                $JustOne = FALSE;
            }
            else
            {
                $SetFlags .= ' | '.$Value;
            }
        }
    }

    //return FLAGS that have been set
    return $SetFlags;
}
/**
 * IsLegitUpdate
 * Parses UPDATE sentence to make sure it is safe and sound
 * @param   string  $Query  The query
 * @return  boolean TRUE if sentence is legit or false otherwise
 * @since   0.0.6
 * @see     https://dev.mysql.com/doc/refman/8.0/en/select.html
 * @todo
 */
function IsLegitUpdate($Query)
{
    if (DEBUGMODE)
    {
        echo date("Y-m-d H:i:s").' -> IsLegitUpdate '.PHP_EOL;
    }

    if (SmellyStatement($Query) === TRUE)
    {
        return FALSE;
    }

    $FirstStatement = StatementType($Query);
    if ($FirstStatement !== 'UPDATE')
    {
        return FALSE;
    }

    if (JustOneStatement($Query, $FirstStatement) === FALSE)
    {
        return FALSE;
    }

    return TRUE;
}

/**
 * Update 
 * changes info on the database
 * @param   array   $ConnectionIndex    The connection heap
 * @param   string  $Query              The query
 * @return  mixed   Affected rows or NULL if affected rows are 0; FALSE if UPDATE fails
 * @since   0.0.3
 * @see     
 * @todo Update Validation
 */
function Update($ConnectionIndex, $Query)
{
    if (DEBUGMODE)
    {
        echo date("Y-m-d H:i:s").' -> Update '.PHP_EOL;
    }

    $SoundConnection = TestConnection($ConnectionIndex);
    //If there are connection problems, return FALSE
    if ($SoundConnection === FALSE)
    {
        $ErrorMessage = 'Unable to make use of connection registered by index: '.$ConnectionIndex;
        ErrorLog($ErrorMessage, E_USER_ERROR);

        return FALSE;
    }

    $InData = $GLOBALS['DB'][$ConnectionIndex];

    //ValidateQuery
    if (IsLegitUpdate($Query) === FALSE)
    {
        $ErrorMessage = 'Invalid UPDATE query: '.$Query;
        ErrorLog($ErrorMessage, E_USER_ERROR);

        return FALSE;
    }

    //Init connection
    $OutData = array();
    $ConnectionLink = MySQLInit($OutData);
    if (!$ConnectionLink)
    {
        $ErrorMessage = 'MySQLInit failure';
        ErrorLog($ErrorMessage, E_USER_ERROR);

        return FALSE;
    }

    //Establish options registered
    if (MySQLOptions($InData, $OutData, $ConnectionLink) === FALSE)
    {
        $OutData['Success'] = TRUE;
        $OutData['ReturnValue'] = 'MySQLOptions test failed';

        return FALSE;
    }

    //Connect
    if (MySQLRealConnect($InData, $OutData, $ConnectionLink) === FALSE)
    {
        $OutData['Success'] = TRUE;
        $OutData['ReturnValue'] = 'MySQLRealConnect test failed with code '.mysqli_errno($ConnectionLink).': '.mysqli_error($ConnectionLink);

        return FALSE;
    }

    //Now update
    if (mysqli_real_query($ConnectionLink, $Query) === FALSE)
    {
        $ErrorMessage = 'Update error with code '.mysqli_errno($ConnectionLink).': '.mysqli_error($ConnectionLink);
        ErrorLog($ErrorMessage, E_USER_ERROR);

        return FALSE;
    }

    $ResultSet['AffectedRows'] = mysqli_affected_rows($ConnectionLink);

    //Close connection
    if (mysqli_close($ConnectionLink) === FALSE)
    {
        $ErrorMessage = 'Error closing connection with code '.mysqli_errno($ConnectionLink).': '.mysqli_error($ConnectionLink);
        ErrorLog($ErrorMessage, E_USER_ERROR);

        return FALSE;
    }

    //Return values
    if ($ResultSet['AffectedRows'] === 0)
    {
        return NULL;
    }

    return $ResultSet;
}

/**
 * IsLegitInsert
 * Parses INSERT sentence to make sure it is safe and sound
 * @param   string  $Query  The query
 * @return  boolean TRUE if sentence is legit or false otherwise
 * @since   0.0.6
 * @see     https://dev.mysql.com/doc/refman/8.0/en/select.html
 * @todo
 */
function IsLegitInsert($Query)
{
    if (DEBUGMODE)
    {
        echo date("Y-m-d H:i:s").' -> IsLegitInsert '.PHP_EOL;
    }

    if (SmellyStatement($Query) === TRUE)
    {
        return FALSE;
    }

    $FirstStatement = StatementType($Query);
    if ($FirstStatement !== 'INSERT')
    {
        return FALSE;
    }

    if (JustOneStatement($Query, $FirstStatement) === FALSE)
    {
        return FALSE;
    }

    return TRUE;
}

/**
 * Insert 
 * inserts info into the database
 * @param   array   $ConnectionIndex    The connection heap
 * @param   string  $Query              The query
 * @return  mixed   Affected rows or FALSE if UPDATE fails
 * @since   0.0.3
 * @see     
 * @todo
 */
function Insert($ConnectionIndex, $Query)
{
    if (DEBUGMODE)
    {
        echo date("Y-m-d H:i:s").' -> Insert: '.$Query.PHP_EOL;
    }

    $SoundConnection = TestConnection($ConnectionIndex);
    //If there are connection problems, return FALSE
    if ($SoundConnection === FALSE)
    {
        $ErrorMessage = 'Unable to make use of connection registered by index: '.$ConnectionIndex;
        ErrorLog($ErrorMessage, E_USER_ERROR);

        return FALSE;
    }

    $InData = $GLOBALS['DB'][$ConnectionIndex];

    //ValidateQuery
    if (IsLegitInsert($Query) === FALSE)
    {
        $ErrorMessage = 'Invalid INSERT query: '.$Query;
        ErrorLog($ErrorMessage, E_USER_ERROR);

        return FALSE;
    }

    //Init connection
    $OutData = array();
    $ConnectionLink = MySQLInit($OutData);
    if (!$ConnectionLink)
    {
        $ErrorMessage = 'MySQLInit failure';
        ErrorLog($ErrorMessage, E_USER_ERROR);

        return FALSE;
    }

    //Establish options registered
    if (MySQLOptions($InData, $OutData, $ConnectionLink) === FALSE)
    {
        $OutData['Success'] = TRUE;
        $OutData['ReturnValue'] = 'MySQLOptions test failed';

        return FALSE;
    }

    //Connect
    if (MySQLRealConnect($InData, $OutData, $ConnectionLink) === FALSE)
    {
        $OutData['Success'] = TRUE;
        $OutData['ReturnValue'] = 'MySQLRealConnect test failed with code '.mysqli_errno($ConnectionLink).': '.mysqli_error($ConnectionLink);

        return FALSE;
    }

    //Now insert
    //Fucks sentence $EscapedQuery = mysqli_real_escape_string($ConnectionToUse, $Query);
    if (mysqli_real_query($ConnectionLink, $Query) === FALSE)
    {
        $ErrorMessage = 'Insert error with code '.mysqli_errno($ConnectionLink).': '.mysqli_error($ConnectionLink);
        ErrorLog($ErrorMessage, E_USER_ERROR);

        return FALSE;
    }

    $ResultSet['AffectedRows'] = mysqli_affected_rows($ConnectionLink);

    //Close connection
    if (mysqli_close($ConnectionLink) === FALSE)
    {
        $ErrorMessage = 'Error closing connection with code '.mysqli_errno($ConnectionLink).': '.mysqli_error($ConnectionLink);
        ErrorLog($ErrorMessage, E_USER_ERROR);

        return FALSE;
    }

    //Return values
    if ($ResultSet['AffectedRows'] === 0)
    {
        return NULL;
    }

    return $ResultSet;
}

/**
 * IsLegitMerge
 * Parses INSERT ...ON DUPLICATE KEY UPDATE sentence to make sure it is safe and sound
 * @param   string  $Query  The query
 * @return  boolean TRUE if sentence is legit or false otherwise
 * @since   0.0.6
 * @see     https://dev.mysql.com/doc/refman/8.0/en/select.html
 * @todo
 */
function IsLegitMerge($Query)
{
    if (DEBUGMODE)
    {
        echo date("Y-m-d H:i:s").' -> IsLegitMerge '.PHP_EOL;
    }

    if (SmellyStatement($Query) === TRUE)
    {
        return FALSE;
    }

    $FirstStatement = StatementType($Query);
    if ($FirstStatement !== 'INSERT')
    {
        return FALSE;
    }

    if (JustOneStatement($Query, $FirstStatement) === FALSE)
    {
        return FALSE;
    }

    return TRUE;
}

/**
 * Merge 
 * merges info into the database
 * @param   array   $ConnectionIndex    The connection heap
 * @param   string  $Query              The query
 * @return  mixed   Affected rows or FALSE if UPDATE fails
 * @since   0.0.3
 * @see     
 * @todo
 */
function Merge($ConnectionIndex, $Query)
{
    if (DEBUGMODE)
    {
        echo date("Y-m-d H:i:s").' -> Merge: '.$Query.PHP_EOL;
    }

    $SoundConnection = TestConnection($ConnectionIndex);
    //If there are connection problems, return FALSE
    if ($SoundConnection === FALSE)
    {
        $ErrorMessage = 'Unable to make use of connection registered by index: '.$ConnectionIndex;
        ErrorLog($ErrorMessage, E_USER_ERROR);

        return FALSE;
    }

    $InData = $GLOBALS['DB'][$ConnectionIndex];

    //ValidateQuery
    if (IsLegitMerge($Query) === FALSE)
    {
        $ErrorMessage = 'Invalid INSERT ...ON DUPLICATE KEY UPDATE query: '.$Query;
        ErrorLog($ErrorMessage, E_USER_ERROR);

        return FALSE;
    }

    //Init connection
    $OutData = array();
    $ConnectionLink = MySQLInit($OutData);
    if (!$ConnectionLink)
    {
        $ErrorMessage = 'MySQLInit failure';
        ErrorLog($ErrorMessage, E_USER_ERROR);

        return FALSE;
    }

    //Establish options registered
    if (MySQLOptions($InData, $OutData, $ConnectionLink) === FALSE)
    {
        $OutData['Success'] = TRUE;
        $OutData['ReturnValue'] = 'MySQLOptions test failed';

        return FALSE;
    }

    //Connect
    if (MySQLRealConnect($InData, $OutData, $ConnectionLink) === FALSE)
    {
        $OutData['Success'] = TRUE;
        $OutData['ReturnValue'] = 'MySQLRealConnect test failed with code '.mysqli_errno($ConnectionLink).': '.mysqli_error($ConnectionLink);

        return FALSE;
    }

    //Now insert
    //Fucks sentence $EscapedQuery = mysqli_real_escape_string($ConnectionToUse, $Query);
    if (mysqli_real_query($ConnectionLink, $Query) === FALSE)
    {
        $ErrorMessage = 'Merge error with code '.mysqli_errno($ConnectionLink).': '.mysqli_error($ConnectionLink);
        ErrorLog($ErrorMessage, E_USER_ERROR);

        return FALSE;
    }

    $ResultSet['AffectedRows'] = mysqli_affected_rows($ConnectionLink);

    //Close connection
    if (mysqli_close($ConnectionLink) === FALSE)
    {
        $ErrorMessage = 'Error closing connection with code '.mysqli_errno($ConnectionLink).': '.mysqli_error($ConnectionLink);
        ErrorLog($ErrorMessage, E_USER_ERROR);

        return FALSE;
    }

    //Return values
    if ($ResultSet['AffectedRows'] === 0)
    {
        return NULL;
    }

    return $ResultSet;
}

/**
 * IsLegitDelete
 * Parses DELETE sentence to make sure it is safe and sound
 * @param   string  $Query  The query
 * @return  boolean TRUE if sentence is legit or false otherwise
 * @since   0.0.6
 * @see     https://dev.mysql.com/doc/refman/8.0/en/select.html
 * @todo
 */
function IsLegitDelete($Query)
{
    if (DEBUGMODE)
    {
        echo date("Y-m-d H:i:s").' -> IsLegitDelete '.PHP_EOL;
    }

    if (SmellyStatement($Query) === TRUE)
    {
        return FALSE;
    }

    $FirstStatement = StatementType($Query);
    if ($FirstStatement !== 'DELETE')
    {
        return FALSE;
    }

    if (JustOneStatement($Query, $FirstStatement) === FALSE)
    {
        return FALSE;
    }

    return TRUE;
}

/**
 * Delete 
 * deletes info from the database
 * @param   array   $ConnectionIndex    The connection heap
 * @param   string  $Query              The query
 * @return  mixed   Affected rows or FALSE if UPDATE fails
 * @since   0.0.3
 * @see     
 * @todo
 */
function Delete($ConnectionIndex, $Query)
{
    if (DEBUGMODE)
    {
        echo date("Y-m-d H:i:s").' -> Delete '.PHP_EOL;
    }

    $SoundConnection = TestConnection($ConnectionIndex);
    //If there are connection problems, return FALSE
    if ($SoundConnection === FALSE)
    {
        $ErrorMessage = 'Unable to make use of connection registered by index: '.$ConnectionIndex;
        ErrorLog($ErrorMessage, E_USER_ERROR);

        return FALSE;
    }

    $InData = $GLOBALS['DB'][$ConnectionIndex];

    //ValidateQuery
    if (IsLegitDelete($Query) === FALSE)
    {
        $ErrorMessage = 'Invalid DELETE query: '.$Query;
        ErrorLog($ErrorMessage, E_USER_ERROR);

        return FALSE;
    }

    //Init connection
    $OutData = array();
    $ConnectionLink = MySQLInit($OutData);
    if (!$ConnectionLink)
    {
        $ErrorMessage = 'MySQLInit failure';
        ErrorLog($ErrorMessage, E_USER_ERROR);

        return FALSE;
    }

    //Establish options registered
    if (MySQLOptions($InData, $OutData, $ConnectionLink) === FALSE)
    {
        $OutData['Success'] = TRUE;
        $OutData['ReturnValue'] = 'MySQLOptions test failed';

        return FALSE;
    }

    //Connect
    if (MySQLRealConnect($InData, $OutData, $ConnectionLink) === FALSE)
    {
        $OutData['Success'] = TRUE;
        $OutData['ReturnValue'] = 'MySQLRealConnect test failed with code '.mysqli_errno($ConnectionLink).': '.mysqli_error($ConnectionLink);

        return FALSE;
    }

    //Now delete
    //Fucks sentence $EscapedQuery = mysqli_real_escape_string($ConnectionToUse, $Query);
    if (mysqli_real_query($ConnectionLink, $Query) === FALSE)
    {
        $ErrorMessage = 'Delete error with code '.mysqli_errno($ConnectionLink).': '.mysqli_error($ConnectionLink);
        ErrorLog($ErrorMessage, E_USER_ERROR);

        return FALSE;
    }

    $ResultSet['AffectedRows'] = mysqli_affected_rows($ConnectionLink);

    //Close connection
    if (mysqli_close($ConnectionLink) === FALSE)
    {
        $ErrorMessage = 'Error closing connection with code '.mysqli_errno($ConnectionLink).': '.mysqli_error($ConnectionLink);
        ErrorLog($ErrorMessage, E_USER_ERROR);

        return FALSE;
    }

    //Return values
    if ($ResultSet['AffectedRows'] === 0)
    {
        return NULL;
    }

    return $ResultSet;















    //LO viejuno
    $ConnectionToUse = TestResurrectConnection($ConnectionIndex);
    //If there are connection problems, return FALSE
    if ($ConnectionToUse === FALSE)
    {
        $ErrorMessage = 'Unable to make use of connection registered by index: '.$ConnectionIndex;
        ErrorLog($ErrorMessage, E_USER_ERROR);

        return FALSE;
    }

    //Now insert
    //Fucks sentence $EscapedQuery = mysqli_real_escape_string($ConnectionToUse, $Query);
    if (mysqli_real_query($ConnectionToUse, $Query) === FALSE)
    {
        $ErrorMessage = 'Delete error with code '.mysqli_errno($ConnectionToUse).': '.mysqli_error($ConnectionToUse);
        ErrorLog($ErrorMessage, E_USER_ERROR);

        return FALSE;
    }

    $ResultSet['AffectedRows'] = mysqli_affected_rows($ConnectionToUse);

    if ($ResultSet['AffectedRows'] === 0)
    {
        return NULL;
    }

    return $ResultSet;
}

/**
 * Reconnect
 * Connects to a previously sanitized standard or persistent connection
 * @param   array   $ConnectionIndex    The connection heap
 * @return  mixed   ConnectionLink or FALSE if connection fails
 * @since   0.0.3
 * @see     
 * @todo adapt to multi-database
 * @deprecated
 */
function Reconnect($ConnectionIndex)
{
    if (DEBUGMODE)
    {
        echo date("Y-m-d H:i:s").' -> Reconnect '.PHP_EOL;
    }

    $OutData = array();
    $InData = array();
    $KeepTheConnection = FALSE;
    //If the registered index is lost, return error
    if (!isset($GLOBALS['DB'][$ConnectionIndex]))
    {
        $ErrorMessage = 'Unable to find registered connection with index: '.$ConnectionIndex;
        ErrorLog($ErrorMessage, E_USER_ERROR);

        return FALSE;
    }

    //Now prepare the connections with the parameters
    $ConnectionLink = MySQLInit($OutData);
    if (!$ConnectionLink)
    {
        $ErrorMessage = 'MySQLInit failed on connection '.$ConnectionIndex;
        ErrorLog($ErrorMessage, E_USER_ERROR);

        return FALSE;
    }
    //Prepare data based on pooled/unpooled
    if (is_int(MIL_POOLEDCONNECTIONS))
    {
        //Pooled connection. Get a random one from the pool
        $IndexMax = MIL_POOLEDCONNECTIONS-1;
        $RandomConn = random_int(0,$IndexMax);
        $ConnectionData = $GLOBALS['DB'][$ConnectionIndex][$RandomConn];
    }
    else
    {
        //Single connection
        $ConnectionData = $GLOBALS['DB'][$ConnectionIndex];
    }
    //Establish options registered
    $InData['System'] = $GLOBALS['DB'][$ConnectionIndex]['System'];
    $InData['ServerName'] = $GLOBALS['DB'][$ConnectionIndex]['ServerName'];
    $InData['Database'] = $GLOBALS['DB'][$ConnectionIndex]['Database'];
    $InData['DBUser'] = $GLOBALS['DB'][$ConnectionIndex]['DBUser'];
    $InData['DBPassword'] = $GLOBALS['DB'][$ConnectionIndex]['DBPassword'];
    if (isset($InData['ConnectionLink']))
    {
        //We are coming from a failed kept-up connection. Flag It
        $KeepTheConnection = TRUE;
    }
    $InData['Options'] = $GLOBALS['DB'][$ConnectionIndex]['Options'];

    //Stablish the connection options
    if (MySQLOptions($InData, $OutData, $ConnectionLink) === FALSE)
    {
        $ErrorMessage = 'MySQLOptions failed on connection '.$ConnectionIndex;
        ErrorLog($ErrorMessage, E_USER_ERROR);

        return FALSE;
    }

    //Connect to the Database
    if (MySQLRealConnect($InData, $OutData, $ConnectionLink) === FALSE)
    {
        $ErrorMessage = 'MySQLRealConnect failed on connection '.$ConnectionIndex.' with code '.mysqli_errno($ConnectionLink).': '.mysqli_error($ConnectionLink);
        ErrorLog($ErrorMessage, E_USER_ERROR);

        return FALSE;
    }

    //Everything sweet. Keep the connection if needed and return it
    if ($KeepTheConnection === TRUE)
    {
        $GLOBALS['DB'][$ConnectionIndex]['ConnectionLink'] = $ConnectionLink;
    }

    return $ConnectionLink;
}

/**
 * TestConnection
 * Recreates connection array if it does not exist
 * @param   string  $ConnectionIndex
 * @return  boolean
 */
function TestConnection($ConnectionIndex)
{
    if (DEBUGMODE)
    {
        echo date("Y-m-d H:i:s").' -> TestConnection '.PHP_EOL;
    }

    //If connection does not exist...
    if (!isset($GLOBALS['DB'][$ConnectionIndex]))
    {
        //and no fallback cache exist, return error
        if (MIL_ACPU === FALSE)
        {
            $ErrorMessage = 'No connection registered by index: '.$ConnectionIndex;
            ErrorLog($ErrorMessage, E_USER_ERROR);

            return FALSE;
        }
        else
        {
            //But if it does, recreate globals from cache
            $ConnectionCache = apcu_fetch('DB');
            if ($ConnectionCache === FALSE)
            {
                //Error when recreating globals
                $ErrorMessage = 'No connection info available on ACPU';
                ErrorLog($ErrorMessage, E_USER_ERROR);

                return FALSE;
            }
            else
            {
                $GLOBALS['DB'] = $ConnectionCache;
            }
        }
    }

    //Now check if the connection was cached
    if (!isset($GLOBALS['DB'][$ConnectionIndex]))
    {
        $ErrorMessage = 'No connection registered by index: '.$ConnectionIndex;
        ErrorLog($ErrorMessage, E_USER_ERROR);

        return FALSE;
    }
    else
    {
        return TRUE;
    }
}

/**
 * TestResurrectConnection
 * Checks connection health and reconnects if necessary
 * @param   string  $ConnectionIndex
 * @return  mixed   The connection to use or FALSE on error 
 * @since   0.0.3
 * @see     
 * @todo 
 * @deprecated
 */
function TestResurrectConnection($ConnectionIndex)
{
    if (DEBUGMODE)
    {
        echo date("Y-m-d H:i:s").' -> TestResurrectConnection '.PHP_EOL;
    }

    //If connection does not exist...
    if (!isset($GLOBALS['DB'][$ConnectionIndex]))
    {
        //and no fallback cache exist, return error
        if (MIL_ACPU === FALSE)
        {
            $ErrorMessage = 'No connection registered by index: '.$ConnectionIndex;
            ErrorLog($ErrorMessage, E_USER_ERROR);

            return FALSE;
        }
        else
        {
            //But if it does, recreate globals from cache
            $ConnectionCache = apcu_fetch('DB');
            if ($ConnectionCache === FALSE)
            {
                //Error when recreating globals
                $ErrorMessage = 'No connection info available on ACPU';
                ErrorLog($ErrorMessage, E_USER_ERROR);

                return FALSE;
            }
            else
            {
                $GLOBALS['DB'] = $ConnectionCache;
            }
        }
    }

    //Pooled connection
    if (is_int(MIL_POOLEDCONNECTIONS))
    {
        echo 'Globals[db]:';
        print_r($GLOBALS['DB']);
        //In any other case, we must reconnect -yes, including persistent connections
        //Shouldn't give any problems as the connection has been checked before
        echo '<p>Pooled connection, but no live connection found</p>';
        $ConnectionToUse = Reconnect($ConnectionIndex);
    }
    else
    {
        //In any other case, we must reconnect -yes, including persistent connections
        //Shouldn't give any problems as the connection has been checked before
        //$ConnectionToUse = Reconnect($ConnectionIndex);
        //Conexion desde $GLOBALS funciona
        //$ConnectionToUse = $GLOBALS['ConnLink'][0];
        //A ver desde APCU
        $ConnectionToUse = apcu_fetch('ConnLink');
    }

    /*If it is kept, just use it
    if ($GLOBALS['DB'][$ConnectionIndex]['KeepOpen'] === TRUE)
    {
        //But first, check if it is alive
        if (is_int(MIL_POOLEDCONNECTIONS))
        {
            //Pooled connection
            echo 'Globals[db]:';
            print_r($GLOBALS['DB']);
            if (property_exists($GLOBALS['DB'][$ConnectionIndex]['ConnectionLink'], "thread_id"))
            {
                $ConnectionToUse = $GLOBALS['DB'][$ConnectionIndex]['ConnectionLink'];
            }
        }
        else
        {
            //Single connection
            if (property_exists($GLOBALS['DB'][$ConnectionIndex]['ConnectionLink'], "thread_id"))
            {
                $ConnectionToUse = $GLOBALS['DB'][$ConnectionIndex]['ConnectionLink'];
            }
        }
    }
    else
    {
        //In any other case, we must reconnect -yes, including persistent connections
        //Shouldn't give any problems as the connection has been checked before
        $ConnectionToUse = Reconnect($ConnectionIndex);
    }
*/
    return $ConnectionToUse;
}

/**
 * Truncate empties a table
 * @param   array   $ConnectionIndex the connection to use
 * @param   string  $TableName the table to empty
 * @return  mixed   The affected row or FALSE if error
 * @since   0.0.7
 * @see     
 * @todo 
 */
function Truncate($ConnectionIndex, $TableName)
{
    if (DEBUGMODE)
    {
        echo date("Y-m-d H:i:s").' -> Truncate '.PHP_EOL;
    }

    $SoundConnection = TestConnection($ConnectionIndex);
    //If there are connection problems, return FALSE
    if ($SoundConnection === FALSE)
    {
        $ErrorMessage = 'Unable to make use of connection registered by index: '.$ConnectionIndex;
        ErrorLog($ErrorMessage, E_USER_ERROR);

        return FALSE;
    }

    $InData = $GLOBALS['DB'][$ConnectionIndex];

    //ValidateQuery
    if (IsValidMySQLName($TableName) === FALSE)
    {
        $ErrorMessage = 'Invalid MySQL Object name: '.$TableName;
        ErrorLog($ErrorMessage, E_USER_ERROR);

        return FALSE;
    }
    $Query = "TRUNCATE ".$TableName.';';

    //Init connection
    $OutData = array();
    $ConnectionLink = MySQLInit($OutData);
    if (!$ConnectionLink)
    {
        $ErrorMessage = 'MySQLInit failure';
        ErrorLog($ErrorMessage, E_USER_ERROR);

        return FALSE;
    }

    //Establish options registered
    if (MySQLOptions($InData, $OutData, $ConnectionLink) === FALSE)
    {
        $OutData['Success'] = TRUE;
        $OutData['ReturnValue'] = 'MySQLOptions test failed';

        return FALSE;
    }

    //Connect
    if (MySQLRealConnect($InData, $OutData, $ConnectionLink) === FALSE)
    {
        $OutData['Success'] = TRUE;
        $OutData['ReturnValue'] = 'MySQLRealConnect test failed with code '.mysqli_errno($ConnectionLink).': '.mysqli_error($ConnectionLink);

        return FALSE;
    }

    //Now update
    if (mysqli_real_query($ConnectionLink, $Query) === FALSE)
    {
        $ErrorMessage = 'Update error with code '.mysqli_errno($ConnectionLink).': '.mysqli_error($ConnectionLink);
        ErrorLog($ErrorMessage, E_USER_ERROR);

        return FALSE;
    }

    $ResultSet['AffectedRows'] = mysqli_affected_rows($ConnectionLink);

    //Close connection
    if (mysqli_close($ConnectionLink) === FALSE)
    {
        $ErrorMessage = 'Error closing connection with code '.mysqli_errno($ConnectionLink).': '.mysqli_error($ConnectionLink);
        ErrorLog($ErrorMessage, E_USER_ERROR);

        return FALSE;
    }

    //Return values
    if ($ResultSet['AffectedRows'] === 0)
    {
        return NULL;
    }

    return $ResultSet;
}

/**
 * InsertFromMySQlAssocDataStructure fills a table with data from an MySQL ASSOC style array
 * Sample ASSOC MySQl Structure
 * array(3) {
 *   ["Columns"]=>
 *   int(4)
 *   ["Rows"]=>
 *   int(3)
 *   ["Data"]=>
 *   array(3) {
 *     [0]=>
 *     array(4) {
 *       ["actor_id"]=>
 *       string(1) "1"
 *       ["first_name"]=>
 *       string(8) "PENELOPE"
 *       ["last_name"]=>
 *       string(7) "GUINESS"
 *       ["last_update"]=>
 *       string(19) "2006-02-15 04:34:33"
 *     }
 *     [1]=>
 *     array(4) {
 *       ["actor_id"]=>
 *       string(1) "2"
 *       ["first_name"]=>
 *       string(4) "NICK"
 *       ["last_name"]=>
 *       string(8) "WAHLBERG"
 *       ["last_update"]=>
 *       string(19) "2006-02-15 04:34:33"
 *     }
 *   }
 * }
 * 
 * @param array     $Data               The data to insert
 * @param string    $TableName          The table to insert into
 * @param array     $ConnectionIndex    The connection to use
 * @return boolean  TRUE on success, FALSE on failure
 * @since   0.0.7
 * @see     
 * @todo 
 */
function InsertFromMySQlAssocDataStructure($Data, $TableName, $ConnectionIndex)
{
    if (DEBUGMODE)
    {
        echo date("Y-m-d H:i:s").' -> InsertFromMySQlAssocDataStructure '.PHP_EOL;
    }

    //Might not be a MySQLAssoc Data Structure if we are called directly
    if (!isset($Data['Data']))
    {
        return FALSE;
    }
    //print_r($Data);
    $QueryMain = "INSERT INTO ".$TableName." (";
    $QueryValues = "(";
    $FirstTimePassed = FALSE;
    foreach ($Data['Data'] as $Key => $Value)
    {
        foreach ($Value AS $ColumnName => $ColumnValue)
        {
            if ($FirstTimePassed === FALSE)
            {
                $QueryMain .= $ColumnName.", ";
            }
            $QueryValues .= MySQLProperQuote($ColumnValue, $Data['Metadata'][$ColumnName]['TypeDesc']).", ";
            //echo $QueryValues.PHP_EOL;
            /*
             * Now we proper scape
            if (is_string($ColumnValue))
            {
                $QueryValues .= "'".$ColumnValue."', ";
            }
            if (is_numeric($ColumnValue))
            {
                $QueryValues .= $ColumnValue.", ";
            }
            if (is_null($ColumnValue))
            {
                $QueryValues .= "NULL, ";
            }
             */
        }
        //Second time we do not need the column names
        if ($FirstTimePassed === FALSE)
        {
            $QueryMain = CloseCommaDelimitedList($QueryMain, ")");
            $FirstTimePassed = TRUE;
        }
        //We close the values list and start a new one
        $QueryValues = CloseCommaDelimitedList($QueryValues, ")").", (";
    }
    //Take out the last ", ("
    $FinalQuery = $QueryMain." VALUES ".substr($QueryValues, 0, strlen($QueryValues)-3).";";
    //echo 'Insert Query: '.$FinalQuery.PHP_EOL;
    $Resultado = Insert($ConnectionIndex, $FinalQuery);
    if ($Resultado === FALSE)
    {
        return FALSE;
    }

    return TRUE;
}

/**
 * MergeFromMySQlAssocDataStructure merges the data form a MySQL ASSOC style array into a Table
 * First key MUST be the Primary/Lookup Key
 * @param array     $Data               The data to insert
 * @param string    $TableName          The table to insert into
 * @param array     $ConnectionIndex    The connection to use
 * @return boolean  TRUE on success, FALSE on failure
 * @since   0.0.7
 * @see     https://dev.mysql.com/doc/refman/8.0/en/insert-on-duplicate.html
 * @todo convert to metadata-aware. First debug the quey building
 */
function MergeFromMySQlAssocDataStructure($Data, $TableName, $ConnectionIndex)
{
    if (DEBUGMODE)
    {
        echo date("Y-m-d H:i:s").' -> MergeFromMySQlAssocDataStructure '.PHP_EOL;
    }

    //Might not be a MySQLAssoc Data Structure if we are called directly
    if (!isset($Data['Data']))
    {
        return FALSE;
    }
    print_r($Data);
    //Here we build and run the sentence over and over
    $QueryMain = "INSERT INTO ".$TableName." (";
    $QueryValues = "(";
    $IAmFirstPKColumn = TRUE;
    $UpdateColumns = "";
    foreach ($Data['Data'] as $Key => $Value)
    {
        foreach ($Value AS $ColumnName => $ColumnValue)
        {
            $QueryMain .= $ColumnName.", ";
            //echo $Key.'=>'.$ColumnName.'=>'.$ColumnValue.'==>'.$QueryMain.PHP_EOL;
            /*if (is_string($ColumnValue))
            {
                $QueryValues .= "'".$ColumnValue."', ";
            }
            if (is_numeric($ColumnValue))
            {
                $QueryValues .= $ColumnValue."', ";
            }
            if (is_null($ColumnValue))
            {
                $QueryValues .= "NULL, ";
            }*/
            $QueryValues .= MySQLProperQuote($ColumnValue, $Data['Metadata'][$ColumnName]['TypeDesc']).", ";
            if ($IAmFirstPKColumn === FALSE)
            {
                $UpdateColumns .= $ColumnName.' = '.MySQLProperQuote($ColumnValue, $Data['Metadata'][$ColumnName]['TypeDesc']).", ";
            }
            else
            {
                //I am the PK column. Unset the flag
                $IAmFirstPKColumn = FALSE;
            }
        }
        //End and run the sentence
        $QueryMain = CloseCommaDelimitedList($QueryMain, ")");
        //We close the values list and potentially updated columns list
        $QueryValues = CloseCommaDelimitedList($QueryValues, ")");
        $UpdateColumns = CloseCommaDelimitedList($UpdateColumns, "");
        $FinalQuery = $QueryMain." VALUES ".$QueryValues."  ON DUPLICATE KEY UPDATE ".$UpdateColumns.";";
        //Run it!!
        $Resultado = Merge($ConnectionIndex, $FinalQuery);
        if ($Resultado === FALSE)
        {
            return FALSE;
        }
        //Reset variables
        $QueryMain = "INSERT INTO ".$TableName." (";
        $QueryValues = "(";
        $IAmFirstPKColumn = TRUE;
        $UpdateColumns = "";
    } //End for each record

    return TRUE;
}
