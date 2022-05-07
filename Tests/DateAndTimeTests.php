<?php

/*
 * DateAndTimeTests
 * To test DateAndTime functions
 * @author ProceduralMan <proceduralman@gmail.com>
 * @copyright 2021-2022
 * @version 1.0 initial version
 * @package Minion Library
 * @todo sacar de 3v4l diferentes times
 */

require_once __DIR__.'/../MinionSetup.php';


//The Unix epoch is 00:00:00 UTC on 1 January 1970
//PHP_INT_MIN = -9223372036854775808
//PHP_INT_MAX = 9223372036854775807

$TestSuiteEpocs = array(
    1650610696,             //Time when this test was built (2022-04-22T06:58:16+00:00)
    1,                      //00:00:01 UTC on 1 January 1970
    -9223372036854775808,   //PHP_INT_MIN. Under that, it just takes that
    9223372036854775807,    //PHP_INT_MAX. Over that, it takes it as a float and shouts a type error
    //-9223372036854775818,   //Under PHP_INT_MIN
    //9223372036854775817,    //Over PHP_INT_MAX
);

foreach ($TestSuiteEpocs as $Value)
{
    $LapseString = HRLapse($Value);
    echo $LapseString.' have elapsed since '.date('Y-m-d H:i:s',$Value).PHP_EOL;
}
