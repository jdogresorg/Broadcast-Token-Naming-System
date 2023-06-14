<?php
/*********************************************************************
 * send.php - SEND command
 *
 * PARAMS:
 * VERSION     - Broadcast Format Version        
 * TICK        - 1 to 250 characters in length   
 * AMOUNT      - Amount of `tokens` to send      
 * DESTINATION - Address to transfer `tokens` to 
 * MEMO        - An optional memo to include     
 * 
 * FORMATS:
 * 0 - Single Send
 * 1 - Multi-Send (Brief)
 * 2 - Multi-Send (Full)
 * 3 - Multi-Send (Full) with Multiple Memos
 ********************************************************************/
function btnsSend($params=null, $data=null, $error=null){
    global $mysqli, $tickers, $addresses;

    // Define list of known FORMATS
    $formats = array(
        0 => 'VERSION|TICK|AMOUNT|DESTINATION|MEMO',
        1 => 'VERSION|TICK|AMOUNT|DESTINATION|AMOUNT|DESTINATION|MEMO',
        2 => 'VERSION|TICK|AMOUNT|DESTINATION|TICK|AMOUNT|DESTINATION|MEMO',
        3 => 'VERSION|TICK|AMOUNT|DESTINATION|MEMO|TICK|AMOUNT|DESTINATION|MEMO'
    );

    /*****************************************************************
     * DEBUGGING - Force params
     ****************************************************************/
    // $str = '0|JDOG|1|1JDogZS6tQcSxwfxhv6XKKjcyicYA4Feev';
    // $str = '0|JDOG|1|1JDogZS6tQcSxwfxhv6XKKjcyicYA4Feev|Testing Memos';
    // $str = '1|BRRR|5|1JDogZS6tQcSxwfxhv6XKKjcyicYA4Feev|1|1BoogrfDADPLQpq8LMASmWQUVYDp4t2hF9';
    // $str = '1|BRRR|5|1JDogZS6tQcSxwfxhv6XKKjcyicYA4Feev|1|1BoogrfDADPLQpq8LMASmWQUVYDp4t2hF9|Testing Memos';
    // $str = '1|BRRR|5|1JDogZS6tQcSxwfxhv6XKKjcyicYA4Feev|1|1BoogrfDADPLQpq8LMASmWQUVYDp4t2hF9|3|1BTNSGASK5En7rFurDJ79LQ8CVYo2ecLC8';
    // $str = '1|BRRR|5|1JDogZS6tQcSxwfxhv6XKKjcyicYA4Feev|1|1BoogrfDADPLQpq8LMASmWQUVYDp4t2hF9|3|1BTNSGASK5En7rFurDJ79LQ8CVYo2ecLC8|Testing Memos';
    // $str = '2|BRRR|5|1JDogZS6tQcSxwfxhv6XKKjcyicYA4Feev|TEST|1|1BoogrfDADPLQpq8LMASmWQUVYDp4t2hF9';
    // $str = '2|BRRR|5|1JDogZS6tQcSxwfxhv6XKKjcyicYA4Feev|TEST|1|1BoogrfDADPLQpq8LMASmWQUVYDp4t2hF9|Testing Memos';
    // $str = '2|BRRR|5|1JDogZS6tQcSxwfxhv6XKKjcyicYA4Feev|TEST|1|1BoogrfDADPLQpq8LMASmWQUVYDp4t2hF9|BACON|3|1BTNSGASK5En7rFurDJ79LQ8CVYo2ecLC8';
    // $str = '2|BRRR|5|1JDogZS6tQcSxwfxhv6XKKjcyicYA4Feev|TEST|1|1BoogrfDADPLQpq8LMASmWQUVYDp4t2hF9|BACON|3|1BTNSGASK5En7rFurDJ79LQ8CVYo2ecLC8|Testing Memos';
    // $str = '3|BRRR|5|1JDogZS6tQcSxwfxhv6XKKjcyicYA4Feev|Testing Memos1|TEST|1|1BoogrfDADPLQpq8LMASmWQUVYDp4t2hF9|Testing Memos2|BACON|3|1BTNSGASK5En7rFurDJ79LQ8CVYo2ecLC8|Testing Memos3';
    // $params = explode('|',$str);

    // Validate that broadcast format is known
    $format = getFormatVersion($params[0]);
    if(!$error && ($format===NULL || !in_array($format,array_keys($formats))))
        $error = 'invalid: VERSION (unknown)';

    // Array of sends [TICK, AMOUNT, DESTINATION, MEMO]
    $sends = array(); 

    // Extract memo
    $memo = NULL;
    $last = count($params) - 1;
    foreach($params as $idx => $param)
        if($idx==$last && (($format==0 && $idx==4) || ($format==1 && $idx%2==0) || ($format==2 && $idx%3==1)))
            $memo = $param;

    // Build out array of sends
    $lastIdx = count($params) - 1;        
    foreach($params as $idx => $param){

        // Single Send
        if($format==0 && $idx==0)
            array_push($sends,[$params[1], $params[2], $params[3], $memo]);

        // Multi-Send (Brief)
        if($format==1 && $idx>1 && $idx%2==1)
            array_push($sends,[$params[1], $params[$idx-1], $params[$idx], $memo]);

        // Multi-Send (Full)
        if($format==2 && $idx>0 && $idx%3==1 && $idx < $lastIdx)
            array_push($sends,[$params[$idx], $params[$idx+1], $params[$idx+2], $memo]);

        // Multi-Send (Full) with Multiple Memos
        if($format==3 && $idx>0 && $idx%4==1 && $idx < $lastIdx)
            array_push($sends,[$params[$idx], $params[$idx+1], $params[$idx+2], $params[$idx+3]]);
    }

    // Get token data for every TICK (reduces duplicated sql queries)
    $ticks = [];
    foreach($sends as $send){
        $tick = $send[0];
        if(!$ticks[$tick])
            $ticks[$tick] = getTokenInfo($tick);
    }

    // Get source address balances 
    $balances = getAddressBalances($data->SOURCE);

    // Store original error value
    $origError = $error;

    // Add SOURCE address to the addresses array
    $addresses[$data->SOURCE] = 1;

    // Loop through sends and process each
    foreach($sends as $info){

        // Reset $error to the original value
        $error = $origError;

        // Copy base BTNS Transacation data object
        $send = $data;

        // Update BTNS transaction data object with send values
        $send->TICK        = $info[0];
        $send->AMOUNT      = $info[1];
        $send->DESTINATION = $info[2];
        $send->MEMO        = $info[3];

        // Get information on BTNS token
        $btInfo = $ticks[$send->TICK];

        // Set divisible flag
        $divisible = ($btInfo->DECIMALS==0) ? 0 : 1; 

        // Validate TICK exists
        if(!$error && !$btInfo)
            $error = 'invalid: TICK (unknown)';

        /*************************************************************
         * FORMAT Validations
         ************************************************************/

        // Verify AMOUNT format
        if(!$error && isset($send->AMOUNT) && !isValidAmountFormat($divisible, $send->AMOUNT))
            $error = "invalid: AMOUNT (format)";

        // Verify DESTINATION address format
        if(!$error && isset($send->DESTINATION) && !isCryptoAddress($send->DESTINATION))
            $error = "invalid: DESTINATION (format)";

        /*************************************************************
         * General Validations
         ************************************************************/

        // Verify no pipe in MEMO (BTNS uses pipe as field delimiter)
        if(!$error && strpos($send->MEMO,'|')!==false)
            $error = 'invalid: MEMO (pipe)';

        // Verify no semicolon in MEMO (BTNS uses semicolon as action delimiter)
        if(!$error && strpos($send->MEMO,';')!==false)
            $error = 'invalid: MEMO (semicolon)';

        // Verify SOURCE has enough balances to cover send AMOUNT
        if(!$error && !hasBalance($balances, $send->TICK, $send->AMOUNT))
            $error = 'invalid: insufficient funds';

        // Adjust balances to reduce by SEND AMOUNT
        if(!$error)
            $balances = debitBalances($balances, $send->TICK, $send->AMOUNT);

        // Determine final status
        $send->STATUS = $status = ($error) ? $error : 'valid';

        // Print status message 
        print "\n\t SEND : {$send->TICK} : {$send->AMOUNT} : {$send->DESTINATION} : {$send->STATUS}";

        // Create record in transfers table
        createSend($send);

        // If this was a valid transaction, then create credit/debit/balance records
        if($status=='valid'){

            // Add ticker to tickers array
            $tickers[$send->TICK] = 1; 

            // Add destination address to addresses array
            $addresses[$send->DESTINATION] = 1;

            // Debit AMOUNT from SOURCE address
            createDebit('SEND', $send->BLOCK_INDEX, $send->TX_HASH, $send->TICK, $send->AMOUNT, $data->SOURCE);

            // Credit AMOUNT to DESTINATION address
            createCredit('SEND', $send->BLOCK_INDEX, $send->TX_HASH, $send->TICK, $send->AMOUNT, $data->DESTINATION);

            // Update balances for SOURCE and DESTINATION addresses
            updateBalances([$send->SOURCE, $send->DESTINATION]);

        }
    }
}

?>