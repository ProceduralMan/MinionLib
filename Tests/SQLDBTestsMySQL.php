<?php

/*
 * SQLDBTestMySQL
 * To test Common Database Primitives on MySQL
 * @author ProceduralMan <proceduralman@gmail.com>
 * @copyright 2021-2022
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
echo 'Simple, standard connection to sakila standard MySQL Test Database. Just compulsory parameters'.PHP_EOL;
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
echo PHP_EOL.PHP_EOL.'Connection using some server options'.PHP_EOL;
$Persistent = FALSE;
$ConnectionTimeout = 60;
$CommandTimeout = 30;
$UseLocalInfile = NULL;
$InitCommand = NULL;
$Charset = 'utf8mb4';
$Index1 = RegisterMySQLConnection($ServerName, $Database, $DBUser, $DBPassword, $Persistent, $ConnectionTimeout,
        $CommandTimeout, $UseLocalInfile, $InitCommand, $Charset);
if ($Index1 === FALSE)
{
    echo 'MySQL with some server options connection failed to register'.PHP_EOL;
}
else
{
    echo 'MySQL with some server options connection registered successfully with index '.$Index1.PHP_EOL;
}

//Connection using optionsfile
echo PHP_EOL.PHP_EOL.'Connection using optionsfile'.PHP_EOL;
$OptionsFile = __DIR__.'/dummy-my.cnf';
$DefaultGroup = NULL;
$ServerPublicKey = NULL;
$Index2 = RegisterMySQLConnection($ServerName, $Database, $DBUser, $DBPassword, $Persistent, $ConnectionTimeout,
        $CommandTimeout, $UseLocalInfile, $InitCommand, $Charset, $OptionsFile, $DefaultGroup, $ServerPublicKey);
if ($Index2 === FALSE)
{
    echo 'MySQL using optionsfile connection failed to register'.PHP_EOL;
}
else
{
    echo 'MySQL using optionsfile connection registered successfully with index '.$Index2.PHP_EOL;
}


//Connection using all options except socket
echo PHP_EOL.PHP_EOL.'Connection using all options except socket'.PHP_EOL;
$ServerName = '127.0.0.1';
$CompressionProtocol = TRUE;
$FoundRows = TRUE;
$IgnoreSpaces = TRUE;
$InteractiveClient = TRUE;
$UseSSL = TRUE;
$DoNotVerifyServerCert = TRUE;
$Port = 3306;

$Index4 = RegisterMySQLConnection($ServerName, $Database, $DBUser, $DBPassword, $Persistent, $ConnectionTimeout,
        $CommandTimeout, $UseLocalInfile, $InitCommand, $Charset, $OptionsFile, $DefaultGroup, $ServerPublicKey, $CompressionProtocol,
        $FoundRows, $IgnoreSpaces, $InteractiveClient, $UseSSL, $DoNotVerifyServerCert, $Port);
if ($Index4 === FALSE)
{
    echo 'MySQL Port options connection failed to register'.PHP_EOL;
}
else
{
    echo 'MySQL Port options connection registered successfully with index '.$Index4.PHP_EOL;
    //var_dump($GLOBALS['DB'][$Index4]['ConnectionLink']);
}

//Connection using all options except port
echo PHP_EOL.PHP_EOL.'Connection using all options except port'.PHP_EOL;
$ServerName = 'localhost';
$CompressionProtocol = TRUE;
$FoundRows = TRUE;
$IgnoreSpaces = TRUE;
$InteractiveClient = TRUE;
$UseSSL = FALSE; //Because over a socket there is no SSL
$DoNotVerifyServerCert = TRUE;
$Port = NULL;
//Standard socket location on Ubuntu systems... will surely change on other distributions
$Socket = '/var/run/mysqld/mysqld.sock';

$Index5 = RegisterMySQLConnection($ServerName, $Database, $DBUser, $DBPassword, $Persistent, $ConnectionTimeout,
        $CommandTimeout, $UseLocalInfile, $InitCommand, $Charset, $OptionsFile, $DefaultGroup, $ServerPublicKey, $CompressionProtocol,
        $FoundRows, $IgnoreSpaces, $InteractiveClient, $UseSSL, $DoNotVerifyServerCert, $Port, $Socket);
if ($Index5 === FALSE)
{
    echo 'MySQL Socket options connection failed to register'.PHP_EOL;
}
else
{
    echo 'MySQL Socket options connection registered successfully with index '.$Index5.PHP_EOL;
}

//Persistent socket connection
echo PHP_EOL.PHP_EOL.'Persistent socket connection'.PHP_EOL;
$Persistent = TRUE;
$Index6 = RegisterMySQLConnection($ServerName, $Database, $DBUser, $DBPassword, $Persistent, $ConnectionTimeout,
        $CommandTimeout, $UseLocalInfile, $InitCommand, $Charset, $OptionsFile, $DefaultGroup, $ServerPublicKey, $CompressionProtocol,
        $FoundRows, $IgnoreSpaces, $InteractiveClient, $UseSSL, $DoNotVerifyServerCert, $Port, $Socket);
if ($Index6 === FALSE)
{
    echo 'MySQL persistent socket options connection failed to register'.PHP_EOL;
}
else
{
    echo 'MySQL persistent socket options connection registered successfully with index '.$Index6.PHP_EOL;
}

//read from simple connection
$Query1 = "SELECT * FROM actor LIMIT 3";
$Result1 = Read($Index6, $Query1, "OBJECT");
if ($Result1 === FALSE)
{
    echo "Some error happended while OBJECT reading".PHP_EOL;
}
elseif ($Result1 === NULL)
{
    echo "OBJECT reading gave no data rows".PHP_EOL;
}
else
{
    echo "OBJECT reading gave ".$Result1['Rows']." rows".PHP_EOL;
}

$Result2 = Read($Index6, $Query1, "ARRAY");
if ($Result2 === FALSE)
{
    echo "Some error happended while ARRAY reading".PHP_EOL;
}
elseif ($Result2 === NULL)
{
    echo "ARRAY reading gave no data rows".PHP_EOL;
}
else
{
    echo "ARRAY reading gave ".$Result2['Rows']." rows".PHP_EOL;
}

$Result3 = Read($Index6, $Query1, "ASSOC");
if ($Result3 === FALSE)
{
    echo "Some error happended while ASSOC reading".PHP_EOL;
}
elseif ($Result3 === NULL)
{
    echo "ASSOC reading gave no data rows".PHP_EOL;
}
else
{
    echo "ASSOC reading gave ".$Result3['Rows']." rows".PHP_EOL;
}

$Result4 = Read($Index6, $Query1, "BOTH");
if ($Result4 === FALSE)
{
    echo "Some error happended while BOTH reading".PHP_EOL;
}
elseif ($Result4 === NULL)
{
    echo "BOTH reading gave no data rows".PHP_EOL;
}
else
{
    echo "BOTH reading gave ".$Result4['Rows']." rows".PHP_EOL;
}

$Result5 = Read($Index6, $Query1, "JSON");
if ($Result5 === FALSE)
{
    echo "Some error happended while JSON reading".PHP_EOL;
}
elseif ($Result5 === NULL)
{
    echo "JSON reading gave no data rows".PHP_EOL;
}
else
{
    echo "JSON reading gave ".$Result5['Rows']." rows".PHP_EOL;
}

$Query2 = "UPDATE actor SET last_name = 'GUINESS' WHERE actor_id = 1;";
$Result6 = Update($Index6, $Query2);
if ($Result6 === FALSE)
{
    echo "Some error happended updating table".PHP_EOL;
}
elseif ($Result6 === NULL)
{
    echo "No affected rows on UPDATE".PHP_EOL;
}
else
{
    echo "UPDATING gave ".$Result6['AffectedRows']." rows".PHP_EOL;
}

$Query3 = "INSERT INTO actor (first_name, last_name, last_update) VALUES ('DUMMY', 'DUMMY', NOW());";
$Result7 = Insert($Index0, $Query3);
if ($Result7 === FALSE)
{
    echo "Some error happended inserting into table".PHP_EOL;
}
elseif ($Result7 === NULL)
{
    echo "No affected rows on INSERT".PHP_EOL;
}
else
{
    echo "INSERTING gave ".$Result7['AffectedRows']." rows".PHP_EOL;
}

$Query4 = "DELETE FROM actor WHERE first_name = 'DUMMY' AND last_name = 'DUMMY';";
$Result8 = Delete($Index0, $Query4);
if ($Result8 === FALSE)
{
    echo "Some error happended inserting into table".PHP_EOL;
}
elseif ($Result8 === NULL)
{
    echo "No affected rows on DELETE".PHP_EOL;
}
else
{
    echo "DELETING gave ".$Result8['AffectedRows']." rows".PHP_EOL;
}
