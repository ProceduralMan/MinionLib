<?php

/*
 * Data Plugs for data about the Internet
 *
 * @author ProceduralMan <proceduralman@gmail.com>
 * @copyright 2021
 * @version 1.0 initial version
 * @package Minion
 * @todo 
 * @see  
 * 
 */

/**
 * GetIanaTLDs
 * Extracts a current list of TLDs from IANA's website
 * Saves it as a CSV
 *      1 Check if CSV present 
 *      2 Check Retention
 *          2.1 If retention has passed read version from csv 3 and compare with online file version
 *              2.1.1 If online file is newer, write online file
 *      3 If CSV not present, write online file
 * @param   int     $Retention number of days to keep the last download
 * @return  mixed   The path to the data on success, FALSE on error
 * @since 1.0
 * @todo
 */
function GetIanaTLDs($Retention = 5)
{
    //Check if CSV is already present
    $FilePath = DATA_PLUG_STORAGE.'/'.'TLDsALphaByDomain.csv';
    if (file_exists($FilePath))
    {
        //Get file modification time
        $ModTS = filemtime($FilePath);
        $Now = time();
        //Se if we are passed retention
        if (($Now-$ModTS)>$Retention*86400)
        {
            //Get the file
            //Read version of currentfile
            $OldLines = file($FilePath);
            $OldVersion = intval($OldLines[0]);
            //Get new file
            $Lines = file('https://data.iana.org/TLD/tlds-alpha-by-domain.txt', FILE_SKIP_EMPTY_LINES);
            if ($Lines === FALSE)
            {
                $ErrorMessage = 'Error getting data from https://data.iana.org/TLD/tlds-alpha-by-domain.txt';
                //echo $ErrorMessage.PHP_EOL;
                ErrorLog($ErrorMessage, E_USER_ERROR);

                return FALSE;
            }
            else
            {
                $FileVersionChunks = explode(",",$Lines[0]);
                $Lines[0] = substr($FileVersionChunks[0], -10).PHP_EOL;
                $NewVersion = intval($Lines[0]);
                //If the new file is newer
                if ($NewVersion>$OldVersion)
                {
                    $Result = file_put_contents($FilePath, $Lines);
                    if ($Result === FALSE)
                    {
                        $ErrorMessage = 'Error writing data to '.$FilePath;
                        //echo $ErrorMessage.PHP_EOL;
                        ErrorLog($ErrorMessage, E_USER_ERROR);

                        return FALSE;
                    }
                }
            } //END IANA endpoint responds OK
        } //END retention period is over
    } //END there is a prior file
    else
    {
        //Get new file
        $Lines = file('https://data.iana.org/TLD/tlds-alpha-by-domain.txt', FILE_SKIP_EMPTY_LINES);
        if ($Lines === FALSE)
        {
            $ErrorMessage = 'Error getting data from https://data.iana.org/TLD/tlds-alpha-by-domain.txt';
            //echo $ErrorMessage.PHP_EOL;
            ErrorLog($ErrorMessage, E_USER_ERROR);

            return FALSE;
        }
        else
        {
            $FileVersionChunks = explode(",",$Lines[0]);
            $Lines[0] = substr($FileVersionChunks[0], -10).PHP_EOL;
            $NewVersion = intval($Lines[0]);
            $Result = file_put_contents($FilePath, $Lines);
            if ($Result === FALSE)
            {
                $ErrorMessage = 'Error writing data to '.$FilePath;
                //echo $ErrorMessage.PHP_EOL;
                ErrorLog($ErrorMessage, E_USER_ERROR);

                return FALSE;
            }
        } //END IANA endpoint responds OK
    } //END there is no prior file

    return $FilePath;
}
