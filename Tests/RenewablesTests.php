<?php

/*
 * RenewablesTests
 * To test Renewables functions 
 * @author ProceduralMan <proceduralman@gmail.com>
 * @copyright 2021
 * @version 1.0 initial version
 * @package Minion Library
 * @todo
 */

//Include the registered APIKEY or it will fail otherwise
define("APIKEY", "<APIKEYHERE>");

require_once __DIR__.'/../MinionSetup.php';

//Site List
echo '1.- Site List'.PHP_EOL;

$Result = SESiteList(APIKEY, 100, 0, 'parac', 'Name', 'ASC', 'All');

echo 'Calling SESiteList returns '.$Result.PHP_EOL.PHP_EOL;

$SiteData = json_decode($Result, TRUE);
$Response = json_decode($SiteData['Response'], TRUE);
$SiteId = $Response['sites']['site'][0]['id'];

echo 'Got Site Id:'.$SiteId.PHP_EOL.PHP_EOL;

//Site Details
echo '2.- Site Details'.PHP_EOL;

$Result2 = SESiteDetails(APIKEY, $SiteId);

echo 'Calling SESiteDetails returns '.$Result2.PHP_EOL.PHP_EOL;

//Site Data: Start and End Dates
echo '3.- Site Data Period'.PHP_EOL;

$Result3 = SESiteDataPeriod(APIKEY, $SiteId);

echo 'Calling SESiteDataPeriod returns '.$Result3.PHP_EOL.PHP_EOL;

//Site Data: Start and End Dates, Bulk Version
echo '4.- Bulk Site Data Period'.PHP_EOL;
$SiteIds = array($SiteId, $SiteId);
//print_r($SiteIds);
$Result4 = SEBulkSiteDataPeriod(APIKEY, $SiteIds);

echo 'Calling SEBulkSiteDataPeriod returns '.$Result4.PHP_EOL.PHP_EOL;

//die();

//Site Energy
echo '5.- Site Energy'.PHP_EOL;

$Result5 = SESiteEnergy(APIKEY, $SiteId, '2021-12-22', '2021-12-22', 'HOUR');

echo 'Calling SESiteEnergy returns '.$Result5.PHP_EOL.PHP_EOL;

//Site Energy, Bulk Version
echo '6.- Bulk Site Energy'.PHP_EOL;

$Result6 = SEBulkSiteEnergy(APIKEY, $SiteIds, '2022-07-03', '2022-07-04', 'HOUR');

echo 'Calling SEBulkSiteEnergy returns '.$Result6.PHP_EOL.PHP_EOL;

//Site Energy – Time Period
echo '7.- Site Energy Time Period'.PHP_EOL;

$Result7 = SESiteEnergyTimePeriod(APIKEY, $SiteId, '2022-07-03', '2022-07-04');

echo 'Calling SESiteEnergyTimePeriod returns '.$Result7.PHP_EOL.PHP_EOL;

//Site Energy Time Period, Bulk Version
echo '8.- Bulk Site Energy Time Period'.PHP_EOL;

$Result8 = SEBulkSiteEnergyTimePeriod(APIKEY, $SiteIds, '2022-07-03', '2022-07-04');

echo 'Calling SEBulkSiteEnergyTimePeriod returns '.$Result8.PHP_EOL.PHP_EOL;

//Site Power
echo '9.- Site Power'.PHP_EOL;

$Result9 = SESitePower(APIKEY, $SiteId, '2022-07-03 08:00:00', '2022-07-04 20:00:00');

echo 'Calling SESitePower returns '.$Result9.PHP_EOL.PHP_EOL;

//Site Power Bulk Version
echo '10.- Bulk Site Power'.PHP_EOL;

$Result10 = SEBulkSitePower(APIKEY, $SiteIds, '2022-07-03 08:00:00', '2022-07-04 20:00:00');

echo 'Calling SEBulkSitePower returns '.$Result10.PHP_EOL.PHP_EOL;

//Site Overview
echo '11.- Site Overview'.PHP_EOL;

$Result11 = SESiteOverview(APIKEY, $SiteId);

echo 'Calling SESiteOverview returns '.$Result11.PHP_EOL.PHP_EOL;

//Site Overview Bulk Version
echo '12.- Bulk Site Overview'.PHP_EOL;

$Result12 = SEBulkSiteOverview(APIKEY, $SiteIds);

echo 'Calling SEBulkSiteOverview returns '.$Result12.PHP_EOL.PHP_EOL;

//Site Power Detailed
echo '13.- Site Power Detailed'.PHP_EOL;

$Result13 = SESitePowerDetailed(APIKEY, $SiteId, '2022-07-03 14:00:00', '2022-07-04 15:00:00', NULL);

echo 'Calling SESitePowerDetailed returns '.$Result13.PHP_EOL.PHP_EOL;

//Site Energy - Detailed
echo '14.- Site Energy - Detailed'.PHP_EOL;

$Result14 = SESiteEnergyDetailed(APIKEY, $SiteId, '2022-07-03 14:00:00', '2022-07-04 15:00:00', 'HOUR', NULL);

echo 'Calling SESiteEnergyDetailed returns '.$Result14.PHP_EOL.PHP_EOL;

//Site Power Flow
echo '15.- Site Power Flow'.PHP_EOL;

$Result15 = SESitePowerFlow(APIKEY, $SiteId);

echo 'Calling SESitePowerFlow returns '.$Result15.PHP_EOL.PHP_EOL;

//Storage Information
echo '16.- Storage Information'.PHP_EOL;

$Result16 = SESiteStorageData(APIKEY, $SiteId, '2022-07-03 14:00:00', '2022-07-04 15:00:00', NULL);

echo 'Calling SESiteStorageData returns '.$Result16.PHP_EOL.PHP_EOL;

//Site Environmental Benefits
echo '17.- Site Environmental Benefits'.PHP_EOL;

$SysUnits = 'Metrics'; //ALTERNATIVE: 'Imperial';
$Result17 = SESiteEnvironmentalBenefits(APIKEY, $SiteId, $SysUnits);

echo 'Calling SESiteEnvironmentalBenefits returns '.$Result17.PHP_EOL.PHP_EOL;

//Site components
echo '18.- Site Components'.PHP_EOL;

$Result18 = SESiteComponents(APIKEY, $SiteId);

echo 'Calling SESiteComponents returns '.$Result18.PHP_EOL.PHP_EOL;

//Site Inventory
echo '19.- Site Inventory'.PHP_EOL;

$Result19 = SESiteInventory(APIKEY, $SiteId);

echo 'Calling SESiteInventory returns '.$Result19.PHP_EOL.PHP_EOL;

$SiteInventoryData = json_decode($Result19, TRUE);
$InvResponse = json_decode($SiteInventoryData['Response'], TRUE);
$SN = $InvResponse['Inventory']['inverters'][0]['SN'];
echo 'Got inverter S/N:'.$SN.PHP_EOL.PHP_EOL;

//Inverter Measures
echo '20.- Inverter Measures'.PHP_EOL;

$Result20 = SEInverterMeasures(APIKEY, $SiteId, $SN, '2022-07-03 14:00:00', '2022-07-04 15:00:00');

echo 'Calling SEInverterMeasures returns '.$Result20.PHP_EOL.PHP_EOL;

//Changes Log
echo '21.- Changes Log'.PHP_EOL;

$Result21 = SESiteChangesLog(APIKEY, $SiteId, $SN);

echo 'Calling SESiteChangesLog returns '.$Result21.PHP_EOL.PHP_EOL;

//Accounts List
echo '22.- Accounts List'.PHP_EOL;

$Result22 = SEAccountsList(APIKEY, 100, 0, 'Parac', 'City', 'ASC');

echo 'Calling SEAccountsList returns '.$Result22.PHP_EOL.PHP_EOL;

//Site Energy Per Meter
echo '23.- Site Energy Per Meter'.PHP_EOL;

$Result23 = SESiteEnergyTimePeriodPerMeter(APIKEY, $SiteId, 'DAY', '2022-07-03 14:00:00', '2022-07-04 15:00:00', NULL);

echo 'Calling SESiteEnergyTimePeriodPerMeter returns '.$Result23.PHP_EOL.PHP_EOL;

//Sensors List
echo '24.- Sensors List'.PHP_EOL;

$Result24 = SESiteSensorsList(APIKEY, $SiteId);

echo 'Calling SESiteSensorsList returns '.$Result24.PHP_EOL.PHP_EOL;

//Sensors Data
echo '25.- Sensors Data'.PHP_EOL;

$Result25 = SESiteSensorsData(APIKEY, $SiteId, '2022-07-03 14:00:00', '2022-07-04 15:00:00');

echo 'Calling SESiteSensorsData returns '.$Result25.PHP_EOL.PHP_EOL;

//API Current Version
echo '26.- API Current Version'.PHP_EOL;

$Result26 = SEAPICurrentVersion(APIKEY);

echo 'Calling SEAPICurrentVersion returns '.$Result26.PHP_EOL.PHP_EOL;

//API Supported Versions
echo '27.- API Supported Versions'.PHP_EOL;

$Result27 = SEAPISupportedVersions(APIKEY);

echo 'Calling SEAPICurrentVersion returns '.$Result27.PHP_EOL;

exit(0);
