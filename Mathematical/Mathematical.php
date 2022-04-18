<?php

/*
 * Mathematical
 * Mathematical utility functions
 *
 * - IsEven validates if the number is even
 * - IsOdd validates if the number is odd
 * @author ProceduralMan <proceduralman@gmail.com>
 * @copyright 2022
 * @version 1.0 initial version
 * @package Minion
 * @todo
 * @see
 *
 */

/**
 * IsEven validates if the number is even
 * @param   int     $Number The integer to test
 * @return  boolean
 * @since 0.0.6
 * @see 
 * @todo
 */
function IsEven($Number)
{
    if (DEBUGMODE)
    {
        echo date("Y-m-d H:i:s").' -> IsEven'.PHP_EOL;
    }

    if ($Number%2 === 0)
    {
        //Even
        return TRUE;
    }
    else
    {
        //Odd
        return FALSE;
    }
}

/**
 * IsOdd validates if the number is odd
 * @param   int     $Number The integer to test
 * @return  boolean
 * @since 0.0.6
 * @see 
 * @todo
 */
function IsOdd($Number)
{
    if (DEBUGMODE)
    {
        echo date("Y-m-d H:i:s").' -> IsOdd'.PHP_EOL;
    }

    if ($Number%2 === 0)
    {
        //Even
        return FALSE;
    }
    else
    {
        //Odd
        return TRUE;
    }
}
