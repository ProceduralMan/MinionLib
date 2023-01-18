<?php

/*
 * EmailingTests
 * To test Emailing functions 
 * @author ProceduralMan <proceduralman@gmail.com>
 * @copyright 2022
 * @version 1.0 initial version
 * @package Minion Library
 * @todo
 */
//LO CARGA EL AUTOLOADER require_once __DIR__.'/../MinionSetup.php';
require_once __DIR__.'/../vendor/autoload.php';

define("MIL_PHPMAILER_LANG", 'es');
define("MIL_PHPMAILER_USERNAME", "info@consabor.es");
define("MIL_PHPMAILER_PASSWORD", "AYrTMjnCooCewukuxV6ptmXtqTXXbP4w0xet6y8q");
/*
$HTMLText = 'A simple email as text.<br>To test emailing functions';
$Subject = 'Testing email functions: An email as simple as possible';


//An email as simple as possible
$Result = SimpleHTMLEmail('jmcollantes@gmail.com', 'consabor', $Subject, $HTMLText);
if ($Result === FALSE)
{
    echo 'Error sending email'.PHP_EOL;
}

//Same email specifying credentials
$Result2 = SimpleHTMLEmailWC(MIL_PHPMAILER_USERNAME, MIL_PHPMAILER_PASSWORD, 'jmcollantes@gmail.com', 'consabor', $Subject, $HTMLText);
if ($Result2 === FALSE)
{
    echo 'Error sending email'.PHP_EOL;
}

//Simple email with options set, simple addresses
$AltText = 'Alternate text body for email clients that do not allow HTML';
$CC = 'choosenorisk@gmail.com';
$BCC = 'josemiguel@likeik.com';
$Result3 = SimpleHTMLEmail('jmcollantes@gmail.com', 'consabor', $Subject, $HTMLText, $AltText, $CC, $BCC);
if ($Result3 === FALSE)
{
    echo 'Error sending email'.PHP_EOL;
}

//Simple email with options set, arrays
$AltText2 = 'Alternate text body for email clients that do not allow HTML';
$To2 = array('jmcollantes@gmail.com' => 'Mia de Gmail');
$CC2 = array('choosenorisk@gmail.com' => 'ChooseNoRisk');
$BCC2 = array('josemiguel@likeik.com' => 'MiadelCurro');
$Result4 = SimpleHTMLEmail($To2, 'consabor', $Subject, $HTMLText, $AltText2, $CC2, $BCC2);
if ($Result4 === FALSE)
{
    echo 'Error sending email'.PHP_EOL;
}

//Simple email with options set, credentials included
$AltText3 = 'Alternate text body for email clients that do not allow HTML';
$To3 = array('jmcollantes@gmail.com' => 'Mia de Gmail');
$CC3 = array('choosenorisk@gmail.com' => 'ChooseNoRisk');
$BCC3 = array('josemiguel@likeik.com' => 'MiadelCurro');
$Result5 = SimpleHTMLEmailWC(MIL_PHPMAILER_USERNAME, MIL_PHPMAILER_PASSWORD, $To3, 'consabor', $Subject, $HTMLText, $AltText3, $CC3, $BCC3);
if ($Result5 === FALSE)
{
    echo 'Error sending email'.PHP_EOL;
}
*/

//Complete email with images and attachements
$Subject4 = 'Welcome to Rica Panamá Travel Agency';
$HTMLText4 = file_get_contents(__DIR__.'/EmailingSampleEmail.html');
$AltText4 = NULL; //Function will prepare it's own text version
$To4 = array('jmcollantes@gmail.com' => 'Mia de Gmail');
$CC4 = array('choosenorisk@gmail.com' => 'ChooseNoRisk');
$BCC4 = array('josemiguel@likeik.com' => 'MiadelCurro');
$Image1 = array(0 => __DIR__.'/EmailingSampleImage.png',    //filepath
    1             => 'RPHeader',                            //cid
    2             => 'TravelHeader',                        //name
    3             => 'base64',                              //encoding: '7bit', '8bit', 'base64' -default-, 'binary' or 'quoted-printable'
    4             => NULL                                   //Let the function detect the Mime type based on the extension
);
$Images = array(0 => $Image1);
$Attach1 = array(0 => __DIR__.'/EmailingSampleAttachment.pdf',  //filepath
    1              => NULL,                                     //cid space, ignored on attachments
    2              => 'PanamaHistory',                           //name
    3              => 'base64',                                 //encoding: '7bit', '8bit', 'base64' -default-, 'binary' or 'quoted-printable'
    4              => 'application/pdf'                         //Or set one yourself
);
$Attachments = array(0 => $Attach1);

$Tags = array(0 => 'Registry', 1 => 'RicoPanama');

$Result6 = CompleteHTMLEmail($To4, 'consabor', $Subject4, $HTMLText4, $AltText4, $Images, $Attachments, $Tags, $CC4, $BCC4);
if ($Result6 === FALSE)
{
    echo 'Error sending email'.PHP_EOL;
}
