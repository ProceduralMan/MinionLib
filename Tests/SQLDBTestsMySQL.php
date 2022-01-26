<?php

/*
 * DataValidationTests
 * To test Data Validation helper functions 
 * @author ProceduralMan <proceduralman@gmail.com>
 * @copyright 2021
 * @version 1.0 initial version
 * @package Minion Library
 * @todo
 */

require_once __DIR__.'/../MinionSetup.php';

$ServerName = 'localhost';
$Database = 'sakila';
$DBUser = 'appuser';
$DBPassword = '82CX39t3gOnf2BHOxPmE';
//Simple, standard connection to sakila standard MySQL Test Database. Just compulsory parameters
$Index0 = RegisterMySQLConnection($ServerName, $Database, $DBUser, $DBPassword);
if ($Index0 === FALSE)
{
    echo 'MySQL minimum connection failed to register'.PHP_EOL;
}
else
{
    echo 'MySQL minimum connection registered successfully with index '.$Index0.PHP_EOL;
}

//Connection using some server options
$ConnectionTimeout = 60;
$CommandTimeout = 30;
$UseLocalInfile = NULL;
$InitCommand = NULL;
$Charset = 'utf8mb4';
$Index1 = RegisterMySQLConnection($ServerName, $Database, $DBUser, $DBPassword, $ConnectionTimeout, $CommandTimeout, $UseLocalInfile, $InitCommand, $Charset);
if ($Index1 === FALSE)
{
    echo 'MySQL options connection failed to register'.PHP_EOL;
}
else
{
    echo 'MySQL options connection registered successfully with index '.$Index1.PHP_EOL;
}

//Connection using all server options
$OptionsFile = NULL;
$DefaultGroup = NULL;
$ServerPublicKey = NULL;
$VerifySSL = FALSE;
$Index2 = RegisterMySQLConnection($ServerName, $Database, $DBUser, $DBPassword, $ConnectionTimeout, $CommandTimeout, $UseLocalInfile, $InitCommand, $Charset,
        $OptionsFile, $DefaultGroup, $ServerPublicKey, $VerifySSL);
if ($Index2 === FALSE)
{
    echo 'MySQL options connection failed to register'.PHP_EOL;
}
else
{
    echo 'MySQL options connection registered successfully with index '.$Index2.PHP_EOL;
}


/*
 * function RegisterMySQLConnection($ServerName, $Database, $DBUser, $DBPassword, $ConnectionTimeout = NULL, $CommandTimeout = NULL, $UseLocalInfile = NULL,
        $InitCommand = NULL, $Charset = NULL, $OptionsFile = NULL, $DefaultGroup = NULL, $ServerPublicKey = NULL, $VerifySSL = NULL,
        $CompressionProtocol = NULL, $FoundRows = NULL, $IgnoreSpaces = NULL, $InteractiveClient = NULL, $UseSSL = NULL, $DoNotVerifyServerCert = NULL,
        $Port = NULL, $Socket = NULL)

 */
