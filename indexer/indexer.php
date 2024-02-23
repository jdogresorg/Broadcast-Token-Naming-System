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
$network  = ($testnet) ? 'testnet' : 'mainnet';
$rollback = (is_numeric($args['rollback'])) ? intval($args['rollback']) : false;
$service  = 'counterparty'; // counterparty / dogeparty

// Define some constants used for locking processes and logging errors
define("LOCKFILE", '/var/tmp/btns-indexer-' . $service . '-' . $network . '.lock');
define("LASTFILE", '/var/tmp/btns-indexer-' . $service . '-' . $network . '.last-block');
define("ERRORLOG", '/var/tmp/btns-indexer-' . $service . '-' . $network . '.errors');

// Load config (only after $network is defined)
require_once('includes/config.php');

// Print indexer version number so it shows up in debug logs
print "BTNS Indexer v" . VERSION_STRING . "\n";

// Set database name from global var CP_DATA 
$dbase = CP_DATA; 

// Initialize the database connection
initDB();

// Create a lock file, and bail if we detect an instance is already running
createLockFile();

// Handle rollbacks
if($rollback)
    btnsRollback($rollback);

// If no block given, load last block from state file, or use first block with BTNX tx
if(!$block){
    $last  = file_get_contents(LASTFILE);
    $first = FIRST_BLOCK; // First block a BTNS transaction is seen
    $block = (isset($last) && $last>=$first) ? (intval($last) + 1) : $first;
}

// Get the current block index from status info
$results = $mysqli->query("SELECT block_index FROM {$dbase}.blocks ORDER BY block_index DESC limit 1");
if($results){
    $row     = (object) $results->fetch_assoc();
    $current = $row->block_index;
} else {
    byeLog('Error while trying to lookup current block');
}

// Check to make sure cp2mysql is not running and parsing in block data
// Prevents issue where tokens might be missed because we are still in middle of parsing in a block
$lockfile = '/var/tmp/' . $service . '2mysql-' . $network . '.lock';
if(file_exists($lockfile))
    byeLog("found {$service} parsing a block... exiting");

// Loop through the blocks until we are current
while($block <= $current){
    $timer = new Profiler();
    print "processing block {$block}...";

    // Lookup any BTNS action broadcasts in this block (anything with bt: or btns: prefix)
    $sql = "SELECT
                b.text,
                b.value as version,
                t.hash as tx_hash,
                a.address as source,
                b.block_index as block_index
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
            ORDER BY b.tx_index ASC";
    $results = $mysqli->query($sql);
    if($results){
        if($results->num_rows)
            while($row = $results->fetch_assoc())
                processTransaction($row);
    } else {
        byeLog("Error while trying to lookup BTNS broadcasts");
    }

    // Create record in `blocks` table with hashes of the credits/debits/transactions tables
    createBlock($block);

    // Do a sanity check to verify that token supplys match data in credits/debits/balances tables 
    sanityCheck($block);

    // Report time to process block
    $time = $timer->finish();
    print " Done [{$time}sec]\n";

    // Bail out if user only wants to process one block
    if($single){
        print "detected single block... bailing out\n";
        break;
    } else {
        // Save block# to state file (so we can resume from this block next run)
        if($block>$last)
            file_put_contents(LASTFILE, $block);
    }

    // Increase block before next loop
    $block++;
}    

// Remove the lockfile now that we are done running
removeLockFile();

print "Total Execution time: " . $runtime->finish() ." seconds\n";
