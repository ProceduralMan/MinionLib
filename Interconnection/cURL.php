<?php

/*
 * cURL utility functions
 * Functions to make operations via cURL
 * cURL has resulted being really sensible to optionbs order, so if the 'Simple' version that holds the required
 * is not enough for you and the 'Full' version fails, try changing the order of your cURLOptions or open an issue
 * at the github repo
 * @author ProceduralMan <proceduralman@gmail.com>
 * @copyright 2022
 * @version 1.0 initial version
 * @package Minion
 * @todo usar curl version para validar
 *          PUT, DELETE Sample (https://www.php.net/manual/en/function.curl-setopt.php#96056)
 *          FTP sample (https://www.php.net/manual/en/function.curl-setopt.php#121973)
 *          POST sample (https://www.php.net/manual/en/function.curl-setopt.php#93351)
 *          SOAP basic auth sample: (https://www.php.net/manual/en/function.curl-setopt.php#80271)
 *          CURL official samples (https://curl.se/libcurl/c/example.html)
 *          cURL multiple async requests (https://zetcode.com/php/curl/)
 * @see  
 *  
 */


/**
 * cURLWarn
 * Raises a warning on an undocumented/obsolete/deprecated cURL option
 * @param   type $Key
 * @param   boolean   $Undocumented   TRUE for undocumented functions
 * @param   mixed     $Obsolete       FALSE for non-obsolete but yet undeprecated functions. The proposed function name if key is obsoleted
 * @param   mixed     $Deprecated     FALSE for deprecated functions. An structure composed of deprecated-since#alternate function name if key is deprecated
 * @return  void
 * @since 0.0.9
 * @todo
 * @see
 */
function cURLWarn($Key, $Undocumented, $Obsolete, $Deprecated)
{
    if (DEBUGMODE)
    {
        echo date("Y-m-d H:i:s").' -> cURLWarn '.PHP_EOL;
    }

    if ($Undocumented === TRUE)
    {
        $Message = $Key.' is undocumented as of PHP 8.1.10 and may render unexpected results.';
        ErrorLog($Message, E_USER_WARNING);
    }

    if ($Obsolete !== FALSE)
    {
        $Message = $Key.' is considered obsolete as of cURL 7.85.0; consider using '.$Obsolete.' instead.';
        ErrorLog($Message, E_USER_WARNING);
    }

    if ($Deprecated !== FALSE)
    {
        $Cachos = explode('#', $Deprecated);
        if (empty($Cachos[0])&&empty($Cachos[1]))
        {
            $Message = $Key.' is deprecated.';
        }
        elseif (empty($Cachos[0]))
        {
            $Message = $Key.' is deprecated; consider using '.$Cachos[1].' instead.';
        }
        elseif (empty($Cachos[1]))
        {
            $Message = $Key.' is deprecated since cURL '.$Cachos[0].'.';
        }
        else
        {
            $Message = $Key.' is deprecated since cURL '.$Cachos[0].'; consider using '.$Cachos[1].' instead.';
        }
        ErrorLog($Message, E_USER_WARNING);
    }
}

/**
 * cURLOptionsValidate
 * Validates the options set to make sure that follow what is stablished and the required PHP/cURL/OpenSSL versions are available
 * 
 * Behaviour
 * Option                               Validation          Explanation
 * CURLOPT_VERBOSE                      must be Boolean     TRUE to output verbose information. Writes output to STDERR, or the file specified using 
 *                                                          CURLOPT_STDERR. All protocols. See https://curl.se/libcurl/c/CURLOPT_VERBOSE.html
 * CURLOPT_HEADER                       must be Boolean     TRUE to include the header in the output. Most protocols. See 
 *                                                          https://curl.se/libcurl/c/CURLOPT_HEADER.html
 * CURLOPT_NOPROGRESS                   must be Boolean     TRUE to disable the progress meter for cURL transfers. PHP automatically sets this option to TRUE, 
 *                                                          this should only be changed for debugging purposes. All protocols. 
 *                                                          See https://curl.se/libcurl/c/CURLOPT_NOPROGRESS.html
 * CURLOPT_NOSIGNAL                     Boolean/cURL 7.10   TRUE to ignore any cURL function that causes a signal to be sent to the PHP process. This is turned 
 *                                                          on by default in multi-threaded SAPIs so timeout options can still be used. All protocols. See 
 *                                                          https://curl.se/libcurl/c/CURLOPT_NOSIGNAL.html
 * CURLOPT_WILDCARDMATCH                Boolean/cURL 7.21   **UNDOCUMENTED** TRUE to to transfer multiple files according to a file name pattern. The pattern can 
 *                                                          be specified as part of the CURLOPT_URL option, using an fnmatch-like pattern (Shell Pattern Matching) 
 *                                                          in the last part of URL (file name). By default, libcurl uses its internal wildcard matching 
 *                                                          implementation. You can provide your own matching function by the CURLOPT_FNMATCH_FUNCTION option. 
 *                                                          FTP protocol downloads. See https://curl.se/libcurl/c/CURLOPT_WILDCARDMATCH.html
 * 
 * Callback
 * Option                               Validation          Explanation
 * CURLOPT_WRITEFUNCTION                must be Callback    A callback accepting two parameters. The first is the cURL resource, and the second is a string with 
 *                                                          the data to be written. The data must be saved by this callback. It must return the exact number of 
 *                                                          bytes written or the transfer will be aborted with an error. All protocols. See 
 *                                                          https://curl.se/libcurl/c/CURLOPT_WRITEFUNCTION.html
 * CURLOPT_WRITEDATA                    must be String      **UNDOCUMENTED** A data pointer to pass to the write callback. If you use the CURLOPT_WRITEFUNCTION 
 *                                                          option, this is the pointer you will get in that callback's 4th argument. If you do not use a write 
 *                                                          callback, you must make pointer a 'FILE *' (cast to 'void *') as libcurl will pass this to fwrite
 *                                                          when writing data. The internal CURLOPT_WRITEFUNCTION will write the data to the FILE * given with 
 *                                                          this option, or to stdout if this option has not been set. All protocols. See 
 *                                                          https://curl.se/libcurl/c/CURLOPT_WRITEDATA.html
 * CURLOPT_READFUNCTION                 must be Callback    A callback accepting three parameters. The first is the cURL resource, the second is a stream 
 *                                                          resource provided to cURL through the option CURLOPT_INFILE, and the third is the maximum amount of 
 *                                                          data to be read. The callback must return a string with a length equal or smaller than the amount of 
 *                                                          data requested, typically by reading it from the passed stream resource. It should return an empty 
 *                                                          string to signal EOF. All protocols. See https://curl.se/libcurl/c/CURLOPT_READFUNCTION.html
 * CURLOPT_READDATA                     must be String      **UNDOCUMENTED** Data pointer to pass to the file read function. If you use the CURLOPT_READFUNCTION 
 *                                                          option, this is the pointer you will get as input in the 4th argument to the callback. If you do not
 *                                                          specify a read callback but instead rely on the default internal read function, this data must be a 
 *                                                          valid readable FILE * (cast to 'void *'). All protocols. See 
 *                                                          https://curl.se/libcurl/c/CURLOPT_READDATA.html
 * CURLOPT_IOCTLFUNCTION                Callback/c7.12.3    **UNDOCUMENTED AND DEPRECATED** This option is deprecated! Do not use it. Use CURLOPT_SEEKFUNCTION 
 *                                                          instead to provide seeking! If CURLOPT_SEEKFUNCTION is set, this parameter will be ignored when 
 *                                                          seeking. This callback function gets called by libcurl when something special I/O-related needs to 
 *                                                          be done that the library cannot do by itself. For now, rewinding the read data stream is the only 
 *                                                          action it can request. The rewinding of the read data stream may be necessary when doing an HTTP PUT 
 *                                                          or POST with a multi-pass authentication method. The callback function should match the prototype 
 *                                                          shown on the reference URL. The callback MUST return CURLIOE_UNKNOWNCMD if the input cmd is not 
 *                                                          CURLIOCMD_RESTARTREAD. HTTP Protocol. See https://curl.se/libcurl/c/CURLOPT_IOCTLFUNCTION.html
 * CURLOPT_IOCTLDATA                    String/c7.12.3      **UNDOCUMENTED** Pass the pointer that will be untouched by libcurl and passed as the 3rd argument 
 *                                                          in the ioctl callback set with CURLOPT_IOCTLFUNCTION. HTTP Protocol. See 
 *                                                          https://curl.se/libcurl/c/CURLOPT_IOCTLDATA.html
 * CURLOPT_SEEKFUNCTION                 Callback/c7.18.0    **UNDOCUMENTED** This function gets called by libcurl to seek to a certain position in the input 
 *                                                          stream and can be used to fast forward a file in a resumed upload (instead of reading all uploaded 
 *                                                          bytes with the normal read function/callback). It is also called to rewind a stream when data has 
 *                                                          already been sent to the server and needs to be sent again. This may happen when doing an HTTP PUT 
 *                                                          or POST with a multi-pass authentication method, or when an existing HTTP connection is reused too 
 *                                                          late and the server closes the connection. The function shall work like fseek or lseek and it gets 
 *                                                          SEEK_SET, SEEK_CUR or SEEK_END as argument for origin, although libcurl currently only passes 
 *                                                          SEEK_SET. The callback function shouls follow the protoype shown on the reference URL. userp is the 
 *                                                          pointer you set with CURLOPT_SEEKDATA. The callback function must return CURL_SEEKFUNC_OK on success,
 *                                                          CURL_SEEKFUNC_FAIL to cause the upload operation to fail or CURL_SEEKFUNC_CANTSEEK to indicate that 
 *                                                          while the seek failed, libcurl is free to work around the problem if possible. HTTP, FTP, SFTP 
 *                                                          Protocols. See https://curl.se/libcurl/c/CURLOPT_SEEKFUNCTION.html
 * CURLOPT_SEEKDATA                     String/c7.18.0      **UNDOCUMENTED** Data pointer to pass to the seek callback function. If you use the 
 *                                                          CURLOPT_SEEKFUNCTION option, this is the pointer you will get as input. HTTP, FTP, SFTP Protocols.
 *                                                          See https://curl.se/libcurl/c/CURLOPT_SEEKDATA.html
 * CURLOPT_SOCKOPTFUNCTION              Callback/c7.16.0    **UNDOCUMENTED** When set, this callback function gets called by libcurl when the socket has been 
 *                                                          created, but before the connect call to allow applications to change specific socket options. The 
 *                                                          callback's purpose argument identifies the exact purpose for this particular socket: 
 *                                                          CURLSOCKTYPE_IPCXN for actively created connections or since 7.28.0 CURLSOCKTYPE_ACCEPT for FTP when
 *                                                          the connection was setup with PORT/EPSV (in earlier versions these sockets were not passed to this 
 *                                                          callback). Future versions of libcurl may support more purposes. libcurl passes the newly created 
 *                                                          socket descriptor to the callback in the curlfd parameter so additional setsockopt() calls can be 
 *                                                          done at the user's discretion. The clientp pointer contains whatever user-defined value set using the
 *                                                          CURLOPT_SOCKOPTDATA function. Return CURL_SOCKOPT_OK from the callback on success. Return 
 *                                                          CURL_SOCKOPT_ERROR from the callback function to signal an unrecoverable error to the library and it 
 *                                                          will close the socket and return CURLE_COULDNT_CONNECT. Alternatively, the callback function can 
 *                                                          return CURL_SOCKOPT_ALREADY_CONNECTED, to tell libcurl that the socket is already connected and then 
 *                                                          libcurl will not attempt to connect it. This allows an application to pass in an already connected 
 *                                                          socket with CURLOPT_OPENSOCKETFUNCTION and then have this function make libcurl not attempt to 
 *                                                          connect (again). All protocols. See https://curl.se/libcurl/c/CURLOPT_SOCKOPTFUNCTION.html
 * CURLOPT_SOCKOPTDATA                  String/c7.16.0      **UNDOCUMENTED** Pass a pointer that will be untouched by libcurl and passed as the first argument 
 *                                                          in the sockopt callback set with CURLOPT_SOCKOPTFUNCTION. All protocols. See 
 *                                                          https://curl.se/libcurl/c/CURLOPT_SOCKOPTDATA.html
 * CURLOPT_OPENSOCKETFUNCTION           Callback/c7.17.1    **UNDOCUMENTED** This callback function gets called by libcurl instead of the socket call. The 
 *                                                          callback's purpose argument identifies the exact purpose for this particular socket. 
 *                                                          CURLSOCKTYPE_IPCXN is for IP based connections and is the only purpose currently used in libcurl. 
 *                                                          Future versions of libcurl may support more purposes. The clientp pointer contains whatever 
 *                                                          user-defined value set using the CURLOPT_OPENSOCKETDATA function. The callback gets the resolved 
 *                                                          peer address as the address argument and is allowed to modify the address or refuse to connect 
 *                                                          completely. The callback function should return the newly created socket or CURL_SOCKET_BAD in case 
 *                                                          no connection could be established or another error was detected. Any additional setsockopt calls 
 *                                                          can of course be done on the socket at the user's discretion. A CURL_SOCKET_BAD return value from 
 *                                                          the callback function will signal an unrecoverable error to libcurl and it will return 
 *                                                          CURLE_COULDNT_CONNECT from the function that triggered this callback. This return code can be used 
 *                                                          for IP address block listing. If you want to pass in a socket with an already established connection, 
 *                                                          pass the socket back with this callback and then use CURLOPT_SOCKOPTFUNCTION to signal that it 
 *                                                          already is connected. All protocols. See https://curl.se/libcurl/c/CURLOPT_OPENSOCKETFUNCTION.html
 * CURLOPT_OPENSOCKETDATA               String/c7.17.1      **UNDOCUMENTED** Pass a pointer that will be untouched by libcurl and passed as the first argument 
 *                                                          in the opensocket callback set with CURLOPT_OPENSOCKETFUNCTION. All protocols. See 
 *                                                          https://curl.se/libcurl/c/CURLOPT_OPENSOCKETDATA.html
 * CURLOPT_CLOSESOCKETFUNCTION          Callback/c7.21.7    **UNDOCUMENTED** Pass a pointer to your callback function, which should match the prototype shown 
 *                                                          above. This callback function gets called by libcurl instead of the close or closesocket call when 
 *                                                          sockets are closed (not for any other file descriptors). This is pretty much the reverse to the 
 *                                                          CURLOPT_OPENSOCKETFUNCTION option. Return 0 to signal success and 1 if there was an error. The 
 *                                                          clientp pointer is set with CURLOPT_CLOSESOCKETDATA. item is the socket libcurl wants to be closed. 
 *                                                          All protocols. See https://curl.se/libcurl/c/CURLOPT_CLOSESOCKETFUNCTION.html
 * CURLOPT_CLOSESOCKETDATA              String/c7.21.7      **UNDOCUMENTED** Pass a pointer that will be untouched by libcurl and passed as the first argument 
 *                                                          in the closesocket callback set with CURLOPT_CLOSESOCKETFUNCTION. All protocols except FILE. See 
 *                                                          https://curl.se/libcurl/c/CURLOPT_CLOSESOCKETDATA.html
 * CURLOPT_PROGRESSFUNCTION             must be Callback    **OBSOLETE** use the newer CURLOPT_XFERINFOFUNCTION instead, if you can. A callback accepting five 
 *                                                          parameters. The first is the cURL resource, the second is the total number of bytes expected to be 
 *                                                          downloaded in this transfer, the third is the number of bytes downloaded so far, the fourth is the 
 *                                                          total number of bytes expected to be uploaded in this transfer, and the fifth is the number of bytes 
 *                                                          uploaded so far. The callback is only called when the CURLOPT_NOPROGRESS option is set to false. 
 *                                                          Return a non-zero value to abort the transfer. In which case, the transfer will set a 
 *                                                          CURLE_ABORTED_BY_CALLBACK error. All protocols. See 
 *                                                          https://curl.se/libcurl/c/CURLOPT_PROGRESSFUNCTION.html
 * CURLOPT_PROGRESSDATA                 must be String      **UNDOCUMENTED** Pass a pointer that will be untouched by libcurl and passed as the first argument 
 *                                                          in the progress callback set with CURLOPT_PROGRESSFUNCTION. All protocols. See 
 *                                                          https://curl.se/libcurl/c/CURLOPT_PROGRESSDATA.html
 * CURLOPT_XFERINFOFUNCTION             Callback/c7.32.0    **UNDOCUMENTED** Pass a pointer to your callback function, which should match the prototype shown 
 *                                                          above. This function gets called by libcurl instead of its internal equivalent with a frequent 
 *                                                          interval. While data is being transferred it will be called frequently, and during slow periods like 
 *                                                          when nothing is being transferred it can slow down to about one call per second. clientp is the 
 *                                                          pointer set with CURLOPT_XFERINFODATA, it is not used by libcurl but is only passed along from the 
 *                                                          application to the callback. The callback gets told how much data libcurl will transfer and has 
 *                                                          transferred, in number of bytes. dltotal is the total number of bytes libcurl expects to download in
 *                                                          this transfer. dlnow is the number of bytes downloaded so far. ultotal is the total number of bytes 
 *                                                          libcurl expects to upload in this transfer. ulnow is the number of bytes uploaded so far. 
 *                                                          Unknown/unused argument values passed to the callback will be set to zero (like if you only download
 *                                                          data, the upload size will remain 0). Many times the callback will be called one or more times first, 
 *                                                          before it knows the data sizes so a program must be made to handle that. If your callback function 
 *                                                          returns CURL_PROGRESSFUNC_CONTINUE it will cause libcurl to continue executing the default progress 
 *                                                          function. Returning any other non-zero value from this callback will cause libcurl to abort the 
 *                                                          transfer and return CURLE_ABORTED_BY_CALLBACK. If you transfer data with the multi interface, this 
 *                                                          function will not be called during periods of idleness unless you call the appropriate libcurl 
 *                                                          function that performs transfers. CURLOPT_NOPROGRESS must be set to 0 to make this function actually
 *                                                          get called. All protocols. See https://curl.se/libcurl/c/CURLOPT_XFERINFOFUNCTION.html
 * CURLOPT_XFERINFODATA                 String/c7.32.0      **UNDOCUMENTED** Pass a pointer that will be untouched by libcurl and passed as the first argument 
 *                                                          in the progress callback set with CURLOPT_XFERINFOFUNCTION. This is an alias for 
 *                                                          CURLOPT_PROGRESSDATA. All protocols. See https://curl.se/libcurl/c/CURLOPT_XFERINFODATA.html
 * CURLOPT_HEADERFUNCTION               must be Callback    A callback accepting two parameters. The first is the cURL resource, the second is a string with the 
 *                                                          header data to be written. The header data must be written by this callback. Return the number of 
 *                                                          bytes written. Used for all protocols with headers or meta-data concept: HTTP, FTP, POP3, IMAP, SMTP 
 *                                                          and more. All protocols with headers or meta-data concept: HTTP, FTP, POP3, IMAP, SMTP and more. See 
 *                                                          https://curl.se/libcurl/c/CURLOPT_HEADERFUNCTION.html
 * CURLOPT_HEADERDATA                   must be String      **UNDOCUMENTED** Pass a pointer to be used to write the header part of the received data to. If 
 *                                                          CURLOPT_WRITEFUNCTION or CURLOPT_HEADERFUNCTION is used, pointer will be passed in to the respective 
 *                                                          callback. If neither of those options are set, pointer must be a valid FILE * and it will be used by 
 *                                                          a plain fwrite() to write headers to. All protocols. See https://curl.se/libcurl/c/CURLOPT_HEADERDATA.html
 * CURLOPT_DEBUGFUNCTION                must be Callback    **UNDOCUMENTED** CURLOPT_DEBUGFUNCTION replaces the standard debug function used when CURLOPT_VERBOSE 
 *                                                          is in effect. This callback receives debug information, as specified in the type argument. This 
 *                                                          function must return 0. The data pointed to by the char * passed to this function WILL NOT be 
 *                                                          null-terminated, but will be exactly of the size as told by the size argument. The userptr argument 
 *                                                          is the pointer set with CURLOPT_DEBUGDATA. Available curl_infotype values:
 *                                                          - CURLINFO_TEXT: The data is informational text.
 *                                                          - CURLINFO_HEADER_IN: The data is header (or header-like) data received from the peer.
 *                                                          - CURLINFO_HEADER_OUT: The data is header (or header-like) data sent to the peer.
 *                                                          - CURLINFO_DATA_IN: The data is protocol data received from the peer.
 *                                                          - CURLINFO_DATA_OUT: The data is protocol data sent to the peer.
 *                                                          - CURLINFO_SSL_DATA_OUT: The data is SSL/TLS (binary) data sent to the peer.
 *                                                          - CURLINFO_SSL_DATA_IN: The data is SSL/TLS (binary) data received from the peer.
 *                                                          All protocols. See https://curl.se/libcurl/c/CURLOPT_DEBUGFUNCTION.html
 * CURLOPT_DEBUGDATA                    must be String      **UNDOCUMENTED** Pass a pointer to whatever you want passed in to your CURLOPT_DEBUGFUNCTION in the 
 *                                                          last void * argument. This pointer is not used by libcurl, it is only passed to the callback. All 
 *                                                          protocols. See https://curl.se/libcurl/c/CURLOPT_DEBUGDATA.html
 * CURLOPT_SSL_CTX_FUNCTION             Callback/c7.11.0    **UNDOCUMENTED** This option only works for libcurl powered by OpenSSL, wolfSSL, mbedTLS or BearSSL. 
 *                                                          If libcurl was built against another SSL library this functionality is absent. This callback function 
 *                                                          gets called by libcurl just before the initialization of an SSL connection after having processed all 
 *                                                          other SSL related options to give a last chance to an application to modify the behavior of the SSL 
 *                                                          initialization. The ssl_ctx parameter is actually a pointer to the SSL library's SSL_CTX for OpenSSL 
 *                                                          or wolfSSL, a pointer to mbedtls_ssl_config for mbedTLS or a pointer to br_ssl_client_context for 
 *                                                          BearSSL. If an error is returned from the callback no attempt to establish a connection is made and 
 *                                                          the perform operation will return the callback's error code. Set the userptr argument with the 
 *                                                          CURLOPT_SSL_CTX_DATA option. All TLS based protocols: HTTPS, FTPS, IMAPS, POP3S, SMTPS etc. See 
 *                                                          https://curl.se/libcurl/c/CURLOPT_SSL_CTX_FUNCTION.html
 * CURLOPT_SSL_CTX_DATA                 String/c7.11.0      **UNDOCUMENTED** Data pointer to pass to the ssl context callback set by the option 
 *                                                          CURLOPT_SSL_CTX_FUNCTION, this is the pointer you will get as third parameter. All TLS based 
 *                                                          protocols: HTTPS, FTPS, IMAPS, POP3S, SMTPS etc. See https://curl.se/libcurl/c/CURLOPT_SSL_CTX_DATA.html
 * CURLOPT_CONV_TO_NETWORK_FUNCTION     Callback/<c7.82.0   **UNDOCUMENTED** CURLOPT_CONV_TO_NETWORK_FUNCTION converts from host encoding to the network encoding. 
 *                                                          It is used when commands or ASCII data are sent over the network. If you set a callback pointer to 
 *                                                          NULL, or do not set it at all, the built-in libcurl iconv functions will be used. If HAVE_ICONV was 
 *                                                          not defined when libcurl was built, and no callback has been established, conversion will return the 
 *                                                          CURLE_CONV_REQD error code. Protocols: FTP, SMTP, IMAP, POP3. See 
 *                                                          https://curl.se/libcurl/c/CURLOPT_CONV_TO_NETWORK_FUNCTION.html
 * CURLOPT_CONV_FROM_NETWORK_FUNCTION   Callback/<c7.82.0   **UNDOCUMENTED** CURLOPT_CONV_FROM_NETWORK_FUNCTION converts to host encoding from the network 
 *                                                          encoding. It is used when commands or ASCII data are received over the network. If you set a callback
 *                                                          pointer to NULL, or do not set it at all, the built-in libcurl iconv functions will be used. If 
 *                                                          HAVE_ICONV was not defined when libcurl was built, and no callback has been established, conversion 
 *                                                          will return the CURLE_CONV_REQD error code. Protocols: FTP, SMTP, IMAP, POP3. See 
 *                                                          https://curl.se/libcurl/c/CURLOPT_CONV_FROM_NETWORK_FUNCTION.html
 * CURLOPT_CONV_FROM_UTF8_FUNCTION      Callback/<c7.82.0   **UNDOCUMENTED** CURLOPT_CONV_FROM_UTF8_FUNCTION converts to host encoding from UTF8 encoding. It is 
 *                                                          required only for SSL processing. If you set a callback pointer to NULL, or do not set it at all, the
 *                                                          built-in libcurl iconv functions will be used. If HAVE_ICONV was not defined when libcurl was built,
 *                                                          and no callback has been established, conversion will return the CURLE_CONV_REQD error code. TLS-based 
 *                                                          protocols. See https://curl.se/libcurl/c/CURLOPT_CONV_FROM_UTF8_FUNCTION.html
 * CURLOPT_INTERLEAVEFUNCTION           Callback/c7.20.0    **UNDOCUMENTED** This callback function gets called by libcurl as soon as it has received interleaved 
 *                                                          RTP data. This function gets called for each $ block and therefore contains exactly one upper-layer 
 *                                                          protocol unit (e.g. one RTP packet). Curl writes the interleaved header as well as the included data
 *                                                          for each call. The first byte is always an ASCII dollar sign. The dollar sign is followed by a one 
 *                                                          byte channel identifier and then a 2 byte integer length in network byte order. See RFC2326 Section 
 *                                                          10.12 for more information on how RTP interleaving behaves. If unset or set to NULL, curl will use 
 *                                                          the default write function. RTSP protocol. See https://curl.se/libcurl/c/CURLOPT_INTERLEAVEFUNCTION.html
 * CURLOPT_INTERLEAVEDATA               String/c7.20.0      **UNDOCUMENTED** This is the userdata pointer that will be passed to CURLOPT_INTERLEAVEFUNCTION when 
 *                                                          interleaved RTP data is received. If the interleave function callback is not set, this pointer is 
 *                                                          not used anywhere. RTSP protocol. See https://curl.se/libcurl/c/CURLOPT_INTERLEAVEDATA.html
 * CURLOPT_CHUNK_BGN_FUNCTION           Callback/c7.21.0    **UNDOCUMENTED** This callback function gets called by libcurl before a part of the stream is going 
 *                                                          to be transferred (if the transfer supports chunks). The transfer_info pointer will point to a struct 
 *                                                          curl_fileinfo with details about the file that is about to get transferred. This callback makes sense 
 *                                                          only when using the CURLOPT_WILDCARDMATCH option for now. FTP protocol. See 
 *                                                          https://curl.se/libcurl/c/CURLOPT_CHUNK_BGN_FUNCTION.html
 * CURLOPT_CHUNK_END_FUNCTION           Callback/c7.21.0    **UNDOCUMENTED** This function gets called by libcurl as soon as a part of the stream has been 
 *                                                          transferred (or skipped). Return CURL_CHUNK_END_FUNC_OK if everything is fine or 
 *                                                          CURL_CHUNK_END_FUNC_FAIL to tell the lib to stop if some error occurred. FTP protocol. See 
 *                                                          https://curl.se/libcurl/c/CURLOPT_CHUNK_END_FUNCTION.html
 * CURLOPT_CHUNK_DATA                   String/c7.21.0      **UNDOCUMENTED** Pass a pointer that will be untouched by libcurl and passed as the ptr argument to 
 *                                                          the CURLOPT_CHUNK_BGN_FUNCTION and CURLOPT_CHUNK_END_FUNCTION. FTP protocol. See 
 *                                                          https://curl.se/libcurl/c/CURLOPT_CHUNK_DATA.html
 * CURLOPT_FNMATCH_FUNCTION             Callback/c7.21.0    **UNDOCUMENTED** Callback function used for wildcard matching. Return CURL_FNMATCHFUNC_MATCH if 
 *                                                          pattern matches the string, CURL_FNMATCHFUNC_NOMATCH if not or CURL_FNMATCHFUNC_FAIL if an error 
 *                                                          occurred. FTP protocol. See https://curl.se/libcurl/c/CURLOPT_FNMATCH_FUNCTION.html
 * CURLOPT_FNMATCH_DATA                 String/c7.21.0      **UNDOCUMENTED** Pass a pointer that will be untouched by libcurl and passed as the ptr argument to 
 *                                                          the CURLOPT_FNMATCH_FUNCTION. FTP protocol. See https://curl.se/libcurl/c/CURLOPT_FNMATCH_DATA.html
 * CURLOPT_SUPPRESS_CONNECT_HEADERS     Bool/c7.54.0/P7.3.0 TRUE to suppress proxy CONNECT response headers from the user callback functions 
 *                                                          CURLOPT_HEADERFUNCTION and CURLOPT_WRITEFUNCTION, when CURLOPT_HTTPPROXYTUNNEL is used and a CONNECT 
 *                                                          request is made. All protocols. See https://curl.se/libcurl/c/CURLOPT_SUPPRESS_CONNECT_HEADERS.html
 * CURLOPT_RESOLVER_START_FUNCTION      Callback/c7.59.0    **UNDOCUMENTED** This callback function gets called by libcurl every time before a new resolve 
 *                                                          request is started. resolver_state points to a backend-specific resolver state. Currently only the 
 *                                                          ares resolver backend has a resolver state. It can be used to set up any desired option on the ares 
 *                                                          channel before it's used, for example setting up socket callback options. All protocols. See 
 *                                                          https://curl.se/libcurl/c/CURLOPT_RESOLVER_START_FUNCTION.html
 * CURLOPT_RESOLVER_START_DATA          String/c7.59.0      **UNDOCUMENTED** Pass a pointer that will be untouched by libcurl and passed as the third argument in 
 *                                                          the resolver start callback set with CURLOPT_RESOLVER_START_FUNCTION. All protocols. See 
 *                                                          https://curl.se/libcurl/c/CURLOPT_RESOLVER_START_DATA.html
 * CURLOPT_PREREQFUNCTION               Callback/c7.80.0    **UNDOCUMENTED** This function gets called by libcurl after a connection has been established or a 
 *                                                          connection has been reused (including any SSL handshaking), but before any request is actually made 
 *                                                          on the connection. For example, for HTTP, this callback is called once a connection has been 
 *                                                          established to the server, but before a GET/HEAD/POST/etc request has been sent. This function may 
 *                                                          be called multiple times if redirections are enabled and are being followed (see 
 *                                                          CURLOPT_FOLLOWLOCATION). All protocols. See https://curl.se/libcurl/c/CURLOPT_PREREQFUNCTION.html
 * CURLOPT_PREREQDATA                   String/c7.80.0      **UNDOCUMENTED** Pass a pointer that will be untouched by libcurl and passed as the first argument in 
 *                                                          the pre-request callback set with CURLOPT_PREREQFUNCTION. All protocols. See 
 *                                                          https://curl.se/libcurl/c/CURLOPT_PREREQDATA.html
 * 
 * Error
 * Option                               Validation          Explanation
 * CURLOPT_ERRORBUFFER                  must be String      **UNDOCUMENTED** String where libcurl may store human readable error messages on failures or problems. 
 *                                                          This may be more helpful than just the return code from curl_easy_perform and related functions. The 
 *                                                          buffer must be at least CURL_ERROR_SIZE bytes big. You must keep the associated buffer available until 
 *                                                          libcurl no longer needs it. Failing to do so will cause odd behavior or even crashes. libcurl will 
 *                                                          need it until you call curl_easy_cleanup or you set the same option again to use a different pointer. 
 *                                                          Do not rely on the contents of the buffer unless an error code was returned. Since 7.60.0 libcurl 
 *                                                          will initialize the contents of the error buffer to an empty string before performing the transfer. 
 *                                                          For earlier versions if an error code was returned but there was no error detail then the buffer is 
 *                                                          untouched. Consider CURLOPT_VERBOSE and CURLOPT_DEBUGFUNCTION to better debug and trace why errors 
 *                                                          happen. All protocols. See https://curl.se/libcurl/c/CURLOPT_ERRORBUFFER.html
 * CURLOPT_STDERR                       must be Stream      An alternative location to output errors to instead of STDERR. All protocols. See 
 *                                                          https://curl.se/libcurl/c/CURLOPT_ERRORBUFFER.html
 * CURLOPT_FAILONERROR                  must be Boolean     TRUE to fail verbosely if the HTTP code returned is greater than or equal to 400. The default 
 *                                                          behavior is to return the page normally, ignoring the code. HTTP protocol. See 
 *                                                          https://curl.se/libcurl/c/CURLOPT_FAILONERROR.html
 * CURLOPT_KEEP_SENDING_ON_ERROR        Bool/c7.51.0/P7.3.0 TRUE to keep sending the request body if the HTTP code returned is equal to or larger than 300. The 
 *                                                          default action would be to stop sending and close the stream or connection. Suitable for manual NTLM 
 *                                                          authentication. Most applications do not need this option. HTTP protocol. See 
 *                                                          https://curl.se/libcurl/c/CURLOPT_KEEP_SENDING_ON_ERROR.html
 * 
 * Network
 * Option                               Validation          Explanation
 * CURLOPT_URL                          must be String      The URL to fetch. This can also be set when initializing a session with curl_init(). All protocols. 
 *                                                          See https://curl.se/libcurl/c/CURLOPT_URL.html
 * CURLOPT_PATH_AS_IS                   Bool/c7.42.0/P7.0.7 Skip the normalization of the path. That is the procedure where curl otherwise removes sequences of 
 *                                                          dot-slash and dot-dot etc. All protocols. See https://curl.se/libcurl/c/CURLOPT_PATH_AS_IS.html
 * CURLOPT_PROTOCOLS                    Int/cURL 7.19.4     Bitmask of CURLPROTO_* values. If used, this bitmask limits what protocols libcurl may use in the 
 *                                                          transfer. This allows you to have a libcurl built to support a wide range of protocols but still 
 *                                                          limit specific transfers to only be allowed to use a subset of them. By default libcurl will accept 
 *                                                          all protocols it supports. See also CURLOPT_REDIR_PROTOCOLS.
 *                                                          Valid protocol options are: CURLPROTO_HTTP, CURLPROTO_HTTPS, CURLPROTO_FTP, CURLPROTO_FTPS, 
 *                                                          CURLPROTO_SCP, CURLPROTO_SFTP, CURLPROTO_TELNET, CURLPROTO_LDAP, CURLPROTO_LDAPS, CURLPROTO_DICT, 
 *                                                          CURLPROTO_FILE, CURLPROTO_TFTP, CURLPROTO_ALL. DEPRECATED. Use CURLOPT_PROTOCOLS_STR instead. All 
 *                                                          built_in protocols. See https://curl.se/libcurl/c/CURLOPT_PROTOCOLS.html
 * CURLOPT_PROTOCOLS_STR                String/cURL 7.85.0  **UNDOCUMENTED** A comma-separated list of case insensitive protocol names (URL schemes) to 
 *                                                          allow in the transfer. This option allows applications to use libcurl built to support a wide range 
 *                                                          of protocols but still limit specific transfers to only be allowed to use a subset of them. 
 *                                                          Available protocols: DICT, FILE, FTP, FTPS, GOPHER, GOPHERS, HTTP, HTTPS, IMAP, IMAPS, LDAP, LDAPS, 
 *                                                          POP3, POP3S, RTMP, RTMPE, RTMPS, RTMPT, RTMPTE, RTMPTS, RTSP, SCP, SFTP, SMB, SMBS, SMTP, SMTPS, 
 *                                                          TELNET, TFTP. You can set "ALL" as a short-cut to enable all protocols. All protocols.
 *                                                          See https://curl.se/libcurl/c/CURLOPT_PROTOCOLS_STR.html
 * CURLOPT_REDIR_PROTOCOLS              Int/cURL 7.19.4     Bitmask of CURLPROTO_* values. If used, this bitmask limits what protocols libcurl may use in a 
 *                                                          transfer that it follows to in a redirect when CURLOPT_FOLLOWLOCATION is enabled. This allows you to 
 *                                                          limit specific transfers to only be allowed to use a subset of protocols in redirections. By default 
 *                                                          libcurl will allow all protocols except for FILE and SCP. This is a difference compared to 
 *                                                          pre-7.19.4 versions which unconditionally would follow to all protocols supported. See also 
 *                                                          CURLOPT_PROTOCOLS for protocol constant values. Deprecated. Use CURLOPT_REDIR_PROTOCOLS_STR instead. 
 *                                                          All protocols. See https://curl.se/libcurl/c/CURLOPT_REDIR_PROTOCOLS.html
 * CURLOPT_REDIR_PROTOCOLS_STR          String/cURL 7.19.4  **UNDOCUMENTED** String that holds a comma-separated list of case insensitive protocol names. All 
 *                                                          protocols. See https://curl.se/libcurl/c/CURLOPT_REDIR_PROTOCOLS_STR.html
 * CURLOPT_DEFAULT_PROTOCOL             Str/c7.45.0/P7.0.7  The default protocol to use if the URL is missing a scheme name. Use one of these protocol (scheme) 
 *                                                          names: dict, file, ftp, ftps, gopher, http, https, imap, imaps, ldap, ldaps, pop3, pop3s, rtsp, scp, 
 *                                                          sftp, smb, smbs, smtp, smtps, telnet, tftp. All protocols. See 
 *                                                          https://curl.se/libcurl/c/CURLOPT_DEFAULT_PROTOCOL.html
 * CURLOPT_PROXY                        must be String      The HTTP proxy to tunnel requests through. All protocols except FILE. Note that some protocols do not 
 *                                                          work well over proxy. See https://curl.se/libcurl/c/CURLOPT_PROXY.html
 * CURLOPT_PRE_PROXY                    Str/c7.52.0/P7.3.0  Set a string holding the host name or dotted numerical IP address to be used as the preproxy that 
 *                                                          curl connects to before it connects to the HTTP(S) proxy specified in the CURLOPT_PROXY option for 
 *                                                          the upcoming request. The preproxy can only be a SOCKS proxy and it should be prefixed with 
 *                                                          [scheme]:// to specify which kind of socks is used. A numerical IPv6 address must be written within 
 *                                                          [brackets]. Setting the preproxy to an empty string explicitly disables the use of a preproxy. To 
 *                                                          specify port number in this string, append :[port] to the end of the host name. The proxy's port 
 *                                                          number may optionally be specified with the separate option CURLOPT_PROXYPORT. Defaults to using 
 *                                                          port 1080 for proxies if a port is not specified. All protocols except FILE. Note that some protocols 
 *                                                          do not work well over proxy. See https://curl.se/libcurl/c/CURLOPT_PRE_PROXY.html
 * CURLOPT_PROXYPORT                    must be Int         The port number of the proxy to connect to. This port number can also be set in CURLOPT_PROXY. All 
 *                                                          protocols. See https://curl.se/libcurl/c/CURLOPT_PROXYPORT.html
 * CURLOPT_PROXYTYPE                    Int/cURL 7.10       Either CURLPROXY_HTTP (default), CURLPROXY_SOCKS4, CURLPROXY_SOCKS5, CURLPROXY_SOCKS4A or 
 *                                                          CURLPROXY_SOCKS5_HOSTNAME. Most ptotocols. See https://curl.se/libcurl/c/CURLOPT_PROXYTYPE.html
 * CURLOPT_NOPROXY                      Str/c7.19.4         **UNDOCUMENTED** The string consists of a comma separated list of host names that do not require a 
 *                                                          proxy to get reached, even if one is specified. The only wildcard available is a single * character, 
 *                                                          which matches all hosts, and effectively disables the proxy. Each name in this list is matched as 
 *                                                          either a domain which contains the hostname, or the hostname itself. For example, example.com would 
 *                                                          match example.com, example.com:80, and www.example.com, but not www.notanexample.com or 
 *                                                          example.com.othertld. If the name in the noproxy list has a leading period, it is a domain match 
 *                                                          against the provided host name. This way ".example.com" will switch off proxy use for both 
 *                                                          "www.example.com" as well as for "foo.example.com". Setting the noproxy string to "" (an empty 
 *                                                          string) will explicitly enable the proxy for all host names, even if there is an environment variable 
 *                                                          set for it. Enter IPv6 numerical addresses in the list of host names without enclosing brackets: 
 *                                                          "example.com,::1,localhost" IPv6 numerical addresses are compared as strings, so they will only match 
 *                                                          if the representations are the same: "::1" is the same as "::0:1" but they do not match. Most 
 *                                                          protocols. See https://curl.se/libcurl/c/CURLOPT_NOPROXY.html
 * CURLOPT_HTTPPROXYTUNNEL              must be Boolean     TRUE to tunnel through a given HTTP proxy. All network protocols. See 
 *                                                          https://curl.se/libcurl/c/CURLOPT_HTTPPROXYTUNNEL.html
 * CURLOPT_CONNECT_TO                  Array/c7.49.0/P7.0.7 Connect to a specific host and port instead of the URL's host and port. Accepts an array of strings 
 *                                                          with the format HOST:PORT:CONNECT-TO-HOST:CONNECT-TO-PORT. All protocols. See 
 *                                                          https://curl.se/libcurl/c/CURLOPT_CONNECT_TO.html
 * CURLOPT_SOCKS5_AUTH                  Int/c7.55.0/P7.3.0  The SOCKS5 authentication method(s) to use. The options are: CURLAUTH_BASIC, CURLAUTH_GSSAPI, 
 *                                                          CURLAUTH_NONE. The bitwise | (or) operator can be used to combine more than one method. If this is 
 *                                                          done, cURL will poll the server to see what methods it supports and pick the best one.
 *                                                          - CURLAUTH_BASIC allows username/password authentication.
 *                                                          - CURLAUTH_GSSAPI allows GSS-API authentication.
 *                                                          - CURLAUTH_NONE allows no authentication.
 *                                                          Defaults to CURLAUTH_BASIC|CURLAUTH_GSSAPI. Set the actual username and password with the 
 *                                                          CURLOPT_PROXYUSERPWD option. All protocols. See https://curl.se/libcurl/c/CURLOPT_SOCKS5_AUTH.html
 * CURLOPT_SOCKS5_GSSAPI_SERVICE        Str/c7.19.4         **UNDOCUMENTED AND DEPRECATED** SOCKS5 proxy authentication service name. Pass a char * as parameter 
 *                                                          to a string holding the name of the service. The default service name for a SOCKS5 server is "rcmd". 
 *                                                          This option allows you to change it. Deprecated since 7.49.0. Use CURLOPT_PROXY_SERVICE_NAME instead. 
 *                                                          All protocols. See https://curl.se/libcurl/c/CURLOPT_SOCKS5_GSSAPI_SERVICE.html
 * CURLOPT_SOCKS5_GSSAPI_NEC            Bool/c7.19.4        **UNDOCUMENTED** Socks proxy gssapi negotiation protection. Pass a long set to 1 to enable or 0 to 
 *                                                          disable. As part of the gssapi negotiation a protection mode is negotiated. The RFC 1961 says in 
 *                                                          section 4.3/4.4 it should be protected, but the NEC reference implementation does not. If enabled, 
 *                                                          this option allows the unprotected exchange of the protection mode negotiation. Most protocols. 
 *                                                          See https://curl.se/libcurl/c/CURLOPT_SOCKS5_GSSAPI_NEC.html
 * CURLOPT_PROXY_SERVICE_NAME           Str/c7.43.0/P7.0.7  The proxy authentication service name. All network protocols. See 
 *                                                          https://curl.se/libcurl/c/CURLOPT_PROXY_SERVICE_NAME.html
 * CURLOPT_HAPROXYPROTOCOL              Bool/c7.60.0/P7.3.0 TRUE to send an HAProxy PROXY protocol v1 header at the start of the connection. The default action 
 *                                                          is not to send this header. HTTP protocol. See https://curl.se/libcurl/c/CURLOPT_HAPROXYPROTOCOL.html
 * CURLOPT_SERVICE_NAME                 Str/c7.43.0/P7.0.7  The authentication service name. HTTP, FTP, IMAP, LDAP, POP3 and SMTP protocols. See 
 *                                                          https://curl.se/libcurl/c/CURLOPT_SERVICE_NAME.html
 * CURLOPT_INTERFACE                    must be String      The name of the outgoing network interface to use. This can be an interface name, an IP address or a 
 *                                                          host name. All protocols. See https://curl.se/libcurl/c/CURLOPT_INTERFACE.html
 * CURLOPT_LOCALPORT                    IANA Port/c7.15.2   **UNDOCUMENTED** Local port number to use for socket. Pass a long. This sets the local port number 
 *                                                          of the socket used for the connection. This can be used in combination with CURLOPT_INTERFACE and 
 *                                                          you are recommended to use CURLOPT_LOCALPORTRANGE as well when this option is set. Valid port numbers 
 *                                                          are 1 - 65535. All protocols. See https://curl.se/libcurl/c/CURLOPT_LOCALPORT.html
 * CURLOPT_LOCALPORTRANGE               Int/c7.15.2         **UNDOCUMENTED** Number of additional local ports to try. The range argument is the number of attempts 
 *                                                          libcurl will make to find a working local port number. It starts with the given CURLOPT_LOCALPORT and 
 *                                                          adds one to the number for each retry. Setting this option to 1 or below will make libcurl do only 
 *                                                          one try for the exact port number. Port numbers by nature are scarce resources that will be busy at 
 *                                                          times so setting this value to something too low might cause unnecessary connection setup failures. 
 *                                                          All protocols. See https://curl.se/libcurl/c/CURLOPT_LOCALPORTRANGE.html
 * CURLOPT_DNS_CACHE_TIMEOUT            must be Int         The number of seconds to keep DNS entries in memory. This option is set to 120 (2 minutes) by 
 *                                                          default. See https://github.com/php/php-src/blob/PHP-8.1.0/ext/curl/interface.c#L1824 But cURL sets 
 *                                                          default is 60 secs. All protocols. See https://curl.se/libcurl/c/CURLOPT_DNS_CACHE_TIMEOUT.html
 * CURLOPT_DNS_USE_GLOBAL_CACHE         Boolean/cURl<7.62.0 TRUE to use a global DNS cache. This option is not thread-safe. It is conditionally enabled by 
 *                                                          default if PHP is built for non-threaded use (CLI, FCGI, Apache2-Prefork, etc.). Has no function 
 *                                                          since 7.62.0. Do not use! All protocols. See https://curl.se/libcurl/c/CURLOPT_DNS_USE_GLOBAL_CACHE.html
 * CURLOPT_DOH_URL                      Str/c7.62.0         **UNDOCUMENTED** Provide the DNS-over-HTTPS URL. String containig URL for the DoH server to use for 
 *                                                          name resolving. The parameter should be a char * to a null-terminated string which must be URL-encoded 
 *                                                          in the following format: "https://host:port/path". It MUST specify an HTTPS URL. Libcurl does not 
 *                                                          validate the syntax or use this variable until the transfer is issued. Even if you set a crazy value 
 *                                                          here, curl_easy_setopt will still return CURLE_OK. Curl sends POST requests to the given 
 *                                                          DNS-over-HTTPS URL. To find the DoH server itself, which might be specified using a name, libcurl will 
 *                                                          use the default name lookup function. You can bootstrap that by providing the address for the DoH 
 *                                                          server with CURLOPT_RESOLVE. Disable DoH use again by setting this option to NULL. All protocols. 
 *                                                          See https://curl.se/libcurl/c/CURLOPT_DOH_URL.html
 * CURLOPT_BUFFERSIZE                   Int/cURL 7.10       The size of the buffer to use for each read. There is no guarantee this request will be fulfilled, 
 *                                                          however. All protocols. See https://curl.se/libcurl/c/CURLOPT_BUFFERSIZE.html
 * CURLOPT_PORT                         must be Int/IANA    An alternative port number to connect to. libcurl discourages using this option since its scope is 
 *                                                          not obvious and hard to predict. Set the preferred port number in the URL instead.All protocols that 
 *                                                          speak to a port number. See https://curl.se/libcurl/c/CURLOPT_PORT.html
 * CURLOPT_TCP_FASTOPEN                 Bool/c7.49.0/P7.0.7 TRUE to enable TCP Fast Open. All protocols. See https://curl.se/libcurl/c/CURLOPT_TCP_FASTOPEN.html
 * CURLOPT_TCP_NODELAY                  Boolean/cURL 7.11.2 TRUE to disable TCP's Nagle algorithm, which tries to minimize the number of small packets on the 
 *                                                          network. All protocols. See https://curl.se/libcurl/c/CURLOPT_TCP_NODELAY.html
 * CURLOPT_ADDRESS_SCOPE                Int/c7.19.0         **UNDOCUMENTED** Specify the scope id value to use when connecting to IPv6 addresses. All protocols, 
 *                                                          when using IPv6. See https://curl.se/libcurl/c/CURLOPT_ADDRESS_SCOPE.html
 * CURLOPT_TCP_KEEPALIVE                Int/cURL 7.25.0     If set to 1, TCP keepalive probes will be sent. The delay and frequency of these probes can be 
 *                                                          controlled by the CURLOPT_TCP_KEEPIDLE and CURLOPT_TCP_KEEPINTVL options, provided the operating 
 *                                                          system supports them. If set to 0 (default) keepalive probes are disabled. All protocols. See 
 *                                                          https://curl.se/libcurl/c/CURLOPT_TCP_KEEPALIVE.html
 * CURLOPT_TCP_KEEPIDLE                 Int/cURL 7.25.0     Sets the delay, in seconds, that the operating system will wait while the connection is idle before 
 *                                                          sending keepalive probes, if CURLOPT_TCP_KEEPALIVE is enabled. Not all operating systems support 
 *                                                          this option. The default is 60. The maximum value this accepts is 2147483648. Any larger value will 
 *                                                          be capped to this amount. All protocols. See https://curl.se/libcurl/c/CURLOPT_TCP_KEEPIDLE.html
 * CURLOPT_TCP_KEEPINTVL                Int/cURL 7.25.0     Sets the interval, in seconds, that the operating system will wait between sending keepalive probes,
 *                                                          if CURLOPT_TCP_KEEPALIVE is enabled. Not all operating systems support this option. The default is 
 *                                                          60. The maximum value this accepts is 2147483648. Any larger value will be capped to this amount. 
 *                                                          All protocols. See https://curl.se/libcurl/c/CURLOPT_TCP_KEEPINTVL.html
 * CURLOPT_UNIX_SOCKET_PATH             Str/c7.40.0/P7.0.7  Enables the use of Unix domain sockets as connection endpoint and sets the path to the given string. 
 *                                                          All protocols except for FILE and FTP are supported in theory. HTTP, IMAP, POP3 and SMTP should in 
 *                                                          particular work (including their SSL/TLS variants). See 
 *                                                          https://curl.se/libcurl/c/CURLOPT_UNIX_SOCKET_PATH.html
 * CURLOPT_ABSTRACT_UNIX_SOCKET         Str/c7.53.0/P7.3.0  Enables the use of an abstract Unix domain socket instead of establishing a TCP connection to a host 
 *                                                          and sets the path to the given string. This option shares the same semantics as 
 *                                                          CURLOPT_UNIX_SOCKET_PATH. These two options share the same storage and therefore only one of them 
 *                                                          can be set per handle. All protocols. See https://curl.se/libcurl/c/CURLOPT_ABSTRACT_UNIX_SOCKET.html
 * 
 * Authentication
 * Option                               Validation          Explanation
 * CURLOPT_NETRC                        must be Boolean     TRUE to scan the ~/.netrc file to find a username and password for the remote site that a connection 
 *                                                          is being established with. Most protocols. See https://curl.se/libcurl/c/CURLOPT_NETRC.html
 * CURLOPT_NETRC_FILE                   Str/c7.10.9         **UNDOCUMENTED** Null-terminated string containing the full path name to the file you want libcurl to 
 *                                                          use as .netrc file. If this option is omitted, and CURLOPT_NETRC is set, libcurl will attempt to find 
 *                                                          a .netrc file in the current user's home directory. All protocols. See 
 *                                                          https://curl.se/libcurl/c/CURLOPT_NETRC_FILE.html
 * CURLOPT_USERPWD                      must be String      A username and password formatted as "[username]:[password]" to use for the connection. Most 
 *                                                          protocols. See https://curl.se/libcurl/c/CURLOPT_USERPWD.html
 * CURLOPT_PROXYUSERPWD                 Must be string      A username and password formatted as "[username]:[password]" to use for the connection to the proxy.
 *                                                          Both the name and the password will be URL decoded before use, so to include for example a colon in 
 *                                                          the user name you should encode it as %3A. All protocols that can use a proxy. See 
 *                                                          https://curl.se/libcurl/c/CURLOPT_PROXYUSERPWD.html
 * CURLOPT_USERNAME                     String/cURL7.19.1   The user name to use in authentication. You should not use this option together with the (older) 
 *                                                          CURLOPT_USERPWD option. Most protocols. See https://curl.se/libcurl/c/CURLOPT_USERNAME.html
 * CURLOPT_PASSWORD                     String/cURL7.19.1   The password to use in authentication. Most protocols. See 
 *                                                          https://curl.se/libcurl/c/CURLOPT_PASSWORD.html
 * CURLOPT_LOGIN_OPTIONS                Str/c7.34.0/P7.0.7  Can be used to set protocol specific login options, such as the preferred authentication mechanism 
 *                                                          via "AUTH=NTLM" or "AUTH=*", and should be used in conjunction with the CURLOPT_USERNAME option. IMAP, 
 *                                                          LDAP, POP3 and SMTP protocols. See https://curl.se/libcurl/c/CURLOPT_LOGIN_OPTIONS.html
 * CURLOPT_PROXYUSERNAME                Str/c7.19.1         **UNDOCUMENTED** CURLOPT_PROXYUSERNAME sets the user name to be used in protocol authentication with 
 *                                                          the proxy. To specify the proxy password use the CURLOPT_PROXYPASSWORD. Most protocols. See 
 *                                                          https://curl.se/libcurl/c/CURLOPT_PROXYUSERNAME.html
 * CURLOPT_PROXYPASSWORD                Str/c7.19.1         **UNDOCUMENTED** String with a null-terminated password to use for authentication with the proxy. The 
 *                                                          CURLOPT_PROXYPASSWORD option should be used in conjunction with the CURLOPT_PROXYUSERNAME option. Most 
 *                                                          protocols. See https://curl.se/libcurl/c/CURLOPT_PROXYPASSWORD.html
 * CURLOPT_HTTPAUTH                     Int/c7.10.6         The HTTP authentication method(s) to use. The options are: CURLAUTH_BASIC, CURLAUTH_DIGEST, 
 *                                                          CURLAUTH_GSSNEGOTIATE, CURLAUTH_NTLM, CURLAUTH_ANY, and CURLAUTH_ANYSAFE. The bitwise | (or) 
 *                                                          operator can be used to combine more than one method. If this is done, cURL will poll the server 
 *                                                          to see what methods it supports and pick the best one.
 *                                                          CURLAUTH_ANY is an alias for CURLAUTH_BASIC | CURLAUTH_DIGEST | CURLAUTH_GSSNEGOTIATE | CURLAUTH_NTLM.
 *                                                          CURLAUTH_ANYSAFE is an alias for CURLAUTH_DIGEST | CURLAUTH_GSSNEGOTIATE | CURLAUTH_NTLM. HTTP 
 *                                                          protocol. See https://curl.se/libcurl/c/CURLOPT_HTTPAUTH.html
 * CURLOPT_TLSAUTH_USERNAME             Str/c7.21.4         **UNDOCUMENTED** String with a null-terminated username to use for the TLS authentication method 
 *                                                          specified with the CURLOPT_TLSAUTH_TYPE option. Requires that the CURLOPT_TLSAUTH_PASSWORD option also 
 *                                                          be set. This feature relies in TLS SRP which does not work with TLS 1.3. All TLS-based protocols. 
 *                                                          See https://curl.se/libcurl/c/CURLOPT_TLSAUTH_USERNAME.html
 * CURLOPT_PROXY_TLSAUTH_USERNAME       Str/c7.52.0/P7.3.0  The username to use for the HTTPS proxy TLS authentication method specified with the 
 *                                                          CURLOPT_PROXY_TLSAUTH_TYPE option. Requires that the CURLOPT_PROXY_TLSAUTH_PASSWORD option to also 
 *                                                          be set. All protocols. See https://curl.se/libcurl/c/CURLOPT_PROXY_TLSAUTH_USERNAME.html
 * CURLOPT_TLSAUTH_PASSWORD             Str/c7.21.4         **UNDOCUMENTED** String with a null-terminated password to use for the TLS authentication method 
 *                                                          specified with the CURLOPT_TLSAUTH_TYPE option. Requires that the CURLOPT_TLSAUTH_USERNAME option also 
 *                                                          be set. This feature relies in TLS SRP which does not work with TLS 1.3. All TLS-based protocols. 
 *                                                          See https://curl.se/libcurl/c/CURLOPT_TLSAUTH_PASSWORD.html
 * CURLOPT_PROXY_TLSAUTH_PASSWORD       Str/c7.52.0/P7.3.0  The password to use for the TLS authentication method specified with the CURLOPT_PROXY_TLSAUTH_TYPE 
 *                                                          option. Requires that the CURLOPT_PROXY_TLSAUTH_USERNAME option to also be set. All protocols. See
 *                                                          https://curl.se/libcurl/c/CURLOPT_PROXY_TLSAUTH_PASSWORD.html
 * CURLOPT_TLSAUTH_TYPE                 Str/c7.21.4         **UNDOCUMENTED** The string should be the method of the TLS authentication. Supported method is 
 *                                                          "SRP": TLS-SRP authentication. Secure Remote Password authentication for TLS is defined in RFC 5054 
 *                                                          and provides mutual authentication if both sides have a shared secret. To use TLS-SRP, you must also 
 *                                                          set the CURLOPT_TLSAUTH_USERNAME and CURLOPT_TLSAUTH_PASSWORD options. TLS SRP does not work with 
 *                                                          TLS 1.3. All TLS-based protocols. See https://curl.se/libcurl/c/CURLOPT_TLSAUTH_TYPE.html
 * CURLOPT_PROXY_TLSAUTH_TYPE           Str/c7.52.0/P7.3.0  The method of the TLS authentication used for the HTTPS connection. Supported method is "SRP". 
 *                                                          Secure Remote Password (SRP) authentication for TLS provides mutual authentication if both sides 
 *                                                          have a shared secret. To use TLS-SRP, you must also set the CURLOPT_PROXY_TLSAUTH_USERNAME and 
 *                                                          CURLOPT_PROXY_TLSAUTH_PASSWORD options. All protocols. See 
 *                                                          https://curl.se/libcurl/c/CURLOPT_PROXY_TLSAUTH_TYPE.html
 * CURLOPT_PROXYAUTH                    Int/cURL 7.10.7     The HTTP authentication method(s) to use for the proxy connection. Use the same bitmasks as 
 *                                                          described in CURLOPT_HTTPAUTH. For proxy authentication, only CURLAUTH_BASIC and CURLAUTH_NTLM are 
 *                                                          currently supported. HTTP protocol. See https://curl.se/libcurl/c/CURLOPT_PROXYAUTH.html
 * CURLOPT_SASL_AUTHZID                 Str/c7.66.0         **UNDOCUMENTED** The string contains a null-terminated authorization identity (authzid) for the 
 *                                                          transfer. Only applicable to the PLAIN SASL authentication mechanism where it is optional. When not 
 *                                                          specified only the authentication identity (authcid) as specified by the username will be sent to the 
 *                                                          server, along with the password. The server will derive a authzid from the authcid when not provided, 
 *                                                          which it will then uses internally. IMAP, LDAP, POP3 and SMTP protocols. See 
 *                                                          https://curl.se/libcurl/c/CURLOPT_SASL_AUTHZID.html
 * CURLOPT_SASL_IR                      Bool/cURL 7.31.10   TRUE to enable sending the initial response in the first packet. IMAP, POP3 and SMTP protocols. See 
 *                                      PHP 7.0.7           https://curl.se/libcurl/c/CURLOPT_SASL_IR.html
 * CURLOPT_XOAUTH2_BEARER               Str/c7.33.0/P7.0.7  Specifies the OAuth 2.0 access token. HTTP, IMAP, LDAP, POP3 and SMTP protocols. See 
 *                                                          https://curl.se/libcurl/c/CURLOPT_XOAUTH2_BEARER.html
 * CURLOPT_DISALLOW_USERNAME_IN_URL     Bool/c7.61.0/P7.3.0 TRUE to not allow URLs that include a username. Usernames are allowed by default. Several protocols. 
 *                                                          See https://curl.se/libcurl/c/CURLOPT_DISALLOW_USERNAME_IN_URL.html
 * 
 * HTTP
 * Option                               Validation          Explanation
 * CURLOPT_AUTOREFERER                  must be Boolean     TRUE to automatically set the Referer: field in requests where it follows a Location: redirect. HTTP 
 *                                                          protocol. See https://curl.se/libcurl/c/CURLOPT_AUTOREFERER.html
 * CURLOPT_ACCEPT_ENCODING              Str/c7.21.6         **UNDOCUMENTED** This option was called CURLOPT_ENCODING before 7.21.6. The string contains what 
 *                                                          encoding you would like. Sets the contents of the Accept-Encoding: header sent in an HTTP request, 
 *                                                          and enables decoding of a response when a Content-Encoding: header is received. Libcurl potentially 
 *                                                          supports several different compressed encodings depending on what support that has been built-in. To 
 *                                                          aid applications not having to bother about what specific algorithms this particular libcurl build 
 *                                                          supports, libcurl allows a zero-length string to be set ("") to ask for an Accept-Encoding: header 
 *                                                          to be used that contains all built-in supported encodings. Alternatively, you can specify exactly 
 *                                                          the encoding or list of encodings you want in the response. Four encodings are supported: identity, 
 *                                                          meaning non-compressed, deflate which requests the server to compress its response using the zlib 
 *                                                          algorithm, gzip which requests the gzip algorithm, (since curl 7.57.0) br which is brotli and (since 
 *                                                          curl 7.72.0) zstd which is zstd. Provide them in the string as a comma-separated list of accepted 
 *                                                          encodings, like "br, gzip, deflate". Set CURLOPT_ACCEPT_ENCODING to NULL to explicitly disable it, 
 *                                                          which makes libcurl not send an Accept-Encoding: header and not decompress received contents 
 *                                                          automatically. You can also opt to just include the Accept-Encoding: header in your request with 
 *                                                          CURLOPT_HTTPHEADER but then there will be no automatic decompressing when receiving data. This is a 
 *                                                          request, not an order; the server may or may not do it. This option must be set (to any non-NULL 
 *                                                          value) or else any unsolicited encoding done by the server is ignored. Servers might respond with 
 *                                                          Content-Encoding even without getting a Accept-Encoding: in the request. Servers might respond with 
 *                                                          a different Content-Encoding than what was asked for in the request. The Content-Length: servers send 
 *                                                          for a compressed response is supposed to indicate the length of the compressed content so when auto 
 *                                                          decoding is enabled it may not match the sum of bytes reported by the write callbacks (although, 
 *                                                          sending the length of the non-compressed content is a common server mistake). HTTP protocol. See 
 *                                                          https://curl.se/libcurl/c/CURLOPT_ACCEPT_ENCODING.html
 * CURLOPT_TRANSFER_ENCODING            Bool/c7.21.6        **UNDOCUMENTED** Adds a request for compressed Transfer Encoding in the outgoing HTTP request. If the 
 *                                                          server supports this and so desires, it can respond with the HTTP response sent using a compressed 
 *                                                          Transfer-Encoding that will be automatically uncompressed by libcurl on reception. Transfer-Encoding 
 *                                                          differs slightly from the Content-Encoding you ask for with CURLOPT_ACCEPT_ENCODING in that a 
 *                                                          Transfer-Encoding is strictly meant to be for the transfer and thus MUST be decoded before the data 
 *                                                          arrives in the client. Traditionally, Transfer-Encoding has been much less used and supported by both 
 *                                                          HTTP clients and HTTP servers. HTTP protocol. See https://curl.se/libcurl/c/CURLOPT_TRANSFER_ENCODING.html
 * CURLOPT_FOLLOWLOCATION               must be Boolean     TRUE to follow any "Location: " header that the server sends as part of the HTTP header. See also 
 *                                                          CURLOPT_MAXREDIRS. HTTP protocol. See https://curl.se/libcurl/c/CURLOPT_FOLLOWLOCATION.html
 * CURLOPT_UNRESTRICTED_AUTH            must be Boolean     TRUE to keep sending the username and password when following locations (using 
 *                                                          CURLOPT_FOLLOWLOCATION), even when the hostname has changed. HTTP protocol. See 
 *                                                          https://curl.se/libcurl/c/CURLOPT_UNRESTRICTED_AUTH.html
 * CURLOPT_MAXREDIRS                    must be Int         The maximum amount of HTTP redirections to follow. Use this option alongside CURLOPT_FOLLOWLOCATION. 
 *                                                          Default value of 20 is set to prevent infinite redirects. Setting to -1 allows inifinite redirects, 
 *                                                          and 0 refuses all redirects. HTTP protocol. See https://curl.se/libcurl/c/CURLOPT_MAXREDIRS.html
 * CURLOPT_POSTREDIR                    Int/cURL 7.19.1     A bitmask of 1 (301 Moved Permanently), 2 (302 Found) and 4 (303 See Other) if the HTTP POST method 
 *                                                          should be maintained when CURLOPT_FOLLOWLOCATION is set and a specific type of redirect occurs. HTTP 
 *                                                          protocol. See https://curl.se/libcurl/c/CURLOPT_POSTREDIR.html
 * CURLOPT_PUT                          must be Boolean     **DEPRECATED** TRUE to HTTP PUT a file. The file to PUT must be set with CURLOPT_INFILE and 
 *                                                          CURLOPT_INFILESIZE. TRUE tells the library to use HTTP PUT to transfer data. The data should be set 
 *                                                          with CURLOPT_READDATA and CURLOPT_INFILESIZE. This option is deprecated since version 7.12.1. Use 
 *                                                          CURLOPT_UPLOAD! HTTP protocol. See https://curl.se/libcurl/c/CURLOPT_PUT.html
 * CURLOPT_POST                         must be Boolean     TRUE to do a regular HTTP POST. This POST is the normal application/x-www-form-urlencoded kind, most 
 *                                                          commonly used by HTML forms. HTTP protocol. See https://curl.se/libcurl/c/CURLOPT_POST.html
 * CURLOPT_POSTFIELDS                   String or Array     The full data to post in a HTTP "POST" operation. This parameter can either be passed as a urlencoded 
 *                                                          string like 'para1=val1&para2=val2&...' or as an array with the field name as key and field data as 
 *                                                          value. If value is an array, the Content-Type header will be set to multipart/form-data. Files can be 
 *                                                          sent using CURLFile or CURLStringFile, in which case value must be an array. HTTP protocol. See 
 *                                                          https://curl.se/libcurl/c/CURLOPT_POSTFIELDS.html
 * CURLOPT_POSTFIELDSIZE                must be Int         **UNDOCUMENTED** Used to post data to the server without having libcurl do a strlen() to measure the 
 *                                                          data size, this option must be used. When this option is used you can post fully binary data, which 
 *                                                          otherwise is likely to fail. Postable data <= 2GB. HTTP protocol. See 
 *                                                          https://curl.se/libcurl/c/CURLOPT_POSTFIELDSIZE.html
 * CURLOPT_POSTFIELDSIZE_LARGE          must be Int         **UNDOCUMENTED** Used to post large (>2GB) data to the server without having libcurl do a strlen() to 
 *                                                          measure the data size, this option must be used. When this option is used you can post fully binary 
 *                                                          data, which otherwise is likely to fail. HTTP protocol. See 
 *                                                          https://curl.se/libcurl/c/CURLOPT_POSTFIELDSIZE_LARGE.html
 * CURLOPT_COPYPOSTFIELDS               Str/c7.17.1         **UNDOCUMENTED** It behaves as the CURLOPT_POSTFIELDS option, but the original data is instead copied 
 *                                                          by the library, allowing the application to overwrite the original data after setting this option. 
 *                                                          Because data are copied, care must be taken when using this option in conjunction with 
 *                                                          CURLOPT_POSTFIELDSIZE or CURLOPT_POSTFIELDSIZE_LARGE: If the size has not been set prior to 
 *                                                          CURLOPT_COPYPOSTFIELDS, the data is assumed to be a null-terminated string; else the stored size 
 *                                                          informs the library about the byte count to copy. In any case, the size must not be changed after 
 *                                                          CURLOPT_COPYPOSTFIELDS, unless another CURLOPT_POSTFIELDS or CURLOPT_COPYPOSTFIELDS option is issued. 
 *                                                          HTTP protocol. See https://curl.se/libcurl/c/CURLOPT_COPYPOSTFIELDS.html
 * CURLOPT_HTTPPOST                     must be Array       **UNDOCUMENTED AND DEPRECATED** Tells libcurl you want a multipart/formdata HTTP POST to be made and 
 *                                                          you instruct what data to pass on to the server in the formpost argument. Pass a pointer to a linked 
 *                                                          list of curl_httppost structs as parameter. The easiest way to create such a list, is to use 
 *                                                          curl_formadd as documented. This option is deprecated in in 7.56.0! Do not use it. Use 
 *                                                          CURLOPT_MIMEPOST instead after having prepared mime data. HTTP protocol. See 
 *                                                          https://curl.se/libcurl/c/CURLOPT_HTTPPOST.html
 * CURLOPT_REFERER                      must be String      The contents of the "Referer: " header to be used in a HTTP request. HTTP protocol. See 
 *                                                          https://curl.se/libcurl/c/CURLOPT_REFERER.html
 * CURLOPT_USERAGENT                    must be String      The contents of the "User-Agent: " header to be used in a HTTP request. HTTP protocol. See 
 *                                                          https://curl.se/libcurl/c/CURLOPT_USERAGENT.html
 * CURLOPT_HTTPHEADER                   must be Array       An array of HTTP header fields to set, in the format array('Content-type: text/plain', 
 *                                                          'Content-length: 100'). HTTP protocol. See https://curl.se/libcurl/c/CURLOPT_HTTPHEADER.html
 * CURLOPT_HEADEROPT                    int/c7.37.0/P7.0.7  How to deal with headers. One of the following constants:
 *                                                              - CURLHEADER_UNIFIED: the headers specified in CURLOPT_HTTPHEADER will be used in requests both 
 *                                                                to servers and proxies. With this option enabled, CURLOPT_PROXYHEADER will not have any effect.
 *                                                              - CURLHEADER_SEPARATE: makes CURLOPT_HTTPHEADER headers only get sent to a server and not to a 
 *                                                                proxy. Proxy headers must be set with CURLOPT_PROXYHEADER to get used. Note that if a 
 *                                                                non-CONNECT request is sent to a proxy, libcurl will send both server headers and proxy 
 *                                                                headers. When doing CONNECT, libcurl will send CURLOPT_PROXYHEADER headers only to the proxy 
 *                                                                and then CURLOPT_HTTPHEADER headers only to the server.
 *                                                          Defaults to CURLHEADER_SEPARATE as of cURL 7.42.1, and CURLHEADER_UNIFIED before. HTTP protocol. See 
 *                                                          https://curl.se/libcurl/c/CURLOPT_HTTPHEADER.html
 * CURLOPT_PROXYHEADER                 Array/c7.37.0/P7.0.7 An array of custom HTTP headers to pass to proxies. HTTP protocol. See 
 *                                                          https://curl.se/libcurl/c/CURLOPT_PROXYHEADER.html
 * CURLOPT_HTTP200ALIASES               Array/cURL 7.10.3   An array of HTTP 200 responses that will be treated as valid responses and not as errors. HTTP 
 *                                                          protocol. See https://curl.se/libcurl/c/CURLOPT_HTTP200ALIASES.html
 * CURLOPT_COOKIE                       must be String      The contents of the "Cookie: " header to be used in the HTTP request. Note that multiple cookies are 
 *                                                          separated with a semicolon followed by a space (e.g., "fruit=apple; colour=red"). HTTP protocol. See 
 *                                                          https://curl.se/libcurl/c/CURLOPT_COOKIE.html
 * CURLOPT_COOKIEFILE                   must be String      The name of the file containing the cookie data. The cookie file can be in Netscape format, or just 
 *                                                          plain HTTP-style headers dumped into a file. If the name is an empty string, no cookies are loaded, 
 *                                                          but cookie handling is still enabled. HTTP protocol. See 
 *                                                          https://curl.se/libcurl/c/CURLOPT_COOKIEFILE.html
 * CURLOPT_COOKIEJAR                    must be String      The name of a file to save all internal cookies to when the handle is closed, e.g. after a call to 
 *                                                          curl_close. HTTP protocol. See https://curl.se/libcurl/c/CURLOPT_COOKIEJAR.html
 * CURLOPT_COOKIESESSION                must be Boolean     TRUE to mark this as a new cookie "session". It will force libcurl to ignore all cookies it is about
 *                                                          to load that are "session cookies" from the previous session. By default, libcurl always stores and 
 *                                                          loads all cookies, independent if they are session cookies or not. Session cookies are cookies  
 *                                                          without expiry date and they are meant to be alive and existing for this "session" only. HTTP 
 *                                                          protocol. See https://curl.se/libcurl/c/CURLOPT_COOKIESESSION.html
 * CURLOPT_COOKIELIST                   String/cURL 7.14.1  A cookie string (i.e. a single line in Netscape/Mozilla format, or a regular HTTP-style Set-Cookie 
 *                                                          header) adds that single cookie to the internal cookie store. "ALL" erases all cookies held in memory. 
 *                                                          "SESS" erases all session cookies held in memory. "FLUSH" writes all known cookies to the file 
 *                                                          specified by CURLOPT_COOKIEJAR. "RELOAD" loads all cookies from the files specified by 
 *                                                          CURLOPT_COOKIEFILE. HTTP protocol. See https://curl.se/libcurl/c/CURLOPT_COOKIELIST.html
 * CURLOPT_ALTSVC                       Str/c7.64.1         **UNDOCUMENTED** Path to a filename to instruct libcurl to use that file as the Alt-Svc cache to read 
 *                                                          existing cache contents from and possibly also write it back to a after a transfer, unless 
 *                                                          CURLALTSVC_READONLYFILE is set in CURLOPT_ALTSVC_CTRL. Specify a blank file name ("") to make libcurl 
 *                                                          not load from a file at all. HTTPS protocol. See https://curl.se/libcurl/c/CURLOPT_ALTSVC.html
 * CURLOPT_ALTSVC_CTRL                  Int/c7.64.1         **UNDOCUMENTED** Populate the bitmask with the correct set of features to instruct libcurl how to 
 *                                                          handle Alt-Svc for the transfers using this handle. Alternative services are only used when setting 
 *                                                          up new connections. If there exists an existing connection to the host in the connection pool, then 
 *                                                          that will be preferred. Setting any bit will enable the alt-svc engine. Options: 
 *                                                          - CURLALTSVC_READONLYFILE: Do not write the alt-svc cache back to the file specified with 
 *                                                            CURLOPT_ALTSVC even if it gets updated. By default a file specified with that option will be read 
 *                                                            and written to as deemed necessary. 
 *                                                          - CURLALTSVC_H1: Accept alternative services offered over HTTP/1.1. 
 *                                                          - CURLALTSVC_H2: Accept alternative services offered over HTTP/2. This will only be used if libcurl 
 *                                                            was also built to actually support HTTP/2, otherwise this bit will be ignored. 
 *                                                          - CURLALTSVC_H3: Accept alternative services offered over HTTP/3. This will only be used if libcurl 
 *                                                            was also built to actually support HTTP/3, otherwise this bit will be ignored. 
 *                                                          HTTPS protocol. See https://curl.se/libcurl/c/CURLOPT_ALTSVC_CTRL.html
 * CURLOPT_HSTS                         Str/c7.74.0         **UNDOCUMENTED** String filename point to a file name to load an existing HSTS cache from, and to 
 *                                                          store the cache in when the easy handle is closed. Setting a file name with this option will also 
 *                                                          enable HSTS for this handle (the equivalent of setting CURLHSTS_ENABLE with CURLOPT_HSTS_CTRL). If 
 *                                                          the given file does not exist or contains no HSTS entries at startup, the HSTS cache will simply start 
 *                                                          empty. Setting the file name to NULL or "" will only enable HSTS without reading from or writing to 
 *                                                          any file. If this option is set multiple times, libcurl will load cache entries from each given file 
 *                                                          but will only store the last used name for later writing. HTTP protocol. See 
 *                                                          https://curl.se/libcurl/c/CURLOPT_HSTS.html
 * CURLOPT_HSTS_CTRL                    Int/c7.74.0         **UNDOCUMENTED** HSTS (HTTP Strict Transport Security) means that an HTTPS server can instruct the 
 *                                                          client to not contact it again over clear-text HTTP for a certain period into the future. libcurl will 
 *                                                          then automatically redirect HTTP attempts to such hosts to instead use HTTPS. Populate the bitmask 
 *                                                          with the correct set of features to instruct libcurl how to handle HSTS:
 *                                                          - CURLHSTS_ENABLE: Enable the in-memory HSTS cache for this handle.
 *                                                          - CURLHSTS_READONLYFILE: Make the HSTS file (if specified) read-only - makes libcurl not save the 
 *                                                            cache to the file when closing the handle. 
 *                                                          HTTP protocol. See https://curl.se/libcurl/c/CURLOPT_HSTS_CTRL.html
 * CURLOPT_HSTSREADFUNCTION             Callback/c7.74.0    **UNDOCUMENTED** This callback function gets called by libcurl repeatedly when it populates the 
 *                                                          in-memory HSTS cache. HTTP protocol. See https://curl.se/libcurl/c/CURLOPT_HSTSREADFUNCTION.html
 * CURLOPT_HSTSREADDATA                 Str/c7.74.0         **UNDOCUMENTED** Data pointer to pass to the HSTS read function. If you use the 
 *                                                          CURLOPT_HSTSREADFUNCTION option, this is the pointer you will get as input in the 3rd argument to 
 *                                                          the callback. This option does not enable HSTS, you need to use CURLOPT_HSTS_CTRL to do that. HTTP 
 *                                                          protocol. See https://curl.se/libcurl/c/CURLOPT_HSTSREADDATA.html
 * CURLOPT_HSTSWRITEFUNCTION            Callback/c7.74.0    **UNDOCUMENTED** This callback function gets called by libcurl repeatedly to allow the application 
 *                                                          to store the in-memory HSTS cache when libcurl is about to discard it. HTTP protocol. See 
 *                                                          https://curl.se/libcurl/c/CURLOPT_HSTSWRITEFUNCTION.html
 * CURLOPT_HSTSWRITEDATA                Str/c7.74.0         **UNDOCUMENTED** Data pointer to pass to the HSTS write function. If you use the 
 *                                                          CURLOPT_HSTSWRITEFUNCTION option, this is the pointer you will get as input in the 4th argument to 
 *                                                          the callback. This option does not enable HSTS, you need to use CURLOPT_HSTS_CTRL to do that. HTTP 
 *                                                          protocol. See https://curl.se/libcurl/c/CURLOPT_HSTSWRITEDATA.html
 * CURLOPT_HTTPGET                      must be Boolean     TRUE to reset the HTTP request method to GET. Since GET is the default, this is only necessary if 
 *                                                          the request method has been changed. HTTP protocol. See https://curl.se/libcurl/c/CURLOPT_HTTPGET.html
 * CURLOPT_REQUEST_TARGET               Str/c7.55.0         **UNDOCUMENTED** String which libcurl uses in the upcoming request instead of the path as extracted 
 *                                                          from the URL. HTTP protocol. See https://curl.se/libcurl/c/CURLOPT_REQUEST_TARGET.html
 * CURLOPT_HTTP_VERSION                 must be Int         CURL_HTTP_VERSION_NONE (default, lets CURL decide which version to use), CURL_HTTP_VERSION_1_0 
 *                                                          (forces HTTP/1.0), CURL_HTTP_VERSION_1_1 (forces HTTP/1.1), CURL_HTTP_VERSION_2_0 (attempts HTTP 2), 
 *                                                          CURL_HTTP_VERSION_2 (alias of CURL_HTTP_VERSION_2_0), CURL_HTTP_VERSION_2TLS (attempts HTTP 2 over 
 *                                                          TLS (HTTPS) only) or CURL_HTTP_VERSION_2_PRIOR_KNOWLEDGE (issues non-TLS HTTP requests using HTTP/2 
 *                                                          without HTTP/1.1 Upgrade). HTTP protocol. See https://curl.se/libcurl/c/CURLOPT_HTTP_VERSION.html
 * CURLOPT_HTTP09_ALLOWED               Bool/c7.64.0        Whether to allow HTTP/0.9 responses. Defaults to FALSE (libcurl 7.66.0); Previously defaulted to 
 *                                                          TRUE. HTTP protocol. See https://curl.se/libcurl/c/CURLOPT_HTTP09_ALLOWED.html
 * CURLOPT_IGNORE_CONTENT_LENGTH        Bool/c7.14.1        **UNDOCUMENTED** TRUE to ignore the Content-Length header in the HTTP response and ignore asking for 
 *                                                          or relying on it for FTP transfers. This is useful for HTTP with Apache 1.x (and similar servers) 
 *                                                          which will report incorrect content length for files over 2 gigabytes. If this option is used, curl 
 *                                                          will not be able to accurately report progress, and will simply stop the download when the server 
 *                                                          ends the connection. It is also useful with FTP when for example the file is growing while the 
 *                                                          transfer is in progress which otherwise will unconditionally cause libcurl to report error. Only use 
 *                                                          this option if strictly necessary. HTTP, FTP protocols. See 
 *                                                          https://curl.se/libcurl/c/CURLOPT_IGNORE_CONTENT_LENGTH.html
 * CURLOPT_HTTP_CONTENT_DECODING        Boolean/cURL 7.16.2 FALSE to get the raw HTTP response body. HTTP protocol. See 
 *                                                          https://curl.se/libcurl/c/CURLOPT_HTTP_CONTENT_DECODING.html
 * CURLOPT_HTTP_TRANSFER_DECODING       Boolean/cURL 7.16.2 **UNDOCUMENTED** If FALSE, transfer decoding will be disabled, if TRUE it is enabled (default). 
 *                                                          libcurl does chunked transfer decoding by default unless this option is set to zero. HTTP protocol. 
 *                                                          See https://curl.se/libcurl/c/CURLOPT_HTTP_TRANSFER_DECODING.html
 * CURLOPT_EXPECT_100_TIMEOUT_MS        Int/c7.36.0/P7.0.7  The number of milliseconds to wait for a server response with the HTTP status 100 (Continue), 417 
 *                                                          (Expectation Failed) or similar after sending an HTTP request containing an Expect: 100-continue 
 *                                                          header. If this times out before a response is received, the request body is sent anyway.Defaults to 
 *                                                          1000 milliseconds. HTTP protocol. See https://curl.se/libcurl/c/CURLOPT_EXPECT_100_TIMEOUT_MS.html
 * CURLOPT_TRAILERFUNCTION              Callback/c7.64.0    **UNDOCUMENTED** This callback function will be called once right before sending the final CR LF in 
 *                                                          an HTTP chunked transfer to fill a list of trailing headers to be sent before finishing the HTTP 
 *                                                          transfer. You can set the userdata argument with the CURLOPT_TRAILERDATA option. The trailing headers 
 *                                                          included in the linked list must not be CRLF-terminated, because libcurl will add the appropriate 
 *                                                          line termination characters after each header item. If you use curl_slist_append to add trailing 
 *                                                          headers to the curl_slist then libcurl will duplicate the strings, and will free the curl_slist and 
 *                                                          the duplicates once the trailers have been sent. If one of the trailing headers is not formatted 
 *                                                          correctly (i.e. HeaderName: headerdata) it will be ignored and an info message will be emitted. The 
 *                                                          return value can either be CURL_TRAILERFUNC_OK or CURL_TRAILERFUNC_ABORT which would respectively 
 *                                                          instruct libcurl to either continue with sending the trailers or to abort the request. If you set 
 *                                                          this option to NULL, then the transfer proceeds as usual without any interruptions. HTTP protocol. 
 *                                                          See https://curl.se/libcurl/c/CURLOPT_TRAILERFUNCTION.html
 * CURLOPT_TRAILERDATA                  Str/c7.64.0         **UNDOCUMENTED** Data pointer to be passed to the HTTP trailer callback function. HTTP protocol. See 
 *                                                          https://curl.se/libcurl/c/CURLOPT_TRAILERDATA.html
 * CURLOPT_PIPEWAIT                     Bool/c7.43.0/P7.0.7 TRUE to wait for pipelining/multiplexing. HTTP protocol. See 
 *                                                          https://curl.se/libcurl/c/CURLOPT_PIPEWAIT.html
 * CURLOPT_STREAM_DEPENDS               cURL handle/c7.46.0 **UNDOCUMENTED** dephandle identifies the stream within the same connection that this stream is 
 *                                                          depending upon. This option clears the exclusive bit and is mutually exclusive to the 
 *                                                          CURLOPT_STREAM_DEPENDS_E option. The spec says "Including a dependency expresses a preference to 
 *                                                          allocate resources to the identified stream rather than to the dependent stream." This option can be 
 *                                                          set during transfer. dephandle must not be the same as handle, that will cause this function to return 
 *                                                          an error. It must be another easy handle, and it also needs to be a handle of a transfer that will be 
 *                                                          sent over the same HTTP/2 connection for this option to have an actual effect. HTTP/2 protocol. See 
 *                                                          https://curl.se/libcurl/c/CURLOPT_STREAM_DEPENDS.html
 * CURLOPT_STREAM_DEPENDS_E             cURL handle/c7.46.0 **UNDOCUMENTED** dephandle identifies the stream within the same connection that this stream is 
 *                                                          depending upon exclusively. That means it depends on it and sets the Exclusive bit. The spec says 
 *                                                          "Including a dependency expresses a preference to allocate resources to the identified stream rather 
 *                                                          than to the dependent stream.". Setting a dependency with the exclusive flag for a reprioritized 
 *                                                          stream causes all the dependencies of the new parent stream to become dependent on the reprioritized 
 *                                                          stream. This option can be set during transfer. dephandle must not be the same as handle, that will 
 *                                                          cause this function to return an error. It must be another easy handle, and it also needs to be a 
 *                                                          handle of a transfer that will be sent over the same HTTP/2 connection for this option to have an 
 *                                                          actual effect. HTTP/2 protocol. See https://curl.se/libcurl/c/CURLOPT_STREAM_DEPENDS_E.html
 * CURLOPT_STREAM_WEIGHT                Int/c7.46.0/P7.0.7  Set the numerical stream weight (a number between 1 and 256). HTTP/2 protocol. See 
 *                                                          https://curl.se/libcurl/c/CURLOPT_STREAM_WEIGHT.html
 * 
 * SMTP
 * Option                               Validation          Explanation
 * CURLOPT_MAIL_FROM                    Str/c7.20.0         **UNDOCUMENTED** String should be used to specify the sender's email address when sending SMTP mail 
 *                                                          with libcurl. An originator email address should be specified with angled brackets (<>) around it, 
 *                                                          which if not specified will be added automatically. If this parameter is not specified then an empty 
 *                                                          address will be sent to the mail server which may cause the email to be rejected. SMTP protocol. See 
 *                                                          https://curl.se/libcurl/c/CURLOPT_MAIL_FROM.html
 * CURLOPT_MAIL_RCPT                    Array/c7.20.0       **UNDOCUMENTED** Linked list of recipients to pass to the server in your SMTP mail request. When 
 *                                                          performing a mail transfer, each recipient should be specified within a pair of angled brackets (<>), 
 *                                                          however, should you not use an angled bracket as the first character libcurl will assume you provided 
 *                                                          a single email address and enclose that address within brackets for you. When performing an address 
 *                                                          verification (VRFY command), each recipient should be specified as the user name or user name and 
 *                                                          domain (as per Section 3.5 of RFC 5321). When performing a mailing list expand (EXPN command), each 
 *                                                          recipient should be specified using the mailing list name, such as "Friends" or "London-Office". SMTP 
 *                                                          protocol. See https://curl.se/libcurl/c/CURLOPT_MAIL_RCPT.html
 * CURLOPT_MAIL_AUTH                    Str/c7.25.0         **UNDOCUMENTED** String will be used to specify the authentication address (identity) of a submitted 
 *                                                          message that is being relayed to another server. This optional parameter allows co-operating agents 
 *                                                          in a trusted environment to communicate the authentication of individual messages and should only be 
 *                                                          used by the application program, using libcurl, if the application is itself a mail server acting in 
 *                                                          such an environment. If the application is operating as such and the AUTH address is not known or is 
 *                                                          invalid, then an empty string should be used for this parameter. Unlike CURLOPT_MAIL_FROM and 
 *                                                          CURLOPT_MAIL_RCPT, the address should not be specified within a pair of angled brackets (<>). However, 
 *                                                          if an empty string is used then a pair of brackets will be sent by libcurl as required by RFC 2554.  
 *                                                          SMTP protocol. See https://curl.se/libcurl/c/CURLOPT_MAIL_AUTH.html
 * CURLOPT_MAIL_RCPT_ALLLOWFAILS        Bool/c7.69.0        **UNDOCUMENTED** When sending data to multiple recipients, by default curl will abort SMTP conversation 
 *                                                          if at least one of the recipients causes RCPT TO command to return an error. The default behavior can 
 *                                                          be changed by setting ignore to 1L which will make curl ignore errors and proceed with the remaining 
 *                                                          valid recipients. If all recipients trigger RCPT TO failures and this flag is specified, curl will 
 *                                                          still abort the SMTP conversation and return the error received from to the last RCPT TO command. 
 *                                                          SMTP protocol. See https://curl.se/libcurl/c/CURLOPT_MAIL_RCPT_ALLLOWFAILS.html
 * 
 * TFTP
 * Option                               Validation          Explanation
 * CURLOPT_TFTP_BLKSIZE                 Int/c7.19.4         **UNDOCUMENTED** Specify blocksize to use for TFTP data transmission. Valid range as per RFC 2348 is 
 *                                                          8-65464 bytes. The default of 512 bytes will be used if this option is not specified. The specified 
 *                                                          block size will only be used pending support by the remote server. If the server does not return an 
 *                                                          option acknowledgement or returns an option acknowledgement with no blksize, the default of 512 bytes 
 *                                                          will be used. TFTP protocol. See https://curl.se/libcurl/c/CURLOPT_TFTP_BLKSIZE.html
 * CURLOPT_TFTP_NO_OPTIONS              Bool/c7.48.0/P7.0.7 TRUE to not send TFTP options requests. Excludes all TFTP options defined in RFC 2347, RFC 2348 and 
 *                                                          RFC 2349 from read and write requests. TFTP protocol. See 
 *                                                          https://curl.se/libcurl/c/CURLOPT_TFTP_NO_OPTIONS.html
 * 
 * FTP
 * Option                               Validation          Explanation
 * CURLOPT_FTPPORT                      must be String      The value which will be used to get the IP address to use for the FTP "PORT" instruction. The "PORT" 
 *                                                          instruction tells the remote server to connect to our specified IP address. The string may be a 
 *                                                          plain IP address, a hostname, a network interface name (under Unix), or just a plain '-' to use the 
 *                                                          systems default IP address. FTP protocol. See https://curl.se/libcurl/c/CURLOPT_FTPPORT.html
 * CURLOPT_QUOTE                        must be Array       An array of FTP commands to execute on the server prior to the FTP request. FTP, SFTP protocols. See 
 *                                                          https://curl.se/libcurl/c/CURLOPT_QUOTE.html
 * CURLOPT_POSTQUOTE                    must be Array       An array of FTP commands to execute on the server after the FTP request has been performed. FTP, SFTP 
 *                                                          protocols. See https://curl.se/libcurl/c/CURLOPT_POSTQUOTE.html
 * CURLOPT_PREQUOTE                     must be Array       **UNDOCUMENTED** FTP commands to pass to the server after the transfer type is set. FTP protocol. 
 *                                                          See https://curl.se/libcurl/c/CURLOPT_PREQUOTE.html
 * CURLOPT_APPEND                       must be Boolean     **UNDOCUMENTED** TRUE to append to the remote file instead of overwrite it. This option was known as 
 *                                                          CURLOPT_FTPAPPEND up to 7.16.4. FTP protocol. See https://curl.se/libcurl/c/CURLOPT_APPEND.html
 * CURLOPT_FTP_USE_EPRT                 Bool/c7.10.5        TRUE to use EPRT (and LPRT) when doing active FTP downloads. Use false to disable EPRT and LPRT and 
 *                                                          use PORT only. FTP protocol. See https://curl.se/libcurl/c/CURLOPT_FTP_USE_EPRT.html
 * CURLOPT_FTP_USE_EPSV                 must be Boolean     TRUE to first try an EPSV command for FTP transfers before reverting back to PASV. Set to false to 
 *                                                          disable EPSV. FTP protocol. See https://curl.se/libcurl/c/CURLOPT_FTP_USE_EPSV.html
 * CURLOPT_FTP_USE_PRET                 Bool/c7.20.0        **UNDOCUMENTED** TRUE tells curl to send a PRET command before PASV (and EPSV). Certain FTP servers, 
 *                                                          mainly drftpd, require this non-standard command for directory listings as well as up and downloads 
 *                                                          in PASV mode. Has no effect when using the active FTP transfers mode. FTP protocol. See
 *                                                          https://curl.se/libcurl/c/CURLOPT_FTP_USE_PRET.html
 * CURLOPT_FTP_CREATE_MISSING_DIRS      Bool/c7.10.7        TRUE to create missing directories when an FTP operation encounters a path that currently doesn't 
 *                                                          exist. Added in 7.10.7. SFTP support added in 7.16.3. The retry option was added in 7.19.4. FTP, SFTP 
 *                                                          protocols. See https://curl.se/libcurl/c/CURLOPT_FTP_CREATE_MISSING_DIRS.html
 * CURLOPT_SERVER_RESPONSE_TIMEOUT      Int/c7.20.0         **UNDOCUMENTED** Set a timeout period (in seconds) on the amount of time that the server is allowed 
 *                                                          to take in order to send a response message for a command before the session is considered dead. FTP, 
 *                                                          IMAP, POP3 and SMTP protocols. See https://curl.se/libcurl/c/CURLOPT_SERVER_RESPONSE_TIMEOUT.html
 * CURLOPT_FTP_ALTERNATIVE_TO_USER      Str/c7.15.5         **UNDOCUMENTED** String which will be used to authenticate if the usual FTP "USER user" and "PASS 
 *                                                          password" negotiation fails. This is currently only known to be required when connecting to 
 *                                                          Tumbleweed's Secure Transport FTPS server using client certificates for authentication. FTP protocol. 
 *                                                          See https://curl.se/libcurl/c/CURLOPT_FTP_ALTERNATIVE_TO_USER.html
 * CURLOPT_FTP_SKIP_PASV_IP             Bool/c7.14.2        **UNDOCUMENTED** TRUE instructs libcurl to not use the IP address the server suggests in its 
 *                                                          227-response to libcurl's PASV command when libcurl connects the data connection. Instead libcurl 
 *                                                          will re-use the same IP address it already uses for the control connection. But it will use the port 
 *                                                          number from the 227-response. This option thus allows libcurl to work around broken server 
 *                                                          installations that due to NATs, firewalls or incompetence report the wrong IP address back. Setting 
 *                                                          the option also reduces the risk for various sorts of client abuse by malicious servers. FTP protocol. 
 *                                                          See https://curl.se/libcurl/c/CURLOPT_FTP_SKIP_PASV_IP.html
 * CURLOPT_FTPSSLAUTH                   Int/cURL 7.12.2     The FTP authentication method (when is activated): CURLFTPAUTH_SSL (try SSL first), CURLFTPAUTH_TLS 
 *                                                          (try TLS first), or CURLFTPAUTH_DEFAULT (let cURL decide). FTP protocol. See 
 *                                                          https://curl.se/libcurl/c/CURLOPT_FTPSSLAUTH.html
 * CURLOPT_FTP_SSL_CCC                  Int/c7.16.1         **UNDOCUMENTED** If enabled, makes libcurl use CCC (Clear Command Channel). It shuts down the SSL/TLS 
 *                                                          layer after authenticating. The rest of the control channel communication will be unencrypted. This 
 *                                                          allows NAT routers to follow the FTP transaction. Pass a long using one of the values below:
 *                                                          - CURLFTPSSL_CCC_NONE: do not attempt to use CCC. 
 *                                                          - CURLFTPSSL_CCC_PASSIVE: Do not initiate the shutdown, but wait for the server to do it. Do not 
 *                                                            send a reply. 
 *                                                          - CURLFTPSSL_CCC_ACTIVE: Initiate the shutdown and wait for a reply.
 *                                                          FTP protocol. See https://curl.se/libcurl/c/CURLOPT_FTP_SSL_CCC.html
 * CURLOPT_FTP_ACCOUNT                  Str/c7.13.0         **UNDOCUMENTED** When an FTP server asks for "account data" after user name and password has been 
 *                                                          provided, this string is sent off using the ACCT command. FTP protocol. See 
 *                                                          https://curl.se/libcurl/c/CURLOPT_FTP_ACCOUNT.html
 * CURLOPT_FTP_FILEMETHOD               int/cURL 7.15.1     Tell curl which method to use to reach a file on a FTP(S) server. Possible values are 
 *                                                          CURLFTPMETHOD_MULTICWD, CURLFTPMETHOD_NOCWD and CURLFTPMETHOD_SINGLECWD. FTP protocol. See 
 *                                                          https://curl.se/libcurl/c/CURLOPT_FTP_FILEMETHOD.html
 * 
 * RTSP
 * Option                               Validation          Explanation
 * CURLOPT_RTSP_REQUEST                 Int/c7.20.0         **UNDOCUMENTED** Tell libcurl what kind of RTSP request to make. Pass one of the following RTSP enum 
 *                                                          values: CURL_RTSPREQ_OPTIONS, CURL_RTSPREQ_DESCRIBE, CURL_RTSPREQ_ANNOUNCE, CURL_RTSPREQ_SETUP, 
 *                                                          CURL_RTSPREQ_PLAY, CURL_RTSPREQ_PAUSE, CURL_RTSPREQ_TEARDOWN, CURL_RTSPREQ_GET_PARAMETER, 
 *                                                          CURL_RTSPREQ_SET_PARAMETER, CURL_RTSPREQ_RECORD, CURL_RTSPREQ_RECEIVE. RTSP protocol. See 
 *                                                          https://curl.se/libcurl/c/CURLOPT_RTSP_REQUEST.html
 * CURLOPT_RTSP_SESSION_ID              Str/c7.20.0         **UNDOCUMENTED** Set the value of the current RTSP Session ID for the handle. RTSP protocol. See 
 *                                                          https://curl.se/libcurl/c/CURLOPT_RTSP_SESSION_ID.html
 * CURLOPT_RTSP_STREAM_URI              Str/c7.20.0         **UNDOCUMENTED** Set the stream URI to operate on. RTSP protocol. See 
 *                                                          https://curl.se/libcurl/c/CURLOPT_RTSP_STREAM_URI.html
 * CURLOPT_RTSP_TRANSPORT               Str/c7.20.0         **UNDOCUMENTED** String to pass for the Transport: header for this RTSP session. RTSP protocol. See 
 *                                                          https://curl.se/libcurl/c/CURLOPT_RTSP_TRANSPORT.html
 * CURLOPT_RTSP_CLIENT_CSEQ             Int/c7.20.0         **UNDOCUMENTED** Set the CSEQ number to issue for the next RTSP request. RTSP protocol. See 
 *                                                          https://curl.se/libcurl/c/CURLOPT_RTSP_CLIENT_CSEQ.html
 * CURLOPT_RTSP_SERVER_CSEQ             Int/c7.20.0         **UNDOCUMENTED** Set the CSEQ number to expect for the next RTSP Server->Client request. NOTE: this 
 *                                                          feature (listening for Server requests) is unimplemented. RTSP protocol. See 
 *                                                          https://curl.se/libcurl/c/CURLOPT_RTSP_SERVER_CSEQ.html
 * CURLOPT_AWS_SIGV4                    Str/c7.75.0         **UNDOCUMENTED** String providing AWS V4 signature authentication on HTTP(S) header. HTTP protocol. 
 *                                                          See https://curl.se/libcurl/c/CURLOPT_AWS_SIGV4.html
 * 
 * Protocol Options
 * Option                               Validation          Explanation
 * CURLOPT_TRANSFERTEXT                 must be Boolean     TRUE to use ASCII mode for FTP transfers. For LDAP, it retrieves data in plain text instead of HTML. 
 *                                                          On Windows systems, it will not set STDOUT to binary mode. FTP protocol. See 
 *                                                          https://curl.se/libcurl/c/CURLOPT_TRANSFERTEXT.html
 * CURLOPT_PROXY_TRANSFER_MODE          Bool/c7.18.0        **UNDOCUMENTED** TRUE tells libcurl to set the transfer mode (binary or ASCII) for FTP transfers done 
 *                                                          via an HTTP proxy, by appending ;type=a or ;type=i to the URL. Without this setting, or it being set 
 *                                                          to FALSE (zero, the default), CURLOPT_TRANSFERTEXT has no effect when doing FTP via a proxy. FTP 
 *                                                          protocol over HTTP proxy. See https://curl.se/libcurl/c/CURLOPT_PROXY_TRANSFER_MODE.html
 * CURLOPT_CRLF                         must be Boolean     TRUE to convert Unix newlines to CRLF newlines on transfers. This is a legacy option of questionable 
 *                                                          use. All protocols. See https://curl.se/libcurl/c/CURLOPT_CRLF.html
 * CURLOPT_RANGE                        must be String      Range(s) of data to retrieve in the format "X-Y" where X or Y are optional. HTTP transfers also 
 *                                                          support several intervals, separated with commas in the format "X-Y,N-M". HTTP, FTP, FILE, RTSP and 
 *                                                          SFTP protocols. See https://curl.se/libcurl/c/CURLOPT_RANGE.html
 * CURLOPT_RESUME_FROM                  must be Int         The offset, in bytes, to resume a transfer from. Set this option to 0 to make the transfer start from 
 *                                                          the beginning (effectively disabling resume). For FTP, set this option to -1 to make the transfer 
 *                                                          start from the end of the target file (useful to continue an interrupted upload). If you need to 
 *                                                          resume a transfer beyond the 2GB limit, use CURLOPT_RESUME_FROM_LARGE instead. HTTP, FTP, SFTP, FILE 
 *                                                          protocols. See https://curl.se/libcurl/c/CURLOPT_RESUME_FROM.html
 * CURLOPT_RESUME_FROM_LARGE            Int/c7.11.0         **UNDOCUMENTED** Set the offset in number of bytes that you want the transfer to start from. Set this 
 *                                                          option to 0 to make the transfer start from the beginning (effectively disabling resume). For FTP, set 
 *                                                          this option to -1 to make the transfer start from the end of the target file (useful to continue an 
 *                                                          interrupted upload). HTTP, FTP, SFTP, FILE protocols. See 
 *                                                          https://curl.se/libcurl/c/CURLOPT_RESUME_FROM_LARGE.html
 * CURLOPT_CURLU                        Str/c7.63.0         **UNDOCUMENTED** Set CURL URL format (CURLU) instead of standard one (URL). All protocols. See 
 *                                                          https://curl.se/libcurl/c/CURLOPT_CURLU.html
 * CURLOPT_CUSTOMREQUEST                must be String      A custom request method to use instead of "GET" or "HEAD" when doing a HTTP request. This is useful 
 *                                                          for doing "DELETE" or other, more obscure HTTP requests. Valid values are things like "GET", "POST", 
 *                                                          "CONNECT" and so on. HTTP, FTP, IMAP, POP3 and SMTP protocols. See 
 *                                                          https://curl.se/libcurl/c/CURLOPT_CUSTOMREQUEST.html
 * CURLOPT_FILETIME                     must be Boolean     TRUE to attempt to retrieve the modification date of the remote document. This value can be 
 *                                                          retrieved using the CURLINFO_FILETIME option with curl_getinfo(). HTTP(S), FTP(S), SFTP, FILE, SMB(S) 
 *                                                          protocols. See https://curl.se/libcurl/c/CURLOPT_FILETIME.html
 * CURLOPT_DIRLISTONLY                  must be Boolean     **UNDOCUMENTED** For FTP and SFTP based URLs, TRUE tells the library to list the names of files in a 
 *                                                          directory, rather than performing a full directory listing that would normally include file sizes, 
 *                                                          dates etc. For POP3, TRUE tells the library to list the email message or messages on the POP3 server. 
 *                                                          This can be used to change the default behavior of libcurl, when combined with a URL that contains a 
 *                                                          message ID, to perform a "scan listing" which can then be used to determine the size of an email. Do 
 *                                                          NOT use this option if you also use CURLOPT_WILDCARDMATCH as it will effectively break that feature 
 *                                                          then. FTP, SFTP and POP3 protocols. See https://curl.se/libcurl/c/CURLOPT_DIRLISTONLY.html
 * CURLOPT_NOBODY                       must be Boolean     TRUE to exclude the body from the output. Request method is then set to HEAD. Changing this to false 
 *                                                          does not change it to GET. Most protocols. See https://curl.se/libcurl/c/CURLOPT_NOBODY.html
 * CURLOPT_INFILESIZE                   must be Int         The expected size, in bytes, of the file when uploading a file to a remote site. Note that using 
 *                                                          this option will not stop libcurl from sending more data, as exactly what is sent depends on 
 *                                                          CURLOPT_READFUNCTION. Many protocols. See https://curl.se/libcurl/c/CURLOPT_INFILESIZE.html
 * CURLOPT_INFILESIZE_LARGE             must be Int         **UNDOCUMENTED** When uploading a file to a remote site, filesize should be used to tell libcurl what 
 *                                                          the expected size of the input file is. For files larger than 2GB. Many protocols. See 
 *                                                          https://curl.se/libcurl/c/CURLOPT_INFILESIZE_LARGE.html
 * CURLOPT_UPLOAD                       must be Boolean     TRUE to prepare for an upload. If you set CURLOPT_UPLOAD to TRUE, libcurl will send a PUT method in 
 *                                                          its HTTP request. Most protocols. See https://curl.se/libcurl/c/CURLOPT_UPLOAD.html
 * CURLOPT_UPLOAD_BUFFERSIZE            Int/c7.62.0         **UNDOCUMENTED** Specify upload buffer preferred size (in bytes) for the upload buffer in libcurl. 
 *                                                          It makes libcurl uses a larger buffer that gets passed to the next layer in the stack to get sent 
 *                                                          off. In some setups and for some protocols, there's a huge performance benefit of having a larger 
 *                                                          upload buffer. This is just treated as a request, not an order. You cannot be guaranteed to actually 
 *                                                          get the given size. The upload buffer size is by default 64 kilobytes. The maximum buffer size allowed 
 *                                                          to be set is 2 megabytes. The minimum buffer size allowed to be set is 16 kilobytes. All protocols. 
 *                                                          See https://curl.se/libcurl/c/CURLOPT_UPLOAD_BUFFERSIZE.html
 * CURLOPT_MIMEPOST                     Array/c7.56.0       **UNDOCUMENTED** Pass a mime handle previously obtained from curl_mime_init. This setting is supported 
 *                                                          by the HTTP protocol to post forms and by the SMTP and IMAP protocols to provide the email data to 
 *                                                          send/upload. This option is the preferred way of posting an HTTP form, replacing and extending the 
 *                                                          deprecated CURLOPT_HTTPPOST option. HTTP, SMTP, IMAP protocols. See 
 *                                                          https://curl.se/libcurl/c/CURLOPT_MIMEPOST.html
 * CURLOPT_MIME_OPTIONS                 Int/c7.81.0         **UNDOCUMENTED** Only available option is CURLMIMEOPT_FORMESCAPE, which tells libcurl to escape 
 *                                                          multipart form field and file names using the backslash-escaping algorithm rather than 
 *                                                          percent-encoding (HTTP only). HTTP, IMAP, SMTP protocols. See 
 *                                                          https://curl.se/libcurl/c/CURLOPT_MIME_OPTIONS.html
 * CURLOPT_MAXFILESIZE                  must be Int         **UNDOCUMENTED** Specify the maximum size (in bytes) of a file to download. If the file requested is 
 *                                                          found larger than this value, the transfer will not start and CURLE_FILESIZE_EXCEEDED will be returned. 
 *                                                          The file size is not always known prior to download, and for such files this option has no effect even 
 *                                                          if the file transfer ends up being larger than this given limit. If you want a limit above 2GB, use 
 *                                                          CURLOPT_MAXFILESIZE_LARGE. FTP, HTTP and MQTT protocols. See 
 *                                                          https://curl.se/libcurl/c/CURLOPT_MAXFILESIZE.html
 * CURLOPT_MAXFILESIZE_LARGE            Int/c7.11.0         **UNDOCUMENTED** Specify the maximum size (in bytes) of a file to download. If the file requested is 
 *                                                          found larger than this value, the transfer will not start and CURLE_FILESIZE_EXCEEDED will be returned. 
 *                                                          The file size is not always known prior to download, and for such files this option has no effect even 
 *                                                          if the file transfer ends up being larger than this given limit. FTP, HTTP and MQTT protocols. See 
 *                                                          https://curl.se/libcurl/c/CURLOPT_MAXFILESIZE_LARGE.html
 * CURLOPT_TIMECONDITION                must be Int         How CURLOPT_TIMEVALUE is treated. Use CURL_TIMECOND_IFMODSINCE to return the page only if it has 
 *                                                          been modified since the time specified in CURLOPT_TIMEVALUE. If it hasn't been modified, a "304 Not 
 *                                                          Modified" header will be returned assuming CURLOPT_HEADER is true. Use CURL_TIMECOND_IFUNMODSINCE 
 *                                                          for the reverse effect. Use CURL_TIMECOND_NONE to ignore CURLOPT_TIMEVALUE and always return the 
 *                                                          page. CURL_TIMECOND_NONE is the default. HTTP, FTP, RTSP, and FILE protocols. See
 *                                                          https://curl.se/libcurl/c/CURLOPT_TIMECONDITION.html
 * CURLOPT_TIMEVALUE                    must be Int         The time in seconds since January 1st, 1970. The time will be used by CURLOPT_TIMECONDITION. HTTP, 
 *                                                          FTP, RTSP, and FILE protocols. See https://curl.se/libcurl/c/CURLOPT_TIMEVALUE.html
 * CURLOPT_TIMEVALUE_LARGE              Int/c7.59.0/P7.3.0  The time in seconds since January 1st, 1970. The time will be used by CURLOPT_TIMECONDITION. 
 *                                                          Defaults to zero. The difference between this option and CURLOPT_TIMEVALUE is the type of the 
 *                                                          argument. On systems where 'long' is only 32 bit wide, this option has to be used to set dates 
 *                                                          beyond the year 2038. HTTP, FTP, RTSP, and FILE protocols. See 
 *                                                          https://curl.se/libcurl/c/CURLOPT_TIMEVALUE_LARGE.html
 * 
 * Connection Options
 * Option                               Validation          Explanation
 * CURLOPT_TIMEOUT                      must be Int         The maximum number of seconds to allow cURL functions to execute. All protocols. See 
 *                                                          https://curl.se/libcurl/c/CURLOPT_TIMEOUT.html
 * CURLOPT_TIMEOUT_MS                   must be Int          The maximum number of milliseconds to allow cURL functions to execute. If libcurl is built to use 
 *                                                          the standard system name resolver, that portion of the connect will still use full-second resolution 
 *                                                          for timeouts with a minimum timeout allowed of one second. All protocols. See 
 *                                                          https://curl.se/libcurl/c/CURLOPT_TIMEOUT_MS.html
 * CURLOPT_LOW_SPEED_LIMIT              must be Int         The transfer speed, in bytes per second, that the transfer should be below during the count of 
 *                                                          CURLOPT_LOW_SPEED_TIME seconds before PHP considers the transfer too slow and aborts. Defaults to 0
 *                                                          (disabled). All protocols. See https://curl.se/libcurl/c/CURLOPT_LOW_SPEED_LIMIT.html
 * CURLOPT_LOW_SPEED_TIME               must be Int         The number of seconds the transfer speed should be below CURLOPT_LOW_SPEED_LIMIT before PHP 
 *                                                          considers the transfer too slow and aborts. Defaults to 0 (disabled). All protocols. See 
 *                                                          https://curl.se/libcurl/c/CURLOPT_LOW_SPEED_TIME.html 
 * CURLOPT_MAX_SEND_SPEED_LARGE         Int/cURL 7.15.5     If an upload exceeds this speed (counted in bytes per second) on cumulative average during the 
 *                                                          transfer, the transfer will pause to keep the average rate less than or equal to the parameter 
 *                                                          value. Defaults to unlimited speed. All protocols except FILE. See 
 *                                                          https://curl.se/libcurl/c/CURLOPT_MAX_SEND_SPEED_LARGE.html
 * CURLOPT_MAX_RECV_SPEED_LARGE         Int/cURL 7.15.5     If a download exceeds this speed (counted in bytes per second) on cumulative average during the 
 *                                                          transfer, the transfer will pause to keep the average rate less than or equal to the parameter 
 *                                                          value. Defaults to unlimited speed. All protocols except FILE. See 
 *                                                          https://curl.se/libcurl/c/CURLOPT_MAX_RECV_SPEED_LARGE.html
 * CURLOPT_MAXCONNECTS                  must be Int         The maximum amount of persistent connections that are allowed. When the limit is reached, 
 *                                                          CURLOPT_CLOSEPOLICY is used to determine which connection to close. Most protocols. See 
 *                                                          https://curl.se/libcurl/c/CURLOPT_MAXCONNECTS.html
 * CURLOPT_FRESH_CONNECT                must be Boolean     TRUE to force the use of a new connection instead of a cached one. Most protocols. See 
 *                                                          https://curl.se/libcurl/c/CURLOPT_FRESH_CONNECT.html
 * CURLOPT_FORBID_REUSE                 must be Boolean     TRUE to force the connection to explicitly close when it has finished processing, and not be pooled 
 *                                                          for reuse. Most protocols. See https://curl.se/libcurl/c/CURLOPT_FORBID_REUSE.html
 * CURLOPT_MAXAGE_CONN                  Int/cURL 7.65.0     **UNDOCUMENTED** Maximum time in seconds that you allow an existing connection to have been idle to 
 *                                                          be considered for reuse for this request. All protocols. See 
 *                                                          https://curl.se/libcurl/c/CURLOPT_MAXAGE_CONN.html
 * CURLOPT_MAXLIFETIME_CONN             Int/cURL 7.80.0     **UNDOCUMENTED** Maximum time in seconds, since the creation of the connection, that you allow an 
 *                                                          existing connection to have to be considered for reuse for this request. All protocols. See 
 *                                                          https://curl.se/libcurl/c/CURLOPT_MAXLIFETIME_CONN.html
 * CURLOPT_CONNECTTIMEOUT               must be Int         The number of seconds to wait while trying to connect. Use 0 to wait indefinitely. cURL says 0 means 
 *                                                          default (= 300 seconds). All protocols. See https://curl.se/libcurl/c/CURLOPT_CONNECTTIMEOUT.html
 * CURLOPT_CONNECTTIMEOUT_MS            must be Int         The number of milliseconds to wait while trying to connect. Use 0 to wait indefinitely. If libcurl 
 *                                                          is built to use the standard system name resolver, that portion of the connect will still use 
 *                                                          full-second resolution for timeouts with a minimum timeout allowed of one second. cURL says 0 means 
 *                                                          default (= 300 seconds). All protocols. See https://curl.se/libcurl/c/CURLOPT_CONNECTTIMEOUT_MS.html
 * CURLOPT_IPRESOLVE                    Int/cURL 7.10.8     Allows an application to select what kind of IP addresses to use when resolving host names. This is 
 *                                                          only interesting when using host names that resolve addresses using more than one version of IP, 
 *                                                          possible values are CURL_IPRESOLVE_WHATEVER, CURL_IPRESOLVE_V4, CURL_IPRESOLVE_V6, by default 
 *                                                          CURL_IPRESOLVE_WHATEVER. All protocols. See https://curl.se/libcurl/c/CURLOPT_IPRESOLVE.html
 * CURLOPT_CONNECT_ONLY                 Boolean/cURL 7.15.2 TRUE tells the library to perform all the required proxy authentication and connection setup, but no 
 *                                                          data transfer. HTTP, SMTP, POP3 and IMAP protocols. See 
 *                                                          https://curl.se/libcurl/c/CURLOPT_CONNECT_ONLY.html
 * CURLOPT_USE_SSL                      Int/cURL 7.11.0     **UNDOCUMENTED** Bitmask to make libcurl use your desired level of SSL for the transfer. These are 
 *                                                          all protocols that start out plain text and get "upgraded" to SSL using the STARTTLS command. Values 
 *                                                          are CURLUSESSL_NONE, CURLUSESSL_TRY, CURLUSESSL_CONTROL, CURLUSESSL_ALL. Added in 7.11.0. This option 
 *                                                          was known as CURLOPT_FTP_SSL up to 7.16.4, and the constants were known as CURLFTPSSL_* Handled by 
 *                                                          LDAP since 7.81.0. Fully supported by the openldap backend only. FTP, SMTP, POP3, IMAP and LDAP 
 *                                                          protocols. See https://curl.se/libcurl/c/CURLOPT_USE_SSL.html
 * CURLOPT_RESOLVE                      Array/cURL 7.21.3   Provide a custom address for a specific host and port pair. An array of hostname, port, and IP 
 *                                                          address strings, each element separated by a colon. In the format: array("example.com:80:127.0.0.1").
 *                                                          HOST:PORT:ADDRESS[,ADDRESS]. All protocols. See https://curl.se/libcurl/c/CURLOPT_RESOLVE.html
 * CURLOPT_DNS_INTERFACE                Str/c7.33.0/P7.0.7  Set the name of the network interface that the DNS resolver should bind to. This must be an interface 
 *                                                          name (not an address). All protocols except FILE - protocols that resolve host names. See 
 *                                                          https://curl.se/libcurl/c/CURLOPT_DNS_INTERFACE.html
 * CURLOPT_DNS_LOCAL_IP4                Str/c7.33.0/P7.0.7  Set the local IPv4 address that the resolver should bind to. The argument should contain a single 
 *                                                          numerical IPv4 address as a string. All protocols. See 
 *                                                          https://curl.se/libcurl/c/CURLOPT_DNS_LOCAL_IP4.html
 * CURLOPT_DNS_LOCAL_IP6                Str/c7.33.0/P7.0.7  Set the local IPv6 address that the resolver should bind to. The argument should contain a single 
 *                                                          numerical IPv6 address as a string. All protocols. See 
 *                                                          https://curl.se/libcurl/c/CURLOPT_DNS_LOCAL_IP6.html
 * CURLOPT_DNS_SERVERS                  Str/cURL 7.24.0     **UNDOCUMENTED** String is the list of DNS servers to be used instead of the system default. The 
 *                                                          format of the dns servers option is: host[:port][,host[:port]]... e.g. 192.168.1.100,192.168.1.101,
 *                                                          3.4.5.6. All protocols. See https://curl.se/libcurl/c/CURLOPT_DNS_SERVERS.html
 * CURLOPT_DNS_SHUFFLE_ADDRESSES        Bool/c7.60.0/P7.3.0 TRUE to shuffle the order of all returned addresses so that they will be used in a random order, 
 *                                                          when a name is resolved and more than one IP address is returned. This may cause IPv4 to be used 
 *                                                          before IPv6 or vice versa. All protocols. See 
 *                                                          https://curl.se/libcurl/c/CURLOPT_DNS_SHUFFLE_ADDRESSES.html
 * CURLOPT_ACCEPTTIMEOUT_MS             Int/cURL 7.24.0     **UNDOCUMENTED** Maximum number of milliseconds to wait for a server to connect back to libcurl when 
 *                                                          an active FTP connection is used. FTP protocol. See 
 *                                                          https://curl.se/libcurl/c/CURLOPT_ACCEPTTIMEOUT_MS.html
 * CURLOPT_HAPPY_EYEBALLS_TIMEOUT_MS    Int/c7.59.0/P7.3.0  Head start for ipv6 for the happy eyeballs algorithm. Happy eyeballs attempts to connect to both 
 *                                                          IPv4 and IPv6 addresses for dual-stack hosts, preferring IPv6 first for timeout milliseconds. 
 *                                                          Defaults to CURL_HET_DEFAULT, which is currently 200 milliseconds. All protocols except FILE. See 
 *                                                          https://curl.se/libcurl/c/CURLOPT_HAPPY_EYEBALLS_TIMEOUT_MS.html
 * CURLOPT_UPKEEP_INTERVAL_MS           Int/cURL 7.62.0     **UNDOCUMENTED** Some protocols have "connection upkeep" mechanisms. These mechanisms usually send 
 *                                                          some traffic on existing connections in order to keep them alive; this can prevent connections from 
 *                                                          being closed due to overzealous firewalls, for example. Currently the only protocol with a connection 
 *                                                          upkeep mechanism is HTTP/2: when the connection upkeep interval is exceeded and curl_easy_upkeep is 
 *                                                          called, an HTTP/2 PING frame is sent on the connection. All protocols. See 
 *                                                          https://curl.se/libcurl/c/CURLOPT_UPKEEP_INTERVAL_MS.html
 * 
 * SSL and Security Options
 * Option                               Validation          Explanation
 * CURLOPT_SSLCERT                      must be String      The name of a file containing a PEM formatted certificate. All TLS based protocols. 
 *                                                          See https://curl.se/libcurl/c/CURLOPT_SSLCERT.html
 * CURLOPT_SSLCERT_BLOB                 Array/cURL 7.71.0   **UNDOCUMENTED** A curl_blob structure, which contains (pointer and size) a client certificate. The 
 *                                                          format must be "P12" on Secure Transport or Schannel. The format must be "P12" or "PEM" on OpenSSL. 
 *                                                          The format must be "DER" or "PEM" on mbedTLS. The format must be specified with CURLOPT_SSLCERTTYPE. 
 *                                                          All TLS based protocols. See https://curl.se/libcurl/c/CURLOPT_SSLCERT_BLOB.html
 * CURLOPT_PROXY_SSLCERT                Str/c7.52.0/P7.3.0  The file name of your client certificate used to connect to the HTTPS proxy. The default format is 
 *                                                          "P12" on Secure Transport and "PEM" on other engines, and can be changed with 
 *                                                          CURLOPT_PROXY_SSLCERTTYPE. With NSS or Secure Transport, this can also be the nickname of the 
 *                                                          certificate you wish to authenticate with as it is named in the security database. If you want to 
 *                                                          use a file from the current directory, please precede it with "./" prefix, in order to avoid 
 *                                                          confusion with a nickname. HTTPS proxy. See https://curl.se/libcurl/c/CURLOPT_PROXY_SSLCERT.html
 * CURLOPT_PROXY_SSLCERT_BLOB           Array/cURL 7.71.0   **UNDOCUMENTED** A curl_blob structure, which contains information (pointer and size) about a memory 
 *                                                          block with binary data of the certificate used to connect to the HTTPS proxy. The format must be "P12" 
 *                                                          on Secure Transport or Schannel. The format must be "P12" or "PEM" on OpenSSL. The string "P12" or 
 *                                                          "PEM" must be specified with CURLOPT_PROXY_SSLCERTTYPE. HTTPS proxy. See 
 *                                                          https://curl.se/libcurl/c/CURLOPT_PROXY_SSLCERT_BLOB.html
 * CURLOPT_SSLCERTTYPE                  String/cURL 7.9.3   The format of the certificate. Supported formats are "PEM" (default), "DER", and "ENG". As of 
 *                                                          OpenSSL 0.9.3, "P12" (for PKCS#12-encoded files) is also supported. All TLS based protocols. See 
 *                                                          https://curl.se/libcurl/c/CURLOPT_SSLCERTTYPE.html
 * CURLOPT_PROXY_SSLCERTTYPE            Str/c7.52.0/P7.3.0  The format of your client certificate used when connecting to an HTTPS proxy. Supported formats are 
 *                                                          "PEM" and "DER", except with Secure Transport. OpenSSL (versions 0.9.3 and later) and Secure 
 *                                                          Transport (on iOS 5 or later, or OS X 10.7 or later) also support "P12" for PKCS#12-encoded files. 
 *                                                          Defaults to "PEM". All protocols. See https://curl.se/libcurl/c/CURLOPT_PROXY_SSLCERTTYPE.html
 * CURLOPT_SSLKEY                       must be String      The name of a file containing a private SSL key. All TLS based protocols. See 
 *                                                          https://curl.se/libcurl/c/CURLOPT_SSLKEY.html
 * CURLOPT_SSLKEY_BLOB                  Array/cURL 7.71.0   **UNDOCUMENTED** A curl_blob structure, which contains information (pointer and size) for a private 
 *                                                          key. Compatible with OpenSSL. The format (like "PEM") must be specified with CURLOPT_SSLKEYTYPE. All 
 *                                                          TLS based protocols. https://curl.se/libcurl/c/CURLOPT_SSLKEY_BLOB.html
 * CURLOPT_PROXY_SSLKEY                 Str/c7.52.0/P7.3.0  The file name of your private key used for connecting to the HTTPS proxy. The default format is "PEM" 
 *                                                          and can be changed with CURLOPT_PROXY_SSLKEYTYPE. (iOS and Mac OS X only) This option is ignored if 
 *                                                          curl was built against Secure Transport. All protocols. See 
 *                                                          https://curl.se/libcurl/c/CURLOPT_PROXY_SSLKEY.html
 * CURLOPT_PROXY_SSLKEY_BLOB            Array/cURL 7.71.0   **UNDOCUMENTED** A pointer to a curl_blob structure that contains information (pointer and size) about 
 *                                                          the private key for connecting to the HTTPS proxy. Compatible with OpenSSL. The format (like "PEM") 
 *                                                          must be specified with CURLOPT_PROXY_SSLKEYTYPE. All TLS based protocols. See 
 *                                                          https://curl.se/libcurl/c/CURLOPT_PROXY_SSLKEY_BLOB.html
 * CURLOPT_SSLKEYTYPE                   must be String      The key type of the private SSL key specified in CURLOPT_SSLKEY. Supported key types are "PEM" 
 *                                                          (default), "DER", and "ENG". All TLS based protocols. See 
 *                                                          https://curl.se/libcurl/c/CURLOPT_SSLKEYTYPE.html
 * CURLOPT_PROXY_SSLKEYTYPE             Str/c7.52.0/P7.3.0  The format of your private key. Supported formats are "PEM", "DER" and "ENG". Used with HTTPS proxy. 
 *                                                          See https://curl.se/libcurl/c/CURLOPT_PROXY_SSLKEYTYPE.html
 * CURLOPT_KEYPASSWD                    String/cURL 7.16.4  The password required to use the CURLOPT_SSLKEY or CURLOPT_SSH_PRIVATE_KEYFILE private key. All TLS 
 *                                                          based protocols. See https://curl.se/libcurl/c/CURLOPT_KEYPASSWD.html
 * CURLOPT_PROXY_KEYPASSWD              Str/c7.52.0/P7.3.0  Set the string be used as the password required to use the CURLOPT_PROXY_SSLKEY private key. You 
 *                                                          never needed a passphrase to load a certificate but you need one to load your private key. This 
 *                                                          option is for connecting to an HTTPS proxy, not an HTTPS server. Used with HTTPS proxy. See 
 *                                                          https://curl.se/libcurl/c/CURLOPT_PROXY_KEYPASSWD.html
 * CURLOPT_SSL_EC_CURVES                Str/cURL 7.73.0     **UNDOCUMENTED** String with a colon delimited list of key exchange curves (EC) algorithms. This 
 *                                                          option defines the client's key exchange algorithms in the SSL handshake (if the SSL backend libcurl 
 *                                                          is built to use supports it). HTTP protocol. See https://curl.se/libcurl/c/CURLOPT_SSL_EC_CURVES.html
 * CURLOPT_SSL_ENABLE_ALPN              Bool/c7.36.0/P7.0.7 FALSE to disable ALPN in the SSL handshake (if the SSL backend libcurl is built to use supports it), 
 *                                                          which can be used to negotiate http2. HTTP protocol. See 
 *                                                          https://curl.se/libcurl/c/CURLOPT_SSL_ENABLE_ALPN.html
 * CURLOPT_SSL_ENABLE_NPN               Bool/c7.36.0/P7.0.7 **DEPRECATED** FALSE to disable NPN in the SSL handshake (if the SSL backend libcurl is built to use 
 *                                                          supports it), which can be used to negotiate http2. Deprecated in 7.86.0. Setting this option has no 
 *                                                          function. HTTP protocol. See https://curl.se/libcurl/c/CURLOPT_SSL_ENABLE_NPN.html
 * CURLOPT_SSLENGINE                    must be String      The identifier for the crypto engine of the private SSL key specified in CURLOPT_SSLKEY. All TLS 
 *                                                          based protocols. See https://curl.se/libcurl/c/CURLOPT_SSLENGINE.html
 * CURLOPT_SSLENGINE_DEFAULT            must be String      The identifier for the crypto engine used for asymmetric crypto operations. All TLS based protocols. 
 *                                                          See https://curl.se/libcurl/c/CURLOPT_SSLENGINE_DEFAULT.html
 * CURLOPT_SSL_FALSESTART               Bool/c7.42.0/P7.0.7 TRUE to enable TLS false start. False start is a mode where a TLS client will start sending 
 *                                                          application data before verifying the server's Finished message, thus saving a round trip when 
 *                                                          performing a full handshake. All TLS based protocols. See 
 *                                                          https://curl.se/libcurl/c/CURLOPT_SSL_FALSESTART.html
 * CURLOPT_SSLVERSION                   must be Int         One of CURL_SSLVERSION_DEFAULT (0), CURL_SSLVERSION_TLSv1 (1), CURL_SSLVERSION_SSLv2 (2), 
 *                                                          CURL_SSLVERSION_SSLv3 (3), CURL_SSLVERSION_TLSv1_0 (4), CURL_SSLVERSION_TLSv1_1 (5) or 
 *                                                          CURL_SSLVERSION_TLSv1_2 (6). The maximum TLS version can be set by using one of the 
 *                                                          CURL_SSLVERSION_MAX_* constants. It is also possible to OR one of the CURL_SSLVERSION_* constants 
 *                                                          with one of the CURL_SSLVERSION_MAX_* constants. CURL_SSLVERSION_MAX_DEFAULT (the maximum version 
 *                                                          supported by the library), CURL_SSLVERSION_MAX_TLSv1_0, CURL_SSLVERSION_MAX_TLSv1_1, 
 *                                                          CURL_SSLVERSION_MAX_TLSv1_2, or CURL_SSLVERSION_MAX_TLSv1_3. All TLS based protocols. See 
 *                                                          https://curl.se/libcurl/c/CURLOPT_SSLVERSION.html
 * CURLOPT_PROXY_SSLVERSION             Int/c7.52.0/P7.3.0  One of CURL_SSLVERSION_DEFAULT, CURL_SSLVERSION_TLSv1, CURL_SSLVERSION_TLSv1_0, 
 *                                                          CURL_SSLVERSION_TLSv1_1, CURL_SSLVERSION_TLSv1_2, CURL_SSLVERSION_TLSv1_3, 
 *                                                          CURL_SSLVERSION_MAX_DEFAULT, CURL_SSLVERSION_MAX_TLSv1_0, CURL_SSLVERSION_MAX_TLSv1_1, 
 *                                                          CURL_SSLVERSION_MAX_TLSv1_2, CURL_SSLVERSION_MAX_TLSv1_3 or CURL_SSLVERSION_SSLv3. All protocols. See
 *                                                          https://curl.se/libcurl/c/CURLOPT_PROXY_SSLVERSION.html
 * CURLOPT_SSL_VERIFYHOST               must be Int[2,0]    2 to verify that a Common Name field or a Subject Alternate Name field in the SSL peer certificate 
 *                                                          matches the provided hostname. 0 to not check the names. 1 should not be used. In production 
 *                                                          environments the value of this option should be kept at 2 (default value). All TLS based protocols. 
 *                                                          See https://curl.se/libcurl/c/CURLOPT_SSL_VERIFYHOST.html
 * CURLOPT_DOH_SSL_VERIFYHOST           Int/cURL 7.76.0     **UNDOCUMENTED** 2 means asking curl to verify the DoH (DNS-over-HTTPS) server's certificate name 
 *                                                          fields against the host name. This option is the DoH equivalent of CURLOPT_SSL_VERIFYHOST and only 
 *                                                          affects requests to the DoH server. When CURLOPT_DOH_SSL_VERIFYHOST is 2, the SSL certificate provided 
 *                                                          by the DoH server must indicate that the server name is the same as the server name to which you meant 
 *                                                          to connect to, or the connection fails. Curl considers the DoH server the intended one when the Common 
 *                                                          Name field or a Subject Alternate Name field in the certificate matches the host name in the DoH URL 
 *                                                          to which you told Curl to connect. When the verify value is set to 1 it is treated the same as 2. 
 *                                                          However for consistency with the other VERIFYHOST options we suggest use 2 and not 1. When the verify 
 *                                                          value is set to 0, the connection succeeds regardless of the names used in the certificate. Use that 
 *                                                          ability with caution! DoH protocol. See https://curl.se/libcurl/c/CURLOPT_DOH_SSL_VERIFYHOST.html
 * CURLOPT_PROXY_SSL_VERIFYHOST         Int/c7.52.0/P7.3.0  Set to 2 to verify in the HTTPS proxy's certificate name fields against the proxy name. When set to 
 *                                                          0 the connection succeeds regardless of the names used in the certificate. Use that ability with 
 *                                                          caution! 1 treated as a debug option in curl 7.28.0 and earlier. From curl 7.28.1 to 7.65.3 
 *                                                          CURLE_BAD_FUNCTION_ARGUMENT is returned. From curl 7.66.0 onwards 1 and 2 is treated as the same 
 *                                                          value. In production environments the value of this option should be kept at 2 (default value). All 
 *                                                          protocols when used over an HTTPS proxy. See https://curl.se/libcurl/c/CURLOPT_PROXY_SSL_VERIFYHOST.html
 * CURLOPT_SSL_VERIFYPEER               must be Boolean     FALSE to stop cURL from verifying the peer's certificate. Alternate certificates to verify against 
 *                                                          can be specified with the CURLOPT_CAINFO option or a certificate directory can be specified with the 
 *                                                          CURLOPT_CAPATH option. TRUE by default as of cURL 7.10. All TLS based protocols. See 
 *                                                          https://curl.se/libcurl/c/CURLOPT_SSL_VERIFYPEER.html
 * CURLOPT_DOH_SSL_VERIFYPEER           Bool/cURL 7.76.0    **UNDOCUMENTED** This option tells curl to verify the authenticity of the DoH (DNS-over-HTTPS) 
 *                                                          server's certificate. A value of 1 means curl verifies; 0 (zero) means it does not. DoH protocol. See
 *                                                          https://curl.se/libcurl/c/CURLOPT_DOH_SSL_VERIFYPEER.html
 * CURLOPT_PROXY_SSL_VERIFYPEER         Bool/c7.52.0/P7.3.0 FALSE to stop cURL from verifying the peer's certificate. Alternate certificates to verify against 
 *                                                          can be specified with the CURLOPT_CAINFO option or a certificate directory can be specified with the 
 *                                                          CURLOPT_CAPATH option. When set to false, the peer certificate verification succeeds regardless. All 
 *                                                          protocols. See https://curl.se/libcurl/c/CURLOPT_PROXY_SSL_VERIFYPEER.html
 * CURLOPT_SSL_VERIFYSTATUS             Bool/c7.41.0/P7.0.7 TRUE to verify the certificate's status. This option determines whether libcurl verifies the status 
 *                                                          of the server cert using the "Certificate Status Request" TLS extension (aka. OCSP stapling). All TLS 
 *                                                          based protocols. See https://curl.se/libcurl/c/CURLOPT_SSL_VERIFYSTATUS.html
 * CURLOPT_DOH_SSL_VERIFYSTATUS         Bool/cURL 7.76.0    **UNDOCUMENTED** This option determines whether libcurl verifies the status of the DoH (DNS-over-
 *                                                          HTTPS) server cert using the "Certificate Status Request" TLS extension (aka. OCSP stapling). 1 to 
 *                                                          enable or 0 to disable. DoH protocol. See https://curl.se/libcurl/c/CURLOPT_DOH_SSL_VERIFYSTATUS.html
 * CURLOPT_CAINFO                       must be String      The name of a file holding one or more certificates to verify the peer with. This only makes sense 
 *                                                          when used in combination with CURLOPT_SSL_VERIFYPEER. Might require an absolute path. All TLS based 
 *                                                          protocols. See https://curl.se/libcurl/c/CURLOPT_CAINFO.html
 * CURLOPT_CAINFO_BLOB                  Array/cURL 7.77.0   **UNDOCUMENTED** A curl_blob structure, which contains information (pointer and size) about a memory 
 *                                                          block with binary data of PEM encoded content holding one or more certificates to verify the HTTPS 
 *                                                          server with. All TLS based protocols. See https://curl.se/libcurl/c/CURLOPT_CAINFO_BLOB.html
 * CURLOPT_PROXY_CAINFO                 Str/c7.52.0/P7.3.0  The path to proxy Certificate Authority (CA) bundle. Set the path as a string naming a file holding 
 *                                                          one or more certificates to verify the HTTPS proxy with. This option is for connecting to an HTTPS 
 *                                                          proxy, not an HTTPS server. Defaults set to the system path where libcurl's cacert bundle is assumed 
 *                                                          to be stored. Used with HTTPS proxy. See https://curl.se/libcurl/c/CURLOPT_PROXY_CAINFO.html
 * CURLOPT_PROXY_CAINFO_BLOB            Array/cURL 7.77.0   **UNDOCUMENTED** A curl_blob structure, which contains information (pointer and size) about a memory 
 *                                                          block with binary data of PEM encoded content holding one or more certificates to verify the HTTPS 
 *                                                          proxy with. Used with HTTPS proxy. See https://curl.se/libcurl/c/CURLOPT_PROXY_CAINFO_BLOB.html
 * CURLOPT_ISSUERCERT                   must be String      **UNDOCUMENTED** String naming a file holding a CA certificate in PEM format. If the option is set, 
 *                                                          an additional check against the peer certificate is performed to verify the issuer is indeed the one 
 *                                                          associated with the certificate provided by the option. This additional check is useful in multi-level 
 *                                                          PKI where one needs to enforce that the peer certificate is from a specific branch of the tree. All 
 *                                                          TLS-based protocols. See https://curl.se/libcurl/c/CURLOPT_ISSUERCERT.html
 * CURLOPT_ISSUERCERT_BLOB              Array/cURL 7.71.0   **UNDOCUMENTED** A curl_blob structure, which contains information (pointer and size) about a memory 
 *                                                          block with binary data of a CA certificate in PEM format. If the option is set, an additional check 
 *                                                          against the peer certificate is performed to verify the issuer is indeed the one associated with the 
 *                                                          certificate provided by the option. This additional check is useful in multi-level PKI where one needs 
 *                                                          to enforce that the peer certificate is from a specific branch of the tree. All TLS-based protocols. 
 *                                                          See https://curl.se/libcurl/c/CURLOPT_ISSUERCERT_BLOB.html
 * CURLOPT_PROXY_ISSUERCERT             Str/cURL 7.71.0     **UNDOCUMENTED** String naming a file holding a CA certificate in PEM format. If the option is set, 
 *                                                          an additional check against the peer certificate is performed to verify the issuer of the the HTTPS 
 *                                                          proxy is indeed the one associated with the certificate provided by the option. This additional check 
 *                                                          is useful in multi-level PKI where one needs to enforce that the peer certificate is from a specific 
 *                                                          branch of the tree. All TLS-based protocols. See 
 *                                                          https://curl.se/libcurl/c/CURLOPT_PROXY_ISSUERCERT.html
 * CURLOPT_PROXY_ISSUERCERT_BLOB        Array/cURL 7.71.0   **UNDOCUMENTED** A curl_blob struct, which contains information (pointer and size) about a memory 
 *                                                          block with binary data of a CA certificate in PEM format. If the option is set, an additional check 
 *                                                          against the peer certificate is performed to verify the issuer of the the HTTPS proxy is indeed the 
 *                                                          one associated with the certificate provided by the option. This additional check is useful in multi-
 *                                                          level PKI where one needs to enforce that the peer certificate is from a specific branch of the tree.
 *                                                          All TLS-based protocols. See https://curl.se/libcurl/c/CURLOPT_PROXY_ISSUERCERT_BLOB.html
 * CURLOPT_CAPATH                       must be String      A directory that holds multiple CA certificates. Use this option alongside CURLOPT_SSL_VERIFYPEER. All 
 *                                                          TLS based protocols. See https://curl.se/libcurl/c/CURLOPT_CAPATH.html
 * CURLOPT_PROXY_CAPATH                 Str/c7.52.0/P7.3.0  The directory holding multiple CA certificates to verify the HTTPS proxy with. Everything used over 
 *                                                          an HTTPS proxy. See https://curl.se/libcurl/c/CURLOPT_PROXY_CAPATH.html
 * CURLOPT_CRLFILE                      Str/cURL 7.19.0     **UNDOCUMENTED** String naming a file with the concatenation of CRL (in PEM format) to use in the 
 *                                                          certificate validation that occurs during the SSL exchange. All TLS-based protocols. See 
 *                                                          https://curl.se/libcurl/c/CURLOPT_CRLFILE.html
 * CURLOPT_PROXY_CRLFILE                Str/c7.52.0/P7.3.0  Set the file name with the concatenation of CRL (Certificate Revocation List) in PEM format to use 
 *                                                          in the certificate validation that occurs during the SSL exchange. Used with HTTPS proxy. See 
 *                                                          https://curl.se/libcurl/c/CURLOPT_PROXY_CRLFILE.html
 * CURLOPT_CA_CACHE_TIMEOUT             Integer/cURL 7.87.0 **UNDOCUMENTED** Maximum time, in seconds, any cached certificate store it has in memory may be kept 
 *                                                          and reused for new connections. See https://curl.se/libcurl/c/CURLOPT_CA_CACHE_TIMEOUT.html
 * CURLOPT_CERTINFO                     Boolean/cURL 7.19.1 TRUE to output SSL certification information to STDERR on secure transfers. Requires setting 
 *                                                          CURLOPT_VERBOSE to TRUE. TLS-based protocols. See https://curl.se/libcurl/c/CURLOPT_CERTINFO.html
 * CURLOPT_PINNEDPUBLICKEY              Str/c7.39.0/P7.0.7  Set the pinned public key. The string can be the file name of your pinned public key. The file 
 *                                                          format expected is "PEM" or "DER". The string can also be any number of base64 encoded sha256 hashes 
 *                                                          preceded by "sha256//" and separated by ";". All TLS based protocols. See 
 *                                                          https://curl.se/libcurl/c/CURLOPT_PINNEDPUBLICKEY.html
 * CURLOPT_PROXY_PINNEDPUBLICKEY        Str/c7.52.0/P7.3.0  Set the pinned public key for HTTPS proxy. The string can be the file name of your pinned public key. 
 *                                                          The file format expected is "PEM" or "DER". The string can also be any number of base64 encoded 
 *                                                          sha256 hashes preceded by "sha256//" and separated by ";". All TLS based protocols. See 
 *                                                          https://curl.se/libcurl/c/CURLOPT_PROXY_PINNEDPUBLICKEY.html
 * CURLOPT_RANDOM_FILE                  must be String      **DEPRECATED** Deprecated option. It serves no purpose anymore. A filename to be used to seed the 
 *                                                          random number generator for SSL. This option was deprecated in 7.84.0. All protocols. See 
 *                                                          https://curl.se/libcurl/c/CURLOPT_RANDOM_FILE.html
 * CURLOPT_EGDSOCKET                    must be String      **DEPRECATED** Like CURLOPT_RANDOM_FILE, except a filename to an Entropy Gathering Daemon socket. 
 *                                                          Deprecated option. It serves no purpose anymore. This option was deprecated in 7.84.0. All TLS based 
 *                                                          protocols. See https://curl.se/libcurl/c/CURLOPT_EGDSOCKET.html
 * CURLOPT_SSL_CIPHER_LIST              must be String      A list of ciphers to use for SSL. For example, RC4-SHA and TLSv1 are valid cipher lists. All TLS based 
 *                                                          protocols. See https://curl.se/libcurl/c/CURLOPT_SSL_CIPHER_LIST.html
 * CURLOPT_PROXY_SSL_CIPHER_LIST        Str/c7.52.0/P7.3.0  The list of ciphers to use for the connection to the HTTPS proxy. The list must be syntactically 
 *                                                          correct, it consists of one or more cipher strings separated by colons. Commas or spaces are also 
 *                                                          acceptable separators but colons are normally used, !, - and + can be used as operators. All 
 *                                                          protocols. See https://curl.se/libcurl/c/CURLOPT_PROXY_SSL_CIPHER_LIST.html
 * CURLOPT_TLS13_CIPHERS                Str/c7.61.0/P7.3.0  The list of cipher suites to use for the TLS 1.3 connection. The list must be syntactically correct, 
 *                                      OpenSSL1.1.1        it consists of one or more cipher suite strings separated by colons. This option is currently used 
 *                                                          only when curl is built to use OpenSSL 1.1.1 or later. If you are using a different SSL backend you 
 *                                                          can try setting TLS 1.3 cipher suites by using the CURLOPT_SSL_CIPHER_LIST option. All TLS based 
 *                                                          protocols. See https://curl.se/libcurl/c/CURLOPT_TLS13_CIPHERS.html
 * CURLOPT_PROXY_TLS13_CIPHERS          Str/c7.61.0/P7.3.0  The list of cipher suites to use for the TLS 1.3 connection to a proxy. The list must be 
 *                                      OpenSSL1.1.1        syntactically correct, it consists of one or more cipher suite strings separated by colons. This 
 *                                                          option is currently used only when curl is built to use OpenSSL 1.1.1 or later. If you are using a 
 *                                                          different SSL backend you can try setting TLS 1.3 cipher suites by using the 
 *                                                          CURLOPT_PROXY_SSL_CIPHER_LIST option. All TLS based protocols. See 
 *                                                          https://curl.se/libcurl/c/CURLOPT_PROXY_TLS13_CIPHERS.html
 * CURLOPT_SSL_SESSIONID_CACHE          Bool/cURL 7.16.0    **UNDOCUMENTED** 0 to disable libcurl's use of SSL session-ID caching. Set this to 1 to enable it. By 
 *                                                          default all transfers are done using the cache enabled. While nothing ever should get hurt by 
 *                                                          attempting to reuse SSL session-IDs, there seem to be or have been broken SSL implementations in the 
 *                                                          wild that may require you to disable this in order for you to succeed. All TLS-based protocols. See 
 *                                                          https://curl.se/libcurl/c/CURLOPT_SSL_SESSIONID_CACHE.html
 * CURLOPT_SSL_OPTIONS                  Int/c7.25.0/P7.0.7  Set SSL behavior options, which is a bitmask of any of the following constants: 
 *                                                          CURLSSLOPT_ALLOW_BEAST: do not attempt to use any workarounds for a security flaw in the SSL3 and 
 *                                                          TLS1.0 protocols. CURLSSLOPT_NO_REVOKE: disable certificate revocation checks for those SSL backends 
 *                                                          where such behavior is present. All TLS-based protocols. See 
 *                                                          https://curl.se/libcurl/c/CURLOPT_SSL_OPTIONS.html
 * CURLOPT_PROXY_SSL_OPTIONS            Int/c7.52.0/P7.3.0  Set proxy SSL behavior options, which is a bitmask of any of the following constants: 
 *                                                          CURLSSLOPT_ALLOW_BEAST: do not attempt to use any workarounds for a security flaw in the SSL3 and 
 *                                                          TLS1.0 protocols. CURLSSLOPT_NO_REVOKE: disable certificate revocation checks for those SSL backends 
 *                                                          where such behavior is present. (curl >= 7.44.0) CURLSSLOPT_NO_PARTIALCHAIN: do not accept "partial" 
 *                                                          certificate chains, which it otherwise does by default. (curl >= 7.68.0). All TLS-based protocols. See
 *                                                          https://curl.se/libcurl/c/CURLOPT_PROXY_SSL_OPTIONS.html
 * CURLOPT_KRBLEVEL                     must be String      **UNDOCUMENTED** Set the kerberos security level for FTP; this also enables kerberos awareness. This 
 *                                                          is a string that should match one of the following: 'clear', 'safe', 'confidential' or 'private'. If 
 *                                                          the string is set but does not match one of these, 'private' will be used. Set the string to NULL to 
 *                                                          disable kerberos support for FTP. FTP protocol. See https://curl.se/libcurl/c/CURLOPT_KRBLEVEL.html
 * CURLOPT_GSSAPI_DELEGATION            Int/cURL 7.22.0     **UNDOCUMENTED** Use CURLGSSAPI_DELEGATION_FLAG to allow unconditional GSSAPI credential delegation. 
 *                                                          The delegation is disabled by default since 7.21.7. Set the parameter to 
 *                                                          CURLGSSAPI_DELEGATION_POLICY_FLAG to delegate only if the OK-AS-DELEGATE flag is set in the service 
 *                                                          ticket in case this feature is supported by the GSS-API implementation and the definition of 
 *                                                          GSS_C_DELEG_POLICY_FLAG was available at compile-time. HTTP Protocol. See 
 *                                                          https://curl.se/libcurl/c/CURLOPT_GSSAPI_DELEGATION.html
 * 
 * SSH
 * Option                               Validation          Explanation
 * CURLOPT_SSH_AUTH_TYPES               Int/cURL 7.16.1     A bitmask consisting of one or more of CURLSSH_AUTH_PUBLICKEY, CURLSSH_AUTH_PASSWORD, 
 *                                                          CURLSSH_AUTH_HOST, CURLSSH_AUTH_KEYBOARD. Set to CURLSSH_AUTH_ANY to let libcurl pick one. SFTP and 
 *                                                          SCP protocols. See https://curl.se/libcurl/c/CURLOPT_SSH_AUTH_TYPES.html
 * CURLOPT_SSH_COMPRESSION              Bool/c7.56.0/P7.3.0 TRUE to enable built-in SSH compression. This is a request; the server may or may not do it. All SSH 
 *                                                          based protocols: SCP, SFTP. See https://curl.se/libcurl/c/CURLOPT_SSH_COMPRESSION.html
 * CURLOPT_SSH_HOST_PUBLIC_KEY_MD5      String/cURL 7.17.1  A string containing 32 hexadecimal digits. The string should be the MD5 checksum of the remote host's 
 *                                                          public key, and libcurl will reject the connection to the host unless the md5sums match. This option 
 *                                                          is only for SCP and SFTP transfers. SCP and SFTP protocols. See 
 *                                                          https://curl.se/libcurl/c/CURLOPT_SSH_HOST_PUBLIC_KEY_MD5.html
 * CURLOPT_SSH_HOST_PUBLIC_KEY_SHA256   Str/cURL 7.80.0     **UNDOCUMENTED** String contains a Base64-encoded SHA256 hash of the remote host's public key. The 
 *                                                          transfer will fail if the given hash does not match the hash the remote host provides. SCP and SFTP 
 *                                                          protocols. See https://curl.se/libcurl/c/CURLOPT_SSH_HOST_PUBLIC_KEY_SHA256.html
 * CURLOPT_SSH_PUBLIC_KEYFILE           String/cURL 7.16.1  The file name for your public key. If not used, libcurl defaults to $HOME/.ssh/id_dsa.pub if the 
 *                                                          HOME environment variable is set, and just "id_dsa.pub" in the current directory if HOME is not set. 
 *                                                          SFTP and SCP protocols. See https://curl.se/libcurl/c/CURLOPT_SSH_PUBLIC_KEYFILE.html
 * CURLOPT_SSH_PRIVATE_KEYFILE          String/cURL 7.16.1  The file name for your private key. If not used, libcurl defaults to $HOME/.ssh/id_dsa if the HOME 
 *                                                          environment variable is set, and just "id_dsa" in the current directory if HOME is not set. If the 
 *                                                          file is password-protected, set the password with CURLOPT_KEYPASSWD. SFTP and SCP protocols. See 
 *                                                          https://curl.se/libcurl/c/CURLOPT_SSH_PRIVATE_KEYFILE.html
 * CURLOPT_SSH_KNOWNHOSTS               Str/cURL 7.19.6     **UNDOCUMENTED** String holding the file name of the known_host file to use. The known_hosts file 
 *                                                          should use the OpenSSH file format as supported by libssh2. If this file is specified, libcurl will 
 *                                                          only accept connections with hosts that are known and present in that file, with a matching public 
 *                                                          key. Use CURLOPT_SSH_KEYFUNCTION to alter the default behavior on host and key (mis)matching. SFTP 
 *                                                          and SCP protocols. See https://curl.se/libcurl/c/CURLOPT_SSH_KNOWNHOSTS.html
 * CURLOPT_SSH_KEYFUNCTION             Callback/cURL 7.19.6 **UNDOCUMENTED** Callback gets called when the known_host matching has been done, to allow the 
 *                                                          application to act and decide for libcurl how to proceed. The callback will only be called if 
 *                                                          CURLOPT_SSH_KNOWNHOSTS is also set. Callback MUST return one of a number of return codes to tell 
 *                                                          libcurl how to act regarding the host+key pair. SFTP and SCP protocols. See 
 *                                                          https://curl.se/libcurl/c/CURLOPT_SSH_KEYFUNCTION.html
 * CURLOPT_SSH_KEYDATA                  Str/cURL 7.19.6     **UNDOCUMENTED** This string will be passed along verbatim to the callback set with 
 *                                                          CURLOPT_SSH_KEYFUNCTION. SFTP and SCP protocols. See 
 *                                                          https://curl.se/libcurl/c/CURLOPT_SSH_KEYDATA.html
 * CURLOPT_SSH_HOSTKEYFUNCTION         Callback/cURL 7.84.0 **UNDOCUMENTED** This callback gets called when the verification of the SSH hostkey is needed. SCP 
 *                                                          and SFTP protocols. See https://curl.se/libcurl/c/CURLOPT_SSH_HOSTKEYFUNCTION.html
 * CURLOPT_SSH_HOSTKEYDATA              Str/cURL 7.84.0     **UNDOCUMENTED** This string will be passed along verbatim to the callback set with 
 *                                                          CURLOPT_SSH_HOSTKEYFUNCTION. SFTP and SCP protocols. See 
 *                                                          https://curl.se/libcurl/c/CURLOPT_SSH_HOSTKEYDATA.html
 * 
 * Websockets Options
 * Option                               Validation          Explanation
 * CURLOPT_WS_OPTIONS                   Int/cURL 7.85.0     **UNDOCUMENTED** WebSockets behavior options: CURLWS_RAW_MODE, CURLWS_COMPRESS_MODE, 
 *                                                          CURLWS_PINGOFF_MODE. WebSockets protocol. See https://curl.se/libcurl/c/CURLOPT_WS_OPTIONS.html
 * 
 * Other Options
 * Option                               Validation          Explanation
 * CURLOPT_PRIVATE                      String/cURL 7.10.3  Any data that should be associated with this cURL handle. This data can subsequently be retrieved 
 *                                                          with the CURLINFO_PRIVATE option of curl_getinfo(). cURL does nothing with this data. When using a 
 *                                                          cURL multi handle, this private data is typically a unique key to identify a standard cURL handle.
 *                                                          All protocols. See https://curl.se/libcurl/c/CURLOPT_PRIVATE.html
 * CURLOPT_SHARE                        must be cURL handle A result of curl_share_init(). Makes the cURL handle to use the data from the shared handle. All 
 *                                                          protocols. See https://curl.se/libcurl/c/CURLOPT_SHARE.html
 * CURLOPT_NEW_FILE_PERMS               Int/cURL 7.16.4     **UNDOCUMENTED** Value of the permissions that will be assigned to newly created files on the remote 
 *                                                          server. The default value is 0644, but any valid value can be used. SFTP, SCP and FILE protocols. See 
 *                                                          https://curl.se/libcurl/c/CURLOPT_NEW_FILE_PERMS.html
 * CURLOPT_NEW_DIRECTORY_PERMS          Int/cURL 7.16.4     **UNDOCUMENTED** Value of the permissions that will be assigned to newly created directories on the 
 *                                                          remote server. The default value is 0755, but any valid value can be used. SFTP, SCP and FILE 
 *                                                          protocols. See https://curl.se/libcurl/c/CURLOPT_NEW_DIRECTORY_PERMS.html
 * CURLOPT_QUICK_EXIT                   Bool/cURL 7.87.0    **UNDOCUMENTED** When recovering from a timeout, libcurl should skip lengthy cleanups that are 
 *                                                          intended to avoid all kinds of leaks (threads etc.), as the caller program is about to call exit() 
 *                                                          anyway. See https://curl.se/libcurl/c/CURLOPT_QUICK_EXIT.html
 * 
 * TELNET Options
 * Option                               Validation          Explanation
 * CURLOPT_TELNETOPTIONS                Array/cURL 7.16.4   **UNDOCUMENTED** Array with with variables to pass to the telnet negotiations. The variables should 
 *                                                          be in the format <option=value>. libcurl supports the options 'TTYPE', 'XDISPLOC' and 'NEW_ENV'. See 
 *                                                          the TELNET standard for details. TELNET protocol. See 
 *                                                          https://curl.se/libcurl/c/CURLOPT_TELNETOPTIONS.html
 * 
 * PHP integration exclusive Options
 * CURLINFO_HEADER_OUT                  must be Boolean     TRUE to track the handle's request string. By the name I infer it sets the CURLINFO_HEADER_OUT option 
 *                                                          of CURLOPT_DEBUGFUNCTION using an internal callback of the php libcurl extension. It then should 
 *                                                          affect all protocols. See https://curl.se/libcurl/c/CURLOPT_DEBUGFUNCTION.html Confirmed by 
 *                                                          https://bugs.php.net/bug.php?id=65348 Internally CURLINFO_HEADER_OUT uses CURLOPT_VERBOSE along with 
 *                                                          CURLOPT_DEBUGFUNCTION, so CURLOPT_VERBOSE and CURLOPT_DEBUGFUNCTION wont work if CURLINFO_HEADER_OUT
 *                                                          is set
 * CURLOPT_MUTE                         Bool/c < 7.15.5     **DEPRECATED** TRUE to be completely silent with regards to the cURL functions. Removed in cURL 7.15.5 
 *                                                          (You can use CURLOPT_RETURNTRANSFER instead) 
 * CURLOPT_PASSWDFUNCTION               must be Callback    A callback accepting three parameters. The first is the cURL resource, the second is a string 
 *                                                          containing a password prompt, and the third is the maximum password length. Return the string 
 *                                                          containing the password. 
 * CURLOPT_RETURNTRANSFER               must be Boolean     TRUE to return the transfer as a string of the return value of curl_exec() instead of outputting it 
 *                                                          directly. If used with CURLOPT_FILE, CURLOPT_RETURNTRANSFER MUST be set BEFORE, see
 *                                                          https://www.php.net/manual/en/function.curl-setopt.php#99082
 * CURLOPT_SAFE_UPLOAD                  TRUE                Always TRUE, what disables support for the @ prefix for uploading files in CURLOPT_POSTFIELDS, which 
 *                                                          means that values starting with @ can be safely passed as fields. CURLFile class 
 *                                                          (https://www.php.net/manual/en/class.curlfile.php) may be used for uploads instead. 
 * 
 * Old/Deprecated Options in libcurl that PHP still uses
 * Option                               Validation          Explanation
 * CURLOPT_FILE                         must be Stream      The file that the transfer should be written to. The default is STDOUT (the browser window). 
 *                                                          Substituted by CURLOPT_HEADERDATA. See https://github.com/curl/curl/blob/master/include/curl/curl.h#L691
 *                                                          If used with CURLOPT_RETURNTRANSFER, CURLOPT_FILE MUST be set AFTER, see
 *                                                          https://www.php.net/manual/en/function.curl-setopt.php#99082
 * CURLOPT_INFILE                       must be Stream      The file that the transfer should be read from when uploading. Substituted by CURLOPT_HEADERDATA. 
 *                                                          See https://github.com/curl/curl/blob/master/include/curl/curl.h#L691
 * CURLOPT_WRITEHEADER                  must be Stream      The file that the header part of the transfer is written to. Substituted by CURLOPT_HEADERDATA. See 
 *                                                          https://github.com/curl/curl/blob/master/include/curl/curl.h#L691
 * CURLOPT_FTPAPPEND                    must be Boolean     TRUE to append to the remote file instead of overwriting it. Substituted by CURLOPT_APPEND. See
 *                                                          https://github.com/curl/curl/blob/master/include/curl/curl.h#L2174
 * CURLOPT_FTPASCII                     must be Boolean     An alias of CURLOPT_TRANSFERTEXT. Use that instead.
 * CURLOPT_FTPLISTONLY                  must be Boolean     TRUE to only list the names of an FTP directory. Use CURLOPT_DIRLISTONLY instead. See 
 *                                                          https://github.com/curl/curl/blob/master/include/curl/curl.h#L2174
 * CURLOPT_KRB4LEVEL                    must be String      The KRB4 (Kerberos 4) security level. Any of the following values (in order from least to most 
 *                                                          powerful) are valid: "clear", "safe", "confidential", "private".. If the string does not match one 
 *                                                          of these, "private" is used. Setting this option to null will disable KRB4 security. Currently KRB4 
 *                                                          security only works with FTP transactions. Use CURLOPT_KRBLEVEL instead. See 
 *                                                          https://github.com/curl/curl/blob/master/include/curl/curl.h#L2183
 * CURLOPT_ENCODING                     String/cURL 7.10    The contents of the "Accept-Encoding: " header. This enables decoding of the response. Supported 
 *                                      PHP 7.3.15/7.4.3    encodings are "identity", "deflate", and "gzip". If an empty string, "", is set, a header containing 
 *                                                          all supported encoding types is sent. Use CURLOPT_ACCEPT_ENCODING instead. See
 *                                                          https://github.com/curl/curl/blob/master/include/curl/curl.h#L634
 * CURLOPT_SSLCERTPASSWD                must be String      The password required to use the CURLOPT_SSLCERT certificate. Use CURLOPT_KEYPASSWD instead. See
 *                                                          https://github.com/curl/curl/blob/master/include/curl/curl.h#L2182
 * CURLOPT_SSLKEYPASSWD                 must be String      The secret password needed to use the private SSL key specified in CURLOPT_SSLKEY. Use 
 *                                                          CURLOPT_KEYPASSWD instead. See https://github.com/curl/curl/blob/master/include/curl/curl.h#L2174
 * 
 * @param   array   $cURLOptions    An array of set CURL options
 * @return  boolean TRUE if all validates OK, FALSE otherwise -as errors get logged on the fly-
 * @since 0.0.9
 * @todo    Change string to int options
 *          Rewrite de ext curl que lo pille todo?  https://github.com/php/php-src/tree/master/ext/curl
 *                                                  https://www.zend.com/resources/writing-php-extensions (ver PDF bajada)
 *                                                  y sobre todo https://www.phpinternalsbook.com/index.html#
 *                                                  Si es muy lío ir a pelo (aunque introduce dependencias): https://github.com/CopernicaMarketingSoftware/PHP-CPP
 *          Crear funciones de validación para las diferentes opciones que hacen lo mismo... cuando me meta con la extensión
 * @see 
 *      cURL Options  https://www.php.net/manual/en/function.curl-setopt.php
 *      Official options: https://curl.se/libcurl/c/curl_easy_setopt.html
 */
function cURLOptionsValidate($cURLOptions)
{
    if (DEBUGMODE)
    {
        echo date("Y-m-d H:i:s").' -> cURLOptionsValidate '.PHP_EOL;
    }

    //Validar las de:
    //  POST application/x-www-form-urlencoded (Field=1&Field=2&Field=3): https://curl.se/libcurl/c/postinmemory.html
    //  POST multipart/form-data (Array):   https://gist.github.com/ramingar/2a75511824a5f50392457538cab7cce6
    //                                      https://gist.github.com/maxivak/18fcac476a2f4ea02e5f80b303811d5f
    //  GET: https://curl.se/libcurl/c/getinmemory.html
    //At least the URL Must be included
    /*
     *     curl_setopt($cURL, CURLOPT_RETURNTRANSFER, TRUE);
    curl_setopt($cURL, CURLOPT_ENCODING, '');
    curl_setopt($cURL, CURLOPT_MAXREDIRS, 10);
    curl_setopt($cURL, CURLOPT_TIMEOUT, 120);
    curl_setopt($cURL, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
    curl_setopt($cURL, CURLOPT_CUSTOMREQUEST, $HTTPMethod); // ***POR AQUI***
    curl_setopt($cURL, CURLOPT_SSL_VERIFYPEER, FALSE);


        cURL handle test @see CURLOPT_SHARE down there
     */
    if (empty($cURLOptions))
    {
        echo 'No options to validate'.PHP_EOL;

        return FALSE;
    }
    else
    {
        echo count($cURLOptions).' options to validate'.PHP_EOL;
    }

    $BadcURLOptions = array();
    //$Key cames as int now
    //#define CURLOPT(na,t,nu) na = t + nu @see https://github.com/curl/curl/blob/master/include/curl/curl.h#L1060
    //#define CURLOPTTYPE_LONG          0       also redefined:
    //                                          #define CURLOPTTYPE_VALUES      CURLOPTTYPE_LONG
    //#define CURLOPTTYPE_OBJECTPOINT   10000   also redefined:
    //                                          #define CURLOPTTYPE_STRINGPOINT CURLOPTTYPE_OBJECTPOINT
    //                                          #define CURLOPTTYPE_SLISTPOINT  CURLOPTTYPE_OBJECTPOINT
    //                                          #define CURLOPTTYPE_CBPOINT     CURLOPTTYPE_OBJECTPOINT
    //#define CURLOPTTYPE_FUNCTIONPOINT 20000
    //#define CURLOPTTYPE_OFF_T         30000
    //#define CURLOPTTYPE_BLOB          40000
    //See below for each string to int translation... hopefully their will be the same in PHP
    foreach ($cURLOptions as $Key => $Value)
    {
        if (!is_array($Value))
        {
            $Textd = $Value;
        }
        else
        {
            $Textd = json_encode($Value, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_NUMERIC_CHECK);
        }
        echo cURLOptionConstantToLiteral($Key).'=>'.$Textd.PHP_EOL;
        switch ($Key)
        {
            //Behaviour Options
            // CURLOPT(CURLOPT_VERBOSE, CURLOPTTYPE_LONG, 41) = 41
            case 41: //CURLOPT_VERBOSE https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L463
            // CURLOPT(CURLOPT_HEADER, CURLOPTTYPE_LONG, 42) = 42
            case 42: //CURLOPT_HEADER https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L148
            // CURLOPT(CURLOPT_NOPROGRESS, CURLOPTTYPE_LONG, 43) = 43
            case 43: //CURLOPT_NOPROGRESS https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L233
                if (!is_bool($Value))
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be a Boolean value';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                break;
            //CURLOPT(CURLOPT_NOSIGNAL, CURLOPTTYPE_LONG, 99) = 99
            case 99: //CURLOPT_NOSIGNAL https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L238
                if (!is_bool($Value)||!CheckcURLVersion('7.10.0'))
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be a Boolean, using cURL >= 7.10.0';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                break;
            //CURLOPT(CURLOPT_WILDCARDMATCH, CURLOPTTYPE_LONG, 197) = 197
            case 197: //CURLOPT_WILDCARDMATCH https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L2165
                cURLWarn($Key, TRUE, FALSE, FALSE);
                if (!is_bool($Value)||!CheckcURLVersion('7.21.0'))
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be a Boolean, using cURL >= 7.21.0';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                break;
                //Callback Options
            //CURLOPT(CURLOPT_WRITEFUNCTION, CURLOPTTYPE_FUNCTIONPOINT, 11) = 20011
            case 20011: //CURLOPT_WRITEFUNCTION https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L468
            //CURLOPT(CURLOPT_READFUNCTION, CURLOPTTYPE_FUNCTIONPOINT, 12) = 20012
            case 20012: //CURLOPT_READFUNCTION https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L323
            //CURLOPT(CURLOPT_HEADERFUNCTION, CURLOPTTYPE_FUNCTIONPOINT, 79) = 20079
            case 20079: //CURLOPT_HEADERFUNCTION https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L153
                //Must be an array of a handle and a string
                //curl_setopt($this->curl_handle, CURLOPT_WRITEFUNCTION, array($this, "receiveResponse"));
                //@see https://www.php.net/manual/en/function.curl-setopt.php#98491
                //@SEE https://www.php.net/manual/es/function.curl-setopt.php#66832 (CURLOPT_READFUNCTION)
                if (!is_array($Value))
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be an array(cURLHandle, string) value';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                else
                {
                    if (!is_object($Value[0])||(!is_a($Value[0],'CurlHandle'))||(!is_string($Value[1])))
                    {
                        $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be an array(cURLHandle, string) value';
                        ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                    }
                }
                break;
            //#define CURLOPT_FILE CURLOPT_WRITEDATA
            //CURLOPT(CURLOPT_WRITEDATA, CURLOPTTYPE_CBPOINT, 1) = 10001
            case 10001: //CURLOPT_WRITEDATA **UNDOCUMENTED AS OF PHP 8.1.0**
            //#define CURLOPT_INFILE CURLOPT_READDATA
            //CURLOPT(CURLOPT_READDATA, CURLOPTTYPE_CBPOINT, 9) = 10009
            case 10009: //CURLOPT_READDATA https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L318
            //#define CURLOPT_WRITEHEADER CURLOPT_HEADERDATA
            //CURLOPT(CURLOPT_HEADERDATA, CURLOPTTYPE_CBPOINT, 29) = 10029
            case 10029: //CURLOPT_HEADERDATA  **UNDOCUMENTED AS OF PHP 8.1.0**
                //CURLOPT_FILE change option to CURLOPT_WRITEDATA, see https://github.com/curl/curl/blob/master/include/curl/curl.h#L692
                //CURLOPT_INFILE change option to CURLOPT_READDATA, see https://github.com/curl/curl/blob/master/include/curl/curl.h#L693
                //CURLOPT_WRITEHEADER change option to CURLOPT_HEADERDATA, see https://github.com/curl/curl/blob/master/include/curl/curl.h#L694
                //Must be a resource, the resource must be a stream, and the stream must be readable
                if (!is_resource($Value))
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be a resource';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                else
                {
                    $Meta = stream_get_meta_data($Value);
                    if (!is_writable($Meta['uri']))
                    {
                        $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be a writeable resource';
                        ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                    }
                }
                break;
            //CURLOPT(CURLOPT_DEBUGDATA, CURLOPTTYPE_CBPOINT, 95) = 10095
            //#define CURLOPT_PROGRESSDATA CURLOPT_XFERINFODATA
            //CURLOPT(CURLOPT_XFERINFODATA, CURLOPTTYPE_CBPOINT, 57) = 10057
            case 10057: //CURLOPT_PROGRESSDATA and CURLOPT_XFERINFODATA **UNDOCUMENTED AS OF PHP 8.1.0**
            case 10095: //CURLOPT_DEBUGDATA  **UNDOCUMENTED AS OF PHP 8.1.0**
                cURLWarn($Key, TRUE, FALSE, FALSE);
                if (!is_string($Value))
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be a string value';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                break;
            //CURLOPT(CURLOPT_DEBUGFUNCTION, CURLOPTTYPE_FUNCTIONPOINT, 94) = 20094
            case 20094: //CURLOPT_DEBUGFUNCTION **UNDOCUMENTED AS OF PHP 8.1.0**
                //Must be an array of a handle and a string
                //curl_setopt($this->curl_handle, CURLOPT_WRITEFUNCTION, array($this, "receiveResponse"));
                //@see https://www.php.net/manual/en/function.curl-setopt.php#98491
                //@SEE https://www.php.net/manual/es/function.curl-setopt.php#66832 (CURLOPT_READFUNCTION)
                cURLWarn($Key, TRUE, FALSE, FALSE);
                if (!is_array($Value))
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be an array(cURLHandle, string) value';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                else
                {
                    if (!is_object($Value[0])||(!is_a($Value[0],'CurlHandle'))||(!is_string($Value[1])))
                    {
                        $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be an array(cURLHandle, string) value';
                        ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                    }
                }
                break;
            //CURLOPT(CURLOPT_SSL_CTX_FUNCTION, CURLOPTTYPE_FUNCTIONPOINT, 108) = 20108
            case 20108: //CURLOPT_SSL_CTX_FUNCTION **UNDOCUMENTED AS OF PHP 8.1.0**
                cURLWarn($Key, TRUE, FALSE, FALSE);
                if (!is_array($Value)||!CheckcURLVersion('7.11.0'))
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be an array(cURLHandle, string) value and using cURL >= 7.11.0';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                else
                {
                    if (!is_object($Value[0])||(!is_a($Value[0],'CurlHandle'))||(!is_string($Value[1])))
                    {
                        $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be an array(cURLHandle, string) value';
                        ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                    }
                }
                break;
            //CURLOPT(CURLOPT_SSL_CTX_DATA, CURLOPTTYPE_CBPOINT, 109) = 10109
            case 10109: //CURLOPT_SSL_CTX_DATA  **UNDOCUMENTED AS OF PHP 8.1.0**
                cURLWarn($Key, TRUE, FALSE, FALSE);
                if (!is_string($Value)||!CheckcURLVersion('7.11.0'))
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be a String value and using cURL >= 7.11.0';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                break;
            //CURLOPT(CURLOPT_IOCTLFUNCTION, CURLOPTTYPE_FUNCTIONPOINT, 130) = 20130
            case 20130: //CURLOPT_IOCTLFUNCTION  **UNDOCUMENTED AS OF PHP 8.1.0**
                cURLWarn($Key, TRUE, FALSE, '7.18.0#CURLOPT_SEEKFUNCTION');
                if (!is_array($Value)||!CheckcURLVersion('7.12.3'))
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be an array(cURLHandle, string) value and using cURL >= 7.12.3';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                else
                {
                    if (!is_object($Value[0])||(!is_a($Value[0],'CurlHandle'))||(!is_string($Value[1])))
                    {
                        $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be an array(cURLHandle, string) value';
                        ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                    }
                }
                break;
            //CURLOPT(CURLOPT_IOCTLDATA, CURLOPTTYPE_CBPOINT, 131) = 10131
            case 10131: //CURLOPT_IOCTLDATA   **UNDOCUMENTED AS OF PHP 8.1.0**
                cURLWarn($Key, TRUE, FALSE, '7.18.0#CURLOPT_SEEKDATA');
                if (!is_string($Value)||!CheckcURLVersion('7.12.3'))
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be a String value and using cURL >= 7.12.3';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
            //CURLOPT(CURLOPT_SOCKOPTFUNCTION, CURLOPTTYPE_FUNCTIONPOINT, 148) = 20148
            case 20148: //CURLOPT_SOCKOPTFUNCTION **UNDOCUMENTED AS OF PHP 8.1.0**
                cURLWarn($Key, TRUE, FALSE, FALSE);
                if (!is_array($Value)||!CheckcURLVersion('7.16.0'))
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be an array(cURLHandle, string) value and using cURL >= 7.16.0';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                else
                {
                    if (!is_object($Value[0])||(!is_a($Value[0],'CurlHandle'))||(!is_string($Value[1])))
                    {
                        $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be an array(cURLHandle, string) value';
                        ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                    }
                }
                break;
            //CURLOPT(CURLOPT_SOCKOPTDATA, CURLOPTTYPE_CBPOINT, 149) = 10149
            case 10149: //CURLOPT_SOCKOPTDATA **UNDOCUMENTED AS OF PHP 8.1.0**
                cURLWarn($Key, TRUE, FALSE, FALSE);
                if (!is_string($Value)||!CheckcURLVersion('7.16.0'))
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be a String value and using cURL >= 7.16.0';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                break;
            //CURLOPT(CURLOPT_OPENSOCKETFUNCTION, CURLOPTTYPE_FUNCTIONPOINT, 163) = 20163
            case 20163: //CURLOPT_OPENSOCKETFUNCTION **UNDOCUMENTED AS OF PHP 8.1.0**
                cURLWarn($Key, TRUE, FALSE, FALSE);
                if (!is_array($Value)||!CheckcURLVersion('7.17.1'))
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be an array(cURLHandle, string) value and using cURL >= 7.17.1';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                else
                {
                    if (!is_object($Value[0])||(!is_a($Value[0],'CurlHandle'))||(!is_string($Value[1])))
                    {
                        $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be an array(cURLHandle, string) value';
                        ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                    }
                }
                break;
            //CURLOPT(CURLOPT_OPENSOCKETDATA, CURLOPTTYPE_CBPOINT, 164) = 10164
            case 10164: //CURLOPT_OPENSOCKETDATA **UNDOCUMENTED AS OF PHP 8.1.0**
                cURLWarn($Key, TRUE, FALSE, FALSE);
                if (!is_string($Value)||!CheckcURLVersion('7.17.1'))
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be a String value and using cURL >= 7.17.1';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                break;
            //CURLOPT(CURLOPT_SEEKFUNCTION, CURLOPTTYPE_FUNCTIONPOINT, 167) = 20167
            case 20167: //CURLOPT_SEEKFUNCTION **UNDOCUMENTED AS OF PHP 8.1.0**
                cURLWarn($Key, TRUE, FALSE, FALSE);
                if (!is_array($Value)||!CheckcURLVersion('7.18.0'))
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be an array(cURLHandle, string) value and using cURL >= 7.18.0';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                else
                {
                    if (!is_object($Value[0])||(!is_a($Value[0],'CurlHandle'))||(!is_string($Value[1])))
                    {
                        $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be an array(cURLHandle, string) value';
                        ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                    }
                }
                break;
            //CURLOPT(CURLOPT_SEEKDATA, CURLOPTTYPE_CBPOINT, 168) = 10168
            case 10168: //CURLOPT_SEEKDATA **UNDOCUMENTED AS OF PHP 8.1.0**
                cURLWarn($Key, TRUE, FALSE, FALSE);
                if (!is_string($Value)||!CheckcURLVersion('7.18.0'))
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be a String value and using cURL >= 7.18.0';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                break;
            //CURLOPT(CURLOPT_INTERLEAVEFUNCTION, CURLOPTTYPE_FUNCTIONPOINT, 196) = 20196
            case 20196: //CURLOPT_INTERLEAVEFUNCTION **UNDOCUMENTED AS OF PHP 8.1.0**
                cURLWarn($Key, TRUE, FALSE, FALSE);
                if (!is_array($Value)||!CheckcURLVersion('7.20.0'))
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be an array(cURLHandle, string) value and using cURL >= 7.20.0';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                else
                {
                    if (!is_object($Value[0])||(!is_a($Value[0],'CurlHandle'))||(!is_string($Value[1])))
                    {
                        $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be an array(cURLHandle, string) value';
                        ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                    }
                }
                break;
            // CURLOPT(CURLOPT_INTERLEAVEDATA, CURLOPTTYPE_CBPOINT, 195) = 10195
            case 10195: //CURLOPT_INTERLEAVEDATA **UNDOCUMENTED AS OF PHP 8.1.0**
                cURLWarn($Key, TRUE, FALSE, FALSE);
                if (!is_string($Value)||!CheckcURLVersion('7.20.0'))
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be a String value and using cURL >= 7.20.0';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                break;
            //CURLOPT(CURLOPT_CHUNK_BGN_FUNCTION, CURLOPTTYPE_FUNCTIONPOINT, 198) = 20198
            case 20198: //CURLOPT_CHUNK_BGN_FUNCTION **UNDOCUMENTED AS OF PHP 8.1.0**
            //CURLOPT(CURLOPT_CHUNK_END_FUNCTION, CURLOPTTYPE_FUNCTIONPOINT, 199) = 20199
            case 20199: //CURLOPT_CHUNK_END_FUNCTION **UNDOCUMENTED AS OF PHP 8.1.0**
            //CURLOPT(CURLOPT_FNMATCH_FUNCTION, CURLOPTTYPE_FUNCTIONPOINT, 200) = 20200
            case 20200: //CURLOPT_FNMATCH_FUNCTION https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L2160
                cURLWarn($Key, TRUE, FALSE, FALSE);
                if (!is_array($Value)||!CheckcURLVersion('7.21.0'))
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be an array(cURLHandle, string) value and using cURL >= 7.21.0';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                else
                {
                    if (!is_object($Value[0])||(!is_a($Value[0],'CurlHandle'))||(!is_string($Value[1])))
                    {
                        $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be an array(cURLHandle, string) value';
                        ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                    }
                }
                break;
            //CURLOPT(CURLOPT_CHUNK_DATA, CURLOPTTYPE_CBPOINT, 201) = 10201
            case 10201: //CURLOPT_CHUNK_DATA **UNDOCUMENTED AS OF PHP 8.1.0**
            //CURLOPT(CURLOPT_FNMATCH_DATA, CURLOPTTYPE_CBPOINT, 202) = 10202
            case 10202: //CURLOPT_FNMATCH_DATA **UNDOCUMENTED AS OF PHP 8.1.0**
                cURLWarn($Key, TRUE, FALSE, FALSE);
                if (!is_string($Value)||!CheckcURLVersion('7.21.0'))
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be a String value and using cURL >= 7.21.0';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                break;
            //CURLOPT(CURLOPT_CLOSESOCKETFUNCTION, CURLOPTTYPE_FUNCTIONPOINT, 208) = 20208
            case 20208: //CURLOPT_CLOSESOCKETFUNCTION **UNDOCUMENTED AS OF PHP 8.1.0**
                cURLWarn($Key, TRUE, FALSE, FALSE);
                if (!is_array($Value)||!CheckcURLVersion('7.21.7'))
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be an array(cURLHandle, string) value and using cURL >= 7.21.7';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                else
                {
                    if (!is_object($Value[0])||(!is_a($Value[0],'CurlHandle'))||(!is_string($Value[1])))
                    {
                        $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be an array(cURLHandle, string) value';
                        ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                    }
                }
                break;
            //CURLOPT(CURLOPT_CLOSESOCKETDATA, CURLOPTTYPE_CBPOINT, 209) = 10209
            case 10209: //CURLOPT_CLOSESOCKETDATA **UNDOCUMENTED AS OF PHP 8.1.0**
                cURLWarn($Key, TRUE, FALSE, FALSE);
                if (!is_string($Value)||!CheckcURLVersion('7.21.7'))
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be a String value and using cURL >= 7.21.7';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                break;
            //CURLOPT(CURLOPT_PROGRESSFUNCTION, CURLOPTTYPE_FUNCTIONPOINT, 56) = 20056
            case 20056: //CURLOPT_PROGRESSFUNCTION https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L273
                cURLWarn($Key, FALSE, FALSE, '7.32.0#CURLOPT_XFERINFOFUNCTION');
                if (!is_array($Value))
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be an array(cURLHandle, string) value';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                else
                {
                    if (!is_object($Value[0])||(!is_a($Value[0],'CurlHandle'))||(!is_string($Value[1])))
                    {
                        $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be an array(cURLHandle, string) value';
                        ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                    }
                }
                break;
            //CURLOPT(CURLOPT_XFERINFOFUNCTION, CURLOPTTYPE_FUNCTIONPOINT, 219) = 20219
            case 20219: //CURLOPT_XFERINFOFUNCTION https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L479
                cURLWarn($Key, TRUE, FALSE, FALSE);
                if (!is_array($Value)||!CheckcURLVersion('7.32.0'))
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be an array(cURLHandle, string) value and using cURL >= 7.32.0';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                else
                {
                    if (!is_object($Value[0])||(!is_a($Value[0],'CurlHandle'))||(!is_string($Value[1])))
                    {
                        $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be an array(cURLHandle, string) value';
                        ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                    }
                }
                break;
            // CURLOPT(CURLOPT_RESOLVER_START_FUNCTION, CURLOPTTYPE_FUNCTIONPOINT, 272) = 20272
            case 20272: //CURLOPT_RESOLVER_START_FUNCTION **UNDOCUMENTED AS OF PHP 8.1.0**
                cURLWarn($Key, TRUE, FALSE, FALSE);
                if (!is_array($Value)||!CheckcURLVersion('7.59.0'))
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be an array(cURLHandle, string) value and using cURL >= 7.59.0';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                else
                {
                    if (!is_object($Value[0])||(!is_a($Value[0],'CurlHandle'))||(!is_string($Value[1])))
                    {
                        $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be an array(cURLHandle, string) value';
                        ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                    }
                }
                break;
            //CURLOPT(CURLOPT_RESOLVER_START_DATA, CURLOPTTYPE_CBPOINT, 273) = 10273
            case 10273: //CURLOPT_RESOLVER_START_DATA **UNDOCUMENTED AS OF PHP 8.1.0**
                cURLWarn($Key, TRUE, FALSE, FALSE);
                if (!is_string($Value)||!CheckcURLVersion('7.59.0'))
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be a String value and using cURL >= 7.59.0';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                break;
            //CURLOPT(CURLOPT_PREREQFUNCTION, CURLOPTTYPE_FUNCTIONPOINT, 312) = 20312
            case 20312: //CURLOPT_PREREQFUNCTION **UNDOCUMENTED AS OF PHP 8.1.0**
                cURLWarn($Key, TRUE, FALSE, FALSE);
                if (!is_array($Value)||!CheckcURLVersion('7.80.0'))
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be an array(cURLHandle, string) value and using cURL >= 7.80.0';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                else
                {
                    if (!is_object($Value[0])||(!is_a($Value[0],'CurlHandle'))||(!is_string($Value[1])))
                    {
                        $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be an array(cURLHandle, string) value';
                        ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                    }
                }
                break;
            //CURLOPT(CURLOPT_PREREQDATA, CURLOPTTYPE_CBPOINT, 313) = 10313
            case 10313: //CURLOPT_PREREQDATA **UNDOCUMENTED AS OF PHP 8.1.0**
                cURLWarn($Key, TRUE, FALSE, FALSE);
                if (!is_string($Value)||!CheckcURLVersion('7.80.0'))
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be a String value and using cURL >= 7.80.0';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                break;
            //CURLOPT(CURLOPT_CONV_TO_NETWORK_FUNCTION, CURLOPTTYPE_FUNCTIONPOINT, 143) = 20143
            case 20143: //CURLOPT_CONV_TO_NETWORK_FUNCTION  **UNDOCUMENTED AS OF PHP 8.1.0**
            //CURLOPT(CURLOPT_CONV_FROM_NETWORK_FUNCTION, CURLOPTTYPE_FUNCTIONPOINT, 142) = 20142
            case 20142: //CURLOPT_CONV_FROM_NETWORK_FUNCTION **UNDOCUMENTED AS OF PHP 8.1.0**
            //CURLOPT(CURLOPT_CONV_FROM_UTF8_FUNCTION, CURLOPTTYPE_FUNCTIONPOINT, 144) = 20144
            case 20144: //CURLOPT_CONV_FROM_UTF8_FUNCTION **UNDOCUMENTED AS OF PHP 8.1.0**
                cURLWarn($Key, TRUE, FALSE, '7.82.0#none as it is not needed anymore.');
                if (!is_array($Value)||!FNAcURLVersion('7.82.0'))
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be an array(cURLHandle, string) value. The function was retired on cURL 7.82.0';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                else
                {
                    if (!is_object($Value[0])||(!is_a($Value[0],'CurlHandle'))||(!is_string($Value[1])))
                    {
                        $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be an array(cURLHandle, string) value';
                        ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                    }
                }
                break;
            //CURLOPT(CURLOPT_PATH_AS_IS, CURLOPTTYPE_LONG, 234) = 234
            case 234: //CURLOPT_PATH_AS_IS https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L2552
                if (!is_bool($Value)||!CheckPHPVersion('7.0.7')||!CheckcURLVersion('7.42.0'))
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be a Boolean, using cURL >= 7.42.0 and PHP >= 7.0.7';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                break;
            //CURLOPT(CURLOPT_SUPPRESS_CONNECT_HEADERS, CURLOPTTYPE_LONG, 265) = 265
            case 265: //CURLOPT_SUPPRESS_CONNECT_HEADERS https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L2870
                if (!is_bool($Value)||!CheckPHPVersion('7.3.0')||!CheckcURLVersion('7.54.0'))
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be a Boolean, using cURL >= 7.54.0 and PHP >= 7.3.0';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                break;
                //Error Options
            //CURLOPT(CURLOPT_ERRORBUFFER, CURLOPTTYPE_OBJECTPOINT, 10) = 10010
            case 10010: //CURLOPT_ERRORBUFFER **UNDOCUMENTED AS OF PHP 8.1.0**
                cURLWarn($Key, TRUE, FALSE, FALSE);
                if (!is_string($Value))
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be a string value';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                break;
            //CURLOPT(CURLOPT_STDERR, CURLOPTTYPE_OBJECTPOINT, 37) = 10037
            case 10037: //CURLOPT_STDERR https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L408
                //Must be a resource, the resource must be a stream, and the stream must be readable
                if (!is_resource($Value))
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be a resource';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                else
                {
                    $Meta = stream_get_meta_data($Value);
                    if (!is_writable($Meta['uri']))
                    {
                        $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be a writeable resource';
                        ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                    }
                }
                break;
            //CURLOPT(CURLOPT_FAILONERROR, CURLOPTTYPE_LONG, 45) = 45
            case 45: //CURLOPT_FAILONERROR https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L93
                if (!is_bool($Value))
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be a Boolean value';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                break;
            //CURLOPT(CURLOPT_KEEP_SENDING_ON_ERROR, CURLOPTTYPE_LONG, 245) = 245
            case 245: //CURLOPT_KEEP_SENDING_ON_ERROR https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L2696
                if (!is_bool($Value)||!CheckPHPVersion('7.3.0')||!CheckcURLVersion('7.51.0'))
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be a Boolean, using cURL >= 7.51.0 and PHP >= 7.3.0';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                break;
                //Network options
            //CURLOPT(CURLOPT_HTTPPROXYTUNNEL, CURLOPTTYPE_LONG, 61) = 61
            case 61: //CURLOPT_HTTPPROXYTUNNEL https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L173
                if (!is_bool($Value))
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be a boolean value';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                break;
            //CURLOPT(CURLOPT_DNS_USE_GLOBAL_CACHE, CURLOPTTYPE_LONG, 91) = 91
            case 91: //CURLOPT_DNS_USE_GLOBAL_CACHE https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L78
                cURLWarn($Key, FALSE, FALSE, '7.11.0#CURLOPT_SHARE');
                if (!is_bool($Value))
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be a Boolean value';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                break;
            //CURLOPT(CURLOPT_DNS_CACHE_TIMEOUT, CURLOPTTYPE_LONG, 92) = 92
            case 92: //CURLOPT_DNS_CACHE_TIMEOUT https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L73
                if (!is_int($Value))
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be an Integer value';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                break;
            //CURLOPT(CURLOPT_INTERFACE, CURLOPTTYPE_STRINGPOINT, 62) = 10062
            case 10062: //CURLOPT_INTERFACE https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L193
                if (!is_string($Value))
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be a string value';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                break;
            //CURLOPT(CURLOPT_PROXY, CURLOPTTYPE_STRINGPOINT, 4) = 10004
            case 10004: //CURLOPT_PROXY https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L193
                //CURLOPT_PROXY: No validation for URL as "" is acceptable
                if (!is_string($Value))
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be a string value';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                else
                {
                    if ($Value !== "")
                    {
                        //Validate format [scheme]://[host]
                        $ValidProxySchemes = array('http', 'https', 'socks4', 'socks4a', 'socks5', 'socks5h', 'socks');
                        $Scheme = parse_url($Value, PHP_URL_SCHEME);
                        if (!in_array($Scheme, $ValidProxySchemes))
                        {
                            $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to one of: '.implode(',',$ValidProxySchemes);
                            ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                        }
                        $Host = parse_url($Value, PHP_URL_HOST);
                        $FailedVerification = FALSE;
                        if ($Host[0] != '[')
                        {
                            //Not an IPv6
                            if (!IsValidHostName($Host)||!IsValidIPv4($Host))
                            {
                                $FailedVerification = TRUE;
                            }
                        }
                        else
                        {
                            $TheIPv6 = substr($Host, 1, strlen($Host)-1);
                            if (!IsValidIPv6($TheIPv6))
                            {
                                $FailedVerification = TRUE;
                            }
                        }
                        if ($FailedVerification === TRUE)
                        {
                            $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be a valid RFC1123 hostname or IPv4. A numerical IPv6 address must be written within [brackets]';
                            ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                        }
                    } //End value is not empty
                }
                break;
            //CURLOPT(CURLOPT_URL, CURLOPTTYPE_STRINGPOINT, 2) = 10002
            case 10002: //CURLOPT_URL https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L448
                if (!is_string($Value)||!IsValidURI($Value))
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be a string value and a valid RFC 3986 URI/URL';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                break;
            //CURLOPT(CURLOPT_PROXYPORT, CURLOPTTYPE_LONG, 59) = 59
            case 59: //CURLOPT_PROXYPORT https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L283
            //CURLOPT(CURLOPT_PORT, CURLOPTTYPE_LONG, 3) = 3
            case 3: //CURLOPT_PORT https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L243
                if (!is_int($Value)||!IsValidIANAPort($Value))
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be an Integer and a valid IANA Port on the range 0-65535';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                break;
            //CURLOPT(CURLOPT_PROXYTYPE, CURLOPTTYPE_VALUES, 101) = 101
            case 101: //CURLOPT_PROXYTYPE https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L288
                if (!is_int($Value)||!CheckcURLVersion('7.10.0')||$Value<0||$Value>7)
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be an Integer on the range [0-7] and using cURL >= 7.10.0';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                else
                {
                    //@see https://github.com/curl/curl/blob/master/include/curl/curl.h#L755
                    //Mutually exclusive
                    //CURLPROXY_HTTP            0   added in 7.10, new in 7.19.4 default is to use CONNECT HTTP/1.1
                    //CURLPROXY_HTTP_1_0        1   added in 7.19.4, force to use CONNECT HTTP/1.0
                    //CURLPROXY_HTTPS           2   added in 7.52.0
                    //CURLPROXY_SOCKS4          4   support added in 7.15.2, enum existed already in 7.10
                    //CURLPROXY_SOCKS5          5   added in 7.10
                    //CURLPROXY_SOCKS4A         6   added in 7.18.0
                    //CURLPROXY_SOCKS5_HOSTNAME 7   Use the SOCKS5 protocol but pass along the host name rather than the IP address. Added in 7.18.0
                    switch ($Value)
                    {
                        case CURLPROXY_HTTP:
                        case CURLPROXY_SOCKS5:
                            //Nothing to do, checked above
                            break;
                        case CURLPROXY_SOCKS4:
                            if (!CheckcURLVersion('7.15.2'))
                            {
                                $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' with CURLPROXY_SOCKS4 requires using cURL >= 7.15.2';
                                ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                            }
                            break;
                        case CURLPROXY_SOCKS4A:
                        case CURLPROXY_SOCKS5_HOSTNAME:
                            if (!CheckcURLVersion('7.18.0'))
                            {
                                $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' with CURLPROXY_SOCKS4A or CURLPROXY_SOCKS5_HOSTNAME requires using cURL >= 7.18.0';
                                ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                            }
                            break;
                        case CURLPROXY_HTTP_1_0:
                            if (!CheckcURLVersion('7.19.4'))
                            {
                                $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' with CURLPROXY_HTTP_1_0 requires using cURL >= 7.19.4';
                                ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                            }
                            break;
                        case CURLPROXY_HTTPS:
                            if (!CheckcURLVersion('7.52.0'))
                            {
                                $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' with CURLPROXY_HTTP_1_0 requires using cURL >= 7.52.0';
                                ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                            }
                            break;
                        default:
                            //Unknown option
                            $Message = $Key.' has an unknown value: '.$Value.'Check on https://github.com/curl/curl/blob/master/include/curl/curl.h. Open an issue in https://github.com/ProceduralMan/MinionLib/issues if needed.';
                            ErrorLog($Message, E_USER_WARNING);
                            break;
                    }
                }
                break;
            //CURLOPT(CURLOPT_BUFFERSIZE, CURLOPTTYPE_LONG, 98) = 98
            case 98: //CURLOPT_BUFFERSIZE https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L23
                if (!is_int($Value)||!CheckcURLVersion('7.10.0'))
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be a Integer, and using cURL >= 7.10.0';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                else
                {
                    //Limits testing
                    if ($Value<1024||$Value>524288)
                    {
                        $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' must be between 1024 and CURL_MAX_READ_SIZE (=512KB)';
                        ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                    }
                }
                break;
            //CURLOPT(CURLOPT_TCP_NODELAY, CURLOPTTYPE_LONG, 121) = 121
            case 121: //CURLOPT_TCP_NODELAY https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L1358
                if (!is_bool($Value)||!CheckcURLVersion('7.11.2'))
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be a Boolean, using cURL >= 7.11.2';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                break;
            //CURLOPT(CURLOPT_LOCALPORT, CURLOPTTYPE_LONG, 139) = 139
            case 139: //CURLOPT_LOCALPORT https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L1449
                cURLWarn($Key, TRUE, FALSE, FALSE);
                if (!is_int($Value)||!IsValidIANAPort($Value)||!CheckcURLVersion('7.15.2'))
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be an Integer, a valid IANA port and using cURL >= 7.15.2';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                break;
            //CURLOPT(CURLOPT_LOCALPORTRANGE, CURLOPTTYPE_LONG, 140) = 140
            case 140: //CURLOPT_LOCALPORTRANGE https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L1454
                cURLWarn($Key, TRUE, FALSE, FALSE);
                if (!is_int($Value)||!CheckcURLVersion('7.15.2'))
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be an Integer and using cURL >= 7.15.2';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                break;
            //CURLOPT(CURLOPT_ADDRESS_SCOPE, CURLOPTTYPE_LONG, 171) = 171
            case 171: //CURLOPT_ADDRESS_SCOPE https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L1733
                cURLWarn($Key, TRUE, FALSE, FALSE);
                if (!is_int($Value)||!CheckcURLVersion('7.19.0'))
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be an Integer and using cURL >= 7.19.0';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                break;
            //CURLOPT(CURLOPT_PROTOCOLS, CURLOPTTYPE_LONG, 181) = 181
            case 181: //CURLOPT_PROTOCOLS https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L1861
                cURLWarn($Key, FALSE, FALSE, '7.85.0#CURLOPT_PROTOCOLS_STR');
                if (!is_int($Value)||!CheckcURLVersion('7.19.4'))
                {
                    //Even though its a bitmask, the option CURLPROTO_ALL is (~0), the bitwise complement of 0, which is a number with all bits filled. On a 64
                    //bits system tham means 1111 1111 1111 1111 1111 1111 1111 1111 1111 1111 1111 1111 1111 1111 1111 1111 =  = PHP_INT_MIN
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be an Integer and using cURL >= 7.19.4';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                else
                {
                    //@see https://github.com/curl/curl/blob/master/include/curl/curl.h#L1014
                    //Possible values a combination of:
                    //CURLPROTO_ALL     (~0)    -1
                    //(~0), the bitwise complement of 0
                    //CURLPROTO_HTTP    (1<<0)  1
                    //(1<<0) bitshift 0 positions the 1 => 1*2^0 = 1
                    //CURLPROTO_HTTPS   (1<<1)  2
                    //(1<<1) bitshift 1 position the 1 => 1*2^1 = 2... and so on
                    //CURLPROTO_FTP     (1<<2)  4
                    //CURLPROTO_FTPS    (1<<3)  8
                    //CURLPROTO_SCP     (1<<4)  16
                    //CURLPROTO_SFTP    (1<<5)  32
                    //CURLPROTO_TELNET  (1<<6)  64
                    //CURLPROTO_LDAP    (1<<7)  128
                    //CURLPROTO_LDAPS   (1<<8)  256
                    //CURLPROTO_DICT    (1<<9)  512
                    //CURLPROTO_FILE    (1<<10) 1024
                    //CURLPROTO_TFTP    (1<<11) 2048
                    //CURLPROTO_IMAP    (1<<12) 4096
                    //CURLPROTO_IMAPS   (1<<13) 8192
                    //CURLPROTO_POP3    (1<<14) 16384
                    //CURLPROTO_POP3S   (1<<15) 32768
                    //CURLPROTO_SMTP    (1<<16) 65536
                    //CURLPROTO_SMTPS   (1<<17) 131072
                    //CURLPROTO_RTSP    (1<<18) 262144
                    //CURLPROTO_RTMP    (1<<19) 524288
                    //CURLPROTO_RTMPT   (1<<20) 1048576
                    //CURLPROTO_RTMPE   (1<<21) 2097152
                    //CURLPROTO_RTMPTE  (1<<22) 4194304
                    //CURLPROTO_RTMPS   (1<<23) 8388608
                    //CURLPROTO_RTMPTS  (1<<24) 16777216
                    //CURLPROTO_GOPHER  (1<<25) 33554432
                    //CURLPROTO_SMB     (1<<26) 67108864
                    //CURLPROTO_SMBS    (1<<27) 134217728
                    //CURLPROTO_MQTT    (1<<28) 268435456
                    //CURLPROTO_GOPHERS (1<<29) 536870912
                    if ($Value<-1||$Value>536870912)
                    {
                        $BadcURLOptions[$Key] = 'Incorrect value for '.cURLOptionConstantToLiteral($Key).'. Accepted values are between -1 and 536870912.';
                        ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                    }
                }
                break;
            //CURLOPT(CURLOPT_REDIR_PROTOCOLS, CURLOPTTYPE_LONG, 182) = 182
            case 182: //CURLOPT_REDIR_PROTOCOLS https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L1866
                cURLWarn($Key, FALSE, FALSE, '7.85.0#CURLOPT_REDIR_PROTOCOLS_STR');
                if (!is_int($Value)||!CheckcURLVersion('7.19.4'))
                {
                    //Even though its a bitmask, the option CURLPROTO_ALL is (~0), the bitwise complement of 0, which is a number with all bits filled. On a 64
                    //bits system tham means 1111 1111 1111 1111 1111 1111 1111 1111 1111 1111 1111 1111 1111 1111 1111 1111  = PHP_INT_MIN
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be an Integer and using cURL >= 7.19.4';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                else
                {
                    if (CheckcURLVersion('7.19.4')&&!CheckcURLVersion('7.40.0'))
                    {
                        //Version between 7.19.4 and 7.40.0
                        //On 7.19.4 all except FILE and SCP
                        if ($Value<-1||$Value>536870912||$Value === 1024||$Value === 16)
                        {
                            $BadcURLOptions[$Key] = 'Incorrect value for '.cURLOptionConstantToLiteral($Key).'. Accepted values are between -1 and 536870912. FILE and SCP protocols cannot be redirected.';
                            ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                        }
                    }
                    elseif (CheckcURLVersion('7.40.0')&&!CheckcURLVersion('7.65.2'))
                    {
                        //Version between 7.40.0 and 7.65.2
                        //On 7.4004 all except FILE, SCP, SMB and SMBS
                        if ($Value<-1||$Value>536870912||$Value === 134217728||$Value === 67108864|$Value === 1024||$Value === 16)
                        {
                            $BadcURLOptions[$Key] = 'Incorrect value for '.cURLOptionConstantToLiteral($Key).'. Accepted values are between -1 and 536870912. FILE, SCP, SMB and SMBS protocols cannot be redirected.';
                            ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                        }
                    }
                    else
                    {
                        //Version 7.65.2 or higher
                        //Only HTTP, HTTPS, FTP and FTPS
                        //CURLPROTO_ALL enables all protocols on redirect, including those disabled for security
                        if ($Value>89)
                        {
                            $BadcURLOptions[$Key] = 'Incorrect value for '.cURLOptionConstantToLiteral($Key).'. Accepted protocols are HTTP, HTTPS, FTP, FTPS or ALL.';
                            ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                        }
                    }
                }
                break;
            //CURLOPT(CURLOPT_NOPROXY, CURLOPTTYPE_STRINGPOINT, 177) = 10177
            case 10177: //CURLOPT_NOPROXY https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L1856
                if (!is_string($Value)||!CheckcURLVersion('7.19.4'))
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be a String, using cURL >= 7.19.4';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                break;
            //CURLOPT(CURLOPT_SOCKS5_GSSAPI_NEC, CURLOPTTYPE_LONG, 180) = 180
            case 180: //CURLOPT_SOCKS5_GSSAPI_NEC https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L1871
                cURLWarn($Key, TRUE, FALSE, FALSE);
                if (!is_bool($Value)||!CheckcURLVersion('7.19.4'))
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be a Boolean, using cURL >= 7.19.4';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                break;
            //CURLOPT(CURLOPT_SOCKS5_GSSAPI_SERVICE, CURLOPTTYPE_STRINGPOINT, 179) = 10179
            case 10179: //CURLOPT_SOCKS5_GSSAPI_SERVICE https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L1876
                cURLWarn($Key, TRUE, FALSE, '7.49.0#CURLOPT_PROXY_SERVICE_NAME');
                if (!is_string($Value)||!CheckcURLVersion('7.19.4'))
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be a String, using cURL >= 7.19.4';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                break;
            //CURLOPT(CURLOPT_TCP_KEEPALIVE, CURLOPTTYPE_LONG, 213) = 213
            case 213: //CURLOPT_TCP_KEEPALIVE https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L2324
                if (!is_int($Value)||!CheckcURLVersion('7.25.5')||$Value<0||$Value>1)
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be an Integer in the range [0-1] and using cURL >= 7.25.5';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                break;
            //CURLOPT(CURLOPT_TCP_KEEPIDLE, CURLOPTTYPE_LONG, 214) = 214
            case 214: //CURLOPT_TCP_KEEPIDLE https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L2329
            //CURLOPT(CURLOPT_TCP_KEEPINTVL, CURLOPTTYPE_LONG, 215) = 215
            case 215: //CURLOPT_TCP_KEEPINTVL https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L2334
                if (!is_int($Value)||!CheckcURLVersion('7.25.0')||$Value<0||$Value>2147483648)
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be an Integer in the range [0-2147483648] and using cURL >= 7.25.0';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                break;
            //CURLOPT(CURLOPT_UNIX_SOCKET_PATH, CURLOPTTYPE_STRINGPOINT, 231) = 10231
            case 10231: //CURLOPT_UNIX_SOCKET_PATH https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L2516
                //You can only test sockets created by yourself....
                if (!is_string($Value)||!CheckPHPVersion('7.0.7')||!CheckcURLVersion('7.40.0'))
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be a String, using cURL >= 7.40.0 and PHP >= 7.0.7';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                break;
            //CURLOPT(CURLOPT_PROXY_SERVICE_NAME, CURLOPTTYPE_STRINGPOINT, 235) = 10235
            case 10235: //CURLOPT_PROXY_SERVICE_NAME https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L2576
                if (!is_string($Value)||!CheckPHPVersion('7.0.7')||!CheckcURLVersion('7.43.0'))
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be a String, using cURL >= 7.43.0 and PHP >= 7.0.7';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                break;
            //CURLOPT(CURLOPT_SERVICE_NAME, CURLOPTTYPE_STRINGPOINT, 236) = 10236
            case 10236: //CURLOPT_SERVICE_NAME https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L2581
                if (!is_string($Value)||!CheckPHPVersion('7.0.7')||!CheckcURLVersion('7.43.0'))
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be a String, using cURL >= 7.43.0 and PHP >= 7.0.7';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                break;
            //CURLOPT(CURLOPT_DEFAULT_PROTOCOL, CURLOPTTYPE_STRINGPOINT, 238) = 10238
            case 10238: //CURLOPT_DEFAULT_PROTOCOL https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L2613
                $AcceptedValues = array(
                    'dict', 'file', 'ftp', 'ftps', 'gopher', 'http', 'https', 'imap', 'imaps', 'ldap', 'ldaps', 'pop3', 'pop3s', 'rtsp', 'scp', 'sftp', 'smb',
                    'smbs', 'smtp', 'smtps', 'telnet', 'tftp'
                );
                if (!is_string($Value)||!CheckPHPVersion('7.0.7')||!CheckcURLVersion('7.45.0')||!in_array($Value, $AcceptedValues))
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be a String, using cURL >= 7.45.0 and PHP >= 7.0.7';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                break;
            //CURLOPT(CURLOPT_CONNECT_TO, CURLOPTTYPE_SLISTPOINT, 243) = 10243
            case 10243: //CURLOPT_CONNECT_TO https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L2670
                if (!is_array($Value)||!CheckPHPVersion('7.0.7')||!CheckcURLVersion('7.49.0'))
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be an array, using cURL >= 7.49.0 and PHP >= 7.0.7';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                else
                {
                    //Format testing...
                    foreach ($Value as $cURLoptConnectToValue)
                    {
                        $Parts = explode(':', $cURLoptConnectToValue);
                        if (count($Parts) !== 4)
                        {
                            $BadcURLOptions[$Key] = $cURLoptConnectToValue.' is invalid in '.cURLOptionConstantToLiteral($Key).'scope. Accepted format is HOST:PORT:CONNECT-TO-HOST:CONNECT-TO-PORT';
                            ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                            break;
                        }
                        $TheHost = $Parts[0];
                        $ThePort = $Parts[1];
                        $ConnecToHost = $Parts[2];
                        $ConnectToPort = $Parts[3];
                        //Test HOST
                        if (!IsValidHost($TheHost, FALSE))
                        {
                            $BadcURLOptions[$Key] = $TheHost.' is not a valida hostname or IP in '.$cURLoptConnectToValue.', in '.cURLOptionConstantToLiteral($Key).'scope.';
                            ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                            break;
                        }
                        //Test PORT
                        if (!IsValidIANAPort($ThePort))
                        {
                            $BadcURLOptions[$Key] = $ThePort.' is not a valida hostname or IP in '.$cURLoptConnectToValue.', in '.cURLOptionConstantToLiteral($Key).'scope.';
                            ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                            break;
                        }
                        //Test DESIREDHOST
                        if (!IsValidHost($ConnecToHost, FALSE))
                        {
                            $BadcURLOptions[$Key] = $ConnecToHost.' is not a valida hostname or IP in '.$cURLoptConnectToValue.', in '.cURLOptionConstantToLiteral($Key).'scope.';
                            ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                            break;
                        }
                        //Test DESIREDPORT
                        if (!IsValidIANAPort($ConnectToPort))
                        {
                            $BadcURLOptions[$Key] = $ConnectToPort.' is not a valida hostname or IP in '.$cURLoptConnectToValue.', in '.cURLOptionConstantToLiteral($Key).'scope.';
                            ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                            break;
                        }
                    }
                } //End is an array and PHP>=7.0.7 and cURL>=7.49.0
                break;
            //CURLOPT(CURLOPT_TCP_FASTOPEN, CURLOPTTYPE_LONG, 244) = 244
            case 244: //CURLOPT_TCP_FASTOPEN https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L2675
                if (!is_bool($Value)||!CheckPHPVersion('7.0.7')||!CheckcURLVersion('7.49.0'))
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be a Boolean, using cURL >= 7.49.0 and PHP >= 7.0.7';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                break;
            //CURLOPT(CURLOPT_PRE_PROXY, CURLOPTTYPE_STRINGPOINT, 262) = 10262
            case 10262: //CURLOPT_PRE_PROXY https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L2729
                if (!is_string($Value)||!CheckPHPVersion('7.3.0')||!CheckcURLVersion('7.52.0'))
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be a String, using cURL >= 7.52.0 and PHP >= 7.3.0';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                else
                {
                    if ($Value !== "")
                    {
                        //Validate format [scheme]://[host]
                        $ValidProxySchemes = array('http', 'https', 'socks4', 'socks4a', 'socks5', 'socks5h', 'socks');
                        $Scheme = parse_url($Value, PHP_URL_SCHEME);
                        if (!in_array($Scheme, $ValidProxySchemes))
                        {
                            $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to one of: '.implode(',',$ValidProxySchemes);
                            ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                        }
                        $Host = parse_url($Value, PHP_URL_HOST);
                        $FailedVerification = FALSE;
                        if ($Host[0] != '[')
                        {
                            //Not an IPv6
                            if (!IsValidHostName($Host)||!IsValidIPv4($Host))
                            {
                                $FailedVerification = TRUE;
                            }
                        }
                        else
                        {
                            $TheIPv6 = substr($Host, 1, strlen($Host)-1);
                            if (!IsValidIPv6($TheIPv6))
                            {
                                $FailedVerification = TRUE;
                            }
                        }
                        if ($FailedVerification === TRUE)
                        {
                            $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be a valid RFC1123 hostname or IPv4. A numerical IPv6 address must be written within [brackets]';
                            ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                        }
                    } //End value is not empty
                }
                break;
            //CURLOPT(CURLOPT_ABSTRACT_UNIX_SOCKET, CURLOPTTYPE_STRINGPOINT, 264) = 10264
            case 10264: //CURLOPT_ABSTRACT_UNIX_SOCKET https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L2832
                if (!is_string($Value)||!CheckPHPVersion('7.3.0')||!CheckcURLVersion('7.53.0'))
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be a String, using cURL >= 7.53.0 and PHP >= 7.3.0';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                break;
            //CURLOPT(CURLOPT_SOCKS5_AUTH, CURLOPTTYPE_LONG, 267) = 267
            case 267: //CURLOPT_SOCKS5_AUTH https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L2921
                if (!is_int($Value)||!CheckPHPVersion('7.3.0')||!CheckcURLVersion('7.55.0'))
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be an Integer, using cURL >= 7.55.0 and PHP >= 7.3.0.';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                else
                {
                    //@see https://github.com/curl/curl/blob/master/include/curl/curl.h#L771
                    //CURLAUTH_NONE         0       0
                    //CURLAUTH_BASIC        (1<<0)  1
                    //CURLAUTH_GSSAPI       (1<<2)  4   Used for CURLOPT_SOCKS5_AUTH to stay terminologically correct
                    if ($Value<0||$Value>5)
                    {
                        $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires bitmask to combine CURLAUTH_NONE(0), CURLAUTH_BASIC(1) and CURLAUTH_GSSAPI(2)';
                        ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                    }
                }
                break;
            //CURLOPT(CURLOPT_HAPROXYPROTOCOL, CURLOPTTYPE_LONG, 274) = 274
            case 274: //CURLOPT_HAPROXYPROTOCOL https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L2986
                if (!is_bool($Value)||!CheckPHPVersion('7.3.0')||!CheckcURLVersion('7.60.0'))
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be a Boolean, using cURL >= 7.60.0 and PHP >= 7.0.7';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                break;
            ///CURLOPT(CURLOPT_DOH_URL, CURLOPTTYPE_STRINGPOINT, 279) = 10279
            case 10279: //CURLOPT_DOH_URL https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L3057
                cURLWarn($Key, TRUE, FALSE, FALSE);
                if (!is_string($Value)||!CheckcURLVersion('7.62.0'))
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be a String, using cURL >= 7.62.0';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                else
                {
                    //Validate format [scheme]://[host]:[port]/[path]
                    $ValidProxySchemes = array('http', 'https', 'socks4', 'socks4a', 'socks5', 'socks5h', 'socks');
                    $Scheme = parse_url($Value, PHP_URL_SCHEME);
                    if ($Scheme !== 'https')
                    {
                        $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires scheme to be https';
                        ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                    }
                }
                break;
            //CURLOPT(CURLOPT_PROTOCOLS_STR, CURLOPTTYPE_STRINGPOINT, 318) = 10318
            case 10318: //CURLOPT_PROTOCOLS_STR **UNDOCUMENTED AS OF PHP 8.1.0**
            //CURLOPT(CURLOPT_REDIR_PROTOCOLS_STR, CURLOPTTYPE_STRINGPOINT, 319) = 10319
            case 10319: //CURLOPT_REDIR_PROTOCOLS_STR **UNDOCUMENTED AS OF PHP 8.1.0**
                cURLWarn($Key, TRUE, FALSE, FALSE);
                $AcceptedValues = array(
                    'ALL', 'DICT', 'FILE', 'FTP', 'FTPS', 'GOPHER', 'GOPHERS', 'HTTP', 'HTTPS', 'IMAP', 'IMAPS',
                    'LDAP', 'LDAPS', 'POP3', 'POP3S', 'RTMP', 'RTMPE', 'RTMPS', 'RTMPT', 'RTMPTE', 'RTMPTS', 'RTSP',
                    'SCP', 'SFTP', 'SMB', 'SMBS', 'SMTP', 'SMTPS', 'TELNET', 'TFTP'
                );
                if (!is_string($Value)||!CheckcURLVersion('7.85.0')||!in_array($Value, $AcceptedValues))
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be a String and using cURL >= 7.85.0. Accepted values are: '.implode(',',$AcceptedValues);
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                break;
                //Authentication options
            //CURLOPT(CURLOPT_NETRC, CURLOPTTYPE_VALUES, 51) = 51
            case 51: //CURLOPT_NETRC https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L223
                //PHP function is a BOOL but CURL function is an INT
                //@see https://github.com/curl/curl/blob/master/include/curl/curl.h#L2246
                //enum CURL_NETRC_OPTION => Mutually exclusive
                //CURL_NETRC_IGNORED    0   The library will ignore the .netrc file. This is the default.
                //CURL_NETRC_OPTIONAL   1   The use of the .netrc file is optional, and information in the URL is to be preferred.
                //CURL_NETRC_REQUIRED   2   The use of the .netrc file is required, and any credential information present in the URL is ignored.
                //CURL_NETRC_LAST       3   Undocumented as of CURL 7.85. But valid
                //So I guess PHP sets CURL_NETRC_REQUIRED...
                if (!is_bool($Value))
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be a boolean value';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                break;
            //CURLOPT(CURLOPT_USERPWD, CURLOPTTYPE_STRINGPOINT, 5)
            case 10005: //CURLOPT_USERPWD https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L458
                $Separators = substr_count($Value, ":");
                if (!is_string($Value))
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be a string value';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                else
                {
                    //Test format [username]:[password]
                    if (strpos($Value, ':') == FALSE)
                    {
                        $BadcURLOptions[$Key] = $Value.' is invalid in '.cURLOptionConstantToLiteral($Key).'scope. Accepted format is [username]:[password] -without brackets-';
                        ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                        break;
                    }
                }
                break;
            //CURLOPT(CURLOPT_PROXYUSERPWD, CURLOPTTYPE_STRINGPOINT, 6) = 10006
            case 10006: //CURLOPT_PROXYUSERPWD https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L293
                //Both the name and the password will be URL decoded so colons in user/password will be encoded as %3A
                $Separators = substr_count($Value, ":");
                if (!is_string($Value)||$Separators !== 1)
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be a string value using format "[username]:[password]" whith both username and password URL-encoded';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                break;
            //CURLOPT(CURLOPT_NETRC_FILE, CURLOPTTYPE_STRINGPOINT, 118) = 10118
            case 10118: //CURLOPT_NETRC_FILE https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L1344
                if (!is_string($Value)||!CheckcURLVersion('7.10.9')||is_readable($Value))
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be a String pointing to a readable file, using cURL >= 7.10.9';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                break;
            //CURLOPT(CURLOPT_HTTPAUTH, CURLOPTTYPE_VALUES, 107) = 107
            case 107: //CURLOPT_HTTPAUTH https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L1197
                if (!is_int($Value)||!CheckcURLVersion('7.10.6'))
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be an Integer and using cURL >= 7.10.6';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                else
                {
                    //@see https://github.com/curl/curl/blob/master/include/curl/curl.h#L771
                    //CURLAUTH_NONE         0       0
                    //CURLAUTH_BASIC        (1<<0)  1
                    //CURLAUTH_DIGEST       (1<<1)  2
                    //CURLAUTH_NEGOTIATE    (1<<2)  4
                    //CURLAUTH_GSSNEGOTIATE (1<<2)  4   ***DEPRECATED SINCE CURLAUTH_NEGOTIATE***
                    //CURLAUTH_GSSAPI       (1<<2)  4   Used for CURLOPT_SOCKS5_AUTH to stay terminologically correct
                    //CURLAUTH_NTLM         (1<<3)  8
                    //CURLAUTH_DIGEST_IE    (1<<4)  16
                    //CURLAUTH_NTLM_WB      (1<<5)  32
                    //CURLAUTH_BEARER       (1<<6)  64
                    //CURLAUTH_AWS_SIGV4    (1<<7)  128
                    //CURLAUTH_ONLY         (1<<31) 2.147.483.648
                    //CURLAUTH_ANY          (~8)    -10
                    //CURLAUTH_ANYSAFE      (~17)   -18
                    if ($Value<-18||$Value>2147483648)
                    {
                        $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be an Integer within the range -18 to 2147483648';
                        ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                    }
                }
                break;
            //CURLOPT(CURLOPT_PROXYAUTH, CURLOPTTYPE_VALUES, 111) = 111
            case 111: //CURLOPT_PROXYAUTH https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L1250
                if (!is_int($Value)||!CheckcURLVersion('7.10.7'))
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be an Integer and using cURL >= 7.10.7';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                else
                {
                    //@see https://github.com/curl/curl/blob/master/include/curl/curl.h#L771
                    //CURLAUTH_NONE         0       0
                    //CURLAUTH_BASIC        (1<<0)  1
                    //CURLAUTH_DIGEST       (1<<1)  2
                    //CURLAUTH_NEGOTIATE    (1<<2)  4
                    //CURLAUTH_GSSNEGOTIATE (1<<2)  4   ***DEPRECATED SINCE CURLAUTH_NEGOTIATE***
                    //CURLAUTH_GSSAPI       (1<<2)  4   Used for CURLOPT_SOCKS5_AUTH to stay terminologically correct
                    //CURLAUTH_NTLM         (1<<3)  8
                    //CURLAUTH_DIGEST_IE    (1<<4)  16
                    //CURLAUTH_NTLM_WB      (1<<5)  32
                    //CURLAUTH_BEARER       (1<<6)  64
                    //CURLAUTH_AWS_SIGV4    (1<<7)  128
                    //CURLAUTH_ONLY         (1<<31) 2.147.483.648
                    //CURLAUTH_ANY          (~8)    -10
                    //CURLAUTH_ANYSAFE      (~17)   -18
                    if ($Value<-18||$Value>2147483648)
                    {
                        $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be an Integer within the range -18 to 2147483648';
                        ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                    }
                }
                break;
            //CURLOPT(CURLOPT_USERNAME, CURLOPTTYPE_STRINGPOINT, 173) = 10173
            case 10173: //CURLOPT_USERNAME https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L1821
            //CURLOPT(CURLOPT_PASSWORD, CURLOPTTYPE_STRINGPOINT, 174) = 10174
            case 10174: //CURLOPT_PASSWORD https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L1801
                if (!is_string($Value)||!CheckcURLVersion('7.19.1'))
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be a String, using cURL >= 7.19.1';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                break;
            //CURLOPT(CURLOPT_PROXYUSERNAME, CURLOPTTYPE_STRINGPOINT, 175) = 10175
            case 10175: //CURLOPT_PROXYUSERNAME https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L1816
            //CURLOPT(CURLOPT_PROXYPASSWORD, CURLOPTTYPE_STRINGPOINT, 176) = 10176
            case 10176: //CURLOPT_PROXYPASSWORD https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L1811
                cURLWarn($Key, TRUE, FALSE, FALSE);
                if (!is_string($Value)||!CheckcURLVersion('7.19.1'))
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be a String, using cURL >= 7.19.1';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                break;
            //CURLOPT(CURLOPT_TLSAUTH_USERNAME, CURLOPTTYPE_STRINGPOINT, 204) = 10204
            case 10204: //CURLOPT_TLSAUTH_USERNAME https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L2246
            // CURLOPT(CURLOPT_TLSAUTH_PASSWORD, CURLOPTTYPE_STRINGPOINT, 205) = 10205
            case 10205: //CURLOPT_TLSAUTH_PASSWORD https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L2236
                cURLWarn($Key, TRUE, FALSE, FALSE);
                if (!is_string($Value)||!CheckcURLVersion('7.21.4'))
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be a String, using cURL >= 7.21.4';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                break;
            //CURLOPT(CURLOPT_TLSAUTH_TYPE, CURLOPTTYPE_STRINGPOINT, 206) = 10206
            case 10206: //CURLOPT_TLSAUTH_TYPE https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L2241
                cURLWarn($Key, TRUE, FALSE, FALSE);
                if (!is_string($Value)||!CheckcURLVersion('7.21.4')||$Value !== 'SRP')
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be a String with value "SRP", and using cURL >= 7.21.4';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                break;
            //CURLOPT(CURLOPT_SASL_IR, CURLOPTTYPE_LONG, 218) = 218
            case 218: //CURLOPT_SASL_IR https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L2388
                if (!is_bool($Value)||!CheckPHPVersion('7.0.7')||!CheckcURLVersion('7.31.0'))
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be a Boolean, using cURL >= 7.31.0 and PHP >= 7.0.7';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                break;
            //CURLOPT(CURLOPT_XOAUTH2_BEARER, CURLOPTTYPE_STRINGPOINT, 220) = 10220
            case 10220: //CURLOPT_XOAUTH2_BEARER https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L2411
                if (!is_string($Value)||!CheckPHPVersion('7.0.7')||!CheckcURLVersion('7.33.0'))
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be a String, using cURL >= 7.33.0 and PHP >= 7.0.7';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                break;
            //CURLOPT(CURLOPT_LOGIN_OPTIONS, CURLOPTTYPE_STRINGPOINT, 224) = 10224
            case 10224: //CURLOPT_LOGIN_OPTIONS https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L2430
                //Basically for AUTH options
                //@see https://www.ietf.org/rfc/rfc2384.txt
                //@see https://www.ietf.org/rfc/rfc5092.txt
                if (!is_string($Value)||!CheckPHPVersion('7.0.7')||!CheckcURLVersion('7.34.0'))
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be a String, using cURL >= 7.34.0 and PHP >= 7.0.7';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                break;
            //CURLOPT(CURLOPT_PROXY_TLSAUTH_USERNAME, CURLOPTTYPE_STRINGPOINT, 251) = 10251
            case 10251: //CURLOPT_PROXY_TLSAUTH_USERNAME https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L2814
            //CURLOPT(CURLOPT_PROXY_TLSAUTH_PASSWORD, CURLOPTTYPE_STRINGPOINT, 252) = 10252
            case 10252: //CURLOPT_PROXY_TLSAUTH_PASSWORD https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L2804
                if (!is_string($Value)||!CheckPHPVersion('7.3.0')||!CheckcURLVersion('7.52.0'))
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be a String, using cURL >= 7.52.0 and PHP >= 7.3.0';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                break;
            //CURLOPT(CURLOPT_PROXY_TLSAUTH_TYPE, CURLOPTTYPE_STRINGPOINT, 253) = 10253
            case 10253: //CURLOPT_PROXY_TLSAUTH_TYPE https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L2809
                if (!is_string($Value)||!CheckPHPVersion('7.3.0')||!CheckcURLVersion('7.52.0')||$Value !== 'SRP')
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be a String with value "SRP", using cURL >= 7.52.0 and PHP >= 7.3.0';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                break;
            //CURLOPT(CURLOPT_DISALLOW_USERNAME_IN_URL, CURLOPTTYPE_LONG, 278) = 278
            case 278: //CURLOPT_DISALLOW_USERNAME_IN_URL https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L3039
                if (!is_bool($Value)||!CheckPHPVersion('7.3.0')||!CheckcURLVersion('7.61.0'))
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be a Boolean, using cURL >= 7.61.0 and PHP >= 7.3.0';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                break;
            //CURLOPT(CURLOPT_SASL_AUTHZID, CURLOPTTYPE_STRINGPOINT, 289) = 10289
            case 10289: //CURLOPT_SASL_AUTHZID https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L3129
                cURLWarn($Key, TRUE, FALSE, FALSE);
                if (!is_string($Value)||!CheckcURLVersion('7.66.0'))
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be a String, using cURL >= 7.66.0';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                break;
                //HTTP/HTTPS options
            //CURLOPT(CURLOPT_AUTOREFERER, CURLOPTTYPE_LONG, 58) = 58
            case 58: //CURLOPT_AUTOREFERER https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L13
            //CURLOPT(CURLOPT_FOLLOWLOCATION, CURLOPTTYPE_LONG, 52) = 52
            case 52: //CURLOPT_FOLLOWLOCATION https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L108
            //CURLOPT(CURLOPT_UNRESTRICTED_AUTH, CURLOPTTYPE_LONG, 105) = 105
            case 105: //CURLOPT_UNRESTRICTED_AUTH https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L438
            //CURLOPT(CURLOPT_POST, CURLOPTTYPE_LONG, 47) = 47
            case 47: //CURLOPT_POST https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L248
            //CURLOPT(CURLOPT_COOKIESESSION, CURLOPTTYPE_LONG, 96) = 96
            case 96: //CURLOPT_COOKIESESSION https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L58
            //CURLOPT(CURLOPT_HTTPGET, CURLOPTTYPE_LONG, 80) = 80
            case 80: //CURLOPT_HTTPGET https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L163
                if (!is_bool($Value))
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be a boolean value';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                break;
            //CURLOPT(CURLOPT_MAXREDIRS, CURLOPTTYPE_LONG, 68) = 68
            case 68: //CURLOPT_MAXREDIRS https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L218
                if (!is_int($Value))
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be an Integer value';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                break;
            //CURLOPT(CURLOPT_HTTP_VERSION, CURLOPTTYPE_VALUES, 84) = 84
            case 84: //CURLOPT_HTTP_VERSION https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L178
                //@see https://github.com/curl/curl/blob/master/include/curl/curl.h#L2207
                //Mutually exclusive
                //CURL_HTTP_VERSION_NONE                0   We do not care about what version the library uses. libcurl will use whatever it thinks fit.
                //CURL_HTTP_VERSION_1_0                 1   Enforce HTTP 1.0 requests.
                //CURL_HTTP_VERSION_1_1                 2   Enforce HTTP 1.1 requests.
                //CURL_HTTP_VERSION_2_0                 3   Attempt HTTP 2 requests. Fall back to HTTP 1.1 if HTTP 2 cannot be negotiated with the server. (7.33.0)
                //CURL_HTTP_VERSION_2                   3   Alias of CURL_HTTP_VERSION_2_0 to better reflect the actual protocol name (7.43.0)
                //CURL_HTTP_VERSION_2TLS                4   Attempt HTTP 2 over TLS (HTTPS) only. Fall back to HTTP 1.1 if HTTP 2 cannot be negotiated with the HTTPS server. (7.47.0)
                //CURL_HTTP_VERSION_2_PRIOR_KNOWLEDGE   5   Issue non-TLS HTTP requests using HTTP/2 without HTTP/1.1 Upgrade. It requires prior knowledge that the server supports HTTP/2 straight away. (7.49.0)
                //CURL_HTTP_VERSION_3                   30  (Explicit value) Use HTTP/3 directly to server given in the URL. (7.66.0)
                //CURL_HTTP_VERSION_LAST                31  *ILLEGAL* http version, never use
                if (!is_int($Value)||$Value<0||$Value>30)
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be an Integer value on the range 0-30';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                else
                {
                    if ($Value === 3&&!CheckcURLVersion('7.33.0'))
                    {
                        $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' with CURL_HTTP_VERSION_2_0 requires cURL to be at least 7.33.0. CURL_HTTP_VERSION_2 requires at least cURL 7.43.0';
                        ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                    }
                    if ($Value === 4&&!CheckcURLVersion('7.47.0'))
                    {
                        $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' with CURL_HTTP_VERSION_2TLS requires cURL to be at least 7.47.0';
                        ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                    }
                    if ($Value === 5&&!CheckcURLVersion('7.49.0'))
                    {
                        $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' with CURL_HTTP_VERSION_2_PRIOR_KNOWLEDGE requires cURL to be at least 7.49.0';
                        ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                    }
                    if ($Value === 30&&!CheckcURLVersion('7.66.0'))
                    {
                        $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' with CURL_HTTP_VERSION_3 requires cURL to be at least 7.66.0';
                        ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                    }
                }
                break;
            //CURLOPT(CURLOPT_REFERER, CURLOPTTYPE_STRINGPOINT, 16) = 10016
            case 10016: //CURLOPT_REFERER https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L328
            //CURLOPT(CURLOPT_USERAGENT, CURLOPTTYPE_STRINGPOINT, 18) = 10018
            case 10018: //CURLOPT_USERAGENT https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L453
            //CURLOPT(CURLOPT_COOKIE, CURLOPTTYPE_STRINGPOINT, 22) = 10022
            case 10022: //CURLOPT_COOKIE https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L43
                //referer, useragent, cookie... pending format validation
                if (!is_string($Value))
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be a String value';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                break;
            //CURLOPT(CURLOPT_COOKIEFILE, CURLOPTTYPE_STRINGPOINT, 31) = 10031
            case 10031: //CURLOPT_COOKIEFILE https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L48
                if (!is_string($Value))
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be a String path to a readable file, empty string or minus sign';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                else
                {
                    if ($Value !== ''||$Value !== '-')
                    {
                        if (!is_readable($Value))
                        {
                            $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be a String path to a readable file, empty string or minus sign';
                            ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                        }
                    }
                }
                break;
            //CURLOPT(CURLOPT_COOKIEJAR, CURLOPTTYPE_STRINGPOINT, 82) = 10082
            case 10082: //CURLOPT_COOKIEJAR https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L53
                if (!is_string($Value)||!is_writable($Value))
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be a String path to a readable file';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                break;
            //CURLOPT(CURLOPT_COOKIELIST, CURLOPTTYPE_STRINGPOINT, 135) = 10135
            case 10135: //CURLOPT_COOKIELIST https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L1418
                //Pending cookie format validation
                if (!is_string($Value))
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be a String value';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                else
                {
                    //Command validation
                    if ($Value === "ALL"&&!CheckcURLVersion('7.14.1'))
                    {
                        $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' with '.$Value.' requires cURL to be at least 7.14.1';
                        ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                    }
                    if ($Value === "SESS"&&!CheckcURLVersion('7.15.4'))
                    {
                        $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' with '.$Value.' requires cURL to be at least 7.15.4';
                        ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                    }
                    if ($Value === "FLUSH"&&!CheckcURLVersion('7.17.1'))
                    {
                        $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' with '.$Value.' requires cURL to be at least 7.17.1';
                        ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                    }
                    if ($Value === "RELOAD"&&!CheckcURLVersion('7.39.0'))
                    {
                        $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' with '.$Value.' requires cURL to be at least 7.39.0';
                        ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                    }
                }
                break;
            //CURLOPT(CURLOPT_POSTFIELDS, CURLOPTTYPE_OBJECTPOINT, 15) = 10015
            case 10015: //CURLOPT_POSTFIELDS https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L253
                if (!is_string($Value)&&!is_array($Value))
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be an String or Array value';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                break;
            //CURLOPT(CURLOPT_HTTPHEADER, CURLOPTTYPE_SLISTPOINT, 23) = 10023
            case 10023: //CURLOPT_HTTPHEADER https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L168
                //Pending header validation
                if (!is_array($Value))
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be an Array value';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                break;
            //CURLOPT(CURLOPT_PUT, CURLOPTTYPE_LONG, 54) = 54
            case 54: //CURLOPT_PUT https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L298
                cURLWarn($Key, FALSE, FALSE, '7.12.1#CURLOPT_UPLOAD');
                if (!is_bool($Value))
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be a boolean value';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                break;
            //CURLOPT(CURLOPT_POSTFIELDSIZE, CURLOPTTYPE_LONG, 60) = 60
            case 60: //CURLOPT_POSTFIELDSIZE **UNDOCUMENTED AS OF PHP 8.1.0**
                cURLWarn($Key, TRUE, FALSE, FALSE);
                if (!is_int($Value)||$Value<-1||$Value>2147483648)
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be an Integer representing less than 2 GiB (-1 < Value < 2147483648';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                break;
            //CURLOPT(CURLOPT_POSTFIELDSIZE_LARGE, CURLOPTTYPE_OFF_T, 120) = 30120
            case 30120: //CURLOPT_POSTFIELDSIZE_LARGE **UNDOCUMENTED AS OF PHP 8.1.0**
                cURLWarn($Key, TRUE, FALSE, FALSE);
                if (!is_int($Value)||$Value<-1)
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be an Integer valued at least -1 or more';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                break;
            //CURLOPT(CURLOPT_HTTPPOST, CURLOPTTYPE_OBJECTPOINT, 24) = 10024
            case 10024: //CURLOPT_HTTPPOST **UNDOCUMENTED AS OF PHP 8.1.0**
                cURLWarn($Key, TRUE, FALSE, '7.56.0#CURLOPT_MIMEPOST');
                if (!is_array($Value))
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be an Array value';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                break;
            //CURLOPT(CURLOPT_HTTP200ALIASES, CURLOPTTYPE_SLISTPOINT, 104) = 10104
            case 10104: //CURLOPT_HTTP200ALIASES https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L158
                if (!is_array($Value)||!CheckcURLVersion('7.10.0'))
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be an Array and using cURL >= 7.10.0';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                break;
            //CURLOPT(CURLOPT_IGNORE_CONTENT_LENGTH, CURLOPTTYPE_LONG, 136) = 136
            case 136: //CURLOPT_IGNORE_CONTENT_LENGTH https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L1423
                cURLWarn($Key, TRUE, FALSE, FALSE);
                if (!is_bool($Value)||!CheckcURLVersion('7.14.1'))
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be a Boolean and using cURL >= 7.14.1';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                break;
            //CURLOPT(CURLOPT_HTTP_CONTENT_DECODING, CURLOPTTYPE_LONG, 158) = 158
            case 158: //CURLOPT_HTTP_CONTENT_DECODING https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L1571
                if (!is_bool($Value)||!CheckcURLVersion('7.16.2'))
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be a Boolean and using cURL >= 7.16.2';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                break;
            //CURLOPT(CURLOPT_HTTP_TRANSFER_DECODING, CURLOPTTYPE_LONG, 157) = 157
            case 157: //CURLOPT_HTTP_TRANSFER_DECODING https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L1576
                cURLWarn($Key, TRUE, FALSE, FALSE);
                if (!is_bool($Value)||!CheckcURLVersion('7.16.2'))
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be a Boolean and using cURL >= 7.16.2';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                break;
            //CURLOPT(CURLOPT_COPYPOSTFIELDS, CURLOPTTYPE_OBJECTPOINT, 165) = 10165
            case 10165: //CURLOPT_COPYPOSTFIELDS **UNDOCUMENTED AS OF PHP 8.1.0**
                if (!is_string($Value)||!CheckcURLVersion('7.17.1'))
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be a String and using cURL >= 7.17.1';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                break;
            //CURLOPT(CURLOPT_POSTREDIR, CURLOPTTYPE_VALUES, 161) = 161
            case 161: //CURLOPT_POSTREDIR https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L1806
                if (!is_int($Value)||!CheckcURLVersion('7.19.1'))
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be an Integer and using cURL >= 7.19.1';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                else
                {
                    //@see https://github.com/curl/curl/blob/master/include/curl/curl.h#L2288
                    //CURL_REDIR_POST_301, CURL_REDIR_POST_302 and CURL_REDIR_POST_303 can be bitwise ORed so that
                    //CURL_REDIR_POST_301 | CURL_REDIR_POST_302 | CURL_REDIR_POST_303 == CURL_REDIR_POST_ALL
                    //CURL_REDIR_GET_ALL    0
                    //CURL_REDIR_POST_301   1
                    //CURL_REDIR_POST_302   2
                    //CURL_REDIR_POST_303   4
                    //CURL_REDIR_POST_ALL   (CURL_REDIR_POST_301|CURL_REDIR_POST_302|CURL_REDIR_POST_303) -that's 7-
                    if ($Value<0||$Value>7)
                    {
                        $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be an Integer within the range 0 to 7';
                        ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                    }
                }
                break;
            //#define CURLOPT_ENCODING CURLOPT_ACCEPT_ENCODING
            //CURLOPT(CURLOPT_ACCEPT_ENCODING, CURLOPTTYPE_STRINGPOINT, 102) = 10102
            case 10102: //CURLOPT_ACCEPT_ENCODING https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L2263
                //libcurl changed CURLOPT_ENCODING to CURLOPT_ACCEPT_ENCODING, see https://github.com/curl/curl/blob/master/include/curl/curl.h#L634
                //But PHP hasn't yet adapted and expects CURLOPT_ENCODING https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L88
                //No problem as both constants share the same value
                $AvailableEncodings = array('identity', 'deflate', 'gzip', 'br', 'zstd');
                if (!is_string($Value)||!CheckcURLVersion('7.21.6'))
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be a String, using cURL >= 7.21.6';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                else
                {
                    //Valid values are NULL, empty string and a comma-delimited list of encodings
                    if (!empty($Value))
                    {
                        $UsedEncodings = explode(',',$Value);
                        foreach ($UsedEncodings as $SingleEcoding)
                        {
                            if (!in_array($SingleEcoding, $AvailableEncodings))
                            {
                                $BadcURLOptions[$Key] = $SingleEcoding.' is not as a valid encoding ('.implode(',',$AvailableEncodings).')';
                                ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                            }
                            if ($SingleEcoding === 'br'&&!CheckcURLVersion('7.57.0'))
                            {
                                $BadcURLOptions[$Key] = 'br (brotli) encoding requires using cURL >= 7.57.0';
                                ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                            }
                            if ($SingleEcoding === 'zstd'&&!CheckcURLVersion('7.72.0'))
                            {
                                $BadcURLOptions[$Key] = 'zstd encoding requires using cURL >= 7.72.0';
                                ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                            }
                        }
                    } //End option is not empty
                }
                break;
            //CURLOPT(CURLOPT_TRANSFER_ENCODING, CURLOPTTYPE_LONG, 207) = 207
            case 207: //CURLOPT_TRANSFER_ENCODING https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L2268
                cURLWarn($Key, TRUE, FALSE, FALSE);
                if (!is_bool($Value)||!CheckcURLVersion('7.21.6'))
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be a Boolean, using cURL >= 7.21.6';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                break;
            //CURLOPT(CURLOPT_HEADEROPT, CURLOPTTYPE_VALUES, 229) = 229
            case 229: //CURLOPT_HEADEROPT https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L2482
                //@see https://github.com/curl/curl/blob/master/include/curl/curl.h#L973
                //Mutually exclusive
                //CURLHEADER_UNIFIED    0       0   The headers specified in CURLOPT_HTTPHEADER will be used in requests both to servers and proxies
                //CURLHEADER_SEPARATE   (1<<0)  1   Makes CURLOPT_HTTPHEADER headers only get sent to a server and not to a proxy.
                if (!is_int($Value)||!CheckPHPVersion('7.0.7')||!CheckcURLVersion('7.31.6')||$Value<0||$Value>1 )
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be an Integer on the range [0-1], using cURL >= 7.31.6 and PHP >= 7.0.7';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                break;
            //CURLOPT(CURLOPT_EXPECT_100_TIMEOUT_MS, CURLOPTTYPE_LONG, 227) = 227
            case 227: //CURLOPT_EXPECT_100_TIMEOUT_MS https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L2454
                if (!is_int($Value)||!CheckPHPVersion('7.0.7')||!CheckcURLVersion('7.36.0'))
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be an Integer, using cURL >= 7.36.0 and PHP >= 7.0.7';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                break;
            //CURLOPT(CURLOPT_PROXYHEADER, CURLOPTTYPE_SLISTPOINT, 228) = 10228
            case 10228: //CURLOPT_PROXYHEADER https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L2487
                if (!is_array($Value)||!CheckPHPVersion('7.0.7')||!CheckcURLVersion('7.37.0'))
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be an Array, using cURL >= 7.37.0 and PHP >= 7.0.7';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                break;
            //CURLOPT(CURLOPT_PIPEWAIT, CURLOPTTYPE_LONG, 237) = 237
            case 237: //CURLOPT_PIPEWAIT https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L2571
                if (!is_bool($Value)||!CheckPHPVersion('7.0.7')||!CheckcURLVersion('7.43.0'))
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be a Boolean, using cURL >= 7.43.0 and PHP >= 7.0.7';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                break;
            //CURLOPT(CURLOPT_STREAM_WEIGHT, CURLOPTTYPE_LONG, 239) = 239
            case 239: //CURLOPT_STREAM_WEIGHT https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L2621
                if (!is_int($Value)||!CheckPHPVersion('7.0.7')||!CheckcURLVersion('7.46.0')||$Value<1||$Value>256)
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be an Integer, using cURL >= 7.46.0 and PHP >= 7.0.7';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                break;
            //CURLOPT(CURLOPT_STREAM_DEPENDS, CURLOPTTYPE_OBJECTPOINT, 240) = 10240
            case 10240: //CURLOPT_STREAM_DEPENDS **UNDOCUMENTED AS OF PHP 8.1.0**
            //CURLOPT(CURLOPT_STREAM_DEPENDS_E, CURLOPTTYPE_OBJECTPOINT, 241) = 10241
            case 10241: //CURLOPT_STREAM_DEPENDS_E **UNDOCUMENTED AS OF PHP 8.1.0**
                //Class name from https://phpbackend.com/blog/post/php-8-0-curlHandle-object-in-curl-functions
                if (!is_object($Value)||(!is_a($Value,'CurlHandle'))||!CheckcURLVersion('7.46.0'))
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be a CurlHandle object';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                break;
            //CURLOPT(CURLOPT_REQUEST_TARGET, CURLOPTTYPE_STRINGPOINT, 266) = 10266
            case 10266: //CURLOPT_REQUEST_TARGET https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L2916
                if (!is_string($Value)||!CheckcURLVersion('7.55.0'))
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be a String, using cURL >= 7.55.0';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                break;
            //CURLOPT(CURLOPT_HTTP09_ALLOWED, CURLOPTTYPE_LONG, 285) = 285
            case 285: //CURLOPT_HTTP09_ALLOWED https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L3075
                if (!is_bool($Value)||!CheckcURLVersion('7.64.0'))
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be a Boolean, using cURL >= 7.64.0';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                break;
            //CURLOPT(CURLOPT_TRAILERDATA, CURLOPTTYPE_CBPOINT, 284) = 10284
            case 10284: //CURLOPT_TRAILERDATA **UNDOCUMENTED AS OF PHP 8.1.0**
                cURLWarn($Key, TRUE, FALSE, FALSE);
                if (!is_string($Value)||!CheckcURLVersion('7.64.0'))
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be a String, using cURL >= 7.64.0';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                break;
            //CURLOPT(CURLOPT_TRAILERFUNCTION, CURLOPTTYPE_FUNCTIONPOINT, 283) = 20283
            case 20283: //CURLOPT_TRAILERFUNCTION **UNDOCUMENTED AS OF PHP 8.1.0**
                cURLWarn($Key, TRUE, FALSE, FALSE);
                if (!is_array($Value)||!CheckcURLVersion('7.64.0'))
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be an array(cURLHandle, string) value';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                else
                {
                    if (!is_object($Value[0])||(!is_a($Value[0],'CurlHandle'))||(!is_string($Value[1])))
                    {
                        $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be an array(cURLHandle, string) value';
                        ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                    }
                }
                break;
            //CURLOPT(CURLOPT_ALTSVC_CTRL, CURLOPTTYPE_LONG, 286) = 286
            case 286: //CURLOPT_ALTSVC_CTRL https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L3108
                cURLWarn($Key, TRUE, FALSE, FALSE);
                if (!is_int($Value)||!CheckcURLVersion('7.64.1'))
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be a string path to a writeable file, using cURL >= 7.64.1';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                else
                {
                    //@see https://github.com/curl/curl/blob/master/include/curl/curl.h#L977
                    //CURLALTSVC_READONLYFILE   (1<<2)  4   Do not write the alt-svc cache back to the file specified with CURLOPT_ALTSVC even if it gets updated
                    //CURLALTSVC_H1             (1<<3)  8   Accept alternative services offered over HTTP/1.1.
                    //CURLALTSVC_H2             (1<<4)  16  Accept alternative services offered over HTTP/2. (*)
                    //CURLALTSVC_H3             (1<<5)  32  Accept alternative services offered over HTTP/3. (*)
                    //(*) HTTP/2 and HTTP/3 bits are only set if libcurl is built with support for those versions.
                    ////32||16||8||4 = 60
                    if ($Value<4||$Value>60)
                    {
                        $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be an Integer within the range 4 to 60';
                        ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                    }
                }
                break;
            //CURLOPT(CURLOPT_ALTSVC, CURLOPTTYPE_STRINGPOINT, 287) = 10287
            case 10287: //CURLOPT_ALTSVC https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L3103
                cURLWarn($Key, TRUE, FALSE, FALSE);
                if (!is_string($Value)||!CheckcURLVersion('7.64.1')||!is_writable($Value))
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be a string path to a writeable file, using cURL >= 7.64.1';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                break;
            //CURLOPT(CURLOPT_HSTS_CTRL, CURLOPTTYPE_LONG, 299) = 299
            case 299: //CURLOPT_HSTS_CTRL https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L3445
                cURLWarn($Key, TRUE, FALSE, FALSE);
                if (!is_int($Value)||!CheckcURLVersion('7.74.0'))
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be a string path to a writeable file, using cURL >= 7.74.0';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                else
                {
                    //@see https://github.com/curl/curl/blob/master/include/curl/curl.h#L1010
                    //CURLHSTS_ENABLE       (1<<0)  1   Enable the in-memory HSTS cache for this handle.
                    //CURLHSTS_READONLYFILE (1<<1)  2   Make the HSTS file (if specified) read-only
                    //1||2 = 3
                    if ($Value<1||$Value>3)
                    {
                        $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be an Integer within the range 1 to 3';
                        ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                    }
                }
                break;
            //CURLOPT(CURLOPT_HSTSREADDATA, CURLOPTTYPE_CBPOINT, 302) = 10302
            case 10302: //CURLOPT_HSTSREADDATA **UNDOCUMENTED AS OF PHP 8.1.0**
            //CURLOPT(CURLOPT_HSTSWRITEDATA, CURLOPTTYPE_CBPOINT, 304) = 10304
            case 10304: //CURLOPT_HSTSWRITEDATA **UNDOCUMENTED AS OF PHP 8.1.0**
                cURLWarn($Key, TRUE, FALSE, FALSE);
                if (!is_string($Value)||!CheckcURLVersion('7.74.0'))
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be a String, using cURL >= 7.74.0';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                break;
            //CURLOPT(CURLOPT_HSTS, CURLOPTTYPE_STRINGPOINT, 300) = 10300
            case 10300: //CURLOPT_HSTS https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L3440
                cURLWarn($Key, TRUE, FALSE, FALSE);
                if (!is_string($Value)||!CheckcURLVersion('7.74.0')||!is_writable($Value))
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be a string path to a writeable file, using cURL >= 7.74.0';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                break;
            //CURLOPT(CURLOPT_HSTSREADFUNCTION, CURLOPTTYPE_FUNCTIONPOINT, 301) = 20301
            case 20301: //CURLOPT_HSTSREADFUNCTION **UNDOCUMENTED AS OF PHP 8.1.0**
            //CURLOPT(CURLOPT_HSTSWRITEFUNCTION, CURLOPTTYPE_FUNCTIONPOINT, 303) = 20303
            case 20303: //CURLOPT_HSTSWRITEFUNCTION **UNDOCUMENTED AS OF PHP 8.1.0**
                cURLWarn($Key, TRUE, FALSE, FALSE);
                if (!is_array($Value)||!CheckcURLVersion('7.74.0'))
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be an array(cURLHandle, string) value, using cURL >= 7.74.0';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                else
                {
                    if (!is_object($Value[0])||(!is_a($Value[0],'CurlHandle'))||(!is_string($Value[1])))
                    {
                        $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be an array(cURLHandle, string) value';
                        ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                    }
                }
                break;
                //SMTP Options
            //CURLOPT(CURLOPT_MAIL_FROM, CURLOPTTYPE_STRINGPOINT, 186) = 10186
            case 10186: //CURLOPT_MAIL_FROM https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L2013
                cURLWarn($Key, TRUE, FALSE, FALSE);
                if (!is_string($Value)||!CheckcURLVersion('7.20.0'))
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be a String, using cURL >= 7.20.0';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                break;
            //CURLOPT(CURLOPT_MAIL_RCPT, CURLOPTTYPE_SLISTPOINT, 187) = 10187
            case 10187: //CURLOPT_MAIL_RCPT https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L2018
                cURLWarn($Key, TRUE, FALSE, FALSE);
                if (!is_array($Value)||!CheckcURLVersion('7.20.0'))
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be an Array, using cURL >= 7.20.0';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                break;
            //CURLOPT(CURLOPT_MAIL_AUTH, CURLOPTTYPE_STRINGPOINT, 217) = 10217
            case 10217: //CURLOPT_MAIL_AUTH https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L2314
                cURLWarn($Key, TRUE, FALSE, FALSE);
                if (!is_string($Value)||!CheckcURLVersion('7.25.0'))
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be a String, using cURL >= 7.25.0';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                break;
            //CURLOPT(CURLOPT_MAIL_RCPT_ALLLOWFAILS, CURLOPTTYPE_LONG, 290) = 290
            case 290: //CURLOPT_MAIL_RCPT_ALLLOWFAILS https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L3163
                cURLWarn($Key, TRUE, FALSE, FALSE);
                if (!is_bool($Value)||!CheckcURLVersion('7.69.0'))
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be a Boolean, using cURL >= 7.69.0';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                break;
                //TFTP Options
            //CURLOPT(CURLOPT_TFTP_BLKSIZE, CURLOPTTYPE_LONG, 178) = 178
            case 178: //CURLOPT_TFTP_BLKSIZE https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L1881
                cURLWarn($Key, TRUE, FALSE, FALSE);
                if (!is_int($Value)||!CheckcURLVersion('7.19.4')||$Value<8||$Value>65464)
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be a integer on the range [8-65464], using cURL >= 7.19.4';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                break;
            //CURLOPT(CURLOPT_TFTP_NO_OPTIONS, CURLOPTTYPE_LONG, 242) = 242
            case 242: //CURLOPT_TFTP_NO_OPTIONS https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L2657
                if (!is_bool($Value)||!CheckPHPVersion('7.0.7')||!CheckcURLVersion('7.48.0'))
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be a Boolean, using cURL >= 7.48.0 and PHP >= 7.0.7';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                break;
                //FTP Options
            //CURLOPT(CURLOPT_FTP_USE_EPSV, CURLOPTTYPE_LONG, 85) = 85
            case 85: //CURLOPT_FTP_USE_EPSV https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L143
                if (!is_bool($Value))
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be a boolean value';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                break;
            //#define CURLOPT_FTPAPPEND CURLOPT_APPEND
            //CURLOPT_FTPAPPEND https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L123
            //CURLOPT(CURLOPT_APPEND, CURLOPTTYPE_LONG, 50) = 50
            case 50: //CURLOPT_APPEND https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L1612
                if (!is_bool($Value))
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be a boolean value';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                break;
            //CURLOPT(CURLOPT_FTPPORT, CURLOPTTYPE_STRINGPOINT, 17) = 10017
            case 10017: //CURLOPT_FTPPORT https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L133
                if (!is_string($Value))
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be a String value';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                break;
            //CURLOPT(CURLOPT_QUOTE, CURLOPTTYPE_SLISTPOINT, 28) = 10028
            case 10028: //CURLOPT_QUOTE https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L303
            //CURLOPT(CURLOPT_POSTQUOTE, CURLOPTTYPE_SLISTPOINT, 39) = 10039
            case 10039: //CURLOPT_POSTQUOTE https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L258
                if (!is_array($Value))
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be an Array value';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                break;
            //CURLOPT(CURLOPT_PREQUOTE, CURLOPTTYPE_SLISTPOINT, 93) = 10093
            case 10093: //CURLOPT_PREQUOTE https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L263
                cURLWarn($Key, TRUE, FALSE, FALSE);
                if (!is_array($Value))
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be an Array value';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                break;
            //CURLOPT(CURLOPT_FTP_USE_EPRT, CURLOPTTYPE_LONG, 106) = 106
            case 106: //CURLOPT_FTP_USE_EPRT https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L106
                if (!is_bool($Value)||!CheckcURLVersion('7.10.5'))
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be a Boolean, using cURL >= 7.10.5';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                break;
            //CURLOPT(CURLOPT_FTP_CREATE_MISSING_DIRS, CURLOPTTYPE_LONG, 110) = 110
            case 110: //CURLOPT_FTP_CREATE_MISSING_DIRS https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L110
                if (!is_bool($Value)||!CheckcURLVersion('7.10.7'))
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be a Boolean, using cURL >= 7.10.7';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                break;
            //CURLOPT(CURLOPT_FTPSSLAUTH, CURLOPTTYPE_VALUES, 129) = 129
            case 129: //CURLOPT_FTPSSLAUTH https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L1380
                if (!is_int($Value)||!CheckcURLVersion('7.12.0'))
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be a Integer, using cURL >= 7.12.0';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                break;
            //CURLOPT(CURLOPT_FTP_ACCOUNT, CURLOPTTYPE_STRINGPOINT, 134) = 10134
            case 10134: //CURLOPT_FTP_ACCOUNT https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L1387
                if (!is_string($Value)||!CheckcURLVersion('7.13.0'))
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be a String, using cURL >= 7.13.0';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                break;
            //CURLOPT(CURLOPT_FTP_SKIP_PASV_IP, CURLOPTTYPE_LONG, 137) = 137
            case 137: //CURLOPT_FTP_SKIP_PASV_IP https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L1430
                cURLWarn($Key, TRUE, FALSE, FALSE);
                if (!is_bool($Value)||!CheckcURLVersion('7.14.2'))
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be a Boolean, using cURL >= 7.14.2';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                break;
            //CURLOPT(CURLOPT_FTP_FILEMETHOD, CURLOPTTYPE_VALUES, 138) = 138
            case 138: //CURLOPT_FTP_FILEMETHOD https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L1437
                //@see https://github.com/curl/curl/blob/master/include/curl/curl.h#L964
                //Mutually exclusive
                //CURLFTPMETHOD_DEFAULT     0   Let libcurl pick
                //CURLFTPMETHOD_MULTICWD    1   Single CWD operation for each path part
                //CURLFTPMETHOD_NOCWD       2   No CWD at all
                //CURLFTPMETHOD_SINGLECWD   3   One CWD to full dir, then work on file
                //CURLFTPMETHOD_LAST        4   Not an option, never use
                if (!is_int($Value)||!CheckcURLVersion('7.15.1')||$Value<0||$Value>3)
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be a Integer on the range [0-3], using cURL >= 7.15.1';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                break;
            //CURLOPT(CURLOPT_FTP_ALTERNATIVE_TO_USER, CURLOPTTYPE_STRINGPOINT, 147) = 10147
            case 10147: //CURLOPT_FTP_ALTERNATIVE_TO_USER https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L1490
                if (!is_string($Value)||!CheckcURLVersion('7.15.5'))
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be a String, using cURL >= 7.15.5';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                break;
            //CURLOPT(CURLOPT_FTP_SSL_CCC, CURLOPTTYPE_LONG, 154) = 154
            case 154: //CURLOPT_FTP_SSL_CCC https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L1529
                //@see https://github.com/curl/curl/blob/master/include/curl/curl.h#L937
                //Mutually exclusive
                //CURLFTPSSL_CCC_NONE       0   Do not send CCC
                //CURLFTPSSL_CCC_PASSIVE    1   Let the server initiate the shutdown
                //CURLFTPSSL_CCC_ACTIVE     2   Initiate the shutdown
                //CURLFTPSSL_CCC_LAST       3   Not an option, never use
                cURLWarn($Key, TRUE, FALSE, FALSE);
                if (!is_int($Value)||!CheckcURLVersion('7.16.1')||$Value<0||$Value>2)
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be a Integer on the range [0-2], using cURL >= 7.16.1';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                break;
            //CURLOPT(CURLOPT_FTP_USE_PRET, CURLOPTTYPE_LONG, 188) = 188
            case 188: //CURLOPT_FTP_USE_PRET https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L2008
                cURLWarn($Key, TRUE, FALSE, FALSE);
                if (!is_bool($Value)||!CheckcURLVersion('7.20.0'))
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be a Boolean, using cURL >= 7.20.0';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                break;
            //#define CURLOPT_FTP_RESPONSE_TIMEOUT CURLOPT_SERVER_RESPONSE_TIMEOUT
            //CURLOPT_FTP_RESPONSE_TIMEOUT https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L1282
            //CURLOPT(CURLOPT_SERVER_RESPONSE_TIMEOUT, CURLOPTTYPE_LONG, 112) = 112
            case 112: //CURLOPT_SERVER_RESPONSE_TIMEOUT
                cURLWarn($Key, TRUE, FALSE, FALSE);
                if (!is_int($Value)||!CheckcURLVersion('7.20.0'))
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be a Integer, using cURL >= 7.20.0';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                break;
                //RTSP Options
            //CURLOPT(CURLOPT_RTSP_REQUEST, CURLOPTTYPE_VALUES, 189) = 189
            case 189: //CURLOPT_RTSP_REQUEST https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L2028
                //@see https://github.com/curl/curl/blob/master/include/curl/curl.h#L2276
                //Mutually exclusive
                //CURL_RTSPREQ_NONE             0   Does nothing, I guess
                //CURL_RTSPREQ_OPTIONS          1   Used to retrieve the available methods of the server
                //CURL_RTSPREQ_DESCRIBE         2   Used to get the low level description of a stream
                //CURL_RTSPREQ_ANNOUNCE         3   When sent by a client, this method changes the description of the session.
                //CURL_RTSPREQ_SETUP            4   Setup is used to initialize the transport layer for the session.
                //CURL_RTSPREQ_PLAY             5   Send a Play command to the server.
                //CURL_RTSPREQ_PAUSE            6   Send a Pause command to the server.
                //CURL_RTSPREQ_TEARDOWN         7   This command terminates an RTSP session.
                //CURL_RTSPREQ_GET_PARAMETER    8   Retrieve a parameter from the server.
                //CURL_RTSPREQ_SET_PARAMETER    9   Set a parameter on the server.
                //CURL_RTSPREQ_RECORD           10  Used to tell the server to record a session.
                //CURL_RTSPREQ_RECEIVE          11  This is a special request because it does not send any data to the server. The application may call this function in order to receive interleaved RTP data.
                //CURL_RTSPREQ_LAST             12  Not an option, never use
                cURLWarn($Key, TRUE, FALSE, FALSE);
                if (!is_int($Value)||!CheckcURLVersion('7.20.0')||$Value<0||$Value>11)
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be a Integer on the range [0-11], using cURL >= 7.20.0';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                break;
            //CURLOPT(CURLOPT_RTSP_CLIENT_CSEQ, CURLOPTTYPE_LONG, 193) = 193
            case 193: //CURLOPT_RTSP_CLIENT_CSEQ https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L2023
                cURLWarn($Key, TRUE, FALSE, FALSE);
                if (!is_int($Value)||!CheckcURLVersion('7.20.0'))
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be a Integer, using cURL >= 7.20.0';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                break;
            //CURLOPT(CURLOPT_RTSP_SERVER_CSEQ, CURLOPTTYPE_LONG, 194) = 194
            case 194: //CURLOPT_RTSP_SERVER_CSEQ https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L2033
                //As of cURL 7.85 the feature is unimplemented, see https://curl.se/libcurl/c/CURLOPT_RTSP_SERVER_CSEQ.html
                $BadcURLOptions[$Key] = 'As of cURL 7.85 '.cURLOptionConstantToLiteral($Key).' is unimplemented. See https://curl.se/libcurl/c/CURLOPT_RTSP_SERVER_CSEQ.html';
                ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                break;
            //CURLOPT(CURLOPT_RTSP_SESSION_ID, CURLOPTTYPE_STRINGPOINT, 190) = 10190
            case 10190: //CURLOPT_RTSP_SESSION_ID https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L2038
            //CURLOPT(CURLOPT_RTSP_STREAM_URI, CURLOPTTYPE_STRINGPOINT, 191) = 10191
            case 10191: //CURLOPT_RTSP_STREAM_URI https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L2043
            //CURLOPT(CURLOPT_RTSP_TRANSPORT, CURLOPTTYPE_STRINGPOINT, 192) = 10192
            case 10192: //CURLOPT_RTSP_TRANSPORT https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L2048
                //CURLOPT_RTSP_STREAM_URI , pending URI format validation
                cURLWarn($Key, TRUE, FALSE, FALSE);
                if (!is_string($Value)||!CheckcURLVersion('7.20.0'))
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be a String, using cURL >= 7.20.0';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                break;
            //CURLOPT(CURLOPT_AWS_SIGV4, CURLOPTTYPE_STRINGPOINT, 305) = 10305
            case 10305: //CURLOPT_AWS_SIGV4 https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L3463
                //Pending parameter format validation
                cURLWarn($Key, TRUE, FALSE, FALSE);
                if (!is_string($Value)||!CheckcURLVersion('7.75.0'))
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be a String, using cURL >= 7.75.0';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                break;
                //Protocol Options
            //CURLOPT(CURLOPT_TRANSFERTEXT, CURLOPTTYPE_LONG, 53) = 53
            case 53: //CURLOPT_TRANSFERTEXT https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L433
            //CURLOPT(CURLOPT_CRLF, CURLOPTTYPE_LONG, 27) = 27
            case 27: //CURLOPT_CRLF https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L63
            //CURLOPT(CURLOPT_FILETIME, CURLOPTTYPE_LONG, 69) = 69
            case 69: //CURLOPT_FILETIME https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L103
            //CURLOPT(CURLOPT_NOBODY, CURLOPTTYPE_LONG, 44) = 44
            case 44: //CURLOPT_NOBODY https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L228
            //CURLOPT(CURLOPT_UPLOAD, CURLOPTTYPE_LONG, 46) = 46
            case 46: //CURLOPT_UPLOAD https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L443
                if (!is_bool($Value))
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be a boolean value';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                break;
            //#define CURLOPT_FTPLISTONLY CURLOPT_DIRLISTONLY
            //CURLOPT_FTPLISTONLY https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L128
            //CURLOPT(CURLOPT_DIRLISTONLY, CURLOPTTYPE_LONG, 48) = 48
            case 48: //CURLOPT_DIRLISTONLY https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L1617
                if (!is_bool($Value))
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be a boolean value';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                break;
            //CURLOPT(CURLOPT_TIMEVALUE, CURLOPTTYPE_LONG, 34) = 34
            case 34: //CURLOPT_TIMEVALUE https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L428
                if (!is_int($Value)||$Value<0)
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be a Integer on the range [0-PHP_MAX_INT]';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                break;
            // CURLOPT(CURLOPT_TIMECONDITION, CURLOPTTYPE_VALUES, 33) = 33
            case 33: //CURLOPT_TIMECONDITION https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L418
                //@see https://github.com/curl/curl/blob/master/include/curl/curl.h#L2300
                //Mutually exclusive
                //CURL_TIMECOND_NONE            0   Does nothing, I guess
                //CURL_TIMECOND_IFMODSINCE      1   Operate if modified since X.
                //CURL_TIMECOND_IFUNMODSINCE    2   Operate if unmodified since X.
                //CURL_TIMECOND_LASTMOD         3   Operate if last modification is X.
                //CURL_TIMECOND_LAST            4   Not an option, never use
                cURLWarn($Key, TRUE, FALSE, FALSE);
                if (!is_int($Value)||$Value<0||$Value>3)
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be a Integer on the range [0-3]';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                break;
            //CURLOPT(CURLOPT_CUSTOMREQUEST, CURLOPTTYPE_STRINGPOINT, 36) = 10036
            case 10036: //CURLOPT_CUSTOMREQUEST' https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L68
                if (!is_string($Value))
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be a string value';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                break;
            //CURLOPT(CURLOPT_RANGE, CURLOPTTYPE_STRINGPOINT, 7) = 10007
            case 10007: //CURLOPT_RANGE https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L313
                if (!is_string($Value))
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be a string value';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                else
                {
                    //"X-Y,N-M"
                    //For each range
                    $Ranges = explode(',',$Value);
                    foreach ($Ranges as $Range)
                    {
                        $Failure = FALSE;
                        $Interval = explode('-',$Range);
                        //There must always be 2 parts on each -
                        $PartsNum = count($Interval);
                        if ($PartsNum !== 2)
                        {
                            $Failure = TRUE;
                        }
                        //If there are two, lets check them
                        if ($Failure == FALSE)
                        {
                            //Ranges are optional
                            if (empty($Interval[0]))
                            {
                                $Interval[0] = 0;
                            }
                            if (empty($Interval[1]))
                            {
                                $Interval[1] = PHP_INT_MAX;
                            }
                            if (!is_numeric($Interval[0])||!is_numeric($Interval[1]))
                            {
                                $Failure = TRUE;
                            }
                        }
                        //If checks fail, let's raise an error
                        if ($Failure === TRUE)
                        {
                            $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to honor the format "X-Y" (Multiple ranges possible, "X-Y,N-M" for HTTP)';
                            ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                        }
                    }
                }
                break;
            //CURLOPT(CURLOPT_RESUME_FROM, CURLOPTTYPE_LONG, 21) = 21
            case 21: //CURLOPT_RESUME_FROM https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L333
            //CURLOPT(CURLOPT_INFILESIZE, CURLOPTTYPE_LONG, 14) = 14
            case 14: //CURLOPT_INFILESIZE https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L188
                if (!is_int($Value)||$Value<-1||$Value>2147483648)
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be an Integer representing less than 2 GiB (-1 < Value < 2147483648)';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                break;
            //CURLOPT(CURLOPT_MAXFILESIZE, CURLOPTTYPE_LONG, 114) = 114
            case 114: //CURLOPT_MAXFILESIZE https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L1292
                if (!is_int($Value)||$Value<0||$Value>2147483648)
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be an Integer representing less than 2 GiB (0 < Value < 2147483648)';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                break;
            //CURLOPT(CURLOPT_RESUME_FROM_LARGE, CURLOPTTYPE_OFF_T, 116) = 30116
            case 30116: //CURLOPT_RESUME_FROM_LARGE **UNDOCUMENTED AS OF PHP 8.1.0**
            //CURLOPT(CURLOPT_INFILESIZE_LARGE, CURLOPTTYPE_OFF_T, 115) == 30115
            case 30115: //CURLOPT_INFILESIZE_LARGE **UNDOCUMENTED AS OF PHP 8.1.0**
                cURLWarn($Key, TRUE, FALSE, FALSE);
                if (!is_int($Value)||!CheckcURLVersion('7.11.0')||$Value<-1)
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be an Integer valued at least -1 or more, using cURL >= 7.11.0';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                break;
            //CURLOPT(CURLOPT_MAXFILESIZE_LARGE, CURLOPTTYPE_OFF_T, 117) = 30117
            case 30117: //CURLOPT_MAXFILESIZE_LARGE https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L1351
                cURLWarn($Key, TRUE, FALSE, FALSE);
                if (!is_int($Value)||!CheckcURLVersion('7.11.0')||$Value<0)
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be an Integer valued at least 0 or more, using cURL >= 7.11.0';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                break;
            //CURLOPT(CURLOPT_PROXY_TRANSFER_MODE, CURLOPTTYPE_LONG, 166) = 166
            case 166: //CURLOPT_PROXY_TRANSFER_MODE https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L1657
                cURLWarn($Key, TRUE, FALSE, FALSE);
                if (!is_bool($Value)||!CheckcURLVersion('7.18.0'))
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be a Boolean, using cURL >= 7.18.0';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                break;
            //CURLOPT(CURLOPT_MIMEPOST, CURLOPTTYPE_OBJECTPOINT, 269) = 10269
            case 10269: //CURLOPT_MIMEPOST **UNDOCUMENTED AS OF PHP 8.1.0**
                cURLWarn($Key, TRUE, FALSE, FALSE);
                if (!is_array($Value)||!CheckcURLVersion('7.56.0'))
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be an Array, using cURL >= 7.56.0';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                break;
            //CURLOPT(CURLOPT_TIMEVALUE_LARGE, CURLOPTTYPE_OFF_T, 270) = 30270
            case 30270: //CURLOPT_TIMEVALUE_LARGE https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L2973
                if (!is_int($Value)||$Value<0||!CheckPHPVersion('7.3.0')||!CheckcURLVersion('7.59.0'))
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be a positive Integer, using cURL >= 7.59.0 and PHP >= 7.3.0';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                break;
            //CURLOPT(CURLOPT_UPLOAD_BUFFERSIZE, CURLOPTTYPE_LONG, 280) = 280
            case 280: //CURLOPT_UPLOAD_BUFFERSIZE https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L3067
                if (!is_int($Value)||!CheckcURLVersion('7.62.0')||$Value<16384||$Value>2097152)
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be an Integer representing between 16 KiB and 2 MiB (16383 < Value < 2097153), using cURL >= 7.62.0';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                break;
            //CURLOPT(CURLOPT_CURLU, CURLOPTTYPE_OBJECTPOINT, 282) = 10282
            case 282: //CURLOPT_CURLU **UNDOCUMENTED AS OF PHP 8.1.0**
                cURLWarn($Key, TRUE, FALSE, FALSE);
                if (!is_string($Value)||!CheckcURLVersion('7.63.0'))
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be a String, using cURL >= 7.63.0';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                break;
            //CURLOPT(CURLOPT_MIME_OPTIONS, CURLOPTTYPE_LONG, 315) = 315
            case 315: //CURLOPT_MIME_OPTIONS **UNDOCUMENTED AS OF PHP 8.1.0**
                //@see https://github.com/curl/curl/blob/master/include/curl/curl.h#L2322
                //CURLMIMEOPT_FORMESCAPE    (1<<0)  1   Use backslash-escaping for forms.
                cURLWarn($Key, TRUE, FALSE, FALSE);
                if (!is_int($Value)||!CheckcURLVersion('7.81.0')||$Value !== 1)
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be 1, using cURL >= 7.81.0';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                break;
                //Connection Options
            //CURLOPT(CURLOPT_FORBID_REUSE, CURLOPTTYPE_LONG, 75) = 75
            case 75: //CURLOPT_FORBID_REUSE https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L113
            //CURLOPT(CURLOPT_FRESH_CONNECT, CURLOPTTYPE_LONG, 74) = 74
            case 74: //CURLOPT_FRESH_CONNECT https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L118
                if (!is_bool($Value))
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be a Boolean value';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                break;
            //CURLOPT(CURLOPT_TIMEOUT, CURLOPTTYPE_LONG, 13) = 13
            case 13: //CURLOPT_TIMEOUT https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L423
            //CURLOPT(CURLOPT_TIMEOUT_MS, CURLOPTTYPE_LONG, 155) = 155
            case 155: //CURLOPT_TIMEOUT_MS https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L1581
            //CURLOPT(CURLOPT_LOW_SPEED_LIMIT, CURLOPTTYPE_LONG, 19) = 19
            case 19: //CURLOPT_LOW_SPEED_LIMIT https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L203
            //CURLOPT(CURLOPT_LOW_SPEED_TIME, CURLOPTTYPE_LONG, 20) = 20
            case 20: //CURLOPT_LOW_SPEED_TIME https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L208
            //CURLOPT(CURLOPT_MAXCONNECTS, CURLOPTTYPE_LONG, 71) = 71
            case 71: //CURLOPT_MAXCONNECTS https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L213
            //CURLOPT(CURLOPT_CONNECTTIMEOUT, CURLOPTTYPE_LONG, 78) = 78
            case 78: //CURLOPT_CONNECTTIMEOUT https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L38
                if (!is_int($Value)||$Value<0)
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be a positive Integer';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                break;
            //CURLOPT(CURLOPT_IPRESOLVE, CURLOPTTYPE_VALUES, 113) = 113
            case 113: //CURLOPT_IPRESOLVE https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L1287
                //@see https://github.com/curl/curl/blob/master/include/curl/curl.h#L2194
                //Seemingly non-OReable
                //CURL_IPRESOLVE_WHATEVER   0   Default, uses addresses to all IP versions that your system allows
                //CURL_IPRESOLVE_V4         1   Uses only IPv4 addresses/connections
                //CURL_IPRESOLVE_V6         2   Uses only IPv6 addresses/connections
                if (!is_int($Value)||$Value<0||$Value>2||!CheckcURLVersion('7.10.8'))
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be an Integer on the range [0-2] and using cURL >= 7.10.8';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                break;
                //CURLOPT(CURLOPT_CONNECT_ONLY, CURLOPTTYPE_LONG, 141) = 141
            case 141: //CURLOPT_CONNECT_ONLY https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L1444
                if (!is_bool($Value)||!CheckcURLVersion('7.15.2'))
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be a Boolean value, using cURL >= 7.15.2';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                break;
            //CURLOPT(CURLOPT_MAX_SEND_SPEED_LARGE, CURLOPTTYPE_OFF_T, 145) = 30145
            case 30145: //CURLOPT_MAX_SEND_SPEED_LARGE https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L1500
            //CURLOPT(CURLOPT_MAX_RECV_SPEED_LARGE, CURLOPTTYPE_OFF_T, 146) = 30146
            case 30146: //CURLOPT_MAX_RECV_SPEED_LARGE https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L1495
                if (!is_int($Value)||$Value<0||!CheckcURLVersion('7.15.5'))
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be a positive Integer and using cURL >= 7.15.5';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                break;
            //CURLOPT(CURLOPT_CONNECTTIMEOUT_MS, CURLOPTTYPE_LONG, 156) = 156
            case 156: //CURLOPT_CONNECTTIMEOUT_MS https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L1566
                if (!is_int($Value)||$Value<0||!CheckcURLVersion('7.16.2'))
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be a positive Integer value and using cURL >= 7.16.2';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                break;
            //#define CURLOPT_FTP_SSL CURLOPT_USE_SSL
            //CURLOPT(CURLOPT_USE_SSL, CURLOPTTYPE_VALUES, 119) = 119
            case 119: //CURLOPT_USE_SSL https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L1622
                //@see https://github.com/curl/curl/blob/master/include/curl/curl.h#L876
                //Mutually exclusive
                //CURLUSESSL_NONE       0   Do not attempt to use SSL
                //CURLUSESSL_TRY        1   Try using SSL, proceed anyway otherwise
                //CURLUSESSL_CONTROL    2   SSL for the control connection or fail
                //CURLUSESSL_ALL        3   SSL for all communication or fail
                //CURLUSESSL_LAST       4   Not an option, never use
                cURLWarn($Key, TRUE, FALSE, FALSE);
                if (!is_int($Value)||$Value<0||$Value>3||!CheckcURLVersion('7.16.4'))
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be an Integer on the range [0-3] and using cURL >= 7.16.4';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                break;
            //CURLOPT(CURLOPT_RESOLVE, CURLOPTTYPE_SLISTPOINT, 203) = 10203
            case 10203: //CURLOPT_RESOLVE https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L2229
                if (!is_array($Value)||!CheckcURLVersion('7.21.3'))
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be an Array and using cURL >= 7.21.3';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                else
                {
                    //Array of strings, format HOST:PORT:ADDRESS[,ADDRESS]
                    foreach ($Value as $Redirection)
                    {
                        $Failure = FALSE;
                        $Parts = explode(':', $Redirection);
                        //Check number of parts
                        $Amount = count($Parts);
                        if ($Amount !== 3)
                        {
                            $Failure = TRUE;
                        }
                        //Check parts
                        if ($Failure === FALSE)
                        {
                            //Host part
                            if (!IsValidHost($Parts[0], FALSE))
                            {
                                $Failure = TRUE;
                            }
                            //Port part
                            if (!IsValidIANAPort($Parts[1]))
                            {
                                $Failure = TRUE;
                            }
                            //IP Part
                            $TheIPs = explode(',',$Parts[2]);
                            foreach ($TheIPs as $IP)
                            {
                                //Can be v4 or v6
                                if (!IsValidIP($IP))
                                {
                                    $Failure = TRUE;
                                }
                            }
                        }
                        //If checks fail, let's raise an error
                        if ($Failure === TRUE)
                        {
                            $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to honor the format "HOST:PORT:ADDRESS[,ADDRESS]"';
                            ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                        }
                    }
                }
                break;
            //CURLOPT(CURLOPT_ACCEPTTIMEOUT_MS, CURLOPTTYPE_LONG, 212) = 212
            case 212: //CURLOPT_ACCEPTTIMEOUT_MS https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L2302
                cURLWarn($Key, TRUE, FALSE, FALSE);
                if (!is_int($Value)||$Value<0||!CheckcURLVersion('7.24.0'))
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be a positive Integer value and using cURL >= 7.24.0';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                break;
            //CURLOPT(CURLOPT_DNS_SERVERS, CURLOPTTYPE_STRINGPOINT, 211) = 10211
            case 10211: //CURLOPT_DNS_SERVERS https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L2307
                //Pending format validation... host[:port][,host[:port]]...
                cURLWarn($Key, TRUE, FALSE, FALSE);
                if (!is_string($Value)||!CheckcURLVersion('7.24.0'))
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be a String, using cURL >= 7.24.0';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                break;
            //CURLOPT(CURLOPT_DNS_INTERFACE, CURLOPTTYPE_STRINGPOINT, 221) = 10221
            case 10221: //CURLOPT_DNS_INTERFACE https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L2396
                if (!is_string($Value)||!CheckPHPVersion('7.0.7')||!CheckcURLVersion('7.33.0'))
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be a String, using cURL >= 7.33.0 and PHP >= 7.0.7';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                break;
            //CURLOPT(CURLOPT_DNS_LOCAL_IP4, CURLOPTTYPE_STRINGPOINT, 222) = 10222
            case 10222: //CURLOPT_DNS_LOCAL_IP4 https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L2401
                if (!is_string($Value)||!IsValidIPv4($Value)||!CheckPHPVersion('7.0.7')||!CheckcURLVersion('7.33.0'))
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be a String containig a valid IPV4, using cURL >= 7.33.0 and PHP >= 7.0.7';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                break;
            //CURLOPT(CURLOPT_DNS_LOCAL_IP6, CURLOPTTYPE_STRINGPOINT, 223) = 10223
            case 10223: //CURLOPT_DNS_LOCAL_IP6 https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L2406
                if (!is_string($Value)||!IsValidIPv6($Value)||!CheckPHPVersion('7.0.7')||!CheckcURLVersion('7.33.0'))
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be a String containig a valid IPV6, using cURL >= 7.33.0 and PHP >= 7.0.7';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                break;
            //CURLOPT(CURLOPT_HAPPY_EYEBALLS_TIMEOUT_MS, CURLOPTTYPE_LONG, 271) = 271
            case 271: //CURLOPT_HAPPY_EYEBALLS_TIMEOUT_MS https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L2968
                if (!is_int($Value)||!CheckPHPVersion('7.3.0')||!CheckcURLVersion('7.59.0'))
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be an Integer, using cURL >= 7.59.0 and PHP >= 7.3.0';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                break;
            //CURLOPT(CURLOPT_DNS_SHUFFLE_ADDRESSES, CURLOPTTYPE_LONG, 275) = 275
            case 275: //CURLOPT_DNS_SHUFFLE_ADDRESSES https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L2981
                if (!is_bool($Value)||!CheckPHPVersion('7.3.0')||!CheckcURLVersion('7.60.0'))
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be a Boolean, using cURL >= 7.60.0 and PHP >= 7.3.0';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                break;
            //CURLOPT(CURLOPT_UPKEEP_INTERVAL_MS, CURLOPTTYPE_LONG, 281) = 281
            case 281: //CURLOPT_UPKEEP_INTERVAL_MS https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L3062
                cURLWarn($Key, TRUE, FALSE, FALSE);
                if (!is_int($Value)||$Value<0||!CheckcURLVersion('7.62.0'))
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be a positive Integer, using cURL >= 7.62.0';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                break;
            //CURLOPT(CURLOPT_MAXAGE_CONN, CURLOPTTYPE_LONG, 288) = 288
            case 288: //CURLOPT_MAXAGE_CONN https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L3121
                cURLWarn($Key, TRUE, FALSE, FALSE);
                if (!is_int($Value)||$Value<0||!CheckcURLVersion('7.65.0'))
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be a positive Integer, using cURL >= 7.65.0';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                break;
            //CURLOPT(CURLOPT_MAXLIFETIME_CONN, CURLOPTTYPE_LONG, 314) = 314
            case 314: //CURLOPT_MAXLIFETIME_CONN https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L3517
                cURLWarn($Key, TRUE, FALSE, FALSE);
                if (!is_int($Value)||$Value<1||!CheckcURLVersion('7.80.0'))
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be a positive Integer, using cURL >= 7.80.0';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                break;
                //SSL and Security Options
            //CURLOPT(CURLOPT_SSL_VERIFYPEER, CURLOPTTYPE_LONG, 64) = 64
            case 64: //CURLOPT_SSL_VERIFYPEER https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L403
                if (!is_bool($Value))
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be a Boolean value';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                elseif (is_bool($Value)&&($Value == FALSE||$Value == 0))
                {
                    $Message = $Key.' set to FALSE allows man in the middle (MITM) attacks. Get an updated cacert.pem from https://curl.se/docs/caextract.html';
                    ErrorLog($Message, E_USER_WARNING);
                }
                break;
            //CURLOPT(CURLOPT_SSLVERSION, CURLOPTTYPE_VALUES, 32) = 32
            case 32: //CURLOPT_SSLVERSION': https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L388
                //@see https://github.com/curl/curl/blob/master/include/curl/curl.h#L2257
                //Mutually exclusive
                //CURL_SSLVERSION_DEFAULT   0   The default acceptable version range. The minimum acceptable version is by default TLS v1.0 since 7.39.0
                //CURL_SSLVERSION_TLSv1     1   TLS v1.0 or later
                //CURL_SSLVERSION_SSLv2     2   SSL v2 - refused
                //CURL_SSLVERSION_SSLv3     3   SSL v3 - refused
                //CURL_SSLVERSION_TLSv1_0   4   TLS v1.0 or later (Added in 7.34.0)
                //CURL_SSLVERSION_TLSv1_1   5   TLS v1.1 or later (Added in 7.34.0)
                //CURL_SSLVERSION_TLSv1_2   6   TLS v1.2 or later (Added in 7.34.0)
                //CURL_SSLVERSION_TLSv1_3   7   TLS v1.3 or later (Added in 7.52.0)
                //CURL_SSLVERSION_LAST      8   Illegal option, do not use
                //The maximum TLS version can be set by using one of the CURL_SSLVERSION_MAX_ macros below. It is also possible to OR one of the
                //CURL_SSLVERSION_ macros with one of the CURL_SSLVERSION_MAX_ macros. (Added in 7.54.0)
                //@see https://github.com/curl/curl/blob/master/include/curl/curl.h#L2270
                //CURL_SSLVERSION_MAX_NONE      0
                //CURL_SSLVERSION_MAX_DEFAULT   65536   (CURL_SSLVERSION_TLSv1 << 16) 1*2^16
                //CURL_SSLVERSION_MAX_TLSv1_0   262144  (CURL_SSLVERSION_TLSv1_0 << 16) 4*2^16
                //CURL_SSLVERSION_MAX_TLSv1_1   327680  (CURL_SSLVERSION_TLSv1_1 << 16) 5*2^16
                //CURL_SSLVERSION_MAX_TLSv1_2   393216  (CURL_SSLVERSION_TLSv1_2 << 16) 6*2^16
                //CURL_SSLVERSION_MAX_TLSv1_3   458752  (CURL_SSLVERSION_TLSv1_3 << 16) 7*2^16
                //Max 458752 OR 7 = 458759
                if (!is_int($Value)||$Value<0)
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be a positive Integer value.';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                elseif ($Value>3&&$Value<7)
                {
                    if (!CheckcURLVersion('7.34.0'))
                    {
                        $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' with CURL_SSLVERSION_TLSv1_0, CURL_SSLVERSION_TLSv1_1 or CURL_SSLVERSION_TLSv1_3 requires using cURL >= 7.34.0.';
                        ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                    }
                }
                elseif ($Value === 7)
                {
                    if (!CheckcURLVersion('7.52.0'))
                    {
                        $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' with CURL_SSLVERSION_TLSv1_3 requires using cURL >= 7.52.0.';
                        ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                    }
                }
                elseif ($Value>7&&$Value<458760)
                {
                    if (!CheckcURLVersion('7.54.0'))
                    {
                        $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' with CURL_SSLVERSION_MAX_* requires using cURL >= 7.54.0.';
                        ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                    }
                }
                elseif ($Value>458759)
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be a positive Integer value on the range [0-458759].';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                break;
            //CURLOPT(CURLOPT_SSL_VERIFYHOST, CURLOPTTYPE_LONG, 81) = 81
            case 81: //CURLOPT_SSL_VERIFYHOST https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L398
                //0 The connection succeeds regardless of the names in the certificate
                //1 Changing behaviour:
                //   - In 7.28.0 and earlier: treated as a debug option of some sorts
                //   - From 7.28.1 to 7.65.3: Returns an error and leaves the flag untouched.
                //   - From 7.66.0: treats 1 and 2 the same
                //2 That certificate must indicate that the server is the server to which you meant to connect, or the connection fails
                if (!is_int($Value)||$Value<0||$Value>2)
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be a positive Integer value on the range [0-2].';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                break;
            //CURLOPT(CURLOPT_SSLCERT, CURLOPTTYPE_STRINGPOINT, 25) = 10025
            case 10025: //CURLOPT_SSLCERT https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L348
            //CURLOPT(CURLOPT_SSLKEY, CURLOPTTYPE_STRINGPOINT, 87) = 10087
            case 10087: //CURLOPT_SSLKEY https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L373
            //CURLOPT(CURLOPT_SSLENGINE, CURLOPTTYPE_STRINGPOINT, 89) = 10089
            case 10089: //CURLOPT_SSLENGINE https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L363
            //CURLOPT(CURLOPT_SSLENGINE_DEFAULT, CURLOPTTYPE_LONG, 90) = 90
            case 90: //CURLOPT_SSLENGINE_DEFAULT https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L368
                //CURLOPT_SSLCERT: Pending certificate format validation (P12, PEM)
                //CURLOPT_SSLCERT: Pending check if it is a filename or a certificate store
                if (!is_string($Value))
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be a String value';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                break;
            //CURLOPT(CURLOPT_ISSUERCERT, CURLOPTTYPE_STRINGPOINT, 170) = 10170
            case 10170: //CURLOPT_ISSUERCERT https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L1743
                //Pending certificate format validation (PEM)
                cURLWarn($Key, TRUE, FALSE, FALSE);
                if (!is_string($Value))
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be a String value';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                break;
            //CURLOPT(CURLOPT_CAINFO, CURLOPTTYPE_STRINGPOINT, 65) = 10065
            case 10065: //CURLOPT_CAINFO https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L28
                if (!is_string($Value)||is_readable($Value))
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be a String representing a path to a readable file.';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                break;
            //CURLOPT(CURLOPT_CAPATH, CURLOPTTYPE_STRINGPOINT, 97) = 10097
            case 10097: //CURLOPT_CAPATH https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L33
                if (!is_string($Value)||is_dir($Value)||is_readable($Value))
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be a String representing a path to a readable directory.';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                break;
            // CURLOPT(CURLOPT_SSLKEYTYPE, CURLOPTTYPE_STRINGPOINT, 88) = 10088
            case 10088: //CURLOPT_SSLKEYTYPE https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L383
                $ValidKeyTypes = array("PEM", "DER", "ENG");
                if (!is_string($Value)||!in_array($Value, $ValidKeyTypes))
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be a String, acceptable values are'.implode(', ',$ValidKeyTypes);
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                break;
            //CURLOPT(CURLOPT_RANDOM_FILE, CURLOPTTYPE_STRINGPOINT, 76) = 10076
            case 10076: //CURLOPT_RANDOM_FILE https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L308
            //CURLOPT(CURLOPT_EGDSOCKET, CURLOPTTYPE_STRINGPOINT, 77) = 10077
            case 10077: //CURLOPT_EGDSOCKET https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L83
                cURLWarn($Key, FALSE, FALSE, '7.84.0#none as it serves no purpose anymore.');
                if (!is_string($Value)||is_readable($Value)||!FNAcURLVersion('7.84.0'))
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be a String representing a path to a readable file. Deprecated in cURL 7.84.0.';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                break;
            //CURLOPT(CURLOPT_SSL_CIPHER_LIST, CURLOPTTYPE_STRINGPOINT, 83) = 10083
            case 10083: //CURLOPT_SSL_CIPHER_LIST https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L393
                //@todo: Validate ciphers following https://curl.se/docs/ssl-ciphers.html
                if (!is_string($Value)||!CheckcURLVersion('7.9.0'))
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be a String, using cURL >= 7.9.0.';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                break;
            //CURLOPT(CURLOPT_SSLCERTTYPE, CURLOPTTYPE_STRINGPOINT, 86) = 10086
            case 10086: //CURLOPT_SSLCERTTYPE https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L358
                $ValidCertFormats = array("PEM", "DER", "P12");
                if (!is_string($Value)||!CheckcURLVersion('7.9.3')||!in_array($Value, $ValidCertFormats))
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be a String, using cURL >= 7.9.3. Acceptable values are'.implode(', ',$ValidCertFormats);
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                break;
            //CURLOPT(CURLOPT_SSL_SESSIONID_CACHE, CURLOPTTYPE_LONG, 150) = 150
            case 150: //CURLOPT_SSL_SESSIONID_CACHE https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L1512
                cURLWarn($Key, TRUE, FALSE, FALSE);
                if (!is_bool($Value)||!CheckcURLVersion('7.16.0'))
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be a Boolean, using cURL >= 7.16.0.';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                break;
            //#define CURLOPT_KRB4LEVEL CURLOPT_KRBLEVEL
            //CURLOPT(CURLOPT_KRBLEVEL, CURLOPTTYPE_STRINGPOINT, 63) = 10063
            case 10063: //CURLOPT_KRBLEVEL https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L1595
                //CURLOPT_KRB4LEVEL change option to CURLOPT_KRBLEVEL, see https://github.com/curl/curl/blob/master/include/curl/curl.h#L2183
                $ValidKRBLevels = array("clear", "safe", "confidential", "private");
                if (!is_string($Value)||!in_array($Value, $ValidKRBLevels))
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be a String, using cURL >= 7.16.3. Acceptable values are'.implode(', ',$ValidKRBLevels);
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                break;
            //#define CURLOPT_SSLCERTPASSWD CURLOPT_KEYPASSWD
            //CURLOPT_SSLCERTPASSWD existed up to cURL 7.9.2 https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L353
            //#define CURLOPT_SSLKEYPASSWD CURLOPT_KEYPASSWD https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L378
            //CURLOPT_SSLKEYPASSWD existed from 7.9.2 to 7.16.4
            //CURLOPT(CURLOPT_KEYPASSWD, CURLOPTTYPE_STRINGPOINT, 26) = 10026
            case 10026: //CURLOPT_KEYPASSWD https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L1748
                //CURLOPT_SSLCERTPASSWD change option to CURLOPT_KEYPASSWD, see https://github.com/curl/curl/blob/master/include/curl/curl.h#L2182
                //CURLOPT_SSLKEYPASSWD change option to CURLOPT_KEYPASSWD, see https://github.com/curl/curl/blob/master/include/curl/curl.h#L2175
                if (!is_string($Value)||!CheckcURLVersion('7.16.4'))
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be a String, using cURL >= 7.16.4.';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                break;
            //CURLOPT(CURLOPT_CERTINFO, CURLOPTTYPE_LONG, 172) = 172
            case 172: //CURLOPT_CERTINFO https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L1796
                if (!is_bool($Value)||!CheckcURLVersion('7.19.0'))
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be a Boolean value, using cURL >= 7.19.0.';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                break;
            //CURLOPT(CURLOPT_CRLFILE, CURLOPTTYPE_STRINGPOINT, 169) = 10169
            case 10169: //CURLOPT_CRLFILE https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L1738
                //@todo research pure-php PEM format validation, such as in https://github.com/phpseclib/phpseclib/blob/master/phpseclib/File/X509.php
                if (!is_string($Value)||is_readable($Value)||!CheckcURLVersion('7.19.0'))
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be a String representing a path to a readable file, using cURL >= 7.19.0.';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                break;
            //CURLOPT(CURLOPT_GSSAPI_DELEGATION, CURLOPTTYPE_VALUES, 210) = 210
            case 210: //CURLOPT_GSSAPI_DELEGATION https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L2290
                //@see https://github.com/curl/curl/blob/master/include/curl/curl.h#L815
                //#define CURLGSSAPI_DELEGATION_NONE        0       0   no delegation (default)
                //#define CURLGSSAPI_DELEGATION_POLICY_FLAG (1<<0)  1   if permitted by policy
                //#define CURLGSSAPI_DELEGATION_FLAG        (1<<1)  2   delegate always
                //Makes no sense to OR them, somutually exclusive
                cURLWarn($Key, TRUE, FALSE, FALSE);
                if (!is_int($Value)||$Value<0||$Value>2||!CheckcURLVersion('7.22.0'))
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be a Integer on the range[0-2], using cURL >= 7.22.0.';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                break;
            //CURLOPT(CURLOPT_SSL_OPTIONS, CURLOPTTYPE_VALUES, 216) = 216
            case 216: //CURLOPT_SSL_OPTIONS https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L2319
                //@see https://github.com/curl/curl/blob/master/include/curl/curl.h#L885
                //L892 #define CURLSSLOPT_ALLOW_BEAST           (1<<0)  1   Allow the BEAST SSL vulnerability in the name of improving interoperability with older servers
                //L896 #define CURLSSLOPT_NO_REVOKE             (1<<1)  2   Disable certificate revocation checks for Schannel (the native Windows SSL library)
                //L900 #define CURLSSLOPT_NO_PARTIALCHAIN       (1<<2)  4   *NOT* accept a partial certificate chain if possible (OpenSSL).
                //L905 #define CURLSSLOPT_REVOKE_BEST_EFFORT    (1<<3)  8   Schannel, ignore certificate revocation offline checks and ignore missing revocation list
                //L909 #define CURLSSLOPT_NATIVE_CA             (1<<4)  16  Experimental, Windows-only, OpenSSL. Use standard certificate store of operating system
                //L913 #define CURLSSLOPT_AUTO_CLIENT_CERT      (1<<5)  32  Schannel, automatically locate and use a client certificate for authentication.
                //1||2||4||8||16||32 = 63
                //Pending version control for each option, backend (curl_version()??) and OS checks (PHP_OS, PHP_OS_FAMILY)
                if (!is_int($Value)||$Value<1||$Value>63||!CheckPHPVersion('7.0.7')||!CheckcURLVersion('7.25.0'))
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be a Integer on the range[1-63], using cURL >= 7.25.0 and PHP >= 7.0.7';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                break;
            //CURLOPT(CURLOPT_SSL_ENABLE_ALPN, CURLOPTTYPE_LONG, 226) = 226
            case 226: //CURLOPT_SSL_ENABLE_ALPN https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L2459
                if (!is_bool($Value)||!CheckPHPVersion('7.0.7')||!CheckcURLVersion('7.36.0'))
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be a Boolean, using cURL >= 7.36.0 and PHP >= 7.0.7';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                break;
            //CURLOPT(CURLOPT_SSL_ENABLE_NPN, CURLOPTTYPE_LONG, 225) = 225
            case 225: //CURLOPT_SSL_ENABLE_NPN https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L2464
                cURLWarn($Key, FALSE, FALSE, '7.86.0#');
                if (!is_bool($Value)||!CheckPHPVersion('7.0.7')||!CheckcURLVersion('7.36.0')||!FNAcURLVersion('7.86.0'))
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be a Boolean, using cURL >= 7.36.0 and PHP >= 7.0.7. Deprecated in cURL 7.86.0.';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                break;
            //CURLOPT(CURLOPT_PINNEDPUBLICKEY, CURLOPTTYPE_STRINGPOINT, 230) = 10230
            case 10230: //CURLOPT_PINNEDPUBLICKEY https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L2508
                //@todo research pure-php PEM/DER format validation, such as in https://github.com/phpseclib/phpseclib/blob/master/phpseclib/File/X509.php
                if (!is_string($Value)||!CheckPHPVersion('7.0.7')||!CheckcURLVersion('7.39.0'))
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be a String, using cURL >= 7.39.0 and PHP >= 7.0.7';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                break;
            //CURLOPT(CURLOPT_SSL_VERIFYSTATUS, CURLOPTTYPE_LONG, 232) = 232
            case 232: //CURLOPT_SSL_VERIFYSTATUS https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L2544
                if (!is_bool($Value)||!CheckPHPVersion('7.0.7')||!CheckcURLVersion('7.41.0'))
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be a Boolean, using cURL >= 7.41.0 and PHP >= 7.0.7';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                break;
            //CURLOPT(CURLOPT_SSL_FALSESTART, CURLOPTTYPE_LONG, 233) = 233
            case 233: //CURLOPT_SSL_FALSESTART https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L2557
                if (!is_bool($Value)||!CheckPHPVersion('7.0.7')||!CheckcURLVersion('7.42.0'))
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be a Boolean, using cURL >= 7.42.0 and PHP >= 7.0.7';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                break;
            //CURLOPT(CURLOPT_PROXY_SSL_VERIFYPEER, CURLOPTTYPE_LONG, 248) = 248
            case 248: //CURLOPT_PROXY_SSL_VERIFYPEER https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L2774
                if (!is_bool($Value)||!CheckPHPVersion('7.3.0')||!CheckcURLVersion('7.52.0'))
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be a Boolean, using cURL >= 7.52.0 and PHP >= 7.3.0';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                break;
            //CURLOPT(CURLOPT_PROXY_SSL_VERIFYHOST, CURLOPTTYPE_LONG, 249) = 249
            case 249: //CURLOPT_PROXY_SSL_VERIFYHOST https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L2769
                //0 The connection succeeds regardless of the names in the certificate
                //1 Changing behaviour:
                //   - From 7.52.0 to 7.65.3: Returns an error and leaves the flag untouched.
                //   - From 7.66.0: treats 1 and 2 the same
                //2 That certificate must indicate that the server is the server to which you meant to connect, or the connection fails
                if (!is_int($Value)||$Value<0||$Value>2||!CheckcURLVersion('7.52.0'))
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be a positive Integer value on the range [0-2], using cURL >= 7.52.0.';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                break;
            //CURLOPT(CURLOPT_CA_CACHE_TIMEOUT, CURLOPTTYPE_LONG, 321) = 321
            case 321: //CURLOPT_CA_CACHE_TIMEOUT  **UNDOCUMENTED AS OF PHP 8.1.0**
                if (!is_int($Value)||$Value<-1||$Value>2147483648||!CheckcURLVersion('7.87.0'))
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be an Integer value on the range [-1-2147483648], using cURL >= 7.87.0.';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                break;
            //CURLOPT(CURLOPT_PROXY_SSLCERT, CURLOPTTYPE_STRINGPOINT, 254) = 10254
            case 10254: //CURLOPT_PROXY_SSLCERT https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L2779
            //CURLOPT(CURLOPT_PROXY_KEYPASSWD, CURLOPTTYPE_STRINGPOINT, 258) = 10258
            case 10258: //CURLOPT_PROXY_KEYPASSWD https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L2749
            //CURLOPT(CURLOPT_PROXY_SSL_CIPHER_LIST, CURLOPTTYPE_STRINGPOINT, 259) = 10259
            case 10259: //CURLOPT_PROXY_SSL_CIPHER_LIST https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L2759
                //CURLOPT_PROXY_SSL_CIPHER_LIST: Validate ciphers following https://curl.se/docs/ssl-ciphers.html
                if (!is_string($Value)||!CheckPHPVersion('7.3.0')||!CheckcURLVersion('7.52.0'))
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be a String, using cURL >= 7.52.0 and PHP >= 7.3.0';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                break;
            //CURLOPT(CURLOPT_PROXY_SSLKEYTYPE, CURLOPTTYPE_STRINGPOINT, 257) = 10257
            case 10257: //CURLOPT_PROXY_SSLKEYTYPE https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L2794
                $ValidKeyTypes = array("PEM", "DER", "ENG");
                if (!is_string($Value)||!CheckPHPVersion('7.3.0')||!CheckcURLVersion('7.52.0')||!in_array($Value, $ValidKeyTypes))
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be a String, using cURL >= 7.52.0 and PHP >= 7.3.0. Acceptable values are'.implode(', ',$ValidKeyTypes);
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                break;
            //CURLOPT(CURLOPT_PROXY_SSLCERTTYPE, CURLOPTTYPE_STRINGPOINT, 255) = 10255
            case 10255: //CURLOPT_PROXY_SSLCERTTYPE https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L2784
                $ValidCertFormats = array("PEM", "DER", "P12");
                if (!is_string($Value)||!CheckPHPVersion('7.3.0')||!CheckcURLVersion('7.52.0')||!in_array($Value, $ValidCertFormats))
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be a String, using cURL >= 7.52.0 and PHP >= 7.3.0. Acceptable values are'.implode(', ',$ValidCertFormats);
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                break;
            //CURLOPT(CURLOPT_PROXY_SSLKEY, CURLOPTTYPE_STRINGPOINT, 256) = 10256
            case 10256: //CURLOPT_PROXY_SSLKEY https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L2789
                if (!is_string($Value)||!CheckPHPVersion('7.3.0')||!CheckcURLVersion('7.52.0')||is_readable($Value))
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be a String representing a path to a readable file, using cURL >= 7.52.0 and PHP >= 7.3.0';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                break;
            //CURLOPT(CURLOPT_PROXY_SSLVERSION, CURLOPTTYPE_VALUES, 250) = 250
            case 250: //CURLOPT_PROXY_SSLVERSION https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L2799
                //@see https://github.com/curl/curl/blob/master/include/curl/curl.h#L2257
                //Mutually exclusive
                //CURL_SSLVERSION_DEFAULT   0   The default acceptable version range. The minimum acceptable version is by default TLS v1.0 since 7.39.0
                //CURL_SSLVERSION_TLSv1     1   TLS v1.0 or later
                //CURL_SSLVERSION_SSLv2     2   SSL v2 - refused
                //CURL_SSLVERSION_SSLv3     3   SSL v3 - refused
                //CURL_SSLVERSION_TLSv1_0   4   TLS v1.0 or later
                //CURL_SSLVERSION_TLSv1_1   5   TLS v1.1 or later
                //CURL_SSLVERSION_TLSv1_2   6   TLS v1.2 or later
                //CURL_SSLVERSION_TLSv1_3   7   TLS v1.3 or later
                //CURL_SSLVERSION_LAST      8   Illegal option, do not use
                //The maximum TLS version can be set by using one of the CURL_SSLVERSION_MAX_ macros below. It is also possible to OR one of the
                //CURL_SSLVERSION_ macros with one of the CURL_SSLVERSION_MAX_ macros. (Added in 7.54.0)
                //@see https://github.com/curl/curl/blob/master/include/curl/curl.h#L2270
                //CURL_SSLVERSION_MAX_NONE      0
                //CURL_SSLVERSION_MAX_DEFAULT   65536   (CURL_SSLVERSION_TLSv1 << 16)   1*2^16
                //CURL_SSLVERSION_MAX_TLSv1_0   262144  (CURL_SSLVERSION_TLSv1_0 << 16) 4*2^16
                //CURL_SSLVERSION_MAX_TLSv1_1   327680  (CURL_SSLVERSION_TLSv1_1 << 16) 5*2^16
                //CURL_SSLVERSION_MAX_TLSv1_2   393216  (CURL_SSLVERSION_TLSv1_2 << 16) 6*2^16
                //CURL_SSLVERSION_MAX_TLSv1_3   458752  (CURL_SSLVERSION_TLSv1_3 << 16) 7*2^16
                //Max 458752 OR 7 = 458759
                if (!is_int($Value)||$Value<0||!CheckcURLVersion('7.52.0'))
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be a positive Integer value, using cURL >= 7.52.0.';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                elseif ($Value>7&&$Value<458760)
                {
                    if (!CheckcURLVersion('7.54.0'))
                    {
                        $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' with CURL_SSLVERSION_MAX_* requires using cURL >= 7.54.0.';
                        ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                    }
                }
                elseif ($Value>458759)
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be a positive Integer value on the range [0-458759].';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                break;
            //CURLOPT(CURLOPT_PROXY_SSL_OPTIONS, CURLOPTTYPE_LONG, 261) = 261
            case 261: //CURLOPT_PROXY_SSL_OPTIONS https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L2764
                //@see https://github.com/curl/curl/blob/master/include/curl/curl.h#L885
                //CURLSSLOPT_ALLOW_BEAST        (1<<0)  1   Allow the BEAST SSL vulnerability in the name of improving interoperability with older servers.
                //CURLSSLOPT_NO_REVOKE          (1<<1)  2   Disable certificate revocation checks
                //CURLSSLOPT_NO_PARTIALCHAIN    (1<<2)  4   Do not accept a partial certificate chain
                //CURLSSLOPT_REVOKE_BEST_EFFORT (1<<3)  8   Ignore certificate revocation offline checks and ignore missing revocation list
                //CURLSSLOPT_NATIVE_CA          (1<<4)  16  Use standard certificate store of operating system. (MS Windows)
                //CURLSSLOPT_AUTO_CLIENT_CERT   (1<<5)  32  Automatically locate and use a client certificate for authentication. (Schannel)
                //32||16||8||4||2||1 = 63
                //Pending version control for each option, backend (curl_version()??) and OS checks (PHP_OS, PHP_OS_FAMILY)
                if (!is_int($Value)||!CheckPHPVersion('7.3.0')||!CheckcURLVersion('7.52.0')||$Value<1||$Value>63)
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be an Integer on the range [1-63], using cURL >= 7.52.0 and PHP >= 7.3.0';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                break;
            //CURLOPT(CURLOPT_PROXY_PINNEDPUBLICKEY, CURLOPTTYPE_STRINGPOINT, 263) = 10263
            case 10263: //CURLOPT_PROXY_PINNEDPUBLICKEY https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L2754
                //@todo research pure-php PEM/DER format validation, such as in https://github.com/phpseclib/phpseclib/blob/master/phpseclib/File/X509.php
                if (!is_string($Value)||!CheckPHPVersion('7.3.0')||!CheckcURLVersion('7.52.0'))
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be a String, using cURL >= 7.52.0 and PHP >= 7.3.0';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                break;
            //CURLOPT(CURLOPT_PROXY_CAINFO, CURLOPTTYPE_STRINGPOINT, 246) = 10246
            case 10246: //CURLOPT_PROXY_CAINFO https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L2734
            //CURLOPT(CURLOPT_PROXY_CRLFILE, CURLOPTTYPE_STRINGPOINT, 260) = 10260
            case 10260: //CURLOPT_PROXY_CRLFILE https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L2744
                //@todo research pure-php PEM format validation, such as in https://github.com/phpseclib/phpseclib/blob/master/phpseclib/File/X509.php
                if (!is_string($Value)||!CheckPHPVersion('7.3.0')||!CheckcURLVersion('7.52.0')||is_readable($Value))
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be a String representing a path to a readable file, using cURL >= 7.52.0 and PHP >= 7.3.0';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                break;
            //CURLOPT(CURLOPT_PROXY_CAPATH, CURLOPTTYPE_STRINGPOINT, 247) = 10247
            case 10247: //CURLOPT_PROXY_CAPATH https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L2739
                if (!is_string($Value)||!CheckPHPVersion('7.3.0')||!CheckcURLVersion('7.52.0')||is_dir($Value)||is_readable($Value))
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be a String representing a path to a readable directory, using cURL >= 7.52.0 and PHP >= 7.3.0';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                break;
            //CURLOPT(CURLOPT_TLS13_CIPHERS, CURLOPTTYPE_STRINGPOINT, 276) = 10276
            case 10276: //CURLOPT_TLS13_CIPHERS https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L3049
            //CURLOPT(CURLOPT_PROXY_TLS13_CIPHERS, CURLOPTTYPE_STRINGPOINT, 277) = 10277
            case 10277: //CURLOPT_PROXY_TLS13_CIPHERS https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L3044
                //@todo: Validate ciphers following https://curl.se/docs/ssl-ciphers.html
                if (!is_string($Value)||!CheckPHPVersion('7.3.0')||CheckOpenSSLVersion('1.1.1')||!CheckcURLVersion('7.61.0'))
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be a String, using cURL >= 7.61.0, OpenSSL >= 1.1.1 and PHP >= 7.3.0';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                break;
            //CURLOPT(CURLOPT_PROXY_ISSUERCERT, CURLOPTTYPE_STRINGPOINT, 296) = 10296
            case 10296: //CURLOPT_PROXY_ISSUERCERT https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L3184
                //Pending certificate format validation (PEM)
                cURLWarn($Key, TRUE, FALSE, FALSE);
                if (!is_string($Value)||!CheckcURLVersion('7.71.0'))
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be a string value and using cURL >= 7.71.0';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                break;
            //CURLOPT(CURLOPT_SSLCERT_BLOB, CURLOPTTYPE_BLOB, 291) = 40291
            case 40291: //CURLOPT_SSLCERT_BLOB https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L3204
            //CURLOPT(CURLOPT_PROXY_SSLCERT_BLOB, CURLOPTTYPE_BLOB, 293) = 40293
            case 40293: //CURLOPT_PROXY_SSLCERT_BLOB https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L3194
            //CURLOPT(CURLOPT_SSLKEY_BLOB, CURLOPTTYPE_BLOB, 292) = 40292
            case 40292: //CURLOPT_SSLKEY_BLOB https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L3209
            //CURLOPT(CURLOPT_PROXY_SSLKEY_BLOB, CURLOPTTYPE_BLOB, 294) = 40294
            case 40294: //CURLOPT_PROXY_SSLKEY_BLOB https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L3199
            //CURLOPT(CURLOPT_ISSUERCERT_BLOB, CURLOPTTYPE_BLOB, 295) = 40295
            case 40295: //CURLOPT_ISSUERCERT_BLOB https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L3179
            //CURLOPT(CURLOPT_PROXY_ISSUERCERT_BLOB, CURLOPTTYPE_BLOB, 297) = 40297
            case 40297: //CURLOPT_PROXY_ISSUERCERT_BLOB https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L3189
                //Pending certificate format validation (P12, PEM)... depends on the option
                cURLWarn($Key, TRUE, FALSE, FALSE);
                if (!is_array($Value)||!CheckcURLVersion('7.71.0'))
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be an array value and using cURL >= 7.71.0';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                break;
            //CURLOPT(CURLOPT_SSL_EC_CURVES, CURLOPTTYPE_STRINGPOINT, 298) = 10298
            case 10298: //CURLOPT_SSL_EC_CURVES https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L3252
                //Pending key exchange algorithms format validation algo1:algo2. Valid algos depend on OpenSSL
                cURLWarn($Key, TRUE, FALSE, FALSE);
                if (!is_string($Value)||!CheckcURLVersion('7.73.0'))
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be a String value and using cURL >= 7.73.0';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                break;
            //CURLOPT(CURLOPT_DOH_SSL_VERIFYPEER, CURLOPTTYPE_LONG, 306) = 306
            case 306: //CURLOPT_DOH_SSL_VERIFYPEER https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L3481
            //CURLOPT(CURLOPT_DOH_SSL_VERIFYSTATUS, CURLOPTTYPE_LONG, 308) = 308
            case 308: //CURLOPT_DOH_SSL_VERIFYSTATUS https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L3486
                cURLWarn($Key, TRUE, FALSE, FALSE);
                if (!is_bool($Value)||!CheckcURLVersion('7.76.0'))
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be a Boolean value and using cURL >= 7.76.0';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                break;
            //CURLOPT(CURLOPT_DOH_SSL_VERIFYHOST, CURLOPTTYPE_LONG, 307) = 307
            case 307: //CURLOPT_DOH_SSL_VERIFYHOST https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L3476
                //0 The connection succeeds regardless of the names in the certificate
                //1 Treats 1 and 2 the same
                //2 That certificate must indicate that the server is the server to which you meant to connect, or the connection fails
                cURLWarn($Key, TRUE, FALSE, FALSE);
                if (!is_int($Value)||$Value<0||$Value>2||!CheckcURLVersion('7.76.0'))
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be a positive Integer value on the range [0-2], using cURL >= 7.76.0.';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                break;
            //CURLOPT(CURLOPT_CAINFO_BLOB, CURLOPTTYPE_BLOB, 309) = 40309
            case 40309: //CURLOPT_CAINFO_BLOB https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L3499
            //CURLOPT(CURLOPT_PROXY_CAINFO_BLOB, CURLOPTTYPE_BLOB, 310) = 40310
            case 40310: //CURLOPT_PROXY_CAINFO_BLOB https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L3504
                cURLWarn($Key, TRUE, FALSE, FALSE);
                if (!is_array($Value)||!CheckcURLVersion('7.77.0'))
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be an array value and using cURL >= 7.77.0';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                break;
                //SSH Options
            //CURLOPT(CURLOPT_SSH_AUTH_TYPES, CURLOPTTYPE_VALUES, 151) = 151
            case 151: //CURLOPT_SSH_AUTH_TYPES https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L1534
                //@see https://github.com/curl/curl/blob/master/include/curl/curl.h#L805 to line 813
                //#define CURLSSH_AUTH_ANY          ~0      PHP_INT_MIN All types supported by the server
                //#define CURLSSH_AUTH_NONE         0       0           None allowed, silly but complete
                //#define CURLSSH_AUTH_PUBLICKEY    (1<<0)  1           Public/private key files
                //#define CURLSSH_AUTH_PASSWORD     (1<<1)  2           Pssword
                //#define CURLSSH_AUTH_HOST         (1<<2)  4           Host key files (currently has no effect)
                //#define CURLSSH_AUTH_KEYBOARD     (1<<3)  8           Keyboard interactive
                //#define CURLSSH_AUTH_AGENT        (1<<4)  16          Connect to ssh-agent or pageant and let the agent attempt the authentication
                //#define CURLSSH_AUTH_GSSAPI       (1<<5)  32          GSSAPI (kerberos, ...)
                //
                if (!is_int($Value)||$Value>63||!CheckcURLVersion('7.16.1'))
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be a Integer value on the range [PHP_INT_MIN-63], using cURL >= 7.16.1';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                break;
            //CURLOPT(CURLOPT_SSH_PUBLIC_KEYFILE, CURLOPTTYPE_STRINGPOINT, 152) = 10152
            case 10152: //CURLOPT_SSH_PUBLIC_KEYFILE https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L1544
            //CURLOPT(CURLOPT_SSH_PRIVATE_KEYFILE, CURLOPTTYPE_STRINGPOINT, 153) = 10153
            case 10153: //CURLOPT_SSH_PRIVATE_KEYFILE https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L1539
                if (!is_string($Value)||!CheckcURLVersion('7.16.1')||is_readable($Value))
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be a String pointing to a readable -non password protected- file, using cURL >= 7.16.1';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                break;
            //CURLOPT(CURLOPT_SSH_HOST_PUBLIC_KEY_MD5, CURLOPTTYPE_STRINGPOINT, 162) = 10162
            case 10162: //CURLOPT_SSH_HOST_PUBLIC_KEY_MD5 https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L1650
                if (!is_string($Value)||!IsValidMD5($Value)||!CheckcURLVersion('7.17.1'))
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be a valid MD5 string value and using cURL >= 7.17.1';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                break;
            //CURLOPT(CURLOPT_SSH_KEYDATA, CURLOPTTYPE_CBPOINT, 185) = 10185
            case 10185: //CURLOPT_SSH_KEYDATA **UNDOCUMENTED AS OF PHP 8.1.0**
                cURLWarn($Key, TRUE, FALSE, FALSE);
                if (!is_string($Value)||!CheckcURLVersion('7.19.6'))
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be a String, using cURL >= 7.19.6';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                break;
            //CURLOPT(CURLOPT_SSH_KNOWNHOSTS, CURLOPTTYPE_STRINGPOINT, 183) = 10183
            case 10183: //CURLOPT_SSH_KNOWNHOSTS https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L1981
                cURLWarn($Key, TRUE, FALSE, FALSE);
                if (!is_string($Value)||!CheckcURLVersion('7.19.6')||is_readable($Value))
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be a String pointing to a readable file, using cURL >= 7.19.6';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                break;
            //CURLOPT(CURLOPT_SSH_KEYFUNCTION, CURLOPTTYPE_FUNCTIONPOINT, 184) = 20184
            case 20184: //CURLOPT_SSH_KEYFUNCTION **UNDOCUMENTED AS OF PHP 8.1.0**
                cURLWarn($Key, TRUE, FALSE, FALSE);
                if (!is_array($Value)||!CheckcURLVersion('7.19.6'))
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be an array(cURLHandle, string) value, using cURL >= 7.19.6';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                else
                {
                    if (!is_object($Value[0])||(!is_a($Value[0],'CurlHandle'))||(!is_string($Value[1])))
                    {
                        $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be an array(cURLHandle, string) value';
                        ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                    }
                }
                break;
            //CURLOPT(CURLOPT_SSH_COMPRESSION, CURLOPTTYPE_LONG, 268) = 268
            case 268: //CURLOPT_SSH_COMPRESSION https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L2929
                if (!is_bool($Value)||!CheckPHPVersion('7.3.0')||!CheckcURLVersion('7.56.0'))
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be a Boolean, using cURL >= 7.52.0 and PHP >= 7.3.0';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                break;
            //CURLOPT(CURLOPT_SSH_HOST_PUBLIC_KEY_SHA256, CURLOPTTYPE_STRINGPOINT, 311) = 10311
            case 10311: //CURLOPT_SSH_HOST_PUBLIC_KEY_SHA256 https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L3522
                cURLWarn($Key, TRUE, FALSE, FALSE);
                if (!is_string($Value)||!IsValidBase64SHA256($Value)||!CheckcURLVersion('7.80.0'))
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be a valid base64-encoded SHA256 string value and using cURL >= 7.80.0';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                break;
            //CURLOPT(CURLOPT_SSH_HOSTKEYDATA, CURLOPTTYPE_CBPOINT, 317) = 10317
            case 10317: //CURLOPT_SSH_HOSTKEYDATA **UNDOCUMENTED AS OF PHP 8.1.0**
                cURLWarn($Key, TRUE, FALSE, FALSE);
                if (!is_string($Value)||!CheckcURLVersion('7.84.0'))
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be a String, using cURL >= 7.84.0';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                break;
            //CURLOPT(CURLOPT_SSH_HOSTKEYFUNCTION, CURLOPTTYPE_FUNCTIONPOINT, 316) = 10316
            case 10316: //CURLOPT_SSH_HOSTKEYFUNCTION **UNDOCUMENTED AS OF PHP 8.1.0**
                cURLWarn($Key, TRUE, FALSE, FALSE);
                if (!is_array($Value)||!CheckcURLVersion('7.84.0'))
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be an array(cURLHandle, string) value, using cURL >= 7.84.0';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                else
                {
                    if (!is_object($Value[0])||(!is_a($Value[0],'CurlHandle'))||(!is_string($Value[1])))
                    {
                        $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be an array(cURLHandle, string) value';
                        ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                    }
                }
                break;
                //Websockets Options
            //CURLOPT(CURLOPT_WS_OPTIONS, CURLOPTTYPE_LONG, 320) = 320
            case 320: //CURLOPT_WS_OPTIONS **UNDOCUMENTED AS OF PHP 8.1.0**
                //@see https://github.com/curl/curl/blob/master/include/curl/websockets.h#L75
                //#define CURLWS_RAW_MODE (1<<0)  1 Deliver "raw" WebSocket traffic to the CURLOPT_WRITEFUNCTION callback
                cURLWarn($Key, TRUE, FALSE, FALSE);
                if (!is_int($Value)||$Value !== 1||!CheckcURLVersion('7.85.0'))
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be a Integer valued 1, using cURL >= 7.85.0';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                break;
                //Other Options
            //CURLOPT(CURLOPT_QUICK_EXIT, CURLOPTTYPE_LONG, 322) = 322
            case 322: //CURLOPT_QUICK_EXIT  **UNDOCUMENTED AS OF PHP 8.1.0**
                cURLWarn($Key, TRUE, FALSE, FALSE);
                if (!is_bool($Value)||!CheckcURLVersion('7.87.0'))
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be a Boolean value and using cURL >= 7.87.0';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                break;
            //CURLOPT(CURLOPT_SHARE, CURLOPTTYPE_OBJECTPOINT, 100) = 10100
            case 10100: //CURLOPT_SHARE https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L343
                //Class name from https://phpbackend.com/blog/post/php-8-0-curlHandle-object-in-curl-functions
                if (!is_object($Value)||(!is_a($Value,'CurlHandle')))
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be a CurlHandle object';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                break;
            //CURLOPT(CURLOPT_PRIVATE, CURLOPTTYPE_OBJECTPOINT, 103) = 10103
            case 10103: //CURLOPT_PRIVATE https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L268
                if (!is_string($Value)||!CheckcURLVersion('7.10.3'))
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be a String value and using cURL >= 7.10.3';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                break;
            //CURLOPT(CURLOPT_NEW_FILE_PERMS, CURLOPTTYPE_LONG, 159) = 159
            case 159: //CURLOPT_NEW_FILE_PERMS https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L1605
            //CURLOPT(CURLOPT_NEW_DIRECTORY_PERMS, CURLOPTTYPE_LONG, 160) = 160
            case 160: //CURLOPT_NEW_DIRECTORY_PERMS https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L1600
                //@see https://rubendougall.co.uk/projects/permissions-calculator/
                //@see https://github.com/Ruben9922/permissions-calculator
                cURLWarn($Key, TRUE, FALSE, FALSE);
                if (!is_int($Value)||$Value<0||$Value>7777||!CheckcURLVersion('7.16.4'))
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be a Integer on the range [0-7777], using cURL >= 7.16.4';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                break;
                //TELNET Options
            //CURLOPT(CURLOPT_TELNETOPTIONS, CURLOPTTYPE_SLISTPOINT, 70) = 10070
            case 10070: //CURLOPT_TELNETOPTIONS https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L413
                //Pending option format validation <option=value> and options validations TTYPE, XDISPLOC, NEW_ENV...
                if (!is_array($Value))
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be an array value';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                break;
                //PHP integration exclusive Options
            case 2: //CURLINFO_HEADER_OUT https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L856
            case 19913: //CURLOPT_RETURNTRANSFER https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L338
                if (!is_bool($Value))
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be a boolean value';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                break;
            //case 99002: //CURLOPT_PASSWDFUNCTION -Unable to find it in PHP 8.1
            //    if (!is_array($Value))
            //    {
            //        $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be an array(cURLHandle, string) value';
            //        ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
            //    }
            //    else
            //    {
            //        if (!is_object($Value[0])||(!is_a($Value[0],'CurlHandle'))||(!is_string($Value[1])))
            //        {
            //            $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be an array(cURLHandle, string) value';
            //            ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
            //        }
            //    }
            //    break;
            //case 99003: //CURLOPT_MUTE -Unable to find it in PHP 8.1**
            //    cURLWarn($Key, FALSE, FALSE, '7.15.5#CURLOPT_RETURNTRANSFER');
            //    if (!is_bool($Value)||!FNAcURLVersion('7.15.5'))
            //    {
            //        $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be a Boolean, using cURL < 7.15.5';
            //        ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
            //    }
            //    break;
            case -1: //CURLOPT_SAFE_UPLOAD https://github.com/php/php-src/blob/master/ext/curl/curl.stub.php#L3529
                //Always TRUE
                if (!is_bool($Value)||$Value !== TRUE)
                {
                    $BadcURLOptions[$Key] = 'Using '.cURLOptionConstantToLiteral($Key).' requires it to be a Boolean, valued TRUE';
                    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                }
                break;
                //Old/Deprecated Options in libcurl that PHP still uses
                //CURLOPT_FTPASCII only shows on docs...
                //case 'CURLOPT_FTPASCII':
            //    $BadcURLOptions[$Key] = $Key.' is an alias of CURLOPT_TRANSFERTEXT. Use that instead.';
            //    ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
            //    break;
            default:
                $BadcURLOptions[$Key] = 'Unknown option: '.$Key;
                ErrorLog($BadcURLOptions[$Key], E_USER_ERROR);
                break;
        } //End option parsing
    } //End for each option
    if (empty($BadcURLOptions))
    {
        return TRUE;
    }
    else
    {
        return FALSE;
    }
}

/**
 * cURLOptionConstantToLiteral turns standard int cURL option constants to option literals
 * @param   int     $cURLConstant
 * @return  string  The error literal
 * @since 0.0.9
 * @todo    transform
 * @see
 */
function cURLOptionConstantToLiteral($cURLConstant)
{
    if (DEBUGMODE)
    {
        echo date("Y-m-d H:i:s").' -> cURLOptionConstantToLiteral '.PHP_EOL;
    }

    switch ($cURLConstant)
    {
        case -1: //CURLOPT_SAFE_UPLOAD ***PHP EXCLUSIVE*** ***Always TRUE***
            return 'CURLOPT_SAFE_UPLOAD';
        case 2: //CURLINFO_HEADER_OUT ***PHP EXCLUSIVE***
            return 'CURLINFO_HEADER_OUT';
        case 3: //CURLOPT_PORT
            return 'CURLOPT_PORT';
        //8 is not used
        case 13: //CURLOPT_TIMEOUT
            return 'CURLOPT_TIMEOUT';
        case 14: //CURLOPT_INFILESIZE
            return 'CURLOPT_INFILESIZE';
        case 19: //CURLOPT_LOW_SPEED_LIMIT
            return 'CURLOPT_LOW_SPEED_LIMIT';
        case 20: //CURLOPT_LOW_SPEED_TIME
            return 'CURLOPT_LOW_SPEED_TIME';
        case 21: //CURLOPT_RESUME_FROM
            return 'CURLOPT_RESUME_FROM';
        case 27: //CURLOPT_CRLF
            return 'CURLOPT_CRLF';
        //30 does not appear on curl.h. Deprecated?
        case 32: //CURLOPT_SSLVERSION
            return 'CURLOPT_SSLVERSION';
        case 33: //CURLOPT_TIMECONDITION
            return 'CURLOPT_TIMECONDITION';
        case 34: //CURLOPT_TIMEVALUE
            return 'CURLOPT_TIMEVALUE';
        //35 = OBSOLETE
        //38 is not used
        //40 = OBSOLETE
        case 41: //CURLOPT_VERBOSE
            return 'CURLOPT_VERBOSE';
        case 42: //CURLOPT_HEADER
            return 'CURLOPT_HEADER';
        case 43: //CURLOPT_NOPROGRESS
            return 'CURLOPT_NOPROGRESS';
        case 44: //CURLOPT_NOBODY
            return 'CURLOPT_NOBODY';
        case 45: //CURLOPT_FAILONERROR
            return 'CURLOPT_FAILONERROR';
        case 46: //CURLOPT_UPLOAD
            return 'CURLOPT_UPLOAD';
        case 47: //CURLOPT_POST
            return 'CURLOPT_POST';
        case 48: //CURLOPT_DIRLISTONLY or CURLOPT_FTPLISTONLY
            if (FNAcURLVersion('7.17.0') === TRUE)
            {
                //If we are using a really old lib
                return 'CURLOPT_FTPLISTONLY';
            }
            else
            {
                return 'CURLOPT_DIRLISTONLY or CURLOPT_FTPLISTONLY';
            }
            break;
        //49 does not appear on curl.h. Deprecated?
        case 50: //CURLOPT_APPEND
            return 'CURLOPT_APPEND';
        case 51: //CURLOPT_NETRC
            return 'CURLOPT_NETRC';
        case 52: //CURLOPT_FOLLOWLOCATION
            return 'CURLOPT_FOLLOWLOCATION';
        case 53: //CURLOPT_TRANSFERTEXT
            return 'CURLOPT_TRANSFERTEXT';
        case 54: //CURLOPT_PUT
            return 'CURLOPT_PUT';
        //55 = OBSOLETE
        case 58: //CURLOPT_AUTOREFERER
            return 'CURLOPT_AUTOREFERER';
        case 59: //CURLOPT_PROXYPORT
            return 'CURLOPT_PROXYPORT';
        case 60: //CURLOPT_POSTFIELDSIZE **UNDOCUMENTED AS OF PHP 8.1.0**
            return 'CURLOPT_POSTFIELDSIZE';
        case 61: //CURLOPT_HTTPPROXYTUNNEL
            return 'CURLOPT_HTTPPROXYTUNNEL';
        case 64: //CURLOPT_SSL_VERIFYPEER
            return 'CURLOPT_SSL_VERIFYPEER';
        //66 = OBSOLETE
        //67 = OBSOLETE
        case 68: //CURLOPT_MAXREDIRS
            return 'CURLOPT_MAXREDIRS';
        case 69: //CURLOPT_FILETIME
            return 'CURLOPT_FILETIME';
        case 71: //CURLOPT_MAXCONNECTS
            return 'CURLOPT_MAXCONNECTS';
        //72 = OBSOLETE
        //73 = OBSOLETE
        case 74: //CURLOPT_FRESH_CONNECT
            return 'CURLOPT_FRESH_CONNECT';
        case 75: //CURLOPT_FORBID_REUSE
            return 'CURLOPT_FORBID_REUSE';
        case 78: //CURLOPT_CONNECTTIMEOUT
            return 'CURLOPT_CONNECTTIMEOUT';
        case 80: //CURLOPT_HTTPGET
            return 'CURLOPT_HTTPGET';
        case 81: //CURLOPT_SSL_VERIFYHOST
            return 'CURLOPT_SSL_VERIFYHOST';
        case 84: //CURLOPT_HTTP_VERSION
            return 'CURLOPT_HTTP_VERSION';
        case 85: //CURLOPT_FTP_USE_EPSV
            return 'CURLOPT_FTP_USE_EPSV';
        case 90: //CURLOPT_SSLENGINE_DEFAULT
            return 'CURLOPT_SSLENGINE_DEFAULT';
        case 91: //CURLOPT_DNS_USE_GLOBAL_CACHE
            return 'CURLOPT_DNS_USE_GLOBAL_CACHE';
        case 92: //CURLOPT_DNS_CACHE_TIMEOUT
            return 'CURLOPT_DNS_CACHE_TIMEOUT';
        case 96: //CURLOPT_COOKIESESSION
            return 'CURLOPT_COOKIESESSION';
        case 98: //CURLOPT_BUFFERSIZE
            return 'CURLOPT_BUFFERSIZE';
        case 99: //CURLOPT_NOSIGNAL
            return 'CURLOPT_NOSIGNAL';
        case 101: //CURLOPT_PROXYTYPE
            return 'CURLOPT_PROXYTYPE';
        case 105: //CURLOPT_UNRESTRICTED_AUTH
            return 'CURLOPT_UNRESTRICTED_AUTH';
        case 106: //CURLOPT_FTP_USE_EPRT
            return 'CURLOPT_FTP_USE_EPRT';
        case 107: //CURLOPT_HTTPAUTH
            return 'CURLOPT_HTTPAUTH';
        case 110: //CURLOPT_FTP_CREATE_MISSING_DIRS
            return 'CURLOPT_FTP_CREATE_MISSING_DIRS';
        case 111: //CURLOPT_PROXYAUTH
            return 'CURLOPT_PROXYAUTH';
        case 112: //CURLOPT_SERVER_RESPONSE_TIMEOUT or CURLOPT_FTP_RESPONSE_TIMEOUT
            if (FNAcURLVersion('7.20.0') === TRUE)
            {
                //If we are using a really old lib
                return 'CURLOPT_FTP_RESPONSE_TIMEOUT';
            }
            else
            {
                return 'CURLOPT_SERVER_RESPONSE_TIMEOUT or CURLOPT_FTP_RESPONSE_TIMEOUT';
            }
            break;
        case 113: //CURLOPT_IPRESOLVE
            return 'CURLOPT_IPRESOLVE';
        case 114: //CURLOPT_MAXFILESIZE
            return 'CURLOPT_MAXFILESIZE';
        case 119: //CURLOPT_USE_SSL or CURLOPT_FTP_SSL
            if (FNAcURLVersion('7.16.3') === TRUE)
            {
                //If we are using a really old lib
                return 'CURLOPT_FTP_SSL';
            }
            else
            {
                return 'CURLOPT_USE_SSL or CURLOPT_FTP_SSL';
            }
            break;
        case 121: //CURLOPT_TCP_NODELAY
            return 'CURLOPT_TCP_NODELAY';
        //122 OBSOLETE, used in 7.12.3. Gone in 7.13.0
        //123 OBSOLETE. Gone in 7.16.0
        //124 OBSOLETE, used in 7.12.3. Gone in 7.13.0
        //125 OBSOLETE, used in 7.12.3. Gone in 7.13.0
        //126 OBSOLETE, used in 7.12.3. Gone in 7.13.0
        //127 OBSOLETE. Gone in 7.16.0
        //128 OBSOLETE. Gone in 7.16.0
        case 129: //CURLOPT_FTPSSLAUTH
            return 'CURLOPT_FTPSSLAUTH';
        case 136: //CURLOPT_IGNORE_CONTENT_LENGTH
            return 'CURLOPT_IGNORE_CONTENT_LENGTH';
        case 137: //CURLOPT_FTP_SKIP_PASV_IP
            return 'CURLOPT_FTP_FILEMETHOD';
        case 138: //CURLOPT_FTP_FILEMETHOD
            return 'CURLOPT_FTP_FILEMETHOD';
        case 139: //CURLOPT_LOCALPORT
            return 'CURLOPT_LOCALPORT';
        case 140: //CURLOPT_LOCALPORTRANGE
            return 'CURLOPT_LOCALPORTRANGE';
        case 141: //CURLOPT_CONNECT_ONLY
            return 'CURLOPT_CONNECT_ONLY';
        case 150: //CURLOPT_SSL_SESSIONID_CACHE
            return 'CURLOPT_SSL_SESSIONID_CACHE';
        case 151: //CURLOPT_SSH_AUTH_TYPES
            return 'CURLOPT_SSH_AUTH_TYPES';
        case 154: //CURLOPT_FTP_SSL_CCC
            return 'CURLOPT_FTP_SSL_CCC';
        case 155: //CURLOPT_TIMEOUT_MS
            return 'CURLOPT_TIMEOUT_MS';
        case 156: //CURLOPT_CONNECTTIMEOUT_MS
            return 'CURLOPT_CONNECTTIMEOUT_MS';
        case 157: //CURLOPT_HTTP_TRANSFER_DECODING
            return 'CURLOPT_HTTP_TRANSFER_DECODING';
        case 158: //CURLOPT_HTTP_CONTENT_DECODING
            return 'CURLOPT_HTTP_CONTENT_DECODING';
        case 159: //CURLOPT_NEW_FILE_PERMS
            return 'CURLOPT_NEW_FILE_PERMS';
        case 160: //CURLOPT_NEW_DIRECTORY_PERMS
            return 'CURLOPT_NEW_DIRECTORY_PERMS';
        case 161: //CURLOPT_POSTREDIR
            return 'CURLOPT_POSTREDIR';
        case 166: //CURLOPT_PROXY_TRANSFER_MODE
            return 'CURLOPT_PROXY_TRANSFER_MODE';
        case 171: //CURLOPT_ADDRESS_SCOPE
            return 'CURLOPT_ADDRESS_SCOPE';
        case 172: //CURLOPT_CERTINFO
            return 'CURLOPT_CERTINFO';
        case 178: //CURLOPT_TFTP_BLKSIZE
            return 'CURLOPT_TFTP_BLKSIZE';
        case 180: //CURLOPT_SOCKS5_GSSAPI_NEC
            return 'CURLOPT_SOCKS5_GSSAPI_NEC';
        case 181: //CURLOPT_PROTOCOLS
            return 'CURLOPT_PROTOCOLS';
        case 182: //CURLOPT_REDIR_PROTOCOLS
            return 'CURLOPT_REDIR_PROTOCOLS';
        case 188: //CURLOPT_FTP_USE_PRET
            return 'CURLOPT_FTP_USE_PRET';
        case 189: //CURLOPT_RTSP_REQUEST
            return 'CURLOPT_RTSP_REQUEST';
        case 193: //CURLOPT_RTSP_CLIENT_CSEQ
            return 'CURLOPT_RTSP_CLIENT_CSEQ';
        case 194: //CURLOPT_RTSP_SERVER_CSEQ
            return 'CURLOPT_RTSP_SERVER_CSEQ';
        case 197: //CURLOPT_WILDCARDMATCH
            return 'CURLOPT_WILDCARDMATCH';
        case 207: //CURLOPT_TRANSFER_ENCODING
            return 'CURLOPT_TRANSFER_ENCODING';
        case 210: //CURLOPT_GSSAPI_DELEGATION
            return 'CURLOPT_GSSAPI_DELEGATION';
        case 212: //CURLOPT_ACCEPTTIMEOUT_MS
            return 'CURLOPT_ACCEPTTIMEOUT_MS';
        case 213: //CURLOPT_TCP_KEEPALIVE
            return 'CURLOPT_TCP_KEEPALIVE';
        case 214: //CURLOPT_TCP_KEEPIDLE
            return 'CURLOPT_TCP_KEEPIDLE';
        case 215: //CURLOPT_TCP_KEEPINTVL
            return 'CURLOPT_TCP_KEEPINTVL';
        case 216: //CURLOPT_SSL_OPTIONS
            return 'CURLOPT_SSL_OPTIONS';
        case 218: //CURLOPT_SASL_IR
            return 'CURLOPT_SASL_IR';
        case 225: //CURLOPT_SSL_ENABLE_NPN
            return 'CURLOPT_SSL_ENABLE_NPN';
        case 226: //CURLOPT_SSL_ENABLE_ALPN
            return 'CURLOPT_SSL_ENABLE_ALPN';
        case 227: //CURLOPT_EXPECT_100_TIMEOUT_MS
            return 'CURLOPT_EXPECT_100_TIMEOUT_MS';
        case 229: //CURLOPT_HEADEROPT
            return 'CURLOPT_HEADEROPT';
        case 232: //CURLOPT_SSL_VERIFYSTATUS
            return 'CURLOPT_SSL_VERIFYSTATUS';
        case 233: //CURLOPT_SSL_FALSESTART
            return 'CURLOPT_SSL_FALSESTART';
        case 234: //CURLOPT_PATH_AS_IS
            return 'CURLOPT_PATH_AS_IS';
        case 237: //CURLOPT_PIPEWAIT
            return 'CURLOPT_PIPEWAIT';
        case 239: //CURLOPT_STREAM_WEIGHT
            return 'CURLOPT_STREAM_WEIGHT';
        case 242: //CURLOPT_TFTP_NO_OPTIONS
            return 'CURLOPT_TFTP_NO_OPTIONS';
        case 244: //CURLOPT_TCP_FASTOPEN
            return 'CURLOPT_TCP_FASTOPEN';
        case 245: //CURLOPT_KEEP_SENDING_ON_ERROR
            return 'CURLOPT_KEEP_SENDING_ON_ERROR';
        case 248: //CURLOPT_PROXY_SSL_VERIFYPEER
            return 'CURLOPT_PROXY_SSL_VERIFYPEER';
        case 249: //CURLOPT_PROXY_SSL_VERIFYHOST
            return 'CURLOPT_PROXY_SSL_VERIFYHOST';
        case 250: //CURLOPT_PROXY_SSLVERSION
            return 'CURLOPT_PROXY_SSLVERSION';
        case 261: //CURLOPT_PROXY_SSL_OPTIONS
            return 'CURLOPT_PROXY_SSL_OPTIONS';
        case 265: //CURLOPT_SUPPRESS_CONNECT_HEADERS
            return 'CURLOPT_SUPPRESS_CONNECT_HEADERS';
        case 267: //CURLOPT_SOCKS5_AUTH
            return 'CURLOPT_SOCKS5_AUTH';
        case 268: //CURLOPT_SSH_COMPRESSION
            return 'CURLOPT_SSH_COMPRESSION';
        case 271: //CURLOPT_HAPPY_EYEBALLS_TIMEOUT_MS
            return 'CURLOPT_HAPPY_EYEBALLS_TIMEOUT_MS';
        case 274: //CURLOPT_HAPROXYPROTOCOL
            return 'CURLOPT_HAPROXYPROTOCOL';
        case 275: //CURLOPT_DNS_SHUFFLE_ADDRESSES
            return 'CURLOPT_DNS_SHUFFLE_ADDRESSES';
        case 278: //CURLOPT_DISALLOW_USERNAME_IN_URL
            return 'CURLOPT_DISALLOW_USERNAME_IN_URL';
        case 280: //CURLOPT_UPLOAD_BUFFERSIZE
            return 'CURLOPT_UPLOAD_BUFFERSIZE';
        case 281: //CURLOPT_UPKEEP_INTERVAL_MS
            return 'CURLOPT_UPKEEP_INTERVAL_MS';
        case 282: //CURLOPT_CURLU **UNDOCUMENTED AS OF PHP 8.1.0**
            return 'CURLOPT_CURLU';
        case 285: //CURLOPT_HTTP09_ALLOWED
            return 'CURLOPT_HTTP09_ALLOWED';
        case 286: //CURLOPT_ALTSVC_CTRL
            return 'CURLOPT_ALTSVC_CTRL';
        case 288: //CURLOPT_MAXAGE_CONN
            return 'CURLOPT_MAXAGE_CONN';
        case 290: //CURLOPT_MAIL_RCPT_ALLLOWFAILS
            return 'CURLOPT_MAIL_RCPT_ALLLOWFAILS';
        case 299: //CURLOPT_HSTS_CTRL
            return 'CURLOPT_HSTS_CTRL';
        case 306: //CURLOPT_DOH_SSL_VERIFYPEER
            return 'CURLOPT_DOH_SSL_VERIFYPEER';
        case 307: //CURLOPT_DOH_SSL_VERIFYHOST
            return 'CURLOPT_DOH_SSL_VERIFYHOST';
        case 308: //CURLOPT_DOH_SSL_VERIFYSTATUS
            return 'CURLOPT_DOH_SSL_VERIFYSTATUS';
        case 314: //CURLOPT_MAXLIFETIME_CONN
            return 'CURLOPT_MAXLIFETIME_CONN';
        case 315: //CURLOPT_MIME_OPTIONS **UNDOCUMENTED AS OF PHP 8.1.0**
            return 'CURLOPT_MIME_OPTIONS';
        case 320: //CURLOPT_WS_OPTIONS **UNDOCUMENTED AS OF PHP 8.1.0**
            return 'CURLOPT_WS_OPTIONS';
        case 321: //CURLOPT_CA_CACHE_TIMEOUT  **UNDOCUMENTED AS OF PHP 8.1.0**
            return 'CURLOPT_CA_CACHE_TIMEOUT';
        case 322: //CURLOPT_QUICK_EXIT  **UNDOCUMENTED AS OF PHP 8.1.0**
            return 'CURLOPT_QUICK_EXIT';
        case 10001: //CURLOPT_WRITEDATA **UNDOCUMENTED AS OF PHP 8.1.0**
            return 'CURLOPT_WRITEDATA';
        case 10002: //CURLOPT_URL
            return 'CURLOPT_URL';
        case 10004: //CURLOPT_PROXY
            return 'CURLOPT_PROXY';
        case 10005: //CURLOPT_USERPWD
            return 'CURLOPT_USERPWD';
        case 10006: //CURLOPT_PROXYUSERPWD
            return 'CURLOPT_PROXYUSERPWD';
        case 10007: //CURLOPT_RANGE
            return 'CURLOPT_RANGE';
        case 10009: //CURLOPT_READDATA
            return 'CURLOPT_READDATA';
        case 10010: //CURLOPT_ERRORBUFFER **UNDOCUMENTED AS OF PHP 8.1.0**
            return 'CURLOPT_ERRORBUFFER';
        case 10015: //CURLOPT_POSTFIELDS
            return 'CURLOPT_POSTFIELDS';
        case 10016: //CURLOPT_REFERER
            return 'CURLOPT_REFERER';
        case 10017: //CURLOPT_FTPPORT
            return 'CURLOPT_FTPPORT';
        case 10018: //CURLOPT_USERAGENT
            return 'CURLOPT_USERAGENT';
        case 10022: //CURLOPT_COOKIE
            return 'CURLOPT_COOKIE';
        case 10023: //CURLOPT_HTTPHEADER
            return 'CURLOPT_HTTPHEADER';
        case 10024: //CURLOPT_HTTPPOST **UNDOCUMENTED AS OF PHP 8.1.0**
            return 'CURLOPT_HTTPPOST';
        case 10025: //CURLOPT_SSLCERT
            return 'CURLOPT_SSLCERT';
        case 10026: //CURLOPT_KEYPASSWD
            if (FNAcURLVersion('7.9.2') === TRUE)
            {
                //If we are using a really old lib
                return 'CURLOPT_SSLCERTPASSWD';
            }
            if (FNAcURLVersion('7.16.4') === TRUE)
            {
                return 'CURLOPT_SSLKEYPASSWD or CURLOPT_SSLCERTPASSWD';
            }
            else
            {
                return 'CURLOPT_KEYPASSWD or CURLOPT_SSLKEYPASSWD or CURLOPT_SSLCERTPASSWD';
            }
            break;
        case 10028: //CURLOPT_QUOTE
            return 'CURLOPT_QUOTE';
        case 10029: //CURLOPT_HEADERDATA  **UNDOCUMENTED AS OF PHP 8.1.0**
            return 'CURLOPT_HEADERDATA';
        case 10031: //CURLOPT_COOKIEFILE
            return 'CURLOPT_COOKIEFILE';
        case 10036: //CURLOPT_CUSTOMREQUEST'
            return 'CURLOPT_CUSTOMREQUEST';
        case 10037: //CURLOPT_STDERR
            return 'CURLOPT_STDERR';
        case 10039: //CURLOPT_POSTQUOTE
            return 'CURLOPT_POSTQUOTE';
        case 10057: //CURLOPT_XFERINFODATA or CURLOPT_PROGRESSDATA
            if (FNAcURLVersion('7.32.0') === TRUE)
            {
                //If we are using a really old lib
                return 'CURLOPT_PROGRESSDATA';
            }
            else
            {
                return 'CURLOPT_XFERINFODATA or CURLOPT_PROGRESSDATA';
            }
            break;
        case 10062: //CURLOPT_INTERFACE
            return 'CURLOPT_INTERFACE';
        case 10063: //CURLOPT_KRBLEVEL
            return 'CURLOPT_KRBLEVEL';
        case 10065: //CURLOPT_CAINFO
            return 'CURLOPT_CAINFO';
        case 10070: //CURLOPT_TELNETOPTIONS
            return 'CURLOPT_TELNETOPTIONS';
        case 10076: //CURLOPT_RANDOM_FILE
            return 'CURLOPT_RANDOM_FILE';
        case 10077: //CURLOPT_EGDSOCKET
            return 'CURLOPT_EGDSOCKET';
        case 10082: //CURLOPT_COOKIEJAR
            return 'CURLOPT_COOKIEJAR';
        case 10083: //CURLOPT_SSL_CIPHER_LIST
            return 'CURLOPT_SSL_CIPHER_LIST';
        case 10086: //CURLOPT_SSLCERTTYPE
            return 'CURLOPT_SSLCERTTYPE';
        case 10087: //CURLOPT_SSLKEY
            return 'CURLOPT_SSLKEY';
        case 10088: //CURLOPT_SSLKEYTYPE
            return 'CURLOPT_SSLKEYTYPE';
        case 10089: //CURLOPT_SSLENGINE
            return 'CURLOPT_SSLENGINE';
        case 10093: //CURLOPT_PREQUOTE
            return 'CURLOPT_PREQUOTE';
        case 10095: //CURLOPT_DEBUGDATA  **UNDOCUMENTED AS OF PHP 8.1.0**
            return 'CURLOPT_DEBUGDATA';
        case 10097: //CURLOPT_CAPATH
            return 'CURLOPT_CAPATH';
        case 10100: //CURLOPT_SHARE
            return 'CURLOPT_SHARE';
        case 10102: //CURLOPT_ACCEPT_ENCODING
            return 'CURLOPT_ACCEPT_ENCODING';
        case 10103: //CURLOPT_PRIVATE
            return 'CURLOPT_PRIVATE';
        case 10104: //CURLOPT_HTTP200ALIASES
            return 'CURLOPT_HTTP200ALIASES';
        case 10109: //CURLOPT_SSL_CTX_DATA  **UNDOCUMENTED AS OF PHP 8.1.0**
            return 'CURLOPT_SSL_CTX_DATA';
        case 10118: //CURLOPT_NETRC_FILE
            return 'CURLOPT_NETRC_FILE';
        case 10131: //CURLOPT_IOCTLDATA   **UNDOCUMENTED AS OF PHP 8.1.0**
            return 'CURLOPT_IOCTLDATA';
        case 10134: //CURLOPT_FTP_ACCOUNT
            return 'CURLOPT_FTP_FILEMETHOD';
        case 10135: //CURLOPT_COOKIELIST
            return 'CURLOPT_COOKIELIST';
        case 10147: //CURLOPT_FTP_ALTERNATIVE_TO_USER
            return 'CURLOPT_FTP_ALTERNATIVE_TO_USER';
        case 10149: //CURLOPT_SOCKOPTDATA **UNDOCUMENTED AS OF PHP 8.1.0**
            return 'CURLOPT_SOCKOPTDATA';
        case 10152: //CURLOPT_SSH_PUBLIC_KEYFILE
            return 'CURLOPT_SSH_PUBLIC_KEYFILE';
        case 10153: //CURLOPT_SSH_PRIVATE_KEYFILE
            return 'CURLOPT_SSH_PRIVATE_KEYFILE';
        case 10162: //CURLOPT_SSH_HOST_PUBLIC_KEY_MD5
            return 'CURLOPT_SSH_HOST_PUBLIC_KEY_MD5';
        case 10164: //CURLOPT_OPENSOCKETDATA **UNDOCUMENTED AS OF PHP 8.1.0**
            return 'CURLOPT_OPENSOCKETDATA';
        case 10165: //CURLOPT_COPYPOSTFIELDS **UNDOCUMENTED AS OF PHP 8.1.0**
            return 'CURLOPT_COPYPOSTFIELDS';
        case 10168: //CURLOPT_SEEKDATA **UNDOCUMENTED AS OF PHP 8.1.0**
            return 'CURLOPT_SEEKDATA';
        case 10169: //CURLOPT_CRLFILE
            return 'CURLOPT_CRLFILE';
        case 10170: //CURLOPT_ISSUERCERT
            return 'CURLOPT_ISSUERCERT';
        case 10173: //CURLOPT_USERNAME
            return 'CURLOPT_USERNAME';
        case 10174: //CURLOPT_PASSWORD
            return 'CURLOPT_PASSWORD';
        case 10175: //CURLOPT_PROXYUSERNAME
            return 'CURLOPT_PROXYUSERNAME';
        case 10176: //CURLOPT_PROXYPASSWORD
            return 'CURLOPT_PROXYPASSWORD';
        case 10177: //CURLOPT_NOPROXY
            return 'CURLOPT_NOPROXY';
        case 10179: //CURLOPT_SOCKS5_GSSAPI_SERVICE
            return 'CURLOPT_SOCKS5_GSSAPI_SERVICE';
        case 10183: //CURLOPT_SSH_KNOWNHOSTS
            return 'CURLOPT_SSH_KNOWNHOSTS';
        case 10185: //CURLOPT_SSH_KEYDATA **UNDOCUMENTED AS OF PHP 8.1.0**
            return 'CURLOPT_SSH_KEYDATA';
        case 10186: //CURLOPT_MAIL_FROM
            return 'CURLOPT_MAIL_FROM';
        case 10187: //CURLOPT_MAIL_RCPT
            return 'CURLOPT_MAIL_RCPT';
        case 10190: //CURLOPT_RTSP_SESSION_ID
            return 'CURLOPT_RTSP_SESSION_ID';
        case 10191: //CURLOPT_RTSP_STREAM_URI
            return 'CURLOPT_RTSP_STREAM_URI';
        case 10192: //CURLOPT_RTSP_TRANSPORT
            return 'CURLOPT_RTSP_TRANSPORT';
        case 10195: //CURLOPT_INTERLEAVEDATA **UNDOCUMENTED AS OF PHP 8.1.0**
            return 'CURLOPT_INTERLEAVEDATA';
        case 10201: //CURLOPT_CHUNK_DATA **UNDOCUMENTED AS OF PHP 8.1.0**
            return 'CURLOPT_CHUNK_DATA';
        case 10202: //CURLOPT_FNMATCH_DATA **UNDOCUMENTED AS OF PHP 8.1.0**
            return 'CURLOPT_FNMATCH_DATA';
        case 10203: //CURLOPT_RESOLVE
            return 'CURLOPT_RESOLVE';
        case 10204: //CURLOPT_TLSAUTH_USERNAME
            return 'CURLOPT_TLSAUTH_USERNAME';
        case 10205: //CURLOPT_TLSAUTH_PASSWORD
            return 'CURLOPT_TLSAUTH_PASSWORD';
        case 10206: //CURLOPT_TLSAUTH_TYPE
            return 'CURLOPT_TLSAUTH_TYPE';
        case 10209: //CURLOPT_CLOSESOCKETDATA **UNDOCUMENTED AS OF PHP 8.1.0**
            return 'CURLOPT_CLOSESOCKETDATA';
        case 10211: //CURLOPT_DNS_SERVERS
            return 'CURLOPT_DNS_SERVERS';
        case 10217: //CURLOPT_MAIL_AUTH
            return 'CURLOPT_MAIL_AUTH';
        case 10220: //CURLOPT_XOAUTH2_BEARER
            return 'CURLOPT_XOAUTH2_BEARER';
        case 10221: //CURLOPT_DNS_INTERFACE
            return 'CURLOPT_DNS_INTERFACE';
        case 10222: //CURLOPT_DNS_LOCAL_IP4
            return 'CURLOPT_DNS_LOCAL_IP4';
        case 10223: //CURLOPT_DNS_LOCAL_IP6
            return 'CURLOPT_DNS_LOCAL_IP6';
        case 10224: //CURLOPT_LOGIN_OPTIONS
            return 'CURLOPT_LOGIN_OPTIONS';
        case 10228: //CURLOPT_PROXYHEADER
            return 'CURLOPT_PROXYHEADER';
        case 10230: //CURLOPT_PINNEDPUBLICKEY
            return 'CURLOPT_PINNEDPUBLICKEY';
        case 10231: //CURLOPT_UNIX_SOCKET_PATH
            return 'CURLOPT_UNIX_SOCKET_PATH';
        case 10235: //CURLOPT_PROXY_SERVICE_NAME
            return 'CURLOPT_PROXY_SERVICE_NAME';
        case 10236: //CURLOPT_SERVICE_NAME
            return 'CURLOPT_SERVICE_NAME';
        case 10238: //CURLOPT_DEFAULT_PROTOCOL
            return 'CURLOPT_DEFAULT_PROTOCOL';
        case 10240: //CURLOPT_STREAM_DEPENDS **UNDOCUMENTED AS OF PHP 8.1.0**
            return 'CURLOPT_STREAM_DEPENDS';
        case 10241: //CURLOPT_STREAM_DEPENDS_E **UNDOCUMENTED AS OF PHP 8.1.0**
            return 'CURLOPT_STREAM_DEPENDS_E';
        case 10243: //CURLOPT_CONNECT_TO
            return 'CURLOPT_CONNECT_TO';
        case 10246: //CURLOPT_PROXY_CAINFO
            return 'CURLOPT_PROXY_CAINFO';
        case 10247: //CURLOPT_PROXY_CAPATH
            return 'CURLOPT_PROXY_CAPATH';
        case 10251: //CURLOPT_PROXY_TLSAUTH_USERNAME
            return 'CURLOPT_PROXY_TLSAUTH_USERNAME';
        case 10252: //CURLOPT_PROXY_TLSAUTH_PASSWORD
            return 'CURLOPT_PROXY_TLSAUTH_PASSWORD';
        case 10253: //CURLOPT_PROXY_TLSAUTH_TYPE
            return 'CURLOPT_PROXY_TLSAUTH_TYPE';
        case 10254: //CURLOPT_PROXY_SSLCERT
            return 'CURLOPT_PROXY_SSLCERT';
        case 10255: //CURLOPT_PROXY_SSLCERTTYPE
            return 'CURLOPT_PROXY_SSLCERTTYPE';
        case 10256: //CURLOPT_PROXY_SSLKEY
            return 'CURLOPT_PROXY_SSLKEY';
        case 10257: //CURLOPT_PROXY_SSLKEYTYPE
            return 'CURLOPT_PROXY_SSLKEYTYPE';
        case 10258: //CURLOPT_PROXY_KEYPASSWD
            return 'CURLOPT_PROXY_KEYPASSWD';
        case 10259: //CURLOPT_PROXY_SSL_CIPHER_LIST
            return 'CURLOPT_PROXY_SSL_CIPHER_LIST';
        case 10260: //CURLOPT_PROXY_CRLFILE
            return 'CURLOPT_PROXY_CRLFILE';
        case 10262: //CURLOPT_PRE_PROXY
            return 'CURLOPT_PRE_PROXY';
        case 10263: //CURLOPT_PROXY_PINNEDPUBLICKEY
            return 'CURLOPT_PROXY_PINNEDPUBLICKEY';
        case 10264: //CURLOPT_ABSTRACT_UNIX_SOCKET
            return 'CURLOPT_ABSTRACT_UNIX_SOCKET';
        case 10266: //CURLOPT_REQUEST_TARGET
            return 'CURLOPT_REQUEST_TARGET';
        case 10269: //CURLOPT_MIMEPOST **UNDOCUMENTED AS OF PHP 8.1.0**
            return 'CURLOPT_MIMEPOST';
        case 10273: //CURLOPT_RESOLVER_START_DATA **UNDOCUMENTED AS OF PHP 8.1.0**
            return 'CURLOPT_RESOLVER_START_DATA';
        case 10276: //CURLOPT_TLS13_CIPHERS
            return 'CURLOPT_TLS13_CIPHERS';
        case 10277: //CURLOPT_PROXY_TLS13_CIPHERS
            return 'CURLOPT_PROXY_TLS13_CIPHERS';
        case 10279: //CURLOPT_DOH_URL
            return 'CURLOPT_DOH_URL';
        case 10284: //CURLOPT_TRAILERDATA **UNDOCUMENTED AS OF PHP 8.1.0**
            return 'CURLOPT_TRAILERDATA';
        case 10287: //CURLOPT_ALTSVC
            return 'CURLOPT_ALTSVC';
        case 10289: //CURLOPT_SASL_AUTHZID
            return 'CURLOPT_SASL_AUTHZID';
        case 10296: //CURLOPT_PROXY_ISSUERCERT
            return 'CURLOPT_PROXY_ISSUERCERT';
        case 10298: //CURLOPT_SSL_EC_CURVES
            return 'CURLOPT_SSL_EC_CURVES';
        case 10300: //CURLOPT_HSTS
            return 'CURLOPT_HSTS';
        case 10302: //CURLOPT_HSTSREADDATA **UNDOCUMENTED AS OF PHP 8.1.0**
            return 'CURLOPT_HSTSREADDATA';
        case 10304: //CURLOPT_HSTSWRITEDATA **UNDOCUMENTED AS OF PHP 8.1.0**
            return 'CURLOPT_HSTSWRITEDATA';
        case 10305: //CURLOPT_AWS_SIGV4
            return 'CURLOPT_AWS_SIGV4';
        case 10311: //CURLOPT_SSH_HOST_PUBLIC_KEY_SHA256
            return 'CURLOPT_SSH_HOST_PUBLIC_KEY_SHA256';
        case 10313: //CURLOPT_PREREQDATA **UNDOCUMENTED AS OF PHP 8.1.0**
            return 'CURLOPT_PREREQDATA';
        case 10316: //CURLOPT_SSH_HOSTKEYFUNCTION **UNDOCUMENTED AS OF PHP 8.1.0**
            return 'CURLOPT_SSH_HOSTKEYFUNCTION';
        case 10317: //CURLOPT_SSH_HOSTKEYDATA **UNDOCUMENTED AS OF PHP 8.1.0**
            return 'CURLOPT_SSH_HOSTKEYDATA';
        case 10318: //CURLOPT_PROTOCOLS_STR **UNDOCUMENTED AS OF PHP 8.1.0**
            return 'CURLOPT_PROTOCOLS_STR';
        case 10319: //CURLOPT_REDIR_PROTOCOLS_STR **UNDOCUMENTED AS OF PHP 8.1.0**
            return 'CURLOPT_REDIR_PROTOCOLS_STR';
        case 20011: //CURLOPT_WRITEFUNCTION
            return 'CURLOPT_WRITEFUNCTION';
        case 20012: //CURLOPT_READFUNCTION
            return 'CURLOPT_READFUNCTION';
        case 20056: //CURLOPT_PROGRESSFUNCTION
            return 'CURLOPT_PROGRESSFUNCTION';
        case 20079: //CURLOPT_HEADERFUNCTION
            return 'CURLOPT_HEADERFUNCTION';
        case 20094: //CURLOPT_DEBUGFUNCTION **UNDOCUMENTED AS OF PHP 8.1.0**
            return 'CURLOPT_DEBUGFUNCTION';
        case 20108: //CURLOPT_SSL_CTX_FUNCTION **UNDOCUMENTED AS OF PHP 8.1.0**
            return 'CURLOPT_SSL_CTX_FUNCTION';
        case 20130: //CURLOPT_IOCTLFUNCTION  **UNDOCUMENTED AS OF PHP 8.1.0**
            return 'CURLOPT_IOCTLFUNCTION';
        case 20142: //CURLOPT_CONV_FROM_NETWORK_FUNCTION **UNDOCUMENTED AS OF PHP 8.1.0**
            return 'CURLOPT_CONV_FROM_NETWORK_FUNCTION';
        case 20143: //CURLOPT_CONV_TO_NETWORK_FUNCTION  **UNDOCUMENTED AS OF PHP 8.1.0**
            return 'CURLOPT_CONV_TO_NETWORK_FUNCTION';
        case 20144: //CURLOPT_CONV_FROM_UTF8_FUNCTION **UNDOCUMENTED AS OF PHP 8.1.0**
            return 'CURLOPT_CONV_FROM_NETWORK_FUNCTION';
        case 20148: //CURLOPT_SOCKOPTFUNCTION **UNDOCUMENTED AS OF PHP 8.1.0**
            return 'CURLOPT_SOCKOPTFUNCTION';
        case 20163: //CURLOPT_OPENSOCKETFUNCTION **UNDOCUMENTED AS OF PHP 8.1.0**
            return 'CURLOPT_OPENSOCKETFUNCTION';
        case 20167: //CURLOPT_SEEKFUNCTION **UNDOCUMENTED AS OF PHP 8.1.0**
            return 'CURLOPT_SEEKFUNCTION';
        case 20184: //CURLOPT_SSH_KEYFUNCTION **UNDOCUMENTED AS OF PHP 8.1.0**
            return 'CURLOPT_SSH_KEYFUNCTION';
        case 20196: //CURLOPT_INTERLEAVEFUNCTION **UNDOCUMENTED AS OF PHP 8.1.0**
            return 'CURLOPT_INTERLEAVEFUNCTION';
        case 20198: //CURLOPT_CHUNK_BGN_FUNCTION **UNDOCUMENTED AS OF PHP 8.1.0**
            return 'CURLOPT_CHUNK_BGN_FUNCTION';
        case 20199: //CURLOPT_CHUNK_END_FUNCTION **UNDOCUMENTED AS OF PHP 8.1.0**
            return 'CURLOPT_CHUNK_END_FUNCTION';
        case 20200: //CURLOPT_FNMATCH_FUNCTION
            return 'CURLOPT_FNMATCH_FUNCTION';
        case 20208: //CURLOPT_CLOSESOCKETFUNCTION **UNDOCUMENTED AS OF PHP 8.1.0**
            return 'CURLOPT_CLOSESOCKETFUNCTION';
        case 20219: //CURLOPT_XFERINFOFUNCTION
            return 'CURLOPT_XFERINFOFUNCTION';
        case 20272: //CURLOPT_RESOLVER_START_FUNCTION **UNDOCUMENTED AS OF PHP 8.1.0**
            return 'CURLOPT_RESOLVER_START_FUNCTION';
        case 20283: //CURLOPT_TRAILERFUNCTION **UNDOCUMENTED AS OF PHP 8.1.0**
            return 'CURLOPT_TRAILERFUNCTION';
        case 20301: //CURLOPT_HSTSREADFUNCTION **UNDOCUMENTED AS OF PHP 8.1.0**
            return 'CURLOPT_HSTSREADFUNCTION';
        case 20303: //CURLOPT_HSTSWRITEFUNCTION **UNDOCUMENTED AS OF PHP 8.1.0**
            return 'CURLOPT_HSTSWRITEFUNCTION';
        case 20312: //CURLOPT_PREREQFUNCTION **UNDOCUMENTED AS OF PHP 8.1.0**
            return 'CURLOPT_PREREQFUNCTION';
        case 30115: //CURLOPT_INFILESIZE_LARGE **UNDOCUMENTED AS OF PHP 8.1.0**
            return 'CURLOPT_INFILESIZE_LARGE';
        case 30116: //CURLOPT_RESUME_FROM_LARGE **UNDOCUMENTED AS OF PHP 8.1.0**
            return 'CURLOPT_RESUME_FROM_LARGE';
        case 30117: //CURLOPT_MAXFILESIZE_LARGE
            return 'CURLOPT_MAXFILESIZE_LARGE';
        case 30120: //CURLOPT_POSTFIELDSIZE_LARGE **UNDOCUMENTED AS OF PHP 8.1.0**
            return 'CURLOPT_POSTFIELDSIZE_LARGE';
        case 30145: //CURLOPT_MAX_SEND_SPEED_LARGE
            return 'CURLOPT_MAX_SEND_SPEED_LARGE';
        case 30146: //CURLOPT_MAX_RECV_SPEED_LARGE
            return 'CURLOPT_MAX_RECV_SPEED_LARGE';
        case 30270: //CURLOPT_TIMEVALUE_LARGE
            return 'CURLOPT_UPLOAD_BUFFERSIZE';
        case 40291: //CURLOPT_SSLCERT_BLOB
            return 'CURLOPT_SSLCERT_BLOB';
        case 40292: //CURLOPT_SSLKEY_BLOB
            return 'CURLOPT_SSLKEY_BLOB';
        case 40293: //CURLOPT_PROXY_SSLCERT_BLOB
            return 'CURLOPT_PROXY_SSLCERT_BLOB';
        case 40294: //CURLOPT_PROXY_SSLKEY_BLOB
            return 'CURLOPT_PROXY_SSLKEY_BLOB';
        case 40295: //CURLOPT_ISSUERCERT_BLOB
            return 'CURLOPT_ISSUERCERT_BLOB';
        case 40297: //CURLOPT_PROXY_ISSUERCERT_BLOB
            return 'CURLOPT_PROXY_ISSUERCERT_BLOB';
        case 40309: //CURLOPT_CAINFO_BLOB
            return 'CURLOPT_CAINFO_BLOB';
        case 40310: //CURLOPT_PROXY_CAINFO_BLOB
            return 'CURLOPT_PROXY_CAINFO_BLOB';


        case 19913: //CURLOPT_RETURNTRANSFER ***PHP EXCLUSIVE***
            return 'CURLOPT_RETURNTRANSFER';





        default:
            return 'UNKNOWN_CODE';
    }
}

/**
 * cURLWebServiceCaller
 * Launches a cURL HTTP petition, treats errors and returns the response
 * Follows this convention:(esto es para crear api, no para leer)
 * a)   COLLECTION/SET SCOPE (e.g. /customers)
 *      HTTPMethod   Action         Return Value
 *      POST         Create         201 (Created), 'Location' header with link to /customers/{id} containing new ID.
 *      GET          Read           200 (OK), list of customers. Use pagination, sorting and filtering to navigate big lists.
 *      PUT          Update/Replace 405 (Method Not Allowed), unless you want to update/replace every resource in the entire collection.
 *      PATCH        Update/Modify  405 (Method Not Allowed), unless you want to modify the collection itself.
 *      DELETE       Delete         405 (Method Not Allowed), unless you want to delete the whole collection—not often desirable.
 * 
 * b)   ITEM SCOPE (e.g.  /customers/{id})
 *      HTTPMethod   Action         Return Value
 *      POST         Create         404 (Not Found), 409 (Conflict) if resource already exists.
 *      GET          Read           200 (OK), single customer. 404 (Not Found), if ID not found or invalid -better than using 404 (Bad Request) for invalid ID's which allow fingerprinting-
 *      PUT          Update/Replace 200 (OK) or 204 (No Content). 404 (Not Found), if ID not found or invalid.
 *      PATCH        Update/Modify  200 (OK) or 204 (No Content). 404 (Not Found), if ID not found or invalid.
 *      DELETE       Delete         200 (OK). 404 (Not Found), if ID not found or invalid.
 * 
 * @param   string  $HTTPMethod     One of: 'GET', 'HEAD', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS', 'CONNECT', 'TRACE'
 * @param   string  $URL            The endpoint to call
 * @param   array   $Parameters     The parameters to be included in the call. A 'Vehicle' parameter, if present, will stablish if those are POSTED or included 
 *                                  as part of the URL. If the parameter is not set it will follow the verb
 * @param   int     $Encoding   Use PHP constants:
 *                                  PHP_QUERY_RFC1738 for RFC1738 (really RFC1630) plus_sign application/x-www-form-urlencoded encoding 
 *                                  PHP_QUERY_RFC3986 for RFC3986 section 2.1 (Percent-Encoding) encoding
 * @param   array   $cURLOptions    The cURL options to set in the call. See cURLOptionsValidate for an explanation
 * @param   boolean $Path           Path wont be NULL when GETtting from/PUTting to FILE;
 * @return  mixed   The response from the API in JSON array format or FALSE if something fails
 *                                  HTTPCode:   The HTTP Code given by the server to our request
 *                                  Response:   The server response
 * @since 0.0.9
 * @todo Force URL->UPLOAD->READDATA->INFILESIZE
 * @see
 *      REST Verbs  https://www.restapitutorial.com/lessons/httpmethods.html
 *      Header      http://www.rfcreader.com/#rfc2616_line4596
 * @deprecated  cURL is too sensitive to options order
 */
function cURLWebServiceCaller($HTTPMethod, $URL, $Parameters = NULL, $Encoding = NULL, $cURLOptions = NULL, $Path = NULL)
{
    if (DEBUGMODE)
    {
        echo date("Y-m-d H:i:s").' -> cURLWebServiceCaller '.PHP_EOL;
    }

    echo 'Soy '.$HTTPMethod.PHP_EOL;
    //print_r($cURLOptions);

    $Feedback = array();
    //Parameter validation
    if (!IsValidHTTPMethod($HTTPMethod))
    {
        ErrorLog('Invalid HTTP Method', E_USER_ERROR);

        return FALSE;
    }
    if (!IsValidURI($URL))
    {
        ErrorLog('Invalid URL', E_USER_ERROR);

        return FALSE;
    }
    //Options
    if (!empty($cURLOptions))
    {
        if (!cURLOptionsValidate($cURLOptions))
        {
            ErrorLog('Error validating cURL Options. Review error output', E_USER_WARNING);

            return FALSE;
        }
    }
    //Init cURL lib
    $cURL = curl_init();
    if ($cURL === FALSE)
    {
        ErrorLog('Error initialising cURL', E_USER_ERROR);

        return FALSE;
    }

    //If there are parameters, add them to the URL or use them as POSTFIELDS based on the method
    switch ($HTTPMethod)
    {
        case 'GET':
            echo 'Entro por GET'.PHP_EOL;
            //Accepts an array or an object
            if (!empty($Parameters))
            {
                if (is_array($Parameters)||is_object($Parameters))
                {
                    if (empty($Encoding))
                    {
                        $URL .= '?'.http_build_query($Parameters);
                    }
                    else
                    {
                        $URL .= '?'.http_build_query($Parameters,  NULL, '&', PHP_QUERY_RFC3986);
                    }
                }
                else
                {
                    ErrorLog('Parameters in GET operations must be passed in array or object formats', E_USER_ERROR);

                    return FALSE;
                }
            }
            if (!empty($Path))
            {
                //GET a file
                $TheFile = fopen($Path, 'wb');
                if ($TheFile === FALSE)
                {
                    ErrorLog('Error opening FILE for writing', E_USER_ERROR);

                    return FALSE;
                }

                //File to write to
                if (!curl_setopt($cURL, CURLOPT_FILE,  $TheFile))
                {
                    ErrorLog('Error setting destination file', E_USER_ERROR);

                    return FALSE;
                }
            }
            //Pass URL
            if (!curl_setopt($cURL, CURLOPT_URL,  $URL))
            {
                ErrorLog('Error setting called URL', E_USER_ERROR);

                return FALSE;
            }
            break;
        case 'POST':
            echo 'Entro por POST'.PHP_EOL;
            //Accepts an array or a string
            //Passing an array to CURLOPT_POSTFIELDS will encode the data as multipart/form-data, while passing a URL-encoded string will encode the data
            //as application/x-www-form-urlencoded.
            if (is_array($Parameters)||is_string($Parameters))
            {
                //Set POSTFIELDS
                if (!curl_setopt($cURL, CURLOPT_POSTFIELDS,  $Parameters))
                {
                    ErrorLog('Error setting POSTFIELDS', E_USER_ERROR);

                    return FALSE;
                }
            }
            else
            {
                ErrorLog('Parameters in POST operations must be passed in array or urlencoded string formats', E_USER_ERROR);

                return FALSE;
            }
            //Pass URL
            if (!curl_setopt($cURL, CURLOPT_URL,  $URL))
            {
                ErrorLog('Error setting called URL', E_USER_ERROR);

                return FALSE;
            }
            break;
        case 'PUT':
            echo 'Entro por PUT'.PHP_EOL;
            if (empty($Path))
            {
                ErrorLog('A path to the file to upload must be given on PUT operations', E_USER_ERROR);

                return FALSE;
            }
            else
            {
                $TheFile = fopen($Path, 'rb');
                if ($TheFile === FALSE)
                {
                    ErrorLog('Error opening '.$Path.' for reading', E_USER_ERROR);

                    return FALSE;
                }
                $FileSize = filesize($Path);
                echo 'Size: '.$FileSize.PHP_EOL;
                //Pass URL
                if (!curl_setopt($cURL, CURLOPT_URL,  $URL))
                {
                    ErrorLog('Error setting called URL', E_USER_ERROR);

                    return FALSE;
                }
                //Force PUT by enabling uploading (implies PUT over HTTP)
                if (!curl_setopt($cURL, CURLOPT_UPLOAD, TRUE))
                {
                    ErrorLog('Error setting PUT operation', E_USER_ERROR);

                    return FALSE;
                }
                //File to read from
                if (!curl_setopt($cURL, CURLOPT_READDATA,  $TheFile))
                {
                    ErrorLog('Error reading origin file', E_USER_ERROR);

                    return FALSE;
                }
                //echo 'Tamaño: '.filesize($Origin).PHP_EOL;
                if (!curl_setopt($cURL, CURLOPT_INFILESIZE, $FileSize))
                {
                    ErrorLog('Error setting origin file size', E_USER_ERROR);

                    return FALSE;
                }
            }
            break;
    } //End method selection

    //Set the rest of options
    foreach ($cURLOptions as $Key => $Value)
    {
        if (!is_array($Value))
        {
            $Textd = $Value;
        }
        else
        {
            $Textd = json_encode($Value, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_NUMERIC_CHECK);
        }
        echo  'Setting '.cURLOptionConstantToLiteral($Key).' as '.$Textd.PHP_EOL;
        if (!curl_setopt($cURL, $Key,  $Value))
        {
            ErrorLog('Error setting '.$Key.' as '.$Textd, E_USER_ERROR);

            return FALSE;
        }
    } //End for each option to set
    echo 'Pre-exec'.PHP_EOL;
    //Now curl_exec returns the response on success, FALSE on failure. HTTP errors are successful transactions
    $Response = curl_exec($cURL);
    if ($Response === FALSE)
    {
        //Something failed, so lets capture error message and number
        $ErrMss = curl_error($cURL);
        $ErrNo = curl_errno($cURL);
        $ErrorMessage = 'cURL Error #'.$ErrNo.':'.$ErrMss.' trying to reach: '.$URL;
        ErrorLog($ErrorMessage, E_USER_ERROR);

        return FALSE;
    }
    echo 'Post-exec'.PHP_EOL;
    //Get the HTTP code of the transaction
    $HTTPCode = curl_getinfo($cURL,  CURLINFO_RESPONSE_CODE);
    if ($HTTPCode === FALSE)
    {
        ErrorLog('Error getting transaction info', E_USER_ERROR);

        return FALSE;
    }

    //Close the cURL session, no use from PHP 8 on
    if (PHP_VERSION_ID>=80000)
    {
        curl_close($cURL);
    }

    //Close the file handler
    if (!empty($Path))
    {
        $Closure = fclose($TheFile);
        if ($Closure === FALSE)
        {
            ErrorLog('Error closing FILE', E_USER_ERROR);

            return FALSE;
        }
    }

    //HTTP Code to be processed Upstream, we are only the callers
    $Feedback['HTTPCode'] = $HTTPCode;
    $Feedback['Response'] = $Response;
    $EncodedFB = json_encode($Feedback,  JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_NUMERIC_CHECK);
    if ($EncodedFB === FALSE)
    {
        ErrorLog('Error JSON-encoding server response', E_USER_ERROR);

        return FALSE;
    }

    return $EncodedFB;
}


/**
 * cURLSimpleGET
 * Does a simple HTTP GET operation via cURL using fixed, known to work options. GET retrieves information from the server. Should not modify the data on the 
 * server. It can be cached, bookmarked, and may remain in the browser history. One of the safe methods. Safe methods should not change the state of the server; 
 * the operation performed by this method should be read-only. Safe methods are also idempotent (produce the same result when several identical requests are 
 * made). Pre-determined RFC 1630 application/x-www-form-urlencoded encoding (spaces as plus signs)
 * @param   string  $URL        The URL to consult
 * @param   array   $Parameters Parameters in 'foo' => 'bar' form
 * @return  mixed   The response from the API in JSON array format or FALSE if something fails
 *                      HTTPCode:   The HTTP Code given by the server to our request
 *                      Response:   The server response
 * @since 0.0.9
 * @todo $Debug parameter that spits call and does not do it
 * @see
 */
function cURLSimpleGET($URL, $Parameters = NULL)
{
    if (DEBUGMODE)
    {
        echo date("Y-m-d H:i:s").' -> cURLSimpleGET '.PHP_EOL;
    }

    //Validation
    if (!IsValidURI($URL))
    {
        ErrorLog('Invalid URI', E_USER_ERROR);

        return FALSE;
    }

    //If there are parameters, add them to the URL
    if (!empty($Parameters))
    {
        if (is_array($Parameters)||is_object($Parameters))
        {
            $URL .= '?'.http_build_query($Parameters);
        }
        else
        {
            ErrorLog('Parameters must be passed in array or object formats', E_USER_ERROR);

            return FALSE;
        }
    }
    if (strpos($URL, 'https://monitoringapi.solaredge.com/sites/2296477,2296477/dataPeriod') !== FALSE)
    {
        echo $URL.PHP_EOL;

        die();
    }
    //Init cURL lib
    $cURL = curl_init();
    if ($cURL === FALSE)
    {
        ErrorLog('Error initialising cURL', E_USER_ERROR);

        return FALSE;
    }

    //Pass URL
    if (!curl_setopt($cURL, CURLOPT_URL,  $URL))
    {
        ErrorLog('Error setting called URL', E_USER_ERROR);

        return FALSE;
    }

    //Capture output
    if (!curl_setopt($cURL, CURLOPT_RETURNTRANSFER, TRUE))
    {
        ErrorLog('Error setting CURLOPT_RETURNTRANSFER option', E_USER_ERROR);

        return FALSE;
    }

    //Now curl_exec returns the response on success, FALSE on failure. HTTP errors are successful transactions
    $Response = curl_exec($cURL);
    if ($Response === FALSE)
    {
        //Something failed, so lets capture error message and number
        $ErrMss = curl_error($cURL);
        $ErrNo = curl_errno($cURL);
        $ErrorMessage = 'cURL Error #'.$ErrNo.':'.$ErrMss.' trying to reach: '.$URL;
        ErrorLog($ErrorMessage, E_USER_ERROR);

        return FALSE;
    }

    //Get the HTTP code of the transaction
    $HTTPCode = curl_getinfo($cURL,  CURLINFO_RESPONSE_CODE);
    if ($HTTPCode === FALSE)
    {
        ErrorLog('Error getting transaction info', E_USER_ERROR);

        return FALSE;
    }

    //Close the cURL session, no use from PHP 8 on
    if (PHP_VERSION_ID>=80000)
    {
        curl_close($cURL);
    }

    //HTTP Code to be processed Upstream, we are only the callers
    $Feedback['HTTPCode'] = $HTTPCode;
    $Feedback['Response'] = $Response;
    $EncodedFB = json_encode($Feedback,  JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_NUMERIC_CHECK);
    if ($EncodedFB === FALSE)
    {
        ErrorLog('Error JSON-encoding server response', E_USER_ERROR);

        return FALSE;
    }

    return $EncodedFB;
}

/**
 * cURLSimpleHEAD
 * Does a simple HTTP HEAD operation via cURL using fixed, known to work options. HEAD is Similar to GET, except it transfers the status line and headers only. 
 * Should not modify the data on the server. It cannot be bookmarked and does not remain in the browser history. One of the safe methods. Safe methods should 
 * not change the state of the server; the operation performed by this method should be read-only. Safe methods are also idempotent (produce the same result 
 * when several identical requests are made). 
 * @param   string  $URL        The URL to consult
 * @param   array   $Parameters Parameters in 'foo' => 'bar' form
 * @return  mixed   The response from the API in JSON array format or FALSE if something fails
 *                      HTTPCode:   The HTTP Code given by the server to our request
 *                      Response:   The server response
 * @since 0.0.9
 * @todo
 * @see https://zetcode.com/php/curl/
 */
function cURLSimpleHEAD($URL, $Parameters = NULL)
{
    if (DEBUGMODE)
    {
        echo date("Y-m-d H:i:s").' -> cURLSimpleHEAD '.PHP_EOL;
    }

    //Validation
    if (!IsValidURI($URL))
    {
        ErrorLog('Invalid URI', E_USER_ERROR);

        return FALSE;
    }

    //If there are parameters, add them to the URL
    if (!empty($Parameters))
    {
        if (is_array($Parameters)||is_object($Parameters))
        {
            $URL .= '?'.http_build_query($Parameters);
        }
        else
        {
            ErrorLog('Parameters must be passed in array or object formats', E_USER_ERROR);

            return FALSE;
        }
    }

    //Init cURL lib
    $cURL = curl_init();
    if ($cURL === FALSE)
    {
        ErrorLog('Error initialising cURL', E_USER_ERROR);

        return FALSE;
    }

    //Pass URL
    if (!curl_setopt($cURL, CURLOPT_URL,  $URL))
    {
        ErrorLog('Error setting called URL', E_USER_ERROR);

        return FALSE;
    }

    //Capture output
    if (!curl_setopt($cURL, CURLOPT_RETURNTRANSFER, TRUE))
    {
        ErrorLog('Error setting CURLOPT_RETURNTRANSFER option', E_USER_ERROR);

        return FALSE;
    }

    //Set HEADER options
    if (!curl_setopt($cURL, CURLOPT_HEADER, TRUE))
    {
        ErrorLog('Error setting CURLOPT_HEADER option', E_USER_ERROR);

        return FALSE;
    }

    if (!curl_setopt($cURL, CURLOPT_NOBODY, TRUE))
    {
        ErrorLog('Error setting CURLOPT_NOBODY option', E_USER_ERROR);

        return FALSE;
    }

    //Now curl_exec returns the response on success, FALSE on failure. HTTP errors are successful transactions
    $Response = curl_exec($cURL);
    if ($Response === FALSE)
    {
        //Something failed, so lets capture error message and number
        $ErrMss = curl_error($cURL);
        $ErrNo = curl_errno($cURL);
        $ErrorMessage = 'cURL Error #'.$ErrNo.':'.$ErrMss.' trying to reach: '.$URL;
        ErrorLog($ErrorMessage, E_USER_ERROR);

        return FALSE;
    }

    //Get the HTTP code of the transaction
    $HTTPCode = curl_getinfo($cURL,  CURLINFO_RESPONSE_CODE);
    if ($HTTPCode === FALSE)
    {
        ErrorLog('Error getting transaction info', E_USER_ERROR);

        return FALSE;
    }

    //Close the cURL session, no use from PHP 8 on
    if (PHP_VERSION_ID>=80000)
    {
        curl_close($cURL);
    }

    //HTTP Code to be processed Upstream, we are only the callers
    $Feedback['HTTPCode'] = $HTTPCode;
    $Feedback['Response'] = $Response;
    $EncodedFB = json_encode($Feedback,  JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_NUMERIC_CHECK);
    if ($EncodedFB === FALSE)
    {
        ErrorLog('Error JSON-encoding server response', E_USER_ERROR);

        return FALSE;
    }

    return $EncodedFB;
}

/**
 * cURLSimplePOST
 * Does a simple HTTP POST operation via cURL using fixed, known to work options. POST is used to Send data to the server, including images, JSON strings, file 
 * downloads, etc. It cannot be cached, bookmarked, and not stored in the browser history. Not a safe or idempotent method.
 * @param   string  $URL        The URL to consult
 * @param   array   $Parameters Parameters in 'foo' => 'bar' form
 * @param   boolean $URLEncoded TRUE to post as application/x-www-form-urlencoded, FALSE to post as multipart/form-data
 * @return  mixed   The response from the API in JSON array format or FALSE if something fails
 *                      HTTPCode:   The HTTP Code given by the server to our request
 *                      Response:   The server response
 * @since   0.0.9
 * @todo
 * @see     https://zetcode.com/php/curl/
 *          https://stackoverflow.com/questions/5725430/http-test-server-accepting-get-post-requests
 */
function cURLSimplePOST($URL, $Parameters = NULL, $URLEncoded = FALSE)
{
    if (DEBUGMODE)
    {
        echo date("Y-m-d H:i:s").' -> cURLSimplePOST '.PHP_EOL;
    }

    //Validation
    if (!IsValidURI($URL))
    {
        ErrorLog('Invalid URI', E_USER_ERROR);

        return FALSE;
    }

    //Init cURL lib
    $cURL = curl_init();
    if ($cURL === FALSE)
    {
        ErrorLog('Error initialising cURL', E_USER_ERROR);

        return FALSE;
    }

    //Pass URL
    if (!curl_setopt($cURL, CURLOPT_URL,  $URL))
    {
        ErrorLog('Error setting called URL', E_USER_ERROR);

        return FALSE;
    }

    //Capture output
    if (!curl_setopt($cURL, CURLOPT_RETURNTRANSFER, TRUE))
    {
        ErrorLog('Error setting CURLOPT_RETURNTRANSFER option', E_USER_ERROR);

        return FALSE;
    }

    //Set post CURLOPT_POST
    if (!curl_setopt($cURL, CURLOPT_POST,  TRUE))
    {
        ErrorLog('Error setting called URL', E_USER_ERROR);

        return FALSE;
    }

    //If there are parameters, use them as POST FIELDS
    if (!empty($Parameters))
    {
        if ($URLEncoded)
        {
            if (is_array($Parameters)||is_object($Parameters))
            {
                if (!curl_setopt($cURL, CURLOPT_POSTFIELDS,  http_build_query($Parameters)))
                {
                    ErrorLog('Error setting CURLOPT_POSTFIELDS', E_USER_ERROR);

                    return FALSE;
                }
            }
            else
            {
                ErrorLog('application/x-www-form-urlencoded post fields must be passed in array or object formats', E_USER_ERROR);

                return FALSE;
            }
        }
        else
        {
            if (is_array($Parameters))
            {
                if (!curl_setopt($cURL, CURLOPT_POSTFIELDS, $Parameters))
                {
                    ErrorLog('Error setting CURLOPT_POSTFIELDS', E_USER_ERROR);

                    return FALSE;
                }
            }
            else
            {
                ErrorLog('multipart/form-data post fields must be passed in array format', E_USER_ERROR);

                return FALSE;
            }
        }
    }

    //Now curl_exec returns the response on success, FALSE on failure. HTTP errors are successful transactions
    $Response = curl_exec($cURL);
    if ($Response === FALSE)
    {
        //Something failed, so lets capture error message and number
        $ErrMss = curl_error($cURL);
        $ErrNo = curl_errno($cURL);
        $ErrorMessage = 'cURL Error #'.$ErrNo.':'.$ErrMss.' trying to reach: '.$URL;
        ErrorLog($ErrorMessage, E_USER_ERROR);

        return FALSE;
    }

    //Get the HTTP code of the transaction
    $HTTPCode = curl_getinfo($cURL,  CURLINFO_RESPONSE_CODE);
    if ($HTTPCode === FALSE)
    {
        ErrorLog('Error getting transaction info', E_USER_ERROR);

        return FALSE;
    }

    //Close the cURL session, no use from PHP 8 on
    if (PHP_VERSION_ID>=80000)
    {
        curl_close($cURL);
    }

    //HTTP Code to be processed Upstream, we are only the callers
    $Feedback['HTTPCode'] = $HTTPCode;
    $Feedback['Response'] = $Response;
    $EncodedFB = json_encode($Feedback,  JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_NUMERIC_CHECK);
    if ($EncodedFB === FALSE)
    {
        ErrorLog('Error JSON-encoding server response', E_USER_ERROR);

        return FALSE;
    }

    return $EncodedFB;
}

/**
 * cURLFullGET
 * Does a full HTTP GET operation via cURL. Requires CURLOPT_RETURNTRANSFER to be set to TRUE and will
 * set it no matter what options are specified. GET retrieves information from the server. Should not modify the data on the server. It can be cached, bookmarked, 
 * and may remain in the browser history. One of the safe methods. Safe methods should not change the state of the server; the operation performed by this method 
 * should be read-only. Safe methods are also idempotent (produce the same result when several identical requests are made).
 * @param   string  $URL        The URL to consult
 * @param   array   $Parameters Parameters in 'foo' => 'bar' form
 * @param   int     $Encoding   Use PHP constants:
 *                                  PHP_QUERY_RFC1738 for RFC1738 (really RFC1630) plus_sign application/x-www-form-urlencoded encoding 
 *                                  PHP_QUERY_RFC3986 for RFC3986 section 2.1 (Percent-Encoding) encoding
 * @param   array   $cURLOptions    Array of cURL options to set
 * @return  mixed   JSON on correct and erroneous calls, FALSE on validation errors
 * @since 0.0.9
 * @todo
 * @see Postman, https://zetcode.com/php/curl/
 */
function cURLFullGET($URL, $Parameters = NULL, $Encoding = NULL, $cURLOptions = NULL)
{
    if (DEBUGMODE)
    {
        echo date("Y-m-d H:i:s").' -> cURLFullGET '.PHP_EOL;
    }

    //Validation
    if (!IsValidURI($URL))
    {
        ErrorLog('Invalid URI', E_USER_ERROR);

        return FALSE;
    }

    //Encoding validation
    if (!empty($Encoding))
    {
        if ($Encoding === PHP_QUERY_RFC1738)
        {
            //Standard encoding
            $Encoding = NULL;
        }
        elseif ($Encoding !== PHP_QUERY_RFC3986)
        {
            ErrorLog('Unknown encoding, use constants PHP_QUERY_RFC1738 for plus-space or PHP_QUERY_RFC3986 for percent encoding', E_USER_ERROR);

            return FALSE;
        }
    }

    //Options validation
    if (!empty($cURLOptions))
    {
        if (!cURLOptionsValidate($cURLOptions))
        {
            ErrorLog('Error validating cURL Options. Review error output', E_USER_WARNING);

            return FALSE;
        }
    }

    //Init cURL lib
    $cURL = curl_init();
    if ($cURL === FALSE)
    {
        ErrorLog('Error initialising cURL', E_USER_ERROR);

        return FALSE;
    }

    //Force GET
    if (!curl_setopt($cURL, CURLOPT_HTTPGET,  TRUE))
    {
        ErrorLog('Error setting HTTPGET', E_USER_ERROR);

        return FALSE;
    }

    //Capture output
    if (!curl_setopt($cURL, CURLOPT_RETURNTRANSFER, TRUE))
    {
        ErrorLog('Error setting CURLOPT_RETURNTRANSFER option', E_USER_ERROR);

        return FALSE;
    }

    //"" = Allow all supported encoding types
    if (!curl_setopt($cURL, CURLOPT_ENCODING, TRUE))
    {
        ErrorLog('Error setting CURLOPT_ENCODING option', E_USER_ERROR);

        return FALSE;
    }

    //10 redirections
    if (!curl_setopt($cURL, CURLOPT_MAXREDIRS, 10))
    {
        ErrorLog('Error setting CURLOPT_MAXREDIRS option', E_USER_ERROR);

        return FALSE;
    }

    //TRUE to follow any "Location: " header that the server sends
    if (!curl_setopt($cURL, CURLOPT_FOLLOWLOCATION, TRUE))
    {
        ErrorLog('Error setting CURLOPT_FOLLOWLOCATION option', E_USER_ERROR);

        return FALSE;
    }

    //No timeout whatsoever
    if (!curl_setopt($cURL, CURLOPT_TIMEOUT, 0))
    {
        ErrorLog('Error setting CURLOPT_TIMEOUT option', E_USER_ERROR);

        return FALSE;
    }

    //Use HTTP1.1, better safe than sorry
    if (!curl_setopt($cURL, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1))
    {
        ErrorLog('Error setting CURLOPT_HTTP_VERSION option', E_USER_ERROR);

        return FALSE;
    }

    //Pass URL
    if (!empty($Parameters))
    {
        if (is_array($Parameters)||is_object($Parameters))
        {
            if (empty($Encoding))
            {
                $URL .= '?'.http_build_query($Parameters);
            }
            else
            {
                $URL .= '?'.http_build_query($Parameters,  NULL, '&', PHP_QUERY_RFC3986);
            }
        }
        else
        {
            ErrorLog('Parameters in GET operations must be passed in array or object formats', E_USER_ERROR);

            return FALSE;
        }
    }
    if (!curl_setopt($cURL, CURLOPT_URL,  $URL))
    {
        ErrorLog('Error setting called URL', E_USER_ERROR);

        return FALSE;
    }

    //Set user options. Might overwrite our own
    if (!empty($cURLOptions))
    {
        foreach ($cURLOptions as $Key => $Value)
        {
            if (!is_array($Value))
            {
                $Textd = $Value;
            }
            else
            {
                $Textd = json_encode($Value, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_NUMERIC_CHECK);
            }
            echo  'Setting '.cURLOptionConstantToLiteral($Key).' as '.$Textd.PHP_EOL;
            if (!curl_setopt($cURL, $Key,  $Value))
            {
                ErrorLog('Error setting '.$Key.' as '.$Textd, E_USER_ERROR);

                return FALSE;
            }
        } //End for each option to set
    }

    //Now curl_exec returns the response on success, FALSE on failure. HTTP errors are successful transactions
    $Response = curl_exec($cURL);
    if ($Response === FALSE)
    {
        //Something failed, so lets capture error message and number
        $ErrMss = curl_error($cURL);
        $ErrNo = curl_errno($cURL);
        $ErrorMessage = 'cURL Error #'.$ErrNo.':'.$ErrMss.' trying to reach: '.$URL;
        ErrorLog($ErrorMessage, E_USER_ERROR);

        return FALSE;
    }

    //Get the HTTP code of the transaction
    $HTTPCode = curl_getinfo($cURL,  CURLINFO_RESPONSE_CODE);
    if ($HTTPCode === FALSE)
    {
        ErrorLog('Error getting transaction info', E_USER_ERROR);

        return FALSE;
    }

    //Close the cURL session, no use from PHP 8 on
    if (PHP_VERSION_ID>=80000)
    {
        curl_close($cURL);
    }

    //HTTP Code to be processed Upstream, we are only the callers
    $Feedback['HTTPCode'] = $HTTPCode;
    $Feedback['Response'] = $Response;
    $EncodedFB = json_encode($Feedback,  JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_NUMERIC_CHECK);
    if ($EncodedFB === FALSE)
    {
        ErrorLog('Error JSON-encoding server response', E_USER_ERROR);

        return FALSE;
    }

    return $EncodedFB;
}

/**
 * cURLFullHEAD
 * Does a full HTTP HEAD operation via cURL. HEAD is Similar to GET, except it transfers the status line and headers only. Should not modify the data on the 
 * server. It cannot be bookmarked and does not remain in the browser history. One of the safe methods. Safe methods should not change the state of the server; 
 * the operation performed by this method should be read-only. Safe methods are also idempotent (produce the same result when several identical requests are 
 * made). As it sets CURLOPT_FOLLOWLOCATION and CURLOPT_HEADER, when redirect/s have happened then the header returned by curl_exec() will contain all the 
 * headers in the redirect chain in the order they were encountered. Set CURLOPT_FOLLOWLOCATION to FALSE if you like to get just the headers of the first call
 * @param   string  $URL        The URL to consult
 * @param   array   $Parameters Parameters in 'foo' => 'bar' form
 * @param   array   $cURLOptions    Array of cURL options to set
 * @return  mixed   JSON on correct and erroneous calls, FALSE on validation errors
 * @since 0.0.9
 * @todo xAQUI
 * @see https://zetcode.com/php/curl/
 */
function cURLFullHEAD($URL, $Parameters = NULL, $cURLOptions = NULL)
{
    if (DEBUGMODE)
    {
        echo date("Y-m-d H:i:s").' -> cURLFullHEAD '.PHP_EOL;
    }

    //Validation
    if (!IsValidURI($URL))
    {
        ErrorLog('Invalid URI', E_USER_ERROR);

        return FALSE;
    }

    //If there are parameters, add them to the URL
    if (!empty($Parameters))
    {
        if (is_array($Parameters)||is_object($Parameters))
        {
            $URL .= '?'.http_build_query($Parameters);
        }
        else
        {
            ErrorLog('Parameters must be passed in array or object formats', E_USER_ERROR);

            return FALSE;
        }
    }

    //Init cURL lib
    $cURL = curl_init();
    if ($cURL === FALSE)
    {
        ErrorLog('Error initialising cURL', E_USER_ERROR);

        return FALSE;
    }

    //Pass URL
    if (!curl_setopt($cURL, CURLOPT_URL,  $URL))
    {
        ErrorLog('Error setting called URL', E_USER_ERROR);

        return FALSE;
    }

    //Capture output
    if (!curl_setopt($cURL, CURLOPT_RETURNTRANSFER, TRUE))
    {
        ErrorLog('Error setting CURLOPT_RETURNTRANSFER option', E_USER_ERROR);

        return FALSE;
    }

    //Set HEADER options
    if (!curl_setopt($cURL, CURLOPT_HEADER, TRUE))
    {
        ErrorLog('Error setting CURLOPT_HEADER option', E_USER_ERROR);

        return FALSE;
    }

    if (!curl_setopt($cURL, CURLOPT_NOBODY, TRUE))
    {
        ErrorLog('Error setting CURLOPT_NOBODY option', E_USER_ERROR);

        return FALSE;
    }

    //"" = Allow all supported encoding types
    if (!curl_setopt($cURL, CURLOPT_ENCODING, TRUE))
    {
        ErrorLog('Error setting CURLOPT_ENCODING option', E_USER_ERROR);

        return FALSE;
    }

    //10 redirections
    if (!curl_setopt($cURL, CURLOPT_MAXREDIRS, 10))
    {
        ErrorLog('Error setting CURLOPT_MAXREDIRS option', E_USER_ERROR);

        return FALSE;
    }

    //TRUE to follow any "Location: " header that the server sends
    if (!curl_setopt($cURL, CURLOPT_FOLLOWLOCATION, TRUE))
    {
        ErrorLog('Error setting CURLOPT_FOLLOWLOCATION option', E_USER_ERROR);

        return FALSE;
    }

    //No timeout whatsoever
    if (!curl_setopt($cURL, CURLOPT_TIMEOUT, 0))
    {
        ErrorLog('Error setting CURLOPT_TIMEOUT option', E_USER_ERROR);

        return FALSE;
    }

    //Use HTTP1.1, better safe than sorry
    if (!curl_setopt($cURL, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1))
    {
        ErrorLog('Error setting CURLOPT_HTTP_VERSION option', E_USER_ERROR);

        return FALSE;
    }

    //Set user options. Might overwrite our own
    if (!empty($cURLOptions))
    {
        foreach ($cURLOptions as $Key => $Value)
        {
            if (!is_array($Value))
            {
                $Textd = $Value;
            }
            else
            {
                $Textd = json_encode($Value, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_NUMERIC_CHECK);
            }
            echo  'Setting '.cURLOptionConstantToLiteral($Key).' as '.$Textd.PHP_EOL;
            if (!curl_setopt($cURL, $Key,  $Value))
            {
                ErrorLog('Error setting '.$Key.' as '.$Textd, E_USER_ERROR);

                return FALSE;
            }
        } //End for each option to set
    }

    //Now curl_exec returns the response on success, FALSE on failure. HTTP errors are successful transactions
    $Response = curl_exec($cURL);
    if ($Response === FALSE)
    {
        //Something failed, so lets capture error message and number
        $ErrMss = curl_error($cURL);
        $ErrNo = curl_errno($cURL);
        $ErrorMessage = 'cURL Error #'.$ErrNo.':'.$ErrMss.' trying to reach: '.$URL;
        ErrorLog($ErrorMessage, E_USER_ERROR);

        return FALSE;
    }

    //Get the HTTP code of the transaction
    $HTTPCode = curl_getinfo($cURL,  CURLINFO_RESPONSE_CODE);
    if ($HTTPCode === FALSE)
    {
        ErrorLog('Error getting transaction info', E_USER_ERROR);

        return FALSE;
    }

    //Close the cURL session, no use from PHP 8 on
    if (PHP_VERSION_ID>=80000)
    {
        curl_close($cURL);
    }

    //HTTP Code to be processed Upstream, we are only the callers
    $Feedback['HTTPCode'] = $HTTPCode;
    $Feedback['Response'] = $Response;
    $EncodedFB = json_encode($Feedback,  JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_NUMERIC_CHECK);
    if ($EncodedFB === FALSE)
    {
        ErrorLog('Error JSON-encoding server response', E_USER_ERROR);

        return FALSE;
    }

    return $EncodedFB;
}

/**
 * cURLFullPOST
 * Does a full HTTP POST operation via cURL. POST is used to Send data to the server, including images, JSON strings, file downloads, etc. It cannot be cached, 
 * bookmarked, and not stored in the browser history. Not a safe or idempotent method.
 * @param   string  $URL        The URL to consult
 * @param   array   $Parameters Parameters in 'foo' => 'bar' form
 * @param   array   $cURLOptions    Array of cURL options to set
 * @param   boolean $URLEncoded TRUE to post as application/x-www-form-urlencoded, FALSE to post as multipart/form-data
 * @return  mixed   JSON on correct and erroneous calls, FALSE on validation errors
 * @since   0.0.9
 * @todo    Enable use of raw (CURLOPT_HTTPHEADER => 'Content-Type: text/plain'), binary (CURLOPT_HTTPHEADER variable), JSON(CURLOPT_HTTPHEADER => application/json)
 * @see Postman, https://zetcode.com/php/curl/
 */
function cURLFullPOST($URL, $Parameters = NULL, $cURLOptions = NULL, $URLEncoded = FALSE)
{
    if (DEBUGMODE)
    {
        echo date("Y-m-d H:i:s").' -> cURLFullPOST '.PHP_EOL;
    }

    //Validation
    if (!IsValidURI($URL))
    {
        ErrorLog('Invalid URI', E_USER_ERROR);

        return FALSE;
    }

    //Init cURL lib
    $cURL = curl_init();
    if ($cURL === FALSE)
    {
        ErrorLog('Error initialising cURL', E_USER_ERROR);

        return FALSE;
    }

    //Pass URL
    if (!curl_setopt($cURL, CURLOPT_URL,  $URL))
    {
        ErrorLog('Error setting called URL', E_USER_ERROR);

        return FALSE;
    }

    //Capture output
    if (!curl_setopt($cURL, CURLOPT_RETURNTRANSFER, TRUE))
    {
        ErrorLog('Error setting CURLOPT_RETURNTRANSFER option', E_USER_ERROR);

        return FALSE;
    }

    //Set post CURLOPT_POST
    if (!curl_setopt($cURL, CURLOPT_POST,  TRUE))
    {
        ErrorLog('Error setting called URL', E_USER_ERROR);

        return FALSE;
    }

    //If there are parameters, use them as POST FIELDS
    if (!empty($Parameters))
    {
        if ($URLEncoded)
        {
            if (is_array($Parameters)||is_object($Parameters))
            {
                if (!curl_setopt($cURL, CURLOPT_POSTFIELDS,  http_build_query($Parameters)))
                {
                    ErrorLog('Error setting CURLOPT_POSTFIELDS', E_USER_ERROR);

                    return FALSE;
                }
            }
            else
            {
                ErrorLog('application/x-www-form-urlencoded post fields must be passed in array or object formats', E_USER_ERROR);

                return FALSE;
            }
        }
        else
        {
            if (is_array($Parameters))
            {
                if (!curl_setopt($cURL, CURLOPT_POSTFIELDS, $Parameters))
                {
                    ErrorLog('Error setting CURLOPT_POSTFIELDS', E_USER_ERROR);

                    return FALSE;
                }
            }
            else
            {
                ErrorLog('multipart/form-data post fields must be passed in array format', E_USER_ERROR);

                return FALSE;
            }
        }
    }

    //"" = Allow all supported encoding types
    if (!curl_setopt($cURL, CURLOPT_ENCODING, TRUE))
    {
        ErrorLog('Error setting CURLOPT_ENCODING option', E_USER_ERROR);

        return FALSE;
    }

    //10 redirections
    if (!curl_setopt($cURL, CURLOPT_MAXREDIRS, 10))
    {
        ErrorLog('Error setting CURLOPT_MAXREDIRS option', E_USER_ERROR);

        return FALSE;
    }

    //TRUE to follow any "Location: " header that the server sends
    if (!curl_setopt($cURL, CURLOPT_FOLLOWLOCATION, TRUE))
    {
        ErrorLog('Error setting CURLOPT_FOLLOWLOCATION option', E_USER_ERROR);

        return FALSE;
    }

    //No timeout whatsoever
    if (!curl_setopt($cURL, CURLOPT_TIMEOUT, 0))
    {
        ErrorLog('Error setting CURLOPT_TIMEOUT option', E_USER_ERROR);

        return FALSE;
    }

    //Use HTTP1.1, better safe than sorry
    if (!curl_setopt($cURL, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1))
    {
        ErrorLog('Error setting CURLOPT_HTTP_VERSION option', E_USER_ERROR);

        return FALSE;
    }

    //Set user options. Might overwrite our own
    if (!empty($cURLOptions))
    {
        foreach ($cURLOptions as $Key => $Value)
        {
            if (!is_array($Value))
            {
                $Textd = $Value;
            }
            else
            {
                $Textd = json_encode($Value, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_NUMERIC_CHECK);
            }
            echo  'Setting '.cURLOptionConstantToLiteral($Key).' as '.$Textd.PHP_EOL;
            if (!curl_setopt($cURL, $Key,  $Value))
            {
                ErrorLog('Error setting '.$Key.' as '.$Textd, E_USER_ERROR);

                return FALSE;
            }
        } //End for each option to set
    }

    //Now curl_exec returns the response on success, FALSE on failure. HTTP errors are successful transactions
    $Response = curl_exec($cURL);
    if ($Response === FALSE)
    {
        //Something failed, so lets capture error message and number
        $ErrMss = curl_error($cURL);
        $ErrNo = curl_errno($cURL);
        $ErrorMessage = 'cURL Error #'.$ErrNo.':'.$ErrMss.' trying to reach: '.$URL;
        ErrorLog($ErrorMessage, E_USER_ERROR);

        return FALSE;
    }

    //Get the HTTP code of the transaction
    $HTTPCode = curl_getinfo($cURL,  CURLINFO_RESPONSE_CODE);
    if ($HTTPCode === FALSE)
    {
        ErrorLog('Error getting transaction info', E_USER_ERROR);

        return FALSE;
    }

    //Close the cURL session, no use from PHP 8 on
    if (PHP_VERSION_ID>=80000)
    {
        curl_close($cURL);
    }

    //HTTP Code to be processed Upstream, we are only the callers
    $Feedback['HTTPCode'] = $HTTPCode;
    $Feedback['Response'] = $Response;
    $EncodedFB = json_encode($Feedback,  JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_NUMERIC_CHECK);
    if ($EncodedFB === FALSE)
    {
        ErrorLog('Error JSON-encoding server response', E_USER_ERROR);

        return FALSE;
    }

    return $EncodedFB;

    //Force POST method, do not accept other
    /*
    //Force CURLOPT_RETURNTRANSFER
    $cURLOptions[CURLOPT_RETURNTRANSFER] = TRUE;

    //Force CURLOPT_POST
    $cURLOptions[CURLOPT_POST] = TRUE;

    //If there are parameters, use them as POST FIELDS
    if (!empty($Parameters))
    {
        if ($URLEncoded)
        {
            if (is_array($Parameters)||is_object($Parameters))
            {
                $cURLOptions[CURLOPT_POSTFIELDS] = http_build_query($Parameters);
            }
            else
            {
                ErrorLog('application/x-www-form-urlencoded post fields must be passed in array or object formats', E_USER_ERROR);

                return FALSE;
            }
        }
        else
        {
            if (is_array($Parameters))
            {
                $cURLOptions[CURLOPT_POSTFIELDS] = $Parameters;
            }
            else
            {
                ErrorLog('multipart/form-data post fields must be passed in array format', E_USER_ERROR);

                return FALSE;
            }
        }
    }

    //Set the rest of options, if they are not set otherwise by user
    if (!isset($cURLOptions[CURLOPT_ENCODING]))
    {
        $cURLOptions[CURLOPT_ENCODING] = ""; //"" means all supported encoding types
    }
    if (!isset($cURLOptions[CURLOPT_MAXREDIRS]))
    {
        $cURLOptions[CURLOPT_MAXREDIRS] = 10; //10 redirections
    }
    if (!isset($cURLOptions[CURLOPT_FOLLOWLOCATION]))
    {
        $cURLOptions[CURLOPT_FOLLOWLOCATION] = TRUE; //TRUE to follow any "Location: " header that the server sends
    }
    if (!isset($cURLOptions[CURLOPT_TIMEOUT]))
    {
        $cURLOptions[CURLOPT_TIMEOUT] = 0; //No timeout whatsoever
    }
    if (!isset($cURLOptions[CURLOPT_HTTP_VERSION]))
    {
        $cURLOptions[CURLOPT_HTTP_VERSION] = CURL_HTTP_VERSION_1_1; //Better safe than sorry
    }

    //Perform the transaction. Will either get a response or FALSE
    //function cURLWebServiceCaller($HTTPMethod, $URL, $Parameters = NULL, $Encoding = NULL, $cURLOptions = NULL, $Path = NULL)
    $Result = cURLWebServiceCaller('POST', $URL, $Parameters, NULL, $cURLOptions, NULL);

    return $Result;
*/
}

/**
 * cURLFileGET
 * Downloads a file to a destination
 * @param   string  $URL         The resource to download
 * @param   string  $Destination Destination file, code will check that is writable
 * @param   array   $cURLOptions Array of cURL options to set
 * @return  mixed   JSON on correct and erroneous calls, FALSE on validation errors
 * @since 0.0.9
 * @todo
 * @see
 */
function cURLFileGET($URL, $Destination, $cURLOptions)
{
    if (DEBUGMODE)
    {
        echo date("Y-m-d H:i:s").' -> cURLFileGET '.PHP_EOL;
    }

    //Validation
    if (!IsValidURI($URL))
    {
        ErrorLog('Invalid URI', E_USER_ERROR);

        return FALSE;
    }

    if (file_exists($Destination))
    {
        if (!is_writable($Destination))
        {
            //If it already exists, must be writeable
            ErrorLog('Unwritable FILE', E_USER_ERROR);

            return FALSE;
        }
    }
    elseif (!is_writable(dirname($Destination)))
    {
        //Parent directory must be writeable
        ErrorLog('Unwritable folder '.dirname($Destination), E_USER_ERROR);

        return FALSE;
    }

    $TheFile = fopen($Destination, 'wb');
    if ($TheFile === FALSE)
    {
        ErrorLog('Error opening FILE for writing', E_USER_ERROR);

        return FALSE;
    }

    //Init cURL lib
    $cURL = curl_init();
    if ($cURL === FALSE)
    {
        ErrorLog('Error initialising cURL', E_USER_ERROR);

        return FALSE;
    }

    //Pass URL
    if (!curl_setopt($cURL, CURLOPT_URL,  $URL))
    {
        ErrorLog('Error setting called URL', E_USER_ERROR);

        return FALSE;
    }

    //File to write to
    if (!curl_setopt($cURL, CURLOPT_FILE,  $TheFile))
    {
        ErrorLog('Error setting destination file', E_USER_ERROR);

        return FALSE;
    }

    //Disable headers
    if (!curl_setopt($cURL, CURLOPT_HEADER,  FALSE))
    {
        ErrorLog('Error disabling headers on the output', E_USER_ERROR);

        return FALSE;
    }

    //"" = Allow all supported encoding types
    if (!curl_setopt($cURL, CURLOPT_ENCODING, TRUE))
    {
        ErrorLog('Error setting CURLOPT_ENCODING option', E_USER_ERROR);

        return FALSE;
    }

    //10 redirections
    if (!curl_setopt($cURL, CURLOPT_MAXREDIRS, 10))
    {
        ErrorLog('Error setting CURLOPT_MAXREDIRS option', E_USER_ERROR);

        return FALSE;
    }

    //TRUE to follow any "Location: " header that the server sends
    if (!curl_setopt($cURL, CURLOPT_FOLLOWLOCATION, TRUE))
    {
        ErrorLog('Error setting CURLOPT_FOLLOWLOCATION option', E_USER_ERROR);

        return FALSE;
    }

    //No timeout whatsoever
    if (!curl_setopt($cURL, CURLOPT_TIMEOUT, 0))
    {
        ErrorLog('Error setting CURLOPT_TIMEOUT option', E_USER_ERROR);

        return FALSE;
    }

    //Use HTTP1.1, better safe than sorry
    if (!curl_setopt($cURL, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1))
    {
        ErrorLog('Error setting CURLOPT_HTTP_VERSION option', E_USER_ERROR);

        return FALSE;
    }

    //Set user options. Might overwrite our own
    if (!empty($cURLOptions))
    {
        foreach ($cURLOptions as $Key => $Value)
        {
            if (!is_array($Value))
            {
                $Textd = $Value;
            }
            else
            {
                $Textd = json_encode($Value, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_NUMERIC_CHECK);
            }
            echo  'Setting '.cURLOptionConstantToLiteral($Key).' as '.$Textd.PHP_EOL;
            if (!curl_setopt($cURL, $Key,  $Value))
            {
                ErrorLog('Error setting '.$Key.' as '.$Textd, E_USER_ERROR);

                return FALSE;
            }
        } //End for each option to set
    }

    //Now curl_exec returns the response on success, FALSE on failure. HTTP errors are successful transactions
    $Response = curl_exec($cURL);
    if ($Response === FALSE)
    {
        //Something failed, so lets capture error message and number
        $ErrMss = curl_error($cURL);
        $ErrNo = curl_errno($cURL);
        $ErrorMessage = 'cURL Error #'.$ErrNo.':'.$ErrMss.' trying to reach: '.$URL;
        ErrorLog($ErrorMessage, E_USER_ERROR);

        return FALSE;
    }

    //Get the HTTP code of the transaction
    $HTTPCode = curl_getinfo($cURL,  CURLINFO_RESPONSE_CODE);
    if ($HTTPCode === FALSE)
    {
        ErrorLog('Error getting transaction info', E_USER_ERROR);

        return FALSE;
    }

    //Close the cURL session, no use from PHP 8 on
    if (PHP_VERSION_ID>=80000)
    {
        curl_close($cURL);
    }

    //Close the file handler
    $Closure = fclose($TheFile);
    if ($Closure === FALSE)
    {
        ErrorLog('Error closing FILE', E_USER_ERROR);

        return FALSE;
    }

    //HTTP Code to be processed Upstream, we are only the callers
    $Feedback['HTTPCode'] = $HTTPCode;
    $Feedback['Response'] = $Response;
    $EncodedFB = json_encode($Feedback,  JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_NUMERIC_CHECK);
    if ($EncodedFB === FALSE)
    {
        ErrorLog('Error JSON-encoding server response', E_USER_ERROR);

        return FALSE;
    }

    return $EncodedFB;
    /*
    //Force no headers
    $cURLOptions[CURLOPT_HEADER] = FALSE;

    //Perform the transaction. Will either get a response or FALSE
    //function cURLWebServiceCaller($HTTPMethod, $URL, $Parameters = NULL, $Encoding = NULL, $cURLOptions = NULL, $Path = NULL)
    $Result = cURLWebServiceCaller('GET', $URL, NULL, NULL, $cURLOptions, $Destination);

    return $Result;
 * 
 */
}

/**
 * cURLSimpleFileGET
 * Downloads a file to a destination with fixed, known to work options
 * @param   string  $URL         The resource to download
 * @param   string  $Destination Destination file, code will check that is writable
 * @return  mixed   JSON on correct and erroneous calls, FALSE on validation errors
 * @since 0.0.9
 * @todo
 * @see
 */
function cURLSimpleFileGET($URL, $Destination)
{
    if (DEBUGMODE)
    {
        echo date("Y-m-d H:i:s").' -> cURLSimpleFileGET '.PHP_EOL;
    }

    //Validation
    if (!IsValidURI($URL))
    {
        ErrorLog('Invalid URI', E_USER_ERROR);

        return FALSE;
    }

    if (file_exists($Destination))
    {
        if (!is_writable($Destination))
        {
            //If it already exists, must be writeable
            ErrorLog('Unwritable FILE', E_USER_ERROR);

            return FALSE;
        }
    }
    elseif (!is_writable(dirname($Destination)))
    {
        //Parent directory must be writeable
        ErrorLog('Unwritable folder '.dirname($Destination), E_USER_ERROR);

        return FALSE;
    }

    $TheFile = fopen($Destination, 'wb');
    if ($TheFile === FALSE)
    {
        ErrorLog('Error opening FILE for writing', E_USER_ERROR);

        return FALSE;
    }

    //Init cURL lib
    $cURL = curl_init();
    if ($cURL === FALSE)
    {
        ErrorLog('Error initialising cURL', E_USER_ERROR);

        return FALSE;
    }

    //Pass URL
    if (!curl_setopt($cURL, CURLOPT_URL,  $URL))
    {
        ErrorLog('Error setting called URL', E_USER_ERROR);

        return FALSE;
    }

    //File to write to
    if (!curl_setopt($cURL, CURLOPT_FILE,  $TheFile))
    {
        ErrorLog('Error setting destination file', E_USER_ERROR);

        return FALSE;
    }

    //Disable headers
    if (!curl_setopt($cURL, CURLOPT_HEADER,  FALSE))
    {
        ErrorLog('Error disabling headers on the output', E_USER_ERROR);

        return FALSE;
    }

    //Now curl_exec returns the response on success, FALSE on failure. HTTP errors are successful transactions
    $Response = curl_exec($cURL);
    if ($Response === FALSE)
    {
        //Something failed, so lets capture error message and number
        $ErrMss = curl_error($cURL);
        $ErrNo = curl_errno($cURL);
        $ErrorMessage = 'cURL Error #'.$ErrNo.':'.$ErrMss.' trying to reach: '.$URL;
        ErrorLog($ErrorMessage, E_USER_ERROR);

        return FALSE;
    }

    //Get the HTTP code of the transaction
    $HTTPCode = curl_getinfo($cURL,  CURLINFO_RESPONSE_CODE);
    if ($HTTPCode === FALSE)
    {
        ErrorLog('Error getting transaction info', E_USER_ERROR);

        return FALSE;
    }

    //Close the cURL session, no use from PHP 8 on
    if (PHP_VERSION_ID>=80000)
    {
        curl_close($cURL);
    }

    //Close the file handler
    $Closure = fclose($TheFile);
    if ($Closure === FALSE)
    {
        ErrorLog('Error closing FILE', E_USER_ERROR);

        return FALSE;
    }

    //HTTP Code to be processed Upstream, we are only the callers
    $Feedback['HTTPCode'] = $HTTPCode;
    $Feedback['Response'] = $Response;
    $EncodedFB = json_encode($Feedback,  JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_NUMERIC_CHECK);
    if ($EncodedFB === FALSE)
    {
        ErrorLog('Error JSON-encoding server response', E_USER_ERROR);

        return FALSE;
    }

    return $EncodedFB;
}

/**
 * cURLFilePUT
 * Uploads a file to a destination using PUT
 * @param   string  $Origin        The file to upload, code will check that is readable
 * @param   string  $URL           Destination
 * @param   array   $cURLOptions   Array of cURL options to set
 * @return  mixed   JSON on correct and erroneous calls, FALSE on validation errors
 * @since 0.0.9
 * @todo
 * @see
 */
function cURLFilePUT($Origin, $URL, $cURLOptions)
{
    if (DEBUGMODE)
    {
        echo date("Y-m-d H:i:s").' -> cURLFilePUT '.PHP_EOL;
    }

    //Validation
    if (is_readable($Origin) === FALSE)
    {
        //If it already exists, must be writeable
        ErrorLog('Unreadable FILE', E_USER_ERROR);

        return FALSE;
    }

    if (!IsValidURI($URL))
    {
        ErrorLog('Invalid URI', E_USER_ERROR);

        return FALSE;
    }

    $TheFile = fopen($Origin, 'rb');
    if ($TheFile === FALSE)
    {
        ErrorLog('Error opening FILE for reading', E_USER_ERROR);

        return FALSE;
    }

    //Init cURL lib
    $cURL = curl_init();
    if ($cURL === FALSE)
    {
        ErrorLog('Error initialising cURL', E_USER_ERROR);

        return FALSE;
    }

    //Pass URL
    if (!curl_setopt($cURL, CURLOPT_URL,  $URL))
    {
        ErrorLog('Error setting called URL', E_USER_ERROR);

        return FALSE;
    }

    //Force PUT by enabling uploading (implies PUT over HTTP)
    if (!curl_setopt($cURL, CURLOPT_UPLOAD, TRUE))
    {
        ErrorLog('Error setting PUT operation', E_USER_ERROR);

        return FALSE;
    }


    //File to read from
    if (!curl_setopt($cURL, CURLOPT_READDATA,  $TheFile))
    {
        ErrorLog('Error reading origin file', E_USER_ERROR);

        return FALSE;
    }
    //echo 'Tamaño: '.filesize($Origin).PHP_EOL;
    if (!curl_setopt($cURL, CURLOPT_INFILESIZE,  filesize($Origin)))
    {
        ErrorLog('Error setting origin file size', E_USER_ERROR);

        return FALSE;
    }

    //"" = Allow all supported encoding types
    if (!curl_setopt($cURL, CURLOPT_ENCODING, TRUE))
    {
        ErrorLog('Error setting CURLOPT_ENCODING option', E_USER_ERROR);

        return FALSE;
    }

    //10 redirections
    if (!curl_setopt($cURL, CURLOPT_MAXREDIRS, 10))
    {
        ErrorLog('Error setting CURLOPT_MAXREDIRS option', E_USER_ERROR);

        return FALSE;
    }

    //TRUE to follow any "Location: " header that the server sends
    if (!curl_setopt($cURL, CURLOPT_FOLLOWLOCATION, TRUE))
    {
        ErrorLog('Error setting CURLOPT_FOLLOWLOCATION option', E_USER_ERROR);

        return FALSE;
    }

    //No timeout whatsoever
    if (!curl_setopt($cURL, CURLOPT_TIMEOUT, 0))
    {
        ErrorLog('Error setting CURLOPT_TIMEOUT option', E_USER_ERROR);

        return FALSE;
    }

    //Use HTTP1.1, better safe than sorry
    if (!curl_setopt($cURL, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1))
    {
        ErrorLog('Error setting CURLOPT_HTTP_VERSION option', E_USER_ERROR);

        return FALSE;
    }

    //Set user options. Might overwrite our own
    if (!empty($cURLOptions))
    {
        foreach ($cURLOptions as $Key => $Value)
        {
            if (!is_array($Value))
            {
                $Textd = $Value;
            }
            else
            {
                $Textd = json_encode($Value, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_NUMERIC_CHECK);
            }
            echo  'Setting '.cURLOptionConstantToLiteral($Key).' as '.$Textd.PHP_EOL;
            if (!curl_setopt($cURL, $Key,  $Value))
            {
                ErrorLog('Error setting '.$Key.' as '.$Textd, E_USER_ERROR);

                return FALSE;
            }
        } //End for each option to set
    }

    //Now curl_exec returns the response on success, FALSE on failure. HTTP errors are successful transactions
    $Response = curl_exec($cURL);
    if ($Response === FALSE)
    {
        //Something failed, so lets capture error message and number
        $ErrMss = curl_error($cURL);
        $ErrNo = curl_errno($cURL);
        $ErrorMessage = 'cURL Error #'.$ErrNo.':'.$ErrMss.' trying to reach: '.$URL;
        ErrorLog($ErrorMessage, E_USER_ERROR);

        return FALSE;
    }

    //Get the HTTP code of the transaction
    $HTTPCode = curl_getinfo($cURL,  CURLINFO_RESPONSE_CODE);
    if ($HTTPCode === FALSE)
    {
        ErrorLog('Error getting transaction info', E_USER_ERROR);

        return FALSE;
    }

    //Close the cURL session, no use from PHP 8 on
    if (PHP_VERSION_ID>=80000)
    {
        curl_close($cURL);
    }

    //Close the file handler
    $Closure = fclose($TheFile);
    if ($Closure === FALSE)
    {
        ErrorLog('Error closing FILE', E_USER_ERROR);

        return FALSE;
    }

    //HTTP Code to be processed Upstream, we are only the callers
    $Feedback['HTTPCode'] = $HTTPCode;
    $Feedback['Response'] = $Response;
    $EncodedFB = json_encode($Feedback,  JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_NUMERIC_CHECK);
    if ($EncodedFB === FALSE)
    {
        ErrorLog('Error JSON-encoding server response', E_USER_ERROR);

        return FALSE;
    }

    return $EncodedFB;

    /*
    //Force PUT by enabling uploading (implies PUT over HTTP)
    //Pa dentro$cURLOptions[CURLOPT_UPLOAD] = TRUE;

    //Set RETURNTRANSFER to avoid echoing the file NO EFFECT
    //$cURLOptions[CURLOPT_RETURNTRANSFER] = FALSE;

    //Perform the transaction. Will either get a response or FALSE
    //function cURLWebServiceCaller($HTTPMethod, $URL, $Parameters = NULL, $Encoding = NULL, $cURLOptions = NULL, $Path = NULL)
    $Result = cURLWebServiceCaller('PUT', $URL, NULL, NULL, $cURLOptions, $Origin);

    return $Result;
 * 
 */
}

/**
 * cURLSimpleFilePUT
 * Uploads a file to a destination using PUT with fixed, known to work options
 * @param   string  $Origin        The file to upload, code will check that is readable
 * @param   string  $URL           Destination
 * @return  mixed   JSON on correct and erroneous calls, FALSE on validation errors
 * @since 0.0.9
 * @todo
 * @see We follow https://curl.se/libcurl/c/httpput.html
 */
function cURLSimpleFilePUT($Origin, $URL)
{
    if (DEBUGMODE)
    {
        echo date("Y-m-d H:i:s").' -> cURLSimpleFilePUT '.PHP_EOL;
    }

    //Validation
    if (is_readable($Origin) === FALSE)
    {
        //If it already exists, must be writeable
        ErrorLog('Unreadable FILE', E_USER_ERROR);

        return FALSE;
    }

    if (!IsValidURI($URL))
    {
        ErrorLog('Invalid URI', E_USER_ERROR);

        return FALSE;
    }

    $TheFile = fopen($Origin, 'rb');
    if ($TheFile === FALSE)
    {
        ErrorLog('Error opening FILE for reading', E_USER_ERROR);

        return FALSE;
    }

    //Init cURL lib
    $cURL = curl_init();
    if ($cURL === FALSE)
    {
        ErrorLog('Error initialising cURL', E_USER_ERROR);

        return FALSE;
    }

    //Pass URL
    if (!curl_setopt($cURL, CURLOPT_URL,  $URL))
    {
        ErrorLog('Error setting called URL', E_USER_ERROR);

        return FALSE;
    }

    //Force PUT by enabling uploading (implies PUT over HTTP)
    if (!curl_setopt($cURL, CURLOPT_UPLOAD, TRUE))
    {
        ErrorLog('Error setting PUT operation', E_USER_ERROR);

        return FALSE;
    }


    //File to read from
    if (!curl_setopt($cURL, CURLOPT_READDATA,  $TheFile))
    {
        ErrorLog('Error reading origin file', E_USER_ERROR);

        return FALSE;
    }
    //echo 'Tamaño: '.filesize($Origin).PHP_EOL;
    if (!curl_setopt($cURL, CURLOPT_INFILESIZE,  filesize($Origin)))
    {
        ErrorLog('Error setting origin file size', E_USER_ERROR);

        return FALSE;
    }

    //Now curl_exec returns the response on success, FALSE on failure. HTTP errors are successful transactions
    $Response = curl_exec($cURL);
    if ($Response === FALSE)
    {
        //Something failed, so lets capture error message and number
        $ErrMss = curl_error($cURL);
        $ErrNo = curl_errno($cURL);
        $ErrorMessage = 'cURL Error #'.$ErrNo.':'.$ErrMss.' trying to reach: '.$URL;
        ErrorLog($ErrorMessage, E_USER_ERROR);

        return FALSE;
    }

    //Get the HTTP code of the transaction
    $HTTPCode = curl_getinfo($cURL,  CURLINFO_RESPONSE_CODE);
    if ($HTTPCode === FALSE)
    {
        ErrorLog('Error getting transaction info', E_USER_ERROR);

        return FALSE;
    }

    //Close the cURL session, no use from PHP 8 on
    if (PHP_VERSION_ID>=80000)
    {
        curl_close($cURL);
    }

    //Close the file handler
    $Closure = fclose($TheFile);
    if ($Closure === FALSE)
    {
        ErrorLog('Error closing FILE', E_USER_ERROR);

        return FALSE;
    }

    //HTTP Code to be processed Upstream, we are only the callers
    $Feedback['HTTPCode'] = $HTTPCode;
    $Feedback['Response'] = $Response;
    $EncodedFB = json_encode($Feedback,  JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_NUMERIC_CHECK);
    if ($EncodedFB === FALSE)
    {
        ErrorLog('Error JSON-encoding server response', E_USER_ERROR);

        return FALSE;
    }

    return $EncodedFB;
}

/**
 * cURLSimpleFilePOST
 * Uploads a file to a destination using POST with fixed, known to work options
 * @param   string  $Origin        The file to upload, code will check that is readable
 * @param   string  $URL           Destination
 * @param   string  $Destination   A path to store the file or just a new filename. Can be NULL
 * @return  mixed   JSON on correct and erroneous calls, FALSE on validation errors
 * @since 0.0.9
 * @todo
 * @see
 */
function cURLSimpleFilePOST($Origin, $URL, $Destination = NULL)
{
    if (DEBUGMODE)
    {
        echo date("Y-m-d H:i:s").' -> cURLSimpleFilePOST '.PHP_EOL;
    }

    //Validation
    if (is_readable($Origin) === FALSE)
    {
        echo $Origin.' is NOT readable!'.PHP_EOL;
        //If it already exists, must be readable
        ErrorLog('Unreadable FILE', E_USER_ERROR);

        return FALSE;
    }
    else
    {
        echo $Origin.' is readable!'.PHP_EOL;
    }

    if (!IsValidURI($URL))
    {
        ErrorLog('Invalid URI', E_USER_ERROR);

        return FALSE;
    }

    //Prepare the file to upload
    if (empty($Destination))
    {
        $CurlFile = curl_file_create($Origin, mime_content_type($Origin));
    }
    else
    {
        //echo 'Destination: '.$Destination.' & Origin: '.$Origin.' ('.mime_content_type($Origin).')'.PHP_EOL;
        $CurlFile = curl_file_create($Origin, mime_content_type($Origin), $Destination);
        //print_r($CurlFile);
    }

    //Init cURL lib
    $cURL = curl_init();
    if ($cURL === FALSE)
    {
        ErrorLog('Error initialising cURL', E_USER_ERROR);

        return FALSE;
    }

    //Pass URL
    if (!curl_setopt($cURL, CURLOPT_URL,  $URL))
    {
        ErrorLog('Error setting called URL', E_USER_ERROR);

        return FALSE;
    }

    //Establish POST
    if (!curl_setopt($cURL, CURLOPT_POST,  TRUE))
    {
        ErrorLog('Error setting PUT oeration', E_USER_ERROR);

        return FALSE;
    }

    //postfields
    $ThePost = array(
        "upload" => $CurlFile
        //, "KEY" => "VALUE" More data could be appended if needed
    );

    if (!curl_setopt($cURL, CURLOPT_POSTFIELDS,  $ThePost))
    {
        ErrorLog('Error setting PUT oeration', E_USER_ERROR);

        return FALSE;
    }

    //Enable return transfer
    if (!curl_setopt($cURL, CURLOPT_RETURNTRANSFER,  TRUE))
    {
        ErrorLog('Error enabling return transfer', E_USER_ERROR);

        return FALSE;
    }

    //Now curl_exec returns the response on success, FALSE on failure. HTTP errors are successful transactions
    $Response = curl_exec($cURL);
    if ($Response === FALSE)
    {
        //Something failed, so lets capture error message and number
        $ErrMss = curl_error($cURL);
        $ErrNo = curl_errno($cURL);
        $ErrorMessage = 'cURL Error #'.$ErrNo.':'.$ErrMss.' trying to reach: '.$URL;
        ErrorLog($ErrorMessage, E_USER_ERROR);

        return FALSE;
    }

    //Get the HTTP code of the transaction
    $HTTPCode = curl_getinfo($cURL,  CURLINFO_RESPONSE_CODE);
    if ($HTTPCode === FALSE)
    {
        ErrorLog('Error getting transaction info', E_USER_ERROR);

        return FALSE;
    }

    //Close the cURL session, no use from PHP 8 on
    if (PHP_VERSION_ID>=80000)
    {
        curl_close($cURL);
    }

    //HTTP Code to be processed Upstream, we are only the callers
    $Feedback['HTTPCode'] = $HTTPCode;
    $Feedback['Response'] = $Response;
    $EncodedFB = json_encode($Feedback,  JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_NUMERIC_CHECK);
    if ($EncodedFB === FALSE)
    {
        ErrorLog('Error JSON-encoding server response', E_USER_ERROR);

        return FALSE;
    }

    return $EncodedFB;
}

/**
 * cURLFilePOST
 * Uploads a file to a destination using POST
 * @param   string  $Origin        The file to upload, code will check that is readable
 * @param   string  $URL           Destination
 * @param   string  $Destination   A path to store the file or just a new filename. Can be NULL
 * @param   array   $cURLOptions   Array of cURL options to set
 * @return  mixed   JSON on correct and erroneous calls, FALSE on validation errors
 * @since 0.0.9
 * @todo
 * @see
 */
function cURLFilePOST($Origin, $URL, $Destination = NULL, $cURLOptions = NULL)
{
    if (DEBUGMODE)
    {
        echo date("Y-m-d H:i:s").' -> cURLSimpleFilePOST '.PHP_EOL;
    }

    //Validation
    if (is_readable($Origin) === FALSE)
    {
        echo $Origin.' is NOT readable!'.PHP_EOL;
        //If it already exists, must be readable
        ErrorLog('Unreadable FILE', E_USER_ERROR);

        return FALSE;
    }
    else
    {
        echo $Origin.' is readable!'.PHP_EOL;
    }

    if (!IsValidURI($URL))
    {
        ErrorLog('Invalid URI', E_USER_ERROR);

        return FALSE;
    }

    //Prepare the file to upload
    if (empty($Destination))
    {
        $CurlFile = curl_file_create($Origin, mime_content_type($Origin));
    }
    else
    {
        //echo 'Destination: '.$Destination.' & Origin: '.$Origin.' ('.mime_content_type($Origin).')'.PHP_EOL;
        $CurlFile = curl_file_create($Origin, mime_content_type($Origin), $Destination);
        //print_r($CurlFile);
    }

    //Init cURL lib
    $cURL = curl_init();
    if ($cURL === FALSE)
    {
        ErrorLog('Error initialising cURL', E_USER_ERROR);

        return FALSE;
    }

    //Pass URL
    if (!curl_setopt($cURL, CURLOPT_URL,  $URL))
    {
        ErrorLog('Error setting called URL', E_USER_ERROR);

        return FALSE;
    }

    //Establish POST
    if (!curl_setopt($cURL, CURLOPT_POST,  TRUE))
    {
        ErrorLog('Error setting PUT oeration', E_USER_ERROR);

        return FALSE;
    }

    //postfields
    $ThePost = array(
        "upload" => $CurlFile
        //, "KEY" => "VALUE" More data could be appended if needed
    );

    if (!curl_setopt($cURL, CURLOPT_POSTFIELDS,  $ThePost))
    {
        ErrorLog('Error setting PUT oeration', E_USER_ERROR);

        return FALSE;
    }

    //Enable return transfer
    if (!curl_setopt($cURL, CURLOPT_RETURNTRANSFER,  TRUE))
    {
        ErrorLog('Error enabling return transfer', E_USER_ERROR);

        return FALSE;
    }

    //"" = Allow all supported encoding types
    if (!curl_setopt($cURL, CURLOPT_ENCODING, TRUE))
    {
        ErrorLog('Error setting CURLOPT_ENCODING option', E_USER_ERROR);

        return FALSE;
    }

    //10 redirections
    if (!curl_setopt($cURL, CURLOPT_MAXREDIRS, 10))
    {
        ErrorLog('Error setting CURLOPT_MAXREDIRS option', E_USER_ERROR);

        return FALSE;
    }

    //TRUE to follow any "Location: " header that the server sends
    if (!curl_setopt($cURL, CURLOPT_FOLLOWLOCATION, TRUE))
    {
        ErrorLog('Error setting CURLOPT_FOLLOWLOCATION option', E_USER_ERROR);

        return FALSE;
    }

    //No timeout whatsoever
    if (!curl_setopt($cURL, CURLOPT_TIMEOUT, 0))
    {
        ErrorLog('Error setting CURLOPT_TIMEOUT option', E_USER_ERROR);

        return FALSE;
    }

    //Use HTTP1.1, better safe than sorry
    if (!curl_setopt($cURL, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1))
    {
        ErrorLog('Error setting CURLOPT_HTTP_VERSION option', E_USER_ERROR);

        return FALSE;
    }

    //Set user options. Might overwrite our own
    if (!empty($cURLOptions))
    {
        foreach ($cURLOptions as $Key => $Value)
        {
            if (!is_array($Value))
            {
                $Textd = $Value;
            }
            else
            {
                $Textd = json_encode($Value, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_NUMERIC_CHECK);
            }
            echo  'Setting '.cURLOptionConstantToLiteral($Key).' as '.$Textd.PHP_EOL;
            if (!curl_setopt($cURL, $Key,  $Value))
            {
                ErrorLog('Error setting '.$Key.' as '.$Textd, E_USER_ERROR);

                return FALSE;
            }
        } //End for each option to set
    }

    //Now curl_exec returns the response on success, FALSE on failure. HTTP errors are successful transactions
    $Response = curl_exec($cURL);
    if ($Response === FALSE)
    {
        //Something failed, so lets capture error message and number
        $ErrMss = curl_error($cURL);
        $ErrNo = curl_errno($cURL);
        $ErrorMessage = 'cURL Error #'.$ErrNo.':'.$ErrMss.' trying to reach: '.$URL;
        ErrorLog($ErrorMessage, E_USER_ERROR);

        return FALSE;
    }

    //Get the HTTP code of the transaction
    $HTTPCode = curl_getinfo($cURL,  CURLINFO_RESPONSE_CODE);
    if ($HTTPCode === FALSE)
    {
        ErrorLog('Error getting transaction info', E_USER_ERROR);

        return FALSE;
    }

    //Close the cURL session, no use from PHP 8 on
    if (PHP_VERSION_ID>=80000)
    {
        curl_close($cURL);
    }

    //HTTP Code to be processed Upstream, we are only the callers
    $Feedback['HTTPCode'] = $HTTPCode;
    $Feedback['Response'] = $Response;
    $EncodedFB = json_encode($Feedback,  JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_NUMERIC_CHECK);
    if ($EncodedFB === FALSE)
    {
        ErrorLog('Error JSON-encoding server response', E_USER_ERROR);

        return FALSE;
    }

    return $EncodedFB;
}
