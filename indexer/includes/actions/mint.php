<?php
/*********************************************************************
 * mint.php - MINT command
 *
 * PARAMS:
 * - VERSION     - Broadcast Format Version
 * - TICK        - 1 to 250 characters in length
 * - AMOUNT      - Amount of tokens to mint
 * - DESTINATION - Address to transfer tokens to
 * 
 * FORMATS:
 * - 0 = Full
 * 
 ********************************************************************/
function btnsMint($params=null, $data=null, $error=null){
    global $mysqli, $reparse, $addresses, $tickers;

    // Define list of known FORMATS
    $formats = array(
        0 => 'VERSION|TICK|AMOUNT|DESTINATION'
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
    $btInfo = getTokenInfo($data->TICK, null, $data->BLOCK_INDEX, $data->TX_INDEX);

    // Clone the raw data for storage in mints table
    $mint = clone($data);

    // Verify TICK is valid before MINT
    if($btInfo->BLOCK_INDEX==$data->BLOCK_INDEX && !validTickerBeforeTxIndex($data->TICK, $data->TX_INDEX))
        unset($btInfo);

    // Set divisible flag
    $divisible = ($btInfo->DECIMALS==0) ? 0 : 1; 

    // Validate TICK exists
    if(!$error && !$btInfo)
        $error = 'invalid: TICK (unknown)';

    // Validate DESTINATION and SOURCE are different
    if($data->DESTINATION == $data->SOURCE)
        unset($data->DESTINATION);

    // Update BTNS transaction object with basic token details
    if($btInfo){
        $data->SUPPLY           = ($btInfo) ? $btInfo->SUPPLY : 0;
        $data->DECIMALS         = ($btInfo) ? $btInfo->DECIMALS : 0;
        $data->MAX_SUPPLY       = ($btInfo) ? $btInfo->MAX_SUPPLY : 0;
        $data->MAX_MINT         = ($btInfo) ? $btInfo->MAX_MINT : 0;
        $data->MINT_ADDRESS_MAX = ($btInfo) ? $btInfo->MINT_ADDRESS_MAX : 0;
        $data->MINT_START_BLOCK = ($btInfo) ? $btInfo->MINT_START_BLOCK : 0;
        $data->MINT_STOP_BLOCK  = ($btInfo) ? $btInfo->MINT_STOP_BLOCK : 0;
    }

    /*****************************************************************
     * FORMAT Validations
     ****************************************************************/

    // Verify AMOUNT format
    if(!$error && isset($data->AMOUNT) && !isValidAmountFormat($divisible, $data->AMOUNT))
        $error = "invalid: AMOUNT (format)";

    // Verify DESTINATION address format
    if(!$error && isset($data->DESTINATION) && !isCryptoAddress($data->DESTINATION))
        $error = "invalid: DESTINATION (format)";

    /*****************************************************************
     * General Validations
     ****************************************************************/

    // Verify AMOUNT is less than MAX_MINT
    if(!$error && isset($data->AMOUNT) && $data->AMOUNT > $data->MAX_MINT)
        $error = 'invalid: AMOUNT > MAX_MINT';

    // Verify minting AMOUNT will not exceed MAX_SUPPLY
    if(!$error && (bcadd($data->SUPPLY,$data->AMOUNT,$data->DECIMALS) > bcadd($data->MAX_SUPPLY,0,$data->DECIMALS)))
        $error = 'invalid: mint exceeds MAX_SUPPLY';

    // Verify action is allowed from SOURCE (ALLOW_LIST & BLOCK_LIST)
    if(!$error && !isActionAllowed($data->TICK, $data->SOURCE))
        $error = 'invalid: SOURCE (not authorized)';

    // Verify action is allowed to DESTINATION (ALLOW_LIST & BLOCK_LIST)
    if(!$error && isset($data->DESTINATION) && !isActionAllowed($data->TICK, $data->DESTINATION))
        $error = 'invalid: DESTINATION (not authorized)';

    // Verify minting AMOUNT will not exceed MINT_ADDRESS_MAX
    if(!$error && isset($data->MINT_ADDRESS_MAX) && $data->MINT_ADDRESS_MAX > 0 && (bcadd(getActionCreditDebitAmount('credits', 'MINT', $data->TICK, $data->SOURCE, $data->TX_INDEX),$data->AMOUNT,$data->DECIMALS) > $data->MINT_ADDRESS_MAX))
        $error = 'invalid: mint exceeds MINT_ADDRESS_MAX';

    // Verify minting begins at MINT_START_BLOCK
    if(!$error && isset($data->MINT_START_BLOCK) && $data->MINT_START_BLOCK > 0 && $data->BLOCK_INDEX < $data->MINT_START_BLOCK)
        $error = 'invalid: MINT_START_BLOCK';

    // Verify minting ends at MINT_STOP_BLOCK
    if(!$error && isset($data->MINT_STOP_BLOCK) && $data->MINT_STOP_BLOCK > 0 && $data->BLOCK_INDEX > $data->MINT_STOP_BLOCK)
        $error = 'invalid: MINT_STOP_BLOCK';

    // Determine final status
    $data->STATUS = $mint->STATUS = $status = ($error) ? $error : 'valid';

    // Print status message 
    print "\n\t MINT : {$data->TICK} : {$data->AMOUNT} : {$data->STATUS}";

    // Create record in mints table
    createMint($mint);

    // If this was a valid transaction, then mint any actual supply
    if($status=='valid'){

        // Credit MINT_SUPPLY to source address
        if($data->AMOUNT){
            createCredit('MINT', $data->BLOCK_INDEX, $data->TX_HASH, $data->TICK, $data->AMOUNT, $data->SOURCE);

            // Transfer AMOUNT to DESTINATION address
            if($data->DESTINATION){
                createDebit('MINT', $data->BLOCK_INDEX, $data->TX_HASH, $data->TICK, $data->AMOUNT, $data->SOURCE);
                createCredit('MINT', $data->BLOCK_INDEX, $data->TX_HASH, $data->TICK, $data->AMOUNT, $data->DESTINATION);
            }
        }

        // If this is a reparse, bail out before updating balances and token information
        if($reparse)
            return;

        // Update balances for addresses
        updateBalances([$data->SOURCE, $data->DESTINATION]);

        // Update supply for token
        updateTokenInfo($data->TICK);
    }
}

?>