<?php
/*********************************************************************
 * airdrop.php - AIRDROP command
 *
 * PARAMS:
 * - VERSION - Broadcast Format Version
 * - TICK    - 1 to 250 characters in length
 * - AMOUNT  - Amount of tokens to airdrop
 * - LIST    - `TX_HASH` of a BTNS `LIST`
 * - MEMO    - An optional memo to include
 * 
 * FORMATS:
 * - 0 = Single Airdrop
 * - 1 = Multi-Airdrop (Brief)
 * - 2 = Multi-Airdrop (Full)
 * - 3 = Multi-Airdrop (Full) with Multiple Memos
 * 
 ********************************************************************/
function btnsAirdrop($params=null, $data=null, $error=null){
    global $mysqli, $reparse, $addresses, $tickers;

    // Define list of known FORMATS
    $formats = array(
        0 => 'VERSION|TICK|AMOUNT|LIST|MEMO',
        1 => 'VERSION|LIST|TICK|AMOUNT|TICK|AMOUNT|MEMO',
        2 => 'VERSION|TICK|AMOUNT|LIST|TICK|AMOUNT|LIST|MEMO',
        3 => 'VERSION|TICK|AMOUNT|LIST|MEMO|TICK|AMOUNT|LIST|MEMO'
    );

    /*****************************************************************
     * DEBUGGING - Force params
     ****************************************************************/
    // Single Airdrop
    // $str = '0|AIRDROPTEST1|1|fbe2a4946dfefb232571d56ed1c84dd85299736ba356dc300296d65d59991362|test'; // ADDRESS LIST
    // $str = '0|AIRDROPTEST2|1|55cd98493c0fe46aed95225d909a82793a9ba7b480dccdb3170a9cd1ce081093|test'; // TICK LIST
    // $str = '0|AIRDROPTEST3|1|afd33c2042cd43b229a44c406f03bcc940702f9736f5a222dfa53295b641a00d|test'; // ASSET LIST
    // Multi-Airdrop (brief)
    // $str = '1|fbe2a4946dfefb232571d56ed1c84dd85299736ba356dc300296d65d59991362|AIRDROPTEST1|1|AIRDROPTEST2|2|test brief';
    // Multi-Airdrop (Full)
    // $str = '2|AIRDROPTEST1|1|fbe2a4946dfefb232571d56ed1c84dd85299736ba356dc300296d65d59991362|AIRDROPTEST2|2|55cd98493c0fe46aed95225d909a82793a9ba7b480dccdb3170a9cd1ce081093|test full';
    // Multi-Airdrop (Full) w multiple memos
    // $str = '3|AIRDROPTEST1|1|fbe2a4946dfefb232571d56ed1c84dd85299736ba356dc300296d65d59991362|memo1|AIRDROPTEST2|2|55cd98493c0fe46aed95225d909a82793a9ba7b480dccdb3170a9cd1ce081093|memo2|AIRDROPTEST3|3|afd33c2042cd43b229a44c406f03bcc940702f9736f5a222dfa53295b641a00d|memo3';
    // $params = explode('|',$str);

    // Validate that broadcast format is known
    $format = getFormatVersion($params[0]);
    if(!$error && ($format===NULL || !in_array($format,array_keys($formats))))
        $error = 'invalid: VERSION (unknown)';

    // Array of airdrops [TICK, AMOUNT, LIST, MEMO]
    $airdrops = array(); 

    // Extract memo
    $memo = NULL;
    $last = count($params) - 1;
    foreach($params as $idx => $param)
        if($idx==$last && (($format==0 && $idx==4) || ($format==1 && $idx%2==0) || ($format==2 && $idx%3==1)))
            $memo = $param;

    // Build out array of airdrops
    $lastIdx = count($params) - 1;        
    foreach($params as $idx => $param){

        // Single Airdrop
        if($format==0 && $idx==0)
            array_push($airdrops,[$params[1], $params[2], $params[3], $memo]);

        // Multi-Airdrop (brief)
        if($format==1 && $idx>1 && $idx%2==1)
            array_push($airdrops,[$params[$idx-1], $params[$idx], $params[1], $memo]);

        // Multi-Airdrop (Full)
        if($format==2 && $idx>0 && $idx%3==1 && $idx < $lastIdx)
            array_push($airdrops,[$params[$idx], $params[$idx+1], $params[$idx+2], $memo]);

        // Multi-Airdrop (Full) with Multiple Memos
        if($format==3 && $idx>0 && $idx%4==1 && $idx < $lastIdx)
            array_push($airdrops,[$params[$idx], $params[$idx+1], $params[$idx+2], $params[$idx+3]]);
    }

    // Get token data for every TICK (reduces duplicated sql queries)
    $ticks = [];
    foreach($airdrops as $airdrop){
        $tick = $airdrop[0];
        if(!$ticks[$tick])
            $ticks[$tick] = getTokenInfo($tick, null, $data->BLOCK_INDEX, $data->TX_INDEX);
    }

    // Get source address balances
    $balances = getAddressBalances($data->SOURCE, null, $data->BLOCK_INDEX, $data->TX_INDEX);

    // Get SOURCE address preferences
    $preferences = getAddressPreferences($data->SOURCE, $data->BLOCK_INDEX, $data->TX_INDEX);

    // Store original error value
    $origError = $error;

    // Array of credits and debits
    $credits = [];
    $debits  = [];

    // Copy base BTNS transaction data object into fees object
    $fees = clone($data);
    $fees->TICK   = 'GAS';
    $fees->AMOUNT = 0;
    $fees->METHOD = ($preferences->FEE_PREFERENCE==1) ? 1 : 2; // 1=Destroy, 2=Donate

    // Loop through airdrops and process each
    foreach($airdrops as $info){

        // Reset $error to the original value
        $error = $origError;

        // Copy base BTNS Transacation data object
        $airdrop = clone($data);

        // Array of addresses that will receive this AIRDROP
        $recipients = [];

        // Update BTNS transaction data object with send values
        $airdrop->TICK   = $info[0];
        $airdrop->AMOUNT = $info[1];
        $airdrop->LIST   = $info[2];
        $airdrop->MEMO   = $info[3];

        // Get information on BTNS token
        $btInfo = $ticks[$airdrop->TICK];

        // Set divisible flag
        $divisible = ($btInfo->DECIMALS==0) ? 0 : 1; 

        // Validate TICK exists
        if(!$error && !$btInfo)
            $error = 'invalid: TICK (unknown)';

        /*************************************************************
         * FORMAT Validations
         ************************************************************/

        // Verify AMOUNT format
        if(!$error && isset($airdrop->AMOUNT) && !isValidAmountFormat($divisible, $airdrop->AMOUNT))
            $error = "invalid: AMOUNT (format)";

        // Verify LIST format
        if(!$error && isset($airdrop->LIST) && !isValidTransactionHash($airdrop->LIST))
            $error = "invalid: LIST (format)";

        /*************************************************************
         * General Validations
         ************************************************************/

        // Verify no pipe in MEMO (BTNS uses pipe as field delimiter)
        if(!$error && strpos($airdrop->MEMO,'|')!==false)
            $error = 'invalid: MEMO (pipe)';

        // Verify no semicolon in MEMO (BTNS uses semicolon as action delimiter)
        if(!$error && strpos($airdrop->MEMO,';')!==false)
            $error = 'invalid: MEMO (semicolon)';

        // Lookup list information
        if(!$error){
            $type = getListType($airdrop->LIST);
            $list = getList($airdrop->LIST);
        }

        // Verify LIST exist
        if(!$error && $type===false)
            $error = 'invalid: LIST (unknown)';

        // Verify LIST type is supported
        if(!$error && !in_array($type,array(1,2,3)))
            $error = 'invalid: LIST TYPE (unsupported)';

        // Handle ASSET / TICK LIST by looking up holders and adding to recipients list
        if(!$error && in_array($type,array(1,2))){
            foreach($list as $tick){
                if($type==1)
                    $holders = getHolders($tick, $data->BLOCK_INDEX, $data->TX_INDEX);
                if($type==2)
                    $holders = getAssetHolders($tick, $data->BLOCK_INDEX);
                foreach($holders as $address => $amount){
                    if(!in_array($address, $recipients))
                        array_push($recipients, $address);
                }
            }
        }

        // Handle ADDRESS LIST by passing forward addresses to recipients list
        if(!$error && $type==3)
            $recipients = $list;

        // Determine total DEBIT
        $airdrop->DEBIT = bcmul(count($recipients),$airdrop->AMOUNT,$btInfo->DECIMALS);

        // Calculate total number of database hits for this AIRDROP
        $db_hits  = count($recipients) * 2; // 1 credits, 1 balances
        $db_hits += 4;                      // 1 debits,  1 balances, 1 airdrops

        // Determine total transaction FEE based on database hits
        $airdrop->FEE_TICK   = 'GAS';
        $airdrop->FEE_AMOUNT = getTransactionFee($db_hits, $airdrop->FEE_TICK);

        // Verify SOURCE has enough balances to cover TICK total DEBIT amount
        if(!$error && !hasBalance($balances, $airdrop->TICK, $airdrop->DEBIT))
            $error = 'invalid: insufficient funds (TICK)';
    
        // Adjust balances to reduce by DEBIT amount
        if(!$error)
            $balances = debitBalances($balances, $airdrop->TICK, $airdrop->DEBIT);

        // Verify SOURCE has enough balances to cover FEE AMOUNT
        if(!$error && !hasBalance($balances, $airdrop->FEE_TICK, $airdrop->FEE_AMOUNT))
            $error = 'invalid: insufficient funds (FEE)';

        // Adjust balances to reduce by FEE amount
        if(!$error)
            $balances = debitBalances($balances, $airdrop->TICK, $airdrop->DEBIT);

        // Determine final status
        $airdrop->STATUS = $status = ($error) ? $error : 'valid';

        // Print status message 
        print "\n\t AIRDROP : {$airdrop->TICK} : {$airdrop->AMOUNT} : {$airdrop->LIST} : {$airdrop->STATUS}";

        // Create record in airdrops table
        createAirdrop($airdrop);

        // If this was a valid transaction, then add records to the credits and debits array
        if($status=='valid'){

            // Store the SOURCE and TICK in addresses list
            addAddressTicker($airdrop->SOURCE, [$airdrop->TICK, $airdrop->FEE_TICK]);

            // Debit SOURCE with total DEBIT and FEE_AMOUNT
            array_push($debits, array($airdrop->TICK,     $airdrop->DEBIT));
            array_push($debits, array($airdrop->FEE_TICK, $airdrop->FEE_AMOUNT));

            // Update FEES object with to AMOUNT
            $fees->AMOUNT = bcadd($fees->AMOUNT, $airdrop->FEE_AMOUNT, 8);

            // Handle using FEE according the the users ADDRESS preferences
            if($preferences->FEE_PREFERENCE>1){

                // Determine what address to donate to
                $address = ($preferences->FEE_PREFERENCE==2) ? DONATE_ADDRESS_1 : DONATE_ADDRESS_2;

                // Update the $fees object with the destination address
                $fees->DESTINATION = $address;

                // Store the donation ADDRESS and TICK in addresses list
                addAddressTicker($address, $airdrop->FEE_TICK);

                // Credit donation address with FEE_AMOUNT
                array_push($credits, array($airdrop->FEE_TICK, $airdrop->FEE_AMOUNT, $address));
            } 

            // Create record of FEE in `fees` table
            createFeeRecord($fees);

            // Loop through recipient addresses
            foreach($recipients as $address){

                // Store the recipient ADDRESS and TICK in addresses list
                addAddressTicker($address, $airdrop->TICK);
    
                // Credit address with TICK AMOUNT
                array_push($credits, array($airdrop->TICK, $airdrop->AMOUNT, $address));
            }
        }
    }

    // Consolidate the credit and debit records to write as few records as possible
    $debits  = consolidateCreditDebitRecords('debits', $debits);
    $credits = consolidateCreditDebitRecords('credits', $credits);

    // Create records in debits table
    foreach($debits as $debit){
        [$tick, $amount] = $debit;
        createDebit('AIRDROP', $data->BLOCK_INDEX, $data->TX_HASH, $tick, $amount, $data->SOURCE);
    }

    // Create records in credits table
    foreach($credits as $credit){
        [$tick, $amount, $destination] = $credit;
        createCredit('AIRDROP', $data->BLOCK_INDEX, $data->TX_HASH, $tick, $amount, $destination);
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