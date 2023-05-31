<?php
/*********************************************************************
 * config.php - Config info / Credentials
 ********************************************************************/

/* 
 * Database Config
 */

// Mainnet config
if($runtype=='mainnet'){
    define("DB_HOST", "localhost");
    define("DB_USER", "mysql_username");
    define("DB_PASS", "mysql_password");
    define("DB_DATA", "BTNS_Counterparty"); // Database where BTNS data is stored
    define("CP_DATA", "Counterparty");      // Database where Counterparty data is stored 
}

// Testnet config
if($runtype=='testnet'){
    define("DB_HOST", "localhost");
    define("DB_USER", "mysql_username");
    define("DB_PASS", "mysql_password");
    define("DB_DATA", "BTNS_Counterparty_Testnet"); // Database where BTNS data is stored
    define("CP_DATA", "Counterparty_Testnet");      // Database where Counterparty data is stored 
}

// BTNS Indexer Version
define("VERSION_MAJOR", 0);
define("VERSION_MINOR", 10);
define("VERSION_REVISION",0);
define("VERSION_STRING", VERSION_MAJOR + '.' + VERSION_MINOR + '.' + VERSION_REVISION)

// First block with BTNS transaction
define("FIRST_BLOCK",789742);

// General functions
require_once('functions.php');

// Tracks Execution Time
require_once('profiler.php');

// Protocol Changes / Activation blocks
require_once('protocol_changes.php');

// BTNS Actions
require_once('actions/airdrops.php');
require_once('actions/batches.php');
require_once('actions/bets.php');
require_once('actions/callbacks.php');
require_once('actions/destroys.php');
require_once('actions/dispensers.php');
require_once('actions/dividends.php');
require_once('actions/issuances.php');
require_once('actions/lists.php');
require_once('actions/mints.php');
require_once('actions/rugs.php');
require_once('actions/sleeps.php');
require_once('actions/sends.php');
require_once('actions/sweeps.php');

// Start runtime clock
$runtime = new Profiler();

?>
