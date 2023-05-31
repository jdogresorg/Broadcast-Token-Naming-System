#!/usr/bin/env php
<?php
/*********************************************************************
 * Broadcast Token Name System (BTNS-420) Indexer
 *
 * DISCLAIMER: 
 *
 * BTNS-420 is a bleeding-edge experimental protocol to play around 
 * with token functionality on Bitcoin and Counterparty. This is a 
 * hobby project, and I am NOT responsible for any losses, financial 
 * or otherwise, incurred from using this experimental protocol and 
 * its functionality.
 * 
 * BTNS Transactions that are considered valid one day may be 
 * considered invalid the next day after further review.
 *
 * Science is messy sometimes... DO NOT put in funds your not willing to lose!
 * 
 * If you use this, please donate: 1BTNS42oqp1vzLTfBzHQGPhfvQdi2UjoEc
 *
 * Author: Jeremy Johnson (J-Dog) <j-dog@j-dog.net>
 * 
 * Command line arguments :
 * --testnet    Load data from testnet
 * --block=#    Load data for given block
 * --rollback=# Rollback data to a given block
 * --single     Load single block
 ********************************************************************/

// Hide all but errors and parse issues
error_reporting(E_ERROR|E_PARSE);

// Parse in any command line args and set basic runtime flags
$args     = getopt("", array("testnet::", "block::", "single::", "rollback::",));
$testnet  = (isset($args['testnet'])) ? true : false;
$single   = (isset($args['single'])) ? true : false;  
$block    = (is_numeric($args['block'])) ? intval($args['block']) : false;
$runtype  = ($testnet) ? 'testnet' : 'mainnet';
$rollback = (is_numeric($args['rollback'])) ? intval($args['rollback']) : false;

// Load config (only after runtype is defined)
require_once('includes/config.php');

// Set database name from global var CP_DATA 
$dbase = CP_DATA; 

// Define some constants used for locking processes and logging errors
define("LOCKFILE", '/var/tmp/btns-indexer-' . $runtype . '.lock');
define("LASTFILE", '/var/tmp/btns-indexer-' . $runtype . '.last-block');
define("ERRORLOG", '/var/tmp/btns-indexer-' . $runtype . '.errors');

// Initialize the database connection
initDB();

// Create a lock file, and bail if we detect an instance is already running
// createLockFile();

// Handle rollbacks
if($rollback){
    $block_index = $mysqli->real_escape_string($rollback);
    $tables = [
        'blocks',
        'credits',
        'debits',
        'issuances',
        'mints',
        'sends',
        'tokens',
        'transactions'
    ];
    foreach($tables as $table){
        $results = $mysqli->query("DELETE FROM {$table} WHERE block_index>{$block_index}");
        if(!$results)
            byeLog("Error while trying to rollback {$table} table to block {$block_index}");
    }
    // Add code here update balances table using credits/debits to get back to sane balances after rollback
    // ... coming soon
    byeLog("Rollback to block {$block_index} complete.");
}

// Define the ACTIVATION_BLOCK for each ACTION
$active = array(
    'AIRDROP'   => 0,
    'BATCH'     => 0,
    'BET'       => 0,
    'CALLBACK'  => 0,
    'DESTROY'   => 0,
    'DISPENSER' => 0,
    'DIVIDEND'  => 0,
    'ISSUE'     => 0,
    'LIST'      => 0,
    'MINT'      => 0,
    'RUG'       => 0,
    'SEND'      => 0,
    'SLEEP'     => 0,
    'SWEEP'     => 0
);


// If no block given, load last block from state file, or use first block with BTNX tx
if(!$block){
    $last  = file_get_contents(LASTFILE);
    $first = FIRST_BLOCK; // First block a BTNS transaction is seen
    $block = (isset($last) && $last>=$first) ? (intval($last) + 1) : $first;
}

// Get the current block index from status info
$sql = "SELECT block_index FROM {$dbase}.blocks ORDER BY block_index DESC limit 1";
$results = $mysqli->query($sql);
if($results){
    $row = $results->fetch_assoc();
    $current = $row['block_index'];
} else {
    byeLog('Error while trying to lookup current block');
}

// Check to make sure cp2mysql is not running and parsing in block data
// Prevents issue where tokens might be missed because we are still in middle of parsing in a block
$service  = 'counterparty'; // counterparty / dogeparty
$lockfile = '/var/tmp/' . $service . '2mysql-' . $runtype . '.lock';
if(file_exists($lockfile)){
    removeLockFile();
    bye("found {$service} parsing a block... exiting");
}

// Loop through the blocks until we are current
while($block <= $current){
    $timer = new Profiler();
    print "processing block {$block}...";

    // Lookup any BTNS action broadcasts in this block (anything with bt: or btns: prefix)
    $sql = "SELECT
                b.text,
                b.value as version,
                t.hash as tx_hash,
                a.address as source
            FROM
                {$dbase}.broadcasts b,
                {$dbase}.index_transactions t,
                {$dbase}.index_addresses a
            WHERE 
                t.id=b.tx_hash_id AND
                a.id=b.source_id AND
                b.block_index='{$block}' AND
                b.status='valid' AND
                (b.text LIKE 'bt:%' OR b.text LIKE 'btns:%')
                -- AND b.text LIKE '%BRRR%'
            ORDER BY b.tx_index ASC";
    $results = $mysqli->query($sql);
    if($results){
        if($results->num_rows){
            while($row = $results->fetch_assoc()){
                $row = (object) $row;
                $version   = $row->version;
                // Define list of allowable actions
                $actions   = array('DEPLOY','ISSUE','MINT','TRANSFER','SEND');
                $prefixes  = array('/^bt:/','/^btns:/');
                $params    = explode('|',preg_replace($prefixes,'',$row->text));
                // Trim whitespace from any params
                foreach($params as $idx => $value)
                    $params[$idx] = trim($value);
                $action    = strtoupper($params[0]); // First param is always action
                $ticker    = $params[1];             // Second param is always ticker (TICK)
                $source    = $row->source;
                $error     = false;
                $addresses = array();    // Assoc array to hold address list (track addresses with changes)
                $tickers   = array();    // Assoc array to hold ticker list (track tick changes)
                $addresses[$source] = 1; // Add source address to list of addresses

                // Decode any base64 tickers
                if(strlen($ticker)>5 && isBase64($ticker))
                    $ticker = base64_decode($ticker);

                // Add tick to list of tickers (used to update all tickers touched by an ACTION)
                $tickers[$ticker] = 1; 

                // Create database records and get ids for ticker, tx_hash, and source address
                $source_id  = createAddress($row->source);
                $tx_hash_id = createTransaction($row->tx_hash);

                $tick_id    = createTicker($ticker);

                // Define basic BTNS transaction data object
                $data = (object) array(
                    'ACTION'      => $action,       // Action (ISSUE, MINT, SEND, etc)
                    'BLOCK_INDEX' => $block,        // Block index 
                    'SOURCE'      => $row->source,  // Source/Broadcasting address
                    'TX_HASH'     => $row->tx_hash  // Transaction Hash
                );

                // Create a record of this transaction in the transactions table
                createTxIndex($data);

                // Support old BRC20/SRC20 actions 
                if($action=='TRANSFER') $action = 'SEND';
                if($action=='DEPLOY')   $action = 'ISSUE';

                // Validate Action
                if(!in_array($action,$actions))
                    $error = 'invalid: unknown BTNS action';

                // Verify action is activated (past ACTIVATION_BLOCK)
                if(!$active[$action])
                    $error = 'invalid: ACTIVATION_BLOCK';

                // Handle processing the specific BTNS ACTION commands
                // btnsAction($action, $params, $data, $error);
            }

            // Handle updating balances for any addresses used in this block (probably unneccessary since we update balances as we parse)
            // updateBalances($addresses);

            // Handle updating token data (amount minted, etc)
            updateTokens($tickers);
        }
    } else {
        byeLog("Error while trying to lookup BTNS broadcasts");
    }

    // Create hash of the credits/debits/balances table and create record in `blocks` table
    createBlock($block);

    // Report time to process block
    $time = $timer->finish();
    print " Done [{$time}ms]\n";

    // Bail out if user only wants to process one block
    if($single){
        print "detected single block... bailing out\n";
        break;
    } else {
        // Save block# to state file (so we can resume from this block next run)
        // file_put_contents(LASTFILE, $block);
    }

    // Increase block before next loop
    $block++;
}    

// Remove the lockfile now that we are done running
removeLockFile();

print "Total Execution time: " . $runtime->finish() ." seconds\n";
