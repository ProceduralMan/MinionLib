# MinionLib Change Log
## Version 0.0.6
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

## Version 0.0.5
- SQLDB Functions
  - IsLegitRead parses SELECT sentence to make sure it is safe and sound
- Bug Fixes:
  - Read makes use of a fixed-size pool instead of a define-controlled one

## Version 0.0.4
- SQLDB Functions
  - APCU-aware RegisterMySQLConnection
  - APCU-aware RegisterDBSystem
  - TestExistingPool avoids creating redundant pools on concurrent ussage
  - APCU-aware TestResurrectConnection
  - APCU-aware Read
  - APCU-aware Update
  - APCU-aware Insert
  - Delete: deletes info from the database

## Version 0.0.3
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

## Version 0.0.2
- Roadmap info
- Composer integration

## Version 0.0.1
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

