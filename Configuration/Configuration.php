<?php

/*
 * Configuration
 * Useful functions for configuring process or program behaviour
 * @author ProceduralMan <proceduralman@gmail.com>
 * @copyright 2021
 * @version 1.0 initial version
 * @package Minion
 * @todo
 * @see
 */

/**
 * ErrorConstantToLiteral turns standard int error constants to error literals
 * @param   int     ErrorType
 * @param mixed $ErrorType
 * @return  string  The error literal
 * @since 0.0.1
 * @see
 * @todo
 */
function ErrorConstantToLiteral($ErrorType)
{
    switch ($ErrorType) {
        case E_ERROR:
            return 'E_ERROR';
        case E_CORE_ERROR:
            return 'E_CORE_ERROR';
        case E_COMPILE_ERROR:
            return 'E_COMPILE_ERROR';
        case E_PARSE:
            return 'E_PARSE';
        case E_WARNING:
            return 'E_WARNING';
        case E_NOTICE:
            return 'E_NOTICE';
        case E_CORE_WARNING:
            return 'E_CORE_WARNING';
        case E_COMPILE_WARNING:
            return 'E_COMPILE_WARNING';
        case E_USER_ERROR:
            return 'E_USER_ERROR';
        case E_USER_WARNING:
            return 'E_USER_WARNING';
        case E_USER_NOTICE:
            return 'E_USER_NOTICE';
        case E_USER_DEPRECATED:
            return 'E_USER_DEPRECATED';
        default:
            //Gracefully fail without logging to avoid deadlocking
            return 'UNKNOWN_CODE';
    }
}
