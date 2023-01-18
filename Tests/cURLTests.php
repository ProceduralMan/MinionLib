<?php

/*
 * cURLTests
 * To test cURL functions 
 * @author ProceduralMan <proceduralman@gmail.com>
 * @copyright 2021
 * @version 1.0 initial version
 * @package Minion Library
 * @todo
 */

require_once __DIR__.'/../MinionSetup.php';

/*/*Some values to review
echo 'Remember that CURLOPT_NEW_DIRECTORY_PERMS is '.CURLOPT_NEW_DIRECTORY_PERMS.PHP_EOL;
echo 'Remember that CURLOPT_TELNETOPTIONS is '.CURLOPT_TELNETOPTIONS.PHP_EOL;
echo 'Remember that CURLINFO_HEADER_OUT is '.CURLINFO_HEADER_OUT.PHP_EOL;
echo 'Remember that CURLOPT_RETURNTRANSFER is '.CURLOPT_RETURNTRANSFER.PHP_EOL;
echo 'Remember that CURLOPT_PASSWDFUNCTION is '.CURLOPT_PASSWDFUNCTION.PHP_EOL;
echo 'Remember that CURLOPT_MUTE is '.CURLOPT_MUTE.' use CURLOPT_RETURNTRANSFER ('.CURLOPT_RETURNTRANSFER.') instead'.PHP_EOL;
echo 'Remember that CURLOPT_SAFE_UPLOAD is '.CURLOPT_SAFE_UPLOAD.PHP_EOL;
echo 'Remember that CURLOPT_FTPAPPEND is '.CURLOPT_FTPAPPEND.' as CURLOPT_APPEND is '.CURLOPT_APPEND.PHP_EOL;
echo 'Remember that CURLOPT_FTPLISTONLY is '.CURLOPT_FTPLISTONLY.' as CURLOPT_DIRLISTONLY is '.CURLOPT_DIRLISTONLY.PHP_EOL;
echo 'Remember that CURLOPT_SSLCERTPASSWD is '.CURLOPT_SSLCERTPASSWD.' as CURLOPT_KEYPASSWD is '.CURLOPT_KEYPASSWD.PHP_EOL;
echo 'Remember that CURLOPT_SSLKEYPASSWD is '.CURLOPT_SSLKEYPASSWD.' as CURLOPT_KEYPASSWD is '.CURLOPT_KEYPASSWD.PHP_EOL;
echo 'Remember that CURLOPT_KRB4LEVEL is '.CURLOPT_KRB4LEVEL.' as CURLOPT_KRBLEVEL is '.CURLOPT_KRBLEVEL.PHP_EOL;
echo 'Remember that CURLOPT_ENCODING is '.CURLOPT_ENCODING.' as CURLOPT_ACCEPT_ENCODING is '.CURLOPT_ACCEPT_ENCODING.PHP_EOL;
echo 'Remember that CURLOPT_FILE is '.CURLOPT_FILE.' as CURLOPT_WRITEDATA is '.CURLOPT_WRITEDATA.PHP_EOL;
echo 'Remember that CURLOPT_INFILE is '.CURLOPT_INFILE.' as CURLOPT_READDATA is '.CURLOPT_READDATA.PHP_EOL;
echo 'Remember that CURLOPT_WRITEHEADER is '.CURLOPT_WRITEHEADER.' as CURLOPT_HEADERDATA is '.CURLOPT_HEADERDATA.PHP_EOL;

exit(0);*/

//Simple GET
echo '1.- Simple GET'.PHP_EOL;
$URL = 'https://dog-api.kinduff.com/api/facts';

$Result = cURLSimpleGET($URL);
echo 'Calling '.$URL.' with simple GET responds '.$Result.PHP_EOL;
//Full GET
echo '2.- Full GET'.PHP_EOL;

$Parameters = array('number' => 5);

$Result2 = cURLFullGET($URL, $Parameters);
echo 'Calling '.$URL.' with full GET responds '.$Result2.PHP_EOL;
//Simple HEAD
echo '3.- Simple HEAD'.PHP_EOL;
$Result3 = cURLSimpleHEAD($URL);
echo 'Calling '.$URL.' with simple HEAD responds '.$Result3.PHP_EOL;

//Full HEAD
echo '4.- Full HEAD'.PHP_EOL;
$Result4 = cURLFullHEAD($URL, $Parameters);
echo 'Calling '.$URL.' with full HEAD responds '.$Result4.PHP_EOL;

$URL2 = 'https://httpbin.org/post';

//Simple POST
echo '5.- Simple POST'.PHP_EOL;
$Result5 = cURLSimplePOST($URL2);
echo 'Calling '.$URL2.' with simple POST responds '.$Result5.PHP_EOL;

//Full POST, UrlEncoded
$Parameters2 = array(
    'custname'  => 'Fake customer',
    'custtel'   => '+1555555',
    'custemail' => 'fake.customer@email.com',
    'size'      => 'large',
    'topping'   => 'bacon',
    'topping'   => 'onion',
    'delivery'  => '20:30',
    'comments'  => 'Fake delivery instructions'
);
echo '6.- Full POST application/x-www-form-urlencoded'.PHP_EOL;
$Result6 = cURLFullPOST($URL2, $Parameters2, NULL, TRUE);
echo 'Calling '.$URL2.' with full POST (application/x-www-form-urlencoded) responds'.$Result6.PHP_EOL;

//Full POST, form data
echo '7.- Full POST multipart/form-data'.PHP_EOL;
$Result7 = cURLFullPOST($URL2, $Parameters2, NULL, FALSE);
echo 'Calling '.$URL2.' with full POST (multipart/form-data) responds'.$Result7.PHP_EOL;

$URL3 = 'https://blog.marcnuri.com/index.html';
$Destination = '/tmp/FileGotten.txt';

//Simple file GET
echo '8.- Simple file GET';
$Result8 = cURLSimpleFileGET($URL3, $Destination);
echo 'Simple GET of '.$URL3.' responds'.$Result8.PHP_EOL;

//Full file GET
echo '9.- Full file GET';
$Result9 = cURLFileGET($URL3, $Destination, NULL);
echo 'Full GET of '.$URL3.' responds'.$Result9.PHP_EOL;


$URL4 = 'http://localhost:88/uploads/uploaded.pdf';
$Origin = __DIR__.'/EmailingSampleAttachment.pdf';

/*
 * File PUT MUST include a freely accesible directory (enable basic auth and include it on the URL)
 * Example for a PUBLIC directory (no auth needed) on NGINX
 *         location ~ "/uploads/([0-9a-zA-Z-.]*)$" {
 *                client_body_temp_path  /tmp/upl_tmp;
 *                dav_methods  PUT DELETE MKCOL COPY MOVE;
 *                create_full_put_path   on;
 *                dav_access             group:rw  all:r;
 *       }
 */
//Simple file PUT
echo '10.- Simple file PUT';
$Result10 = cURLSimpleFilePUT($Origin, $URL4);
echo 'Simple PUT of '.$URL4.' responds'.$Result10.PHP_EOL;

//Full file PUT
echo '11.- Full file PUT';
$URL5 = 'http://localhost:88/uploads/uploaded2.pdf';
$Result11 = cURLFilePUT($Origin, $URL5, NULL);
echo 'PUT of '.$URL5.' responds'.$Result11.PHP_EOL;

/*
 * File POST MUST include a server-side script to receive the file, such as:
 * <?php
 * // SERVER B - RECEIVE FILE UPLOAD
 * echo "SERVER B FILE UPLOAD - ";
 * //print_r($_FILES);
 * //post_name looses the path when transferred to $_FILES["upload"]["name"] so we force it
 * $Destination = 'uploads/';
 * $Result = move_uploaded_file($_FILES["upload"]["tmp_name"], $Destination.$_FILES["upload"]["name"]);
 * if ($Result  === TRUE)
 * {
 *     echo 'OK';
 * }
 * else
 * {
 *     echo 'ERROR';
 * }
 */
$URL6 = 'http://localhost:88/subidor.php';
//Simple file POST
$Destination2 = 'uploads/uploaded3.pdf';
echo '12.- Simple file POST';
$Result12 = cURLSimpleFilePOST($Origin, $URL6, $Destination2);
echo 'Simple POST of '.$URL6.' responds'.$Result12.PHP_EOL;

//Full file POST
$Destination3 = 'uploads/uploaded4.pdf';
echo '13.- Full file POST';
$Result13 = cURLFilePOST($Origin, $URL6, $Destination3, NULL);
echo 'Full POST of '.$URL6.' responds'.$Result13.PHP_EOL;
