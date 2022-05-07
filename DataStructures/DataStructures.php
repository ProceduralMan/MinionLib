<?php

/*
 * DataStructures
 * all about data structures and moving from one to the other
 *
 * @author ProceduralMan <proceduralman@gmail.com>
 * @copyright 2022
 * @version 1.0 initial version
 * @package Minion
 * @todo
 * @see
 *
 */

/**
 * AssocToMySQLAssoc Converts a regular assoc array to a MySQL Assoc Data Structure
 * Sample ASSOC MySQl Structure
 * array(3) {
 *   ["Columns"]=>
 *   int(4)
 *   ["Rows"]=>
 *   int(3)
 *   ["Data"]=>
 *   array(3) {
 *     [0]=>
 *     array(4) {
 *       ["actor_id"]=>
 *       string(1) "1"
 *       ["first_name"]=>
 *       string(8) "PENELOPE"
 *       ["last_name"]=>
 *       string(7) "GUINESS"
 *       ["last_update"]=>
 *       string(19) "2006-02-15 04:34:33"
 *     }
 *     [1]=>
 *     array(4) {
 *       ["actor_id"]=>
 *       string(1) "2"
 *       ["first_name"]=>
 *       string(4) "NICK"
 *       ["last_name"]=>
 *       string(8) "WAHLBERG"
 *       ["last_update"]=>
 *       string(19) "2006-02-15 04:34:33"
 *     }
 *   }
 * }
 * 
 * @param   array   $Data               The array to process
 * @param   string  $TableName          The table to insert into
 * @param   array   $ConnectionIndex    The connection to use
 * @param   mixed   $MeaningfulKey      The key to use when the array has meaningful keys on the first dimension
 * @return  mixed   The MySQLAssoc Data Structure or FALSE in case of failure
 * @since   0.0.7
 * @see     
 * @todo
 */
function AssocToMySQLAssoc($Data, $TableName, $ConnectionIndex, $MeaningfulKey = FALSE)
{
    if (DEBUGMODE)
    {
        echo date("Y-m-d H:i:s").' -> AssocToMySQLAssoc '.PHP_EOL;
    }

    $MySQLAssoc = array();
    $RowRecord = array();

    //If no data we return error
    if (empty($Data))
    {
        return FALSE;
    }

    //Is it array data?
    if (is_array($Data) === FALSE)
    {
        return FALSE;
    }

    //Can we get the metadata?
    if (is_null($TableName)||is_null($ConnectionIndex))
    {
        return FALSE;
    }

    //If it is already kosher we return success
    if (IsMySQLAssocDataStructure($Data) === FALSE)
    {
        return $Data;
    }

    //If is a single assoc record we do the conversion
    if (IsMultiArray($Data) === 1)
    {
        if (IsAssocArray($Data) === FALSE)
        {
            return FALSE;
        }
        else
        {
            $Rows = 1;
            $ColumnsCount = 0;
            foreach ($Data as $Key => $Value)
            {
                $RowRecord[0][$Key] = $Value;
                $ColumnsCount++;
            }
        }
    }
    else
    {
        //print_r($Data);
        //Must be a multiarray as we check prior to see that it is a proper array
        $Metadata = GetMySQLTableMetadata($TableName, $ConnectionIndex);
        //Might be a "standard" MySQL ASSOC Data Structure
        if (isset($Data['Columns'], $Data['Rows'], $Data['Data']))
        {
            $Data['Metadata'] = $Metadata;

            return $Data;
        }

        //Plain NUMERIC+ASSOC array assumed -like the 'Data' part of a MySQL ASSOC Structure
        $Rows = count($Data);
        $Index = 0;
        foreach ($Data as $RecordKey => $RecordValue)
        {
            $ColumnsCount = 0;
            if ($MeaningfulKey !== FALSE)
            {
                $RowRecord[$Index][$MeaningfulKey] = $RecordKey;
            }
            foreach ($RecordValue as $Key => $Value)
            {
                $RowRecord[$Index][$Key] = $Value;
                $ColumnsCount++;
            }
            $Index++;
        }
    }

    //We dump the value to a MySQL ASSOC Structure
    $MySQLAssoc['Columns'] = $ColumnsCount;
    $MySQLAssoc['Rows'] = $Rows;
    $MySQLAssoc['Data'] = $RowRecord;
    $MySQLAssoc['Metadata'] = $Metadata;

    return $MySQLAssoc;
}
