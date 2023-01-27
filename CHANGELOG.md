# MinionLib Change Log
## Version 0.0.9.1 (2023-01-23)
- Solve array to string issue when validating cURL options
- Solve array to string issue when passing options to cURL
## Version 0.0.9 (2022-12-06)
- Configuration Functions:
  - CheckPHPVersion Checks if the runtime PHP version is the same or newer than the required one
  - CheckcURLVersion Checks if the runtime version is the same or newer than the required one.
  - FNAcURLVersion (F)irst (N)on (A)vailable cURL version. Checks if the runtime version is older than the FNA one.
  - CheckOpenSSLVersion Checks if the runtime version is the same or newer than the required one.
- Data Validation Functions:
  - ISODateNoEarlierThan checks that date is in ISO format (YYYY-MM-DD) and later or the same as a limit date
  - ISODateNoLaterThan checks that date is in ISO format (YYYY-MM-DD) and earlier or the same as a limit date
  - ISODateTimeNoEarlierThan checks that date is in ISO format (YYYY-MM-DD HH:MM:SS) and later or the same as a limit date
  - ISODateTimeNoLaterThan checks that date is in ISO format (YYYY-MM-DD HH:MM:SS) and earlier or the same as a limit date
  - IsValidBase64SHA256 validates a base64-encoded SHA256
  - IsValidHostname validates a RFC1123 hostname
  - IsValidHTTPMethod validates a string as a valid HTTP method
  - IsValidInt validates that it is really an Int and value is between Min and Max
  - IsValidIP validates an IPv4 or IPv6
  - IsValidIPv4 validates an IPv4
  - IsValidIPv6 validates an IPv6
  - IsValidISODate checks that date is in ISO format (YYYY-MM-DD), is valid, and within a range of dates
  - IsValidISODateTime hecks that datetime is in ISO format (YYYY-MM-DD HH:MM:SS), is valid, and within a range of datetimes
  - IsValidMD5 validates a MD5 hash
  - IsValidSHA256 validates a SHA256 hash
  - IsValidURI validates an URL/URI based on RFC3986
- Emailing:
  - Support for direct mailing using a procedural wrapper to PHPMailer
  - SimpleHTMLEmail sends a simple email with no images or attachments. Credential are taken from defined constants
  - SimpleHTMLEmailWC sends a simple email with no images or attachments. Credentials are passed as parameters
- Interconnection Functions:
  - cURLFileGET downloads a file to a destination
  - cURLFilePOST uploads a file to a destination using POST
  - cURLFilePUT uploads a file to a destination using PUT
  - cURLFullGET does a full HTTP GET operation via cURL
  - cURLFullHEAD does a full HTTP HEAD operation via cURL
  - cURLFullPOST does a full HTTP POST operation via cURL
  - cURLOptionConstantToLiteral turns standard int cURL option constants to option literals
  - cURLOptionsValidate validates the options set to make sure that follow what is stablished and the required PHP/cURL/OpenSSL versions are available
  - cURLSimpleFileGET downloads a file to a destination with fixed, known to work options
  - cURLSimpleFilePOST uploads a file to a destination using POST with fixed, known to work options
  - cURLSimpleFilePUT uploads a file to a destination using PUT with fixed, known to work options
  - cURLSimpleGET does s simple HTTP GET operation via cURL using fixed, known to work options
  - cURLSimpleHEAD does a simple HTTP HEAD operation via cURL using fixed, known to work options
  - cURLSimplePOST does a simple HTTP POST operation via cURL using fixed, known to work options
  - cURLWarn raises a warning on an undocumented/obsolete/deprecated cURL option
- Text and String Functions
  - MBPathInfo a multibyte-safe and cross-platform version of PHP pathinfo()
  - MimeType finds the mime type of a file represented by a physical path or a URL
- Renewables Monitoring (SolarEdge)
  - SEAPICurrentVersion returns the most updated version number in <major.minor.revision> format.
  - SEAPISupportedVersions returns a list of supported version numbers in <major.minor.revision> format
  - SEAccountsList returns a list of sites related to the given token, which is the account api_key
  - SEBulkSiteDataPeriod returns the energy production start and end dates of the site
  - SEBulkSiteEnergy returns the  energy measurements of a number of sites
  - SEBulkSiteEnergyTimePeriod returns the site total energy produced for a given period of a number of sites
  - SEBulkSiteOverview returns the overview data of a number of sites
  - SEBulkSitePower returns power measurements in 15 minutes resolution for a number of sites
  - SEInverterMeasures returns specific inverter data -measures- for a given timeframe
  - SESiteChangesLog returns a list of equipment component replacements ordered by date
  - SESiteComponents returns a list of inverters/SMIs in the specific site
  - SESiteDataPeriod returns the energy production start and end dates of the site
  - SESiteDetails returns the site details, such as name, location, status, etc
  - SESiteEnergy returns the site energy measurements
  - SESiteEnergyDetailed detailed site energy measurements from meters such as consumption, export (feed-in), import (purchase), etc
  - SESiteEnergyTimePeriod returns the site total energy produced for a given period
  - SESiteEnergyTimePeriodPerMeter returns for each meter on site its lifetime energy reading, metadata and the device to which it’s connected to
  - SESiteEnvironmentalBenefits returns all environmental benefits based on site energy production: CO2 emissions saved, equivalent trees planted, and light bulbs powered for a day
  - SESiteInventory returns the inventory of SolarEdge equipment in the site, including inverters/SMIs, batteries, meters, gateways and sensors
  - SESiteList returns a list of sites related to the given token, which is the account api_key
  - SESiteOverview returns the site overview data
  - SESitePower returns the site power measurements in 15 minutes resolution
  - SESitePowerDetailed returns the site power measurements in 15 minutes resolution
  - SESitePowerFlow retrieves the current power flow between all elements of the site including PV array, storage (battery), loads (consumption) and grid
  - SESiteSensorsData returns the data of all the sensors in the site, by the gateway they are connected to
  - SESiteSensorsList returns a list of all the sensors in the site, and the device to which they are connected.
  - SESiteStorageData gets detailed storage information from batteries: the state of energy, power and lifetime energy

## Version 0.0.8 (2022-05-09)
- Bug Fixes:
  - APCU2Array does not NOTICE on cache misses
  - ReadCache returns full structure on empty datasets
- Refactoring:
  - APCU2Array renamed to APCUToMYSMA
  - Array2APCU renamed to MYSMAToAPCU. Now, it just deals with metada-aware structures.
  - AssocToMySQLAssoc renamed to AssocToMYSMA
  - InsertFromMySQLDataStructure renamed to InsertFromMYSMADataStructure
  - IsMySQLAssocDataStructure falls back to testing 'Pure' MySQL Assoc Data Structure
  - MergeFromMySQLDataStructure renamed to MergeFromMYSMADataStructure
  - PersistCache now expects MYSMA structures
- Data Structure Functions:
  - MultiRecordNumericAssocToMYSMA converts a multi-record numeric+assoc array to a MYSMA Structure
  - SingleAssocToMYSMA converts a single ASSOC record to a MYSMA Structure
- Data Validation Functions:
  - IsMYSMADataStructure checks for the metadata-aware MySQL Assoc Data Structura that we use internally
- SQLDB Functions
  - MYSMAToTable writes a MYSMA Structure to database

## Version 0.0.7.1 (2022-05-08)
- Bug Fixes:
  - ReadCache filters semicolons on the filter condition.
  - ReadCache does not NOTICE and returns NULL when dealing with empty datasets
  - TableToAssoc returns full structure

## Version 0.0.7 (2022-05-07)
- Improvements on functions documentation
- Caching Functions
  - APCUDefaultsSetter sets the default config flags if APCU is running on the system
  - IsAPCURunning checks if APCU is enabled and returns TRUE if so, FALSE otherwise
  - APCUStatus returns APCU Status info
  - APCU2Array returns data from APCU cache
  - PersistCache reads from cache and stores in table using auxiliary functions
  - ReadCache tries cache first and if not reads from table and stores in cache
  - APCULastDBWrite checks the last time a cache object was persisted to DB
- Configuration Functions
  - Defaults creates constants. Defines if not defined
  - IniShorthand2Int translates shorthand notation to int value
- Data Structures Functions
  -AssocToMySQLAssoc converts a standard ASSOC array to a MySQl ASSOC Data Structure
- Data Validation Functions
  - IsAssocArray checks wether or not an array is associative
  - IsNumericArray checks wether or not an array is numeric
  - IsMultiArray checks if the array is single or multidimensional
  - IsMySQLAssocDataStructure checks if the array has the structure of a MySQL ASSOC Data Structure
- Date and Time Functions
  -HRLapse Calculates a lapse and returns it in human-readable format
  -MRLapse Calculates a lapse and returns it in machine-readable format
- SQLDB Functions
  - AssocToTable writes an associative array into a DB Table
  - GetMySQLTableMetadata gets  metadata from a MySQL table/resultset
  - InsertFromMySQlAssocDataStructure fills a table with data from an MySQL ASSOC style array
  - IsLegitMerge parses INSERT ...ON DUPLICATE KEY UPDATE sentence to make sure it is safe and sound
  - Merge merges info into the database
  - MergeFromMySQlAssocDataStructure merges the data form a MySQL ASSOC style array into a Table
  - MySQLTableMetadata gets the metadata on the fields included in the resultset
  - MySQLProperQuote quotes the field according to the data type
  - MySQLRSFlags explicits the MySQL flags that have been set
  - TableToArray Reads a table to an array, same structure as an ARRAY MySQL Query
  - TableToAssoc Reads a table to an array, same structure as an ASSOC MySQL Query
  - Truncate empties a table
- Text and String Functions
  - CloseCommaDelimitedList takes the last comma from the list and adds a closure char/string

## Version 0.0.6 (2022-04-18)
- Bug Fixes:
  - Caching connection objets cripples them, due to a serialization issue.
    Connection pool eliminated
- New versions of the following functions:
  - RegisterDBSystem
  - DBSystemSanityCheck
  - RegisterMySQLConnection
- SQLDBFunctions
  - IsLegitUpdate parses UPDATE sentence to make sure it is safe and sound
  - IsLegitInsert parses INSERT sentence to make sure it is safe and sound
  - IsLegitDelete parses DELETE sentence to make sure it is safe and sound
  - StripValuesFromQuery takes out values than can contain SQL Operators
- Mathematical Functions
  - IsEven validates if the number is even
  - IsOdd validates if the number is odd

## Version 0.0.5 (2022-04-08)
- SQLDB Functions
  - IsLegitRead parses SELECT sentence to make sure it is safe and sound
- Bug Fixes:
  - Read makes use of a fixed-size pool instead of a define-controlled one

## Version 0.0.4 (2022-03-20)
- SQLDB Functions
  - APCU-aware RegisterMySQLConnection
  - APCU-aware RegisterDBSystem
  - TestExistingPool avoids creating redundant pools on concurrent ussage
  - APCU-aware TestResurrectConnection
  - APCU-aware Read
  - APCU-aware Update
  - APCU-aware Insert
  - Delete: deletes info from the database

## Version 0.0.3 (2022-01-30)
- Fix php-cs config
- Data Validation Functions
  - IsAdequateDatabasePort: checks if port is OK for holding a SQL Database main port
  - IsValidCharset: validates Database Character Sets
  - IsValidIANAPort: checks if it falls in the valid range 0-65535
  - IsValidMySQLName: checks if the database object name(table, column, view, alias...) is valid
  - IsValidMySQLObjectName: helper function for IsValidMySQLName
  - IsValidMySQLUser: checks if the database user is valid
- SQLDB Functions
  - Read: gets info from the database in form of associative array or JSON
  - Update: changes info on the database
  - Insert: inserts info into the database
  - MySQLInit: performs connection initializacion
  - MySQLOptions: sets connection options
  - MySQLRealConnect: performs connection to Database
  - DBSystemSanityCheck: checks that all needed configuration is in place
  - Reconnect: connects to a previously sanitized standard or persistent connection
  - RegisterDBSystem: registers a DB Connection
  - RegisterMySQLConnection: front-end to register a connection to MySQL
  - TestResurrectConnection: checks connection health and reconnects if necessary

## Version 0.0.2 (2022-01-27)
- Roadmap info
- Composer integration

## Version 0.0.1 (2022-01-27)
- Configuration Functions
  - ErrorConstantToLiteral: turns standard int error constants to error literals
- Data Plugs Functions
  - GetIanaTLDs: Extracts a current list of TLDs from IANA's website
- Data Sets
  - Up to data JSON lists of IANA port database
- Data Validation Functions
  - IsValidHost validates a hostname, IPv4 or IPv6
- Error Handler
  - Error logging to file
- Text and String Functions
  - IsValidUTF8: validates if the text complies with UTF-8
  - Enclosure: detects if the string is enclosed by something

