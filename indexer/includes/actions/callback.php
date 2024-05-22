<?php
/*********************************************************************
 * callback.php - CALLBACK command
 *
 * PARAMS:
 * - VERSION - Broadcast Format Version
 * - TICK    - 1 to 250 characters in length
 * - MEMO    - An optional memo to include
 * 
 * FORMATS:
 * 0 = VERSION|TICK|MEMO
 ********************************************************************/
function btnsCallback($params=null, $data=null, $error=null){
    global $mysqli, $reparse, $addresses, $tickers;

    // Define list of known FORMATS
    $formats = array(
        0 => 'VERSION|TICK|MEMO'
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

    // Get information on BTNS token
    $btInfo  = getTokenInfo($data->TICK, null, $data->BLOCK_INDEX, $data->TX_INDEX);

    // Clone the raw data for storage in callbacks table
    $callback = clone($data);

    // Create the fees object 
    $fees = createFeesObject($data);

    // Define placeholders for holders and balances
    $holder   = [];
    $balances = [];

    // Get list of TICK holders and SOURCE address balances
    if(!$error){
        $holders  = getHolders($data->TICK, $data->BLOCK_INDEX, $data->TX_INDEX);
        $balances = getAddressBalances($data->SOURCE, null, $data->BLOCK_INDEX, $data->TX_INDEX);
    }

    // Validate TICK exists
    if(!$error && !$btInfo)
        $error = 'invalid: TICK (unknown)';

    // Verify only token OWNER can do CALLBACK
    if(!$error && $data->SOURCE!=$btInfo->OWNER)
        $error = 'invalid: SOURCE (not authorized)';

    // Get information on CALLBACK_TICK token
    if(!$error && isset($btInfo->CALLBACK_TICK))
        $btInfo2 = getTokenInfo($btInfo->CALLBACK_TICK, null, $data->BLOCK_INDEX, $data->TX_INDEX);

    // Validate CALLBACK_TICK exists
    if(!$error && !$btInfo2)
        $error = 'invalid: CALLBACK_TICK (unknown)';

    // Set divisible flags
    $divisible  = ($btInfo->DECIMALS==0) ? 0 : 1; 
    $divisible2 = ($btInfo2->DECIMALS==0) ? 0 : 1; 

    // Populate callback object with callback data
    if($btInfo){
        $callback->CALLBACK_TICK   = $btInfo->CALLBACK_TICK;
        $callback->CALLBACK_AMOUNT = $btInfo->CALLBACK_AMOUNT;
    }

    /*****************************************************************
     * ACTION Validations
     ****************************************************************/

    // Verify CALLBACK is allowed
    if(!$error && isset($btInfo->LOCK_CALLBACK) && $btInfo->LOCK_CALLBACK==1)
        $error = "invalid: LOCK_CALLBACK";

    /*****************************************************************
     * FORMAT Validations
     ****************************************************************/

    // Verify CALLBACK_BLOCK format
    if(!$error && $btInfo && isset($btInfo->CALLBACK_BLOCK) && $btInfo->CALLBACK_BLOCK!=intval($btInfo->CALLBACK_BLOCK))
        $error = 'invalid: CALLBACK_BLOCK (format)';

    // Verify CALLBACK_AMOUNT format
    if(!$error && isset($btInfo->CALLBACK_AMOUNT) && !isValidAmountFormat($divisible2, $btInfo->CALLBACK_AMOUNT))
        $error = "invalid: CALLBACK_AMOUNT (format)";

    /*****************************************************************
     * General Validations
     ****************************************************************/

    // Verify CALLBACK_BLOCK is less than or equal to current block index
    if(!$error && $btInfo && isset($btInfo->CALLBACK_BLOCK) && $btInfo->CALLBACK_BLOCK > $data->BLOCK_INDEX)
        $error = 'invalid: CALLBACK_BLOCK (block index)';

    // Loop through holders and determine CALLBACK_TOTAL 
    $total = (object)[];
    $total->TICK          = 0;
    $total->CALLBACK_TICK = 0;
    foreach($holders as $address => $amount){
        // Skip including SOURCE address in total calculations
        if($address==$data->SOURCE)
            continue;
        $callback_amount      = bcmul($amount, $btInfo->CALLBACK_AMOUNT, $btInfo2->DECIMALS);
        $total->TICK          = bcadd($amount, $total->TICK, $btInfo->DECIMALS);
        $total->CALLBACK_TICK = bcadd($callback_amount, $total->CALLBACK_TICK, $btInfo2->DECIMALS);
    }

    // Verify SOURCE has enough balances to cover total callback amount
    if(!$error && !hasBalance($balances, $btInfo->CALLBACK_TICK, $total->CALLBACK_TICK))
        $error = 'invalid: insufficient funds (CALLBACK_TICK)';

    // Adjust balances to reduce by callback total
    if(!$error)
        $balances = debitBalances($balances, $btInfo->CALLBACK_TICK, $data->CALLBACK_TICK);

    // Calculate total number of database hits for this CALLBACK
    if(!$error){
        $db_hits  = count($holders) * 3; // 1 debits, 1 credits, 1 balances
        $db_hits += 4;                   // 1 debits, 1 credits, 1 balances, 1 callback

        // Determine total transaction FEE based on database hits
        $fees->AMOUNT = getTransactionFee($db_hits, $fees->TICK);
    }

    // Verify SOURCE has enough balances to cover FEE AMOUNT
    if(!$error && !hasBalance($balances, $fees->TICK, $fees->AMOUNT))
        $error = 'invalid: insufficient funds (FEE)';

    // Adjust balances to reduce by FEE amount
    if(!$error)
        $balances = debitBalances($balances, $fees->TICK, $fees->AMOUNT);

    // Determine final status
    $data->STATUS = $callback->STATUS = $status = ($error) ? $error : 'valid';

    // Print status message 
    print "\n\t CALLBACK : {$data->TICK} : {$data->STATUS}";

    // Create record in callbacks table
    createCallback($callback);

    // If this was a valid transaction, then add records to the credits and debits array
    if($status=='valid'){

        // TODO: update to support dispensers (close dispenser)

        // Store the SOURCE, TICK, CALLBACK_TICK, and fee TICK in addresses list
        addAddressTicker($data->SOURCE, [$data->TICK, $btInfo->CALLBACK_TICK, $fees->TICK]);

        // Debit CALLBACK_TOTAL from SOURCE address 
        createDebit('CALLBACK', $data->BLOCK_INDEX, $data->TX_HASH, $btInfo->CALLBACK_TICK, $total->CALLBACK_TICK, $data->SOURCE);

        // Credit all TICK SUPPLY to SOURCE address
        createCredit('CALLBACK', $data->BLOCK_INDEX, $data->TX_HASH, $btInfo->TICK, $total->TICK, $data->SOURCE);

        // Handle any transaction FEE according the users's ADDRESS preferences
        processTransactionFees('CALLBACK', $fees);

        // Loop through TICK holders
        // TODO: Decimal precision needs a bit more work... 
        foreach($holders as $address => $amount){
            $callback_amount = bcmul($amount, $btInfo->CALLBACK_AMOUNT, $btInfo2->DECIMALS);

            // Skip including SOURCE address in credits/debits (already )
            if($address==$data->SOURCE)
                continue;

            // Debit TICK from holders address
            createDebit('CALLBACK',  $data->BLOCK_INDEX, $data->TX_HASH, $data->TICK, $amount, $address);

            // Credit CALLBACK_TICK to holders address except SOURCE
            if($callback_amount>0)
                createCredit('CALLBACK', $data->BLOCK_INDEX, $data->TX_HASH, $btInfo->CALLBACK_TICK, $callback_amount, $address);

            // Store the recipient ADDRESS, TICK, and CALLBACK_TICK in addresses list
            addAddressTicker($address, [$data->TICK, $btInfo->CALLBACK_TICK]);
        }

    }

    // If this is a reparse, bail out before updating balances
    if($reparse)
        return;

    // Update address balances
    updateBalances(array_keys($addresses));

    // Update supply for GAS (no other supply should change)
    updateTokens($fees->TICK);

    // TODO: Remove this when decimal precision is perfect better
    updateTokens($tickers);

}

?>