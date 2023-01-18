<?php

/*
 * SolarEdge renewables monitoring
 * Use of the monitoring server API is subject to a query limit of 300 requests for a specific account token and a parallel query limit of 300 requests for each 
 * specific site ID from the same source IP.
 * APIs that do not have a specific ID (e.g. Site List, Account List) will be counted as part of the account query limit.
 * Any additional site or account level request will result in HTTP 429 error – too many requests
 * The monitoring server API allows up to 3 concurrent API calls from the same source IP. Any additional concurrent calls will return HTTP 429 error – too many 
 * requests. To execute APIs concurrently without exceeding the above limitation, it is the client responsibility to implement a throttling mechanism on the 
 * client side.
 * Some of the APIs offer a bulk form, that is, they take multiple site IDs as an input. The list is separated by a comma and should contain up to 100 site IDs.
 * A bulk call for multiple sites consumes 1 call from the daily quota allowed for each of the sites included in the call.
 * @author ProceduralMan <proceduralman@gmail.com>
 * @copyright 2022
 * @version 1.0 initial version
 * @package Minion
 * @todo 
 * @see  
 */

//EndPoints for Solar Edge Site Data API

/**
 * SESiteList
 * Returns a list of sites related to the given token, which is the account api_key. 
 * This endpoint accepts parameters for convenient search, sort and pagination.
 * Method:      GET
 * Request:     Parameter       Type    Mandatory   Description
 *              size            Integer No          see $Size @param
 *              startIndex      Integer No          see $StartIndex @param
 *              searchText      String  No          see $SearchText @param
 *              sortProperty    String  No          see $SortProperty @param
 *              sortOrder       String  No          see $SortOrder @param
 *              Status          String  No          see $Status @param
 * Response:    The returned data is the site list, including the sites that match the given search criteria. For each entry, the following information is 
 *              displayed:
 *                  - id:               The site ID
 *                  - name:             The site name
 *                  - accountId:        The account this site belongs to
 *                  - status:           The site status (ACTIVE –The site is active-, PENDING COMMUNICATION –The site was created successfully however there is 
 *                                          no communication yet from its inverters/SMI-)
 *                  - peakPower:        Site peak power
 *                  - currency:         Configured currency
 *                  - installationDate: Site installation date (format: yyyy-MM-DD hh:mm:ss)
 *                  - ptoDate:          Permission to operate date
 *                  - notes:            Site notes
 *                  - type:             Site type ("Optimizers & inverters", "Safety and monitoring interface" or "Monitoring combiner boxes")
 *                  - location:         Includes country, state, city, address, secondary address, zip and time zone
 *                  - alertQuantity:    Number of open alerts in this site (needs account level API KEY. Site level API KEY does not include this info)
 *                  - alertSeverity:    The highest alert severity in this site (needs account level API KEY. Site level API KEY does not include this info)
 *                  - publicSettings:   Includes if this site is public (isPublic) and its public name (name)
 * Example:     JSON output:
 *      {
 *        "Sites": {
 *          "count": 1567,
 *          "list": [
 *            {
 *              "id": 1,
 *              "name": "Test",
 *              "accountId": 0,
 *              "status": "Active",
 *              "peakPower": 10,
 *              "currency": "EUR",
 *              "installationDate": "2012-06-08 00:00:00",
 *              "ptoDate": "2017-05-11",
 *              "notes": "test notes",
 *              "type": "Optimizers & Inverters",
 *              "location": {
 *                "country": "the country",
 *                "state": "the state",
 *                "city": "the city",
 *                "address": "the address",
 *                "address2": "the address2",
 *                "zip": "00000",
 *                "timeZone": "GMT"
 *              },
 *              "alertQuantity": 0,
 *              "alertSeverity": "NONE",
 *              "uris": {
 *                "PUBLIC_URL": "the public URL name",
 *                "IMAGE_URI": "the site image link"
 *              },
 *              "publicSettings": {
 *                "name": "the public name",
 *                "isPublic": true
 *              }
 *            },
 *            {
 *              "id": 2,
 *              "name": "Test",
 *              "accountId": 0,
 *              "status": "Active",
 *              "peakPower": 10,
 *              "currency": "EUR",
 *              "installationDate": "2012-06-08 00:00:00",
 *              "ptoDate": "2017-05-11",
 *              "notes": "test notes",
 *              "type": "Optimizers & Inverters",
 *              "location": {
 *                "country": "the country",
 *                "state": "the state",
 *                "city": "the city",
 *                "address": "the address",
 *                "address2": "the address2",
 *                "zip": "00000",
 *                "timeZone": "GMT"
 *              },
 *              "alertQuantity": 0,
 *              "alertSeverity": "NONE",
 *              "uris": {
 *                "PUBLIC_URL": "the public URL name",
 *                "IMAGE_URI": "the site image link"
 *              },
 *              "publicSettings": {
 *                "name": "the public name",
 *                "isPublic": true
 *              }
 *            }
 *          ]
 *        }
 *      }
 * 
 * @param   string  $APIKey         The API KEY to use
 * @param   int     $Size           The maximum number of sites returned by this call. The maximum number of sites that can be returned by this call is 100. 
 *                                  Default value is 100.
 * @param   int     $StartIndex     The first site index to be returned in the results. If you have more than 100 sites, just request another 100 sites with 
 *                                  startIndex=100.This will fetch sites 100-199. Default value is 0;
 * @param   string  $SearchText     Search text for this site. Searchable site properties: Name, Notes, Address, City, Zip code, Full address, Country.
 * @param   string  $SortProperty   A case-sensitive sorting option for this site list, based on one of its properties: Name (sort by site name), Country (sort 
 *                                  by site country), State (sort by site state), City (sort by site city), Address (sort by site address), Zip (sort by site 
 *                                  zip code), Status (sort by site status), PeakPower (sort by peak power), InstallationDate (sort by installation date), 
 *                                  Amount (sort by amount of alerts), MaxSeverity (sort by alert severity), CreationTime (sort by site creation time)
 * @param   string  $SortOrder      Sort order for the sort property. Allowed values are ASC (ascending) and DESC (descending). Default value is ASC
 * @param   string  $Status         Select the sites to be included in the list by their status: Active, Pending, Disabled, All. Default list will include 
 *                                  Active and Pending sites.
 * @return  mixed   The response from the API in JSON array format or FALSE if something fails
 *                      HTTPCode:   The HTTP Code given by the server to our request
 *                      Response:   The server response
 * @since 0.0.9
 * @todo
 * @see
 */
function SESiteList($APIKey, $Size = NULL, $StartIndex = NULL, $SearchText = NULL, $SortProperty = NULL,
                    $SortOrder = NULL, $Status = NULL)
{
    $URL = MIL_SEBASEURL.'sites/list';
    $Parameters = array();

    //API KEY validation
    if (empty($APIKey))
    {
        ErrorLog('An API KEY is missing', E_USER_ERROR);

        return FALSE;
    }
    else
    {
        $Parameters['api_key'] = $APIKey;
    }

    //Parameters validation
    if (!empty($Size))
    {
        if (IsValidInt($Size,1,100))
        {
            $Parameters['size'] = $Size;
        }
        else
        {
            ErrorLog('size must be an integer between 1 and 100. Skipping the option', E_USER_WARNING);
        }
    }

    if (!empty($StartIndex))
    {
        if (IsValidInt($StartIndex,1,NULL))
        {
            $Parameters['startIndex'] = $StartIndex;
        }
        else
        {
            ErrorLog('startIndex must be a positive integer. Skipping the option', E_USER_WARNING);
        }
    }

    if (!empty($SearchText))
    {
        if (is_string($SearchText))
        {
            $Parameters['searchText'] = $SearchText;
        }
        else
        {
            ErrorLog('searchText must be a string. Skipping the option', E_USER_WARNING);
        }
    }

    $SortValues = array('Name', 'Country', 'State', 'City', 'Address', 'Zip', 'Status', 'PeakPower', 'InstallationDate',
        'Amount', 'MaxSeverity', 'CreationTime');
    if (!empty($SortProperty))
    {
        if (in_array($SortProperty, $SortValues))
        {
            $Parameters['sortProperty'] = $SortProperty;
        }
        else
        {
            ErrorLog('sortProperty is case-sensitive and must be one of those:'.implode(',',$SortValues).'. Skipping.', E_USER_WARNING);
        }
    }

    $OrderValues = array('ASC', 'DESC');
    if (!empty($SortOrder))
    {
        if (in_array($SortOrder, $OrderValues))
        {
            $Parameters['sortOrder'] = $SortOrder;
        }
        else
        {
            ErrorLog('sortOrder is case-sensitive and must be one of those:'.implode(',',$OrderValues).'. Skipping.', E_USER_WARNING);
        }
    }

    $StatusValues = array('Active', 'Pending', 'Disabled', 'All');
    if (!empty($Status))
    {
        if (in_array($Status, $StatusValues))
        {
            $Parameters['Status'] = $Status;
        }
        else
        {
            ErrorLog('Status is case-sensitive and must be one of those:'.implode(',',$StatusValues).'. Skipping.', E_USER_WARNING);
        }
    }

    //Now we have all the correct options
    $Result = cURLSimpleGET($URL, $Parameters);

    return $Result;
}

/**
 * SESiteDetails
 * Returns the site details, such as name, location, status, etc
 * Method:      GET
 * Request:     Parameter       Type    Mandatory   Description
 *              siteId          Integer Yes         see $SiteId @param
 * Response:    The returned data includes the following site information:
 *                  - id:               The site ID
 *                  - name:             The site name
 *                  - accountId:        The account this site belongs to
 *                  - status:           The site status (ACTIVE –The site is active-, PENDING COMMUNICATION –The site was created successfully however there is 
 *                                          no communication yet from its inverters/SMI-)
 *                  - peakPower:        Site peak power
 *                  - currency:         Configured currency
 *                  - installationDate: Site installation date (format: yyyy-MM-DD hh:mm:ss)
 *                  - ptoDate:          Permission to operate date
 *                  - notes:            Site notes
 *                  - type:             Site type ("Optimizers & inverters", "Safety and monitoring interface" or "Monitoring combiner boxes")
 *                  - location:         Includes country, state, city, address, secondary address, zip and time zone
 *                  - alertQuantity:    Number of open alerts in this site (needs account level API KEY. Site level API KEY does not include this info)
 *                  - alertSeverity:    The highest alert severity in this site (needs account level API KEY. Site level API KEY does not include this info)
 *                  - publicSettings:   Includes if this site is public (isPublic) and its public name (name)
 * Example:     JSON output:
 *      {
 *        "details": {
 *          "id": 0,
 *          "name": "site name",
 *          "accountId": 0,
 *          "status": "Active",
 *          "peakPower": 9.8,
 *          "currency": "EUR",
 *          "installationDate": "2012-08-16 00:00:00",
 *          "ptoDate": "2017-05-11",
 *          "notes": "my notes",
 *          "type": "Optimizers & Inverters",
 *          "location": {
 *            "country": "my country",
 *            "state": "my state",
 *            "city": "my city",
 *            "address": "my address",
 *            "address2": "",
 *            "zip": "0000",
 *            "timeZone": "GMT"
 *          },
 *          "alertQuantity": 0,
 *          "alertSeverity": "NONE",
 *          "uris": {
 *            "IMAGE_URI": "site image uri"
 *          },
 *          "publicSettings": {
 *            "name": null,
 *            "isPublic": false
 *          }
 *        }
 *      }
 * 
 * @param   string  $APIKey         The API KEY to use
 * @param   int     $SiteId         The site identifier
 * @return  mixed   The response from the API in JSON array format or FALSE if something fails
 *                      HTTPCode:   The HTTP Code given by the server to our request
 *                      Response:   The server response
 * @since 0.0.9
 * @todo
 * @see
 */
function SESiteDetails($APIKey, $SiteId)
{
    //SiteId validation
    if (!empty($SiteId))
    {
        if (IsValidInt($SiteId,1,NULL))
        {
            $URL = MIL_SEBASEURL.'site/'.$SiteId.'/details';
        }
        else
        {
            ErrorLog('siteId must be a positive integer. Aborting', E_USER_ERROR);

            return FALSE;
        }
    }

    $Parameters = array();

    //API KEY validation
    if (empty($APIKey))
    {
        ErrorLog('An API KEY is missing', E_USER_ERROR);

        return FALSE;
    }
    else
    {
        $Parameters['api_key'] = $APIKey;
    }

    //Now we have all the correct options
    $Result = cURLSimpleGET($URL, $Parameters);

    return $Result;
}

/**
 * SESiteDataPeriod
 * Returns the energy production start and end dates of the site
 * Method:      GET
 * Request:     Parameter       Type    Mandatory   Description
 *              siteId          Integer Yes         see $SiteId @param
 * Response:    The returned data includes <start, end> dates of the requested site. In case this site is not transmitting, the response is “null”
 * Example:     JSON output:
 *      {
 *        "dataPeriod": {
 *          "startDate": "2013-05-05 12:00:00",
 *          "endDate": "2013-05-28 23:59:59"
 *        }
 *      }
 *              JSON output (non-transmitting sites):
 *      {
 *        "dataPeriod": {
 *          "startDate": null,
 *          "endDate": null
 *        }
 *      }
 * 
 * @param   string  $APIKey         The API KEY to use
 * @param   int     $SiteId         The site identifier
 * @return  mixed   The response from the API in JSON array format or FALSE if something fails
 *                      HTTPCode:   The HTTP Code given by the server to our request
 *                      Response:   The server response
 * @since 0.0.9
 * @todo
 * @see
 */
function SESiteDataPeriod($APIKey, $SiteId)
{
    //SiteId validation
    if (!empty($SiteId))
    {
        if (IsValidInt($SiteId,1,NULL))
        {
            $URL = MIL_SEBASEURL.'site/'.$SiteId.'/dataPeriod';
        }
        else
        {
            ErrorLog('siteId must be a positive integer. Aborting', E_USER_ERROR);

            return FALSE;
        }
    }
    else
    {
        ErrorLog('A siteId is missing', E_USER_ERROR);

        return FALSE;
    }

    $Parameters = array();

    //API KEY validation
    if (empty($APIKey))
    {
        ErrorLog('An API KEY is missing', E_USER_ERROR);

        return FALSE;
    }
    else
    {
        $Parameters['api_key'] = $APIKey;
    }

    //Now we have all the correct options
    $Result = cURLSimpleGET($URL, $Parameters);

    return $Result;
}

/**
 * SEBulkSiteDataPeriod
 * Returns the energy production start and end dates of the site
 * Method:      GET
 * Request:     Parameter       Type    Mandatory   Description
 *              siteId          Integer Yes         see $SiteId @param
 * Response:    The response includes a list of <start, end> data transmission dates for the requested sites. The value “null” will be displayed for sites that 
 *              have no data (not transmitting).
 * Example:     JSON output:
 *      {
 *        "dataPeriod": {
 *          "count": 2,
 *          "list": [
 *            {
 *              "id": 1,
 *              "startDate": "2013-05-05 12:00:00",
 *              "endDate": "2013-05-28 23:59:59"
 *            },
 *            {
 *              "id": 4,
 *              "startDate": null,
 *              "endDate": null
 *            }
 *          ]
 *        }
 *      }
 * 
 * @param   string  $APIKey         The API KEY to use
 * @param   array   $SiteIds        The site identifiers. Maximun 100 sites.
 * @return  mixed   The response from the API in JSON array format or FALSE if something fails
 *                      HTTPCode:   The HTTP Code given by the server to our request
 *                      Response:   The server response
 * @since 0.0.9
 * @todo
 * @see
 */
function SEBulkSiteDataPeriod($APIKey, $SiteIds)
{
    //SiteIds validation
    if (!empty($SiteIds))
    {
        if (is_array($SiteIds))
        {
            $TheSites = count($SiteIds);
            //echo '***Sites in site array:'.$TheSites.PHP_EOL;
            if ($TheSites>100)
            {
                ErrorLog('Only the first 100 of the '.$TheSites.' sites will be processed', E_USER_WARNING);
                $Limit = 100;
            }
            else
            {
                $Limit = $TheSites;
            }
            $SiteIdList = '';
            for ($i = 0; $i<$Limit; $i++)
            {
                //var_dump($SiteIds[$i]);
                if (!IsValidInt($SiteIds[$i],1,NULL))
                {
                    ErrorLog('SiteId must be an integer. Aborting', E_USER_ERROR);

                    return FALSE;
                }
                else
                {
                    $SiteIdList .= $SiteIds[$i].',';
                }
            }
            $FinalSiteList = substr($SiteIdList, 0, strlen($SiteIdList)-1);
            $URL = MIL_SEBASEURL.'sites/'.$FinalSiteList.'/dataPeriod';
        }
        else
        {
            ErrorLog('SiteIds must be an array of integers. Aborting', E_USER_ERROR);

            return FALSE;
        }
    }
    else
    {
        ErrorLog('A siteIds array is compulsory', E_USER_ERROR);

        return FALSE;
    }

    $Parameters = array();

    //API KEY validation
    if (empty($APIKey))
    {
        ErrorLog('An API KEY is missing', E_USER_ERROR);

        return FALSE;
    }
    else
    {
        $Parameters['api_key'] = $APIKey;
    }

    //Now we have all the correct options
    //$Result = cURLSimpleGET($URL, $Parameters);
    $Result = cURLFullGET($URL, $Parameters);

    return $Result;
}

/**
 * SESiteEnergy
 * Returns the site energy measurements. This API is limited to one year when using timeUnit=DAY (i.e., daily resolution) and to one month when using 
 * timeUnit=QUARTER_OF_AN_HOUR or timeUnit=HOUR. This means that the period between endTime and startTime should not exceed one year or one month respectively. 
 * If the period is longer, the system will generate error 403
 * Energy is the integral of power, or SUM(Pw*P)[t0->tn] being t0 the initial time, tn the end time, PW the power and P the probing interval
 * Method:      GET
 * Request:     Parameter       Type    Mandatory   Description
 *              siteId          Integer Yes         see $SiteId @param
 *              startDate       String  Yes         see $StartDate @param
 *              endDate         String  Yes         see $EndDate @param
 *              timeUnit        String  No          see $TimeUnit @param
 * Response:    The response includes the requested time unit, the units of measurement (e.g. Wh), and the pairs of date and energy for every date 
 *              ({"date":"2013-06-01 00:00:00","value":null}). The date is calculated based on the time zone of the site. “null” means there is no data for that 
 *              time.
 * Example:     JSON output:
 *      {
 *        "energy": {
 *          "timeUnit": "DAY",
 *          "unit": "Wh",
 *          "values": [
 *            {
 *              "date": "2013-06-01 00:00:00",
 *              "value": null
 *            },
 *            {
 *              "date": "2013-06-02 00:00:00",
 *              "value": null
 *            },
 *            {
 *              "date": "2013-06-03 00:00:00",
 *              "value": null
 *            },
 *            {
 *              "date": "2013-06-04 00:00:00",
 *              "value": 67313.24
 *            }
 *          ]
 *        }
 *      }
 * 
 * @param   string  $APIKey         The API KEY to use
 * @param   int     $SiteId         The site identifier
 * @param   string  $StartDate      The start date to return energy measurement, ISO format (YYYY-MM-DD)
 * @param   string  $EndDate        The end date to return energy measurement, ISO format (YYYY-MM-DD)
 * @param   string  $TimeUnit       Aggregation granularity, default: DAY. Case sensitive. Available options: QUARTER_OF_AN_HOUR, HOUR, DAY, WEEK, MONTH, YEAR
 * @return  mixed   The response from the API in JSON array format or FALSE if something fails
 *                      HTTPCode:   The HTTP Code given by the server to our request
 *                      Response:   The server response
 * @since 0.0.9
 * @todo
 * @see
 */
function SESiteEnergy($APIKey, $SiteId, $StartDate, $EndDate, $TimeUnit)
{
    $Parameters = array();

    //API KEY validation
    if (empty($APIKey))
    {
        ErrorLog('An API KEY is missing', E_USER_ERROR);

        return FALSE;
    }
    else
    {
        $Parameters['api_key'] = $APIKey;
    }

    //Parameters validation
    if (!empty($SiteId))
    {
        if (IsValidInt($SiteId,1,NULL))
        {
            $URL = MIL_SEBASEURL.'site/'.$SiteId.'/energy';
        }
        else
        {
            ErrorLog('siteId must be a positive integer. Aborting', E_USER_ERROR);

            return FALSE;
        }
    }
    else
    {
        ErrorLog('siteId is compulsory. Aborting', E_USER_ERROR);

        return FALSE;
    }


    if (!empty($StartDate))
    {
        //The first practical photovoltaic cell was developed in April, 1954 at Bell Laboratories by Daryl Chaplin, Gerald Pearson and Calvin Souther Fuller.
        if (IsValidISODate($StartDate,'1954-04-01',date('Y-m-d')))
        {
            $Parameters['startDate'] = $StartDate;
        }
        else
        {
            ErrorLog('startDate must be a valid date between April, 1954 and today. Aborting', E_USER_ERROR);
        }
    }
    else
    {
        ErrorLog('startDate is compulsory. Aborting', E_USER_ERROR);

        return FALSE;
    }

    if (!empty($EndDate))
    {
        //The first practical photovoltaic cell was developed in April, 1954 at Bell Laboratories by Daryl Chaplin, Gerald Pearson and Calvin Souther Fuller.
        if (IsValidISODate($EndDate,'1954-04-01',date('Y-m-d')))
        {
            $Parameters['endDate'] = $EndDate;
        }
        else
        {
            ErrorLog('endDate must be a valid ISO date string (YYY-MM-DD) between April, 1954 and today. Aborting', E_USER_ERROR);
        }
    }
    else
    {
        ErrorLog('endDate is compulsory. Aborting', E_USER_ERROR);

        return FALSE;
    }

    $TimeUnitValues = array('QUARTER_OF_AN_HOUR', 'HOUR', 'DAY', 'WEEK', 'MONTH', 'YEAR');
    if (!empty($TimeUnit))
    {
        if (in_array($TimeUnit, $TimeUnitValues))
        {
            $Parameters['sortProperty'] = $TimeUnit;
        }
        else
        {
            ErrorLog('timeUnit is case-sensitive and must be one of those:'.implode(',',$TimeUnitValues).'. Skipping.', E_USER_WARNING);
        }
    }

    //Check FROM is earlier than TO
    $LogicDates = ISODateNoLaterThan($StartDate, $EndDate);
    if ($LogicDates === FALSE)
    {
        ErrorLog($StartDate.' shoud be the same or earlier than '.$EndDate.'. Aborting.', E_USER_ERROR);
    }

    //Now we have all the correct options
    $Result = cURLSimpleGET($URL, $Parameters);

    return $Result;
}

/**
 * SEBulkSiteEnergy
 * Returns the  energy measurements of a number of sites. This API is limited to one year when using timeUnit=DAY (i.e., daily resolution) and to one month when using 
 * timeUnit=QUARTER_OF_AN_HOUR or timeUnit=HOUR. This means that the period between endTime and startTime should not exceed one year or one month respectively. 
 * If the period is longer, the system will generate error 403
 * Energy is the integral of power, or SUM(Pw*P)[t0->tn] being t0 the initial time, tn the end time, PW the power and P the probing interval
 * Method:      GET
 * Request:     Parameter       Type    Mandatory   Description
 *              siteId          Integer Yes         see $SiteId @param
 *              startDate       String  Yes         see $StartDate @param
 *              endDate         String  Yes         see $EndDate @param
 *              timeUnit        String  No          see $TimeUnit @param
 * Response:    The response includes the requested time unit, the units of measurement (e.g. Wh), and the list of sites. For each site there is a list of items 
 *              which include a time stamp and the energy produced in that period. Example: ({"date":"2013-06-01 00:00:00","value":1500.12}). The date is 
 *              calculated based on the time zone of every site; if there is no value for the selected time, “null” will be displayed.
 * Example:     JSON output:
 *      {
 *        "energy": {
 *          "timeUnit": "DAY",
 *          "unit": "Wh",
 *          "count": 2,
 *          "list": [
 *            {
 *              "id": 1,
 *              "values": [
 *                {
 *                  "date": "2013-06-01 00:00:00",
 *                  "value": null
 *                },
 *                {
 *                  "date": "2013-06-02 00:00:00",
 *                  "value": null
 *                },
 *                {
 *                  "date": "2013-06-03 00:00:00",
 *                  "value": null
 *                },
 *                {
 *                  "date": "2013-06-04 00:00:00",
 *                  "value": 67313.24
 *                }
 *              ]
 *            },
 *            {
 *              "id": 4,
 *              "values": [
 *                {
 *                  "date": "2013-06-01 00:00:00",
 *                  "value": null
 *                },
 *                {
 *                  "date": "2013-06-02 00:00:00",
 *                  "value": null
 *                },
 *                {
 *                  "date": "2013-06-03 00:00:00",
 *                  "value": null
 *                },
 *                {
 *                  "date": "2013-06-04 00:00:00",
 *                  "value": 67313.24
 *                }
 *              ]
 *            }
 *          ]
 *        }
 *      }
 * 
 * @param   string  $APIKey         The API KEY to use
 * @param   array   $SiteIds        The site identifiers as an array of integers
 * @param   string  $StartDate      The start date to return energy measurement, ISO format (YYYY-MM-DD)
 * @param   string  $EndDate        The end date to return energy measurement, ISO format (YYYY-MM-DD)
 * @param   string  $TimeUnit       Aggregation granularity, default: DAY. Case sensitive. Available options: QUARTER_OF_AN_HOUR, HOUR, DAY, WEEK, MONTH, YEAR
 * @return  mixed   The response from the API in JSON array format or FALSE if something fails
 *                      HTTPCode:   The HTTP Code given by the server to our request
 *                      Response:   The server response
 * @since 0.0.9
 * @todo
 * @see
 */
function SEBulkSiteEnergy($APIKey, $SiteIds, $StartDate, $EndDate, $TimeUnit)
{
    //SiteIds validation
    if (!empty($SiteIds))
    {
        if (is_array($SiteIds))
        {
            $TheSites = count($SiteIds);
            if ($TheSites>100)
            {
                ErrorLog('Only the first 100 of the '.$TheSites.' sites will be processed', E_USER_WARNING);
                $Limit = 100;
            }
            else
            {
                $Limit = $TheSites;
            }
            $SiteIdList = '';
            for ($i = 0; $i<$Limit; $i++)
            {
                if (!IsValidInt($SiteIds[$i],1,NULL))
                {
                    ErrorLog('SiteId must be an integer. Aborting', E_USER_ERROR);

                    return FALSE;
                }
                else
                {
                    $SiteIdList .= $SiteIds[$i].',';
                }
            }
            $FinalSiteList = substr($SiteIdList, 0, strlen($SiteIdList)-1);
            $URL = MIL_SEBASEURL.'sites/'.$FinalSiteList.'/energy';
        }
        else
        {
            ErrorLog('SiteIds must be an array of integers. Aborting', E_USER_ERROR);

            return FALSE;
        }
    }
    else
    {
        ErrorLog('A siteIds array is compulsory', E_USER_ERROR);

        return FALSE;
    }

    $Parameters = array();

    //API KEY validation
    if (empty($APIKey))
    {
        ErrorLog('An API KEY is missing', E_USER_ERROR);

        return FALSE;
    }
    else
    {
        $Parameters['api_key'] = $APIKey;
    }

    //Parameters validation
    if (!empty($StartDate))
    {
        //The first practical photovoltaic cell was developed in April, 1954 at Bell Laboratories by Daryl Chaplin, Gerald Pearson and Calvin Souther Fuller.
        if (IsValidISODate($StartDate,'1954-04-01',date('Y-m-d')))
        {
            $Parameters['startDate'] = $StartDate;
        }
        else
        {
            ErrorLog('startDate must be a valid date between April, 1954 and today. Aborting', E_USER_ERROR);
        }
    }
    else
    {
        ErrorLog('startDate is compulsory. Aborting', E_USER_ERROR);

        return FALSE;
    }

    if (!empty($EndDate))
    {
        //The first practical photovoltaic cell was developed in April, 1954 at Bell Laboratories by Daryl Chaplin, Gerald Pearson and Calvin Souther Fuller.
        if (IsValidISODate($EndDate,'1954-04-01',date('Y-m-d')))
        {
            $Parameters['endDate'] = $EndDate;
        }
        else
        {
            ErrorLog('endDate must be a valid ISO date string (YYY-MM-DD) between April, 1954 and today. Aborting', E_USER_ERROR);
        }
    }
    else
    {
        ErrorLog('endDate is compulsory. Aborting', E_USER_ERROR);

        return FALSE;
    }

    $TimeUnitValues = array('QUARTER_OF_AN_HOUR', 'HOUR', 'DAY', 'WEEK', 'MONTH', 'YEAR');
    if (!empty($TimeUnit))
    {
        if (in_array($TimeUnit, $TimeUnitValues))
        {
            $Parameters['timeUnit'] = $TimeUnit;
        }
        else
        {
            ErrorLog('timeUnit is case-sensitive and must be one of those:'.implode(',',$TimeUnitValues).'. Skipping.', E_USER_WARNING);
        }
    }

    //Check FROM is earlier than TO
    $LogicDates = ISODateNoLaterThan($StartDate, $EndDate);
    if ($LogicDates === FALSE)
    {
        ErrorLog($StartDate.' shoud be the same or earlier than '.$EndDate.'. Aborting.', E_USER_ERROR);
    }

    //Now we have all the correct options
    $Result = cURLFullGET($URL, $Parameters);

    return $Result;
}

/**
 * SESiteEnergyTimePeriod
 * Returns the site total energy produced for a given period. Two measures, generated at StartDaye and generated at EndDate
 * Energy is the integral of power, or SUM(Pw*P)[t0->tn] being t0 the initial time, tn the end time, PW the power and P the probing interval
 * Method:      GET
 * Request:     Parameter       Type    Mandatory   Description
 *              siteId          Integer Yes         see $SiteId @param
 *              startDate       String  Yes         see $StartDate @param
 *              endDate         String  Yes         see $EndDate @param
 * Response:    The response includes the energy summary for the given time period with units of measurement (e.g. Wh). The date is calculated based on the 
 *              time zone where the site is located. 
 * Example:     JSON output:
 *      {
 *          "timeFrameEnergy":{
 *          "energy":761985.8,
 *          "unit":"Wh"
 *          }
 *      }
 * @param   string  $APIKey         The API KEY to use
 * @param   int     $SiteId         The site identifier
 * @param   string  $StartDate      The start date to return energy generation, ISO format (YYYY-MM-DD)
 * @param   string  $EndDate        The end date to return energy generation, ISO format (YYYY-MM-DD)
 * @return  mixed   The response from the API in JSON array format or FALSE if something fails
 *                      HTTPCode:   The HTTP Code given by the server to our request
 *                      Response:   The server response
 * @since 0.0.9
 * @todo
 * @see
 */
function SESiteEnergyTimePeriod($APIKey, $SiteId, $StartDate, $EndDate)
{
    $Parameters = array();

    //API KEY validation
    if (empty($APIKey))
    {
        ErrorLog('An API KEY is missing', E_USER_ERROR);

        return FALSE;
    }
    else
    {
        $Parameters['api_key'] = $APIKey;
    }

    //Parameters validation
    if (!empty($SiteId))
    {
        if (IsValidInt($SiteId,1,NULL))
        {
            $URL = MIL_SEBASEURL.'site/'.$SiteId.'/timeFrameEnergy';
        }
        else
        {
            ErrorLog('siteId must be a positive integer. Aborting', E_USER_ERROR);

            return FALSE;
        }
    }
    else
    {
        ErrorLog('siteId is compulsory. Aborting', E_USER_ERROR);

        return FALSE;
    }


    if (!empty($StartDate))
    {
        //The first practical photovoltaic cell was developed in April, 1954 at Bell Laboratories by Daryl Chaplin, Gerald Pearson and Calvin Souther Fuller.
        if (IsValidISODate($StartDate,'1954-04-01',date('Y-m-d')))
        {
            $Parameters['startDate'] = $StartDate;
        }
        else
        {
            ErrorLog('startDate must be a valid date between April, 1954 and today. Aborting', E_USER_ERROR);
        }
    }
    else
    {
        ErrorLog('startDate is compulsory. Aborting', E_USER_ERROR);

        return FALSE;
    }

    if (!empty($EndDate))
    {
        //The first practical photovoltaic cell was developed in April, 1954 at Bell Laboratories by Daryl Chaplin, Gerald Pearson and Calvin Souther Fuller.
        if (IsValidISODate($EndDate,'1954-04-01',date('Y-m-d')))
        {
            $Parameters['endDate'] = $EndDate;
        }
        else
        {
            ErrorLog('endDate must be a valid ISO date string (YYY-MM-DD) between April, 1954 and today. Aborting', E_USER_ERROR);
        }
    }
    else
    {
        ErrorLog('endDate is compulsory. Aborting', E_USER_ERROR);

        return FALSE;
    }

    //Check FROM is earlier than TO
    $LogicDates = ISODateNoLaterThan($StartDate, $EndDate);
    if ($LogicDates === FALSE)
    {
        ErrorLog($StartDate.' shoud be the same or earlier than '.$EndDate.'. Aborting.', E_USER_ERROR);
    }

    //Now we have all the correct options
    $Result = cURLSimpleGET($URL, $Parameters);

    return $Result;
}

/**
 * SEBulkSiteEnergyTimePeriod
 * Returns the site total energy produced for a given period of a number of sites. Two measures for each site, generated at StartDaye and generated at EndDate
 * Energy is the integral of power, or SUM(Pw*P)[t0->tn] being t0 the initial time, tn the end time, PW the power and P the probing interval
 * Method:      GET
 * Request:     Parameter       Type    Mandatory   Description
 *              siteId          Integer Yes         see $SiteId @param
 *              startDate       String  Yes         see $StartDate @param
 *              endDate         String  Yes         see $EndDate @param
 * Response:    The response includes the units of measurement (e.g. Wh), and the list of sites that include energy summary for the given time period. The date 
 *              is calculated based on the time zone of every site; if no data exists for the requested period, “null” will be displayed for the value field
 * Example:     JSON output:
 *      {
 *        "timeFrameEnergy": {
 *          "unit": "Wh",
 *          "count": 4,
 *          "list": [
 *            {
 *              "id": 1,
 *              "energy": 761985.8
 *            },
 *            {
 *              "id": 4,
 *              "energy": 234284.4
 *            },
 *            {
 *              "id": 534,
 *              "energy": null
 *            },
 *            {
 *              "id": 222,
 *              "energy": 9984724.5
 *            }
 *          ]
 *        }
 *      }
 * 
 * @param   string  $APIKey         The API KEY to use
 * @param   array   $SiteIds        The site identifiers as an array of integers
 * @param   string  $StartDate      The start date to return energy generation, ISO format (YYYY-MM-DD)
 * @param   string  $EndDate        The end date to return energy generation, ISO format (YYYY-MM-DD)
 * @return  mixed   The response from the API in JSON array format or FALSE if something fails
 *                      HTTPCode:   The HTTP Code given by the server to our request
 *                      Response:   The server response
 * @since 0.0.9
 * @todo
 * @see
 */
function SEBulkSiteEnergyTimePeriod($APIKey, $SiteIds, $StartDate, $EndDate)
{
    //SiteIds validation
    if (!empty($SiteIds))
    {
        if (is_array($SiteIds))
        {
            $TheSites = count($SiteIds);
            if ($TheSites>100)
            {
                ErrorLog('Only the first 100 of the '.$TheSites.' sites will be processed', E_USER_WARNING);
                $Limit = 100;
            }
            else
            {
                $Limit = $TheSites;
            }
            $SiteIdList = '';
            for ($i = 0; $i<$Limit; $i++)
            {
                if (!IsValidInt($SiteIds[$i],1,NULL))
                {
                    ErrorLog('SiteId must be an integer. Aborting', E_USER_ERROR);

                    return FALSE;
                }
                else
                {
                    $SiteIdList .= $SiteIds[$i].',';
                }
            }
            $FinalSiteList = substr($SiteIdList, 0, strlen($SiteIdList)-1);
            $URL = MIL_SEBASEURL.'sites/'.$FinalSiteList.'/timeFrameEnergy';
        }
        else
        {
            ErrorLog('SiteIds must be an array of integers. Aborting', E_USER_ERROR);

            return FALSE;
        }
    }
    else
    {
        ErrorLog('A siteIds array is compulsory', E_USER_ERROR);

        return FALSE;
    }

    $Parameters = array();

    //API KEY validation
    if (empty($APIKey))
    {
        ErrorLog('An API KEY is missing', E_USER_ERROR);

        return FALSE;
    }
    else
    {
        $Parameters['api_key'] = $APIKey;
    }

    //Parameters validation
    if (!empty($StartDate))
    {
        //The first practical photovoltaic cell was developed in April, 1954 at Bell Laboratories by Daryl Chaplin, Gerald Pearson and Calvin Souther Fuller.
        if (IsValidISODate($StartDate,'1954-04-01',date('Y-m-d')))
        {
            $Parameters['startDate'] = $StartDate;
        }
        else
        {
            ErrorLog('startDate must be a valid date between April, 1954 and today. Aborting', E_USER_ERROR);
        }
    }
    else
    {
        ErrorLog('startDate is compulsory. Aborting', E_USER_ERROR);

        return FALSE;
    }

    if (!empty($EndDate))
    {
        //The first practical photovoltaic cell was developed in April, 1954 at Bell Laboratories by Daryl Chaplin, Gerald Pearson and Calvin Souther Fuller.
        if (IsValidISODate($EndDate,'1954-04-01',date('Y-m-d')))
        {
            $Parameters['endDate'] = $EndDate;
        }
        else
        {
            ErrorLog('endDate must be a valid ISO date string (YYY-MM-DD) between April, 1954 and today. Aborting', E_USER_ERROR);
        }
    }
    else
    {
        ErrorLog('endDate is compulsory. Aborting', E_USER_ERROR);

        return FALSE;
    }

    //Check FROM is earlier than TO
    $LogicDates = ISODateNoLaterThan($StartDate, $EndDate);
    if ($LogicDates === FALSE)
    {
        ErrorLog($StartDate.' shoud be the same or earlier than '.$EndDate.'. Aborting.', E_USER_ERROR);
    }

    //Now we have all the correct options
    $Result = cURLFullGET($URL, $Parameters);

    return $Result;
}

/**
 * SESitePower
 * Returns the site power measurements in 15 minutes resolution. This endpoint is limited to one-month period. This means that the period between endTime and 
 * startTime should not exceed one month. If the period is longer, the system will generate error 403 with proper description.
 * The date is calculated in ticks starting 1-1-1970 and presented based on the time zone of the site. “null” means there is no data for that time.
 * Method:      GET
 * Request:     Parameter       Type    Mandatory   Description
 *              siteId          Integer Yes         see $SiteId @param
 *              startTime       String  Yes         see $StartTime @param
 *              endTime         String  Yes         see $EndTime @param
 * Response:    The response includes the time unit (i.e. QUARTER_OF_AN_HOUR), the measurement units (e.g. Watt) and the pairs of date and power (in Watts) for 
 *              every date ({"date":"2013-06-04 14:00:00","value":7722.3896})
 * Example:     JSON output:
 *      {
 *        "power": {
 *          "timeUnit": "QUARTER_OF_AN_HOUR",
 *          "unit": "W",
 *          "values": [
 *            {
 *              "date": "2013-06-04 11:00:00",
 *              "value": 7987.03
 *            },
 *            {
 *              "date": "2013-06-04 11:15:00",
 *              "value": 9710.121
 *            },
 *            {
 *              "date": "2013-06-04 11:30:00",
 *              "value": 8803.309
 *            },
 *            {
 *              "date": "2013-06-04 11:45:00",
 *              "value": 9000.743
 *            },
 *            {
 *              "date": "2013-06-04 12:00:00",
 *              "value": 6492.2075
 *            },
 *            {
 *              "date": "2013-06-04 12:15:00",
 *              "value": 7395.716
 *            },
 *            {
 *              "date": "2013-06-04 12:30:00",
 *              "value": 8855.878
 *            },
 *            {
 *              "date": "2013-06-04 12:45:00",
 *              "value": 6551.6655
 *            },
 *            {
 *              "date": "2013-06-04 13:00:00",
 *              "value": 8114.938
 *            },
 *            {
 *              "date": "2013-06-04 13:15:00",
 *              "value": 7466.171
 *            },
 *            {
 *              "date": "2013-06-04 13:30:00",
 *              "value": 6595.561
 *            },
 *            {
 *              "date": "2013-06-04 13:45:00",
 *              "value": 8824.195
 *            },
 *            {
 *              "date": "2013-06-04 14:00:00",
 *              "value": 7722.3896
 *            }
 *          ]
 *        }
 *      }
 * 
 * @param   string  $APIKey         The API KEY to use
 * @param   int     $SiteId         The site identifier
 * @param   string  $StartTime      The start datetime to get power measurements, ISO format (YYYY-MM-DD HH:MM:SS)
 * @param   string  $EndTime        The end datetime to get power measurements, ISO format (YYYY-MM-DD HH:MM:SS)
 * @return  mixed   The response from the API in JSON array format or FALSE if something fails
 *                      HTTPCode:   The HTTP Code given by the server to our request
 *                      Response:   The server response
 * @since   0.0.9
 * @todo
 * @see
 */
function SESitePower($APIKey, $SiteId, $StartTime, $EndTime)
{
    $Parameters = array();

    //API KEY validation
    if (empty($APIKey))
    {
        ErrorLog('An API KEY is missing', E_USER_ERROR);

        return FALSE;
    }
    else
    {
        $Parameters['api_key'] = $APIKey;
    }

    //Parameters validation
    if (!empty($SiteId))
    {
        if (IsValidInt($SiteId,1,NULL))
        {
            $URL = MIL_SEBASEURL.'site/'.$SiteId.'/power';
        }
        else
        {
            ErrorLog('siteId must be a positive integer. Aborting', E_USER_ERROR);

            return FALSE;
        }
    }
    else
    {
        ErrorLog('siteId is compulsory. Aborting', E_USER_ERROR);

        return FALSE;
    }


    if (!empty($StartTime))
    {
        //January,1st, 1970
        if (IsValidISODateTime($StartTime,'1970-01-01 00:00:00',date('Y-m-d H:i:s')))
        {
            $Parameters['startTime'] = $StartTime;
        }
        else
        {
            ErrorLog('startTime must be a valid ISO datetime string (YYYY-MM-DD HH:MM:SS) between January 1st, 1970 and today. Aborting', E_USER_ERROR);
        }
    }
    else
    {
        ErrorLog('startTime is compulsory. Aborting', E_USER_ERROR);

        return FALSE;
    }

    if (!empty($EndTime))
    {
        //January,1st, 1970
        if (IsValidISODateTime($EndTime,'1970-01-01 00:00:00',date('Y-m-d H:i:s')))
        {
            $Parameters['endTime'] = $EndTime;
        }
        else
        {
            ErrorLog('endTime must be a valid ISO datetime string (YYYY-MM-DD HH:MM:SS) between January 1st, 1970 and today. Aborting', E_USER_ERROR);
        }
    }
    else
    {
        ErrorLog('endTime is compulsory. Aborting', E_USER_ERROR);

        return FALSE;
    }

    //Check FROM is earlier than TO
    $LogicDates = ISODateTimeNoLaterThan($StartTime, $EndTime);
    if ($LogicDates === FALSE)
    {
        ErrorLog($StartTime.' shoud be the same or earlier than '.$EndTime.'. Aborting.', E_USER_ERROR);
    }

    //Check if both dates are no more than a month apart
    if (DatesSeparatedNoMoreThan($StartTime, $EndTime, 'Y-m-d H:i:s','1 month') === FALSE)
    {
        return FALSE;
    }

    //Now we have all the correct options
    $Result = cURLSimpleGET($URL, $Parameters);

    return $Result;
}

/**
 * SEBulkSitePower
 * Returns power measurements in 15 minutes resolution for a number of sites. This endpoint is limited to one-month period. This means that the period between 
 * endTime and startTime should not exceed one month. If the period is longer, the system will generate error 403 with proper description.
 * The date is calculated in ticks starting 1-1-1970 and presented based on the time zone of the site. “null” means there is no data for that time.
 * Method:      GET
 * Request:     Parameter       Type    Mandatory   Description
 *              siteId          Integer Yes         see $SiteId @param
 * Response:    The response includes the resolution of time measurements (e.g. QUARTER_OF_AN_HOUR), units of measurement (e.g. W), and the list of sites that 
 *              include date and power in the given resolution. The date is calculated in ticks starting 1-1-1970 and presented based on the time zone of the 
 *              site. If no data exists for the requested period, “null” will be displayed for the value field.
 * Example:     JSON output:
 *      {
 *        "power": {
 *          "timeUnit": "QUARTER_OF_AN_HOUR",
 *          "unit": "W",
 *          "count": 2,
 *          "list": [
 *            {
 *              "id": 1,
 *              "values": [
 *                {
 *                  "date": "2013-06-04 11:00:00",
 *                  "value": 7987.03
 *                },
 *                {
 *                  "date": "2013-06-04 11:15:00",
 *                  "value": 9710.121
 *                },
 *                {
 *                  "date": "2013-06-04 11:30:00",
 *                  "value": 8803.309
 *                },
 *                {
 *                  "date": "2013-06-04 11:45:00",
 *                  "value": 9000.743
 *                },
 *                {
 *                  "date": "2013-06-04 12:00:00",
 *                  "value": 6492.207
 *                }
 *              ]
 *            },
 *            {
 *              "id": 4,
 *              "values": [
 *                {
 *                  "date": "2013-06-04 11:00:00",
 *                  "value": 7987.03
 *                },
 *                {
 *                  "date": "2013-06-04 11:15:00",
 *                  "value": 9710.121
 *                },
 *                {
 *                  "date": "2013-06-04 11:30:00",
 *                  "value": 8803.309
 *                },
 *                {
 *                  "date": "2013-06-04 11:45:00",
 *                  "value": 9000.743
 *                },
 *                {
 *                  "date": "2013-06-04 12:00:00",
 *                  "value": 6492.2075
 *                }
 *              ]
 *            }
 *          ]
 *        }
 *      }
 * 
 * @param   string  $APIKey         The API KEY to use
 * @param   int     $SiteIds        The site identifier
 * @param   string  $StartTime      The start datetime to get power measurements, ISO format (YYYY-MM-DD HH:MM:SS)
 * @param   string  $EndTime        The end datetime to get power measurements, ISO format (YYYY-MM-DD HH:MM:SS)
 * @return  mixed   The response from the API in JSON array format or FALSE if something fails
 *                      HTTPCode:   The HTTP Code given by the server to our request
 *                      Response:   The server response
 * @since   0.0.9
 * @todo    
 * @see
 */
function SEBulkSitePower($APIKey, $SiteIds, $StartTime, $EndTime)
{
    //SiteIds validation
    if (!empty($SiteIds))
    {
        if (is_array($SiteIds))
        {
            $TheSites = count($SiteIds);
            if ($TheSites>100)
            {
                ErrorLog('Only the first 100 of the '.$TheSites.' sites will be processed', E_USER_WARNING);
                $Limit = 100;
            }
            else
            {
                $Limit = $TheSites;
            }
            $SiteIdList = '';
            for ($i = 0; $i<$Limit; $i++)
            {
                if (!IsValidInt($SiteIds[$i],1,NULL))
                {
                    ErrorLog('SiteId must be an integer. Aborting', E_USER_ERROR);

                    return FALSE;
                }
                else
                {
                    $SiteIdList .= $SiteIds[$i].',';
                }
            }
            $FinalSiteList = substr($SiteIdList, 0, strlen($SiteIdList)-1);
            $URL = MIL_SEBASEURL.'sites/'.$FinalSiteList.'/power';
        }
        else
        {
            ErrorLog('SiteIds must be an array of integers. Aborting', E_USER_ERROR);

            return FALSE;
        }
    }
    else
    {
        ErrorLog('A siteIds array is compulsory', E_USER_ERROR);

        return FALSE;
    }

    $Parameters = array();

    //API KEY validation
    if (empty($APIKey))
    {
        ErrorLog('An API KEY is missing', E_USER_ERROR);

        return FALSE;
    }
    else
    {
        $Parameters['api_key'] = $APIKey;
    }

    //Parameters validation
    if (!empty($StartTime))
    {
        //January,1st, 1970
        if (IsValidISODateTime($StartTime,'1970-01-01 00:00:00',date('Y-m-d H:i:s')))
        {
            $Parameters['startTime'] = $StartTime;
        }
        else
        {
            ErrorLog('startTime must be a valid ISO datetime string (YYYY-MM-DD HH:MM:SS) between January 1st, 1970 and today. Aborting', E_USER_ERROR);
        }
    }
    else
    {
        ErrorLog('startTime is compulsory. Aborting', E_USER_ERROR);

        return FALSE;
    }

    if (!empty($EndTime))
    {
        //January,1st, 1970
        if (IsValidISODateTime($EndTime,'1970-01-01 00:00:00',date('Y-m-d H:i:s')))
        {
            $Parameters['endTime'] = $EndTime;
        }
        else
        {
            ErrorLog('endTime must be a valid ISO datetime string (YYYY-MM-DD HH:MM:SS) between January 1st, 1970 and today. Aborting', E_USER_ERROR);
        }
    }
    else
    {
        ErrorLog('endTime is compulsory. Aborting', E_USER_ERROR);

        return FALSE;
    }

    //Check FROM is earlier than TO
    $LogicDates = ISODateTimeNoLaterThan($StartTime, $EndTime);
    if ($LogicDates === FALSE)
    {
        ErrorLog($StartTime.' shoud be the same or earlier than '.$EndTime.'. Aborting.', E_USER_ERROR);
    }

    //Check if both dates are no more than a month apart
    if (DatesSeparatedNoMoreThan($StartTime, $EndTime, 'Y-m-d H:i:s','1 month') === FALSE)
    {
        return FALSE;
    }

    //Now we have all the correct options
    $Result = cURLFullGET($URL, $Parameters);

    return $Result;
}

/**
 * DatesSeparatedNoMoreThan
 * Ad-hoc to test if dates are more than $DateInterval apart. Will evolve to a generic function
 * @param string $StartDateString   The initial date string
 * @param string $EndDateString     The end date string
 * @param string $DateFormat        The date format. See https://www.php.net/manual/en/datetimeimmutable.createfromformat.php
 * @param string $DateInterval      The interval. See https://www.php.net/manual/en/datetime.formats.relative.php
 * @return boolean
 * @since 0.0.9
 * @todo
 * @see
 */
function DatesSeparatedNoMoreThan($StartDateString, $EndDateString, $DateFormat, $DateInterval)
{
    $StartDate = date_create_from_format($DateFormat, $StartDateString);
    if ($StartDate === FALSE)
    {
        return FALSE;
    }

    $EndDate = date_create_from_format($DateFormat, $EndDateString);
    if ($EndDate === FALSE)
    {
        return FALSE;
    }

    $TheDateInterval = date_interval_create_from_date_string($DateInterval);
    if ($TheDateInterval === FALSE)
    {
        return FALSE;
    }

    //Supposedly never fails with proper -non FALSE- interval arguments
    $EndMinusInterval = date_sub($EndDate, $TheDateInterval);

    if ($EndMinusInterval>$StartDate)
    {
        return FALSE;
    }
    else
    {
        return TRUE;
    }
}

/**
 * SESiteOverview
 * Returns the site overview data
 * Method:      GET
 * Request:     Parameter       Type    Mandatory   Description
 *              siteId          Integer Yes         see $SiteId @param
 * Response:    The response includes the site current power, daily energy, monthly energy, yearly energy and life time energy.
 * Example:     JSON output:
 *      {
 *        "overview": {
 *          "lastUpdateTime": "2013-10-01 02:37:47",
 *          "lifeTimeData": {
 *            "energy": 761985.75,
 *            "revenue": 946.13104
 *          },
 *          "lastYearData": {
 *            "energy": 761985.8,
 *            "revenue": 0
 *          },
 *          "lastMonthData": {
 *            "energy": 492736.7,
 *            "revenue": 0
 *          },
 *          "lastDayData": {
 *            "energy": 0,
 *            "revenue": 0
 *          },
 *          "currentPower": {
 *            "power": 0
 *          }
 *        }
 *      }
 * 
 * @param   string  $APIKey         The API KEY to use
 * @param   int     $SiteId         The site identifier
 * @return  mixed   The response from the API in JSON array format or FALSE if something fails
 *                      HTTPCode:   The HTTP Code given by the server to our request
 *                      Response:   The server response
 * @since   0.0.9
 * @todo    
 * @see
 */
function SESiteOverview($APIKey, $SiteId)
{
    //SiteId validation
    if (!empty($SiteId))
    {
        if (IsValidInt($SiteId,1,NULL))
        {
            $URL = MIL_SEBASEURL.'site/'.$SiteId.'/overview';
        }
        else
        {
            ErrorLog('siteId must be a positive integer. Aborting', E_USER_ERROR);

            return FALSE;
        }
    }

    $Parameters = array();

    //API KEY validation
    if (empty($APIKey))
    {
        ErrorLog('An API KEY is missing', E_USER_ERROR);

        return FALSE;
    }
    else
    {
        $Parameters['api_key'] = $APIKey;
    }

    //Now we have all the correct options
    $Result = cURLSimpleGET($URL, $Parameters);

    return $Result;
}

/**
 * SEBulkSiteOverview
 * Returns the overview data of a number of sites
 * Method:      GET
 * Request:     Parameter       Type    Mandatory   Description
 *              siteId          Integer Yes         see $SiteId @param
 * Response:    The response includes the last update time, current power, and daily, monthly, yearly and life time energy and revenue measurements for each of 
 *              the sites in the list.
 * Example:     JSON output:
 *      {
 *        "overview": {
 *          "count": 2,
 *          "list": [
 *            {
 *              "id": 1,
 *              "lastUpdateTime": "2013-10-01 02:37:47",
 *              "lifeTimeData": {
 *                "energy": 761985.75,
 *                "revenue": 946.13104
 *              },
 *              "lastYearData": {
 *                "energy": 761985.8,
 *                "revenue": 0
 *              },
 *              "lastMonthData": {
 *                "energy": 492736.7,
 *                "revenue": 0
 *              },
 *              "lastDayData": {
 *                "energy": 0,
 *                "revenue": 0
 *              },
 *              "currentPower": {
 *                "power": 0
 *              }
 *            },
 *            {
 *              "id": 4,
 *              "lastUpdateTime": "2013-10-01 02:37:47",
 *              "lifeTimeData": {
 *                "energy": 761985.75,
 *                "revenue": 946.13104
 *              },
 *              "lastYearData": {
 *                "energy": 761985.8,
 *                "revenue": 0
 *              },
 *              "lastMonthData": {
 *                "energy": 492736.7,
 *                "revenue": 0
 *              },
 *              "lastDayData": {
 *                "energy": 0,
 *                "revenue": 0
 *              },
 *              "currentPower": {
 *                "power": 0
 *              }
 *           }
 *          ]
 *        }
 *      }
 * 
 * @param   string  $APIKey         The API KEY to use
 * @param   array   $SiteIds        The site identifiers. Maximun 100 sites.
 * @return  mixed   The response from the API in JSON array format or FALSE if something fails
 *                      HTTPCode:   The HTTP Code given by the server to our request
 *                      Response:   The server response
 * @since   0.0.9
 * @todo    
 * @see
 */
function SEBulkSiteOverview($APIKey, $SiteIds)
{
    //SiteIds validation
    if (!empty($SiteIds))
    {
        if (is_array($SiteIds))
        {
            $TheSites = count($SiteIds);
            if ($TheSites>100)
            {
                ErrorLog('Only the first 100 of the '.$TheSites.' sites will be processed', E_USER_WARNING);
                $Limit = 100;
            }
            else
            {
                $Limit = $TheSites;
            }
            $SiteIdList = '';
            for ($i = 0; $i<$Limit; $i++)
            {
                if (!IsValidInt($SiteIds[$i],1,NULL))
                {
                    ErrorLog('SiteId must be an integer. Aborting', E_USER_ERROR);

                    return FALSE;
                }
                else
                {
                    $SiteIdList .= $SiteIds[$i].',';
                }
            }
            $FinalSiteList = substr($SiteIdList, 0, strlen($SiteIdList)-1);
            $URL = MIL_SEBASEURL.'sites/'.$FinalSiteList.'/overview';
        }
        else
        {
            ErrorLog('SiteIds must be an array of integers. Aborting', E_USER_ERROR);

            return FALSE;
        }
    }
    else
    {
        ErrorLog('A siteIds array is compulsory', E_USER_ERROR);

        return FALSE;
    }

    $Parameters = array();

    //API KEY validation
    if (empty($APIKey))
    {
        ErrorLog('An API KEY is missing', E_USER_ERROR);

        return FALSE;
    }
    else
    {
        $Parameters['api_key'] = $APIKey;
    }

    //Now we have all the correct options
    $Result = cURLFullGET($URL, $Parameters);

    return $Result;
}

/**
 * SESitePowerDetailed
 * Returns the site power measurements in 15 minutes resolution. This endpoint is limited to one-month period. This means that the period between endTime and 
 * startTime should not exceed one month. If the period is longer, the system will generate error 403 with proper description.
 * The date is calculated in ticks starting 1-1-1970 and presented based on the time zone of the site. “null” means there is no data for that time.
 * Method:      GET
 * Request:     Parameter       Type    Mandatory   Description
 *              siteId          Integer Yes         see $SiteId @param
 *              startTime       String  Yes         see $StartTime @param
 *              endTime         String  Yes         see $EndTime @param
 *              meters          String  No          see $Meters @param
 * Response:    The response provides 15 minute resolution data series for each of the requested meters. The response includes the following
 *              - powerDetails:     Root element
 *                  - timeUnit:     The time unit of the data (i.e. QUARTER_OF_AN_HOUR)
 *                  - unit:         Power measurement units (e.g. Watt)
 *                  - meters:       List of meters. For each meter:
 *                      - type:     The meter type (Production/Consumption/SelfConsumption/FeedIn (export)/Purchased (import))
 *                      - values:   Pairs of date and power for every date ({"date":"2013-06-04 14:00:00" , "value":7722.3896})
 * Example:     JSON output:
 *      {
 *        "powerDetails": {
 *          "timeUnit": "QUARTER_OF_AN_HOUR",
 *          "unit": "W",
 *          "meters": [
 *            {
 *              "type": "Consumption",
 *              "values": [
 *                {
 *                  "date": "2015-11-21 11:00:00",
 *                  "value": 619.8288
 *                },
 *                {
 *                  "date": "2015-11-21 11:15:00",
 *                  "value": 474.87576
 *                },
 *                {
 *                  "date": "2015-11-21 11:30:00",
 *                  "value": 404.7733
 *                }
 *              ]
 *            },
 *            {
 *              "type": "Purchased",
 *              "values": [
 *                {
 *                  "date": "2015-11-21 11:00:00",
 *                  "value": 619.8288
 *                },
 *                {
 *                  "date": "2015-11-21 11:15:00",
 *                  "value": 474.87576
 *                },
 *                {
 *                  "date": "2015-11-21 11:30:00",
 *                  "value": 404.7733
 *                }
 *              ]
 *            },
 *            {
 *              "type": "Production",
 *              "values": [
 *                {
 *                  "date": "2015-11-21 11:00:00",
 *                  "value": 0
 *                },
 *                {
 *                  "date": "2015-11-21 11:15:00",
 *                  "value": 0
 *                },
 *                {
 *                  "date": "2015-11-21 11:30:00",
 *                  "value": 0
 *                }
 *              ]
 *            },
 *            {
 *              "type": "SelfConsumption",
 *              "values": [
 *                {
 *                  "date": "2015-11-21 11:00:00",
 *                  "value": 0
 *                },
 *                {
 *                  "date": "2015-11-21 11:15:00",
 *                  "value": 0
 *                },
 *                {
 *                  "date": "2015-11-21 11:30:00",
 *                  "value": 0
 *                }
 *              ]
 *            },
 *            {
 *              "type": "FeedIn",
 *              "values": [
 *                {
 *                  "date": "2015-11-21 11:00:00",
 *                  "value": 0
 *                },
 *                {
 *                  "date": "2015-11-21 11:15:00",
 *                  "value": 0
 *                },
 *                {
 *                  "date": "2015-11-21 11:30:00",
 *                  "value": 0
 *                }
 *              ]
 *            }
 *          ]
 *        }
 *      }
 * 
 * @param   string  $APIKey         The API KEY to use
 * @param   int     $SiteId         The site identifier
 * @param   string  $StartTime      The power measured start time, ISO format (YYYY-MM-DD HH:MM:SS)
 * @param   string  $EndTime        The power measured end time, ISO format (YYYY-MM-DD HH:MM:SS)
 * @param   array   $Meters         Select specific meters only. If this value is omitted, all meter readings are returned. Array can include any of these 
 *                                  elements: PRODUCTION -AC production power meter or, as a fallback,  inverter production AC power-, CONSUMPTION -Consumption 
 *                                  meter-, SELFCONSUMPTION -virtual self-consumption (calculated)-, FEEDIN -Export to GRID meter-, PURCHASED -Import power from 
 *                                  GRID meter-
 * @return  mixed   The response from the API in JSON array format or FALSE if something fails
 *                      HTTPCode:   The HTTP Code given by the server to our request
 *                      Response:   The server response
 * @since   0.0.9
 * @todo    
 * @see
 */
function SESitePowerDetailed($APIKey, $SiteId, $StartTime, $EndTime, $Meters)
{
    $Parameters = array();

    //API KEY validation
    if (empty($APIKey))
    {
        ErrorLog('An API KEY is missing', E_USER_ERROR);

        return FALSE;
    }
    else
    {
        $Parameters['api_key'] = $APIKey;
    }

    //Parameters validation
    if (!empty($SiteId))
    {
        if (IsValidInt($SiteId,1,NULL))
        {
            $URL = MIL_SEBASEURL.'site/'.$SiteId.'/powerDetails';
        }
        else
        {
            ErrorLog('siteId must be a positive integer. Aborting', E_USER_ERROR);

            return FALSE;
        }
    }
    else
    {
        ErrorLog('siteId is compulsory. Aborting', E_USER_ERROR);

        return FALSE;
    }


    if (!empty($StartTime))
    {
        //January,1st, 1970
        if (IsValidISODateTime($StartTime,'1970-01-01 00:00:00',date('Y-m-d H:i:s')))
        {
            $Parameters['startTime'] = $StartTime;
        }
        else
        {
            ErrorLog('startTime must be a valid ISO datetime string (YYYY-MM-DD HH:MM:SS) between January 1st, 1970 and today. Aborting', E_USER_ERROR);
        }
    }
    else
    {
        ErrorLog('startTime is compulsory. Aborting', E_USER_ERROR);

        return FALSE;
    }

    if (!empty($EndTime))
    {
        //January,1st, 1970
        if (IsValidISODateTime($EndTime,'1970-01-01 00:00:00',date('Y-m-d H:i:s')))
        {
            $Parameters['endTime'] = $EndTime;
        }
        else
        {
            ErrorLog('endTime must be a valid ISO datetime string (YYYY-MM-DD HH:MM:SS) between January 1st, 1970 and today. Aborting', E_USER_ERROR);
        }
    }
    else
    {
        ErrorLog('endTime is compulsory. Aborting', E_USER_ERROR);

        return FALSE;
    }

    //Check FROM is earlier than TO
    $LogicDates = ISODateTimeNoLaterThan($StartTime, $EndTime);
    if ($LogicDates === FALSE)
    {
        ErrorLog($StartTime.' shoud be the same or earlier than '.$EndTime.'. Aborting.', E_USER_ERROR);
    }

    //Check if both dates are no more than a month apart
    if (DatesSeparatedNoMoreThan($StartTime, $EndTime, 'Y-m-d H:i:s','1 month') === FALSE)
    {
        return FALSE;
    }

    //Validate meters
    $ValidMeters = array('PRODUCTION', 'CONSUMPTION', 'SELFCONSUMPTION', 'FEEDIN', 'PURCHASED');
    if (!empty($Meters))
    {
        $Size = count($Meters);
        if ($Size>5)
        {
            ErrorLog('You specified '.$Size.' meter categories and there are only five. Skipping.', E_USER_WARNING);
        }
        foreach ($Meters as $Key => $Value)
        {
            if (in_array($Value, $ValidMeters) === FALSE)
            {
                ErrorLog($Value.' is not a valid meter category ('.implode(',',$ValidMeters).'. Aborting.', E_USER_ERROR);
            }
        }

        $Parameters['meters'] = implode(',',$Meters);
    }

    //Now we have all the correct options
    $Result = cURLSimpleGET($URL, $Parameters);

    return $Result;
}

/**
 * SESiteEnergyDetailed
 * Detailed site energy measurements from meters such as consumption, export (feed-in), import (purchase), etc. This endpoint is limited to a year when using 
 * daily resolution (timeUnit=DAY), a month when using hourly resolution of higher (timeUnit=QUARTER_OF_AN_HOUR or timeUnit=HOUR). Lower resolutions (weekly, 
 * monthly, yearly) have no period limitation. In case the requested resolution is not allowed for the requested period, error 403 with proper description will 
 * be returned.
 * The date is calculated in ticks starting 1-1-1970 and presented based on the time zone of the site. “null” means there is no data for that time.
 * Energy is the integral of power, or SUM(Pw*P)[t0->tn] being t0 the initial time, tn the end time, PW the power and P the probing interval
 * Method:      GET
 * Request:     Parameter       Type    Mandatory   Description
 *              siteId          Integer Yes         see $SiteId @param
 *              startTime       String  Yes         see $StartTime @param
 *              endTime         String  Yes         see $EndTime @param
 *              timeUnit        String  No          see $TimeUnit @param
 *              meters          String  No          see $Meters @param
 * Response:    The response returns power flow for each of the elements in the system and their state. In case the site does not support this information, the 
 *              response should be an empty object. Otherwise, the response includes the following:
 *              - energyDetails:    Root element
 *                  - timeUnit:     the requested time unit
 *                  - unit:         The measurement units (e.g. Wh)
 *                  - meters:       List of meters. For each meter:
 *                      - type:     The meter type (Production/Consumption/SelfConsumption/FeedIn (export)/Purchased (import))
 *                      - values:   Pairs of date and power for every date ({"date":"2013-06-04 14:00:00" , "value":7722.3896})
 * Example:     JSON output:
 *      {
 *        "energyDetails": {
 *          "timeUnit": "WEEK",
 *          "unit": "Wh",
 *          "meters": [
 *            {
 *              "type": "Production",
 *              "values": [
 *                {
 *                  "date": "2015-10-19 00:00:00"
 *                },
 *                {
 *                  "date": "2015-10-26 00:00:00"
 *                },
 *                {
 *                  "date": "2015-11-02 00:00:00"
 *                },
 *                {
 *                  "date": "2015-11-09 00:00:00"
 *                },
 *                {
 *                  "date": "2015-11-16 00:00:00",
 *                  "value": 2953
 *                }
 *              ]
 *            },
 *            {
 *              "type": "Consumption",
 *              "values": [
 *                {
 *                  "date": "2015-10-19 00:00:00"
 *                },
 *                {
 *                  "date": "2015-10-26 00:00:00"
 *                },
 *                {
 *                  "date": "2015-11-02 00:00:00"
 *                },
 *                {
 *                  "date": "2015-11-09 00:00:00"
 *                },
 *                {
 *                  "date": "2015-11-16 00:00:00",
 *                  "value": 29885
 *                }
 *              ]
 *            }
 *          ]
 *        }
 *      }
 * 
 * @param   string  $APIKey         The API KEY to use
 * @param   int     $SiteId         The site identifier
 * @param   string  $StartTime      The power measured start time, ISO format (YYYY-MM-DD HH:MM:SS)
 * @param   string  $EndTime        The power measured end time, ISO format (YYYY-MM-DD HH:MM:SS)
 * @param   string  $TimeUnit       Aggregation granularity, default: DAY. Case sensitive. Available options: QUARTER_OF_AN_HOUR, HOUR, DAY, WEEK, MONTH, YEAR
 * @param   array   $Meters         Select specific meters only. If this value is omitted, all meter readings are returned. Array can include any of these 
 *                                  elements: PRODUCTION -AC production power meter or, as a fallback,  inverter production AC power-, CONSUMPTION -Consumption 
 *                                  meter-, SELFCONSUMPTION -virtual self-consumption (calculated)-, FEEDIN -Export to GRID meter-, PURCHASED -Import power from 
 *                                  GRID meter-
 * @return  mixed   The response from the API in JSON array format or FALSE if something fails
 *                      HTTPCode:   The HTTP Code given by the server to our request
 *                      Response:   The server response
 * @since   0.0.9
 * @todo    
 * @see
 */
function SESiteEnergyDetailed($APIKey, $SiteId, $StartTime, $EndTime, $TimeUnit, $Meters)
{
    $Parameters = array();

    //API KEY validation
    if (empty($APIKey))
    {
        ErrorLog('An API KEY is missing', E_USER_ERROR);

        return FALSE;
    }
    else
    {
        $Parameters['api_key'] = $APIKey;
    }

    //Parameters validation
    if (!empty($SiteId))
    {
        if (IsValidInt($SiteId,1,NULL))
        {
            $URL = MIL_SEBASEURL.'site/'.$SiteId.'/energyDetails';
        }
        else
        {
            ErrorLog('siteId must be a positive integer. Aborting', E_USER_ERROR);

            return FALSE;
        }
    }
    else
    {
        ErrorLog('siteId is compulsory. Aborting', E_USER_ERROR);

        return FALSE;
    }


    if (!empty($StartTime))
    {
        //January,1st, 1970
        if (IsValidISODateTime($StartTime,'1970-01-01 00:00:00',date('Y-m-d H:i:s')))
        {
            $Parameters['startTime'] = $StartTime;
        }
        else
        {
            ErrorLog('startTime must be a valid ISO datetime string (YYYY-MM-DD HH:MM:SS) between January 1st, 1970 and today. Aborting', E_USER_ERROR);
        }
    }
    else
    {
        ErrorLog('startTime is compulsory. Aborting', E_USER_ERROR);

        return FALSE;
    }

    if (!empty($EndTime))
    {
        //January,1st, 1970
        if (IsValidISODateTime($EndTime,'1970-01-01 00:00:00',date('Y-m-d H:i:s')))
        {
            $Parameters['endTime'] = $EndTime;
        }
        else
        {
            ErrorLog('endTime must be a valid ISO datetime string (YYYY-MM-DD HH:MM:SS) between January 1st, 1970 and today. Aborting', E_USER_ERROR);
        }
    }
    else
    {
        ErrorLog('endTime is compulsory. Aborting', E_USER_ERROR);

        return FALSE;
    }

    //Check FROM is earlier than TO
    $LogicDates = ISODateTimeNoLaterThan($StartTime, $EndTime);
    if ($LogicDates === FALSE)
    {
        ErrorLog($StartTime.' shoud be the same or earlier than '.$EndTime.'. Aborting.', E_USER_ERROR);
    }

    //Check if both dates are no more than a month apart
    if (DatesSeparatedNoMoreThan($StartTime, $EndTime, 'Y-m-d H:i:s','1 month') === FALSE)
    {
        return FALSE;
    }

    //Validate Time Unit
    $TimeUnitValues = array('QUARTER_OF_AN_HOUR', 'HOUR', 'DAY', 'WEEK', 'MONTH', 'YEAR');
    if (!empty($TimeUnit))
    {
        if (in_array($TimeUnit, $TimeUnitValues))
        {
            $Parameters['timeUnit'] = $TimeUnit;
        }
        else
        {
            ErrorLog('timeUnit is case-sensitive and must be one of those:'.implode(',',$TimeUnitValues).'. Skipping.', E_USER_WARNING);
        }
    }

    //Validate meters
    $ValidMeters = array('PRODUCTION', 'CONSUMPTION', 'SELFCONSUMPTION', 'FEEDIN', 'PURCHASED');
    if (!empty($Meters))
    {
        $Size = count($Meters);
        if ($Size>5)
        {
            ErrorLog('You specified '.$Size.' meter categories and there are only five. Skipping.', E_USER_WARNING);
        }
        foreach ($Meters as $Key => $Value)
        {
            if (in_array($Value, $ValidMeters) === FALSE)
            {
                ErrorLog($Value.' is not a valid meter category ('.implode(',',$ValidMeters).'. Aborting.', E_USER_ERROR);
            }
        }

        $Parameters['meters'] = implode(',',$Meters);
    }

    //Now we have all the correct options
    $Result = cURLSimpleGET($URL, $Parameters);

    return $Result;
}

/**
 * SESitePowerFlow
 * Retrieves the current power flow between all elements of the site including PV array, storage (battery), loads (consumption) and grid
 * Method:      GET
 * Request:     Parameter       Type    Mandatory   Description
 *              siteId          Integer Yes         see $SiteId @param
 * Response:    The response returns power flow for each of the elements in the system and their state. In case the site does not support this information, the 
 *              response should be an empty object. Otherwise, the response includes the following:
 *              - siteCurrentPowerFlow: Root element
 *              - unit:                 The measurement units (e.g. Watt)
 *              - connections:          A table including all the relationships between the elements, and the power flow directions (producing element and 
 *                                      consuming element):
 *                                          - from: from element - The element providing power
 *                                          - to:   to element - The element consuming power
 *              - A list of elements: "GRID" -always-, "LOAD" -always-, "PV" -if available-, "STORAGE" -if available-. Each element show the following:
 *                                          - status:       The current status of the element (Active/Idle/Disabled)
 *                                          - currentPower: The current power of the element. All numbers are positive; power direction is determined by the 
 *                                                          “connections” section above.
 *                                          - chargeLevel:  Only for STORAGE. The accumulated state of energy (% of charge) for all batteries
 *                                          - critical:     Only for STORAGE. If the accumulated storage charge level drops below a configurable level 
 *                                                          (currently 10%), this flag is returned.
 *                                          - timeLeft:     Only for STORAGE. In Backup mode (GRID is Disabled), this property is returned to specify the time 
 *                                                          left before the storage energy runs out (estimated according to current load level).
 * Example:     JSON output:
 *      {
 *        "siteCurrentPowerFlow": {
 *        "unit": "W",
 *          "connections": [
 *            {
 *              "from": "GRID",
 *              "to": "Load"
 *            }
 *          ],
 *          "GRID": {
 *            "status": "Active",
 *            "currentPower": 3435.77978515625
 *          },
 *          "LOAD": {
 *            "status": "Active",
 *            "currentPower": 3435.77978515625
 *          },
 *          "PV": {
 *            "status": "Idle",
 *            "currentPower": 0
 *          },
 *          "STORAGE": {
 *            "status": "Idle",
 *            "currentPower": 0,
 *            "chargeLevel": 27,
 *            "critical": false
 *          }
 *        }
 *      }
 * 
 * @param   string  $APIKey         The API KEY to use
 * @param   int     $SiteId         The site identifier
 * @return  mixed   The response from the API in JSON array format or FALSE if something fails
 *                      HTTPCode:   The HTTP Code given by the server to our request
 *                      Response:   The server response
 * @since   0.0.9
 * @todo    
 * @see
 */
function SESitePowerFlow($APIKey, $SiteId)
{
    //SiteId validation
    if (!empty($SiteId))
    {
        if (IsValidInt($SiteId,1,NULL))
        {
            $URL = MIL_SEBASEURL.'site/'.$SiteId.'/currentPowerFlow';
        }
        else
        {
            ErrorLog('siteId must be a positive integer. Aborting', E_USER_ERROR);

            return FALSE;
        }
    }

    $Parameters = array();

    //API KEY validation
    if (empty($APIKey))
    {
        ErrorLog('An API KEY is missing', E_USER_ERROR);

        return FALSE;
    }
    else
    {
        $Parameters['api_key'] = $APIKey;
    }

    //Now we have all the correct options
    $Result = cURLSimpleGET($URL, $Parameters);

    return $Result;
}

/**
 * SESiteStorageData
 * Gets detailed storage information from batteries: the state of energy, power and lifetime energy. This endpoint is limited to one-week period. Specifying a 
 * period that is longer than 7 days will generate error 403 with proper description
 * Method:      GET
 * Request:     Parameter       Type    Mandatory   Description
 *              siteId          Integer Yes         see $SiteId @param
 *              startTime       String  Yes         see $StartTime @param
 *              endTime         String  Yes         see $EndTime @param
 *              serials         String  No          see $Serials @param
 * Response:    The response includes the following:
 *                  - storageData:  Root element
 *                  - batteryCount: Number of batteries includes in the response
 *                  - batteries:    A list of battery objects, each containing the following:
 *                                  - serialNumber:     The battery serial number
 *                                  - nameplate:        The nameplate (nominal) capacity of the battery
 *                                  - modelNumber:      Battery model number
 *                                  - telemetryCount:   The number of telemetries for this battery in the response
 *                                  - telemetries:      A list of storage data telemetries. each entry contains:
 *                                                      - timeStamp:                Telemetry timestamp in the format YYYY-MM-DD HH:MM:SS
 *                                                      - power:                    Positive power indicates the battery is charging, negative is discharging
 *                                                      - batteryState:             One of the following: 0 (Invalid), 1 (Standby), 2 (Thermal Mgmt.), 3 
 *                                                                                      (Enabled), 4 (Fault)
 *                                                      - lifeTimeEnergyCharged:    The energy Charged from the battery in Wh, during battery lifetime.
 *                                                      - lifeTimeEnergyDischarged: The energy discharged from the battery in Wh, during battery lifetime.
 *                                                      - fullPackEnergyAvailable:  The maximum energy (Wh) that can currently be stored in the battery. Note 
 *                                                                                      that the battery state of health (SoH) can be calculated from this value. 
 *                                                                                      SoH is defined as Full Pack Energy available today/Full Pack Energy 
 *                                                                                      available on day one. Full pack energy available on day one can be 
 *                                                                                      extracted from the battery nameplate value or battery model information. 
 *                                                                                      Both the battery name plate value and model number are provided by the 
 *                                                                                      storageData method.
 *                                                      - internalTemp:             Battery internal temperature in Celsius
 *                                                      - ACGridCharging:           Amount of AC energy used to charge the battery from grid within a specified 
 *                                                                                      date range in Wh.
 *                                                      - stateOfCharge:            The battery state of charge as percentage of the available capacity.
 * Example:     JSON output:
 *      {
 *        "storageData": {
 *          "batteryCount": 1,
 *          "batteries": [
 *            {
 *              "nameplate": 1,
 *              "serialNumber": "BFA",
 *              "modelNumber": "LGXXXXX-XXX",
 *              "telemetryCount": 9,
 *              "telemetries": [
 *                {
 *                  "timeStamp": "2015-10-13 08:00:00",
 *                  "power": 12,
 *                  "batteryState": 3,
 *                  "lifeTimeEnergyCharged": 6,
 *                  "lifeTimeEnergyDischarged": 6,
 *                  "fullPackEnergyAvailable": 8950,
 *                  "internalTemp": 38,
 *                  "ACGridCharging": 234
 *                },...
 *                {
 *                  "timeStamp": "2015-10-13 08:15:00",
 *                  "power": 12,
 *                  "batteryState": 3,
 *                  "lifeTimeEnergyCharged": 6,
 *                  "lifeTimeEnergyDischarged": 6,
 *                  "fullPackEnergyAvailable": 8950,
 *                  "internalTemp": 38,
 *                  "ACGridCharging": 234
 *                }
 *              ]
 *            }
 *          ]
 *        }
 *      }
 * 
 * @param   string  $APIKey         The API KEY to use
 * @param   int     $SiteId         The site identifier
 * @param   string  $StartTime      Storage power measured start time, ISO format (YYYY-MM-DD HH:MM:SS)
 * @param   string  $EndTime        Storage power measured end time, ISO format (YYYY-MM-DD HH:MM:SS)
 * @param   string  $Serials        Return data only for specific battery serial numbers; the list is comma separated. If omitted, the response includes all the 
 *                                  batteries in the site
 * @return  mixed   The response from the API in JSON array format or FALSE if something fails
 *                      HTTPCode:   The HTTP Code given by the server to our request
 *                      Response:   The server response
 * @since   0.0.9
 * @todo
 * @see
 */
function SESiteStorageData($APIKey, $SiteId, $StartTime, $EndTime, $Serials)
{
    $Parameters = array();

    //API KEY validation
    if (empty($APIKey))
    {
        ErrorLog('An API KEY is missing', E_USER_ERROR);

        return FALSE;
    }
    else
    {
        $Parameters['api_key'] = $APIKey;
    }

    //Parameters validation
    if (!empty($SiteId))
    {
        if (IsValidInt($SiteId,1,NULL))
        {
            $URL = MIL_SEBASEURL.'site/'.$SiteId.'/storageData';
        }
        else
        {
            ErrorLog('siteId must be a positive integer. Aborting', E_USER_ERROR);

            return FALSE;
        }
    }
    else
    {
        ErrorLog('siteId is compulsory. Aborting', E_USER_ERROR);

        return FALSE;
    }


    if (!empty($StartTime))
    {
        //January,1st, 1970
        if (IsValidISODateTime($StartTime,'1970-01-01 00:00:00',date('Y-m-d H:i:s')))
        {
            $Parameters['startTime'] = $StartTime;
        }
        else
        {
            ErrorLog('startTime must be a valid ISO datetime string (YYYY-MM-DD HH:MM:SS) between January 1st, 1970 and today. Aborting', E_USER_ERROR);
        }
    }
    else
    {
        ErrorLog('startTime is compulsory. Aborting', E_USER_ERROR);

        return FALSE;
    }

    if (!empty($EndTime))
    {
        //January,1st, 1970
        if (IsValidISODateTime($EndTime,'1970-01-01 00:00:00',date('Y-m-d H:i:s')))
        {
            $Parameters['endTime'] = $EndTime;
        }
        else
        {
            ErrorLog('endTime must be a valid ISO datetime string (YYYY-MM-DD HH:MM:SS) between January 1st, 1970 and today. Aborting', E_USER_ERROR);
        }
    }
    else
    {
        ErrorLog('endTime is compulsory. Aborting', E_USER_ERROR);

        return FALSE;
    }

    //Check FROM is earlier than TO
    $LogicDates = ISODateTimeNoLaterThan($StartTime, $EndTime);
    if ($LogicDates === FALSE)
    {
        ErrorLog($StartTime.' shoud be the same or earlier than '.$EndTime.'. Aborting.', E_USER_ERROR);
    }

    //Check if both dates are no more than a 7 days apart
    if (DatesSeparatedNoMoreThan($StartTime, $EndTime, 'Y-m-d H:i:s','7 days') === FALSE)
    {
        return FALSE;
    }

    //Include Serials, if available
    if (!empty($Serials))
    {
        $Parameters['serials'] = $Serials;
    }

    //Now we have all the correct options
    $Result = cURLSimpleGET($URL, $Parameters);

    return $Result;
}

/**
 * SESiteEnvironmentalBenefits
 * Returns all environmental benefits based on site energy production: CO2 emissions saved, equivalent trees planted, and light bulbs powered for a day.
 * Method:      GET
 * Request:     Parameter       Type    Mandatory   Description
 *              siteId          Integer Yes         see $SiteId @param
 *              systemUnits     String  No          see $SystemUnits @param
 * Response:    Returns the list of environmental benefits associated with the site energy production:
 *                  - gasEmissionSaved: Quantity of CO2 emissions that would have been generated by an equivalent fossil fuel system.
 *                  - treesPlanted:     Equivalent planting of new trees for reducing CO2 levels
 *                  - lightBulbs:       Number of light bulbs that could have been powered by the site for a day
 * Example:     JSON output:
 *      {
 *          "envBenefits": {
 *              "gasEmissionSaved": {
 *                  "units": "kg",
 *                  "co2": 5914.8447,
 *                  "so2": 11321.556,
 *                  "nox": 3610.4175
 *              },
 *              "treesPlanted": 260.7523776,
 *              "lightBulbs": 67534.93
 *          }
 *      }
 * 
 * @param   string  $APIKey         The API KEY to use
 * @param   int     $SiteId         The site identifier
 * @param   string  $SystemUnits    The system units used when returning gas emission savings: 'Metrics', 'Imperial' – note systemUnits are case sensitive. If 
 *                                  systemUnits are not specified, the logged in user system units are used.
 * @return  mixed   The response from the API in JSON array format or FALSE if something fails
 *                      HTTPCode:   The HTTP Code given by the server to our request
 *                      Response:   The server response
 * @since   0.0.9
 * @todo    
 * @see
 */
function SESiteEnvironmentalBenefits($APIKey, $SiteId, $SystemUnits)
{
    $Parameters = array();

    //API KEY validation
    if (empty($APIKey))
    {
        ErrorLog('An API KEY is missing', E_USER_ERROR);

        return FALSE;
    }
    else
    {
        $Parameters['api_key'] = $APIKey;
    }

    //Parameters validation
    if (!empty($SiteId))
    {
        if (IsValidInt($SiteId,1,NULL))
        {
            $URL = MIL_SEBASEURL.'site/'.$SiteId.'/envBenefits';
        }
        else
        {
            ErrorLog('siteId must be a positive integer. Aborting', E_USER_ERROR);

            return FALSE;
        }
    }
    else
    {
        ErrorLog('siteId is compulsory. Aborting', E_USER_ERROR);

        return FALSE;
    }


    //Validate Time Unit
    if (!empty($SystemUnits))
    {
        if (($SystemUnits === 'Metrics')||($SystemUnits === 'Imperial'))
        {
            $Parameters['systemUnits'] = $SystemUnits;
        }
        else
        {
            ErrorLog('systemUnits is case-sensitive and must be either Metrics or Imperial. Skipping.', E_USER_WARNING);
        }
    }

    //Now we have all the correct options
    $Result = cURLSimpleGET($URL, $Parameters);

    return $Result;
}

//EndPoints for Solar Edge Site Equipment API

/**
 * SESiteComponents
 * Returns a list of inverters/SMIs in the specific site
 * Method:      GET
 * Request:     Parameter       Type    Mandatory   Description
 *              siteId          Integer Yes         see $SiteId @param
 * Response:    The response includes a list of inverters/SMIs with their name, model, manufacturer and serial number
 * Example:     JSON output:
 *      {
 *        "list": [
 *          {
 *            "name": "Inverter 1",
 *            "manufacturer": "SolarEdge",
 *            "model": "SE16K",
 *            "serialNumber": "12345678-00"
 *          },...
 *          {
 *            "name": "Inverter 1",
 *            "manufacturer": "SolarEdge",
 *            "model": "SE16K",
 *            "serialNumber": "12345678-65"
 *          }
 *        ]
 *      }
 * 
 * @param   string  $APIKey         The API KEY to use
 * @param   int     $SiteId         The site identifier
 * @return  mixed   The response from the API in JSON array format or FALSE if something fails
 *                      HTTPCode:   The HTTP Code given by the server to our request
 *                      Response:   The server response
 * @since 0.0.9
 * @todo  
 * @see
 */
function SESiteComponents($APIKey, $SiteId)
{
    //SiteId validation
    if (!empty($SiteId))
    {
        if (IsValidInt($SiteId,1,NULL))
        {
            $URL = MIL_SEBASEURL.'site/'.$SiteId.'/list';
        }
        else
        {
            ErrorLog('siteId must be a positive integer. Aborting', E_USER_ERROR);

            return FALSE;
        }
    }

    $Parameters = array();

    //API KEY validation
    if (empty($APIKey))
    {
        ErrorLog('An API KEY is missing', E_USER_ERROR);

        return FALSE;
    }
    else
    {
        $Parameters['api_key'] = $APIKey;
    }

    //Now we have all the correct options
    $Result = cURLSimpleGET($URL, $Parameters);

    return $Result;
}

/**
 * SESiteInventory
 * Returns the inventory of SolarEdge equipment in the site, including inverters/SMIs, batteries, meters, gateways and sensors
 * Method:      GET
 * Request:     Parameter       Type    Mandatory   Description
 *              siteId          Integer Yes         see $SiteId @param
 * Response:    The response includes a list equipment installed on site:
 *                  - inverters:            SolarEdge inverters
 *                      - name:                         The inverter name e.g. Inverter 1
 *                      - manufacturer:                 Manufacturer name (SolarEdge)
 *                      - model:                        Model name e.g. SE16K
 *                      - firmwareVersion:              CPU Firmware version e.g. 2.52.311
 *                      - DSP1:                         DSP 1 Firmware version
 *                      - DSP2:                         DSP 2 Firmware version
 *                      - communicationMethod:          The communication interface used to connect to server. E.g. Ethernet.
 *                      - serialNumber:                 The equipment serial number e.g. 7F123456-00
 *                      - connectedOptimizers:          Number of optimizers connected to the inverter
 *                  - thirdPartyInverters:  3rd party inverters
 *                      - name:                         The inverter name e.g. Inverter 1
 *                      - manufacturer:                 Manufacturer name
 *                      - model:                        Model name
 *                      - SN:                           Serial number
 *                  - meters:               Connected meters
 *                      - name:                         The meter name e.g. “Feed In Meter”
 *                      - manufacturer:                 Manufacturer name e.g. “WattNode”
 *                      - model:                        Meter model number
 *                      - SN:                           Serial number (if applicable)
 *                      - Type:                         Meter type, e.g. “Production”
 *                      - firmwareVersion:              Firmware Version (if applicable)
 *                      - ConnectedTo:                  Name of SolarEdge device the meter is connected to
 *                      - connectedSolaredgeDeviceSN:   Serial number of the inverter / gateway the meter is connected to
 *                      - form:                         Physical for a HW meter or virtual if calculated by arithmetic between other meters
 *                  - sensors:               Irradiance/wind/temperature sensors
 *                      - connectedSolaredgeDeviceSN:   The S/N of the device it is connected to
 *                      - Id:                           Id – e.g. “SensorDirectIrradiance”
 *                      - ConnectedTo:                  Name of the device it is connected to e.g “Gateway 1”
 *                      - Category:                     Category – e.g. IRRADIANCE
 *                      - Type:                         Type – e.g. “Plane of array irradiance”
 *                  - gateways:
 *                      - name:                         The equipment name
 *                      - serialNumber:                 The equipment serial number e.g. 7F123456-00
 *                      - firmwareVersion:              Firmware Version
 *                  - batteries:
 *                      - name:                         The equipment name
 *                      - serialNumber:                 The equipment serial number
 *                      - Manufacturer:                 The battery manufacturer name
 *                      - Model:                        The battery model name
 *                      - nameplateCapacity:            The nameplate capacity of the battery as provided by the manufacturer
 *                      - firmwareVersion:              Firmware Version
 *                      - ConnectedTo:                  Name of SolarEdge device the battery is connected to
 *                      - connectedSolaredgeDeviceSN:   Serial number of the inverter/gateway the battery is connected to
 * Example:     JSON output:
 *      {
 *          "Inventory": {
 *              "meters": [
 *                  {
 *                      "name": "Self Consumption",
 *                      "firmwareVersion": "",
 *                      "connectedTo": "Inverter 1",
 *                      "connectedSolaredgeDeviceSN": "12345678-90",
 *                      "type": "SelfConsumption",
 *                      "form": "virtual"
 *                  },
 *                  {
 *                      "name": "Consumption Meter",
 *                      "firmwareVersion": "",
 *                      "connectedTo": "Inverter 1",
 *                      "connectedSolaredgeDeviceSN": "12345678-90",
 *                      "type": "Consumption",
 *                      "form": "virtual"
 *                  },
 *                  {
 *                      "name": "Import Meter",
 *                      "manufacturer": "SolarEdge",
 *                      "model": "SE-RGMTR-1D-240C-A",
 *                      "firmwareVersion": "31",
 *                      "connectedTo": "Inverter 1",
 *                      "connectedSolaredgeDeviceSN": "12345678-90",
 *                      "type": "Purchased",
 *                      "form": "physical",
 *                      "SN": "1234567"
 *                  },
 *                  {
 *                      "name": "Export Meter",
 *                      "manufacturer": "SolarEdge",
 *                      "model": "SE-RGMTR-1D-240C-A",
 *                      "firmwareVersion": "31",
 *                      "connectedTo": "Inverter 1",
 *                      "connectedSolaredgeDeviceSN": "12345678-90",
 *                      "type": "FeedIn",
 *                      "form": "physical",
 *                      "SN": "7654321"
 *                  }
 *              ],
 *              "sensors": [
 *                  {
 *                      "connectedSolaredgeDeviceSN":"12345678-90",
 *                      "id":"SensorDirectIrradiance",
 *                      "connectedTo":"Gateway 1",
 *                      "category":"IRRADIANCE",
 *                      "type":"Direct irradiance"
 *                  }
 *              ],
 *              "gateways": [
 *                  {
 *                      "name":"Gateway 1",
 *                      "firmwareVersion":"2.956.0",
 *                      "SN":"12345678-00"
 *                  }
 *              ],
 *              "batteries": [
 *                  {
 *                      "name":"Battery 1.1",
 *                      "manufacturer":"NAME",
 *                      "model":"10KWh",
 *                      "firmwareVersion":"2.0",
 *                      "connectedInverterSn":"12345678-90",
 *                      "nameplateCapacity":6400.0,
 *                      "SN":"T123456789
 *                  }
 *              ],
 *              "inverters": [
 *                  {
 *                      "name": "Inverter 1",
 *                      "manufacturer": "SolarEdge",
 *                      "model": "SE8000H-RW000BNN4",
 *                      "communicationMethod": "WIFI",
 *                      "cpuVersion": "4.12.28",
 *                      "SN": "12345678-90",
 *                      "connectedOptimizers": 20
 *                  }
 *              ]
 *          }
 *      }
 * 
 * @param   string  $APIKey         The API KEY to use
 * @param   int     $SiteId         The site identifier
 * @return  mixed   The response from the API in JSON array format or FALSE if something fails
 *                      HTTPCode:   The HTTP Code given by the server to our request
 *                      Response:   The server response
 * @since 0.0.9
 * @todo
 * @see
 */
function SESiteInventory($APIKey, $SiteId)
{
    //SiteId validation
    if (!empty($SiteId))
    {
        if (IsValidInt($SiteId,1,NULL))
        {
            $URL = MIL_SEBASEURL.'site/'.$SiteId.'/inventory';
        }
        else
        {
            ErrorLog('siteId must be a positive integer. Aborting', E_USER_ERROR);

            return FALSE;
        }
    }

    $Parameters = array();

    //API KEY validation
    if (empty($APIKey))
    {
        ErrorLog('An API KEY is missing', E_USER_ERROR);

        return FALSE;
    }
    else
    {
        $Parameters['api_key'] = $APIKey;
    }

    //Now we have all the correct options
    $Result = cURLSimpleGET($URL, $Parameters);

    return $Result;
}

/**
 * SEInverterMeasures
 * Returns specific inverter data -measures- for a given timeframe. This endpoint is limited to one-week period. Specifying a 
 * period that is longer than 7 days will generate error 403 with proper description
 * Method:      GET
 * Request:     Parameter       Type    Mandatory   Description
 *              siteId          Integer Yes         see $SiteId @param
 *              serialNumber    String  Yes         see $SerialNumber @param
 *              startTime       String  Yes         see $StartTime @param
 *              endTime       String  Yes           see $EndTime @param
 * Response:    The response includes technical parameters as for the inverter’s performance (e.g., voltage, current, active power etc.), inverter type (1ph or 
 *              3ph), and software version. If an attribute is not supported based on the inverter version or type it will be omitted from the response.
 *                  Original name           Comment                                                 Data divided by phase
 *                  timestamp                                                                       No
 *                  AC current                                                                      Yes
 *                  AC voltage                                                                      Yes
 *                  AC frequency                                                                    Yes
 *                  QRef                                                                            Yes
 *                  CosPhi                                                                          Yes
 *                  Total Active Power                                                              No
 *                  apparentPower           Supported starting communication board version 2.474    Yes
 *                  activePower             Supported starting communication board version 2.474    Yes
 *                  reactivePower           Supported starting communication board version 2.474    Yes
 *                  DC voltage                                                                      No
 *                  groundFaultResistance                                                           No
 *                  powerLimit %                                                                    No
 *                  Lifetime energy         Supported starting communication board version 2.474    No
 *                  inverterMode            (1) See inverter mode options                           No
 *                  operationMode           (2) See operation mode options                          No
 *                  apparentPower           VA                                                      Yes
 *                  activePower             VA                                                      Yes
 *                  reactivePower           VAR                                                     Yes
 *                  cosPhi                                                                          Yes
 *                  vL1ToN                                                                          1 ph only
 *                  vL2ToN                                                                          1 ph only
 *                  vL1To2                                                                          3 ph only
 *                  vL2To3                                                                          3 ph only
 *                  vL1To2                                                                          3 ph only
 *              (1) Inverter mode options:
 *                  - OFF:                      Off
 *                  - NIGHT:                    Night mode
 *                  - WAKE_UP:                  Pre-production
 *                  - PRODUCTION:               Production
 *                  - PRODUCTION_LIMIT:         Forced power reduction
 *                  - SHUTDOWN:                 Shutdown procedure
 *                  - ERROR:                    Error mode
 *                  - SETUP:                    Maintenance
 *                  - LOCKED_STDBY:             Standby mode lock
 *                  - LOCKED_FIRE_FIGHTERS:     Fire-fighters mode lock
 *                  - LOCKED_FORCE_SHUTDOWN:    Forced shutdown from server
 *                  - LOCKED_COMM_TIMEOUT:      Communication timeout
 *                  - LOCKED_INV_TRIP:          Inverter self-lock trip
 *                  - LOCKED_INV_ARC_DETECTED:  Inverter self-lock on arc detection
 *                  - LOCKED_DG:                Inverter lock due to DG mode enable
 *              (2) Operation mode options:
 *                  - 0:    On-grid
 *                  - 1:    Off-grid mode using PV or battery
 *                  - 3:    Off-grid mode with generator (e.g. diesel) is present
 * Example:     JSON output:
 *      {
 *          "data": {
 *              "count": 12,
 *              "telemetries": [
 *                  {
 *                      "date": "2022-07-03 14:01:17",
 *                      "totalActivePower": 6885.34,
 *                      "dcVoltage": 401.287,
 *                      "groundFaultResistance": 11000.0,
 *                      "powerLimit": 100.0,
 *                      "totalEnergy": 1.57938E7,
 *                      "temperature": 66.1165,
 *                      "inverterMode": "MPPT",
 *                      "operationMode": 0,
 *                      "L1Data": {
 *                          "acCurrent": 29.684,
 *                          "acVoltage": 231.809,
 *                          "acFrequency": 49.992,
 *                          "apparentPower": 6888.35,
 *                          "activePower": 6885.34,
 *                          "reactivePower": 203.804,
 *                          "cosPhi": 1.0
 *                      }
 *                  },...
 *                  {
 *                      "date": "2022-07-03 14:56:17",
 *                      "totalActivePower": 7122.83,
 *                      "dcVoltage": 401.378,
 *                      "groundFaultResistance": 11000.0,
 *                      "powerLimit": 100.0,
 *                      "totalEnergy": 1.58004E7,
 *                      "temperature": 70.0935,
 *                      "inverterMode": "MPPT",
 *                      "operationMode": 0,
 *                      "L1Data": {
 *                          "acCurrent": 30.2474,
 *                          "acVoltage": 235.232,
 *                          "acFrequency": 50.0111,
 *                          "apparentPower": 7125.93,
 *                          "activePower": 7122.83,
 *                          "reactivePower": 209.876,
 *                          "cosPhi": 1.0
 *                      }
 *                  }
 *              ]
 *          }
 *      }
 * @param   string  $APIKey         The API KEY to use
 * @param   int     $SiteId         The site identifier
 * @param   string  $SerialNumber   The inverter short serial number
 * @param   string  $StartTime      Inverter data start time, ISO format (YYYY-MM-DD HH:MM:SS)
 * @param   string  $EndTime        Inverter data end time, ISO format (YYYY-MM-DD HH:MM:SS)
 * @return  mixed   The response from the API in JSON array format or FALSE if something fails
 *                      HTTPCode:   The HTTP Code given by the server to our request
 *                      Response:   The server response
 * @since 0.0.9
 * @todo 
 * @see
 */
function SEInverterMeasures($APIKey, $SiteId, $SerialNumber, $StartTime, $EndTime)
{
    $Parameters = array();

    //API KEY validation
    if (empty($APIKey))
    {
        ErrorLog('An API KEY is missing', E_USER_ERROR);

        return FALSE;
    }
    else
    {
        $Parameters['api_key'] = $APIKey;
    }

    //Parameters validation
    if (!empty($SiteId))
    {
        if (IsValidInt($SiteId,1,NULL))
        {
            //We also need a S/N to call
            if (!empty($SerialNumber))
            {
                $URL = MIL_SEBASEURL.'equipment/'.$SiteId.'/'.$SerialNumber.'/data';
            }
            else
            {
                ErrorLog('serialNumber is compulsory. Aborting', E_USER_ERROR);

                return FALSE;
            }
        }
        else
        {
            ErrorLog('siteId must be a positive integer. Aborting', E_USER_ERROR);

            return FALSE;
        }
    }
    else
    {
        ErrorLog('siteId is compulsory. Aborting', E_USER_ERROR);

        return FALSE;
    }


    if (!empty($StartTime))
    {
        //January,1st, 1970
        if (IsValidISODateTime($StartTime,'1970-01-01 00:00:00',date('Y-m-d H:i:s')))
        {
            $Parameters['startTime'] = $StartTime;
        }
        else
        {
            ErrorLog('startTime must be a valid ISO datetime string (YYYY-MM-DD HH:MM:SS) between January 1st, 1970 and today. Aborting', E_USER_ERROR);
        }
    }
    else
    {
        ErrorLog('startTime is compulsory. Aborting', E_USER_ERROR);

        return FALSE;
    }

    if (!empty($EndTime))
    {
        //January,1st, 1970
        if (IsValidISODateTime($EndTime,'1970-01-01 00:00:00',date('Y-m-d H:i:s')))
        {
            $Parameters['endTime'] = $EndTime;
        }
        else
        {
            ErrorLog('endTime must be a valid ISO datetime string (YYYY-MM-DD HH:MM:SS) between January 1st, 1970 and today. Aborting', E_USER_ERROR);
        }
    }
    else
    {
        ErrorLog('endTime is compulsory. Aborting', E_USER_ERROR);

        return FALSE;
    }

    //Check FROM is earlier than TO
    $LogicDates = ISODateTimeNoLaterThan($StartTime, $EndTime);
    if ($LogicDates === FALSE)
    {
        ErrorLog($StartTime.' shoud be the same or earlier than '.$EndTime.'. Aborting.', E_USER_ERROR);
    }

    //Check if both dates are no more than a 7 days apart
    if (DatesSeparatedNoMoreThan($StartTime, $EndTime, 'Y-m-d H:i:s','7 days') === FALSE)
    {
        return FALSE;
    }

    //Now we have all the correct options
    $Result = cURLSimpleGET($URL, $Parameters);

    return $Result;
}

/**
 * SESiteChangesLog
 * Returns a list of equipment component replacements ordered by date. This method is applicable to inverters, optimizers, batteries and gateways
 * Method:      GET
 * Request:     Parameter       Type    Mandatory   Description
 *              siteId          Integer Yes         see $SiteId @param
 *              serialNumber    String  Yes         see $SerialNumber @param
 * Response:    The response includes a list of replacements by the specified equipment component, ordered-by date. The list contains the component serial 
 *              number, model and date of replacement. 
 *                  - count:        number of replacements of specified component
 *                  - list:         list of replacements where each replacement contains:
 *                          - serialNumber: equipment short serial number
 *                          - partNumber:   inverter/battery/optimizer/gateway model.
 *                          - date:         date of replacement of that equipment component
 * Example:     JSON output:
 *      {
 *        "ChangeLog": {
 *          "count": 1,
 *          "list": {
 *            "serialNumber": "1234567-3A",
 *            "partNumber": null,
 *            "date": "2017-08-30"
 *          }
 *        }
 *      }
 * @param   string  $APIKey         The API KEY to use
 * @param   int     $SiteId         The site identifier
 * @param   string  $SerialNumber   Inverter, battery, optimizer or gateway short serial number
 * @return  mixed   The response from the API in JSON array format or FALSE if something fails
 *                      HTTPCode:   The HTTP Code given by the server to our request
 *                      Response:   The server response
 * @since 0.0.9
 * @todo
 * @see
 */
function SESiteChangesLog($APIKey, $SiteId, $SerialNumber)
{
    $Parameters = array();

    //API KEY validation
    if (empty($APIKey))
    {
        ErrorLog('An API KEY is missing', E_USER_ERROR);

        return FALSE;
    }
    else
    {
        $Parameters['api_key'] = $APIKey;
    }

    //Parameters validation
    if (!empty($SiteId))
    {
        if (IsValidInt($SiteId,1,NULL))
        {
            //We also need a S/N to call
            if (!empty($SerialNumber))
            {
                $URL = MIL_SEBASEURL.'equipment/'.$SiteId.'/'.$SerialNumber.'/changeLog';
            }
            else
            {
                ErrorLog('serialNumber is compulsory. Aborting', E_USER_ERROR);

                return FALSE;
            }
        }
        else
        {
            ErrorLog('siteId must be a positive integer. Aborting', E_USER_ERROR);

            return FALSE;
        }
    }
    else
    {
        ErrorLog('siteId is compulsory. Aborting', E_USER_ERROR);

        return FALSE;
    }

    //Now we have all the correct options
    $Result = cURLSimpleGET($URL, $Parameters);

    return $Result;
}

//EndPoints for Solar Edge Accounts API

/**
 * SEAccountsList
 * Returns a list of sites related to the given token, which is the account api_key. 
 * This endpoint accepts parameters for convenient search, sort and pagination.
 * **WARNING** Gives a 403 on end-customer API Keys, seemingly
 * Method:      GET
 * Request:     Parameter       Type    Mandatory   Default value   Description
 *              size            Integer No          100             see $Size @param
 *              startIndex      Integer No          0               see $StartIndex @param
 *              searchText      String  No                          see $SearchText @param
 *              sortProperty    String  No                          see $SortProperty @param
 *              sortOrder       String  No          ASC             see $SortOrder @param
 * Response:    The returned data is the account data, including sub-accounts. For each entry, the following information is displayed:
 *                  - id:               account ID
 *                  - name:             account name
 *                  - location:         includes country, state, city, address, address2 (secondary address), zip
 *                  - companyWebSite:   the company web site
 *                  - contactPerson:    the account contact person first name and surname
 *                  - email:            the contact person email
 *                  - phoneNumber:      account phone number
 *                  - faxNumber:        account fax number
 *                  - notes:            account notes
 *                  - parentId:         account parent identifier
 * Example:     JSON output:
 *                  {
 *                    "accounts": {
 *                      "count": 2638,
 *                      "list": [
 *                        {
 *                          "id": 0,
 *                          "name": " account 1",
 *                          "location": {
 *                            "country": "my country",
 *                            "state": null,
 *                            "city": null,
 *                            "address": "my address 4",
 *                            "address2": "my address 2",
 *                            "zip": "00000"
 *                          },
 *                          "companyWebSite": "",
 *                          "contactPerson": "Saar",
 *                          "email": "mail@mail.com",
 *                          "phoneNumber": "+00000000",
 *                          "faxNumber": "",
 *                          "notes": " ",
 *                          "parentId": 32,
 *                          "uris": null
 *                        }
 *                      ]
 *                    }
 *                  }
 * @param   string  $APIKey         The API KEY to use
 * @param   int     $Size           The maximum number of accounts returned by this call. If you have more than 100 accounts, just request another 100 accounts 
 *                                  with startIndex=100. This will fetch accounts 100-199.
 *                                  Default value is 100.
 * @param   int     $StartIndex     The first account index to be returned in the results. Default value is 0;
 * @param   string  $SearchText     Search text for this account. Searchable site properties: Name – the account name, Notes, Email – contact person email, 
 *                                  Country, State, City, Zip code, Full address.
 * @param   string  $SortProperty   A case-sensitive sorting option for this site list, based on one of its properties: Name (sort by account name), Country 
 *                                  (sort by account country), City (sort by account city), Address (sort by account address), Zip (sort by account zip code), 
 *                                  Fax (sort by account fax number), Phone (sort by account phone), Notes (sort by account notes).
 * @param   string  $SortOrder      Sort order for the sort property. Allowed values are ASC (ascending) and DESC (descending). Default value is ASC
 * @return  mixed   The response from the API in JSON array format or FALSE if something fails
 *                      HTTPCode:   The HTTP Code given by the server to our request
 *                      Response:   The server response
 * @since 0.0.9
 * @todo
 * @see
 */
function SEAccountsList($APIKey, $Size = NULL, $StartIndex = NULL, $SearchText = NULL, $SortProperty = NULL,
                    $SortOrder = NULL)
{
    $URL = MIL_SEBASEURL.'accounts/list';
    $Parameters = array();

    //API KEY validation
    if (empty($APIKey))
    {
        ErrorLog('An API KEY is missing', E_USER_ERROR);

        return FALSE;
    }
    else
    {
        $Parameters['api_key'] = $APIKey;
    }

    //Parameters validation
    if (!empty($Size))
    {
        if (IsValidInt($Size,1,100))
        {
            $Parameters['size'] = $Size;
        }
        else
        {
            ErrorLog('size must be an integer between 1 and 100. Skipping the option', E_USER_WARNING);
        }
    }

    if (!empty($StartIndex))
    {
        if (IsValidInt($StartIndex,1,NULL))
        {
            $Parameters['startIndex'] = $StartIndex;
        }
        else
        {
            ErrorLog('startIndex must be a positive integer. Skipping the option', E_USER_WARNING);
        }
    }

    if (!empty($SearchText))
    {
        if (is_string($SearchText))
        {
            $Parameters['searchText'] = $SearchText;
        }
        else
        {
            ErrorLog('searchText must be a string. Skipping the option', E_USER_WARNING);
        }
    }

    $SortValues = array('Name', 'Country', 'City', 'Address', 'Zip', 'Fax', 'Phone', 'Notes');
    if (!empty($SortProperty))
    {
        if (in_array($SortProperty, $SortValues))
        {
            $Parameters['sortProperty'] = $SortProperty;
        }
        else
        {
            ErrorLog('sortProperty is case-sensitive and must be one of those:'.implode(',',$SortValues).'. Skipping.', E_USER_WARNING);
        }
    }

    $OrderValues = array('ASC', 'DESC');
    if (!empty($SortOrder))
    {
        if (in_array($SortOrder, $OrderValues))
        {
            $Parameters['sortOrder'] = $SortOrder;
        }
        else
        {
            ErrorLog('sortOrder is case-sensitive and must be one of those:'.implode(',',$OrderValues).'. Skipping.', E_USER_WARNING);
        }
    }

    //Now we have all the correct options
    $Result = cURLSimpleGET($URL, $Parameters);

    return $Result;
}

//EndPoints for Solar Edge Meters API

/**
 * SESiteEnergyTimePeriodPerMeter
 * Returns for each meter on site its lifetime energy reading, metadata and the device to which it’s connected to.
 * Method:      GET
 * Request:     Parameter   Type    Mandatory   Description
 *              siteId      Integer Yes         see $SiteId @param
 *              timeUnit    String  No          see $TimeUnit @param
 *              startTime   String  Yes         see $StartTime @param
 *              endTime     String  Yes         see $EndTime @param
 *              meters      String  No          see $Meters @param
 * Response:    Response parameters include lifetime energy reading at the defined granularity within the specified date range, including the following 
 *              parameters:
 *                  Original Name               Comment                                         Data divided per meter
 *                  timeUnit                    Aggregation granularity                         No
 *                  Unit                        Wh                                              No
 *                  meterSerialNumber                                                           Yes
 *                  connectedSolaredgeDeviceSN  Inverter to which the meter is connected to     Yes
 *                  model                       Meter model                                     Yes
 *                  meterType                   Production, Consumption, FeedIn or Purchased    Yes
 *                  date                        Measurement timestamp                           Yes
 *                  value                       Lifetime energy reading                         Yes
 * Example:     JSON output:
 *              {
 *                  "meterEnergyDetails": {
 *                      "timeUnit": "DAY",
 *                      "unit": "Wh",
 *                      "meters": [
 *                          {
 *                              "meterSerialNumber": "12345678",
 *                              "connectedSolaredgeDeviceSN": "7E212128-E8",
 *                              "model": "RWNC-3Y-480-MB",
 *                              "meterType": "FeedIn",
 *                              "values": [
 *                                  {
 *                                      "date": "2022-07-03 00:01:17",
 *                                      "value": 1.0996777E7
 *                                  },
 *                                  {
 *                                      "date": "2022-07-04 00:01:16",
 *                                      "value": 1.1030984E7
 *                                  }
 *                              ]
 *                          },
 *                          {
 *                              "meterSerialNumber": "12345678",
 *                              "connectedSolaredgeDeviceSN": "7E212128-E8",
 *                              "model": "RWNC-3Y-480-MB",
 *                              "meterType": "Purchased",
 *                              "values": [
 *                                  {
 *                                      "date": "2022-07-03 00:01:17",
 *                                      "value": 4349689.0
 *                                  },
 *                                  {
 *                                      "date": "2022-07-04 00:01:16",
 *                                      "value": 4360356.0
 *                                  }
 *                              ]
 *                          }
 *                      ]
 *                  }
 *              }
 * @param   string  $APIKey         The API KEY to use
 * @param   int     $SiteId         The site identifier
 * @param   string  $TimeUnit       Aggregation granularity, default: DAY. Case sensitive. Available options: QUARTER_OF_AN_HOUR, HOUR, DAY, WEEK, MONTH, YEAR
 * @param   string  $StartTime      The Enrgy measured start time, ISO format (YYYY-MM-DD HH:MM:SS)
 * @param   string  $EndTime        The power measured end time, ISO format (YYYY-MM-DD HH:MM:SS)
 * @param   array   $Meters         Select specific meters only. If this value is omitted, all meter readings are returned. Array can include any of these 
 *                                  elements: PRODUCTION -AC production power meter or, as a fallback,  inverter production AC power-, CONSUMPTION -Consumption 
 *                                  meter-, FEEDIN -Exported to GRID meter-, PURCHASED -Imported power from grid meter-
 * @return  mixed   The response from the API in JSON array format or FALSE if something fails
 *                      HTTPCode:   The HTTP Code given by the server to our request
 *                      Response:   The server response
 * @since 0.0.9
 * @todo
 * @see
 */
function SESiteEnergyTimePeriodPerMeter($APIKey, $SiteId, $TimeUnit, $StartTime, $EndTime, $Meters)
{
    $Parameters = array();

    //API KEY validation
    if (empty($APIKey))
    {
        ErrorLog('An API KEY is missing', E_USER_ERROR);

        return FALSE;
    }
    else
    {
        $Parameters['api_key'] = $APIKey;
    }

    //Parameters validation
    if (!empty($SiteId))
    {
        if (IsValidInt($SiteId,1,NULL))
        {
            $URL = MIL_SEBASEURL.'site/'.$SiteId.'/meters';
        }
        else
        {
            ErrorLog('siteId must be a positive integer. Aborting', E_USER_ERROR);

            return FALSE;
        }
    }
    else
    {
        ErrorLog('siteId is compulsory. Aborting', E_USER_ERROR);

        return FALSE;
    }

    //Validate Time Unit
    $TimeUnitValues = array('QUARTER_OF_AN_HOUR', 'HOUR', 'DAY', 'WEEK', 'MONTH', 'YEAR');
    if (!empty($TimeUnit))
    {
        if (in_array($TimeUnit, $TimeUnitValues))
        {
            $Parameters['timeUnit'] = $TimeUnit;
        }
        else
        {
            ErrorLog('timeUnit is case-sensitive and must be one of those:'.implode(',',$TimeUnitValues).'. Skipping.', E_USER_WARNING);
        }
    }

    //Time period validation

    if (!empty($StartTime))
    {
        //January,1st, 1970
        if (IsValidISODateTime($StartTime,'1970-01-01 00:00:00',date('Y-m-d H:i:s')))
        {
            $Parameters['startTime'] = $StartTime;
        }
        else
        {
            ErrorLog('startTime must be a valid ISO datetime string (YYYY-MM-DD HH:MM:SS) between January 1st, 1970 and today. Aborting', E_USER_ERROR);
        }
    }
    else
    {
        ErrorLog('startTime is compulsory. Aborting', E_USER_ERROR);

        return FALSE;
    }

    if (!empty($EndTime))
    {
        //January,1st, 1970
        if (IsValidISODateTime($EndTime,'1970-01-01 00:00:00',date('Y-m-d H:i:s')))
        {
            $Parameters['endTime'] = $EndTime;
        }
        else
        {
            ErrorLog('endTime must be a valid ISO datetime string (YYYY-MM-DD HH:MM:SS) between January 1st, 1970 and today. Aborting', E_USER_ERROR);
        }
    }
    else
    {
        ErrorLog('endTime is compulsory. Aborting', E_USER_ERROR);

        return FALSE;
    }

    //Check FROM is earlier than TO
    $LogicDates = ISODateTimeNoLaterThan($StartTime, $EndTime);
    if ($LogicDates === FALSE)
    {
        ErrorLog($StartTime.' shoud be the same or earlier than '.$EndTime.'. Aborting.', E_USER_ERROR);
    }

    //Check if both dates are no more than a month apart
    if (DatesSeparatedNoMoreThan($StartTime, $EndTime, 'Y-m-d H:i:s','1 month') === FALSE)
    {
        return FALSE;
    }

    //Validate meters
    $ValidMeters = array('PRODUCTION', 'CONSUMPTION', 'SELFCONSUMPTION', 'FEEDIN', 'PURCHASED');
    if (!empty($Meters))
    {
        $Size = count($Meters);
        if ($Size>5)
        {
            ErrorLog('You specified '.$Size.' meter categories and there are only five. Skipping.', E_USER_WARNING);
        }
        foreach ($Meters as $Key => $Value)
        {
            if (in_array($Value, $ValidMeters) === FALSE)
            {
                ErrorLog($Value.' is not a valid meter category ('.implode(',',$ValidMeters).'. Aborting.', E_USER_ERROR);
            }
        }

        $Parameters['meters'] = implode(',',$Meters);
    }

    //Now we have all the correct options
    $Result = cURLSimpleGET($URL, $Parameters);

    return $Result;
}

//EndPoints for Solar Edge Sensors API

/**
 * SESiteSensorsList
 * Returns a list of all the sensors in the site, and the device to which they are connected.
 * Method:      GET
 * Request:     Parameter   Type    Mandatory   Description
 *              siteId      Integer Yes         see $SiteId @param
 * Response:    Returns the list of sensors installed in the site associated with the gateway they are connected with. Each entry will include the following 
 *              parameters:
 *                  - connectedTo:  name of the gateway the sensor is connected to
 *                  - name:         the name of the sensor
 *                  - measurement:  what the sensor measures, e.g.: SensorGlobalHorizontalIrradiance, SensorDiffusedIrradiance, SensorAmbientTemperature
 *                  - type:         the sensor type e.g.: Temperature, Irradiance.
 * Example:     JSON output:
 *                  "SiteSensors": {
 *                      "count": 3,
 *                      "list": [{
 *                          "connectedTo": "Gateway 19",
 *                          "count": 3,
 *                          "sensors": [{
 *                              "name": "Global horizontal irradiance",
 *                              "measurement": "SensorGlobalHorizontalIrradiance",
 *                              "type": "IRRADIANCE"
 *                          },
 *                          {
 *                              "name": "Diffused irradiance",
 *                              "measurement": "SensorDiffusedIrradiance",
 *                              "type": "IRRADIANCE"
 *                          },
 *                          {
 *                              "name": "Ambient temperature",
 *                              "measurement": "SensorAmbientTemperature",
 *                              "type": "TEMPERATURE"
 *                          }]
 *                      }]
 *                  }
 * @param   string  $APIKey         The API KEY to use
 * @param   int     $SiteId         The site identifier
 * @return  mixed   The response from the API in JSON array format or FALSE if something fails
 *                      HTTPCode:   The HTTP Code given by the server to our request
 *                      Response:   The server response
 * @since 0.0.9
 * @todo
 * @see
 */
function SESiteSensorsList($APIKey, $SiteId)
{
    //SiteId validation
    if (!empty($SiteId))
    {
        if (IsValidInt($SiteId,1,NULL))
        {
            $URL = MIL_SEBASEURL.'equipment/'.$SiteId.'/sensors';
        }
        else
        {
            ErrorLog('siteId must be a positive integer. Aborting', E_USER_ERROR);

            return FALSE;
        }
    }

    $Parameters = array();

    //API KEY validation
    if (empty($APIKey))
    {
        ErrorLog('An API KEY is missing', E_USER_ERROR);

        return FALSE;
    }
    else
    {
        $Parameters['api_key'] = $APIKey;
    }

    //Now we have all the correct options
    $Result = cURLSimpleGET($URL, $Parameters);

    return $Result;
}

/**
 * SESiteSensorsData
 * Returns the data of all the sensors in the site, by the gateway they are connected to. This API is limited to one week period. This means that the period 
 * between endDate and startDate should not exceed one year or one month respectively. 
 * If the period is longer, the system will generate error 403
 * Method:      GET
 * Request:     Parameter   Type    Mandatory   Description
 *              siteId      Integer Yes         see $SiteId @param
 *              startDate   String  Yes         see $StartTime @param
 *              endDate     String  Yes         see $EndTime @param
 * Response:    Returns the telemetries reported by all sensors in the site, by the device they are connected to. Each entry will include the following 
 *              parameters:
 *                  - connectedTo:  name of the gateway the sensor is connected to
 *                  - count:        the number of telemetries
 *                  - date:         timestamp of the telemetries
 *                  - measurement:  (e.g. ambientTemperature) and its numerical value (metric system)
 * Example:     JSON output:
 *                  {
 *                      "siteSensors": {
 *                          "data": [{
 *                              "connectedTo": "Gateway 19",
 *                              "count": 0,
 *                              "telemetries": []
 *                          },
 *                          {
 *                              "connectedTo": "Gateway 1",
 *                              "count": 427,
 *                              "telemetries": [{
 *                              "date": "2015-06-15 13:00:00",
 *                              "ambientTemperature": -22.1155,
 *                              "moduleTemperature": 47.2601,
 *                              "windSpeed": 81.3652,
 *                              …]
 *                          }
 *                      }
 *                  }
 * @param   string  $APIKey         The API KEY to use
 * @param   int     $SiteId         The site identifier
 * @param   string  $StartTime      The power measured start time, ISO format (YYYY-MM-DD HH:MM:SS)
 * @param   string  $EndTime        The power measured end time, ISO format (YYYY-MM-DD HH:MM:SS)
 * @return  mixed   The response from the API in JSON array format or FALSE if something fails
 *                      HTTPCode:   The HTTP Code given by the server to our request
 *                      Response:   The server response
 * @since 0.0.9
 * @todo
 * @see
 */
function SESiteSensorsData($APIKey, $SiteId, $StartTime, $EndTime)
{
    $Parameters = array();

    //API KEY validation
    if (empty($APIKey))
    {
        ErrorLog('An API KEY is missing', E_USER_ERROR);

        return FALSE;
    }
    else
    {
        $Parameters['api_key'] = $APIKey;
    }

    //Parameters validation
    if (!empty($SiteId))
    {
        if (IsValidInt($SiteId,1,NULL))
        {
            $URL = MIL_SEBASEURL.'equipment/'.$SiteId.'/sensors';
        }
        else
        {
            ErrorLog('siteId must be a positive integer. Aborting', E_USER_ERROR);

            return FALSE;
        }
    }
    else
    {
        ErrorLog('siteId is compulsory. Aborting', E_USER_ERROR);

        return FALSE;
    }


    if (!empty($StartTime))
    {
        //January,1st, 1970
        if (IsValidISODateTime($StartTime,'1970-01-01 00:00:00',date('Y-m-d H:i:s')))
        {
            $Parameters['startTime'] = $StartTime;
        }
        else
        {
            ErrorLog('startTime must be a valid ISO datetime string (YYYY-MM-DD HH:MM:SS) between January 1st, 1970 and today. Aborting', E_USER_ERROR);
        }
    }
    else
    {
        ErrorLog('startTime is compulsory. Aborting', E_USER_ERROR);

        return FALSE;
    }

    if (!empty($EndTime))
    {
        //January,1st, 1970
        if (IsValidISODateTime($EndTime,'1970-01-01 00:00:00',date('Y-m-d H:i:s')))
        {
            $Parameters['endTime'] = $EndTime;
        }
        else
        {
            ErrorLog('endTime must be a valid ISO datetime string (YYYY-MM-DD HH:MM:SS) between January 1st, 1970 and today. Aborting', E_USER_ERROR);
        }
    }
    else
    {
        ErrorLog('endTime is compulsory. Aborting', E_USER_ERROR);

        return FALSE;
    }

    //Check FROM is earlier than TO
    $LogicDates = ISODateTimeNoLaterThan($StartTime, $EndTime);
    if ($LogicDates === FALSE)
    {
        ErrorLog($StartTime.' shoud be the same or earlier than '.$EndTime.'. Aborting.', E_USER_ERROR);
    }

    //Check if both dates are no more than a month apart
    if (DatesSeparatedNoMoreThan($StartTime, $EndTime, 'Y-m-d H:i:s','7 days') === FALSE)
    {
        return FALSE;
    }

    //Now we have all the correct options
    $Result = cURLFullGET($URL, $Parameters);

    return $Result;
}

//EndPoints for Solar Edge Versions API

/**
 * SEAPICurrentVersion
 * Returns the most updated version number in <major.minor.revision> format.
 * Method:      GET
 * Request:     No parameters
 * Response:    The current version
 * Example:     JSON output:
 *                  {"version":"1.0.0"}
 * @param   string  $APIKey         The API KEY to use
 * @return  mixed   The response from the API in JSON array format or FALSE if something fails
 *                      HTTPCode:   The HTTP Code given by the server to our request
 *                      Response:   The server response
 * @since 0.0.9
 * @todo incluir APIKEY como en el postman
 * @see
 */
function SEAPICurrentVersion($APIKey)
{
    $URL = MIL_SEBASEURL.'version/current';
    $Parameters = array();

    //API KEY validation
    if (empty($APIKey))
    {
        ErrorLog('An API KEY is missing', E_USER_ERROR);

        return FALSE;
    }
    else
    {
        $Parameters['api_key'] = $APIKey;
    }

    //Now we have all the correct options
    $Result = cURLSimpleGET($URL, $Parameters);

    return $Result;
}

/**
 * SEAPISupportedVersions
 * Returns a list of supported version numbers in <major.minor.revision> format.
 * Method:      GET
 * Request:     No parameters
 * Response:    A list of supported versions
 * Example:     JSON output:
 *                  {"supported":["0.9.5","1.0.0"]}
 * @param   string  $APIKey         The API KEY to use
 * @return  mixed   The response from the API in JSON array format or FALSE if something fails
 *                      HTTPCode:   The HTTP Code given by the server to our request
 *                      Response:   The server response
 * @since 0.0.9
 * @todo
 * @see
 */
function SEAPISupportedVersions($APIKey)
{
    $URL = MIL_SEBASEURL.'version/supported';

    //API KEY validation
    if (empty($APIKey))
    {
        ErrorLog('An API KEY is missing', E_USER_ERROR);

        return FALSE;
    }
    else
    {
        $Parameters['api_key'] = $APIKey;
    }

    //Now we have all the correct options
    $Result = cURLSimpleGET($URL, $Parameters);

    return $Result;
}






//Pending consulting https://sunrisesunset.io/api/
//Págiuna de status chula https://status.sunrisesunset.io/

//Datos sobre costes horarios
https://tarifaluzhora.es/ (scraping) o directamente en https://www.ree.es/es/apidatos
