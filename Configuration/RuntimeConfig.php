<?php

/*
 * RuntimeConfig
 * Useful functions checking runtime config
 * @author ProceduralMan <proceduralman@gmail.com>
 * @copyright 2022
 * @version 1.0 initial version
 * @package Minion
 * @todo
 * @see
 */

/**
 * CheckPHPVersion
 * Checks if the runtime version is the same or newer than the required one.
 * @param   string  $RequiredVer The version required
 * @return  boolean TRUE if it is, FALSE otherwise
 * @since 0.0.9
 * @see
 * @todo
 */
function CheckPHPVersion($RequiredVer)
{
    if (DEBUGMODE)
    {
        echo date("Y-m-d H:i:s").' -> CheckPHPVersion '.PHP_EOL;
    }

    //By default, version_compare() returns -1 if the first version is lower than the second, 0 if they are equal, and 1 if the second is lower.
    if (version_compare($RequiredVer, PHP_VERSION)<1)
    {
        //Actual version is at least equal to required version
        return TRUE;
    }
    else
    {
        //Actual version is lower than required version
        return FALSE;
    }
}

/**
 * CheckcURLVersion
 * Checks if the runtime version is the same or newer than the required one.
 * @param   string  $RequiredVer the required version
 * @return  boolean TRUE if it is, FALSE otherwise
 * @since 0.0.9
 * @see
 * @todo
 */
function CheckcURLVersion($RequiredVer)
{
    if (DEBUGMODE)
    {
        echo date("Y-m-d H:i:s").' -> CheckcURLVersion '.PHP_EOL;
    }

    $VersionInfo = curl_version();

    //By default, version_compare() returns -1 if the first version is lower than the second, 0 if they are equal, and 1 if the second is lower.
    if (version_compare($RequiredVer, $VersionInfo['version'])<1)
    {
        //Actual version is at least equal to required version
        return TRUE;
    }
    else
    {
        //Actual version is lower than required version
        return FALSE;
    }
}

/**
 * FNAcURLVersion
 * (F)irst (N)on (A)vailable cURL version. Checks if the runtime version is older than the FNA one.
 * @param   string  $FirstNonAvailableVer the required version
 * @return  boolean FALSE if it is equal or older, TRUE otherwise
 * @since 0.0.9
 * @see
 * @todo
 */
function FNAcURLVersion($FirstNonAvailableVer)
{
    if (DEBUGMODE)
    {
        echo date("Y-m-d H:i:s").' -> CheckcURLVersion '.PHP_EOL;
    }

    $VersionInfo = curl_version();

    //By default, version_compare() returns -1 if the first version is lower than the second, 0 if they are equal, and 1 if the second is lower.
    if (version_compare($VersionInfo['version'], $FirstNonAvailableVer)<0)
    {
        //Actual version is lower than required version
        return TRUE;
    }
    else
    {
        //Actual version is at least equal to required version
        return FALSE;
    }
}

/**
 * CheckOpenSSLVersion
 * Checks if the runtime version is the same or newer than the required one.
 * @param   string  $RequiredVer the required version
 * @return  boolean TRUE if it is, FALSE otherwise
 * @since 0.0.9
 * @see
 * @todo
 */
function CheckOpenSSLVersion($RequiredVer)
{
    if (DEBUGMODE)
    {
        echo date("Y-m-d H:i:s").' -> CheckcURLVersion '.PHP_EOL;
    }

    $VersionInfo = curl_version();

    //By default, version_compare() returns -1 if the first version is lower than the second, 0 if they are equal, and 1 if the second is lower.
    if (version_compare($RequiredVer, $VersionInfo['ssl_version'])<1)
    {
        //Actual version is at least equal to required version
        return TRUE;
    }
    else
    {
        //Actual version is lower than required version
        return FALSE;
    }
}
