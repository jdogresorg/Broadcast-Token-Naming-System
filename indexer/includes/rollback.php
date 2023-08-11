<?php
/*********************************************************************
 * rollback.php - Handles rolling back database updates safely
 ********************************************************************/
function btnsRollback($block_index=null){
    global $mysqli;

    $block_index = (int) $block_index;

    $tables = [
        'blocks',
        'credits',
        'debits',
        'issues',
        'lists',
        'mints',
        'sends',
        'tokens',
        'transactions'
    ];

    // Arrays to track address/tick/transaction ids
    $addresses    = array();
    $tickers      = array();
    $transactions = array();

    // Loop through all database tables
    foreach($tables as $table){

        // Get list of any addresses or tickers associated with the rollback blocks
        if(in_array($table, array('credits','debits'))){
            $results = $mysqli->query("SELECT address_id, tick_id FROM {$table} WHERE block_index>{$block_index}");
            if($results){
                if($results->num_rows){
                    while($row = $results->fetch_assoc()){
                        $row = (object) $row;
                        if(!in_array($row->address_id, $addresses))
                            array_push($addresses, $row->address_id);
                        if(!in_array($row->tick_id, $tickers))
                            array_push($tickers, $row->tick_id);
                    }
                }
            } else {
                byeLog('Error while trying to lookup rollback data in the ' . $table);
            }
        }

        // Get list of transactions associated with the rollback blocks
        if($table=='transactions'){
            $results = $mysqli->query("SELECT tx_hash_id FROM transactions WHERE block_index>{$block_index}");
            if($results){
                if($results->num_rows){
                    while($row = $results->fetch_assoc()){
                        $row = (object) $row;
                        if(!in_array($row->tx_hash_id, $transactions))
                            array_push($transactions, $row->tx_hash_id);
                    }
                }
            }
        }

        // Delete data from rollback blocks
        $results = $mysqli->query("DELETE FROM {$table} m WHERE block_index>{$block_index}");
        if(!$results)
            byeLog("Error while trying to rollback {$table} table to block {$block_index}");
    }

    // Update address balances to get back to sane balances based on credits/debits
    updateBalances($addresses, true);

    // Update token information
    updateTokens($tickers, true);

    // Delete items from list_{items,edits} tables
    deleteLists($transactions, true);

    // Notify user rollback is complete
    byeLog("Rollback to block {$block_index} complete.");
}

?>
