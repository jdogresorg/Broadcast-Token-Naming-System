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
 * - MINT_ALLOW_LIST  - `TX_HASH of a BTNS LIST of addresses to allow minting from
 * - MINT_BLOCK_LIST  - `TX_HASH of a BTNS LIST of addresses to NOT allow minting from
 * 
 * FORMATS:
 * 0 = VERSION|TICK|MAX_SUPPLY|MAX_MINT|DECIMALS|DESCRIPTION|MINT_SUPPLY|TRANSFER|TRANSFER_SUPPLY|LOCK_SUPPLY|LOCK_MINT|LOCK_DESCRIPTION|LOCK_RUG|LOCK_SLEEP|LOCK_CALLBACK|CALLBACK_BLOCK|CALLBACK_TICK|CALLBACK_AMOUNT|MINT_ALLOW_LIST|MINT_BLOCK_LIST
 ********************************************************************/
function btnsIssue( $params=null, $data=null, $error=null){
    global $mysqli;

    /*
     * Broadcast Formats
     */
    $formats = array();
    $formats[0] = 'ISSUE|VERSION|TICK|MAX_SUPPLY|MAX_MINT|DECIMALS|DESCRIPTION|MINT_SUPPLY|TRANSFER|TRANSFER_SUPPLY|LOCK_SUPPLY|LOCK_MINT|LOCK_DESCRIPTION|LOCK_RUG|LOCK_SLEEP|LOCK_CALLBACK|CALLBACK_BLOCK|CALLBACK_TICK|CALLBACK_AMOUNT|MINT_ALLOW_LIST|MINT_BLOCK_LIST';

    // Validate that broadcast format is 
    $format = $params[0];


    // Add ACTION specific params to transaction data
    $data->VERSION          = $params[0];            // Format version (default 0) 
    $data->TICK             = $params[1];           // 1 to 250 characters in length (see rules below ) (required)
    $data->MAX_SUPPLY       = (string) $params[2];  // Maximum token supply 
    $data->MAX_MINT         = (string) $params[3];  // Maximum amount of supply a MINT transaction can issue
    $data->DECIMALS         = $params[4];           // Number of decimal places token should have (max: 18, default: 0
    $data->DESCRIPTION      = $params[5];           // Description of token (250 chars max) 
    $data->MINT_SUPPLY      = (string) $params[6];  // Amount of token supply to mint in immediately (default:0)
    $data->TRANSFER         = $params[7];           // Address to transfer ownership of the token to (owner can perform future actions on token)
    $data->TRANSFER_SUPPLY  = $params[8];           // Address to transfer MINT_SUPPLY to (mint initial supply and transfer to address)
    $data->LOCK_SUPPLY      = $params[9];           // Lock `MAX_SUPPLY` permanently (cannot increase `MAX_SUPPLY`)
    $data->LOCK_MINT        = $params[10];          // Lock `MAX_MINT` permanently (cannot edit `MAX_MINT`)
    $data->LOCK_DESCRIPTION = $params[11];          // Lock `token` against `DESCRIPTION` changes
    $data->LOCK_RUG         = $params[12];          // Lock `token` against `RUG` command
    $data->LOCK_SLEEP       = $params[13];          // Lock `token` against `SLEEP` command
    $data->LOCK_CALLBACK    = $params[14];          // Lock `token` `CALLBACK` info
    $data->CALLBACK_BLOCK   = $params[15];          // Enable `CALLBACK` command after `CALLBACK_BLOCK` 
    $data->CALLBACK_TICK    = $params[16];          // `TICK` `token` users get when `CALLBACK` command is used
    $data->CALLBACK_AMOUNT  = $params[17];          // `TICK` `token` amount that users get when `CALLBACK` command is used
    $divisible = ($data->DECIMALS==0) ? 0 : 1;
    [$supply_int, $supply_sats] = explode('.',$data->MAX_SUPPLY);
    [$max_int,    $max_sats]    = explode('.',$data->MAX_MINT);
    [$mint_int,   $mint_sats]   = explode('.',$data->MINT_SUPPLY);



    /*
     * TICK Validations
     */

    // Verify length is 1-250 chars (BTNS)
    // $max = 250
    // if(!$error && (strlen($ticker)<1 || strlen($ticker)>250))
    //     $error = 'invalid: TICK (length)';

    // // Verify no pipe in TICK (BTNS uses pipe as field delimiter)
    // if(!$error && strpos($ticker,'|')!==false)
    //     $error = 'invalid: TICK (pipe)';

    // // Verify no semicolon in TICK (BTNS uses semicolon as action delimiter)
    // if(!$error && strpos($ticker,';')!==false)
    //     $error = 'invalid: TICK (semicolon)';


    /*
     * FORMAT Validations
     */

    // Verify MAX_SUPPLY format
    if(!$error && isset($data->MAX_SUPPLY) && (!is_numeric($supply_int)||($divisible && !is_numeric($supply_sats))))
        $error = 'invalid: MAX_SUPPLY (format)';
    // Verify MAX_MINT format
    if(!$error && isset($data->MAX_MINT) && (!is_numeric($max_int)||($divisible && !is_numeric($max_sats))))
        $error = 'invalid: MAX_MINT (format)';
    // Verify DECIMALS format (required)
    if(!$error && isset($data->DECIMALS)){
        $dec = $data->DECIMALS;
        if(!is_numeric($dec)||$dec<0||$dec>18)
            $error = 'invalid: DECIMALS (format)';
    }
    // Verify MINT_SUPPLY format
    if(!$error && $data->MAX_MINT && (!is_numeric($supply_int)||($divisible && !is_numeric($supply_sat))))
        $error = 'invalid: MINT_SUPPLY (format)';


    /* 
     * LOCK Validations
     */

    // Verify LOCK_SUPPLY can not be changed once enabled
    // Verify LOCK_MINT can not be changed once enabled
    // Verify LOCK_DESCRIPTION can not be changed once enabled
    // Verify LOCK_RUG can not be changed once enabled
    // Verify LOCK_SLEEP can not be changed once enabled
    // Verify LOCK_CALLBACK can not be changed once enabled

    /* 
     * General Validations
     */ 

    // Verify TRANSFER and TRANSFER_SUPPLY addresses in a lose way (26-35=P2PKH, 42=Segwit)
    $len = strlen($data->TRANSFER);
    if(!$error && (($len>=26 && $len<=35)||$len==42))
        $error = 'invalid: TRANSFER (bad address)';
    $len = strlen($data->TRANSFER_SUPPLY);
    if(!$error && (($len>=26 && $len<=35)||$len==42))
        $error = 'invalid: TRANSFER_SUPPLY (bad address)';
    // Verify MAX_SUPPLY is within limits (0-18,446,744,073,709,551,615)
    if(!$error && ($supply_int<1 || $supply_int>18446744073709551615))
        $error = 'invalid: MAX_SUPPLY (min/max)';
    // Verify MINT_SUPPLY is less than MAX_SUPPLY
    if(!$error && ($data->MINT_SUPPLY > $data->MAX_SUPPLY))
        $error = 'invalid: MINT_SUPPLY > MAX_SUPPLY';
    // Verify TICK DEPLOY is new, or done by current owner
    if(!$error){
        $tx_hash_id = createTransaction($data->TX_HASH);
        $results2 = $mysqli->query("SELECT id FROM tokens WHERE tick_id='{$tick_id}' AND owner_id!='{$source_id}'");
        if($results2 && $results2->num_rows)
            $error = 'invalid: issued by another address';
    }
    // Verify MAX_SUPPLY can not be changed if LOCK_SUPPLY is enabled
    // Verify MINT_SUPPLY can not be changed if LOCK_MINT is enabled
    // Verify DECIMALS can not be changed after supply is issued
    // Verify DESCRIPTION can not be changed if LOCK_DESCRIPTION is enabled

    // Verify CALLBACK_TICK can not be changed if supply has been distributed
    // Verify CALLBACK_AMOUNT can not be changed if supply has been distributed

    // Verify CALLBACK_BLOCK is greater than current block
    // Verify CALLBACK_BLOCK only increases in value

    // Determine final status
    $data->STATUS = $status = ($error) ? $error : 'valid';
    // Print status message 
    print "\n\t ISSUE : {$data->TICK} : {$data->STATUS}";
    // Create record in issuances table
    createIssuance($data);
    // If this was a valid transaction, then create the token record, and perform any additional actions
    if($status=='valid'){
        // Add any addresses to the addresses array
        if($data->TRANSFER)
            $addresses[$data->TRANSFER] = 1;
        if($data->TRANSFER_SUPPLY)
            $addresses[$data->TRANSFER_SUPPLY] = 1;
        // Set some properties before we create the token
        $data->SUPPLY = ($data->MINT_SUPPLY) ? $data->MINT_SUPPLY : 0;
        $data->OWNER  = ($transfer) ? $transfer : $data->SOURCE;
        // Create record in tokens table
        createToken($data);
        // Credit MINT_SUPPLY to source address
        if($data->MINT_SUPPLY)
            createCredit('DEPLOY', $data->BLOCK_INDEX, $data->TX_HASH, $data->TICK, $data->MINT_SUPPLY, $data->SOURCE);
        // Transfer MINT_SUPPLY to TRANSFER_SUPPLY address
        if($data->TRANSFER_SUPPLY){
            createDebit('DEPLOY', $data->BLOCK_INDEX, $data->TX_HASH, $data->TICK, $data->MINT_SUPPLY, $data->TRANSFER);
            createCredit('DEPLOY', $data->BLOCK_INDEX, $data->TX_HASH, $data->TICK, $data->MINT_SUPPLY, $data->TRANSFER_SUPPLY);
        }
        // Update balances for addresses
        updateBalances([$data->SOURCE, $data->TRANSFER_SUPPLY]);
    }    
}
