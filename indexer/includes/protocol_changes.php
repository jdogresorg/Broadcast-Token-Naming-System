<?php
/*********************************************************************
 * protocol_changes.php - Tracks protocol changes and activation blocks
 ********************************************************************/

// Temp placeholder for associative array of protocol changes
$changes = array(
    // FORMAT
    // 'NAME' => array($version_major, $version_minor, $version_revision, $mainnet_block_index, $testnet_block_index),

    // Define `ACTION` commands and `ACTIVATION_BLOCK` for each (ALL UPPER case)
    // BTNS-420 SPEC defines when things are ACTUALLY activated
    // active here just means active for testing / debugging
    'ADDRESS'   => array(0,  10,   0,     789742,       2580955),
    'AIRDROP'   => array(0,  10,   0,     789742,       2581842),
    'BATCH'     => array(0,  10,   0,     789742,       2581531),
    'BET'       => array(0,  10,   0,     9999999,      999999999),
    'CALLBACK'  => array(0,  10,   0,     9999999,      999999999),
    'DESTROY'   => array(0,  10,   0,     789742,       2473585),
    'DISPENSER' => array(0,  10,   0,     9999999,      999999999),
    'DIVIDEND'  => array(0,  10,   0,     9999999,      999999999),
    'ISSUE'     => array(0,  10,   0,     789742,       2473585),
    'LIST'      => array(0,  10,   0,     789742,       2473585),
    'MINT'      => array(0,  10,   0,     789742,       2473585),
    'RUG'       => array(0,  10,   0,     9999999,      999999999),
    'SEND'      => array(0,  10,   0,     789742,       2473585),
    'SLEEP'     => array(0,  10,   0,     9999999,      999999999),
    'SWEEP'     => array(0,  10,   0,     9999999,      999999999),

    // Define protocol changes (ALL LOWER Case)
    // 'name' => array(0,  10,   0,     0,      0),
);

// Loop through changes, validate formats, die on any errors
$protocol_changes = array();
foreach($changes as $name => $info){
    $status = addProtocolChange($name, $info[0], $info[1], $info[2], $info[3], $info[4]);
    if(gettype($status)=='string')
        byeLog("PROTOCOL_CHANGE ERROR: {$name} - {$status}");
}

// Define PROTOCOL_CHANGES constant 
define("PROTOCOL_CHANGES", $protocol_changes);

// Examples
// 
// Validate that feature is active
// isEnabled($name=null, $network=null, $block_index=null);
// $enabled = isEnabled('AIRDROP','mainnet', 123456);

?>
