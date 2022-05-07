<?php

/*
 * DateAndTime
 * Functions related with dates, times, theiur  formats and duration calculations
 * @author ProceduralMan <proceduralman@gmail.com>
 * @copyright 2022
 * @version 1.0 initial version
 * @package Minion
 * @todo
 * @see
 */

/**
 * HRLapse
 * Calculates a lapse and returns it in human-readable format
 * @param   int     $InitialTime Timestamp to calculate duration from
 * @return  string  Duration expresed in human-readable string
 * @since 0.0.7
 * @see
 * @todo
 */
function HRLapse($InitialTime)
{
    $ActualTime = time();
    $Years = (int)((($ActualTime-$InitialTime)/(7*86400))/52.177457);
    $RemainingSeconds = (int)(($ActualTime-$InitialTime)-($Years*52.177457*7*86400));
    $Weeks = (int)(($RemainingSeconds)/(7*86400));
    $Days = (int)(($RemainingSeconds)/86400)-$Weeks*7;
    $Hours = (int)(($RemainingSeconds)/3600)-$Days*24-$Weeks*7*24;
    $Minutes = (int)(($RemainingSeconds)/60)-$Hours*60-$Days*24*60-$Weeks*7*24*60;
    $DurationString = '';
    if ($Years == 1)
    {
        $DurationString .= "$Years year, ";
    }
    if ($Years>1)
    {
        $DurationString .= "$Years years, ";
    }
    if ($Weeks == 1)
    {
        $DurationString .= "$Weeks week, ";
    }
    if ($Weeks>1)
    {
        $DurationString .= "$Weeks weeks, ";
    }
    if ($Days == 1)
    {
        $DurationString .= "$Days day,";
    }
    if ($Days>1)
    {
        $DurationString .= "$Days days,";
    }
    if ($Hours == 1)
    {
        $DurationString .= " $Hours hour and";
    }
    if ($Hours>1)
    {
        $DurationString .= " $Hours hours and";
    }
    if ($Minutes == 1)
    {
        $DurationString .= " 1 minute";
    }
    else
    {
        $DurationString .= " $Minutes minutes";
    }

    return $DurationString;
}

/**
 * MRLapse
 * Calculates a lapse and returns it in machine-readable format
 * @param   int     $InitialTime Timestamp to calculate duration from
 * @param   string  $TimeUnit    The time unit to use. (S)econds, (M)inutes, (H)ours, (D)ays, (W)eeks or (Y)ears
 * @return  float   Duration expresed in a machine-readable number
 * @since 0.0.7
 * @see     
 * @todo
 */
function MRLapse($InitialTime, $TimeUnit = 'S')
{
    $ActualTime = time();
    $ElapsedSeconds = $ActualTime-$InitialTime;

    switch (strtoupper($TimeUnit))
    {
        case 'S':
            return $ElapsedSeconds;
        case 'M':
            return $ElapsedSeconds/60;
        case 'H':
            return $ElapsedSeconds/3600;
        case 'D':
            return $ElapsedSeconds/86400;
        case 'W':
            return $ElapsedSeconds/(86400*7);
        case 'Y':
            return $ElapsedSeconds/(86400*7*52.177457);
    }
}
