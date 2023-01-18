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

$TestSuiteIsValidHost = array(
    'localhost',                                                                        //localhost
    'localhot',                                                                      //Malformed localhost
    '127.0.0.1',                                                                        //Loopback
    '127.0.01',                                                                         //Malformed loopback
    '127.0.1.1',                                                                        //Another loopback
    '127,0,1,1',                                                                        //Malformed alt loopback
    '192.168.1.5',                                                                      //Private IPv4
    '192,168.1.6',                                                                      //Malformed private IPv4
    '213.229.163.7',                                                                    //Public IPv4
    '213.229.163.007',                                                                  //Padded public IPv4
    '0:0:0:0:0:0:0:1',                                                                  //IPv6 loopback
    '::1',                                                                              //Condensed IPv6 loopback
    '2001:0db8:85a3:0000:0000:8a2e:0370:7334',                                          //Valid IPv6
    '2001:0db8:85a3:0000:0000:8a2e:7334',                                               //Malformed IPv6
    '2001:db8:85a3:0:0:8a2e:370:7334',                                                  //Summarized IPv6
    '2001:85a3:0:0:8a2e:370:7334',                                                      //Malformed summarized IPv6
    '2001:db8:85a3::8a2e:370:7334',                                                     //Ultra reduced IPv6
    '2001:85a3::8a2e:370:7334',                                                         //Malformed ultra reduced IPv6
    '::ffff:192.0.2.128',                                                               //IPv4-mapped IPv6
    '::ffff:192.0.2128',                                                                //Malformed IPv4-mapped IPv6
    '::192.0.2.128',                                                                    //IPv4-mapped malformed IPv6
    'heavymetal.com',                                                                   //Domain name
    'suburban.heavymetal.com',                                                          //Hostname
    'suburban.heavymetal.internal',                                                          //Internal Hostname
    'multiple.dotted.hostnames.are.also.valid.but.should.end.with.an.iana.tld.such.as.com',     //Multiple-dotted name
    'ExtremelyLongHostnameBiggerThanSixtyThreeCharsSuchAsAfroItaloAmerican.heavymetal.com',     //Hostname with extremely long label
    'ThisHostnameRepeats.Itself.Until.Bigger.Than.253.chars.ThisHostnameRepeats.Itself.Until.Bigger.Than.253.chars.ThisHostnameRepeats.Itself.Until.Bigger.Than.253.chars.ThisHostnameRepeats.Itself.Until.Bigger.Than.253.chars.ThisHostnameRepeats.Itself.Until.Bigger.Than.253.chars.com'
);

$TestSuiteIsValidAlias = array(
    'ThisIsAValidAlias',                //A valid alias name
    '',                                 //Invalid -empty- alias
    'Bad.Dotted.Unquoted.Alias',        //An unquoted alias with dots
    'BAD/AliasWithForwardSlash',        //An unquoted alias with an asterisk
    'BAD?AliasWithInterrogation',       //An unquoted alias with an interrogation
    '928374928437',                     //An unquoted all-numeric alias
    'ThisIsAnAliasSoBigThatIsNotValidThisIsAnAliasSoBigThatIsNotValidThisIsAnAliasSoBigThatIsNotValidThisIsAnAliasSoBigThatIsNotValidThisIsAnAliasSoBigThatIsNotValidThisIsAnAliasSoBigThatIsNotValidThisIsAnAliasSoBigThatIsNotValidThisIsAnAliasSoBigThatIsNotValidThisIsAnAliasSoBigThatIsNotValid', //A lengthy Alias
    '`ThisIsAValidAlias`',              //A quoted valid alias name
    '``',                               //Invalid -empty- quoted alias
    '`Bad.Dotted.Unquoted.Alias`',      //A quoted alias with dots
    '`BAD/AliasWithForwardSlash`',      //A quoted alias with an asterisk
    '`BAD?AliasWithInterrogation`',     //A quoted alias with an interrogation
    '`928374928437`',                   //A quoted all-numeric alias
    '`ThisIsAnAliasSoBigThatIsNotValidThisIsAnAliasSoBigThatIsNotValidThisIsAnAliasSoBigThatIsNotValidThisIsAnAliasSoBigThatIsNotValidThisIsAnAliasSoBigThatIsNotValidThisIsAnAliasSoBigThatIsNotValidThisIsAnAliasSoBigThatIsNotValidThisIsAnAliasSoBigThatIsNotValidThisIsAnAliasSoBigThatIsNotValid`' //A lengthy quoted alias
);

$TestSuiteIsValidEntity = array(
    'ThisIsAValidEntityExceptUser',                                                 //A valid entity name
    '',                                                                             //Invalid -empty- entity name
    'Bad.Dotted.Unquoted.Alias',                                                    //An unquoted entity name with dots
    'BAD/AliasWithForwardSlash',                                                    //An unquoted entity name with an asterisk
    'BAD?AliasWithInterrogation',                                                   //An unquoted entity name with an interrogation
    '928374928437',                                                                 //An unquoted all-numeric entity name
    'ThisIsAnEntityNameSoBigThatIsNotValidThisIsAnEntityNameSoBigThatIsNotValid',   //A lengthy entity name
    '`ThisIsAValidAlias`',                                                          //A quoted valid entity name
    '``',                                                                           //Invalid -empty- quoted entity name
    '`Bad.Dotted.Quoted.Alias`',                                                    //A quoted entity name with dots
    '`BAD/AliasWithForwardSlash`',                                                  //A quoted entity name with an asterisk
    '`BAD?AliasWithInterrogation`',                                                 //A quoted entity name with an interrogation
    '`928374928437`',                                                               //A quoted all-numeric entity name
    '`ThisIsAnEntityNameSoBigThatIsNotValidThisIsAnEntityNameSoBigThatIsNotValid`'  //A lengthy quoted entity name
);

$TestSuiteIsValidUser = array(
    'ThisIsAValidUser',                     //A valid user name
    '',                                     //Invalid -empty- user name
    'Dotted.Unquoted.User',                 //An unquoted user name with dots
    'User/WithForwardSlash',                //An unquoted user name with an asterisk
    '?UserWithInterrogation',               //An unquoted user name with an interrogation
    'Unquoted_Hyphenized_User',             //An unquoted hyphenized user name
    'Unquoted*Asterisk*User',               //An unquoted user name with asterisks
    '928374928437',                         //An unquoted all-numeric user name
    'ThisIsAUserNameSoBigThatIsNotValid',   //A lengthy entity name
    '`ThisIsAValidUser`',                   //A quoted valid user name
    '``',                                   //Invalid -empty- quoted entity name
    '`Dotted.Quoted.User`',                 //A quoted entity name with dots
    '`User/WithForwardSlash`',              //A quoted entity name with an asterisk
    '`?UserWithInterrogation`',             //A quoted entity name with an interrogation
    '`Quoted_Hyphenized_User`',             //A quoted hyphenized user name
    '`Quoted*Asterisk*User`',               //A quoted user name with asterisks
    '`928374928437`',                       //A quoted all-numeric entity name
    '`ThisIsAUserNameSoBigThatIsNotValid`'  //A lengthy quoted entity name
);

$TestSuiteIsValidPort = array(
    -1,     //An invalid, negative port
    237,    //A valid port
    70000   //An invalid, too big port
);

$TestAdequateDBPort = array(
    3306,   //Standard MySQL Port
    8550,   //Unassigned, valid port
    8554,   //A port assigned to other service
    1783,   //A reserved port
    55555   //An ephemeral port
);

$TestSuiteIsValidURI = array(
    "",
    "Buy It Now",
    "localhost/foo/bar",
    "blarg",
    "blarg/",
    "blarg/some/path/file.ext",
    "http://google.com",
    "http://google.com/",
    "http://google.com/some/path.ext",
    "http://google.com/some/path.ext?foo=bar",
    "example.com",
    "example.com/",
    "example.com/some/path/file.ext",
    "example.com/some/path/file.ext?foo=bar",
    "example.com:1234",
    "example.com:1234/",
    "example.com:1234/some/path/file.ext",
    "example.com:1234/some/path/file.ext?foo=bar",
    "//foobar.com",
    "//foobar.com/",
    "//foobar.com/path/file.txt",
    "//cdn.example.com/js_file.js",
    "http://example.com?id=some-file-id"
);

echo PHP_EOL.PHP_EOL.'Testing UTF8 text'.PHP_EOL;

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

echo PHP_EOL.PHP_EOL.'Testing internal hostnames'.PHP_EOL;

foreach ($TestSuiteIsValidHost as $Value)
{
    if (IsValidHost($Value, FALSE) === TRUE)
    {
        echo $Value.' is a valid internal MySQL Host -no IANA checking-'.PHP_EOL;
    }
    else
    {
        echo $Value.' *IS NOT* a valid internal MySQL Host  -no IANA checking-'.PHP_EOL;
    }
}

echo PHP_EOL.PHP_EOL.'Testing public hostnames'.PHP_EOL;

foreach ($TestSuiteIsValidHost as $Value)
{
    if (IsValidHost($Value, TRUE) === TRUE)
    {
        echo $Value.' is a valid public MySQL Host'.PHP_EOL;
    }
    else
    {
        echo $Value.' *IS NOT* a valid public MySQL Host'.PHP_EOL;
    }
    /*
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
    }*/
}

echo PHP_EOL.PHP_EOL.'Testing MySQL Aliases'.PHP_EOL;
foreach ($TestSuiteIsValidAlias as $Value)
{
    //echo 'Testing '.$Value.'=>';
    if (IsValidMySQLName($Value, TRUE) === TRUE)
    {
        echo $Value.' is valid MySQL ALias'.PHP_EOL;
    }
    else
    {
        if (empty($Value))
        {
            echo '<empty> *IS NOT* valid MySQL Alias'.PHP_EOL;
        }
        else
        {
            echo $Value.' *IS NOT* valid MySQL Alias'.PHP_EOL;
        }
    }
}

echo PHP_EOL.PHP_EOL.'Testing MySQL Entities (except aliases and users)'.PHP_EOL;
foreach ($TestSuiteIsValidEntity as $Value)
{
    //echo 'Testing '.$Value.'=>';
    if (IsValidMySQLName($Value, FALSE) === TRUE)
    {
        echo $Value.' is valid MySQL Entity'.PHP_EOL;
    }
    else
    {
        if (empty($Value))
        {
            echo '<empty> *IS NOT* valid MySQL Entity'.PHP_EOL;
        }
        else
        {
            echo $Value.' *IS NOT* valid MySQL Entity'.PHP_EOL;
        }
    }
}

echo PHP_EOL.PHP_EOL.'Testing MySQL Users'.PHP_EOL;
foreach ($TestSuiteIsValidUser as $Value)
{
    //echo 'Testing '.$Value.'=>';
    if (IsValidMySQLUser($Value) === TRUE)
    {
        echo $Value.' is valid MySQL User'.PHP_EOL;
    }
    else
    {
        if (empty($Value))
        {
            echo '<empty> *IS NOT* valid MySQL User'.PHP_EOL;
        }
        else
        {
            echo $Value.' *IS NOT* valid MySQL User'.PHP_EOL;
        }
    }
}

echo PHP_EOL.PHP_EOL.'Testing Valid Port'.PHP_EOL;
foreach ($TestSuiteIsValidPort as $Value)
{
    if (IsValidIANAPort($Value) === TRUE)
    {
        echo $Value.' is valid IANA Port'.PHP_EOL;
    }
    else
    {
        echo $Value.' *IS NOT* valid IANA Port'.PHP_EOL;
    }
}

echo PHP_EOL.PHP_EOL.'Testing Adequate Database Port'.PHP_EOL;
//Other protocol
$Result = IsAdequateDatabasePort(MIL_MYSQL, 8550, "UDP");
echo 'Port is '.$Result['code'].' due to '.$Result['explanation'].PHP_EOL;

//TestSuite
foreach ($TestAdequateDBPort as $Value)
{
    $Result = IsAdequateDatabasePort(MIL_MYSQL, $Value, "TCP");
    echo 'Port '.$Value.' is '.$Result['code'].' due to '.$Result['explanation'].PHP_EOL;
}

//Testing Valid URIs
echo PHP_EOL.PHP_EOL.'Testing URIs'.PHP_EOL;

foreach ($TestSuiteIsValidURI as $Value)
{
    if (IsValidURI($Value) === TRUE)
    {
        echo $Value.' is a valid RFC 3986 URI/URL'.PHP_EOL;
    }
    else
    {
        echo $Value.' *IS NOT* a valid RFC 3986 URI/URL'.PHP_EOL;
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
