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
 * Valid when whole tables are cached (auxiliary tables, counter tables and the like
 * @param   string  $Key                The cache key under which the data is stored
 * @param   array   $DBConnection       Connection index to use when reading from database
 * @param   string  $DestinationTable   Table to query if cache reading fails
 * @param   boolean $FullRewrite        TRUE if the table is to be deleted and rewritten with the array info, false otherwise
 * @param   array   $ColumnNames        The array of column names to use on NUMERIC arrays. First column *MUST* be the PK!
 * @param   string  $CacheType          The cache type, either APCU or REDIS by now
 * @param   string  $ArrayType          Wheter to use a numeric -NUMERIC- or associative -ASSOC- array for storing the data
 * @return  boolean TRUE if persisted successfully, FALSE otherwise
 * @since   0.0.7
 * @see
 * @todo
 */
function PersistCache($Key, $DBConnection, $DestinationTable,  $FullRewrite = FALSE, $ColumnNames = NULL, $CacheType = 'APCU',$ArrayType = 'ASSOC')
{
    if (DEBUGMODE)
    {
        echo date("Y-m-d H:i:s").' -> PersistCache '.PHP_EOL;
    }

    //Numeric arrays MUST include $ColumnNames
    if ($ArrayType === 'NUMERIC')
    {
        if (IsNumericArray($ColumnNames) === FALSE)
        {
            return FALSE;
        }
    }
    elseif ($ArrayType !== 'ASSOC')
    {
        //Only ASSOC and NUMERIC array types allowed
        return FALSE;
    }

    $Data = array();
    $MySQLData = array();
    switch ($CacheType)
    {
        case 'APCU':
            //Read from Cache
            $Result = APCU2Array($Key, $Data);
            if ($Result === FALSE)
            {
                $ErrorMessage = 'Unable to read from cache. Key: '.$Key;
                ErrorLog($ErrorMessage, E_USER_ERROR);

                return FALSE;
            }

            //Is data an array?
            if (is_array($Data === FALSE))
            {
                return FALSE;
            }

            /*Convert to MySQL Data Structure
            if ($ArrayType === 'ASSOC')
            {
                //Convert Array to MySQL Data Structure 
                $MySQLData = AssocToMySQLAssoc($Data, FALSE);
                if ($MySQLData === FALSE)
                {
                    return FALSE;
                }
            }*/

            //Is it a proper Metadata-aware structure?
            if (IsMySQLAssocDataStructure($Data) === FALSE)
            {
                return FALSE;
            }

            //Now to persist the data
            if ($ArrayType === 'ASSOC')
            {
                //Store in DB
                $StoreResult = AssocToTable($Data, $DestinationTable, $DBConnection, $FullRewrite);
                if ($StoreResult === FALSE)
                {
                    $ErrorMessage = 'Unable to perform cache reads and writes. Is it functional?';
                    ErrorLog($ErrorMessage, E_USER_ERROR);
                }
            }
            else
            {
                //Store in DB
                NumericToTable($Data, $DestinationTable, $DBConnection, $ColumnNames, $FullRewrite);
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
 * tries Cache first and if not reads from table and stores in cache
 * Valid when whole tables are cached -auxiliary tables, counter tables and the like-
 * @param   string  $Key                    The cache key under which the data is stored
 * @param   array   $FailOverConnection     Connection index to use when reading from database
 * @param   string  $FailOverTable          Table to query if cache reading fails
 * @param   boolean $Persistable            The flag that marks if it is a persistable array or a volatile one
 * @param   int     $TimeToLive             The time for the cache to live
 * @param   string  $CacheType              The cache type, either APCU or REDIS by now
 * @param   mixed   $FilterCondition        The condition for constraining the selection. If ommited, all the table is selected -thus replaced when cached-
 * @param   string  $ArrayType              Wheter to use a numeric -NUMERIC- or associative -ASSOC- array for storing the data
 * @param   array   $ParametricKeys         A numeric array of column names used to persist numeric arrays into DB. Only needed of we use $Persistable = TRUE
 *                                              and $ArrayType = NUMERIC
 * @return  mixed   The data read, NULL on empty datasets or FALSE on failure 
 * @since   0.0.7
 * @see
 * @todo esta devolviendo conexiones... revisar
 */
function ReadCache($Key, $FailOverConnection, $FailOverTable, $Persistable = FALSE, $TimeToLive = 0, $CacheType = 'APCU', $FilterCondition = "", $ArrayType = 'ASSOC',
        $ParametricKeys = NULL)
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
    if (is_null($ParametricKeys) === FALSE)
    {
        $PersistInfo['ParametricKeys'] = $ParametricKeys;
    }

    switch ($CacheType)
    {
        case 'APCU':
            //Try Cache first
            $Result = APCU2Array($Key, $Data);
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
                if ($ArrayType === 'NUMERIC')
                {
                    $DBResult = TableToArray($Data, $FailOverTable, $FailOverConnection, $FilterCondition);
                }
                else
                {
                    $DBResult = TableToAssoc($Data, $FailOverTable, $FailOverConnection, $FilterCondition);
                }
                if ($DBResult === FALSE)
                {
                    $ErrorMessage = 'Unable to read from cache and unable to read from database.';
                    ErrorLog($ErrorMessage, E_USER_ERROR);

                    return FALSE;
                }
                //If Dataset it's empty, no need to cache it
                if ($Data['Rows'] === 0)
                {
                    //No need to raise NOTICE
                    //$ErrorMessage = 'Reading an empty dataset from database.';
                    //ErrorLog($ErrorMessage, E_USER_NOTICE);

                    return NULL;
                }
                $StoreResult = Array2APCU($Data, $Key, $TimeToLive, $PersistInfo);
                if ($StoreResult === FALSE)
                {
                    $ErrorMessage = 'Unable to perform cache reads and writes. Is it functional?';
                    ErrorLog($ErrorMessage, E_USER_ERROR);
                }
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
