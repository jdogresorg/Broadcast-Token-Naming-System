<?php
/*********************************************************************
 * issue.php - ISSUE command
 * 
 * PARAMS:
 * - VERSION          - Broadcast Format Version
 * - TICK             - 1 to 250 characters in length
 * - MAX_SUPPLY       - Maximum token supply 
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
 * - ALLOW_LIST       - `TX_HASH` of a BTNS LIST of addresses allowed to interact with this token
 * - BLOCK_LIST       - `TX_HASH` of a BTNS LIST of addresses NOT allowed to interact with this token
 * 
 * FORMATS :
 * - 0 = Full
 * - 1 = Brief
 * - 2 = Edit MINT PARAMS
 * - 3 = Edit LOCK PARAMS
 * - 4 = Edit CALLBACK PARAMS
 * 
 ********************************************************************/
function btnsIssue( $params=null, $data=null, $error=null){
    global $mysqli, $tickers, $addresses;

    // Define list of known FORMATS
    $formats = array(
        0 => 'VERSION|TICK|MAX_SUPPLY|MAX_MINT|DECIMALS|DESCRIPTION|MINT_SUPPLY|TRANSFER|TRANSFER_SUPPLY|LOCK_SUPPLY|LOCK_MINT|LOCK_DESCRIPTION|LOCK_RUG|LOCK_SLEEP|LOCK_CALLBACK|CALLBACK_BLOCK|CALLBACK_TICK|CALLBACK_AMOUNT|ALLOW_LIST|BLOCK_LIST',
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
    // $str = "0|JDOG|1000||18";
    // $params = explode('|',$str);
    // $data->SOURCE = BURN_ADDRESS;

    // Validate that broadcast format is known
    $format = getFormatVersion($params[0]);
    if(!$error && ($format===NULL || !in_array($format,array_keys($formats))))
        $error = 'invalid: VERSION (unknown)';

    // Parse PARAMS using given VERSION format and update BTNS transaction data object
    if(!$error)
        $data = setActionParams($data, $params, $formats[$format]);

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

    // Get information on BTNS token
    $btInfo        = getTokenInfo($data->TICK);
    $isDistributed = isDistributed($data->TICK);

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
    if(!$error && isset($data->MAX_SUPPLY) && ($data->MAX_SUPPLY < MIN_TOKEN_SUPPLY || $data->MAX_SUPPLY > MAX_TOKEN_SUPPLY))
        $error = 'invalid: MAX_SUPPLY (min/max)';

    // Verify DECIMAL min/max
    if(!$error && isset($data->DECIMALS) && ($data->DECIMALS < MIN_TOKEN_DECIMALS || $data->DECIMALS > MAX_TOKEN_DECIMALS))
        $error = 'invalid: DECIMALS (min/max)';

    // Verify DECIMALS cannot be changed after supply has been issued
    if(!$error && isset($data->DECIMALS) && $btnInfo->SUPPLY > 0 && $data->DECIMALS!=$btnInfo->DECIMALS)
        $error = 'invalid: DECIMALS (locked)';

    // Verify TRANSFER addresses
    if(!$error && isset($data->TRANSFER) && !isCryptoAddress($data->TRANSFER))
        $error = 'invalid: TRANSFER (bad address)';

    // Verify TRANSFER_SUPPLY addresses
    if(!$error && isset($data->TRANSFER_SUPPLY) && !isCryptoAddress($data->TRANSFER_SUPPLY))
        $error = 'invalid: TRANSFER_SUPPLY (bad address)';

    // Verify MINT_SUPPLY is less than MAX_SUPPLY
    if(!$error && isset($data->MINT_SUPPLY) && $data->MINT_SUPPLY > $data->MAX_SUPPLY)
        $error = 'invalid: MINT_SUPPLY > MAX_SUPPLY';

    // Verify MAX_SUPPLY can not be changed if LOCK_SUPPLY is enabled
    if(!$error && $btInfo && $btnInfo->LOCK_SUPPLY && isset($data->MAX_SUPPLY) && $data->MAX_SUPPLY!=$btnInfo->MAX_SUPPLY)
        $error = 'invalid: MAX_SUPPLY (locked)';

    // Verify MAX_MINT can not be changed if LOCK_MINT is enabled
    if(!$error && $btInfo && $btnInfo->LOCK_MINT && isset($data->MAX_MINT) && $data->MAX_MINT!=$btnInfo->MAX_MINT)
        $error = 'invalid: MAX_MINT (locked)';

    // Verify DESCRIPTION can not be changed if LOCK_DESCRIPTION is enabled
    if(!$error && $btInfo && $btnInfo->LOCK_DESCRIPTION && isset($data->MAX_MINT) && $data->MAX_MINT!=$btnInfo->MAX_MINT)
        $error = 'invalid: MAX_MINT (locked)';

    // Verify CALLBACK_BLOCK can not be changed if LOCK_CALLBACK is enabled
    if(!$error && $btInfo && $btnInfo->LOCK_CALLBACK && isset($data->CALLBACK_BLOCK) && $data->CALLBACK_BLOCK!=$btnInfo->CALLBACK_BLOCK)
        $error = 'invalid: CALLBACK_BLOCK (locked)';

    // Verify CALLBACK_TICK can not be changed if LOCK_CALLBACK is enabled
    if(!$error && $btInfo && $btnInfo->LOCK_CALLBACK && isset($data->CALLBACK_TICK) && $data->CALLBACK_TICK!=$btnInfo->CALLBACK_TICK)
        $error = 'invalid: CALLBACK_TICK (locked)';

    // Verify CALLBACK_TICK can not be changed if LOCK_CALLBACK is enabled
    if(!$error && $btInfo && $btnInfo->LOCK_CALLBACK && isset($data->CALLBACK_AMOUNT) && $data->CALLBACK_AMOUNT!=$btnInfo->CALLBACK_AMOUNT)
        $error = 'invalid: CALLBACK_AMOUNT (locked)';

    // Verify CALLBACK_BLOCK only increases in value
    if(!$error && $btInfo && isset($data->CALLBACK_BLOCK) && $data->CALLBACK_BLOCK < $btnInfo->CALLBACK_BLOCK)
        $error = 'invalid: CALLBACK_BLOCK (decreased)';

    // Verify CALLBACK_BLOCK is greater than current block index
    if(!$error && $btInfo && isset($data->CALLBACK_BLOCK) && $data->CALLBACK_BLOCK > $data->BLOCK_INDEX)
        $error = 'invalid: CALLBACK_BLOCK (block index)';

    // Verify CALLBACK_TICK can not be changed if supply is distributed
    if(!$error && isset($data->CALLBACK_TICK) && $data->CALLBACK_TICK!=$btnInfo->CALLBACK_TICK && $isDistributed)
        $error = 'invalid: CALLBACK_TICK (supply distributed)';

    // Verify CALLBACK_AMOUNT can not be changed if supply is distributed
    if(!$error && isset($data->CALLBACK_AMOUNT) && $data->CALLBACK_AMOUNT!=$btnInfo->CALLBACK_AMOUNT && $isDistributed)
        $error = 'invalid: CALLBACK_AMOUNT (supply distributed)';

    // Verify MINT_ALLOW_LIST is a valid list of addresses
    if(!$error && isset($data->ALLOW_LIST) && !isValidList($data->ALLOW_LIST,3))
        $error = 'invalid: ALLOW_LIST (bad list)';

    // Verify MINT_BLOCK_LIST is a valid list of addresses
    if(!$error && isset($data->BLOCK_LIST) && !isValidList($data->BLOCK_LIST,3))
        $error = 'invalid: BLOCK_LIST (bad list)';

    // Determine final status
    $data->STATUS = $status = ($error) ? $error : 'valid';

    // Print status message 
    print "\n\t ISSUE : {$data->TICK} : {$data->STATUS}";

    // Create record in issues table
    createIssue($data);

    // If this was a valid transaction, then create the token record, and perform any additional actions
    if($status=='valid'){

        // Add the ticker to the tickers array
        $tickers[$data->TICK] = 1;

        // Add any addresses to the addresses array
        $addresses[$data->SOURCE] = 1;
        if($data->TRANSFER)
            $addresses[$data->TRANSFER] = 1;
        if($data->TRANSFER_SUPPLY)
            $addresses[$data->TRANSFER_SUPPLY] = 1;

        // Set some properties before we create the token
        $data->SUPPLY = ($data->MINT_SUPPLY) ? $data->MINT_SUPPLY : 0;
        $data->OWNER  = ($transfer) ? $transfer : $data->SOURCE;

        // Create/Update record in tokens table
        createToken($data);

        // Credit MINT_SUPPLY to source address
        if($data->MINT_SUPPLY)
            createCredit('ISSUE', $data->BLOCK_INDEX, $data->TX_HASH, $data->TICK, $data->MINT_SUPPLY, $data->SOURCE);

        // Transfer MINT_SUPPLY to TRANSFER_SUPPLY address
        if($data->TRANSFER_SUPPLY){
            createDebit('ISSUE', $data->BLOCK_INDEX, $data->TX_HASH, $data->TICK, $data->MINT_SUPPLY, $data->TRANSFER);
            createCredit('ISSUE', $data->BLOCK_INDEX, $data->TX_HASH, $data->TICK, $data->MINT_SUPPLY, $data->TRANSFER_SUPPLY);
        }

        // Update balances for addresses
        updateBalances([$data->SOURCE, $data->TRANSFER_SUPPLY]);
    }    

}
