<?php
/*********************************************************************
 * dividend.php - DIVIDEND command
 *
 * PARAMS:
 * - VERSION        - Broadcast Format Version
 * - TICK           - 1 to 250 characters in length
 * - DIVIDEND_TICK  - 1 to 250 characters in length
 * - AMOUNT         - The quantity of DIVIDEND_TICK rewarded per UNIT
 * - MEMO           - An optional memo to include
 *  
 * FORMATS:
 * 0 = VERSION|TICK|DIVIDEND_TICK|AMOUNT
 ********************************************************************/
function btnsDividend($params=null, $data=null, $error=null){
    global $mysqli, $reparse, $addresses, $tickers;

    // Define list of known FORMATS
    $formats = array(
        0 => 'VERSION|TICK|DIVIDEND_TICK|AMOUNT|MEMO'
    );

    /*****************************************************************
     * DEBUGGING - Force params
     ****************************************************************/
    // $str = "0|BACONTITS|DIVIDENDTEST1|1|testing dividends";
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
    $btInfo2 = getTokenInfo($data->DIVIDEND_TICK, null, $data->BLOCK_INDEX, $data->TX_INDEX);

    // Set divisible flags
    $divisible  = ($btInfo->DECIMALS==0) ? 0 : 1; 
    $divisible2 = ($btInfo2->DECIMALS==0) ? 0 : 1; 

    // Clone the raw data for storage in dividends table
    $dividend = clone($data);

    // Create the fees object 
    $fees = createFeesObject($data);

    // Validate TICK exists
    if(!$error && !$btInfo)
        $error = 'invalid: TICK (unknown)';

    // Validate DIVIDEND_TICK exists
    if(!$error && !$btInfo2)
        $error = 'invalid: DIVIDEND_TICK (unknown)';

    // Define placeholders for holders and balances
    $holder   = [];
    $balances = [];

    // Get list of TICK holders and SOURCE address balances
    if(!$error){
        $holders  = getHolders($data->TICK, $data->BLOCK_INDEX, $data->TX_INDEX);
        $balances = getAddressBalances($data->SOURCE, null, $data->BLOCK_INDEX, $data->TX_INDEX);
    }

    /*************************************************************
     * FORMAT Validations
     ************************************************************/

    // Verify AMOUNT format valid for TICK and DIVIDEND_TICK
    if(!$error && isset($data->AMOUNT) && (!isValidAmountFormat($divisible2, $data->AMOUNT) || !isValidAmountFormat($divisible, $data->AMOUNT)))
        $error = "invalid: AMOUNT (format)";

    /*************************************************************
     * General Validations
     ************************************************************/

    // Verify no pipe in MEMO (BTNS uses pipe as field delimiter)
    if(!$error && strpos($data->MEMO,'|')!==false)
        $error = 'invalid: MEMO (pipe)';

    // Verify no semicolon in MEMO (BTNS uses semicolon as action delimiter)
    if(!$error && strpos($data->MEMO,';')!==false)
        $error = 'invalid: MEMO (semicolon)';

    // Remove SOURCE address from the TICK holders list
    unset($holders[$data->SOURCE]);

    // Handle TICK and DIVIDEND_TICK divisibility mismatches by cleaning up the holders list (only deal with integer amounts)
    if(!$error && $divisible!=$divisible2)
        $holders = cleanupHolders($holders);

    // Determine total amount of TICK
    $data->HOLDER_TOTAL = 0;
    foreach($holders as $addr => $amount)
        $data->HOLDER_TOTAL = bcadd($data->HOLDER_TOTAL, $amount, $btInfo->DECIMALS);

    // Determine total amount of DIVIDEND_TICK 
    $data->DIVIDEND_TOTAL = bcmul($data->HOLDER_TOTAL, $data->AMOUNT, $btInfo2->DECIMALS);

    // Verify SOURCE has enough balances to cover total dividend amount
    if(!$error && !hasBalance($balances, $data->DIVIDEND_TICK, $data->DIVIDEND_TOTAL))
        $error = 'invalid: insufficient funds (DIVIDEND_TICK)';

    // Adjust balances to reduce by dividend total
    if(!$error)
        $balances = debitBalances($balances, $data->DIVIDEND_TICK, $data->DIVIDEND_TOTAL);

    // Calculate total number of database hits for this DIVIDEND
    $db_hits  = count($holders) * 2; // 1 credits, 1 balances
    $db_hits += 4;                   // 1 debits,  1 balances, 1 dividend

    // Determine total transaction FEE based on database hits
    $fees->AMOUNT = getTransactionFee($db_hits, $fees->TICK);

    // Verify SOURCE has enough balances to cover FEE AMOUNT
    if(!$error && !hasBalance($balances, $fees->TICK, $fees->AMOUNT))
        $error = 'invalid: insufficient funds (FEE)';

    // Adjust balances to reduce by FEE amount
    if(!$error)
        $balances = debitBalances($balances, $fees->TICK, $fees->AMOUNT);

    // Determine final status
    $data->STATUS = $dividend->STATUS = $status = ($error) ? $error : 'valid';

    // Print status message 
    print "\n\t DIVIDEND : {$data->TICK} : {$data->DIVIDEND_TICK} : {$data->AMOUNT} : {$data->STATUS}";

    // Create record in dividend table
    createDividend($dividend);

    // If this was a valid transaction, then add records to the credits and debits array
    if($status=='valid'){

        // Store the SOURCE, DIVIDEND_TICK, and fee TICK in addresses list
        addAddressTicker($data->SOURCE, [$data->DIVIDEND_TICK, $fees->TICK]);

        // Debit DIVIDEND_TOTAL from SOURCE address
        createDebit('DIVIDEND', $data->BLOCK_INDEX, $data->TX_HASH, $data->DIVIDEND_TICK, $data->DIVIDEND_TOTAL, $data->SOURCE);

        // Handle any transaction FEE according the users's ADDRESS preferences
        processTransactionFees('DIVIDEND', $fees);

        // Loop through TICK holders and credit them with DIVIDEND_TICK amount
        foreach($holders as $address => $amount){
            $dividend_amount = bcmul($amount, $data->AMOUNT, $btInfo2->DECIMALS);

            // Credit TICK holders with DIVIDEND_TICK AMOUNT
            createCredit('DIVIDEND', $data->BLOCK_INDEX, $data->TX_HASH, $data->DIVIDEND_TICK, $dividend_amount, $address);

            // Store the recipient ADDRESS and TICK in addresses list
            addAddressTicker($address, $data->DIVIDEND_TICK);
        }
    }

    // If this is a reparse, bail out before updating balances
    if($reparse)
        return;

    // Store the SOURCE and TICKERS in addresses list
    addAddressTicker($data->SOURCE, $tickers);

    // Update address balances
    updateBalances(array_keys($addresses));

    // Update supply for tokens
    updateTokens($tickers);
}
?>