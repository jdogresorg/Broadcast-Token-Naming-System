<?php
/*********************************************************************
 * rollback.php - Handles rolling back database updates safely
 ********************************************************************/
function btnsRollback($block_index=null){
    global $mysqli, $addresses, $tickers;

    $block_index = (int) $block_index;

    $tables = [
        'airdrops',
        'addresses',
        'batches',
        'blocks',
        'credits',
        'debits',
        'destroys',
        'fees',
        'issues',
        'lists',
        'mints',
        'sends',
        'tokens',
        'transactions'
    ];

    // Array to track transaction ids
    $transactions = array();
    $timer        = new Profiler();

    // Notify user of start of rollback
    print "Starting rollback to block {$block_index}...";

    // Loop through all database tables
    foreach($tables as $table){

        // Build out the correct SQL to pull data from the various tables
        $sql = false;

        // Credits / Debits
        if(in_array($table, array('credits','debits'))){
            $sql = "SELECT 
                        a.address, 
                        t2.tick
                    FROM 
                        {$table} t1, 
                        index_tickers t2,
                        index_addresses a
                    WHERE 
                        t2.id=t1.tick_id AND 
                        a.id=t1.address_id AND
                        t1.block_index>{$block_index}";
        }

        // AIRDROP / DESTROY
        if(in_array($table, array('airdrops','destroys'))){
            $sql = "SELECT 
                        t2.tick,
                        a.address
                    FROM 
                        {$table} t1, 
                        index_tickers t2,
                        index_addresses a
                    WHERE 
                        t2.id=t1.tick_id AND 
                        a.id=t1.source_id AND
                        t1.block_index>{$block_index}";
        }

        // MINT / SEND / FEE
        if(in_array($table, array('mints','sends','fees'))){
            $sql = "SELECT 
                        t2.tick,
                        a.address,
                        a2.address as address2
                    FROM 
                        {$table} t1 
                        LEFT JOIN index_addresses a2 on (t1.destination_id=a2.id),
                        index_tickers t2,
                        index_addresses a
                    WHERE 
                        t2.id=t1.tick_id AND 
                        a.id=t1.source_id AND
                        t1.block_index>{$block_index}";
        }

        // ISSUE
        if($table=='issues'){
            $sql = "SELECT 
                        t2.tick,
                        a.address,
                        a2.address as address2,
                        a3.address as address3
                    FROM 
                        {$table} t1 
                        LEFT JOIN index_addresses a2 on (t1.transfer_id=a2.id)
                        LEFT JOIN index_addresses a3 on (t1.transfer_supply_id=a3.id),
                        index_tickers t2,
                        index_addresses a
                    WHERE 
                        t2.id=t1.tick_id AND 
                        a.id=t1.source_id AND
                        t1.block_index>={$block_index}";
        }

        // Get list of transactions associated with the rollback blocks
        if($table=='transactions'){
            $sql = "SELECT 
                        tx_hash_id 
                    FROM 
                        transactions 
                    WHERE 
                        block_index>{$block_index}";
        }

        // Run the SQL query and populate the addresses, tickers, and transactions arrays
        if($sql){
            $results = $mysqli->query($sql);
            if($results){
                if($results->num_rows){
                    while($row = $results->fetch_assoc()){
                        $row = (object) $row;
                        if($table=='transactions'){
                            if(!in_array($row->tx_hash_id, $transactions))
                                array_push($transactions, $row->tx_hash_id);
                        } else {
                            addAddressTicker($row->address, $row->tick);
                            if(!is_null($row->address2))
                                addAddressTicker($row->address2, $row->tick);
                            if(!is_null($row->address3))
                                addAddressTicker($row->address3, $row->tick);
                        }
                    }
                }
            } else {
                byeLog("Error while trying to lookup rollback data in the {$table} table");
            }
        }

        // Delete data from rollback blocks
        $results = $mysqli->query("DELETE FROM {$table} m WHERE block_index>{$block_index}");
        if(!$results)
            byeLog("Error while trying to rollback {$table} table to block {$block_index}");
    }

    // Update address balances to get back to sane balances based on credits/debits
    updateBalances(array_keys($addresses), true);

    // Update token information
    updateTokens($tickers, true);

    // Delete items from list_{items,edits} tables
    deleteLists($transactions, true);

    // Report time to process block
    $time = $timer->finish();
    print " Done [{$time}sec]\n";

    // Notify user rollback is complete
    byeLog("Rollback to block {$block_index} complete.");
}

?>
