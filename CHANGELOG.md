# MinionLib Change Log
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

