<?php

/*
 * APCU
 * Functions for interacting with and monitoring APCU cache
 * @author ProceduralMan <proceduralman@gmail.com>
 * @copyright 2022
 * @version 1.0 initial version
 * @package Minion
 * @todo
 * @see https://github.com/krakjoe/apcu/
 */

//If we have been required/included, run the setter
APCUDefaultsSetter();

/**
 * APCUDefaultsSetter 
 * sets the default config flags if APCU is running on the system
 * @since 0.0.7
 * @see
 * @todo
 */
function APCUDefaultsSetter()
{
    if (DEBUGMODE)
    {
        echo date("Y-m-d H:i:s").' -> APCUDefaultsSetter '.PHP_EOL;
    }

    if (function_exists('apcu_cache_info') === FALSE)
    {
        //ACPU disabled flag
        Defaults("MIL_ACPU", FALSE);
        $ErrorMessage = "No cache info available.  APCU does not appear to be running.";
        ErrorLog($ErrorMessage, E_USER_NOTICE);
    }
    else
    {
        //ACPU enabled flag
        Defaults("MIL_ACPU", TRUE);
    }
}

/**
 * IsAPCURunning
 * Checks if APCU is enabled
 * @return boolean TRUE si APCU funciona, FALSE en caso contrario
 * @since 0.0.7
 * @see
 * @todo
 */
function IsAPCURunning()
{
    if (DEBUGMODE)
    {
        echo date("Y-m-d H:i:s").' -> IsAPCURunning '.PHP_EOL;
    }

    if (function_exists('apcu_cache_info') === FALSE)
    {
        return FALSE;
    }
    else
    {
        return TRUE;
    }
}

/**
 * APCUStatus
 * Returns APCU status info
 * @return array APCU Status Info
 * @since 0.0.7
 * @see https://www.php.net/manual/es/apcu.configuration.php
 * @todo Incluir cálculos de fragmentación de https://github.com/krakjoe/apcu/blob/master/apc.php L858, cuando sea necesario
 */
function APCUStatus()
{
    if (DEBUGMODE)
    {
        echo date("Y-m-d H:i:s").' -> APCUStatus '.PHP_EOL;
    }

    $Status = array();
    if (IsAPCURunning() === TRUE)
    {
        /*
         * apcu_cache_info() = apcu_cache_info(FALSE) gives
         * Array
         * (
         *     [num_slots] => 4099
         *     [ttl] => 0
         *     [num_hits] => 0
         *     [num_misses] => 0
         *     [num_inserts] => 0
         *     [num_entries] => 0
         *     [expunges] => 0
         *     [start_time] => 1650640310
         *     [mem_size] => 0
         *     [memory_type] => mmap
         *     [cache_list] => Array
         *         (
         *         )
         * 
         *     [deleted_list] => Array
         *         (
         *         )
         * 
         *     [slot_distribution] => Array
         *         (
         *         )
         * 
         * )
         * 
         * apcu_cache_info(TRUE) gives
         * Array
         * (
         *     [num_slots] => 4099          //max number of slots (=values) on the cache. Managed by configuration directive apc.entries_hint
         *     [ttl] => 0                   //seconds to live. The number of seconds a cache entry is allowed to idle in a slot in case this cache entry slot is
         *                                       needed by another entry. Managed by configuration directive apc.ttl
         *     [num_hits] => 0              //Global number of cache hits.
         *     [num_misses] => 0            //Global number of cache misses
         *     [num_inserts] => 0           //Number of inserts
         *     [num_entries] => 0           //Number of entries
         *     [expunges] => 0              //Cache full count
         *     [start_time] => 1650640557   //APCU start time
         *     [mem_size] => 0              //Memory size
         *     [memory_type] => mmap        //Memory type
         * )
         * 
         */
        $APCUInfo = apcu_cache_info(TRUE);

        $Status['Status'] = 'APCU running since '.date('Y-m-d H:i:s',$APCUInfo['start_time']);
        $Status['apc_version'] = phpversion('apcu');
        $Status['php_version'] = phpversion();
        $ServerName = filter_input(INPUT_SERVER, 'SERVER_NAME');
        if (!empty($ServerName))
        {
            $Status['apcu_host'] = $ServerName;
        }
        $ServerSW = filter_input(INPUT_SERVER, 'SERVER_SOFTWARE');
        if (!empty($ServerName))
        {
            $Status['server_sw'] = $ServerSW;
        }
        $ElapsedTime = time()-$APCUInfo['start_time'];
        $Status['num_slots'] = $APCUInfo['num_slots'];
        $Status['ttl'] = $APCUInfo['ttl'];
        $Status['num_hits'] = $APCUInfo['num_hits'];
        $TotalTries = $APCUInfo['num_hits']+$APCUInfo['num_misses'];
        if ($TotalTries>0)
        {
            $HitsPercentage = $APCUInfo['num_hits']*100/$TotalTries; //The higher the better
        }
        else
        {
            $HitsPercentage = 0;
        }
        $Status['hits_perc'] = round($HitsPercentage, 2);
        $Status['num_misses'] = $APCUInfo['num_misses'];
        if ($TotalTries>0)
        {
            $MissesPercentage = $APCUInfo['num_misses']*100/$TotalTries; //The lower the better
        }
        else
        {
            $MissesPercentage = 0;
        }
        $Status['misses_perc'] = round($MissesPercentage, 2);
        if ($TotalTries>0)
        {
            $Status['req_rate'] = $TotalTries/$ElapsedTime;
        }
        else
        {
            $Status['req_rate'] = 0;
        }
        if ($APCUInfo['num_hits']>0)
        {
            $Status['hit_rate'] = $APCUInfo['num_hits']/$ElapsedTime;
        }
        else
        {
            $Status['hit_rate'] = 0;
        }
        if ($APCUInfo['num_misses']>0)
        {
            $Status['miss_rate'] = $APCUInfo['num_misses']/$ElapsedTime;
        }
        else
        {
            $Status['miss_rate'] = 0;
        }
        $Status['num_inserts'] = $APCUInfo['num_inserts'];
        if ($APCUInfo['num_inserts']>0)
        {
            $Status['inserts_rate'] = $APCUInfo['num_inserts']/$ElapsedTime;
        }
        else
        {
            $Status['inserts_rate'] = 0;
        }
        $Status['num_entries'] = $APCUInfo['num_entries'];
        $Status['expunges'] = $APCUInfo['expunges'];
        $Status['start_time'] = date('Y-m-d H:i:s', $APCUInfo['start_time']);
        $Status['uptime'] = HRLapse($APCUInfo['start_time']);
        $Status['mem_size'] = $APCUInfo['mem_size'];
        $Status['memory_type'] = $APCUInfo['memory_type'];
        /*
         * pcu_sma_info() = apcu_sma_info(FALSE) gives
         * Array
         * (
         *     [num_seg] => 1
         *     [seg_size] => 33554312
         *     [avail_mem] => 33521328
         *     [block_lists] => Array
         *         (
         *             [0] => Array
         *                 (
         *                     [0] => Array
         *                         (
         *                             [size] => 33521296
         *                             [offset] => 33072
         *                         )
         * 
         *                 )
         * 
         *         )
         * 
         * )
         * 
         * apcu_sma_info(TRUE) gives 
         * Array
         * (
         *     [num_seg] => 1           //The number of shared memory segments to allocate for the compiler cache. Managed by configuration 
         *                                  directive apc.shm_segments
         *     [seg_size] => 33554312   //The size of each shared memory segment in bytes. Managed by configuration directive apc.shm_size that may use 
         *                                  shorthand notation (K (for Kilobytes), M (for Megabytes) and G (for Gigabytes), and are all case-insensitive. 
         *                                  Anything else assumes bytes.) 
         *                                  
         *     [avail_mem] => 33521328  //Memory availabla for data
         * )
         */
        $APCUMem = apcu_sma_info(TRUE);
        $Status['num_seg'] = $APCUMem['num_seg'];
        $Status['seg_size'] = $APCUMem['seg_size'];
        $SegmentSizeBytes = IniShorthand2Int($APCUMem['seg_size']);
        if ($SegmentSizeBytes === FALSE)
        {
            //If the shorthand gives rubbish we set the memory to 0
            $SegmentSizeBytes = 0;
        }
        $MemorySize = $APCUMem['num_seg']*$SegmentSizeBytes;
        $Status['mem_size'] = $MemorySize;
        $AvailableMemory = $APCUMem['avail_mem'];
        $Status['avail_mem'] = $APCUMem['avail_mem'];
        $MemoryUsed = $MemorySize-$AvailableMemory;
        $Status['mem_used'] = $MemoryUsed;
        $RuntimeConfig = ini_get_all('apcu');
        foreach ($RuntimeConfig as $Key => $Value)
        {
            $Status['runtime_config'][$Key] = $Value['local_value'];
        }
    }
    else
    {
        $Status['Status'] = 'APCU does not appear to be running';
    }

    return $Status;
}

/**
 * APCU2Array returns data from APCU cache
 * APCU Stored array has a special structure
 * array(2) {
 *   ["LastDBWrite"]=>
 *   int(1650610696)
 *   ["Data"]=>
 *   array(xx) {...
 *   }
 * }
 * So we get only 'Data'
 * 
 * @param   string  $Key            The key under which the data is stored at the cache
 * @param   array   $Data           The var to load data into
 * @return  boolean TRUE on success, FALSE on failure
 * @return  mixed   $Data by reference
 * @since 0.0.7
 * @see
 * @todo 
 */
function APCU2Array($Key, &$Data)
{
    if (DEBUGMODE)
    {
        echo date("Y-m-d H:i:s").' -> APCU2Array '.PHP_EOL;
    }

    $FetchSuccess = FALSE;
    $Data = apcu_fetch($Key, $FetchSuccess);
    if ($FetchSuccess === FALSE)
    {
        //Error getting the data
        $ErrorMessage = "Error getting the data for key ".$Key;
        ErrorLog($ErrorMessage, E_USER_NOTICE);
        //echo '->fetch error';

        return FALSE;
    }
    //$Data = $TempData['Data'];

    return TRUE;
}

/**
 * Array2APCU writes data to APCU cache
 * Does not check if returned data is an array, so it can be anything
 * But, if $Persistable is TRUE the array must be associative or it will fail
 * @param   array   $Data           The data to cache
 * @param   string  $Key            The key to store the data under
 * @param   int     $TimeToLive     The seconds before the data gets cleaned off the cache
 * @param   array   $PersistInfo    Structure that holds needed data when persisting the cache data to DB
 *                      'Persistable'       The flag that marks if it is a persistable array or a volatile one
 *                      'TableName'         The table to persist to
 *                      'ConnectionIndex'   The connection to use
 *                      'FullRewrite'       The flag that marks if we should rewrite the table with the cache data
 *                      'ParametricKeys'    The array of paremtric keys for numeric arrays... if is set, logic will assume the array is numerioc
 * @return  boolean TRUE on success, FALSE on failure
 * @return  mixed   $Data by reference
 * @since 0.0.7
 * @see
 * @todo
 */
function Array2APCU($Data, $Key, $TimeToLive = 0, $PersistInfo = NULL)
{
    if (DEBUGMODE)
    {
        echo date("Y-m-d H:i:s").' -> Array2APCU '.'*** ARRAY COUNT***'.count($Data).PHP_EOL;
    }
    //print_r($Data);
    $WriteToDB = FALSE;
    if ((isset($PersistInfo['Persistable']))&&($PersistInfo['Persistable'] === TRUE))
    {
        //Get the data
        $LastDBWrite = APCULastDBWrite($Key);
        if ($LastDBWrite === FALSE)
        {
            //Error getting APCU info / first time trying
            $LastDBWrite = strtotime('2022-01-27'); //MinionLib 0.0.1 launched on this date... in any case longer ago than MIL_ACPUPERSISTLAPSE lapse
        }
        if (MRLapse($LastDBWrite, 'S')>MIL_ACPUPERSISTLAPSE)
        {
            $WriteToDB = TRUE;
            $Data['LastDBWrite'] = time();
        }
        else
        {
            $Data['LastDBWrite'] = $LastDBWrite;
        }
    }
    else
    {
        $Data['LastDBWrite'] = '***VOLATILE CACHE***';
    }
    //$TempData['Data'] = $Data;
    //echo '*** DATA COUNT***'.count($TempData['Data']).PHP_EOL;
    //print_r($Data);
    $Result = apcu_store($Key, $Data, $TimeToLive);
    if ($Result === FALSE)
    {
        $Message = 'Error registering connection over APCU';
        ErrorLog($Message, E_USER_ERROR);

        return FALSE;
    }
    //Write to DB if necessary
    if ($WriteToDB === TRUE)
    {
        //If it is numeric
        if (isset($PersistInfo['ParametricKeys']))
        {
            NumericToTable($Data, $PersistInfo['TableName'], $PersistInfo['ConnectionIndex'], $PersistInfo['ParametricKeys'], $PersistInfo['FullRewrite']);
        }
        else
        {
            AssocToTable($Data, $PersistInfo['TableName'], $PersistInfo['ConnectionIndex'], $PersistInfo['FullRewrite']);
        }
    }

    return TRUE;
}

/**
 * APCULastDBWrite checks the last time a cache object was persisted to DB
 * @param   string  $Key    The cache key to check
 * @return  mixed   Last DB write time or FALSE if error
 */
function APCULastDBWrite($Key)
{
    //Beggining time
    $FirstWrite = strtotime('2022-01-27'); //MinionLib 0.0.1 launched on this date... in any case longer ago than MIL_ACPUPERSISTLAPSE lapse

    if (DEBUGMODE)
    {
        echo date("Y-m-d H:i:s").' -> APCULastDBWrite '.PHP_EOL;
    }

    $FetchSuccess = FALSE;
    $Data = apcu_fetch($Key, $FetchSuccess);
    if ($FetchSuccess === FALSE)
    {
        //Error getting the data
        $ErrorMessage = "Error getting the data for key ".$Key;
        ErrorLog($ErrorMessage, E_USER_NOTICE);

        return FALSE;
    }
    if (!isset($Data['LastDBWrite']))
    {
        //It's an invalid structure. We return the first 100 chars for reference
        $RefContent = print_r($Data, TRUE);
        $ErrorMessage = $Key." has an invalid structure: ".substr($RefContent, 0, 100);
        ErrorLog($ErrorMessage, E_USER_ERROR);
    }
    elseif (is_string($Data['LastDBWrite']))
    {
        //We can anytime decide to persist a volatile structure
        return $FirstWrite;
    }

    return $Data['LastDBWrite'];
}
