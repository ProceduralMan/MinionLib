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
 * AssocToMYSMA Converts a regular assoc array to our MySQL Assoc Data Structure plus Metadata MYSMA structure
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
function AssocToMYSMA($Data, $TableName, $ConnectionIndex, $MeaningfulKey = FALSE)
{
    if (DEBUGMODE)
    {
        echo date("Y-m-d H:i:s").' -> AssocToMYSMA '.PHP_EOL;
    }

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

    //If it is already kosher we return success
    if (IsMYSMADataStructure($Data) === TRUE)
    {
        return $Data;
    }

    //We will need meta-data for the following steps... can we get the metadata?
    if (is_null($TableName)||is_null($ConnectionIndex))
    {
        return FALSE;
    }

    $Metadata = GetMySQLTableMetadata($TableName, $ConnectionIndex);

    //If it is a MySQL Assoc Data Structure we do the conversion
    if (IsMySQLAssocDataStructure($Data) === TRUE)
    {
        $Data['Metadata'] = $Metadata;

        return $Data;
    }

    //Then either is a single assoc record or a multi-level numeric plus assoc like the one shown of the function header doc
    if (IsMultiArray($Data) === 1)
    {
        return SingleAssocToMYSMA($Data, $Metadata);
    }
    else
    {
        return MultiRecordNumericAssocToMYSMA($Data, $Metadata, $MeaningfulKey);
    }
}

/**
 * MultiRecordNumericAssocToMYSMA converts a multi-record numeric+assoc array to a MYSMA Structure
 * @param   array $Data       The multi-record structure
 * @param   array $Metadata   The associated table metadata
 * @param   mixed   $MeaningfulKey      The key to use when the array has meaningful keys on the first dimension
 * @return  array The MYSMA Data Structure
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
 * @since   0.0.8
 * @see     
 * @todo
 */
function MultiRecordNumericAssocToMYSMA($Data, $Metadata, $MeaningfulKey)
{
    if (DEBUGMODE)
    {
        echo date("Y-m-d H:i:s").' -> MultiRecordNumericAssocToMYSMA '.PHP_EOL;
    }

    $Rows = count($Data);
    $Index = 0;
    $RowRecord = array();
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
    //We dump the value to a MySQL ASSOC Structure
    $MYSMA['Columns'] = $ColumnsCount;
    $MYSMA['Rows'] = $Rows;
    $MYSMA['Data'] = $RowRecord;
    $MYSMA['Metadata'] = $Metadata;

    return $MYSMA;
}


/**
 * SingleAssocToMYSMA converts a single ASSOC record to a MYSMA Structure
 * @param   array $Data       The ASSOC record
 * @param   array $Metadata   The associated table metadata
 * @return  array The MYSMA Data Structure
 * @since   0.0.8
 * @see     
 * @todo
 */
function SingleAssocToMYSMA($Data, $Metadata)
{
    if (DEBUGMODE)
    {
        echo date("Y-m-d H:i:s").' -> SingleAssocToMYSMA '.PHP_EOL;
    }

    $Rows = 1;
    $ColumnsCount = 0;
    $RowRecord = array();
    foreach ($Data as $Key => $Value)
    {
        $RowRecord[0][$Key] = $Value;
        $ColumnsCount++;
    }
    //We dump the value to a MySQL ASSOC Structure
    $MYSMA['Columns'] = $ColumnsCount;
    $MYSMA['Rows'] = $Rows;
    $MYSMA['Data'] = $RowRecord;
    $MYSMA['Metadata'] = $Metadata;

    return $MYSMA;
}
