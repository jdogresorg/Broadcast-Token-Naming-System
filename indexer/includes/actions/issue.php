<?php
/*********************************************************************
 * issue.php - ISSUE command
 * 
 * PARAMS:
 * - VERSION          - Broadcast Format Version
 * - TICK             - 1 to 250 characters in length
 * - MAX_SUPPLY       - Maximum token supply (max: 18,446,744,073,709,551,615 - commas not allowed)
 * - MAX_MINT         - Maximum amount of supply a `MINT` transaction can issue
 * - DECIMALS         - Number of decimal places token should have (max: 18, default: 0)
 * - DESCRIPTION      - Description of token (250 chars max) 
 * - MINT_SUPPLY      - Amount of token supply to mint in immediately (default:0)
 * - TRANSFER         - Address to transfer ownership of the `token` to (owner can perform future actions on token)
 * - TRANSFER_SUPPLY  - Address to transfer `MINT_SUPPLY` to (mint initial supply and transfer to address)
 * - LOCK_SUPPLY      - Lock `MAX_SUPPLY` permanently (cannot increase `MAX_SUPPLY`)
 * - LOCK_MINT        - Lock `MAX_MINT` permanently (cannot edit `MAX_MINT`)
 * - LOCK_DESCRIPTION - Lock `token` against `DESCRIPTION` changes
 * - LOCK_RUG         - Lock `token` against `RUG` command
 * - LOCK_SLEEP       - Lock `token` against `SLEEP` command
 * - LOCK_CALLBACK    - Lock `token` `CALLBACK` info
 * - CALLBACK_BLOCK   - Enable `CALLBACK` command after `CALLBACK_BLOCK` 
 * - CALLBACK_TICK    - `TICK` `token` users get when `CALLBACK` command is used
 * - CALLBACK_AMOUNT  - `TICK` `token` amount that users get when `CALLBACK` command is used
 * - MINT_ALLOW_LIST  - `TX_HASH` of a BTNS LIST of addresses to allow minting from
 * - MINT_BLOCK_LIST  - `TX_HASH` of a BTNS LIST of addresses to NOT allow minting from
 * 
 * FORMATS :
 * - 0 - Full
 * - 1 - Brief
 * - 2 - Edit MINT PARAMS
 * - 3 - Edit LOCK PARAMS
 * - 4 - Edit CALLBACK PARAMS
 * 
 ********************************************************************/
function btnsIssue( $params=null, $data=null, $error=null){
    global $mysqli;

    // Define list of known FORMATS
    $formats = array(
        0 => 'VERSION|TICK|MAX_SUPPLY|MAX_MINT|DECIMALS|DESCRIPTION|MINT_SUPPLY|TRANSFER|TRANSFER_SUPPLY|LOCK_SUPPLY|LOCK_MINT|LOCK_DESCRIPTION|LOCK_RUG|LOCK_SLEEP|LOCK_CALLBACK|CALLBACK_BLOCK|CALLBACK_TICK|CALLBACK_AMOUNT|MINT_ALLOW_LIST|MINT_BLOCK_LIST',
        1 => 'VERSION|TICK|DESCRIPTION',
        2 => 'VERSION|TICK|MAX_MINT|MINT_SUPPLY|TRANSFER_SUPPLY',
        3 => 'VERSION|TICK|LOCK_SUPPLY|LOCK_MINT|LOCK_DESCRIPTION|LOCK_RUG|LOCK_SLEEP|LOCK_CALLBACK',
        4 => 'VERSION|TICK|LOCK_CALLBACK|CALLBACK_BLOCK|CALLBACK_TICK'
    );

    // Define list of AMOUNT and LOCK fields (used in validations)
    $fieldList = array(
        'AMOUNT' => array('MAX_SUPPLY','MAX_MINT','MINT_SUPPLY','CALLBACK_AMOUNT'),
        'LOCK'   => array('LOCK_SUPPLY', 'LOCK_MINT', 'LOCK_DESCRIPTION', 'LOCK_RUG', 'LOCK_SLEEP', 'LOCK_CALLBACK')
    );

    /*****************************************************************
     * DEBUGGING - Force params
     ****************************************************************/
    $str = "0|JDOG|1000||18";
    $params = explode('|',$str);
    $data->SOURCE = BURN_ADDRESS;

    // Validate that broadcast format is known
    $format = getFormatVersion($params[0]);
    if(!$error && ($format===NULL || !in_array($format,array_keys($formats))))
        $error = 'invalid: VERSION (unknown)';

    // Parse `PARAMS` using given format and update BTNS transaction data object
    if(!$error){
        $fields = explode('|',$formats[$format]);
        foreach($fields as $idx => $field)
            $data->{$field} = (strlen($params[$idx])!=0) ? $params[$idx] : NULL;
    }

    // Decode any base64 tickers (come back through and clean this up)
    // if(isBase64($data->TICK))
    //     $data->TICK = base64_decode($data->TICK);

    /*****************************************************************
     * TICK Validations
     ****************************************************************/

    // Verify length is within acceptable range
    if(!$error && (strlen($data->TICK) < MIN_TICK_LENGTH || strlen($data->TICK) > MAX_TICK_LENGTH))
        $error = 'invalid: TICK (length)';

    // Verify no pipe in TICK (BTNS uses pipe as field delimiter)
    if(!$error && strpos($data->TICK,'|')!==false)
        $error = 'invalid: TICK (pipe)';

    // Verify no semicolon in TICK (BTNS uses semicolon as action delimiter)
    if(!$error && strpos($data->TICK,';')!==false)
        $error = 'invalid: TICK (semicolon)';

    // Verify TICK is not on RESERVED_TICKS list
    if(!$error && in_array($data->TICK,RESERVED_TICKS))        
        $error = 'invalid: TICK (reserved)';

    // Get BTNS information on ticker
    $btInfo = getTokenInfo($data->TICK);

    // If BTNS Token does not exist yet, do some additional validations
    if(!$btInfo){
        $cpInfo = getAssetInfo($data->TICK);

        // Verify TICK is not already registered on Counterparty by a different address
        if(!$error && $cpInfo && $cpInfo->OWNER!=$data->SOURCE)
            $error = 'invalid: TICK (reserved asset)';

        // Verify TICK is not a reserved subasset on Counterparty
        if(!$error && strpos($data->TICK,'.')!==false){
            [$asset, $subasset] = explode('.',$data->TICK);
            $cpInfo = getAssetInfo($asset);
            if($cpInfo && $cpInfo->OWNER!=$data->SOURCE)
                $error = 'invalid: TICK (reserved subasset)';
        }
    }

    /*****************************************************************
     * FORMAT Validations
     ****************************************************************/

    // Set divisible first based on if token exist, if not, use DECIMALS in request
    $divisible = ($data->DECIMALS==0) ? 0 : 1;
    if($btInfo)
        $divisible = ($btInfo->DECIMALS==0) ? 0 : 1; 

    // Verify AMOUNT field formats
    foreach($fieldList['AMOUNT'] as $name){
        $value = $data->{$name};
        if(!$error && isset($value) && !isValidAmountFormat($divisible, $value))
            $error = "invalid: {$name} (format)";
    }

    // Verify LOCK field formats
    foreach($fieldList['LOCK'] as $name){
        $value = $data->{$name};
        if(!$error && isset($value) && !isValidLockValue($value))
            $error = "invalid: {$name} (format)";
    }

    /*****************************************************************
     * General Validations
     ****************************************************************/

    // Verify ISSUE is coming from TICK owner
    if(!$error && $btInfo && $btInfo->OWNER!=$data->SOURCE)
        $error = 'invalid: issued by another address';

    // Verify LOCK fields cannot be changed once enabled/locked
    foreach($fieldList['LOCK'] as $name){
        $value = $data->{$name};
        if(!$error && isset($value) && !isValidLock($btInfo, $data, $name))
            $error = "invalid: {$name} (locked)";
    }

    // Verify MAX_SUPPLY min/max

    // Verify no SUPPLY has been issued if trying to change DECIMALS

    // Verify DECIMAL min/max
    // if(!$error && isset($data->DECIMALS)){
    //     $dec = $data->DECIMALS;
    //     if($dec<0||$dec>18)
    //         $error = 'invalid: DECIMALS (min/max)';
    // }

    // // Verify TRANSFER addresses
    // $len = strlen($data->TRANSFER);
    // if(!$error && (($len>=26 && $len<=35)||$len==42))
    //     $error = 'invalid: TRANSFER (bad address)';
    // // Verify TRANSFER_SUPPLY addresses
    // $len = strlen($data->TRANSFER_SUPPLY);
    // if(!$error && (($len>=26 && $len<=35)||$len==42))
    //     $error = 'invalid: TRANSFER_SUPPLY (bad address)';
    // // Verify MAX_SUPPLY is within limits (0-18,446,744,073,709,551,615)
    // if(!$error && ($supply_int<1 || $supply_int>18446744073709551615))
    //     $error = 'invalid: MAX_SUPPLY (min/max)';
    // // Verify MINT_SUPPLY is less than MAX_SUPPLY
    // if(!$error && ($data->MINT_SUPPLY > $data->MAX_SUPPLY))
    //     $error = 'invalid: MINT_SUPPLY > MAX_SUPPLY';
    // // Verify TICK DEPLOY is new, or done by current owner
    // if(!$error){
    //     $tx_hash_id = createTransaction($data->TX_HASH);
    //     $results2 = $mysqli->query("SELECT id FROM tokens WHERE tick_id='{$tick_id}' AND owner_id!='{$source_id}'");
    //     if($results2 && $results2->num_rows)
    //         $error = 'invalid: issued by another address';
    // }
    // // Verify MAX_SUPPLY can not be changed if LOCK_SUPPLY is enabled
    // // Verify MINT_SUPPLY can not be changed if LOCK_MINT is enabled
    // // Verify DECIMALS can not be changed after supply is issued
    // // Verify DESCRIPTION can not be changed if LOCK_DESCRIPTION is enabled

    // // Verify CALLBACK_TICK can not be changed if supply has been distributed
    // // Verify CALLBACK_AMOUNT can not be changed if supply has been distributed

    // // Verify CALLBACK_BLOCK is greater than current block
    // // Verify CALLBACK_BLOCK only increases in value

// Debug 
if($error)
    bye($error);

    // // Determine final status
    // $data->STATUS = $status = ($error) ? $error : 'valid';
    // // Print status message 
    // print "\n\t ISSUE : {$data->TICK} : {$data->STATUS}";
    // // Create record in issuances table
    // createIssuance($data);
    // // If this was a valid transaction, then create the token record, and perform any additional actions
    // if($status=='valid'){
    //     // Add any addresses to the addresses array
    //     if($data->TRANSFER)
    //         $addresses[$data->TRANSFER] = 1;
    //     if($data->TRANSFER_SUPPLY)
    //         $addresses[$data->TRANSFER_SUPPLY] = 1;
    //     // Set some properties before we create the token
    //     $data->SUPPLY = ($data->MINT_SUPPLY) ? $data->MINT_SUPPLY : 0;
    //     $data->OWNER  = ($transfer) ? $transfer : $data->SOURCE;
    //     // Create record in tokens table
    //     createToken($data);
    //     // Credit MINT_SUPPLY to source address
    //     if($data->MINT_SUPPLY)
    //         createCredit('DEPLOY', $data->BLOCK_INDEX, $data->TX_HASH, $data->TICK, $data->MINT_SUPPLY, $data->SOURCE);
    //     // Transfer MINT_SUPPLY to TRANSFER_SUPPLY address
    //     if($data->TRANSFER_SUPPLY){
    //         createDebit('DEPLOY', $data->BLOCK_INDEX, $data->TX_HASH, $data->TICK, $data->MINT_SUPPLY, $data->TRANSFER);
    //         createCredit('DEPLOY', $data->BLOCK_INDEX, $data->TX_HASH, $data->TICK, $data->MINT_SUPPLY, $data->TRANSFER_SUPPLY);
    //     }
    //     // Update balances for addresses
    //     updateBalances([$data->SOURCE, $data->TRANSFER_SUPPLY]);
    // }    
}
