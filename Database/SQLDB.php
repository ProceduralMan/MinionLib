<?php

/*
 * SQLDB
 * SQL Database Management the Minion way
 *
 * @author ProceduralMan <proceduralman@gmail.com>
 * @copyright 2021
 * @version 1.0 initial version
 * @package Minion
 * @todo INCLUIR TODAS LAS BASES DE DATOS DE FICHERO TIPO BERKELEYDB https://www.php.net/manual/es/intro.dba.php
 * @todo La de base de datos debiera exponer solo cuatro funciones, Lee(Devuelve array asociativo), Actualiza(T/F), Inserta(T/F), InsertaConID(Devuelve ID)=> esta debiera incluir el SELECT SCOPE_IDENTITY();, chequear que fuera INSERT... etc etc. Tal vez tb un ¿tienefilas?
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
 * Front-end function to register a connection to MySQL
 * @param string    $ServerName             The hostname
 * @param string    $Database               The database name
 * @param string    $DBUser                 The user
 * @param string    $DBPassword             The password
 * @param boolean   $KeepOpen               Keep the connection link open on the registry
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
function RegisterMySQLConnection(
    $ServerName,
    $Database,
    $DBUser,
    $DBPassword,
    $KeepOpen = FALSE,
    $Persistent = FALSE,
    $ConnectionTimeout = NULL,
    $CommandTimeout = NULL,
    $UseLocalInfile = NULL,
    $InitCommand = NULL,
    $Charset = NULL,
    $OptionsFile = NULL,
    $DefaultGroup = NULL,
    $ServerPublicKey = NULL,
    $CompressionProtocol = NULL,
    $FoundRows = NULL,
    $IgnoreSpaces = NULL,
    $InteractiveClient = NULL,
    $UseSSL = NULL,
    $DoNotVerifyServerCert = NULL,
    $Port = NULL,
    $Socket = NULL
) {
    if (DEBUGMODE)
    {
        echo date("Y-m-d H:i:s").' -> RegisterMySQLConnection '.PHP_EOL;
    }

    //If persistent, cannot be kept-open -as it already is by the system-
    if ($Persistent == TRUE)
    {
        $KeepOpen = FALSE;
    }

    //Prepare compulsory Data
    $InData['System'] = MIL_MYSQL;
    $InData['ServerName'] = $ServerName;
    $InData['Database'] = $Database;
    $InData['DBUser'] = $DBUser;
    $InData['DBPassword'] = $DBPassword;
    $InData['KeepOpen'] = $KeepOpen;
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
        if ($KeepOpen === TRUE)
        {
            $InData['ConnectionLink'] = $OutData['ConnectionLink'];
        }
        $RegisterResult = RegisterDBSystem($InData, $OutData);
        if ($RegisterResult === FALSE)
        {
            $ErrorMessage = 'Error registering MySQL connection: '.$OutData['ReturnValue'];
            echo $ErrorMessage.PHP_EOL;
            ErrorLog($ErrorMessage, E_USER_ERROR);

            return FALSE;
        }
    }

    return $RegisterResult;
}

/**
 * DBSystemSanityCheck checks that all needed configuration is in place
 * @param array $InData
 *                      'System'                The chosen DB System: CUBRID, DBASE, FIREBIRD, DB2(Including CLOUDSCAPE and DERBY), MYSQL, ORACLE, POSTGRE, SQLITE or SQLSRV
 *                      'ServerName'            The chosen server. 'localhost' or nothing for SQLLite
 *                      'Database'              The Database
 *                      'DBUser'                The User
 *                      'DBPassword'            The Password
 *                      'KeepOpen'              Whether to keep the connection open on the registry or not
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
                        case 'KeepOpen':
                            $TestOption = is_bool($Value);
                            if ($TestOption === FALSE)
                            {
                                $AllGood = $TestOption;
                                $InvalidParameters .= 'KeepOpen:'.var_dump($Value).', ';
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
            if ($InData['KeepOpen'] === FALSE)
            {
                if (mysqli_close($ConnectionLink) === FALSE)
                {
                    $OutData['Success'] = TRUE;
                    $OutData['ReturnValue'] = 'Error closing connection with code '.mysqli_errno($ConnectionLink).': '.mysqli_error($ConnectionLink);

                    return FALSE;
                }
            }

            $OutData['Success'] = TRUE;
            $OutData['ReturnValue'] = TRUE;

            if ($InData['KeepOpen'] === TRUE)
            {
                $OutData['ConnectionLink'] = $ConnectionLink;
            }

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
 * It indexes the connection under the database name
 * @param  string  $System          The chosen DB System: CUBRID, DBASE, FIREBIRD, DB2(Including CLOUDSCAPE and DERBY), MYSQL, ORACLE, POSTGRE, SQLITE or
 *                                      SQLSRV
 * @param  string  $ServerName      The chosen server. 'localhost' or nothing for SQLite
 * @param  string  $Database        The Database. Filename path or :memory: if using in-memory db for SQLite
 * @param  string  $DBUser          The User
 * @param  string  $DBPassword      The Password
 * @param  array   $Options         System-specific options
 *                                      MySQL
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
 *                                      SQLSRV
 *                                          'WarningsReturnAsErrors'    By default, the SQLSRV driver treats warnings as errors; To disable this behavior,
 *                                                                          set a 0 here. To enable it, set a 1
 *                                          'LogSubsystems'             What is logged to PHP System Log
 *                                              SQLSRV_LOG_SYSTEM_OFF (0)   Turns logging off.
 *                                              SQLSRV_LOG_SYSTEM_INIT (1)  Turns on logging of initialization activity
 *                                              SQLSRV_LOG_SYSTEM_CONN (2)  Turns on logging of connection activity.
 *                                              SQLSRV_LOG_SYSTEM_STMT (4)  Turns on logging of statement activity.
 *                                              SQLSRV_LOG_SYSTEM_UTIL (8)  Turns on logging of error functions activity (such as handle_error and
 *                                                                              handle_warning).
 *                                              SQLSRV_LOG_SYSTEM_ALL (-1)  Turns on logging of all subsystems.
 *                                          'LogSeverity'               Which errorlevel to log
 *                                              SQLSRV_LOG_SEVERITY_ERROR (1)   Specifies that errors are logged. This is the default.
 *                                              SQLSRV_LOG_SEVERITY_WARNING (2) Specifies that warnings are logged.
 *                                              SQLSRV_LOG_SEVERITY_NOTICE (4)  Specifies that notices are logged.
 *                                              SQLSRV_LOG_SEVERITY_ALL (-1)    Specifies that errors, warnings, and notices are logged.
 *                                         'AccessToken'                The Azure AD Access Token extracted from an OAuth JSON response. The connection
 *                                                                          string must not contain user ID, password, or the Authentication keyword
 *                                                                          (requires ODBC Driver version 17 or above in Linux or macOS). Mutually exclusive
 *                                                                          with 'Authentication'
 *                                         'Authentication'             Authentication mode determined by other keywords
 *                                              SqlPassword                     Directly authenticate to a SQL Server instance (which may be an Azure instance)
 *                                                                                  using a username and password. The username and password must be passed into
 *                                                                                  the connection string using the UID and PWD keywords.
 *                                              ActiveDirectoryPassword         Authenticate with an Azure Active Directory identity using a username and
 *                                                                                  password. The username and password must be passed into the connection
 *                                                                                  string using the UID and PWD keywords.
 *                                              ActiveDirectoryMsi              Authenticate using either a system-assigned managed identity or a user-assigned
 *                                                                                  managed identity (requires ODBC Driver version 17.3.1.1 or above)
 *                                              ActiveDirectoryServicePrincipal Authenticate using service principal objects (requires ODBC Driver version 17.7
 *                                                                                  or above)
 *                                         'UID'                        Specifies the User ID to be used when connecting with SQL Server Authentication (duplicado de $DBUser)
 *                                         'PWD'                        Specifies the password associated with the User ID to be used when connecting with SQL
 *                                                                          Server Authentication.
 *                                         'ApplicationIntent'          Declares the application workload type when connecting to a server. Possible values are
 *                                                                          ReadOnly and ReadWrite. Default value ReadWrite
 *                                         'AttachDBFileName'           Specifies which database file the server should attach.
 *                                         'CharacterSet'               Specifies the character set used to send data to the server. Possible values are
 *                                                                          SQLSRV_ENC_CHAR and UTF-8. Default value SQLSRV_ENC_CHAR
 *                                         'ColumnEncryption'           Specifies whether the Always Encrypted feature is enabled or not. Possible values are
 *                                                                          Enabled and Disabled. Default value Disabled
 *                                         'ConnectionPooling'          Specifies whether the connection is assigned from a connection pool (1 or true) or not
 *                                                                          (0 or false). Default value true (1). Has no effect on Linux and MacOs where should
 *                                                                          be set via odbcinst.ini
 *                                         'ConnectRetryCount'          The maximum number of attempts to reestablish a broken connection before giving up. By
 *                                                                          default, a single attempt is made to reestablish a connection when broken. A value
 *                                                                          of 0 means that no reconnection will be attempted. Values: 0-255. Default value 1
 *                                         'ConnectRetryInterval'       The time, in seconds, between attempts to reestablish a connection. The application will
 *                                                                          attempt to reconnect immediately upon detecting a broken connection, and will then
 *                                                                          wait ConnectRetryInterval seconds before trying again. This keyword is ignored if
 *                                                                          ConnectRetryCount is equal to 0. Values 0-60. Default value 10
 *                                         'Database'                   Specifies the name of the database in use for the connection being established. (duplicado de $Database)
 *                                         'FormatDecimals'             Specifies whether to add leading zeroes to decimal strings when appropriate and enables
 *                                                                          the DecimalPlaces option for formatting money types (1 or true). If left false (0),
 *                                                                          the default behavior of returning exact precision and omitting leading zeroes for
 *                                                                          values less than 1 is used. Default value false (0)
 *                                         'DecimalPlaces'              Specifies the decimal places when formatting fetched money values. Integer between 0 and
 *                                                                          4 (inclusive). This option works only when FormatDecimals is true. Any negative
 *                                                                          integer or value more than 4 will be ignored.
 *                                         'Driver'                     Specifies the Microsoft ODBC driver used to communicate with SQL Server. Windows only
 *                                              ODBC Driver 11 for SQL Server
 *                                              ODBC Driver 13 for SQL Server
 *                                              ODBC Driver 17 for SQL Server
 *                                         'Encrypt'                    Specifies whether the communication with SQL Server is encrypted (1 or true) or
 *                                                                          unencrypted (0 or false). Default value false (0)
 *                                         'Failover_Partner'           Windows only. Specifies the server and instance of the database's mirror (if enabled and
 *                                                                          configured) to use when the primary server is unavailable.
 *                                         'KeyStoreAuthentication'     Authentication method for accessing Azure Key Vault. Controls what kind of credentials
 *                                                                          are used with KeyStorePrincipalId and KeyStoreSecret.
 *                                              KeyVaultPassword
 *                                              KeyVaultClientSecret
 *                                         'KeyStorePrincipalId'        Identifier for the account seeking to access Azure Key Vault. If KeyStoreAuthentication
 *                                                                          is KeyVaultPassword, this value must be an Azure Active Directory username. If
 *                                                                          KeyStoreAuthentication is KeyVaultClientSecret, this value must be an application
 *                                                                          client ID
 *                                         'KeyStoreSecret'             Credential secret for the account seeking to access Azure Key Vault. If
 *                                                                          KeyStoreAuthentication is KeyVaultPassword, this value must be an Azure Active
 *                                                                          Directory password. If KeyStoreAuthentication is KeyVaultClientSecret, this value
 *                                                                          must be an application client secret.
 *                                         'Language'                   Specifies the language of messages returned by the server. The available languages are
 *                                                                          listed in the sys.syslanguages table. Does not affect the language used by the
 *                                                                          drivers themselves, as they are currently available only in English, and it does not
 *                                                                          affect the language of the underlying ODBC driver, whose language is determined by
 *                                                                          the localized version installed on the client system. The default is the language
 *                                                                          set in SQL Server.
 *                                         'LoginTimeout'               Specifies the number of seconds to wait before failing the connection attempt. Default
 *                                                                          value no timeout.
 *                                         'MultipleActiveResultSets'   Disables -false (0)- or explicitly enables -true (1)- support for multiple active result
 *                                                                          sets (MARS). Default value true (1).
 *                                         'MultiSubnetFailover'        Support for AlwaysOn Availability Groups. Possible values are yes and no. Default value
 *                                                                          no. Always specify multiSubnetFailover=yes when connecting to the availability group
 *                                                                          listener of a SQL Server 2012 (11.x) availability group or a SQL Server 2012 (11.x)
 *                                                                          Failover Cluster Instance.
 *                                         'TransparentNetworkIPResolution' Affects the connection sequence when the first resolved IP of the hostname does not
 *                                                                          respond and there are multiple IPs associated with the hostname. Possible values
 *                                                                          enabled and disabled. It interacts with MultiSubnetFailover to provide different
 *                                                                          connection sequences.
 *                                         'QuotedId'                   Specifies whether to use SQL-92 rules for quoted identifiers (1 or true) or to use
 *                                                                          legacy Transact-SQL rules (0 or false). Default value true (1)
 *                                         'ReturnDatesAsStrings'       Retrieves date and time types (datetime, smalldatetime, date, time, datetime2, and
 *                                                                          datetimeoffset) as strings or as PHP types. 1 or true to return date and time types
 *                                                                          as strings. 0 or false to return date and time types as PHP DateTime types. Default
 *                                                                          value false (0).
 *                                         'Scrollable'                 Client-side (buffered) and server-side (unbuffered) cursors. Default value 'forward'
 *                                              SQLSRV_CURSOR_FORWARD           Lets you move one row at a time starting at the first row of the result set
 *                                                                                  until you reach the end of the result set. This is the default cursor type.
 *                                                                                  type. sqlsrv_num_rows returns an error for result sets created with this
 *                                                                                  cursor. 'forward' is the abbreviated form of SQLSRV_CURSOR_FORWARD
 *                                              SQLSRV_CURSOR_STATIC            Lets you access rows in any order but will not reflect changes in the database.
 *                                                                                  'static' is the abbreviated form of SQLSRV_CURSOR_STATIC
 *                                              SQLSRV_CURSOR_DYNAMIC           Lets you access rows in any order and will reflect changes in the database.
 *                                                                                  sqlsrv_num_rows returns an error for result sets created with this cursor
 *                                                                                  type. 'dynamic' is the abbreviated form of SQLSRV_CURSOR_DYNAMIC.
 *                                              SQLSRV_CURSOR_KEYSET            Lets you access rows in any order. However, a keyset cursor does not update the
 *                                                                                  row count if a row is deleted from the table (a deleted row is returned with
 *                                                                                  no values). keyset is the abbreviated form of SQLSRV_CURSOR_KEYSET.
 *                                              SQLSRV_CURSOR_CLIENT_BUFFERED   Lets you access rows in any order. Creates a client-side cursor query.
 *                                                                                  'buffered' is the abbreviated form of SQLSRV_CURSOR_CLIENT_BUFFERED.
 *                                         'TraceOn'                    Specifies whether ODBC tracing is enabled (1 or true) or disabled (0 or false) for the
 *                                                                          connection being established. Default value false (0)
 *                                         'TraceFile'                  Specifies the path for the file used for trace data.
 *                                         'APP'                        Specifies the application name used in tracing.
 *                                         'WSID'                       Specifies the name of the computer for tracing.
 *                                         'TransactionIsolation'       Specifies the transaction isolation level. DSefault value SQLSRV_TXN_READ_COMMITTED
 *                                              SQLSRV_TXN_READ_UNCOMMITTED     Specifies that statements can read rows that have been modified by other
 *                                                                                  transactions but not yet committed. Transactions running at the READ
 *                                                                                  UNCOMMITTED level do not issue shared locks to prevent other transactions
 *                                                                                  from modifying data read by the current transaction. It is possible to make
 *                                                                                  dirty reads.
 *                                              SQLSRV_TXN_READ_COMMITTED       Specifies that statements cannot read data that has been modified but not
 *                                                                                  committed by other transactions. This prevents dirty reads. Data can be
 *                                                                                  changed by other transactions between individual statements within the
 *                                                                                  current transaction, resulting in nonrepeatable reads or phantom data.
 *                                                                                  This option is the SQL Server default.
 *                                              SQLSRV_TXN_REPEATABLE_READ      Specifies that statements cannot read data that has been modified but not yet
 *                                                                                  committed by other transactions and that no other transactions can modify
 *                                                                                  data that has been read by the current transaction until the current
 *                                                                                  transaction completes. Shared locks are placed on all data read by each
 *                                                                                  statement in the transaction and are held until the transaction completes.
 *                                                                                  Use this option only when necessary.
 *                                              SQLSRV_TXN_SNAPSHOT             Specifies that data read by any statement in a transaction will be the
 *                                                                                  transactionally consistent version of the data that existed at the start of
 *                                                                                  the transaction. The transaction can only recognize data modifications that
 *                                                                                  were committed before the start of the transaction. Data modifications made
 *                                                                                  by other transactions after the start of the current transaction are not
 *                                                                                  visible to statements executing in the current transaction. The effect is as
 *                                                                                  if the statements in a transaction get a snapshot of the committed data as
 *                                                                                  it existed at the start of the transaction.
 *                                              SQLSRV_TXN_SERIALIZABLE         1)Statements cannot read data that has been modified but not yet committed by
 *                                                                                  other transactions. 2) No other transactions can modify data that has been
 *                                                                                  read by the current transaction until the current transaction completes. 3)
 *                                                                                  Other transactions cannot insert new rows with key values that would fall in
 *                                                                                  the range of keys read by any statements in the current transaction until
 *                                                                                  the current transaction completes. Range locks are placed in the range of
 *                                                                                  key values that match the search conditions of each statement executed in a
 *                                                                                  transaction. Because concurrency is lower, use this option only when
 *                                                                                  necessary.
 *                                         'TrustServerCertificate'     Specifies whether the client should trust (1 or true) or reject (0 or false) a
 *                                                                          self-signed server certificate. Default value false (0).
 *                                      SQLITE3
 *                                         'flags'                      How to open SQLite. Default value SQLITE3_OPEN_READWRITE|SQLITE3_OPEN_CREATE
 *                                              SQLITE3_OPEN_READONLY       Open in read-only mode
 *                                              SQLITE3_OPEN_READWRITE      Open in read-write mode
 *                                              SQLITE3_OPEN_CREATE         Create database if it does not exist
 * @param  int     $LoginTimeout           The Timeout
 * @param  boolean $Encrypt                Must the connection be encrypted?
 * @param  boolean $TrustServerCertificate Must the server certificate not be checked?
 * @param mixed $InData
 * @param mixed $OutData
 * @return int     The connection index
 * @since 0.0.3
 * @see
 * @todo new parameter might arise from yet unimplemented systems
 */
function RegisterDBSystem($InData, $OutData)
{
    if (DEBUGMODE)
    {
        echo date("Y-m-d H:i:s").' -> RegisterDBSystem '.PHP_EOL;
    }
    /*
    $InData['System'] = $System;
    $InData['ServerName'] = $ServerName;
    $InData['Database'] = $Database;
    $InData['DBUser'] = $DBUser;
    $InData['DBPassword'] = $DBPassword;
    $InData['Options'] = $Options;

    $OutData = array();
    $SanityCheck = DBSystemSanityCheck($InData, $OutData);
    if ($SanityCheck === FALSE)
    {
        echo 'Error registering logger. Review parameters. Message:'.$OutData['ReturnValue'].PHP_EOL;

        exit(1);
    }
*/
    //Register the Connection
    /*if (isset($GLOBALS['DB']))
    {
        $FreeCell = count($GLOBALS['DB']);
    }
    else
    {
        $FreeCell = 0;
    }*/
    //Make sure there are no collisions on the id
    //no need usleep(10);
    $ConnectionName = uniqid();
    $GLOBALS['DB'][$ConnectionName]['System'] = $InData['System'];
    $GLOBALS['DB'][$ConnectionName]['ServerName'] = $InData['ServerName'];
    $GLOBALS['DB'][$ConnectionName]['Database'] = $InData['Database'];
    $GLOBALS['DB'][$ConnectionName]['DBUser'] = $InData['DBUser'];
    $GLOBALS['DB'][$ConnectionName]['DBPassword'] = $InData['DBPassword'];
    $GLOBALS['DB'][$ConnectionName]['KeepOpen'] = $InData['KeepOpen'];
    if (isset($InData['ConnectionLink']))
    {
        $GLOBALS['DB'][$ConnectionName]['ConnectionLink'] = $InData['ConnectionLink'];
    }
    if (isset($InData['Options']))
    {
        $GLOBALS['DB'][$ConnectionName]['Options'] = $InData['Options'];
    }

    $OutData['Success'] = TRUE;
    $OutData['ReturnValue'] = $ConnectionName;

    return $ConnectionName;
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
 * Read 
 * gets info from the database in form of associative array or JSON
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
    $ConnectionToUse = TestResurrectConnection($ConnectionIndex);
    //If there are connection problems, return FALSE
    if ($ConnectionToUse === FALSE)
    {
        $ErrorMessage = 'Unable to make use of connection registered by index: '.$ConnectionIndex;
        ErrorLog($ErrorMessage, E_USER_ERROR);

        return FALSE;
    }

    //Now read
    $ResultSet = array();
    //fucks sentence $EscapedQuery = mysqli_real_escape_string($ConnectionToUse, $Query);
    if (mysqli_real_query($ConnectionToUse, $Query) === FALSE)
    {
        $ErrorMessage = 'Query error with code '.mysqli_errno($ConnectionToUse).': '.mysqli_error($ConnectionToUse);
        ErrorLog($ErrorMessage, E_USER_ERROR);

        return FALSE;
    }
    //Get Data
    $TheData = mysqli_store_result($ConnectionToUse);
    //var_dump($TheData);
    $ResultSet['Columns'] = mysqli_field_count($ConnectionToUse);
    $ResultSet['Rows'] = mysqli_num_rows($TheData);
    //Return NULL if there are no rows
    if ($ResultSet['Rows'] === 0)
    {
        return NULL;
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

    return $ResultSet;
}

/**
 * Update 
 * changes info on the database
 * @param   array   $ConnectionIndex    The connection heap
 * @param   string  $Query              The query
 * @return  mixed   Affected rows or NULL if affected rows are 0; FALSE if UPDATE fails
 * @since   0.0.3
 * @see     
 * @todo
 */
function Update($ConnectionIndex, $Query)
{
    $ConnectionToUse = TestResurrectConnection($ConnectionIndex);
    //If there are connection problems, return FALSE
    if ($ConnectionToUse === FALSE)
    {
        $ErrorMessage = 'Unable to make use of connection registered by index: '.$ConnectionIndex;
        ErrorLog($ErrorMessage, E_USER_ERROR);

        return FALSE;
    }

    //Now update
    //fucks sentence $EscapedQuery = mysqli_real_escape_string($ConnectionToUse, $Query);
    if (mysqli_real_query($ConnectionToUse, $Query) === FALSE)
    {
        $ErrorMessage = 'Update error with code '.mysqli_errno($ConnectionToUse).': '.mysqli_error($ConnectionToUse);
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
        $ErrorMessage = 'Insert error with code '.mysqli_errno($ConnectionToUse).': '.mysqli_error($ConnectionToUse);
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
 */
function Reconnect($ConnectionIndex)
{
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

    //Establish options registered
    $InData['System'] = $GLOBALS['DB'][$ConnectionIndex]['System'];
    $InData['ServerName'] = $GLOBALS['DB'][$ConnectionIndex]['ServerName'];
    $InData['Database'] = $GLOBALS['DB'][$ConnectionIndex]['Database'];
    $InData['DBUser'] = $GLOBALS['DB'][$ConnectionIndex]['DBUser'];
    $InData['DBPassword'] = $GLOBALS['DB'][$ConnectionIndex]['DBPassword'];
    $InData['KeepOpen'] = $GLOBALS['DB'][$ConnectionIndex]['KeepOpen'];
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
 * TestResurrectConnection
 * Checks connection health and reconnects if necessary
 * @param   string  $ConnectionIndex
 * @return  mixed   The connection to use or FALSE on error 
 */
function TestResurrectConnection($ConnectionIndex)
{
    //If connection does not exist, return error
    if (!isset($GLOBALS['DB'][$ConnectionIndex]))
    {
        $ErrorMessage = 'No connection registered by index: '.$ConnectionIndex;
        ErrorLog($ErrorMessage, E_USER_ERROR);

        return FALSE;
    }

    //If it is kept, just use it
    if ($GLOBALS['DB'][$ConnectionIndex]['KeepOpen'] === TRUE)
    {
        //But first, check if it is alive
        if (property_exists($GLOBALS['DB'][$ConnectionIndex]['ConnectionLink'], "thread_id"))
        {
            $ConnectionToUse = $GLOBALS['DB'][$ConnectionIndex]['ConnectionLink'];
        }
    }
    else
    {
        //In any other case, we must reconnect -yes, including persistent connections
        //Shouldn't give any problems as the copnnection has been checked before
        $ConnectionToUse = Reconnect($ConnectionIndex);
    }

    return $ConnectionToUse;
}
