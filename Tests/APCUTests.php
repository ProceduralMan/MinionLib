<?php

/*
 * APCUTests
 * To test APCU caching functions
 * @author ProceduralMan <proceduralman@gmail.com>
 * @copyright 2021-2022
 * @version 1.0 initial version
 * @package Minion Library
 * @todo
 */

require_once __DIR__.'/../MinionSetup.php';

$APCUSTatus = APCUStatus();
print_r($APCUSTatus);
