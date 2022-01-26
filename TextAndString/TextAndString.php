<?php

/*
 * TextAndString
 * Text ans String utility functions
 *
 * @author ProceduralMan <proceduralman@gmail.com>
 * @copyright 2021
 * @version 1.0 initial version
 * @package Minion
 * @todo 
 * @see  
 * 
 */

//Quotes
define("MIL_QUOTES_NONE",           0);     // No quotes
define("MIL_QUOTES_SINGLE",         10);    // Single quotes '
define("MIL_QUOTES_DOUBLE",         20);    // Double quotes "
define("MIL_QUOTES_BACKTICK",       30);    // Backtick quotes `
define("MIL_QUOTES_PARENTHESES",    40);    // Parentheses ()
define("MIL_QUOTES_BRACES",         50);    // Curly brackets or braces {}
define("MIL_QUOTES_BRACKETS",       60);    // Square brackets or brackets []

/**
 * Enclosure detects if the string is enclosed by something
 * @param type $String
 * @return type
 */
function Enclosure($String)
{
    $FirstChar = mb_substr($String, 0, 1, 'UTF-8');
    $LastChar = mb_substr($String, -1, 1, 'UTF-8');
    //echo 'String='.$String.', FC='.$FirstChar.', LC='.$LastChar.'=>';
    if (DEBUGMODE)
    {
        echo date("Y-m-d H:i:s").' -> Enclosure FC='.$FirstChar.', LC='.$LastChar.PHP_EOL;
    }

    //Is it enclosed by single quotes?
    //if (preg_match("/('[^'\\\\]*(?:\\\\.[^'\\\\]*)*')/", $String))
    if (($FirstChar === "'")&&($LastChar === "'"))
    {
        return MIL_QUOTES_SINGLE;
    }
    //Is it enclosed by double quotes?
    //if (preg_match('/^(["\']).*\1$/m', $String))
    if (($FirstChar === '"')&&($LastChar === '"'))
    {
        return MIL_QUOTES_DOUBLE;
    }
    //Is it enclosed by backticks?
    //if (preg_match('/^([`\']).*\1$/m', $String))
    if (($FirstChar === "`")&&($LastChar === "`"))
    {
        return MIL_QUOTES_BACKTICK;
    }
    //Is it enclosed by parentheses?
    if (($FirstChar === "(")&&($LastChar === ")"))
    {
        return MIL_QUOTES_PARENTHESES;
    }
    //Is it enclosed by braces?
    if (($FirstChar === "{")&&($LastChar === "}"))
    {
        return MIL_QUOTES_BRACES;
    }
    //Is it enclosed by brackets?
    if (($FirstChar === "[")&&($LastChar === "]"))
    {
        return MIL_QUOTES_BRACES;
    }

    return MIL_QUOTES_NONE;
}

/**
 * IsValidUTF8Text validates if the texto complies withg UTF-8
 * @param type $Text
 * @return boolean
 * @see https://www.w3.org/International/questions/qa-forms-utf-8.en
 */
function IsValidUTF8Text($Text)
{
    if (mb_check_encoding($Text,'UTF-8'))
    {
        return TRUE;
    }

    return FALSE;
}
