<?php

/*
 * ErrorLogging
 * Error logging front-end to handle the logic warnings
 * @author ProceduralMan <proceduralman@gmail.com>
 * @copyright 2021
 * @version 1.0 initial version
 * @package Minion
 * @todo 
 * @see  
 */

require_once __DIR__.'/../Configuration/Configuration.php';

/**
 * ErrorLog
 * ErrorLog is a front end for user-facing function to log the errors in an standard way if they are using the standard logging
 * as well as if the Minion errors are used
 * @param string    $ErrorMessage the error message
 * @param int       $ErrorType The PHP errofunc constants https://www.php.net/manual/en/errorfunc.constants.php
 */
function ErrorLog($ErrorMessage, $ErrorType)
{
    if (isset($GLOBALS['Loggers']))
    {
        $StandardLogging = FALSE;
    }
    else
    {
        $StandardLogging = TRUE;
    }

    $ErrorLegend = ErrorConstantToLiteral($ErrorType);

    switch ($ErrorType)
    {
        case E_ERROR:
        case E_CORE_ERROR:
        case E_COMPILE_ERROR:
        case E_PARSE:
            $WrongConstantAlert = 'An error of type '.$ErrorLegend.' has been triggered when only E_USER_ERROR should be used. Review your code';
            if ($StandardLogging)
            {
                trigger_error($ErrorMessage, E_USER_ERROR);
                trigger($WrongConstantAlert, E_USER_NOTICE);
            }
            else
            {
                AddCritical($ErrorMessage);
                AddNotice($WrongConstantAlert);
            }
            break;
        case E_WARNING:
        case E_CORE_WARNING:
        case E_COMPILE_WARNING:
            $WrongConstantAlert = 'A warning of type '.$ErrorLegend.' has been triggered when only E_USER_WARNING should be used. Review your code';
            if ($StandardLogging)
            {
                trigger_error($ErrorMessage, E_USER_WARNING);
                trigger($WrongConstantAlert, E_USER_NOTICE);
            }
            else
            {
                AddWarning($ErrorMessage);
                AddNotice($WrongConstantAlert);
            }
            break;
        case E_NOTICE:
            $WrongConstantAlert = 'A notice of type '.$ErrorLegend.' has been triggered when only E_USER_NOTICE should be used. Review your code';
            if ($StandardLogging)
            {
                trigger_error($ErrorMessage, E_USER_NOTICE);
                trigger($WrongConstantAlert, E_USER_NOTICE);
            }
            else
            {
                AddNotice($ErrorMessage);
                AddNotice($WrongConstantAlert);
            }
            break;
        case E_USER_ERROR:
            if ($StandardLogging)
            {
                trigger_error($ErrorMessage, E_USER_ERROR);
            }
            else
            {
                AddError($ErrorMessage);
            }
            break;
        case E_USER_WARNING:
            if ($StandardLogging)
            {
                trigger_error($ErrorMessage, E_USER_WARNING);
            }
            else
            {
                AddWarning($ErrorMessage);
            }
            break;
        case E_USER_NOTICE:
            if ($StandardLogging)
            {
                trigger_error($ErrorMessage, E_USER_NOTICE);
            }
            else
            {
                AddNotice($ErrorMessage);
            }
            break;
        case E_USER_DEPRECATED:
            if ($StandardLogging)
            {
                trigger_error($ErrorMessage, E_USER_DEPRECATED);
            }
            else
            {
                AddInfo($ErrorMessage);
            }
            break;
        default:
            $WrongConstantAlert = 'An error of type '.$ErrorLegend.' has been triggered (int value '.$ErrorType.'). Review your code';
            if ($StandardLogging)
            {
                trigger_error($ErrorMessage, E_USER_ERROR);
                trigger($WrongConstantAlert, E_USER_ERROR);
            }
            else
            {
                AddAlert($ErrorMessage);
                AddError($WrongConstantAlert);
            }
            break;
    }
}
