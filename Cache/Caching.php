<?php

/*
 * Caching
 * Functions for interacting with caches
 * @author ProceduralMan <proceduralman@gmail.com>
 * @copyright 2022
 * @version 1.0 initial version
 * @package Minion
 * @todo
 * @see 
 */

//Cache-especific includes
require_once 'APCU.php';

/**
 * PersistCache
 * Reads from cache and stores in table using Array2Table (SQLDB)
 * Expects MYSMA structures
 * @param   string  $Key                The cache key under which the data is stored
 * @param   array   $DBConnection       Connection index to use when reading from database
 * @param   string  $DestinationTable   Table to query if cache reading fails
 * @param   boolean $FullRewrite        TRUE if the table is to be deleted and rewritten with the array info, false otherwise
 * @param   int     $TimeToLive         The time for the cache to live
 * @param   string  $CacheType          The cache type, either APCU or REDIS by now
 * @return  boolean TRUE if persisted successfully, FALSE otherwise
 * @since   0.0.7
 * @see
 * @todo
 */
function PersistCache($Key, $DBConnection, $DestinationTable,  $FullRewrite = FALSE, $TimeToLive = 0, $CacheType = 'APCU')
{
    if (DEBUGMODE)
    {
        echo date("Y-m-d H:i:s").' -> PersistCache '.PHP_EOL;
    }

    $Data = array();
    switch ($CacheType)
    {
        case 'APCU':
            //Read from Cache
            $Result = APCUToMYSMA($Key, $Data);
            if ($Result === FALSE)
            {
                $ErrorMessage = 'Unable to read from cache. Key: '.$Key;
                ErrorLog($ErrorMessage, E_USER_ERROR);

                return FALSE;
            }
            //print_r($Data);

            //Is data an array?
            if (is_array($Data === FALSE))
            {
                return FALSE;
            }

            //Is it a proper Metadata-aware MYSAM structure?
            if (IsMYSMADataStructure($Data) === FALSE)
            {
                //echo '***DOES NOT HAVE PROPER STRUCTURE***';
                $ErrorMessage = 'Data to cache does not have proper structure. Key: '.$Key;
                ErrorLog($ErrorMessage, E_USER_ERROR);

                return FALSE;
            }

            //Now to persist the data
            //Store in DB
            $StoreResult = MYSMAToTable($Data, $DestinationTable, $DBConnection, $FullRewrite);
            if ($StoreResult === FALSE)
            {
                $ErrorMessage = 'Unable to perform cache reads and writes. Is it functional?';
                ErrorLog($ErrorMessage, E_USER_ERROR);
            }
            //Now flag it as persitable if it is not
            $Data['LastDBWrite'] = time();
            $CacheResult = apcu_store($Key, $Data, $TimeToLive);
            if ($CacheResult === FALSE)
            {
                $Message = 'Error caching data over APCU';
                ErrorLog($Message, E_USER_ERROR);

                return FALSE;
            }

            break;
        case 'REDIS':
            $ErrorMessage = 'Unimplemented cache system. Open an issue in https://github.com/ProceduralMan/MinionLib if you need it';
            ErrorLog($ErrorMessage, E_USER_ERROR);

            return FALSE;
        default:
            $ErrorMessage = 'Unknowns cache system: '.$CacheType.' Open an issue in https://github.com/ProceduralMan/MinionLib if you need it';
            ErrorLog($ErrorMessage, E_USER_ERROR);

            return FALSE;
    }
    //return the data
    return $Data;
}


/**
 * ReadCache
 * Tries Cache first and if not reads from table and stores in cache
 * If the lapse between now and the last time it was persisted exceeds MIL_APCUPERSISTLAPSE -defined on MinionSetup.php- it writes it to DB
 * @param   string  $Key                    The cache key under which the data is stored
 * @param   array   $FailOverConnection     Connection index to use when reading from database
 * @param   string  $FailOverTable          Table to query if cache reading fails
 * @param   boolean $Persistable            The flag that marks if it is a persistable array or a volatile one
 * @param   int     $TimeToLive             The time for the cache to live
 * @param   string  $CacheType              The cache type, either APCU or REDIS by now
 * @param   mixed   $FilterCondition        The condition for constraining the selection. If ommited, all the table is selected -thus replaced when cached-
 * disabled param   string  $ArrayType              Wheter to use a numeric -NUMERIC- or associative -ASSOC- array for storing the data
 *                                              and $ArrayType = NUMERIC => For the time
 * @return  mixed   The data read, NULL on empty datasets or FALSE on failure 
 * @since   0.0.7
 * @see
 * @todo esta devolviendo conexiones... revisar
 */
function ReadCache($Key, $FailOverConnection, $FailOverTable, $Persistable = FALSE, $TimeToLive = 0, $CacheType = 'APCU', $FilterCondition = "")
{
    if (DEBUGMODE)
    {
        echo date("Y-m-d H:i:s").' -> ReadCache '.PHP_EOL;
    }

    $Data = array();
    $PersistInfo['Persistable'] = $Persistable;
    $PersistInfo['TableName'] = $FailOverTable;
    $PersistInfo['ConnectionIndex'] = $FailOverConnection;
    if (empty($FilterCondition))
    {
        $PersistInfo['FullRewrite'] = TRUE;
    }
    else
    {
        $PersistInfo['FullRewrite'] = FALSE;
    }

    switch ($CacheType)
    {
        case 'APCU':
            //Try Cache first
            $Result = APCUToMYSMA($Key, $Data);
            if ($Result === FALSE)
            {
                if (DEBUGMODE)
                {
                    echo date("Y-m-d H:i:s").' -> ReadCache -> Key not found / Data non cached'.PHP_EOL;
                }
                //Filter semicolons
                if (!empty($FilterCondition))
                {
                    $FilterCondition = str_replace(";","",$FilterCondition);
                }
                //Non-successful. Read from table
                $DBResult = TableToMYSMA($Data, $FailOverTable, $FailOverConnection, $FilterCondition);
                if ($DBResult === FALSE)
                {
                    $ErrorMessage = 'Unable to read from cache and unable to read from database.';
                    ErrorLog($ErrorMessage, E_USER_ERROR);

                    return FALSE;
                }
                //We cache even empty datasets, as a way to 'initialice' the cache structure
                $StoreResult = MYSMAToAPCU($Data, $Key, $TimeToLive, $PersistInfo);
                if ($StoreResult === FALSE)
                {
                    $ErrorMessage = 'Unable to perform cache reads and writes. Is it functional?';
                    ErrorLog($ErrorMessage, E_USER_ERROR);

                    return FALSE;
                }
            } //End first-time caching
            //We always end returning the structure... it is down below the switch
            break;
        case 'REDIS':
            $ErrorMessage = 'Unimplemented cache system. Open an issue in https://github.com/ProceduralMan/MinionLib if you need it';
            ErrorLog($ErrorMessage, E_USER_ERROR);

            return FALSE;
        default:
            $ErrorMessage = 'Unknowns cache system: '.$CacheType.' Open an issue in https://github.com/ProceduralMan/MinionLib if you need it';
            ErrorLog($ErrorMessage, E_USER_ERROR);

            return FALSE;
    }
    //return the data
    return $Data;
}
