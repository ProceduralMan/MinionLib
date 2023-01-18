<?php

/*
 * Work with local or remote filesystems
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
 * GetIanaTLDs
 * Extracts a current list of TLDs from IANA's website
 * Saves it as a CSV
 *      1 Check if CSV present 
 *      2 Check Retention
 *          2.1 If retention has passed read version from csv 3 and compare with online file version
 *              2.1.1 If online file is newer, write online file
 *      3 If CSV not present, write online file
 * @param   int     $Retention number of days to keep the last download
 * @param mixed $FileName
 * @param mixed $Path
 * @param null|mixed $Options
 * @return  mixed   The path to the data on success, FALSE on error
 * @since 1.0
 * @see
 * @todo
 */

/**
 * MBPathInfo
 * Works as pathinfo(), but multibyte-safe and cross-platform.
 *
 * @param   string  $Path    A filename or path, does not need to exist as a file
 * @param   int     $Options A PHP's PATHINFO_* constant, namely:
 *                      PATHINFO_DIRNAME - equals 1 -
 *                      PATHINFO_BASENAME - equals 2 -
 *                      PATHINFO_EXTENSION - equals 4 -
 *                      PATHINFO_FILENAME - equals 8 -
 * @return  mixed   Path part string or full pathinfo array, or FALSE if no info could be extracted
 * @since   0.0.9
 * @see
 *          https://github.com/PHPMailer/PHPMailer/blob/master/src/PHPMailer.php function mb_pathinfo
 *          http://www.php.net/manual/en/function.pathinfo.php#107461
 * @todo
 */
function MBPathInfo($Path, $Options = NULL)
{
    $Result = array();
    $PathInfo = array();
    if (preg_match('#^(.*?)[\\\\/]*(([^/\\\\]*?)(\.([^.\\\\/]+?)|))[\\\\/.]*$#m', $Path, $PathInfo))
    {
        if (array_key_exists(1, $PathInfo))
        {
            $Result['dirname'] = $PathInfo[1];
        }
        if (array_key_exists(2, $PathInfo))
        {
            $Result['basename'] = $PathInfo[2];
        }
        if (array_key_exists(5, $PathInfo))
        {
            $Result['extension'] = $PathInfo[5];
        }
        if (array_key_exists(3, $PathInfo))
        {
            $Result['filename'] = $PathInfo[3];
        }
    }
    else
    {
        //preg_match returns either 0 or FALSE. In any case we did not get any info
        return FALSE;
    }

    switch ($Options) {
        case PATHINFO_DIRNAME:
            return $Result['dirname'];
        case PATHINFO_BASENAME:
            return $Result['basename'];
        case PATHINFO_EXTENSION:
            return $Result['extension'];
        case PATHINFO_FILENAME:
            return $Result['filename'];
        default:
            return $Result;
    }
}


/**
 * MimeType
 * Finds the mime type of a file represented by a physical path or a URL
 * Defaults to 'application/octet-stream', i.e.. arbitrary binary data.
 *
 * @param   string  $FileName A file name, full path or URL, does not need to exist as a file
 * @return  string  The mime type of the file
 * @since   0.0.9
 * @see     
 *          https://github.com/PHPMailer/PHPMailer/blob/master/src/PHPMailer.php function mimetypes
 *          https://developer.mozilla.org/en-US/docs/Web/HTTP/Basics_of_HTTP/MIME_types/Common_types
 *          
 * @todo
 */
function MimeType($FileName)
{
    $MimeCodes = array(
        '3g2'       => 'video/3gpp2',
        '3gp'       => 'video/3gpp',
        '7z'        => 'application/x-7z-compressed',
        'aac'       => 'audio/aac',
        'abw'       => 'application/x-abiword',
        'ai'        => 'application/postscript',
        'aif'       => 'audio/x-aiff',
        'aifc'      => 'audio/x-aiff',
        'aiff'      => 'audio/x-aiff',
        'arc'       => 'application/x-freearc',
        'avi'       => 'video/x-msvideo',
        'avif'      => 'image/avif',
        'azw'       => 'application/vnd.amazon.ebook',
        'bin'       => 'application/macbinary',
        'bmp'       => 'image/bmp',
        'bz'        => 'application/x-bzip',
        'bz2'       => 'application/x-bzip2',
        'cda'       => 'application/x-cdf',
        'class'     => 'application/octet-stream',
        'cpt'       => 'application/mac-compactpro',
        'csh'       => 'application/x-csh',
        'css'       => 'text/css',
        'csv'       => 'text/csv',
        'dcr'       => 'application/x-director',
        'dir'       => 'application/x-director',
        'dll'       => 'application/octet-stream',
        'dms'       => 'application/octet-stream',
        'doc'       => 'application/msword',
        'docx'      => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'dotx'      => 'application/vnd.openxmlformats-officedocument.wordprocessingml.template',
        'dvi'       => 'application/x-dvi',
        'dxr'       => 'application/x-director',
        'eml'       => 'message/rfc822',
        'eot'       => 'application/vnd.ms-fontobject',
        'eps'       => 'application/postscript',
        'epub'      => 'application/epub+zip',
        'exe'       => 'application/octet-stream',
        'gif'       => 'image/gif',
        'gtar'      => 'application/x-gtar',
        'gz'        => 'application/gzip',
        'heic'      => 'image/heic',
        'heics'     => 'image/heic-sequence',
        'heif'      => 'image/heif',
        'heifs'     => 'image/heif-sequence',
        'hqx'       => 'application/mac-binhex40',
        'htm'       => 'text/html',
        'html'      => 'text/html',
        'ico'       => 'image/vnd.microsoft.icon',
        'ics'       => 'text/calendar',
        'jar'       => 'application/java-archive',
        'jpe'       => 'image/jpeg',
        'jpeg'      => 'image/jpeg',
        'jpg'       => 'image/jpeg',
        'js'        => 'application/javascript',
        'json'      => 'application/json',
        'jsonld'    => 'application/ld+json',
        'lha'       => 'application/octet-stream',
        'log'       => 'text/plain',
        'lzh'       => 'application/octet-stream',
        'm4a'       => 'audio/mp4',
        'm4v'       => 'video/mp4',
        'mid'       => 'audio/midi',
        'mid .midi' => 'audio/midi audio/x-midi',
        'midi'      => 'audio/midi',
        'mif'       => 'application/vnd.mif',
        'mjs'       => 'text/javascript',
        'mka'       => 'audio/x-matroska',
        'mkv'       => 'video/x-matroska',
        'mov'       => 'video/quicktime',
        'movie'     => 'video/x-sgi-movie',
        'mp2'       => 'audio/mpeg',
        'mp3'       => 'audio/mpeg',
        'mp4'       => 'video/mp4',
        'mpe'       => 'video/mpeg',
        'mpeg'      => 'video/mpeg',
        'mpg'       => 'video/mpeg',
        'mpga'      => 'audio/mpeg',
        'mpkg'      => 'application/vnd.apple.installer+xml',
        'oda'       => 'application/oda',
        'odp'       => 'application/vnd.oasis.opendocument.presentation',
        'ods'       => 'application/vnd.oasis.opendocument.spreadsheet',
        'odt'       => 'application/vnd.oasis.opendocument.text',
        'oga'       => 'audio/ogg',
        'ogv'       => 'video/ogg',
        'ogx'       => 'application/ogg',
        'opus'      => 'audio/opus',
        'otf'       => 'font/otf',
        'pdf'       => 'application/pdf',
        'php'       => 'application/x-httpd-php',
        'php3'      => 'application/x-httpd-php',
        'php4'      => 'application/x-httpd-php',
        'phps'      => 'application/x-httpd-php-source',
        'phtml'     => 'application/x-httpd-php',
        'png'       => 'image/png',
        'potx'      => 'application/vnd.openxmlformats-officedocument.presentationml.template',
        'ppsx'      => 'application/vnd.openxmlformats-officedocument.presentationml.slideshow',
        'ppt'       => 'application/vnd.ms-powerpoint',
        'pptx'      => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        'ps'        => 'application/postscript',
        'psd'       => 'application/octet-stream',
        'qt'        => 'video/quicktime',
        'ra'        => 'audio/x-realaudio',
        'ram'       => 'audio/x-pn-realaudio',
        'rar'       => 'application/vnd.rar',
        'rm'        => 'audio/x-pn-realaudio',
        'rpm'       => 'audio/x-pn-realaudio-plugin',
        'rtf'       => 'text/rtf',
        'rtx'       => 'text/richtext',
        'rv'        => 'video/vnd.rn-realvideo',
        'sea'       => 'application/octet-stream',
        'sh'        => 'application/x-sh',
        'shtml'     => 'text/html',
        'sit'       => 'application/x-stuffit',
        'sldx'      => 'application/vnd.openxmlformats-officedocument.presentationml.slide',
        'smi'       => 'application/smil',
        'smil'      => 'application/smil',
        'so'        => 'application/octet-stream',
        'svg'       => 'image/svg+xml',
        'swf'       => 'application/x-shockwave-flash',
        'tar'       => 'application/x-tar',
        'text'      => 'text/plain',
        'tgz'       => 'application/x-tar',
        'tif'       => 'image/tiff',
        'tiff'      => 'image/tiff',
        'ts'        => 'video/mp2t',
        'ttf'       => 'font/ttf',
        'txt'       => 'text/plain',
        'vcard'     => 'text/vcard',
        'vcf'       => 'text/vcard',
        'vsd'       => 'application/vnd.visio',
        'wav'       => 'audio/wav',
        'wbxml'     => 'application/vnd.wap.wbxml',
        'weba'      => 'audio/webm',
        'webm'      => 'video/webm',
        'webp'      => 'image/webp',
        'wmlc'      => 'application/vnd.wap.wmlc',
        'wmv'       => 'video/x-ms-wmv',
        'woff'      => 'font/woff',
        'woff2'     => 'font/woff2',
        'word'      => 'application/msword',
        'xht'       => 'application/xhtml+xml',
        'xhtml'     => 'application/xhtml+xml',
        'xl'        => 'application/excel',
        'xlam'      => 'application/vnd.ms-excel.addin.macroEnabled.12',
        'xls'       => 'application/vnd.ms-excel',
        'xlsb'      => 'application/vnd.ms-excel.sheet.binary.macroEnabled.12',
        'xlsx'      => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'xltx'      => 'application/vnd.openxmlformats-officedocument.spreadsheetml.template',
        'xml'       => 'application/xml',
        'xsl'       => 'text/xml',
        'xul'       => 'application/vnd.mozilla.xul+xml',
        'xwav'      => 'audio/x-wav',
        'zip'       => 'application/zip',
    );

    //In case the path is a URL, strip any query string before getting extension
    $QueryString = strpos($FileName, '?');
    if ($QueryString !== FALSE)
    {
        $FileName = substr($FileName, 0, $QueryString);
    }
    $Extension = MBPathInfo($FileName, PATHINFO_EXTENSION);

    if (array_key_exists(strtolower($Extension), $MimeCodes))
    {
        return $MimeCodes[$Extension];
    }
    else
    {
        return 'application/octet-stream';
    }
}
