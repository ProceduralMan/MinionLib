<?php

/*
 * TextAndStringTests
 * To test Text and String helper functions 
 * @author ProceduralMan <proceduralman@gmail.com>
 * @copyright 2021
 * @version 1.0 initial version
 * @package Minion Library
 * @todo
 */

require_once __DIR__.'/../MinionSetup.php';

$TestSuiteEnclosure = array(
    "NonEnclosed",
    "'EnclosedBySingleQuotes'",
    "'BeginsWithSingleQuotes",
    "EndsWithSingleQuotes'",
    '"EnclosedByDoubleQuotes"',
    '"BeginsWithDoubleQuotes',
    'EndsWithDoubleQuotes"',
    "`EnclosedByBackTicks`",
    "`BeginsWithBackTick",
    "EndsWithBackTick`",
    '(EnclosedByParentheses)',
    '(BeginsWithParentheses',
    'EndsWithParentheses)',
    '{EnclosedByBraces}',
    '{BeginsWithBrace',
    'EndsWithBrace}',
    '[EnclosedByBrackets]',
    '[BeginsWithBrace',
    'EndsWithBrace]',
    '`Mixed1"',
    "'Mixed2)",
    '(Mixed3}',
    '{Mixed4)',
    '[Mixed5"',
    '[Mixed6}',
    '(Mixed7"',
    '"там админ жид куянский"', //Multibyte test 1
    '`там админ жид куянский`', //Multibyte test 2
    '(там админ жид куянский)'  //Multibyte test 3
);

$TestSuiteUTF8 = array(
    'Normal Latin1 Text',
    'Accented Ón Tèxt',
    'Ñö with country-espeçific',
    'там админ жид куянский',
    '🙈🙈😰😰🥘😡',
    'Mixed🙈😰😰🥘',
    chr(0xC0).chr(0x80),                                                  // Overlong encoding of code point 0
    chr(0xF8).chr(0x80).chr(0x80).chr(0x80).chr(0x80),              // Overlong encoding of 5 byte encoding
    chr(0xFC).chr(0x80).chr(0x80).chr(0x80).chr(0x80).chr(0x80),  // Overlong encoding of 6 byte encoding
    chr(0xD0).chr(0x01)                                                   // High code-point without trailing characters
);

foreach ($TestSuiteEnclosure as $Value)
{
    switch (Enclosure($Value))
    {
        case 0:
            echo $Value.' is not enclosed'.PHP_EOL;
            break;
        case 10:
            echo $Value.' is enclosed by single quotes'.PHP_EOL;
            break;
        case 20:
            echo $Value.' is enclosed by double quotes'.PHP_EOL;
            break;
        case 30:
            echo $Value.' is is enclosed by backticks'.PHP_EOL;
            break;
        case 40:
            echo $Value.' is enclosed by parentheses'.PHP_EOL;
            break;
        case 50:
            echo $Value.' is enclosed by braces'.PHP_EOL;
            break;
        case 60:
            echo $Value.' is enclosed by brackets'.PHP_EOL;
            break;
        default:
            echo $Value.'... Hmm, something fishy here. Please fill an issue at project <github URL>'.PHP_EOL;
            break;
    }
}

foreach ($TestSuiteUTF8 as $Value)
{
    if (IsValidUTF8Text($Value) === TRUE)
    {
        echo $Value.' is valid UTF-8 text'.PHP_EOL;
    }
    else
    {
        echo $Value.' *IS NOT* valid UTF-8 text'.PHP_EOL;
    }
    if (IsValidUTF8TextAlt($Value) === TRUE)
    {
        echo '(ALT) '.$Value.' is valid UTF-8 text'.PHP_EOL;
    }
    else
    {
        echo '(ALT) '.$Value.' *IS NOT* valid UTF-8 text'.PHP_EOL;
    }
    if (IsValidUTF8TextLong($Value) === TRUE)
    {
        echo '(LONG) '.$Value.' is valid UTF-8 text'.PHP_EOL;
    }
    else
    {
        echo '(LONG) '.$Value.' *IS NOT* valid UTF-8 text'.PHP_EOL;
    }
}


/**
 * IsValidUTF8TextAlt uses preg_match('//u') as an alternative to mb_check_encoding for checking... (spoiler) no differences
 * @param string $Text
 * @return boolean TRUE for valid UTF-8, FALSe otherwise
 */
function IsValidUTF8TextAlt($Text)
{
    //if (preg_match('/%^(?:[\x09\x0A\x0D\x20-\x7E] | [\xC2-\xDF][\x80-\xBF] | \xE0[\xA0-\xBF][\x80-\xBF] | [\xE1-\xEC\xEE\xEF][\x80-\xBF]{2} | \xED[\x80-\x9F][\x80-\xBF] | \xF0[\x90-\xBF][\x80-\xBF]{2} | [\xF1-\xF3][\x80-\xBF]{3} | \xF4[\x80-\x8F][\x80-\xBF]{2})*$%xs/', $Text) === TRUE)
    //if (preg_match('/\A([\x00-\x7F]|[\xC2-\xDF][\x80-\xBF]|\xE0[\xA0-\xBF][\x80-\xBF]|[\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}|\xED[\x80-\x9F][\x80-\xBF]|\xF0[\x90-\xBF][\x80-\xBF]{2}|[\xF1-\xF3][\x80-\xBF]{3}|\xF4[\x80-\x8F][\x80-\xBF]{2})*\z/', $Text) === TRUE)
    if (preg_match('//u', $Text))
    {
        return TRUE;
    }

    return FALSE;
}

/**
 * IsValidUTF8TextLong uses long regexp as an alternative to mb_check_encoding for checking... (spoiler) no differences
 * @param string $Text
 * @return boolean TRUE for valid UTF-8, FALSe otherwise
 * @see https://stackoverflow.com/questions/11709410/regex-to-detect-invalid-utf-8-string
 */
function IsValidUTF8TextLong($Text)
{
    //if (preg_match('/%^(?:[\x09\x0A\x0D\x20-\x7E] | [\xC2-\xDF][\x80-\xBF] | \xE0[\xA0-\xBF][\x80-\xBF] | [\xE1-\xEC\xEE\xEF][\x80-\xBF]{2} | \xED[\x80-\x9F][\x80-\xBF] | \xF0[\x90-\xBF][\x80-\xBF]{2} | [\xF1-\xF3][\x80-\xBF]{3} | \xF4[\x80-\x8F][\x80-\xBF]{2})*$%xs/', $Text) === TRUE)
    //if (preg_match('/\A([\x00-\x7F]|[\xC2-\xDF][\x80-\xBF]|\xE0[\xA0-\xBF][\x80-\xBF]|[\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}|\xED[\x80-\x9F][\x80-\xBF]|\xF0[\x90-\xBF][\x80-\xBF]{2}|[\xF1-\xF3][\x80-\xBF]{3}|\xF4[\x80-\x8F][\x80-\xBF]{2})*\z/', $Text) === TRUE)
    if (preg_match('/(
    [\xC0-\xC1] # Invalid UTF-8 Bytes
    | [\xF5-\xFF] # Invalid UTF-8 Bytes
    | \xE0[\x80-\x9F] # Overlong encoding of prior code point
    | \xF0[\x80-\x8F] # Overlong encoding of prior code point
    | [\xC2-\xDF](?![\x80-\xBF]) # Invalid UTF-8 Sequence Start
    | [\xE0-\xEF](?![\x80-\xBF]{2}) # Invalid UTF-8 Sequence Start
    | [\xF0-\xF4](?![\x80-\xBF]{3}) # Invalid UTF-8 Sequence Start
    | (?<=[\x00-\x7F\xF5-\xFF])[\x80-\xBF] # Invalid UTF-8 Sequence Middle
    | (?<![\xC2-\xDF]|[\xE0-\xEF]|[\xE0-\xEF][\x80-\xBF]|[\xF0-\xF4]|[\xF0-\xF4][\x80-\xBF]|[\xF0-\xF4][\x80-\xBF]{2})[\x80-\xBF] # Overlong Sequence
    | (?<=[\xE0-\xEF])[\x80-\xBF](?![\x80-\xBF]) # Short 3 byte sequence
    | (?<=[\xF0-\xF4])[\x80-\xBF](?![\x80-\xBF]{2}) # Short 4 byte sequence
    | (?<=[\xF0-\xF4][\x80-\xBF])[\x80-\xBF](?![\x80-\xBF]) # Short 4 byte sequence (2)
)/x', $Text))
    {
        return FALSE;
    }

    return TRUE;
}
