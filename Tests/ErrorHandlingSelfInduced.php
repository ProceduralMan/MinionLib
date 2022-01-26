<?php

/*
 * ErrorHandlingSelfInduced
 * To test self-induced errors on Minion Error Handling 
 * Logic Flow (file logging):
 * Main    
 *  -> RegisterLogger
 *      -> ErrorHandlingSanityCheck
 *          -> ChannelSanityCheck
 *          -> DestinationSanityCheck
 *              -> FilesDestinationSanityCheck
 *          -> FormatterSanityCheck
 *      -> BootStrapLogger
 *  -> b
 *      -> c
 *          -> AddError
 *            -> ProcessError
 *      -> AddError
 *          -> ProcessError
 *  -> AddError
 *      -> ProcessError
 *  -> NotifyError
 *      -> PreprocessErrorData
 *      -> FormatErrorLog
 *          -> LineFormatter
 *      -> RegisterError
 *          -> Register2File
 * @author ProceduralMan <proceduralman@gmail.com>
 * @copyright 2021
 * @version 1.0 initial version
 * @package Minion Library
 * @todo
 */

//Autoload
//require_once 'vendor/autoload.php';

require_once '../Minion/MinionSetup.php';

//Testing sanity... one by one as they are defined
$FileOptions['Separator'] = MIL_TABSEPARATOR;
$FileOptions['LogPath'] = '/raid/logs/TestLog.log';

//Start Process
    echo date("Y-m-d H:i:s").' -> Main '.PHP_EOL;


$Result = RegisterLogger(MIL_FILES, MIL_FILE, MIL_CODECONTEXT, MIL_LINEFORMATTER, $FileOptions, DEBUG);

//Testing errors
//a is main
$BData['arg1'] = 'arg1value';
$BData['arg2'] = 'arg2value';
$BData['arg3'] = 'arg3value';
$OutData = array();
$Outcome = b($BData, $OutData);
if ($Outcome === FALSE)
{
    //Something goes wrong on my logic... add to heap
    AddError("Something bad came from b()");
    //As I am the upper processor, dump to log
    $NE = NotifyError();
}
else
{
    echo 'All good on b()'.PHP_EOL;
}

/**
 * b()
 * A skeleton mid-level function that fails
 * @param  type    $InData  fake in parameters
 * @param  type    $OutData fake outcome
 * @return boolean FALSE as always fails
 */
function b($InData, &$OutData)
{
    echo date("Y-m-d H:i:s").' -> b '.PHP_EOL;
    echo "b()->arg1 = ".$InData['arg1'].PHP_EOL;
    echo "b()->arg2 = ".$InData['arg2'].PHP_EOL;
    echo "b()->arg3 = ".$InData['arg3'].PHP_EOL;
    $CData['arg4'] = 'arg4value';
    $CData['arg5'] = 'arg5value';
    $CData['arg6'] = 'arg6value';
    $OutData['ReturnValue']['arg4'] = 'arg4value';
    $OutData['ReturnValue']['arg5'] = 'arg5value';
    $OutData['ReturnValue']['arg6'] = 'arg6value';

    $Outcome = c($CData, $OutData);
    if ($Outcome === FALSE)
    {
        //Something goes wrong on my logic... add to heap
        AddError("Something wrong came out of c()!");

        return FALSE;
    }
    else
    {
        echo 'All good on c()'.PHP_EOL;
    }
}

/**
 * c()
 * A skeleton low level function that fails
 * @param  type    $InData  fake in parameters
 * @param  type    $OutData fake outcome
 * @return boolean FALSE as always fails
 */
function c($InData, &$OutData)
{
    echo date("Y-m-d H:i:s").' -> c '.PHP_EOL;
    echo "c()->arg4 = ".$InData['arg4'].PHP_EOL;
    echo "c()->arg5 = ".$InData['arg5'].PHP_EOL;
    echo "c()->arg6 = ".$InData['arg6'].PHP_EOL;
    $OutData['ReturnValue']['arg7'] = 'arg7value';
    $OutData['ReturnValue']['arg8'] = 'arg8value';
    $OutData['ReturnValue']['arg9'] = 'arg9value';
    //Something bad goes on while processing...
    AddError("Whooopa! Something went VERY wrong!");

    return FALSE;
}
