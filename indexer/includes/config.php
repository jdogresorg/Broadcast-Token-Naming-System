<?php
/*********************************************************************
 * config.php - Config info
 ********************************************************************/

// Network (mainnet/testnet)
$network = ($network!='mainnet') ? $network : 'mainnet';
define("NETWORK", $network);

// BTNS Indexer Version
define("VERSION_MAJOR", 0);
define("VERSION_MINOR", 14);
define("VERSION_REVISION",0);
define("VERSION_STRING", VERSION_MAJOR . '.' . VERSION_MINOR . '.' . VERSION_REVISION);

// TICK constants
define("MIN_TICK_LENGTH",1);
define("MAX_TICK_LENGTH",250);

// Reserved BTNS TICK names
$reserved = array('BTC','XCP');
define("RESERVED_TICKS",$reserved);

// Min/Max MAX_SUPPLY
define('MIN_TOKEN_SUPPLY',0.000000000000000001);
define('MAX_TOKEN_SUPPLY',1000000000000000000000);

// Min/Max DECIMALS
define('MIN_TOKEN_DECIMALS',0);
define('MAX_TOKEN_DECIMALS',18);

// Max DESCRIPTION length
define('MAX_TOKEN_DESCRIPTION',250);

// Mainnet config
if(NETWORK=='mainnet'){
    // First block with BTNS transaction
    define("FIRST_BLOCK",789742);

    // BTNS Addresses
    define('BURN_ADDRESS', "1Muhahahahhahahahahahhahahauxh9QX");
    define('GAS_ADDRESS', "1BTNSGASK5En7rFurDJ79LQ8CVYo2ecLC8");

    // Donation Addresses 
    define('DONATE_ADDRESS_1', "1BTNSGASK5En7rFurDJ79LQ8CVYo2ecLC8"); // Protocol Development
    define('DONATE_ADDRESS_2', "1BTNSGASK5En7rFurDJ79LQ8CVYo2ecLC8"); // Community Develoment
}

// Testnet config
if(NETWORK=='testnet'){
    // First block with BTNS transaction (none yet, so just picking block)
    define("FIRST_BLOCK",2473585);

    // BTNS Address 
    define('BURN_ADDRESS', "mvCounterpartyXXXXXXXXXXXXXXW24Hef");
    define('GAS_ADDRESS', "mvThcDEbeqog2aJ7JNj1FefUPaNdYYGqHt");

    // Donation Addresses 
    define('DONATE_ADDRESS_1', "mvThcDEbeqog2aJ7JNj1FefUPaNdYYGqHt"); // Protocol Development
    define('DONATE_ADDRESS_2', "mvThcDEbeqog2aJ7JNj1FefUPaNdYYGqHt"); // Community Develoment
}

// Database Credentials
require_once('db-config.php');

// General functions
require_once('functions.php');

// Tracks Execution Time
require_once('profiler.php');

// Ledger Management / Maintenance
require_once('rollback.php');
require_once('reparse.php');
require_once('compare.php');

// Protocol Changes / Activation blocks
require_once('protocol_changes.php');

// BTNS Actions
require_once('actions/address.php');
require_once('actions/airdrop.php');
require_once('actions/batch.php');
require_once('actions/bet.php');
require_once('actions/callback.php');
require_once('actions/destroy.php');
require_once('actions/dispenser.php');
require_once('actions/dividend.php');
require_once('actions/issue.php');
require_once('actions/list.php');
require_once('actions/mint.php');
require_once('actions/rug.php');
require_once('actions/sleep.php');
require_once('actions/send.php');
require_once('actions/sweep.php');

// Start runtime clock
$runtime = new Profiler();

?>
