<?php

/*
 * TextAndString
 * Text ans String utility functions
 *
 * - Enclosure detects if the string is enclosed by something
 * - IsValidUTF8Text validates if the text complies with UTF-8
 * @author ProceduralMan <proceduralman@gmail.com>
 * @copyright 2021
 * @version 1.0 initial version
 * @package Minion
 * @todo
 * @see
 *
 */

//Quotes
define("MIL_QUOTES_NONE", 0);     // No quotes
define("MIL_QUOTES_SINGLE", 10);    // Single quotes '
define("MIL_QUOTES_DOUBLE", 20);    // Double quotes "
define("MIL_QUOTES_BACKTICK", 30);    // Backtick quotes `
define("MIL_QUOTES_PARENTHESES", 40);    // Parentheses ()
define("MIL_QUOTES_BRACES", 50);    // Curly brackets or braces {}
define("MIL_QUOTES_BRACKETS", 60);    // Square brackets or brackets []

/**
 * Enclosure detects if the string is enclosed by something
 * @param   string  $String
 * @return  string  Enclosure char
 * @since 0.0.1
 * @see
 * @todo
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
 * IsValidUTF8Text validates if the text complies with UTF-8
 * @param type $Text
 * @return boolean
 * @since 0.0.1
 * @see https://www.w3.org/International/questions/qa-forms-utf-8.en
 * @todo
 */
function IsValidUTF8Text($Text)
{
    if (DEBUGMODE)
    {
        echo date("Y-m-d H:i:s").' -> IsValidUTF8Text'.PHP_EOL;
    }

    if (mb_check_encoding($Text, 'UTF-8'))
    {
        return TRUE;
    }

    return FALSE;
}

/**
 * CloseCommaDelimitedList takes the last comma from the list and adds a closure char/string
 * Assumes an string ending in ',' or ' ,'
 * @param   string  $TheList    the comma-delimited string
 * @param   string  $Closure    the closure to add
 * @return  string  the comma-delimited string
 * @since 0.0.7
 * @see
 * @todo
 */
function CloseCommaDelimitedList($TheList, $Closure)
{
    //1.- take out extra spaces
    $TheList2 = rtrim($TheList);
    //2.- Take out the comma
    $TheList3 = substr($TheList2, 0, strlen($TheList2)-1);
    //3.- Take any loosen spaces and close the list
    $TheList4 = rtrim($TheList3).$Closure;

    return $TheList4;
}
