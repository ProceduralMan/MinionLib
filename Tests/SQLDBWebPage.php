<?php

/*
 * SQLDBWebPage
 * To test Common Database Primitives over a webpage using APCU/REDIS
 * @author ProceduralMan <proceduralman@gmail.com>
 * @copyright 2021-2022
 * @version 1.0 initial version
 * @package Minion Library
 * @todo
 *  2022-03-03 17:28:17 -> CustomErrorProcessor->File: SQLDBWebPage.php Line: 50 Msg: Undefined variable: ServerName 
 * 2022-03-03 17:28:17 -> ProcessError LogRecord Array ( [ServerDate] => 2022-03-03 17:28:17 UTC [LocalDate] => 2022-03-03 18:28:17 CET [LogLevel] => 250 [EventText] => Undefined variable: ServerName ) ->exits OK 
 * 2022-03-03 17:28:17 -> NotifyError For no loggers, no notifications 
 * 2022-03-03 17:28:17 -> CustomErrorProcessor->File: SQLDBWebPage.php Line: 50 Msg: Undefined variable: Database 
 * 2022-03-03 17:28:17 -> ProcessError LogRecord Array ( [ServerDate] => 2022-03-03 17:28:17 UTC [LocalDate] => 2022-03-03 18:28:17 CET [LogLevel] => 250 [EventText] => Undefined variable: Database ) ->exits OK 
 * 2022-03-03 17:28:17 -> NotifyError For no loggers, no notifications 
 * 2022-03-03 17:28:17 -> CustomErrorProcessor->File: SQLDBWebPage.php Line: 50 Msg: Undefined variable: DBUser 
 * 2022-03-03 17:28:17 -> ProcessError LogRecord Array ( [ServerDate] => 2022-03-03 17:28:17 UTC [LocalDate] => 2022-03-03 18:28:17 CET [LogLevel] => 250 [EventText] => Undefined variable: DBUser ) ->exits OK 
 * 2022-03-03 17:28:17 -> NotifyError For no loggers, no notifications 
 * 2022-03-03 17:28:17 -> CustomErrorProcessor->File: SQLDBWebPage.php Line: 50 Msg: Undefined variable: DBPassword 
 * 2022-03-03 17:28:17 -> ProcessError LogRecord Array ( [ServerDate] => 2022-03-03 17:28:17 UTC [LocalDate] => 2022-03-03 18:28:17 CET [LogLevel] => 250 [EventText] => Undefined variable: DBPassword ) ->exits OK 
 * 2022-03-03 17:28:17 -> NotifyError For no loggers, no notifications 
 * 2022-03-03 17:28:17 -> RegisterMySQLConnection 
 * 2022-03-03 17:28:17 -> DBSystemSanityCheck Array ( [System] => MYSQL [ServerName] => [Database] => [DBUser] => [DBPassword] => [KeepOpen] => [Options] => Array ( [Persistent] => [ConnectionTimeout] => 60 [CommandTimeout] => 30 [Charset] => utf8mb4 ) ) 
 * 2022-03-03 17:28:17 -> Enclosure FC=, LC= 
 * 2022-03-03 17:28:17 -> Enclosure FC=, LC= 
 * 2022-03-03 17:28:17 -> Enclosure FC=, LC= Error checking MySQL connection: Invalid parameters->ServerName:, Database:, DBUser:, 
 * 2022-03-03 17:28:17 -> CustomErrorProcessor->File: ErrorLogging.php Line: 85 Msg: Error checking MySQL connection: Invalid parameters->ServerName:, Database:, DBUser:, 
 * 2022-03-03 17:28:17 -> ProcessError LogRecord Array ( [ServerDate] => 2022-03-03 17:28:17 UTC [LocalDate] => 2022-03-03 18:28:17 CET [LogLevel] => 400 [EventText] => Error checking MySQL connection: Invalid parameters->ServerName:, Database:, DBUser:, ) ->exits OK 
 * 2022-03-03 17:28:17 -> NotifyError For no loggers, no notifications

Da error al conectar, bien por que la conexion se serializa al guardarla en el array o bien al meterla en el ACPU.... hay que testarlo

 */
require_once __DIR__.'/../MinionSetup.php';

$ServerName = 'localhost';
$Database = 'sakila';
$DBUser = 'appuser';
$DBPassword = '82CX39t3gOnf2BHOxPmE';

?> 
<html>
	<body>
		<h1>Welcome to the 90's</h1>
		<p>Hi! I am a bare html webpage hastily set-up to test MinionLib Database primitives with APCU support</p>
		<p>Enjoy the ride!</p>
                <h2> Testing APCU</h2>
                <?php
                if (MIL_APCU !== TRUE)
                {
                    echo '<p>APCU not enabled</p>'.PHP_EOL;
                    echo '<p>End tests</p>'.PHP_EOL;
                }
                else
                {
                    echo '<p>APCU enabled</p>'.PHP_EOL;
                    $ExitoFetch = FALSE;
                    $Conexiones = apcu_fetch('DB', $ExitoFetch);
                    if ($ExitoFetch === TRUE)
                    {
                        //Hay ya registros
                        print_r($Conexiones);
                    }
                    else
                    {
                        echo '<p>Sin conexiones heredadas</p>'.PHP_EOL;
                    }
                    echo '<h2>Testing Minimal Server Options</h2>'.PHP_EOL;
                    $Index0 = RegisterMySQLConnection($ServerName, $Database, $DBUser, $DBPassword);
                    if ($Index0 === FALSE)
                    {
                        echo '<p>MySQL minimum connection failed to register<p>'.PHP_EOL;
                    }
                    else
                    {
                        echo '<p>MySQL minimum connection registered successfully with index '.$Index0.'<p>'.PHP_EOL;
                        apcu_clear_cache();
                    }
                    //Connection using some server options
                    echo '<h2>Testing w/ some Server Options</h2>'.PHP_EOL;
                    echo '<br>'.$ServerName.'<br><br>';
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
                        echo '<p>MySQL with some server options connection failed to register</p>'.PHP_EOL;
                    }
                    else
                    {
                        echo '<p>MySQL with some server options connection registered successfully with index '.$Index1.'</p>'.PHP_EOL;
                        apcu_clear_cache();
                    }
                    //Connection using optionsfile
                    echo '<h2>Testing using optionsfile</h2>'.PHP_EOL;
                    $OptionsFile = __DIR__.'/dummy-my.cnf';
                    $DefaultGroup = NULL;
                    $ServerPublicKey = NULL;
                    $Index2 = RegisterMySQLConnection($ServerName, $Database, $DBUser, $DBPassword, $Persistent, $ConnectionTimeout,
                            $CommandTimeout, $UseLocalInfile, $InitCommand, $Charset, $OptionsFile, $DefaultGroup, $ServerPublicKey);
                    if ($Index2 === FALSE)
                    {
                        echo '<p>MySQL using optionsfile connection failed to register</p>'.PHP_EOL;
                    }
                    else
                    {
                        echo '<p>MySQL using optionsfile connection registered successfully with index '.$Index2.'</p>'.PHP_EOL;
                        apcu_clear_cache();
                    }

                    //Keeped up connection using all options except socket
                    echo '<h2>Testing Keeped up connection using all server options except socket</h2>'.PHP_EOL;
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
                        echo '<p>MySQL Port options connection failed to register</p>'.PHP_EOL;
                    }
                    else
                    {
                        echo '<p>MySQL Port options connection registered successfully with index '.$Index4.'</p>'.PHP_EOL;
                        //var_dump($GLOBALS['DB'][$Index4]['ConnectionLink']);
                        apcu_clear_cache();
                    }

                    //Keeped up connection using all options except port
                    echo '<h2>Testing Keeped up connection using all server options except port</h2>'.PHP_EOL;
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
                        echo '<p>MySQL Socket options connection failed to register</p>'.PHP_EOL;
                    }
                    else
                    {
                        echo '<p>MySQL Socket options connection registered successfully with index '.$Index5.'</p>'.PHP_EOL;
                        //var_dump($GLOBALS['DB'][$Index5]['ConnectionLink']);
                        apcu_clear_cache();
                    }

                    //Persistent socket connection
                    echo '<h2>Testing persistent socket connection</h2>'.PHP_EOL;
                    $Persistent = TRUE;
                    $Socket = '/var/run/mysqld/mysqld.sock';
                    $Index6 = RegisterMySQLConnection($ServerName, $Database, $DBUser, $DBPassword, $Persistent, $ConnectionTimeout,
                            $CommandTimeout, $UseLocalInfile, $InitCommand, $Charset, $OptionsFile, $DefaultGroup, $ServerPublicKey, $CompressionProtocol,
                            $FoundRows, $IgnoreSpaces, $InteractiveClient, $UseSSL, $DoNotVerifyServerCert, $Port, $Socket);
                    if ($Index6 === FALSE)
                    {
                        echo '<p>MySQL persistent socket options connection failed to register</p>'.PHP_EOL;
                    }
                    else
                    {
                        echo '<p>MySQL persistent socket options connection registered successfully with index '.$Index6.'</p>'.PHP_EOL;
                        //apcu_clear_cache();
                    }
                    //sI oK, TEST APCU POR EL REGISTRO. SI FALLO, ACABAMOS

                    //Sync GLOBALS and APCU
                    $GLOBALS['DB'] = apcu_fetch('DB');
                }
                ?>
                <h2>ACPU connections registered</h2>
                <?php
                    $ExitoFetch = FALSE;
                    $Conexiones = apcu_fetch('DB', $ExitoFetch);
                    if ($ExitoFetch === TRUE)
                    {
                        //Hay ya registros
                        print_r($Conexiones);
                    }
                    else
                    {
                        echo '<p>Sin conexiones heredadas</p>'.PHP_EOL;
                    }
                ?>
                <h2>Object reading</h2>
                <?php
                //read from simple connection
                $Query1 = "SELECT * FROM actor LIMIT 3";
                $Result1 = Read($Index6, $Query1, "OBJECT");
                if ($Result1 === FALSE)
                {
                    echo "<p>Some error happended while OBJECT reading</p>".PHP_EOL;
                }
                elseif ($Result1 === NULL)
                {
                    echo "<p>OBJECT reading gave no data rows</p>".PHP_EOL;
                }
                else
                {
                    echo "<p>OBJECT reading gave ".$Result1['Rows']." rows</p>".PHP_EOL;
                }
                ?>
                <h2>Array reading</h2>
                <?php
                $Result2 = Read($Index6, $Query1, "ARRAY");
                if ($Result2 === FALSE)
                {
                    echo "<p>Some error happended while ARRAY reading</p>".PHP_EOL;
                }
                elseif ($Result2 === NULL)
                {
                    echo "<p>ARRAY reading gave no data rows</p>".PHP_EOL;
                }
                else
                {
                    echo "<p>ARRAY reading gave ".$Result2['Rows']." rows".PHP_EOL;
                }
                ?>
                <h2>Assoc reading</h2>
                <?php
                $Result3 = Read($Index6, $Query1, "ASSOC");
                if ($Result3 === FALSE)
                {
                    echo "<p>Some error happended while ASSOC reading</p>".PHP_EOL;
                }
                elseif ($Result3 === NULL)
                {
                    echo "<p>ASSOC reading gave no data rows</p>".PHP_EOL;
                }
                else
                {
                    echo "<p>ASSOC reading gave ".$Result3['Rows']." rows</p>".PHP_EOL;
                }
                ?>
                <h2>Both reading</h2>
                <?php
                $Result4 = Read($Index6, $Query1, "BOTH");
                if ($Result4 === FALSE)
                {
                    echo "<p>Some error happended while BOTH reading</p>".PHP_EOL;
                }
                elseif ($Result4 === NULL)
                {
                    echo "<p>BOTH reading gave no data rows</p>".PHP_EOL;
                }
                else
                {
                    echo "<p>BOTH reading gave ".$Result4['Rows']." rows</p>".PHP_EOL;
                }
                ?>
                <h2>JSON reading</h2>
                <?php
                $Result5 = Read($Index6, $Query1, "JSON");
                if ($Result5 === FALSE)
                {
                    echo "<p>Some error happended while JSON reading</p>".PHP_EOL;
                }
                elseif ($Result5 === NULL)
                {
                    echo "<p>JSON reading gave no data rows</p>".PHP_EOL;
                }
                else
                {
                    echo "<p>JSON reading gave ".$Result5['Rows']." rows</p>".PHP_EOL;
                }
                ?>
                <h2>UPDATE test</h2>
                <?php
                $Query2 = "UPDATE actor SET last_name = 'GUINESS' WHERE actor_id = 1;";
                $Result6 = Update($Index6, $Query2);
                if ($Result6 === FALSE)
                {
                    echo "<p>Some error happended updating table</p>".PHP_EOL;
                }
                elseif ($Result6 === NULL)
                {
                    echo "<p>No affected rows on UPDATE</p>".PHP_EOL;
                }
                else
                {
                    echo "<p>UPDATING gave ".$Result6['AffectedRows']." rows</p>".PHP_EOL;
                }
                ?>
                <h2>INSERT test</h2>
                <?php
                $Query3 = "INSERT INTO actor (first_name, last_name, last_update) VALUES ('DUMMY', 'DUMMY', NOW());";
                $Result7 = Insert($Index6, $Query3);
                if ($Result7 === FALSE)
                {
                    echo "<p>Some error happended inserting into table".PHP_EOL;
                }
                elseif ($Result7 === NULL)
                {
                    echo "<p>No affected rows on INSERT".PHP_EOL;
                }
                else
                {
                    echo "<p>INSERTING gave ".$Result7['AffectedRows']." rows".PHP_EOL;
                }
                ?>
                <h2>DELETE test</h2>
                <?php
                $Query4 = "DELETE FROM actor WHERE first_name = 'DUMMY' AND last_name = 'DUMMY';";
                $Result8 = Delete($Index6, $Query4);
                if ($Result8 === FALSE)
                {
                    echo "<p>Some error happended inserting into table</p>".PHP_EOL;
                }
                elseif ($Result8 === NULL)
                {
                    echo "<p>No affected rows on DELETE</p>".PHP_EOL;
                }
                else
                {
                    echo "<p>DELETING gave ".$Result8['AffectedRows']." rows</p>".PHP_EOL;
                }
                ?>
                <h2>High-level cache read, full table. Fisrt time it gets it from DB</h2>
                <?php
                    $TestData = ReadCache('TestData', $Index6, 'ActorTestTable');
                    echo "<p>Got ".count($TestData['Data'])." rows!</p>";
                ?>
                <h2>High-level cache read, full table. Second time it gets it from APCU</h2>
                <?php
                    $TestData2 = ReadCache('TestData', $Index6, 'ActorTestTable');
                    echo "<p>Got ".count($TestData2['Data'])." rows!</p>";
                ?>
                <h2>Now, with a persistable cache data</h2>
                <?php
                    $TestData3 = ReadCache('TestDataP', $Index6, 'ActorTestTable', TRUE, $TimeToLive, 'APCU');
                    echo "<p>Got ".count($TestData3['Data'])." rows!</p>";
                ?>
                <h2>Add a row to the array and force-persist the cache, updating (no FULL-REWRITE)</h2>
                <?php
                    $TestData3['Data'][6]['actor_id'] = 7;
                    $TestData3['Data'][6]['first_name'] = 'JOHNNY';
                    $TestData3['Data'][6]['last_name'] = 'ME LAVO';
                    $TestData3['Data'][6]['last_update'] = '2022-02-15 22:22:22';
                    $Result9 = MYSMAToAPCU($TestData3, 'TestDataP');
                    if (!$Result9)
                    {
                        echo '*** ERROR CACHEING***';
                    }
                    else
                    {
                        $Result10 = PersistCache('TestDataP', $Index6, 'ActorTestTable', FALSE);
                        if (!$Result10)
                        {
                            echo '*** ERROR PERSISTING CACHE***';
                        }
                    }
                    echo "<p>Got ".count($TestData3['Data'])." rows!</p>";
                ?>
	</body>
</html>