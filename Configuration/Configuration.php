<?php

/*
 * Configuration
 * Useful functions for configuring process or program behaviour
 * @author ProceduralMan <proceduralman@gmail.com>
 * @copyright 2021
 * @version 1.0 initial version
 * @package Minion
 * @todo
 * @see
 */

/**
 * Defaults
 * defines if not defined. Creates constants.
 * @param   string    $Define The constant literal
 * @param   mixed     $Value  The constant value
 * @return  void
 * @since 0.0.7
 * @todo
 * @see
 */
function Defaults($Define, $Value)
{
    if (DEBUGMODE)
    {
        echo date("Y-m-d H:i:s").' -> Defaults '.PHP_EOL;
    }

    if (defined($Define) === FALSE)
    {
        define($Define,$Value);
    }
}

/**
 * ErrorConstantToLiteral turns standard int error constants to error literals
 * @param   int     ErrorType
 * @param mixed $ErrorType
 * @return  string  The error literal
 * @since 0.0.1
 * @todo
 * @see
 */
function ErrorConstantToLiteral($ErrorType)
{
    if (DEBUGMODE)
    {
        echo date("Y-m-d H:i:s").' -> ErrorConstantToLiteral '.PHP_EOL;
    }

    switch ($ErrorType) {
        case E_ERROR:
            return 'E_ERROR';
        case E_CORE_ERROR:
            return 'E_CORE_ERROR';
        case E_COMPILE_ERROR:
            return 'E_COMPILE_ERROR';
        case E_PARSE:
            return 'E_PARSE';
        case E_WARNING:
            return 'E_WARNING';
        case E_NOTICE:
            return 'E_NOTICE';
        case E_CORE_WARNING:
            return 'E_CORE_WARNING';
        case E_COMPILE_WARNING:
            return 'E_COMPILE_WARNING';
        case E_USER_ERROR:
            return 'E_USER_ERROR';
        case E_USER_WARNING:
            return 'E_USER_WARNING';
        case E_USER_NOTICE:
            return 'E_USER_NOTICE';
        case E_USER_DEPRECATED:
            return 'E_USER_DEPRECATED';
        default:
            //Gracefully fail without logging to avoid deadlocking
            return 'UNKNOWN_CODE';
    }
}

/**
 * IniShorthand2Int translates shorthand notation to int value
 * Following https://www.php.net/manual/en/faq.using.php#faq.using.shorthandbytes
 * The available options are K (for Kilobytes), M (for Megabytes) and G (for Gigabytes), 
 * and are all case-insensitive. Anything else assumes bytes. 1M equals one Megabyte or 1048576 bytes. 
 * 1K equals one Kilobyte or 1024 bytes. These shorthand notations may be used in php.ini and in the 
 * ini_set() function. Note that the numeric value is cast to int; for instance, 0.5M is interpreted as 0. 
 * @param  string $IniValue The configuration value
 * @return int $IntValue of the Ini Value
 */
function IniShorthand2Int($IniValue)
{
    if (DEBUGMODE)
    {
        echo date("Y-m-d H:i:s").' -> IniShorthand2Int '.PHP_EOL;
    }

    //Nothing to do if it is an empty value
    if (empty($IniValue))
    {
        $ErrorMessage = "Empty configuration directive value.";
        ErrorLog($ErrorMessage, E_USER_ERROR);

        return FALSE;
    }

    //Shorthand (K,M,G) notations will fail the is_numeric test
    if (is_numeric($IniValue))
    {
        return intval($IniValue);
    }
    else
    {
        //Might be proper shorthand or rubbish
        //Strings in configuration are never multibyte so
        $LastChar = strtoupper(substr($IniValue, -1));
        $NumericPart = substr($IniValue, 0, strlen($IniValue)-1);
        switch ($LastChar)
        {
            case 'K':
                return intval($NumericPart)*1024;
            case 'M':
                return intval($NumericPart)*1024*1024;
            case 'G':
                return intval($NumericPart)*1024*1024*1024;
            default:
                $ErrorMessage = "Uncontrolled configuration directive value: ".$IniValue;
                ErrorLog($ErrorMessage, "E_USER_ERROR");

                return FALSE;
        }
    }
}
