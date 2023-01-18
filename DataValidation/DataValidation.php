<?php

/*
 * DataValidation
 * Data Validation the Minion way
 *
 * @author ProceduralMan <proceduralman@gmail.com>
 * @copyright 2021
 * @version 1.0 initial version
 * @package Minion
 * @todo
 * @see
 *
 */

/**
 * IsValidIP validates an IPv4 or IPv6
 * @param   string      $IP the IP to validate
 * @return  boolean     TRUE if valid, FALSE if invalid
 * @since   0.0.9
 * @see
 * @todo
 */
function IsValidIP($IP)
{
    if (DEBUGMODE)
    {
        echo date("Y-m-d H:i:s").' -> IsValidIP '.PHP_EOL;
    }

    //IPv4
    //if (preg_match("/^(((([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])\.){3}([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5]))/", $IP))
    if (preg_match("/(([1-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])\.){1}(([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])\.){2}([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])/", $IP))
    {
        //echo "=>Good by IPV4=>";

        return TRUE;
    }

    //IPv6
    if (preg_match("/([a-f0-9:]+:+)+[a-f0-9]+/", $IP))
    {
        //echo "=>Good by IPV6=>";

        return TRUE;
    }

    //No luck
    return FALSE;
}


/**
 * IsValidIPv4 validates an IPv4
 * @param   string      $IP the IP to validate
 * @return  boolean     TRUE if valid, FALSE if invalid
 * @since   0.0.9
 * @see
 * @todo
 */
function IsValidIPv4($IP)
{
    if (DEBUGMODE)
    {
        echo date("Y-m-d H:i:s").' -> IsValidIPv4 '.PHP_EOL;
    }

    //IPv4
    //if (preg_match("/^(((([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])\.){3}([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5]))/", $IP))
    if (preg_match("/(([1-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])\.){1}(([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])\.){2}([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])/", $IP))
    {
        //echo "=>Good by IPV4=>";

        return TRUE;
    }

    //No luck
    return FALSE;
}

/**
 * IsValidIPv6 validates an IPv6
 * @param   string      $IP IP to validate
 * @return  boolean     TRUE if valid, FALSE if invalid
 * @since   0.0.9
 * @see
 * @todo
 */
function IsValidIPv6($IP)
{
    if (DEBUGMODE)
    {
        echo date("Y-m-d H:i:s").' -> IsValidIPv4 '.PHP_EOL;
    }

    //IPv6
    if (preg_match("/([a-f0-9:]+:+)+[a-f0-9]+/", $IP))
    {
        //echo "=>Good by IPV6=>";

        return TRUE;
    }

    //No luck
    return FALSE;
}

/**
 * IsValidHostname validates a RFC1123 hostname
 * @param   string      $HostnameOrIP the host or IP to validate
 * @return  boolean     TRUE if valid, FALSE if invalid
 * @since   0.0.1
 * @see
 * @todo
 */
function IsValidHostName($HostnameOrIP)
{
    if (DEBUGMODE)
    {
        echo date("Y-m-d H:i:s").' -> IsValidHostName '.PHP_EOL;
    }

    //Localhost is OK
    if ($HostnameOrIP === 'localhost')
    {
        //echo "=>Good by localhost=>";

        return TRUE;
    }
    else
    {
        //echo 'Not localhost =>';
    }

    //RFC1123 hostname
    //Each label within a valid hostname may be no more than 63 octets long
    //The total length of the hostname must not exceed 255 characters.
    //if (preg_match("/((([a-zA-Z]|[a-zA-Z][a-zA-Z0-9\-]*[a-zA-Z0-9])\.)+([A-Za-z|[A-Za-z][A-Za-z0-9\‌\u{200b}-]*[A-Za-z0-9])))$/gm/", $HostnameOrIP))
    //if (preg_match("/^(([a-zA-Z0-9]|[a-zA-Z0-9][a-zA-Z0-9\-]*[a-zA-Z0-9])\.)*([A-Za-z0-9]|[A-Za-z0-9][A-Za-z0-9\-]*[A-Za-z0-9])$/", $HostnameOrIP))
    if (preg_match("/^([a-zA-Z0-9]|[a-zA-Z0-9][a-zA-Z0-9\-]{0,61}[a-zA-Z0-9])(\.([a-zA-Z0-9]|[a-zA-Z0-9][a-zA-Z0-9\-]{0,61}[a-zA-Z0-9]))*$/", $HostnameOrIP))
    {
        //echo 'Passes RFC1123 rexexp=>';
        if (strlen($HostnameOrIP)<254)
        {
            //echo 'Passes RFC1123 less than 254 chars=>';
            return TRUE;
        } //END hostname is lower than 254 chars
        else
        {
            //echo "=>Bad by RFC1123 (bigger than 253 chars)=>";
        }
    } //End hostname has only alphanumeric chars and hyphen
    else
    {
        //echo "=>Bad by RFC1123 (regexp failure)=>";
    }

    //No luck
    return FALSE;
}

/**
 * IsValidHost validates a hostname, IPv4 or IPv6
 * @param   string      $HostnameOrIP the host or IP to validate
 * @param   boolean     $PublicNetwork must check or not for valid TLDs (TRUE)
 * @return  boolean     TRUE if valid, FALSE if invalid
 * @since   0.0.1
 * @see
 * @todo
 */
function IsValidHost($HostnameOrIP, $PublicNetwork = TRUE)
{
    if (DEBUGMODE)
    {
        echo date("Y-m-d H:i:s").' -> IsValidHost '.PHP_EOL;
    }

    //Localhost is OK
    if ($HostnameOrIP === 'localhost')
    {
        //echo "=>Good by localhost=>";

        return TRUE;
    }
    else
    {
        //echo 'Not localhost =>';
    }

    //IPv4
    //if (preg_match("/^(((([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])\.){3}([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5]))/", $HostnameOrIP))
    if (preg_match("/(([1-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])\.){1}(([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])\.){2}([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])/", $HostnameOrIP))
    {
        //echo "=>Good by IPV4=>";

        return TRUE;
    }
    else
    {
        //echo 'Not IPv4 =>';
    }

    //IPv6
    if (preg_match("/([a-f0-9:]+:+)+[a-f0-9]+/", $HostnameOrIP))
    {
        //echo "=>Good by IPV6=>";

        return TRUE;
    }
    else
    {
        //echo 'Not IPv6 =>';
    }

    //RFC1123 hostname
    //Each label within a valid hostname may be no more than 63 octets long
    //The total length of the hostname must not exceed 255 characters.
    //if (preg_match("/((([a-zA-Z]|[a-zA-Z][a-zA-Z0-9\-]*[a-zA-Z0-9])\.)+([A-Za-z|[A-Za-z][A-Za-z0-9\‌\u{200b}-]*[A-Za-z0-9])))$/gm/", $HostnameOrIP))
    //if (preg_match("/^(([a-zA-Z0-9]|[a-zA-Z0-9][a-zA-Z0-9\-]*[a-zA-Z0-9])\.)*([A-Za-z0-9]|[A-Za-z0-9][A-Za-z0-9\-]*[A-Za-z0-9])$/", $HostnameOrIP))
    if (preg_match("/^([a-zA-Z0-9]|[a-zA-Z0-9][a-zA-Z0-9\-]{0,61}[a-zA-Z0-9])(\.([a-zA-Z0-9]|[a-zA-Z0-9][a-zA-Z0-9\-]{0,61}[a-zA-Z0-9]))*$/", $HostnameOrIP))
    {
        //echo 'Passes RFC1123 rexexp=>';
        if (strlen($HostnameOrIP)<254)
        {
            //echo 'Passes RFC1123 less than 254 chars=>';
            //If it is a public network, we check that the 1st level domain is an approved IANA TLD
            if ($PublicNetwork)
            {
                //echo 'Is a public network=>';
                $Result = GetIanaTLDs(10);
                if ($Result === FALSE)
                {
                    $ErrorMessage = 'Error getting data';
                    ErrorLog($ErrorMessage, E_USER_ERROR);
                }
                else
                {
                    $Lines = file($Result);
                    if ($Lines === FALSE)
                    {
                        $ErrorMessage = 'Error getting data from '.$Result;
                        //echo $ErrorMessage.PHP_EOL;
                        ErrorLog($ErrorMessage, E_USER_ERROR);

                        return FALSE;
                    }
                    else
                    {
                        //print_r($Lines);
                        $HostnameChunks = explode(".", $HostnameOrIP);
                        $TheTLD = $HostnameChunks[count($HostnameChunks)-1];
                        $TheTLD = strtoupper($TheTLD).PHP_EOL;
                        //echo 'TLD = '.$TheTLD.'=>';
                        if (in_array($TheTLD, $Lines))
                        {
                            //echo "=>Good by RFC1123=>";

                            return TRUE;
                        }
                    } //END IANA file loading goes well
                } //END IANA querying goes swift
            } //END is a public network hostname
            else
            {
                //echo "=>Good by RFC1123=>";

                return TRUE;
            } //END is a private network hostname
        } //END hostname is lower than 254 chars
        else
        {
            //echo "=>Bad by RFC1123 (bigger than 253 chars)=>";
        }
    } //End hostname has only alphanumeric chars and hyphen
    else
    {
        //echo "=>Bad by RFC1123 (regexp failure)=>";
    }

    //No luck
    return FALSE;
}

/**
 * IsValidMySQLObjectName validates the name
 * Unquoted names can consist of any alphanumeric characters in the server's default character set, plus the characters '_' and '$'
 * Names can start with any character that is legal in a name, including a digit. However, a name cannot consist entirely of digits because that would make it
 * indistinguishable from a number. Diallowed chars: First, you cannot use the '.' character because it is the separator in db_name.tbl_name and
 * db_name.tbl_name.col_name notation. Second, you cannot use the UNIX or Windows pathname separator characters ('/' or '\').
 * @param string $Object
 * @return boolean TRUE if valid, FALSE if invalid
 * @since 0.0.1
 * @see https://www.informit.com/articles/article.aspx?p=30875
 *      https://dev.mysql.com/doc/refman/8.0/en/identifiers.html
 */
function IsValidMySQLObjectName($Object)
{
    if (DEBUGMODE)
    {
        echo date("Y-m-d H:i:s").' -> IsValidMySQLObjectName '.PHP_EOL;
    }

    //echo 'Object '.$Object.'=>';

    if (Enclosure($Object) === MIL_QUOTES_BACKTICK)
    {
        $NudeObject = mb_substr($Object, 1, mb_strlen($Object, "UTF-8")-2, "UTF-8");
        //echo $Object.'=>'.$NudeObject.PHP_EOL;
        //Empty name is bad
        if (empty($NudeObject))
        {
            return FALSE;
        }
        //A passed test of . / \ is bad
        if (preg_match("/[.\/\\\\]/", $NudeObject))
        {
            //echo $Object.' has dots or slashes'.PHP_EOL;

            return FALSE;
        }
        else
        {
            //Al digits objects are bad
            if (preg_match("/^[0-9]*$/", $NudeObject))
            {
                return FALSE;
            }

            return TRUE;
        }
    }
    else
    {
        //Only allow alphanumeric, $ and _ (thus not allow /, \ and .)
        if (preg_match("/^[A-Za-z0-9_\$]+$/", $Object) === 0)
        {
            return FALSE;
        }
        else
        {
            //Al digits objects are bad
            if (preg_match("/^[0-9]*$/", $Object))
            {
                return FALSE;
            }

            return TRUE;
        }
    }
}

/**
 * IsValidMySQLName checks if the database object name(table, column, view, alias...) is valid
 *              Identifier Type             Maximum Length (characters)
 *              Database                    64 (includes NDB Cluster 8.0.18 and later)
 *              Table                       64 (includes NDB Cluster 8.0.18 and later)
 *              Column                      64
 *              Index                       64
 *              Constraint                  64
 *              Stored Program              64
 *              View                        64
 *              Tablespace                  64
 *              Server                      64
 *              Log File Group              64
 *              Alias                       256 except aliases for column names in CREATE VIEW statements, that are checked against the maximum column length of
 *                                              64 characters
 *              Compound Statement Label    16
 *              User-Defined Variable       64
 *              Resource Group              64
 *
 * Used also for entities -except USER-
 *              Column Name             Maximum Permitted Characters
 *              Host, Proxied_host      255 (60 prior to MySQL 8.0.17)
 *              User, Proxied_user 	32
 *              Db                      64
 *              Table_name              64
 *              Column_name             64
 *              Routine_name            64
 *
 * @param string  $DBObject The name being checked
 * @param boolean $IsAlias  Is it an alias?
 * @return boolean TRUE if valid, FALSE if invalid
 * @since 0.0.1
 * @see https://documentation.sas.com/doc/en/pgmsascdc/9.4_3.5/acreldb/n0rfg6x1shw0ppn1cwhco6yn09f7.htm
 *      https://dev.mysql.com/doc/refman/8.0/en/identifier-length.html
 * @todo
 */
function IsValidMySQLName($DBObject, $IsAlias = FALSE)
{
    if (DEBUGMODE)
    {
        echo date("Y-m-d H:i:s").' -> IsValidMySQLName '.PHP_EOL;
    }

    //echo 'DBObject = '.$DBObject.'=>';
    //Check valid length
    if ($IsAlias === TRUE)
    {
        //Nobody uses multibyte chars on Db names... true?
        if (empty($DBObject)||mb_strlen($DBObject, 'UTF-8')>255)
        {
            //Alias can have between 1 and 255 chars
            return FALSE;
        }
    }
    else
    {
        if (empty($DBObject)||mb_strlen($DBObject, 'UTF-8')>64)
        {
            //The rest between 1 and 64 chars
            return FALSE;
        }
    }
    //Check valid chars
    //if (Enclosure($DBObject === MIL_QUOTES_BACKTICK))
    //{
    if (IsValidMySQLObjectName($DBObject) === FALSE)
    {
        return FALSE;
    }
    //}

    return TRUE;
}

/**
 * IsValidMySQLUser checks if the database user is valid
 * Restricted by Grant Table Scope Column Lengths
 *              Column Name             Maximum Permitted Characters
 *              Host, Proxied_host      255 (60 prior to MySQL 8.0.17)
 *              User, Proxied_user 	32
 *              Db                      64
 *              Table_name              64
 *              Column_name             64
 *              Routine_name            64
 *
 * @param type $DBObject
 * @param type $IsAlias
 * @param mixed $Username
 * @return boolean TRUE if valid, FALSE if invalid
 * @since 0.0.1
 * @see https://mariadb.com/kb/en/create-user/
 *      https://dev.mysql.com/doc/refman/8.0/en/user-names.html
 * @todo
 */
function IsValidMySQLUser($Username)
{
    if (DEBUGMODE)
    {
        echo date("Y-m-d H:i:s").' -> IsValidMySQLUser '.PHP_EOL;
    }

    if ((Enclosure($Username) === MIL_QUOTES_SINGLE)||(Enclosure($Username) === MIL_QUOTES_DOUBLE)||(Enclosure($Username) === MIL_QUOTES_BACKTICK))
    {
        $NudeUser = mb_substr($Username, 1, mb_strlen($Username, "UTF-8")-2, "UTF-8");
        //Empty name is bad
        if (empty($NudeUser))
        {
            return FALSE;
        }
        //Any UTF-8 char is good
        if (IsValidUTF8Text($Username) === TRUE)
        {
            //Maria DB usernames can be up to 80 characters long before 10.6 and starting from 10.6 it can be 128 characters long
            //But MySQL allow only 32
            if (mb_strlen($Username, 'UTF-8')<33)
            {
                return TRUE;
            }
        }
        //more than 80 or non-valid UTF-8
        return FALSE;
    }
    else
    {
        //Not enclosed or using parentheses, braces or brackets
        //Empty name is bad
        if (empty($Username))
        {
            return FALSE;
        }
        //A passed test of _ * ? is bad
        if (preg_match("/[_*?]/", $Username))
        {
            return FALSE;
        }
        else
        {
            if (mb_strlen($Username, 'UTF-8')<33)
            {
                return TRUE;
            }
        }
    }
}

/**
 * IsValidCharset validates Database Character Sets
 * @param type $Charset
 * @param type $System
 * @return boolean TRUE if valid, FALSE if invalid
 * @since 0.0.1
 * @see https://dev.mysql.com/doc/refman/8.0/en/charset-charsets.html
 * @todo include SQLServer and SQLite charsets(este no se si hace falta)
 *
 */
function IsValidCharset($Charset, $System)
{
    if (DEBUGMODE)
    {
        echo date("Y-m-d H:i:s").' -> IsValidCharset '.PHP_EOL;
    }

    $ValidMySQLCharsets = array(
        'armscii8', //ARMSCII-8 Armenian
        'ascii',    //US ASCII
        'big5',     //Big5 Traditional Chinese
        'binary',   //Binary pseudo charset
        'cp1250',   //Windows Central European
        'cp1251',   //Windows Cyrillic
        'cp1256',   //Windows Arabic
        'cp1257',   //Windows Baltic
        'cp850',    //DOS West European
        'cp852',    //DOS Central European
        'cp866',    //DOS Russian
        'cp932',    //SJIS for Windows Japanese
        'dec8',     //DEC West European
        'eucjpms',  //UJIS for Windows Japanese
        'euckr',    //EUC-KR Korean
        'gb18030',  //China National Standard GB18030
        'gb2312',   //GB2312 Simplified Chinese
        'gbk',      //GBK Simplified Chinese
        'geostd8',  //GEOSTD8 Georgian
        'greek',    //ISO 8859-7 Greek
        'hebrew',   //ISO 8859-8 Hebrew
        'hp8',      //HP West European
        'keybcs2',  //DOS Kamenicky Czech-Slovak
        'koi8r',    //KOI8-R Relcom Russian
        'koi8u',    //KOI8-U Ukrainian
        'latin1',   //cp1252 West European
        'latin2',   //ISO 8859-2 Central European
        'latin5',   //ISO 8859-9 Turkish
        'latin7',   //ISO 8859-13 Baltic
        'macce',    //Mac Central European
        'macroman', //Mac West European
        'sjis',     //Shift-JIS Japanese
        'swe7',     //7bit Swedish
        'tis620',   //TIS620 Thai
        'ucs2',     //UCS-2 Unicode
        'ujis',     //EUC-JP Japanese
        'utf16',    //UTF-16 Unicode
        'utf16le',  //UTF-16LE Unicode
        'utf32',    //UTF-32 Unicode
        'utf8mb3',  //UTF-8 Unicode
        'utf8mb4',  //UTF-8 Unicode
    );

    switch ($System)
    {
        case "MIL_CUBRID":
        case "CUBRID":
            $ErrorMessage = "Unimplemented system. Open an issue in https://github.com/ProceduralMan/MinionLib if you need it";
            echo $ErrorMessage.PHP_EOL;
            ErrorLog($ErrorMessage, E_USER_ERROR);

            return FALSE;
        case "MIL_DBASE":
        case "DBASE":
            $ErrorMessage = "Unimplemented system. Open an issue in https://github.com/ProceduralMan/MinionLib if you need it";
            echo $ErrorMessage.PHP_EOL;
            ErrorLog($ErrorMessage, E_USER_ERROR);

            return FALSE;
        case "MIL_FIREBIRD":
        case "FIREBIRD":
            $ErrorMessage = "Unimplemented system. Open an issue in https://github.com/ProceduralMan/MinionLib if you need it";
            echo $ErrorMessage.PHP_EOL;
            ErrorLog($ErrorMessage, E_USER_ERROR);

            return FALSE;
        case "MIL_INTERBASE":
        case "INTERBASE":
            $ErrorMessage = "Unimplemented system. Open an issue in https://github.com/ProceduralMan/MinionLib if you need it";
            echo $ErrorMessage.PHP_EOL;
            ErrorLog($ErrorMessage, E_USER_ERROR);

            return FALSE;
        case "MIL_DB2":
        case "MIL_CLOUDSCAPE":
        case "MIL_DERBY":
        case "DB2":
            $ErrorMessage = "Unimplemented system. Open an issue in https://github.com/ProceduralMan/MinionLib if you need it";
            echo $ErrorMessage.PHP_EOL;
            ErrorLog($ErrorMessage, E_USER_ERROR);

            return FALSE;
        case "MIL_MYSQL":
        case "MYSQL":
            if (in_array($Charset, $ValidMySQLCharsets))
            {
                return TRUE;
            }
            else
            {
                $ErrorMessage = "Unknown charset '.$Charset.'. Open an issue in https://github.com/ProceduralMan/MinionLib if you feel it is an error";
                echo $ErrorMessage.PHP_EOL;
                ErrorLog($ErrorMessage, E_USER_ERROR);

                return FALSE;
            }
            break;
        case "MIL_DBASE":
        case "DBASE":
            $ErrorMessage = "Unimplemented system. Open an issue in https://github.com/ProceduralMan/MinionLib if you need it";
            echo $ErrorMessage.PHP_EOL;
            ErrorLog($ErrorMessage, E_USER_ERROR);

            return FALSE;
        case "MIL_ORACLE":
        case "ORACLE":
            $ErrorMessage = "Unimplemented system. Open an issue in https://github.com/ProceduralMan/MinionLib if you need it";
            echo $ErrorMessage.PHP_EOL;
            ErrorLog($ErrorMessage, E_USER_ERROR);

            return FALSE;
        case "MIL_POSTGRE":
        case "POSTGRE":
            $ErrorMessage = "Unimplemented system. Open an issue in https://github.com/ProceduralMan/MinionLib if you need it";
            echo $ErrorMessage.PHP_EOL;
            ErrorLog($ErrorMessage, E_USER_ERROR);

            return FALSE;
        case "MIL_SQLITE":
        case "SQLITE":
            $ErrorMessage = "Unimplemented system. Open an issue in https://github.com/ProceduralMan/MinionLib if you need it";
            echo $ErrorMessage.PHP_EOL;
            ErrorLog($ErrorMessage, E_USER_ERROR);

            return FALSE;
        case "MIL_SQLSRV":
        case "SQLSRV":
            $ErrorMessage = "Unimplemented system. Open an issue in https://github.com/ProceduralMan/MinionLib if you need it";
            echo $ErrorMessage.PHP_EOL;
            ErrorLog($ErrorMessage, E_USER_ERROR);

            return FALSE;
        default:
            $ErrorMessage = 'Unknown DB System';
            echo $ErrorMessage.PHP_EOL;
            ErrorLog($ErrorMessage, E_USER_ERROR);

            exit(1);
    }
}

/**
 * IsValidIANAPort just checks if it falls in the valid range 0-65535
 * @param int $PortNumber
 * @return boolean TRUE if the value is valid, FALSE if not
 * @since   0.0.1
 * @see     https://en.wikipedia.org/wiki/List_of_TCP_and_UDP_port_numbers
 * @todo
 */
function IsValidIANAPort($PortNumber)
{
    if (DEBUGMODE)
    {
        echo date("Y-m-d H:i:s").' -> IsValidIANAPort '.PHP_EOL;
    }

    if ($PortNumber<0)
    {
        return FALSE;
    }
    if ($PortNumber>65535)
    {
        return FALSE;
    }
    else
    {
        return TRUE;
    }
}

/**
 * function IsAdequateDatabasePort will check if port is OK for holding a SQL Database main port
 * Will only accept TCP Ports and return the following strings
 *  OK      if the port is the assigned one for that system or unassigned
 *  WARN    if port is assigned to other service or on the ephemeral range
 *  WRONG   if proto is other than TCP, or port is reserved
 *  ERROR   Error making the validation
 * Based on data from january, 20th, 2022. Will be updated accordingly over time
 * @param string    $System
 * @param int       $PortNumber
 * @param string    $Proto
 * @return array with the keys ['code'], ['service'] (can be null), and ['explanation']
 * @since   0.0.1
 * @see     https://www.iana.org/assignments/service-names-port-numbers/service-names-port-numbers.xhtml
 * @todo
 */
function IsAdequateDatabasePort($System, $PortNumber, $Proto = 'TCP')
{
    if (DEBUGMODE)
    {
        echo date("Y-m-d H:i:s").' -> IsAdequateDatabasePort '.PHP_EOL;
    }

    $DataVersion = '20220120';
    $Result['code'] = '';
    $Result['service'] = '';
    $Result['explanation'] = '';

    $StandardPorts = array(
        "MYSQL"    => 3306,
        "MS-SQL-S" => 1433
    );

    //Test if TCP protocol
    if ($Proto !== 'TCP')
    {
        $Result['code'] = 'WRONG';
        $Result['service'] = NULL;
        $Result['explanation'] = 'SQL Database systems work over TCP';

        return $Result;
    }

    //Test if valid port
    if (IsValidIANAPort($PortNumber) === FALSE)
    {
        $Result['code'] = 'WRONG';
        $Result['service'] = NULL;
        $Result['explanation'] = 'Port must fall on the valid 0-65535 range';

        return $Result;
    }

    //Load TCP ports DataSet
    $FileString = $DataVersion.$Proto.'PortsIANA.json';
    $JSONString = file_get_contents(__DIR__.'/../DataSets/'.$FileString);
    if ($JSONString === FALSE)
    {
        $Result['code'] = 'ERROR';
        $Result['service'] = NULL;
        $Result['explanation'] = 'Unable to read file '.$FileString;

        return $Result;
    }

    /*
     * Get the optimized port info file
     * Gives an array of the form:
     *         [5832] => Array
     *             (
     *                 [START] => 8504
     *                 [END] => 8553
     *                 [STATUS] => UNASSIGNED
     *                 [SERVICE] =>
     *                 [DESCRIPTION] =>
     *             )
     *
     *         [5833] => Array
     *             (
     *                 [START] => 8554
     *                 [END] => 8554
     *                 [STATUS] => ASSIGNED
     *                 [SERVICE] => rtsp-alt
     *                 [DESCRIPTION] => RTSP Alternate (see port 554)
     *             )
     *
     */
    $TCPPorts = json_decode($JSONString, TRUE);
    if ($TCPPorts === NULL)
    {
        $Result['code'] = 'ERROR';
        $Result['service'] = NULL;
        $Result['explanation'] = 'Error decoding '.$FileString.' JSON file. '.json_last_error_msg();

        return $Result;
    }

    //As the array is sorted, we can find the nearest using the initial port
    //eg: For port 8525, closest is 8504 (key 5832)
    //eg: For port 8550, closest is 8554 (key 5833) which is indeed the nearest start but on other sequence
    $Closest = NULL;
    foreach ($TCPPorts as $Key => $Value)
    {
        if ($Closest === NULL||abs($PortNumber-$Closest)>abs($Value['START']-$PortNumber))
        {
            $Closest = $Value['START'];
            $ClosestKey = $Key;
        }
    }

    //eg: For port 8525, closest is 8504 (key 5832)
    //eg: For port 8550, closest is 8554 (key 5833) which is indeed the nearest start but on other sequence
    //So we check includeness -it will always be no more than one less or one more then the correct
    $Start = $TCPPorts[$ClosestKey]['START'];
    $End = $TCPPorts[$ClosestKey]['END'];
    if (($Start<=$PortNumber)&&($PortNumber<=$End))
    {
        $ValidationKey = $ClosestKey;
    }
    elseif ($PortNumber<$Start)
    {
        $ValidationKey = $ClosestKey-1;
    }
    else
    {
        $ValidationKey = $ClosestKey+1;
    }
    //echo 'Closest is '.$Closest.' (key '.$ClosestKey.') but correct is range '.$TCPPorts[$ValidationKey]['START'].'-'.$TCPPorts[$ValidationKey]['END'].PHP_EOL;

    //See if it is standard
    if ($StandardPorts[$System] === $PortNumber)
    {
        $Result['code'] = 'OK';
        $Result['service'] = $TCPPorts[$ValidationKey]['SERVICE'];
        $Result['explanation'] = 'SQL Database uses standard port '.$PortNumber.' '.$TCPPorts[$ValidationKey]['STATUS'].' to '.
                $TCPPorts[$ValidationKey]['SERVICE'].' ('.$TCPPorts[$ValidationKey]['DESCRIPTION'].')';

        return $Result;
    }

    //Status-based return code
    switch ($TCPPorts[$ValidationKey]['STATUS'])
    {
        case 'UNASSIGNED':
            $Result['code'] = 'OK';
            $Result['service'] = NULL;
            $Result['explanation'] = 'SQL Database uses '.$TCPPorts[$ValidationKey]['STATUS'].' port '.$PortNumber;
            break;
        case 'ASSIGNED':
            $Result['code'] = 'WARN';
            $Result['service'] = $TCPPorts[$ValidationKey]['SERVICE'];
            $Result['explanation'] = 'SQL Database uses valid port '.$PortNumber.' but that port is '.$TCPPorts[$ValidationKey]['STATUS'].' to '.
                $TCPPorts[$ValidationKey]['SERVICE'].' ('.$TCPPorts[$ValidationKey]['DESCRIPTION'].'), so it might give problems if you need that service';
            break;
        case 'EPHEMERAL':
            $Result['code'] = 'WARN';
            $Result['service'] = NULL;
            $Result['explanation'] = 'SQL Database uses valid port '.$PortNumber.' but that port is '.$TCPPorts[$ValidationKey]['STATUS'].
                    ', thus meant to be used "'.$TCPPorts[$ValidationKey]['DESCRIPTION'].'"... not the best choice';
            break;
        case 'RESERVED':
            $Result['code'] = 'WRONG';
            $Result['service'] = NULL;
            $Result['explanation'] = 'SQL Database uses valid port '.$PortNumber.' but that port is '.$TCPPorts[$ValidationKey]['STATUS'].
                ' and should not be used.';
            break;
    }

    return $Result;
}

/**
 * IsAssocArray checks wether or not an array is associative
 * @param   array   $Data the array to check
 * @return  boolean TRUE if is an associative array, FALSE otherwise
 * @since   0.0.7
 * @see     
 * @todo
 */
function IsAssocArray(&$Data)
{
    if (DEBUGMODE)
    {
        echo date("Y-m-d H:i:s").' -> IsAssocArray '.PHP_EOL;
    }

    //Check if we have an array
    if (is_array($Data) === FALSE)
    {
        return FALSE;
    }
    //From PHP 8.1 on, there is a function for it
    if ((PHP_MAJOR_VERSION === 8)&&(PHP_MINOR_VERSION>=1))
    {
        if (array_is_list($Data) === TRUE)
        {
            //Array is a list -NUMERIC ARRAY-
            return FALSE;
        }
        else
        {
            //Array is not a list -ASSOC ARRAY-
            return TRUE;
        }
    }
    else
    {
        return is_array($Data)&&array_diff_key($Data,array_keys(array_keys($Data)));
    }
}

/**
 * IsNumericArray checks wether or not an array is numeric
 * @param   array   $Data the array to check
 * @return  boolean TRUE if it is a numeric array, FALSE otherwise
 * @since   0.0.7
 * @see     
 * @todo
 */
function IsNumericArray(&$Data)
{
    if (DEBUGMODE)
    {
        echo date("Y-m-d H:i:s").' -> IsNumericArray '.PHP_EOL;
    }

    //Check if we have an array
    if (is_array($Data) === FALSE)
    {
        return FALSE;
    }

    if (IsAssocArray($Data) === TRUE)
    {
        //If it is associative, it is not numeric
        return FALSE;
    }
    else
    {
        return TRUE;
    }
}

/**
 * IsMultiArray checks if the array is single or multidimensional.
 * @param   array   $Data The array to check
 * @return  mixed   FALSE if it is not an array, 1 for unidimensional arrays and 2 for multidimensional arrays
 * @since   0.0.7
 * @see     
 * @todo
 */
function IsMultiArray(&$Data)
{
    if (DEBUGMODE)
    {
        echo date("Y-m-d H:i:s").' -> IsMultiArray '.PHP_EOL;
    }

    if (is_array($Data) === FALSE)
    {
        return FALSE;
    }

    $SimpleCount = count($Data);
    $RecursiveCount = count($Data, COUNT_RECURSIVE);
    if ($SimpleCount === $RecursiveCount)
    {
        //Unidimensional array
        return 1;
    }
    else
    {
        //Multidimensional array
        return 2;
    }
}

/**
 * IsMySQLAssocDataStructure checks if the array has the structure of a MySQL ASSOC Data Structure -no Metadata-
 * @param   array   $Data The array to check
 * @return  boolean TRUE if it is, FALSE if it is not
 * @since   0.0.7
 * @see     
 * @todo
 */
function IsMySQLAssocDataStructure(&$Data)
{
    if (DEBUGMODE)
    {
        echo date("Y-m-d H:i:s").' -> IsMySQLAssocDataStructure '.PHP_EOL;
    }

    //Must have the necessary structure
    if ((isset($Data['Columns']) === FALSE)||(isset($Data['Rows']) === FALSE)||(isset($Data['Data']) === FALSE))
    {
        return FALSE;
    }
    else
    {
        //But not the metadata!
        if (isset($Data['Metadata']) === TRUE)
        {
            return FALSE;
        }
        else
        {
            return TRUE;
        }
    }
}

/**
 * IsMYSMADataStructure checks if the array has the structure of a MySQL ASSOC Data Structure -plus Metadata-
 * @param   array   $Data The array to check
 * @return  mixed   FALSE if it is not an array, 1 for unidimensional arrays and 2 for multidimensional arrays
 * @since   0.0.8
 * @see     
 * @todo
 */
function IsMYSMADataStructure(&$Data)
{
    if (DEBUGMODE)
    {
        echo date("Y-m-d H:i:s").' -> IsMySQLAssocDataStructure '.PHP_EOL;
    }
    if ((isset($Data['Columns']) === FALSE)||(isset($Data['Rows']) === FALSE)||(isset($Data['Metadata']) === FALSE)||(isset($Data['Data']) === FALSE))
    {
        return FALSE;
    }
    else
    {
        return TRUE;
    }
}

/**
 * IsValidURI
 * Validates an URL/URI based on RFC3986
 * @param   string  $URI    The URI(URL) to check
 * @return  boolean TRUE if it is a valid URI, FALSE otherwise
 * @since   0.0.9
 * @see     taken from https://gist.github.com/kpobococ/92f120c6c4a9a52b84e3
 * @todo
 */
function IsValidURI($URI)
{
    // Play around with this regexp online:
    // http://regex101.com/r/hZ5gU9/1

    // Links to relevant RFC documents:
    // RFC 3986: http://tools.ietf.org/html/rfc3986 (URI scheme)
    // RFC 2234: http://tools.ietf.org/html/rfc2234#section-6.1 (ABNF notation)
    $RegExp = '/
    # URI scheme RFC 3986
    (?(DEFINE)
      # ABNF notation of RFC 2234
    
      (?<ALPHA>     [\x41-\x5A\x61-\x7A] )    # Latin character (A-Z, a-z)
      (?<CR>        \x0D )                    # Carriage return (\r)
      (?<DIGIT>     [\x30-\x39] )             # Decimal number (0-9)
      (?<DQUOTE>    \x22 )                    # Double quote (")
      (?<HEXDIG>    (?&DIGIT) | [\x41-\x46] ) # Hexadecimal number (0-9, A-F)
      (?<LF>        \x0A )                    # Line feed (\n)
      (?<SP>        \x20 )                    # Space
    
      # RFC 3986 body
    
      (?<uri>    (?&scheme) \: (?&hier_part) (?: \? (?&query) )? (?: \# (?&fragment) )? )
    
      (?<hier_part>    \/\/ (?&authority) (?&path_abempty)
                     | (?&path_absolute)
                     | (?&path_rootless)
                     | (?&path_empty) )
    
      (?<uri_reference>    (?&uri) | (?&relative_ref) )
    
      (?<absolute_uri>    (?&scheme) \: (?&hier_part) (?: \? (?&query) )? )
    
      (?<relative_ref>    (?&relative_part) (?: \? (?&query) )? (?: \# (?&fragment) )? )
    
      (?<relative_part>     \/\/ (?&authority) (?&path_abempty)
                          | (?&path_absolute)
                          | (?&path_noscheme)
                          | (?&path_empty) )
    
      (?<scheme>    (?&ALPHA) (?: (?&ALPHA) | (?&DIGIT) | \+ | \- | \. )* )
    
      (?<authority>    (?: (?&userinfo) \@ )? (?&host) (?: \: (?&port) )? )
      (?<userinfo>     (?: (?&unreserved) | (?&pct_encoded) | (?&sub_delims) | \: )* )
      (?<host>         (?&ip_literal) | (?&ipv4_address) | (?&reg_name) )
      (?<port>         (?&DIGIT)* )
    
      (?<ip_literal>    \[ (?: (?&ipv6_address) | (?&ipv_future) ) \] )
    
      (?<ipv_future>    \x76 (?&HEXDIG)+ \. (?: (?&unreserved) | (?&sub_delims) | \: )+ )
    
      (?<ipv6_address>                                              (?: (?&h16) \: ){6} (?&ls32)
                        |                                      \:\: (?: (?&h16) \: ){5} (?&ls32)
                        |                           (?&h16)?   \:\: (?: (?&h16) \: ){4} (?&ls32)
                        | (?: (?: (?&h16) \: ){0,1} (?&h16) )? \:\: (?: (?&h16) \: ){3} (?&ls32)
                        | (?: (?: (?&h16) \: ){0,2} (?&h16) )? \:\: (?: (?&h16) \: ){2} (?&ls32)
                        | (?: (?: (?&h16) \: ){0,3} (?&h16) )? \:\:     (?&h16) \:      (?&ls32)
                        | (?: (?: (?&h16) \: ){0,4} (?&h16) )? \:\:                     (?&ls32)
                        | (?: (?: (?&h16) \: ){0,5} (?&h16) )? \:\:                     (?&h16)
                        | (?: (?: (?&h16) \: ){0,6} (?&h16) )? \:\: )
    
      (?<h16>             (?&HEXDIG){1,4} )
      (?<ls32>            (?: (?&h16) \: (?&h16) ) | (?&ipv4_address) )
      (?<ipv4_address>    (?&dec_octet) \. (?&dec_octet) \. (?&dec_octet) \. (?&dec_octet) )
    
      (?<dec_octet>    (?&DIGIT)
                     | [\x31-\x39] (?&DIGIT)
                     | \x31 (?&DIGIT){2}
                     | \x32 [\x30-\x34] (?&DIGIT)
                     | \x32\x35 [\x30-\x35] )
    
      (?<reg_name>     (?: (?&unreserved) | (?&pct_encoded) | (?&sub_delims) )* )
    
      (?<path>    (?&path_abempty)
                | (?&path_absolute)
                | (?&path_noscheme)
                | (?&path_rootless)
                | (?&path_empty) )
    
      (?<path_abempty>     (?: \/ (?&segment) )* )
      (?<path_absolute>    \/ (?: (?&segment_nz) (?: \/ (?&segment) )* )? )
      (?<path_noscheme>    (?&segment_nz_nc) (?: \/ (?&segment) )* )
      (?<path_rootless>    (?&segment_nz) (?: \/ (?&segment) )* )
      (?<path_empty>       (?&pchar){0} ) # For explicity only
    
      (?<segment>       (?&pchar)* )
      (?<segment_nz>    (?&pchar)+ )
      (?<segment_nz_nc> (?: (?&unreserved) | (?&pct_encoded) | (?&sub_delims) | \@ )+ )
    
      (?<pchar>    (?&unreserved) | (?&pct_encoded) | (?&sub_delims) | \: | \@ )
    
      (?<query>    (?: (?&pchar) | \/ | \? )* )
    
      (?<fragment>    (?: (?&pchar) | \/ | \? )* )
    
      (?<pct_encoded>    \% (?&HEXDIG) (?&HEXDIG) )
    
      (?<unreserved>    (?&ALPHA) | (?&DIGIT) | \- | \. | \_ | \~ )
      (?<reserved>      (?&gen_delims) | (?&sub_delims) )
      (?<gen_delims>    \: | \/ | \? | \# | \[ | \] | \@ )
      (?<sub_delims>    \! | \$ | \& | \' | \( | \)
                      | \* | \+ | \, | \; | \= )
    
    )
    ^(?&uri)$
    /x';

    if (preg_match($RegExp, $URI) === 1)
    {
        return TRUE;
    }
    else
    {
        //Unmatching or erroneous
        return FALSE;
    }
}

/**
 * IsValidMD5
 * Validates a string as a valid MD5 hex string
 * @param string    $Hash  The MD5 to check
 * @return boolean  TRUE if it is a valid MD5, FALSE otherwise
 * @since   0.0.9
 * @see     
 * @todo
 */
function IsValidMD5($Hash)
{
    if ((is_string($Hash))&&(strlen($Hash) === 32)&&(trim($Hash, '0..9A..Fa..f') === ''))
    {
        // string is 32 byte hexadecimal string
        return TRUE;
    }
    else
    {
        return FALSE;
    }
}

/**
 * IsValidSHA256
 * Validates a string as a valid SHA256 hex string
 * @param string    $Hash  The SHA256 to check
 * @return boolean  TRUE if it is a valid SHA256, FALSE otherwise
 * @since   0.0.9
 * @see     
 * @todo
 */
function IsValidSHA256($Hash)
{
    if ((is_string($Hash))&&(strlen($Hash) === 64)&&(trim($Hash, '0..9A..Fa..f') === ''))
    {
        // string is 64 byte hexadecimal string
        return TRUE;
    }
    else
    {
        return FALSE;
    }
}

/**
 * IsValidBase64SHA256
 * Validates a string as a valid base64-encoded SHA256 hex string
 * @param string    $Hash  The base64-encoded SHA256 to check
 * @return boolean  TRUE if it is a valid SHA256, FALSE otherwise
 * @since   0.0.9
 * @see     
 * @todo
 */
function IsValidBase64SHA256($Hash)
{
    return IsValidSHA256(base64_decode($Hash));
}


/**
 * IsValidHTTPMethod
 * Validates a string as a valid HTTP method
 * @param   string  $HTTPMethod the method to test
 * @return  boolean TRUE if it is a valid HTTP method, FALSE otherwise
 * @since   0.0.9
 * @see     https://reqbin.com/Article/HttpMethods
 *          https://www.restapitutorial.com/lessons/httpmethods.html
 * @todo
 */
function IsValidHTTPMethod($HTTPMethod)
{
    $ValidHTTPMethods = array('GET', 'HEAD', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS', 'CONNECT', 'TRACE');
    if (!in_array($HTTPMethod, $ValidHTTPMethods))
    {
        return FALSE;
    }
    else
    {
        return TRUE;
    }
}

/**
 * IsValidInt
 * Validates that it is really an Int and value is between Min and Max
 * @param mixed     $Value  The value to test
 * @param int       $Min    Value must be equal to or greater than this
 * @param int       $Max    Value must be equal to or lower than this
 * @return boolean  TRUE if it is really an Int and value is between Min and Max, FALSE otherwise
 * @since   0.0.9
 * @see     
 * @todo
 */
function IsValidInt($Value, $Min = NULL, $Max = NULL)
{
    //Must be an INT
    if (!is_int($Value))
    {
        return FALSE;
    }

    //Equal to or bigger than MIN, if specified
    if (!empty($Min)&&($Value<$Min))
    {
        return FALSE;
    }

    //Equal to or lower than MAX, if specified
    if (!empty($Max)&&($Value>$Max))
    {
        return FALSE;
    }

    //All good, is a proper Int
    return TRUE;
}

/**
 * IsValidISODate
 * Checks that date is in ISO format (YYYY-MM-DD), is valid, and within a range of dates
 * @param   string $Value       The date to check
 * @param   string $StartDate   The below limit date
 * @param   string $EndDate     The above limit date
 * @return  boolean TRUE if it is a valid date within the desired range, FALSE otherwise
 * @since   0.0.9
 * @see     
 * @todo
 */
function IsValidISODate($Value, $StartDate, $EndDate)
{
    //Create Date Object
    $ObjectiveDate = date_create_from_format('Y-m-d', $Value);
    if ($ObjectiveDate === FALSE)
    {
        return FALSE;
    }

    //Check validity
    if (date_format($ObjectiveDate, 'Y-m-d') !== $Value)
    {
        //Non-valid date
        return FALSE;
    }

    //Check limits
    $MinDate = date_create_from_format('Y-m-d', $StartDate);
    if ($MinDate === FALSE)
    {
        return FALSE;
    }

    $MaxDate = date_create_from_format('Y-m-d', $EndDate);
    if ($MaxDate === FALSE)
    {
        return FALSE;
    }

    if (($ObjectiveDate<$MinDate)||($ObjectiveDate>$MaxDate))
    {
        return FALSE;
    }
    else
    {
        return TRUE;
    }
}

/**
 * IsValidISODateTime
 * Checks that datetime is in ISO format (YYYY-MM-DD HH:MM:SS), is valid, and within a range of datetimes
 * @param   string $Value       The date to check
 * @param   string $StartDate   The below limit date
 * @param   string $EndDate     The above limit date
 * @return  boolean TRUE if it is a valid date within the desired range, FALSE otherwise
 * @since   0.0.9
 * @see     
 * @todo
 */
function IsValidISODateTime($Value, $StartDate, $EndDate)
{
    //Create Date Object
    $ObjectiveDate = date_create_from_format('Y-m-d H:i:s', $Value);
    if ($ObjectiveDate === FALSE)
    {
        return FALSE;
    }

    //Check validity
    if (date_format($ObjectiveDate, 'Y-m-d H:i:s') !== $Value)
    {
        //Non-valid date
        return FALSE;
    }

    //Check limits
    $MinDate = date_create_from_format('Y-m-d H:i:s', $StartDate);
    if ($MinDate === FALSE)
    {
        return FALSE;
    }

    $MaxDate = date_create_from_format('Y-m-d H:i:s', $EndDate);
    if ($MaxDate === FALSE)
    {
        return FALSE;
    }

    if (($ObjectiveDate<$MinDate)||($ObjectiveDate>$MaxDate))
    {
        return FALSE;
    }
    else
    {
        return TRUE;
    }
}

/**
 * ISODateNoLaterThan
 * Checks that date is in ISO format (YYYY-MM-DD) and earlier or the same as a limit date
 * @param   string  $Value the date to check
 * @param   string  $Limit the limit date
 * @return  boolean TRUE if $Value is the same as $Limit or earlier. FALSE otherwise.
 * @since   0.0.9
 * @see     
 * @todo
 */
function ISODateNoLaterThan($Value, $Limit)
{
    $ObjectiveDate = date_create_from_format('Y-m-d', $Value);
    if ($ObjectiveDate === FALSE)
    {
        return FALSE;
    }

    $MaxDate = date_create_from_format('Y-m-d', $Limit);
    if ($MaxDate === FALSE)
    {
        return FALSE;
    }

    if ($ObjectiveDate>$MaxDate)
    {
        return FALSE;
    }
    else
    {
        return TRUE;
    }
}

/**
 * ISODateNoEarlierThan
 * Checks that date is in ISO format (YYYY-MM-DD) and later or the same as a limit date
 * @param   string  $Value the date to check
 * @param   string  $Limit the limit date
 * @return  boolean TRUE if $Value is the same as $Limit or later. FALSE otherwise.
 */
function ISODateNoEarlierThan($Value, $Limit)
{
    $ObjectiveDate = date_create_from_format('Y-m-d', $Value);
    if ($ObjectiveDate === FALSE)
    {
        return FALSE;
    }

    $MaxDate = date_create_from_format('Y-m-d', $Limit);
    if ($MaxDate === FALSE)
    {
        return FALSE;
    }

    if ($ObjectiveDate<$MaxDate)
    {
        return FALSE;
    }
    else
    {
        return TRUE;
    }
}

/**
 * ISODateTimeNoLaterThan
 * Checks that date is in ISO format (YYYY-MM-DD HH:MM:SS) and earlier or the same as a limit date
 * @param   string  $Value the date to check
 * @param   string  $Limit the limit date
 * @return  boolean TRUE if $Value is the same as $Limit or earlier. FALSE otherwise.
 * @since   0.0.9
 * @see     
 * @todo
 */
function ISODateTimeNoLaterThan($Value, $Limit)
{
    $ObjectiveDate = date_create_from_format('Y-m-d H:i:s', $Value);
    if ($ObjectiveDate === FALSE)
    {
        return FALSE;
    }

    $MaxDate = date_create_from_format('Y-m-d H:i:s', $Limit);
    if ($MaxDate === FALSE)
    {
        return FALSE;
    }

    if ($ObjectiveDate>$MaxDate)
    {
        return FALSE;
    }
    else
    {
        return TRUE;
    }
}

/**
 * ISODateTimeNoEarlierThan
 * Checks that date is in ISO format (YYYY-MM-DD HH:MM:SS) and later or the same as a limit date
 * @param   string  $Value the date to check
 * @param   string  $Limit the limit date
 * @return  boolean TRUE if $Value is the same as $Limit or later. FALSE otherwise.
 */
function ISODateTimeNoEarlierThan($Value, $Limit)
{
    $ObjectiveDate = date_create_from_format('Y-m-d H:i:s', $Value);
    if ($ObjectiveDate === FALSE)
    {
        return FALSE;
    }

    $MaxDate = date_create_from_format('Y-m-d H:i:s', $Limit);
    if ($MaxDate === FALSE)
    {
        return FALSE;
    }

    if ($ObjectiveDate<$MaxDate)
    {
        return FALSE;
    }
    else
    {
        return TRUE;
    }
}
