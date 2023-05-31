<?php
/*********************************************************************
 * destroys.php - DESTROY command
 *
 * PARAMS:
 * - TICK    - 1 to 5 characters in length (required)
 * - COMMAND - Any valid BTNS ACTION with PARAMS
 * 
 * FORMATS:
 * - bt:BATCH|COMMAND;COMMAND
 ********************************************************************/
function btnsSend($params=null, $data=null, $error=null){
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



                // Debug / Testing (no transfers to test on yet)
                // $string = 'bt:TRANSFER|JDOG|1|1JDogZS6tQcSxwfxhv6XKKjcyicYA4Feev';
                // $string = 'bt:TRANSFER|BRRR|5|1JDogZS6tQcSxwfxhv6XKKjcyicYA4Feev|1|1BoogrfDADPLQpq8LMASmWQUVYDp4t2hF9';
                // $string = 'bt:TRANSFER|BRRR|5|1JDogZS6tQcSxwfxhv6XKKjcyicYA4Feev|TEST|1|1BoogrfDADPLQpq8LMASmWQUVYDp4t2hF9';
                // $params = explode('|',preg_replace($prefixes,'',$string));
                // // Trim whitespace from any params
                // foreach($params as $idx => $value)
                //     $params[$idx] = trim($value);
                // $action = strtoupper($params[0]); // First param is always action (DEPLOY / MINT / TRANSFER)

    
                // Handle SEND command
                // if($action=='SEND'){
                //     // `SEND` Formats
                //     // Format 1: bt:SEND|TICK|AMOUNT|DESTINATION
                //     // Format 2: bt:SEND|TICK|AMOUNT|DESTINATION|AMOUNT|DESTINATION
                //     // Format 3: bt:SEND|TICK|AMOUNT|DESTINATION|TICK|AMOUNT|DESTINATION
                //     $transfers = array(); // [TICK, AMOUNT, DESTINATION]
                //     // Determine correct format to use for parsing transfers
                //     // Format 1 - Single Send
                //     $format = 1; 
                //     // Format 2 - Multiple (brief)
                //     if(is_numeric($params[4]) && isCryptoAddress($params[5]))
                //         $format = 2;
                //     // Format 3 - Multiple (full)
                //     if(is_numeric($params[5]) && isCryptoAddress($params[6]))
                //         $format = 3;
                //     // Build out array of transfers
                //     $tick = $params[1];
                //     if($format==1)
                //         array_push($transfers,[$tick, $params[2], $params[3]]);
                //     // Parse in Multiple transfers
                //     if($format==2||$format==3){
                //         foreach($params as $idx => $param){
                //             if($format==2 && $idx>1 && $idx%2)
                //                 array_push($transfers,[$tick, $params[$idx-1], $params[$idx]]);
                //             if($format==3 && $idx>0 && $idx%3==1)
                //                 array_push($transfers,[$params[$idx], $params[$idx+1], $params[$idx+2]]);
                //         }
                //     }
                //     // Get source address balances 
                //     $balances = getAddressBalances($source_id);
                //     // Loop through transfers and determine total amount sent for each TICK
                //     $totals = []; // Assoc array to track tick/total
                //     foreach($transfers as $t){
                //         if(!$totals[$t[0]])
                //             $totals[$t[0]] = $t[1];
                //         else
                //             $totals[$t[0]] += $t[1];
                //     }
                //     // Verify that source address has balances to cover all token transfers
                //     // var_dump($transfers);
                //     // var_dump($balances);
                //     //  ... soon... Hoping on plane to Miami.
                //     // Create record of the TRANSFER
                //     if(!$error){
                //         // Determine final status
                //         $data->STATUS = $status = ($error) ? $error : 'valid';
                //         // Print status message 
                //         print "\n\t SEND : {$data->STATUS}";
                //         // Create record in transfers table
                //         createSend($data);
                //     }
                //     // If TRANSFER is valid, then create credit/debit/balance records
                //     if(!$error){
                //         // Loop through any transfers and process
                //         foreach($transfers as $t){
                //             $tick        = $t[0];
                //             $amount      = $t[1];
                //             $destination = $t[2];
                //             // Handle moving token between addresses
                //             if($amount){
                //                 // Add ticker to tickers array
                //                 $tickers[$tick] = 1; 
                //                 // Add destination address to addresses array
                //                 $addresses[$destination] = 1;
                //                 // Print status message 
                //                 print "\n\t SEND : {$tick} : {$amount} : {$destination}";
                //                 createDebit('SEND', $data->BLOCK_INDEX, $data->TX_HASH, $tick, $amount, $data->SOURCE);
                //                 createCredit('SEND', $data->BLOCK_INDEX, $data->TX_HASH, $tick, $amount, $destination);
                //                 // Update balances for addresses
                //                 updateBalances([$data->SOURCE, $destination]);
                //             }
                //         }
                //     }
                // }}
