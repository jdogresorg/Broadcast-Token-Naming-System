<?php
/*********************************************************************
 * sweep.php - SWEEP command
 *
 * PARAMS:
 * VERSION     - Broadcast Format Version
 * DESTINATION - address where `token` shall be swept
 * BALANCES    - Indicates if address `token` balances should be swept (default=1)
 * OWNERSHIPS  - Indicates if address `token` ownerships should be swept (default=1)
 * MEMO        - Optional memo to include
 * 
 * FORMATS:
 * 0 = VERSION|DESTINATION|BALANCES|OWNERSHIP|MEMO
 ********************************************************************/
function btnsSweep($params=null, $data=null, $error=null){
    global $mysqli, $reparse, $addresses, $tickers;

    // Define list of known FORMATS
    $formats = array(
        0 => 'VERSION|DESTINATION|BALANCES|OWNERSHIPS|MEMO'
    );

    /*****************************************************************
     * DEBUGGING - Force params
     ****************************************************************/
    // $str = "0|JDOG|1|";
    // $params = explode('|',$str);

    // Validate that broadcast format is known
    $format = getFormatVersion($params[0]);
    if(!$error && ($format===NULL || !in_array($format,array_keys($formats))))
        $error = 'invalid: VERSION (unknown)';

    // Parse PARAMS using given VERSION format and update BTNS transaction data object
    if(!$error)
        $data = setActionParams($data, $params, $formats[$format]);

    // Get SOURCE address balances and ownership information
    $balances    = getAddressBalances($data->SOURCE, null, $data->BLOCK_INDEX, $data->TX_INDEX);
    $ownerships  = getAddressOwnership($data->SOURCE, null, $data->BLOCK_INDEX, $data->TX_INDEX);

    // Create the fees object 
    $fees = createFeesObject($data);

    /*****************************************************************
     * FORMAT Validations
     ****************************************************************/

    // Verify DESTINATION address format
    if(!$error && isset($data->DESTINATION) && !isCryptoAddress($data->DESTINATION))
        $error = "invalid: DESTINATION (format)";

    // Verify BALANCES format is valid (0 or 1)
    if(!$error && isset($data->BALANCES) && !in_array($data->BALANCES,array(0,1)))
        $error = "invalid: BALANCES (format)";

    // Verify OWNERSHIP format is valid (0 or 1)
    if(!$error && isset($data->OWNERSHIP) && !in_array($data->OWNERSHIP,array(0,1)))
        $error = "invalid: OWNERSHIP (format)";

    // Set default values for BALANCES and OWNERSHIP (default = 1)
    $data->BALANCES  = (isset($data->BALANCES)) ? $data->BALANCES : 1;
    $data->OWNERSHIPS = (isset($data->OWNERSHIPS)) ? $data->OWNERSHIPS : 1;

    // Clone the raw data for storage in sweeps table
    $sweep = clone($data);

    /*****************************************************************
     * General Validations
     ****************************************************************/

    // Verify no pipe in MEMO (BTNS uses pipe as field delimiter)
    if(!$error && strpos($data->MEMO,'|')!==false)
        $error = 'invalid: MEMO (pipe)';

    // Verify no semicolon in MEMO (BTNS uses semicolon as action delimiter)
    if(!$error && strpos($data->MEMO,';')!==false)
        $error = 'invalid: MEMO (semicolon)';

    // Calculate total number of database hits for this SWEEP
    $db_hits = 1;                                                     // 1 sweeps
    $db_hits += ($data->BALANCES)  ? bcmul(count($balances),3,0) : 0; // 1 debits, 1 credits, 1 balances
    $db_hits += ($data->OWNERSHIP) ? count($ownerships) : 0;          // 1 issues

    // Determine total transaction FEE based on database hits
    $fees->AMOUNT = getTransactionFee($db_hits, $fees->TICK);

    // Verify SOURCE has enough balances to cover FEE AMOUNT
    if(!$error && !hasBalance($balances, $fees->TICK, $fees->AMOUNT))
        $error = 'invalid: insufficient funds (FEE)';

    // Adjust balances to reduce by FEE amount
    if(!$error)
        $balances = debitBalances($balances, $fees->TICK, $fees->AMOUNT);

    // Determine final status
    $data->STATUS = $sweep->STATUS = $status = ($error) ? $error : 'valid';

    // Print status message 
    print "\n\t SWEEP : {$data->DESTINATION} : {$data->STATUS}";

    // Create record in sweeps table
    createSweep($sweep);

    // If this was a valid transaction, then handle the actual sweep actions
    if($status=='valid'){

        // Store the SOURCE and FEE_TICK in addresses and tickers arrays
        addAddressTicker($data->SOURCE, $fees->TICK);

        // Handle any transaction FEE according the users's ADDRESS preferences
        processTransactionFees('SWEEP', $fees);

        // Transfer Balances
        if($data->BALANCES==1){
            foreach($balances as $tick_id => $amount){
                $tick = getTicker($tick_id);

                // Debit token amount from SOURCE and credit to DESTINATION
                createDebit('SWEEP',  $data->BLOCK_INDEX, $data->TX_HASH, $tick, $amount, $data->SOURCE);
                createCredit('SWEEP', $data->BLOCK_INDEX, $data->TX_HASH, $tick, $amount, $data->DESTINATION);

                // Store the SOURCE, DESTINATION and TICK in addresses and tickers arrays
                addAddressTicker($data->SOURCE, $tick);
                addAddressTicker($data->DESTINATION, $tick);
            }
        }

        // Transfer token ownerships
        if($data->OWNERSHIPS==1){
            // Copy base BTNS transaction data object into issue object
            $issue = $data;
            $issue->TRANSFER = $data->DESTINATION;
            foreach($ownerships as $tick){
                $issue->TICK = $tick;

                // Create issue record for transfer of ownership
                createIssue($issue);

                // Store the DESTINATION and TICK in addresses and tickers arrays
                addAddressTicker($data->DESTINATION, $tick);
            }
        }

        // If this is a reparse, bail out before updating balances and token information
        if($reparse)
            return;

        // Update address balances
        updateBalances(array_keys($addresses));

        // Update supply for tokens
        updateTokens($tickers);
    }
}

?>