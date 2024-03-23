<?php
/*********************************************************************
 * reparse.php - Handles reparsing the ledger to re-validate all txs
 ********************************************************************/
function btnsReparse($block_index=null, $start_index=null){
    global $mysqli, $runtime;

    // Placeholder for any error message
    $error = false;

    // Notify user of start of reparse
    print "Reparsing transactions...\n";

    // Start reparsing transactions at the first block or the given start_index
    $block   = (isset($start_index) && is_numeric($start_index)) ? $start_index : FIRST_BLOCK;
    $current = $block_index;

    // Loop through the blocks until we are current
    while($block <= $current){
        $timer = new Profiler();
        print "reparsing block {$block}...";

        // Dump ledger info BEFORE reparsing data
        $ledgerA = getBlockDataHashes($block);

        // Get and process any transactions in the block
        $txs = getBroadcastTransactions($block);
        foreach($txs as $tx)
            processTransaction($tx);

        // Dump ledger info AFTER reparsing data
        $ledgerB = getBlockDataHashes($block);

        // Hash both ledgers for quick comparison
        $hashA = getDataHash($ledgerA);
        $hashB = getDataHash($ledgerB);

        // If we detect a ledger mismatch, report as much detail on the issue as possible for debugging purposes
        if($hashA!=$hashB){
            $error = "ERROR: Found ledger difference at block {$block}";
            foreach($ledgerA as $table => $info){
                if($ledgerA[$table]['hash']!=$ledgerB[$table]['hash']){
                    $error .= " in the {$table} table";
                    if(isset($ledgerA[$table]['data'])){
                        foreach($ledgerA[$table]['data'] as $idx => $info){
                            if($ledgerA[$table]['data'][$idx]!=$ledgerB[$table]['data'][$idx]){
                                $tx_index = $ledgerA[$table]['data'][$idx]->tx_index;
                                if($tx_index)
                                    $error .= " for tx_index {$tx_index}";
                            }
                        }
                    }
                }
            }
        }

        // Display any errors and exit
        if($error){
            print "\n";
            byeLog($error);
        }

        // Print out a status update
        $credits = substr($ledgerA['credits']['hash'],0,5);
        $debits  = substr($ledgerA['debits']['hash'],0,5);
        $txlist  = substr($ledgerA['txlist']['hash'],0,5);
        print "\n\t [credits:{$credits} debits:{$debits} txlist:{$txlist}]";

        // Report time to process block
        $time = $timer->finish();
        print " Done [{$time}sec]\n";

        // Increase block before next loop
        $block++;
    }    

    // Print out information on the total runtime
    printRuntime($runtime->finish());

    // Notify user reparse is complete
    byeLog("Reparse complete.");
}

?>
