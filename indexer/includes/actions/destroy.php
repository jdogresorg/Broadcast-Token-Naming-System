<?php
/*********************************************************************
 * destroy.php - DESTROY command
 *
 * PARAMS:
 * - VERSION - Broadcast Format Version
 * - TICK    - 1 to 250 characters in length
 * - AMOUNT  - Amount of tokens to destroy
 * - MEMO    - An optional memo to include     
 * 
 * FORMATS:
 * - 0 = Single Destroy
 * - 1 = Multi-Destroy (Brief)
 * - 2 = Multi-Destroy (Full)
 *
 ********************************************************************/
function btnsDestroy($params=null, $data=null, $error=null){
    global $mysqli, $reparse, $tickers, $addresses;

    // Define list of known FORMATS
    $formats = array(
        0 => 'VERSION|TICK|AMOUNT|MEMO',
        1 => 'VERSION|TICK|AMOUNT|TICK|AMOUNT|MEMO',
        2 => 'VERSION|TICK|AMOUNT|MEMO|TICK|AMOUNT|MEMO',
    );

    /*****************************************************************
     * DEBUGGING - Force params
     ****************************************************************/
    // $str = '0|BRRR|1|foo';
    // $str = '1|BRRR|1|GAS|10|bar';
    // $str = '2|BRRR|1|foo|GAS|10|bar';
    // $params = explode('|',$str);

    // Validate that broadcast format is known
    $format = getFormatVersion($params[0]);
    if(!$error && ($format===NULL || !in_array($format,array_keys($formats))))
        $error = 'invalid: VERSION (unknown)';

    // Array of destroys [TICK, AMOUNT, MEMO]
    $destroys = array(); 

    // Extract memo
    $memo = NULL;
    $last = count($params) - 1;
    foreach($params as $idx => $param)
        if($idx==$last && (($format==0 && $idx==3) || ($format==1 && $idx%2==1)))
            $memo = $param;

    // Build out array of destroys
    $lastIdx = count($params) - 1;        
    foreach($params as $idx => $param){

        // Single Destroy
        if($format==0 && $idx==0)
            array_push($destroys,[$params[1], $params[2], $memo]);

        // Multi-Destroy (Brief)
        if($format==1 && $idx>1 && $idx%2==1)
            array_push($destroys,[$params[1], $params[$idx-1], $memo]);

        // Multi-Destroy (Full)
        if($format==2 && $idx>0 && $idx%3==1 && $idx < $lastIdx)
            array_push($destroys,[$params[$idx], $params[$idx+1], $params[$idx+2]]);
    }

    // Get token data for every TICK (reduces duplicated sql queries)
    $ticks = [];
    foreach($destroys as $destroy){
        $tick = $destroy[0];
        if(!$ticks[$tick])
            $ticks[$tick] = getTokenInfo($tick);
    }

    // Get source address balances 
    $balances = getAddressBalances($data->SOURCE);

    // Store original error value
    $origError = $error;

    // Add SOURCE address to the addresses array
    $addresses[$data->SOURCE] = 1;

    // Array of debits
    $debits  = [];

    // Loop through destroys and process each
    foreach($destroys as $info){

        // Reset $error to the original value
        $error = $origError;

        // Copy base BTNS Transacation data object
        $destroy = $data;

        // Update BTNS transaction data object with destroy values
        $destroy->TICK        = $info[0];
        $destroy->AMOUNT      = $info[1];
        $destroy->MEMO        = $info[2];

        // Get information on BTNS token
        $btInfo = $ticks[$destroy->TICK];

        // Set divisible flag
        $divisible = ($btInfo->DECIMALS==0) ? 0 : 1; 

        // Validate TICK exists
        if(!$error && !$btInfo)
            $error = 'invalid: TICK (unknown)';

        /*************************************************************
         * FORMAT Validations
         ************************************************************/

        // Verify AMOUNT format
        if(!$error && isset($destroy->AMOUNT) && !isValidAmountFormat($divisible, $destroy->AMOUNT))
            $error = "invalid: AMOUNT (format)";

        /*************************************************************
         * General Validations
         ************************************************************/

        // Verify no pipe in MEMO (BTNS uses pipe as field delimiter)
        if(!$error && strpos($destroy->MEMO,'|')!==false)
            $error = 'invalid: MEMO (pipe)';

        // Verify no semicolon in MEMO (BTNS uses semicolon as action delimiter)
        if(!$error && strpos($destroy->MEMO,';')!==false)
            $error = 'invalid: MEMO (semicolon)';

        // Verify SOURCE has enough balances to cover destroy AMOUNT
        if(!$error && !hasBalance($balances, $destroy->TICK, $destroy->AMOUNT))
            $error = 'invalid: insufficient funds';
    
        // Adjust balances to reduce by DESTROY AMOUNT
        if(!$error)
            $balances = debitBalances($balances, $destroy->TICK, $destroy->AMOUNT);

        // Determine final status
        $destroy->STATUS = $status = ($error) ? $error : 'valid';

        // Print status message 
        print "\n\t DESTROY : {$destroy->TICK} : {$destroy->AMOUNT} : {$destroy->MEMO} : {$destroy->STATUS}";

        // Create record in transfers table
        createDestroy($destroy);

        // If this was a valid transaction, then create debit record
        if($status=='valid'){

            // Add ticker to tickers array
            $tickers[$destroy->TICK] = 1; 

            // Add ticker and amount to debits array
            array_push($debits,  array($destroy->TICK, $destroy->AMOUNT));
        }
    }

    // Consolidate the debit records to write as few records as possible
    $debits  = consolidateCreditDebitRecords('debits', $debits);

    // Create records in debits table
    foreach($debits as $debit){
        [$tick, $amount] = $debit;
        createDebit('DESTROY', $data->BLOCK_INDEX, $data->TX_HASH, $tick, $amount, $data->SOURCE);
    }

    // Update address balances
    updateBalances(array_keys($addresses));

    // Update supply for tokens
    updateTokens(array_keys($ticks));
}

?>