<?php
/*********************************************************************
 * mints.php - MINT command
 *
 * PARAMS:
 * - TICK           - token name registered with ISSUE format (required)
 * - AMOUNT         - Amount of tokens to mint (required)
 * - DESTINATION    - Address to transfer tokens to
 * 
 * FORMATS:
 * - bt:MINT|TICK|AMOUNT|DESTINATION
 ********************************************************************/
function btnsMint($params=null, $data=null, $error=null){
    global $mysqli;
    // Coming soon

                // Validate AMOUNT and ADDRESS for MINT/SEND
                // if(in_array($action,array('MINT','SEND'))){
                //     $info = getTokenInfo($tick_id);
                //     // Update BTNS transaction object with basic token details
                //     $data->SUPPLY     = ($info) ? $info->SUPPLY : 0;
                //     $data->DECIMALS   = ($info) ? $info->DECIMALS : 0;
                //     $data->MAX_SUPPLY = ($info) ? $info->MAX_SUPPLY : 0;
                //     $data->MAX_MINT   = ($info) ? $info->MAX_MINT : 0;
                //     $data->AMOUNT     = (string) $params[2]; // Amount  of tokens
                //     $data->TRANSFER   = $params[3];          // Address to transfer tokens to 
                //     $divisible        = ($data->DECIMALS==0) ? 0 : 1;
                //     [$amount_int, $amount_sats] = explode('.',$data->AMOUNT);
                //     // Verify TICK exist (seen in valid DEPLOY)
                //     if($info->MAX_SUPPLY===NULL)
                //         $error = 'invalid: TICK (unknown)';
                //     // Verify AMOUNT format
                //     if(!$error && (!is_numeric($amount_int)||($divisible && !is_numeric($amount_sats))))
                //         $error = 'invalid: AMOUNT format';
                //     // Verify TRANSFER address in a lose way (26-35=P2PKH, 42=Segwit)
                //     if(!$error && (in_array($action,array('SEND','TRANSFER'))||$data->TRANSFER!='')){
                //         $len   = strlen($data->TRANSFER);
                //         $field = ($action=='TRANSFER') ? 'DESTINATION' : 'TRANSFER';
                //         if(!$error && (($len>=26 && $len<=35)||$len==42))
                //             $error = "invalid: {$field} address";
                //     }
                // }


                // Handle MINT actions
                // if($action=='MINT'){
                //     // `MINT` Format
                //     // bt:MINT|TICK|AMOUNT|TRANSFER
                //     // Verify AMOUNT is less than MAX_MINT
                //     if(!$error && ($data->AMOUNT > $data->MAX_MINT))
                //         $error = 'invalid: AMOUNT > MAX_MINT';
                //     // Verify minting AMOUNT will not exceed MAX_SUPPLY
                //     if(!$error && (bcadd($data->SUPPLY,$data->AMOUNT,$data->DECIMALS) > bcadd($data->MAX_SUPPLY,0,$data->DECIMALS)))
                //         $error = 'invalid: mint exceeds MAX_SUPPLY';
                //     // Determine final status
                //     $data->STATUS = $status = ($error) ? $error : 'valid';
                //     // Print status message 
                //     print "\n\t MINT : {$data->TICK} : {$data->AMOUNT} : {$data->STATUS}";
                //     // Create record in mints table
                //     createMint($data);
                //     if($status=='valid'){
                //         // Credit MINT_SUPPLY to source address
                //         if($data->AMOUNT){
                //             createCredit('MINT', $data->BLOCK_INDEX, $data->TX_HASH, $data->TICK, $data->AMOUNT, $data->SOURCE);
                //             // Transfer AMOUNT to TRANSFER address
                //             if($data->TRANSFER){
                //                 createDebit('MINT', $data->BLOCK_INDEX, $data->TX_HASH, $data->TICK, $data->AMOUNT, $data->SOURCE);
                //                 createCredit('MINT', $data->BLOCK_INDEX, $data->TX_HASH, $data->TICK, $data->AMOUNT, $data->TRANSFER);
                //             }
                //         }
                //         // Update balances for addresses
                //         updateBalances([$data->SOURCE, $data->TRANSFER]);
                //     }
                // }
}
