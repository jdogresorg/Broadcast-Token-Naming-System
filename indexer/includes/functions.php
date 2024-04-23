<?php
/*********************************************************************
 * functions.php - Common functions
 ********************************************************************/

// Handle generating random strings of various lengths
function randString($length = 32){
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[random_int(0, $charactersLength - 1)];
    }
    return $randomString;
}

// Handle creating a lockfile and bailing out if lock file already exists (ie, an instance is already running)
function createLockFile($file=null){
    $lockFile = ($file!='') ? $file : LOCKFILE;
    if(file_exists($lockFile)){
        print "detected lockfile at {$lockFile} ... exiting\n";
        exit;
    } else {
        // Write a lockfile so we prevent other runs while we are running
        file_put_contents($lockFile, 1);
    }
}

// Handle removing a lockfile
function removeLockFile($file=null){
    $lockFile = ($file!='') ? $file : LOCKFILE;
    if(file_exists($lockFile))
        unlink($lockFile);
}

// Simple function to print message and exit
function bye($msg=null){
    print $msg . "\n";
    exit;
}

// Print error, log it to a file, and exit
function byeLog($error=null, $log=null){
    $logFile   = (strlen($log)) ? $log : ERRORLOG;
    $errorLine = '[' . gmdate("Y-m-d H:i:s") . ' UTC] - '. $error . "\n";
    if(strlen($logFile))
        file_put_contents($logFile, $errorLine, FILE_APPEND);
    print $errorLine;
    // Try to remove the lockfile, so we can continue running next time
    removeLockFile();
    exit;
}

// Setup database connection
function initDB($database=DB_DATA, $hostname=DB_HOST, $username=DB_USER, $password=DB_PASS){
    global $mysqli;
    $mysqli = new mysqli($hostname, $username, $password, $database);
    if($mysqli->connect_errno){
        $msg = 'Database Connection Failure: ' . $mysqli->connect_error;
        bye($msg);
    }
}

// Handle checking if a string is possibly base64 encoded
function isBase64($str){
    $decoded_str = base64_decode($str);
    $Str1 = preg_replace('/[\x00-\x1F\x7F-\xFF]/', '', $decoded_str);
    if ($Str1!=$decoded_str || $Str1 == '')
        return false;
    return true;
}

// Create records in the 'index_addresses' table and return record id
function createAddress( $address=null ){
    global $mysqli;
    if(!isset($address) || $address=='')
        return 0;
    $address = $mysqli->real_escape_string($address);
    $results = $mysqli->query("SELECT id FROM index_addresses WHERE address='{$address}' LIMIT 1");
    if($results){
        if($results->num_rows){
            $row = $results->fetch_assoc();
            return $row['id'];
        } else {
            $results = $mysqli->query("INSERT INTO index_addresses (`address`) values ('{$address}')");
            if($results){
                return $mysqli->insert_id;
            } else {
                byeLog('Error while trying to create record in index_addresses table');
            }
        }
    } else {
        byeLog('Error while trying to lookup record in index_addresses table');
    }
}

// Create records in the 'index_transactions' table and return record id
function createTransaction( $hash=null ){
    global $mysqli;
    if(!isset($hash) || $hash=='')
        return 0;
    $hash    = $mysqli->real_escape_string($hash);
    $results = $mysqli->query("SELECT id FROM index_transactions WHERE `hash`='{$hash}' LIMIT 1");
    if($results){
        if($results->num_rows){
            $row = $results->fetch_assoc();
            return $row['id'];
        } else {
            $results = $mysqli->query("INSERT INTO index_transactions (`hash`) values ('{$hash}')");
            if($results){
                return $mysqli->insert_id;
            } else {
                byeLog('Error while trying to create record in index_transactions table');
            }
        }
    } else {
        byeLog('Error while trying to lookup record in index_transactions table');
    }
}

// Create records in the 'index_ticks' table and return record id
function createTicker( $tick=null ){
    global $mysqli;
    if(!isset($tick) || $tick=='')
        return 0;
    // Truncate description to 250 chars 
    $tick    = substr($tick,0,250);
    $tick    = $mysqli->real_escape_string($tick);
    $results = $mysqli->query("SELECT id FROM index_tickers WHERE tick='{$tick}' LIMIT 1");
    if($results){
        if($results->num_rows){
            $row = $results->fetch_assoc();
            return $row['id'];
        } else {
            $results = $mysqli->query("INSERT INTO index_tickers (`tick`) values ('{$tick}')");
            if($results){
                return $mysqli->insert_id;
            } else {
                byeLog('Error while trying to create record in index_tickers table');
            }
        }
    } else {
        byeLog('Error while trying to lookup record in index_tickers table');
    }
}

// Create records in the 'index_statuses' table and return record id
function createStatus( $status=null ){
    global $mysqli;
    $status  = $mysqli->real_escape_string($status);
    $results = $mysqli->query("SELECT id FROM index_statuses WHERE `status`='{$status}' LIMIT 1");
    if($results){
        if($results->num_rows){
            $row = $results->fetch_assoc();
            return $row['id'];
        } else {
            $results = $mysqli->query("INSERT INTO index_statuses (`status`) values ('{$status}')");
            if($results){
                return $mysqli->insert_id;
            } else {
                byeLog('Error while trying to create record in index_statuses table');
            }
        }
    } else {
        byeLog('Error while trying to lookup record in index_statuses table');
    }
}

// Create records in the 'index_memos' table and return record id
function createMemo( $memo=null ){
    global $mysqli;
    $memo  = $mysqli->real_escape_string($memo);
    $results = $mysqli->query("SELECT id FROM index_memos WHERE memo='{$memo}' LIMIT 1");
    if($results){
        if($results->num_rows){
            $row = $results->fetch_assoc();
            return $row['id'];
        } else {
            $results = $mysqli->query("INSERT INTO index_memos (memo) values ('{$memo}')");
            if($results){
                return $mysqli->insert_id;
            } else {
                byeLog('Error while trying to create record in index_memos table');
            }
        }
    } else {
        byeLog('Error while trying to lookup record in index_memos table');
    }
}

// Create records in the 'index_actions' table and return record id
function createAction( $action=null ){
    global $mysqli;
    $action  = $mysqli->real_escape_string($action);
    $results = $mysqli->query("SELECT id FROM index_actions WHERE action='{$action}' LIMIT 1");
    if($results){
        if($results->num_rows){
            $row = $results->fetch_assoc();
            return $row['id'];
        } else {
            $results = $mysqli->query("INSERT INTO index_actions (action) values ('{$action}')");
            if($results){
                return $mysqli->insert_id;
            } else {
                byeLog('Error while trying to create record in index_actions table');
            }
        }
    } else {
        byeLog('Error while trying to lookup record in index_actions table');
    }
}

// Create record in `issues` table
function createIssue( $data=null ){
    global $mysqli;
    // Define list of LOCK fields
    $locks = array(
        'LOCK_MAX_SUPPLY',
        'LOCK_MINT',
        'LOCK_MINT_SUPPLY',
        'LOCK_MAX_MINT',
        'LOCK_DESCRIPTION',
        'LOCK_RUG',
        'LOCK_SLEEP',
        'LOCK_CALLBACK'
    );
    // Unset any LOCK fields with invalid values
    foreach($locks as $lock)
        if(!in_array($data->{$lock},array(0,1)))
            unset($data->{$lock});
    // Unset DECIMALS if it is outside of the acceptable range
    if(isset($data->DECIMALS) && ($data->DECIMALS < MIN_TOKEN_DECIMALS || $data->DECIMALS > MAX_TOKEN_DECIMALS))
        unset($data->DECIMALS);
    // Make data safe for use in SQL queries
    $description        = $mysqli->real_escape_string(substr($data->DESCRIPTION,0,250)); // Truncate description to 250 chars 
    $max_supply         = $mysqli->real_escape_string($data->MAX_SUPPLY);
    $max_mint           = $mysqli->real_escape_string($data->MAX_MINT);
    $mint_supply        = $mysqli->real_escape_string($data->MINT_SUPPLY);
    $mint_address_max   = $mysqli->real_escape_string($data->MINT_ADDRESS_MAX);
    $mint_start_block   = $mysqli->real_escape_string($data->MINT_START_BLOCK);
    $mint_stop_block    = $mysqli->real_escape_string($data->MINT_STOP_BLOCK);
    $decimals           = $mysqli->real_escape_string($data->DECIMALS);
    $block_index        = $mysqli->real_escape_string($data->BLOCK_INDEX);
    $tx_index           = $mysqli->real_escape_string($data->TX_INDEX);
    $status             = $mysqli->real_escape_string($data->STATUS);
    $lock_max_supply    = $mysqli->real_escape_string($data->LOCK_MAX_SUPPLY);
    $lock_mint          = $mysqli->real_escape_string($data->LOCK_MINT);
    $lock_mint_supply   = $mysqli->real_escape_string($data->LOCK_MINT_SUPPLY);
    $lock_max_mint      = $mysqli->real_escape_string($data->LOCK_MAX_MINT);
    $lock_description   = $mysqli->real_escape_string($data->LOCK_DESCRIPTION);
    $lock_rug           = $mysqli->real_escape_string($data->LOCK_RUG);
    $lock_sleep         = $mysqli->real_escape_string($data->LOCK_SLEEP);
    $lock_callback      = $mysqli->real_escape_string($data->LOCK_CALLBACK);
    $callback_block     = $mysqli->real_escape_string($data->CALLBACK_BLOCK);
    $callback_amount    = $mysqli->real_escape_string($data->CALLBACK_AMOUNT);
    $callback_tick_id   = createTicker($data->CALLBACK_TICK);
    $tick_id            = createTicker($data->TICK);
    $source_id          = createAddress($data->SOURCE);
    $transfer_id        = createAddress($data->TRANSFER);
    $transfer_supply_id = createAddress($data->TRANSFER_SUPPLY);
    $tx_hash_id         = createTransaction($data->TX_HASH);
    $allow_list_id      = createTransaction($data->ALLOW_LIST);
    $block_list_id      = createTransaction($data->BLOCK_LIST);
    $tx_index           = $data->TX_INDEX;
    $status_id          = createStatus($data->STATUS);
    // Check if record already exists
    $results = $mysqli->query("SELECT tx_index FROM issues WHERE tx_hash_id='{$tx_hash_id}'");
    if($results){
        if($results->num_rows){
            // UPDATE record
            $sql = "UPDATE
                        issues
                    SET
                        tick_id='{$tick_id}',
                        max_supply='{$max_supply}',
                        max_mint='{$max_mint}',
                        decimals='{$decimals}',
                        description='{$description}',
                        mint_supply='{$mint_supply}',
                        transfer_id='{$transfer_id}',
                        transfer_supply_id='{$transfer_supply_id}',
                        lock_max_supply='{$lock_max_supply}',
                        lock_mint='{$lock_mint}',
                        lock_mint_supply='{$lock_mint_supply}',
                        lock_max_mint='{$lock_max_mint}',
                        lock_description='{$lock_description}',
                        lock_rug='{$lock_rug}',
                        lock_sleep='{$lock_sleep}',
                        lock_callback='{$lock_callback}',
                        callback_block='{$callback_block}',
                        callback_tick_id='{$callback_tick_id}',
                        callback_amount='{$callback_amount}',
                        allow_list_id='{$allow_list_id}',
                        block_list_id='{$block_list_id}',
                        mint_address_max='{$mint_address_max}',
                        mint_start_block='{$mint_start_block}',
                        mint_stop_block='{$mint_stop_block}',
                        source_id='{$source_id}',
                        block_index='{$block_index}',
                        tx_index='{$tx_index}',
                        status_id='{$status_id}'
                    WHERE 
                        tx_hash_id='{$tx_hash_id}'";
        } else {
            // INSERT record
            $sql = "INSERT INTO issues (tick_id, max_supply, max_mint, decimals, description, mint_supply, transfer_id, transfer_supply_id, lock_max_supply, lock_mint, lock_mint_supply, lock_max_mint, lock_description, lock_rug, lock_sleep, lock_callback, callback_block, callback_tick_id, callback_amount, allow_list_id, block_list_id, mint_address_max, mint_start_block, mint_stop_block, source_id, tx_hash_id, block_index, tx_index, status_id) values ('{$tick_id}', '{$max_supply}', '{$max_mint}', '{$decimals}', '{$description}', '{$mint_supply}', '{$transfer_id}', '{$transfer_supply_id}', '{$lock_max_supply}', '{$lock_mint}',  '{$lock_mint_supply}', '{$lock_max_mint}', '{$lock_description}', '{$lock_rug}', '{$lock_sleep}', '{$lock_callback}', '{$callback_block}', '{$callback_tick_id}', '{$callback_amount}', '{$allow_list_id}', '{$block_list_id}', '{$mint_address_max}', '{$mint_start_block}', '{$mint_stop_block}', '{$source_id}', '{$tx_hash_id}', '{$block_index}', '{$tx_index}', '{$status_id}')";
        }
        // print $sql;
        $results = $mysqli->query($sql);
        if(!$results)
            byeLog('Error while trying to create / update a record in the issues table');
    } else {
        byeLog('Error while trying to lookup record in issues table');
    }
}

// Create record in `mints` table
function createMint( $data=null ){
    global $mysqli;
    $tick_id        = createTicker($data->TICK);
    $source_id      = createAddress($data->SOURCE);
    $destination_id = createAddress($data->DESTINATION);
    $tx_hash_id     = createTransaction($data->TX_HASH);
    $status_id      = createStatus($data->STATUS);
    $tx_index       = $mysqli->real_escape_string($data->TX_INDEX);
    $amount         = $mysqli->real_escape_string($data->AMOUNT);
    $block_index    = $mysqli->real_escape_string($data->BLOCK_INDEX);
    $amount         = $mysqli->real_escape_string($data->AMOUNT);
    // Check if record already exists
    $results = $mysqli->query("SELECT tx_index FROM mints WHERE tx_hash_id='{$tx_hash_id}'");
    if($results){
        if($results->num_rows){
            // UPDATE record
            $sql = "UPDATE
                        mints
                    SET
                        tick_id='{$tick_id}',
                        amount='{$amount}',
                        destination_id='{$destination_id}',
                        source_id='{$source_id}',
                        block_index='{$block_index}',
                        tx_index='{$tx_index}',
                        status_id='{$status_id}'
                    WHERE 
                        tx_hash_id='{$tx_hash_id}'";
        } else {
            // INSERT record
            $sql = "INSERT INTO mints (tx_index, tick_id, amount, source_id, destination_id, tx_hash_id, block_index, status_id) values ('{$tx_index}','{$tick_id}', '{$amount}', '{$source_id}', '{$destination_id}', '{$tx_hash_id}', '{$block_index}', '{$status_id}')";
        }
        $results = $mysqli->query($sql);
        if(!$results)
            byeLog('Error while trying to create / update a record in the mints table');
    } else {
        byeLog('Error while trying to lookup record in mints table');
    }
}

// Create record in `sends` table
function createSend( $data=null ){
    global $mysqli;
    $tick_id        = createTicker($data->TICK);
    $source_id      = createAddress($data->SOURCE);
    $destination_id = createAddress($data->DESTINATION);
    $tx_hash_id     = createTransaction($data->TX_HASH);
    $memo_id        = createMemo($data->MEMO);
    $status_id      = createStatus($data->STATUS);
    $tx_index       = $mysqli->real_escape_string($data->TX_INDEX);
    $amount         = $mysqli->real_escape_string($data->AMOUNT);
    $block_index    = $mysqli->real_escape_string($data->BLOCK_INDEX);
    $amount         = $mysqli->real_escape_string($data->AMOUNT);
    // Check if record already exists
    $sql = "SELECT
                tx_index
            FROM
                sends
            WHERE
                tick_id='{$tick_id}' AND
                source_id='{$source_id}' AND
                destination_id='{$destination_id}' AND
                amount='{$amount}' AND
                tx_hash_id='{$tx_hash_id}'";
    $results = $mysqli->query($sql);
    if($results){
        if($results->num_rows){
            // UPDATE record
            $sql = "UPDATE
                        sends
                    SET
                        tx_index='{$tx_index}',
                        block_index='{$block_index}',
                        memo_id='{$memo_id}',
                        status_id='{$status_id}'
                    WHERE 
                        tick_id='{$tick_id}' AND
                        source_id='{$source_id}' AND
                        destination_id='{$destination_id}' AND
                        amount='{$amount}' AND
                        tx_hash_id='{$tx_hash_id}'";
        } else {
            // INSERT record
            $sql = "INSERT INTO sends (tx_index, tick_id, source_id, destination_id, amount, memo_id, tx_hash_id, block_index, status_id) values ('{$tx_index}','{$tick_id}', '{$source_id}', '{$destination_id}', '{$amount}','{$memo_id}', '{$tx_hash_id}', '{$block_index}', '{$status_id}')";
        }
        $results = $mysqli->query($sql);
        if(!$results)
            byeLog('Error while trying to create / update a record in the sends table');
    } else {
        byeLog('Error while trying to lookup record in sends table');
    }
}

// Create record in `destroys` table
function createDestroy( $data=null ){
    global $mysqli;
    $tick_id        = createTicker($data->TICK);
    $source_id      = createAddress($data->SOURCE);
    $tx_hash_id     = createTransaction($data->TX_HASH);
    $status_id      = createStatus($data->STATUS);
    $memo_id        = createMemo($data->MEMO);
    $tx_index       = $mysqli->real_escape_string($data->TX_INDEX);
    $amount         = $mysqli->real_escape_string($data->AMOUNT);
    $block_index    = $mysqli->real_escape_string($data->BLOCK_INDEX);
    $amount         = $mysqli->real_escape_string($data->AMOUNT);
    // Check if record already exists
    $sql = "SELECT
                tx_index
            FROM
                destroys
            WHERE
                tick_id='{$tick_id}' AND
                source_id='{$source_id}' AND
                amount='{$amount}' AND
                tx_hash_id='{$tx_hash_id}'";
    $results = $mysqli->query($sql);
    if($results){
        if($results->num_rows){
            // UPDATE record
            $sql = "UPDATE
                        destroys
                    SET
                        tx_index='{$tx_index}',
                        block_index='{$block_index}',
                        memo_id='{$memo_id}',
                        status_id='{$status_id}'
                    WHERE 
                        tick_id='{$tick_id}' AND
                        source_id='{$source_id}' AND
                        amount='{$amount}' AND
                        tx_hash_id='{$tx_hash_id}'";
        } else {
            // INSERT record
            $sql = "INSERT INTO destroys (tx_index, tick_id, source_id, amount, memo_id, tx_hash_id, block_index, status_id) values ('{$tx_index}','{$tick_id}', '{$source_id}', '{$amount}','{$memo_id}', '{$tx_hash_id}', '{$block_index}', '{$status_id}')";
        }
        $results = $mysqli->query($sql);
        if(!$results)
            byeLog('Error while trying to create / update a record in the destroys table');
    } else {
        byeLog('Error while trying to lookup record in destroys table');
    }
}

// Create / Update record in `tokens` table
function createToken( $data=null ){
    global $mysqli;
    // Convert supply amounts to integers
    $supply             = (isset($data->SUPPLY) && is_numeric($data->SUPPLY)) ? $data->SUPPLY : 0;
    $max_supply         = (isset($data->MAX_SUPPLY) && is_numeric($data->MAX_SUPPLY)) ? $data->MAX_SUPPLY : 0;
    $max_mint           = (isset($data->MAX_MINT) && is_numeric($data->MAX_MINT)) ? $data->MAX_MINT : 0;
    $mint_supply        = (isset($data->MINT_SUPPLY) && is_numeric($data->MINT_SUPPLY)) ? $data->MINT_SUPPLY : 0;
    $mint_address_max   = (isset($data->MINT_ADDRESS_MAX) && is_numeric($data->MINT_ADDRESS_MAX)) ? $data->MINT_ADDRESS_MAX : 0;
    $mint_start_block   = (isset($data->MINT_START_BLOCK) && is_numeric($data->MINT_START_BLOCK)) ? $data->MINT_START_BLOCK : 0;
    $mint_stop_block    = (isset($data->MINT_STOP_BLOCK) && is_numeric($data->MINT_STOP_BLOCK)) ? $data->MINT_STOP_BLOCK : 0;
    $callback_amount    = (isset($data->CALLBACK_AMOUNT) && is_numeric($data->CALLBACK_AMOUNT)) ? $data->CALLBACK_AMOUNT : 0;
    $decimals           = (isset($data->DECIMALS) && is_numeric($data->DECIMALS)) ? intval($data->DECIMALS) : 0;
    // Force any amount values to the correct decimal precision
    if(is_numeric($decimals) && $decimals>=0 && $decimals<=18){
        $max_supply       = bcmul($max_supply,1,$decimals);
        $max_mint         = bcmul($max_mint,1,$decimals);
        $mint_supply      = bcmul($mint_supply,1,$decimals);
        $mint_address_max = bcmul($mint_address_max,1,$decimals);
        $callback_amount  = bcmul($callback_amount,1,$decimals);
    }
    $supply             = $mysqli->real_escape_string($supply);
    $max_supply         = $mysqli->real_escape_string($max_supply);
    $max_mint           = $mysqli->real_escape_string($max_mint);
    $mint_address_max   = $mysqli->real_escape_string($mint_address_max);
    $mint_start_block   = $mysqli->real_escape_string($mint_start_block);
    $mint_stop_block    = $mysqli->real_escape_string($mint_stop_block);
    $decimals           = $mysqli->real_escape_string($decimals);
    $description        = $mysqli->real_escape_string($data->DESCRIPTION);
    $block_index        = $mysqli->real_escape_string($data->BLOCK_INDEX);
    // Force lock fields to integer values 
    $lock_max_supply    = ($data->LOCK_MAX_SUPPLY==1) ? 1 : 0;
    $lock_mint          = ($data->LOCK_MINT==1) ? 1 : 0;
    $lock_max_mint      = ($data->LOCK_MAX_MINT==1) ? 1 : 0;
    $lock_description   = ($data->LOCK_DESCRIPTION==1) ? 1 : 0;
    $lock_rug           = ($data->LOCK_RUG==1) ? 1 : 0;
    $lock_sleep         = ($data->LOCK_SLEEP==1) ? 1 : 0;
    $lock_callback      = ($data->LOCK_CALLBACK==1) ? 1 : 0;
    $callback_block     = ($data->CALLBACK_BLOCK>0) ? $data->CALLBACK_BLOCK : 0;
    $callback_amount    = $mysqli->real_escape_string($callback_amount);
    $callback_tick_id   = createTicker($data->CALLBACK_TICK);
    $tick_id            = createTicker($data->TICK);
    $owner_id           = createAddress($data->OWNER);
    $allow_list_id      = createTransaction($data->ALLOW_LIST);
    $block_list_id      = createTransaction($data->BLOCK_LIST);
    // Check if record already exists
    $results = $mysqli->query("SELECT id FROM tokens WHERE tick_id='{$tick_id}'");
    if($results){
        if($results->num_rows){
            // UPDATE record
            $sql = "UPDATE
                        tokens
                    SET
                        max_supply='{$max_supply}',
                        max_mint='{$max_mint}',
                        decimals='{$decimals}',
                        description='{$description}',
                        lock_max_supply='{$lock_max_supply}',
                        lock_mint='{$lock_mint}',
                        lock_max_mint='{$lock_max_mint}',
                        lock_description='{$lock_description}',
                        lock_rug='{$lock_rug}',
                        lock_sleep='{$lock_sleep}',
                        lock_callback='{$lock_callback}',
                        callback_block='{$callback_block}',
                        callback_tick_id='{$callback_tick_id}',
                        callback_amount='{$callback_amount}',
                        allow_list_id='{$allow_list_id}',
                        block_list_id='{$block_list_id}',
                        mint_address_max='{$mint_address_max}',
                        mint_start_block='{$mint_start_block}',
                        mint_stop_block='{$mint_stop_block}',
                        supply='{$supply}',
                        owner_id='{$owner_id}'
                    WHERE 
                        tick_id='{$tick_id}'";
        } else {
            // INSERT record
            $sql = "INSERT INTO tokens (tick_id, max_supply, max_mint, decimals, description, lock_max_supply, lock_mint, lock_max_mint, lock_description, lock_rug, lock_sleep, lock_callback, callback_block, callback_tick_id, callback_amount, allow_list_id, block_list_id, mint_address_max, mint_start_block, mint_stop_block, owner_id, supply, block_index) values ('{$tick_id}', '{$max_supply}', '{$max_mint}', '{$decimals}', '{$description}', '{$lock_max_supply}', '{$lock_mint}', '{$lock_max_mint}', '{$lock_description}', '{$lock_rug}', '{$lock_sleep}', '{$lock_callback}', '{$callback_block}', '{$callback_tick_id}', '{$callback_amount}', '{$allow_list_id}', '{$block_list_id}', '{$mint_address_max}', '{$mint_start_block}', '{$mint_stop_block}', '{$owner_id}','{$supply}', '{$block_index}')";
        }
        // print $sql;
        $results = $mysqli->query($sql);
        if(!$results)
            byeLog('Error while trying to create / update a record in the tokens table');
    } else {
        byeLog('Error while trying to lookup record in tokens table');
    }
}

// Create record in `credits` table
function createCredit( $action=null, $block_index=null, $event=null, $tick=null, $amount=null, $address=null ){
    global $mysqli;
    $action      = $mysqli->real_escape_string($action);
    $amount      = $mysqli->real_escape_string($amount);
    $block_index = $mysqli->real_escape_string($block_index);
    $tick_id     = createTicker($tick);
    $address_id  = createAddress($address);
    $event_id    = createTransaction($event);
    $action_id   = createAction($action);
    // Check if record already exists
    $sql = "SELECT
                event_id
            FROM
                credits
            WHERE
                address_id='{$address_id}' AND 
                tick_id='{$tick_id}' AND
                amount='{$amount}' AND
                action_id='{$action_id}' AND
                event_id='{$event_id}'";
    $results = $mysqli->query($sql);
    if($results){
        if($results->num_rows){
            // UPDATE record
            $sql = "UPDATE
                        credits
                    SET
                        block_index='{$block_index}'
                    WHERE 
                        address_id='{$address_id}' AND 
                        tick_id='{$tick_id}' AND
                        amount='{$amount}' AND
                        action_id='{$action_id}' AND
                        event_id='{$event_id}'";
        } else {
            // INSERT record
            $sql = "INSERT INTO credits (block_index, address_id, tick_id, amount, action_id, event_id) values ('{$block_index}', '{$address_id}', '{$tick_id}', '{$amount}', '{$action_id}', '{$event_id}')";
        }
        $results = $mysqli->query($sql);
        if(!$results)
            byeLog('Error while trying to create / update a record in the credits table');
    } else {
        byeLog('Error while trying to lookup record in credits table');
    }
}

// Create record in `debits` table
function createDebit( $action=null, $block_index=null, $event=null, $tick=null, $amount=null, $address=null ){
    global $mysqli;
    $action      = $mysqli->real_escape_string($action);
    $amount      = $mysqli->real_escape_string($amount);
    $block_index = $mysqli->real_escape_string($block_index);
    $tick_id     = createTicker($tick);
    $address_id  = createAddress($address);
    $event_id    = createTransaction($event);
    $action_id   = createAction($action);
    // Check if record already exists
    $sql = "SELECT
                event_id
            FROM
                debits
            WHERE
                address_id='{$address_id}' AND 
                tick_id='{$tick_id}' AND
                amount='{$amount}' AND
                action_id='{$action_id}' AND
                event_id='{$event_id}'";
    $results = $mysqli->query($sql);
    if($results){
        if($results->num_rows){
            // UPDATE record
            $sql = "UPDATE
                        debits
                    SET
                        block_index='{$block_index}'
                    WHERE 
                        address_id='{$address_id}' AND 
                        tick_id='{$tick_id}' AND
                        amount='{$amount}' AND
                        action_id='{$action_id}' AND
                        event_id='{$event_id}'";
        } else {
            // INSERT record
            $sql = "INSERT INTO debits (block_index, address_id, tick_id, amount, action_id, event_id) values ('{$block_index}', '{$address_id}', '{$tick_id}', '{$amount}', '{$action_id}', '{$event_id}')";
        }
        $results = $mysqli->query($sql);
        if(!$results)
            byeLog('Error while trying to create / update a record in the debits table');
    } else {
        byeLog('Error while trying to lookup record in debits table');
    }
}

// Create record in `blocks` table
function createBlock( $block=null ){
    global $mysqli, $dbase;
    $block_time   = 0;
    // Get timestamp of Block from main database 
    $results = $mysqli->query("SELECT block_time FROM {$dbase}.blocks WHERE block_index='{$block}' LIMIT 1");
    if($results){
        if($results->num_rows){
            $row = (object) $results->fetch_assoc();
            $block_time = $row->block_time;
        }
    } else {
        byeLog('Error while trying to lookup records in credits table');
    }
    // Get a list of hashes for this block
    $info    = getBlockHashes($block);
    $credits = $info['credits']['hash'];
    $debits  = $info['debits']['hash'];
    $txlist  = $info['txlist']['hash'];
    $credits_hash_id = createTransaction($credits);
    $debits_hash_id  = createTransaction($debits);
    $txlist_hash_id  = createTransaction($txlist);
    // Check if record already exists
    $results = $mysqli->query("SELECT id FROM blocks WHERE block_index='{$block}'");
    if($results){
        if($results->num_rows){
            // UPDATE record
            $sql = "UPDATE
                        blocks
                    SET
                        block_time='{$block_time}',
                        credits_hash_id='{$credits_hash_id}',
                        debits_hash_id='{$debits_hash_id}',
                        txlist_hash_id='{$txlist_hash_id}'
                    WHERE 
                        block_index='{$block}'";
        } else {
            // INSERT record
            $sql = "INSERT INTO blocks (block_index, block_time, credits_hash_id, debits_hash_id, txlist_hash_id) values ('{$block}', '{$block_time}', '{$credits_hash_id}', '{$debits_hash_id}', '{$txlist_hash_id}')";
        }
        $results = $mysqli->query($sql);
        if(!$results)
            byeLog('Error while trying to create / update a record in the blocks table');
    } else {
        byeLog('Error while trying to lookup record in blocks table');
    }
    // Print out a status update
    $credits = substr($credits,0,5);
    $debits  = substr($debits,0,5);
    $txlist  = substr($txlist,0,5);
    print "\n\t [credits:{$credits} debits:{$debits} txlist:{$txlist}]";

}

// Create record in `lists` table
function createList( $data=null ){
    global $mysqli;
    $source_id       = createAddress($data->SOURCE);
    $tx_hash_id      = createTransaction($data->TX_HASH);
    $list_tx_hash_id = createTransaction($data->LIST_TX_HASH);
    $status_id       = createStatus($data->STATUS);
    $tx_index        = $mysqli->real_escape_string($data->TX_INDEX);
    $list_type       = $mysqli->real_escape_string($data->TYPE);
    $list_edit       = $mysqli->real_escape_string($data->EDIT);
    $block_index     = $mysqli->real_escape_string($data->BLOCK_INDEX);
    // Check if record already exists
    $results = $mysqli->query("SELECT tx_index FROM lists WHERE tx_hash_id='{$tx_hash_id}'");
    if($results){
        if($results->num_rows){
            // UPDATE record
            $sql = "UPDATE
                        lists
                    SET
                        type='{$list_type}',
                        edit='{$list_edit}',
                        source_id='{$source_id}',
                        block_index='{$block_index}',
                        list_tx_hash_id='{$list_tx_hash_id}',
                        tx_index='{$tx_index}',
                        status_id='{$status_id}'
                    WHERE 
                        tx_hash_id='{$tx_hash_id}'";
        } else {
            // INSERT record
            $sql = "INSERT INTO lists (tx_index, type, edit, source_id, list_tx_hash_id, tx_hash_id, block_index, status_id) values ('{$tx_index}','{$list_type}', '{$list_edit}', '{$source_id}', '{$list_tx_hash_id}', '{$tx_hash_id}', '{$block_index}', '{$status_id}')";
        }
        // print $sql;
        $results = $mysqli->query($sql);
        if(!$results)
            byeLog('Error while trying to create / update a record in the lists table');
    } else {
        byeLog('Error while trying to lookup record in lists table');
    }
}

// Return a list given a tx_hash
function getList($tx_hash=null){
    global $mysqli, $dbase;
    $list_id    = (is_numeric($tx_hash)) ? $tx_hash : createTransaction($tx_hash);
    $list_type  = getListType($list_id);
    $list       = array();
    $sql        = false;
    if($list_type==1)
        $sql = "SELECT t.tick as item FROM list_items l, index_tickers t WHERE l.item_id=t.id AND l.list_id='{$list_id}'";
    // Asset
    if($list_type==2)
        $sql = "SELECT a.asset as item FROM list_items l, {$dbase}.assets a WHERE l.item_id=a.id AND l.list_id='{$list_id}'";
    // Address
    if($list_type==3)
        $sql = "SELECT a.address as item FROM list_items l, index_addresses a WHERE l.item_id=a.id AND l.list_id='{$list_id}'";
    // Check if record already exists
    if($sql){
        $results = $mysqli->query($sql);
        if($results){
            while($row = $results->fetch_assoc()){
                $row = (object) $row;
                array_push($list, $row->item);
            }
        }
    }
    return $list;
}

// Return a list type given a tx_hash
function getListType($tx_hash=null){
    global $mysqli;
    $list_id = (is_numeric($tx_hash)) ? $tx_hash : createTransaction($tx_hash);
    $type = false;
    // Check if record already exists
    $sql = "SELECT type FROM lists WHERE tx_hash_id='{$list_id}'";
    $results = $mysqli->query($sql);
    if($results && $results->num_rows==1){
        $row  = (object) $results->fetch_assoc();
        $type = (int) $row->type;
    }
    return $type;
}

// Create record in `list_edits` table
function createListEdit($data=null, $item=null, $status=null ){
    global $mysqli;
    $list_id   = (is_numeric($data->TX_HASH)) ? $data->TX_HASH : createTransaction($data->TX_HASH);
    $status_id = createStatus($status);
    if($data->TYPE==1)
        $item_id = createTicker($item);
    if($data->TYPE==2)
        $item_id = getAssetId($item);
    if($data->TYPE==3)
        $item_id = createAddress($item);
    // Check if record already exists
    $results = $mysqli->query("SELECT item_id FROM list_edits WHERE list_id='{$list_id}' AND item_id='{$item_id}' AND status_id='{$status_id}'");
    if($results){
        if($results->num_rows==0){
            // INSERT record
            $sql = "INSERT INTO list_edits (list_id, item_id, status_id) values ('{$list_id}','{$item_id}', '{$status_id}')";
            $results = $mysqli->query($sql);
        }
        if(!$results)
            byeLog('Error while trying to create / update a record in the list_edits table');
    } else {
        byeLog('Error while trying to lookup record in list_edits table');
    }
}

// Create record in `list_items` table
function createListItem($data=null, $item=null){
    global $mysqli;
    $list_id   = createTransaction($data->TX_HASH);
    if($data->TYPE==1)
        $item_id = createTicker($item);
    if($data->TYPE==2)
        $item_id = getAssetId($item);
    if($data->TYPE==3)
        $item_id = createAddress($item);
    // Check if record already exists
    $results = $mysqli->query("SELECT item_id FROM list_items WHERE list_id='{$list_id}' AND item_id='{$item_id}'");
    if($results){
        if($results->num_rows==0){
            // INSERT record
            $sql = "INSERT INTO list_items (list_id, item_id) values ('{$list_id}','{$item_id}')";
            $results = $mysqli->query($sql);
        }
        if(!$results)
            byeLog('Error while trying to create / update a record in the list_edits table');
    } else {
        byeLog('Error while trying to lookup record in list_edits table');
    }
}

// Delete records in lists, list_items, and list_edits tables
function deleteLists($list=null, $rollback=null){
    global $mysqli;
    $lists = array();
    $type  = gettype($list);
    if($type==='array')
        $lists = $list;
    if($type==='string'||is_numeric($list))
        array_push($lists, $list);
    // Loop through lists and convert any transaction hashes to database ids
    foreach($lists as $idx => $list){
        $type = getType($list);
        if($type=="string" && !is_numeric($list))
            $lists[$idx] = createTransaction($list);
    }
    $tables = ['list_items', 'list_edits'];
    foreach($lists as $list_id){
        // Delete item from lists table
        $results = $mysqli->query("DELETE FROM lists WHERE tx_hash_id='{$list_id}'");
        if(!$results)
            byeLog('Error while trying to delete records from the lists table');
        // Deletes item from list_items and list_edits tables
        foreach($tables as $table){
            $results = $mysqli->query("DELETE FROM {$table} WHERE list_id='{$list_id}'");
            if(!$results)
                byeLog('Error while trying to delete records from the ' . $table . ' table');
        }
    }
}

// Handle getting token information using issues table
function getTokenInfo($tick=null, $tick_id=null, $block_index=null, $tx_index=null){
    global $mysqli;
    $data     = false;
    $whereSql = "";
    // Get the tick_id for the given ticker
    if(!is_null($tick) && is_null($tick_id))
        $tick_id = createTicker($tick);
    // If a block index was given, only lookup tokens created before or in given block
    if(isset($block_index) && is_numeric($block_index))
        $whereSql .= " AND t1.block_index <= {$block_index}";
    if(isset($tx_index) && is_numeric($tx_index))
        $whereSql .= " AND t1.tx_index < '{$tx_index}'";
    // Get data from issues table
    $sql = "SELECT 
                t2.tick,
                t1.max_supply,
                t1.max_mint,
                t1.decimals,
                t1.description,
                t1.block_index,
                t1.lock_max_supply,
                t1.lock_mint_supply,
                t1.lock_mint,
                t1.lock_max_mint,
                t1.lock_description,
                t1.lock_rug,
                t1.lock_sleep,
                t1.lock_callback,
                t1.callback_block,
                t3.tick as callback_tick,            
                t1.callback_amount,
                t4.hash as allow_list,
                t5.hash as block_list,
                t1.mint_address_max,
                t1.mint_start_block,
                t1.mint_stop_block,
                a1.address as owner,
                a2.address as transfer
            FROM 
                issues t1
                LEFT JOIN index_addresses a2 on (a2.id=t1.transfer_id)
                LEFT JOIN index_tickers t3 on (t3.id=t1.callback_tick_id)
                LEFT JOIN index_transactions t4 on (t4.id=t1.allow_list_id)
                LEFT JOIN index_transactions t5 on (t5.id=t1.block_list_id),
                index_tickers t2,
                index_addresses a1,
                index_statuses s1
            WHERE 
                t2.id=t1.tick_id AND
                a1.id=t1.source_id AND
                s1.id=t1.status_id AND
                s1.status='valid' AND
                t1.tick_id='{$tick_id}' 
                {$whereSql}
            ORDER BY tx_index ASC";
    $results = $mysqli->query($sql);
    if($results){
        if($results->num_rows){
            // Loop through issues 
            while($row = $results->fetch_assoc()){
                $row  = (object) $row;
                $arr  = array(
                    'TICK'              => $row->tick,
                    'OWNER'             => ($row->transfer) ? $row->transfer : $row->owner,
                    'MAX_SUPPLY'        => $row->max_supply,
                    'MAX_MINT'          => $row->max_mint,
                    'DECIMALS'          => (isset($row->decimals)) ? intval($row->decimals) : 0,
                    'DESCRIPTION'       => $row->description,
                    'LOCK_MAX_SUPPLY'   => $row->lock_max_supply,
                    'LOCK_MINT_SUPPLY'  => $row->lock_mint_supply,
                    'LOCK_MINT'         => $row->lock_mint,
                    'LOCK_MAX_MINT'     => $row->lock_max_mint,
                    'LOCK_DESCRIPTION'  => $row->lock_description,
                    'LOCK_RUG'          => $row->lock_rug,
                    'LOCK_SLEEP'        => $row->lock_sleep,
                    'LOCK_CALLBACK'     => $row->lock_callback,
                    'CALLBACK_TICK'     => $row->callback_tick,
                    'CALLBACK_BLOCK'    => $row->callback_block,
                    'CALLBACK_AMOUNT'   => $row->callback_amount,
                    'ALLOW_LIST'        => $row->allow_list,
                    'BLOCK_LIST'        => $row->block_list,
                    'MINT_ADDRESS_MAX'  => $row->mint_address_max,
                    'MINT_START_BLOCK'  => $row->mint_start_block,
                    'MINT_STOP_BLOCK'   => $row->mint_stop_block
                );
                // build out token state before tx_index
                // TODO: will need to massage the data a bit more to build out accurate token state... this is quick and dirty
                foreach($arr as $key => $value){
                    // Disallow unsetting of LOCK flags
                    if(substr($key,0,5)=='LOCK_')
                        if($data[$key]==1)
                            continue;
                    // Skip setting value if value is null or empty (use last explicit value)
                    if(!isset($value) || $value=='')
                        continue;
                    $data[$key] = $value;
                }
            }
        } 
    } else {
        byeLog("Error while trying to lookup token info for : {$tick}");
    }
    if($data){
        // Get token supply at the given tx_index
        $data['SUPPLY'] = getTokenSupply($tick, $tick_id, null, $tx_index); 
        $data = (object) $data;
    }
    return $data;
}

// Handle getting database id for a given asset
function getAssetId($asset=null){
    global $mysqli, $dbase;
    $id    = false;
    $asset = $mysqli->real_escape_string($asset);
    $sql   = "SELECT id FROM {$dbase}.assets WHERE asset='{$asset}' OR asset_longname='{$asset}' LIMIT 1";
    $results = $mysqli->query($sql);
    if($results){
        $row = $results->fetch_assoc();
        $id  = $row['id'];
    }
    return $id;
}

// Handle getting asset information for an asset
function getAssetInfo($asset=null, $block_index=null){
    global $mysqli, $dbase;
    $type = gettype($asset);
    $data = false;
    // Only do lookup on strings, since all CP assets are strings
    if($type=='string')
        $asset_id = getAssetId($asset);
    if($asset_id){
        // Get data from assets table
        $sql = "SELECT 
                    a1.asset,
                    a1.type,
                    a1.asset_longname,
                    a2.address as owner
                FROM 
                    {$dbase}.assets a1 LEFT JOIN 
                    {$dbase}.index_addresses a2 on (a2.id=a1.owner_id)
                WHERE 
                    a1.type IN (1,2,3) AND
                    a1.id='{$asset_id}'";
        // print $sql;
        $results = $mysqli->query($sql);
        if($results){
            if($results->num_rows){
                $row  = (object) $results->fetch_assoc();
                $data = (object) array(
                    'ASSET' => $row->asset,
                    'TYPE'  => $row->type,
                    'NAME'  => ($row->type==3) ? $row->asset_longname : $row->asset,
                    'OWNER' => $row->owner
                );
            } 
        } else {
            byeLog("Error while trying to lookup asset info for : {$asset}");
        }
        // If block_index is set, get asset owner at time of block_index
        if(is_numeric($block_index)){
            $sql = "SELECT 
                        a1.address as issuer,
                        a2.address as source
                    FROM
                        {$dbase}.issuances i LEFT JOIN 
                        {$dbase}.index_addresses a1 on (a1.id=i.issuer_id),
                        {$dbase}.index_addresses a2
                    WHERE
                        a2.id=i.source_id AND
                        i.asset_id='{$asset_id}' AND
                        i.block_index<='{$block_index}'
                    ORDER BY block_index DESC
                    LIMIT 1";
            // print $sql;
            $results = $mysqli->query($sql);
            if($results){
                if($results->num_rows){
                    $row  = (object) $results->fetch_assoc();
                    $data->OWNER = (!is_null($row->issuer)) ? $row->issuer : $row->source;
                } else {
                    // If no issuances exist at time of block_index, asset was not created yet
                    $data = false;
                }
            } else {
                byeLog("Error while trying to lookup asset owner for {$asset} at block {$block_index}");
            }
        }
    }
    return $data;
}

// Handle getting decimal precision for a given tick_id
function getTokenDecimalPrecision($tick_id=null){
    global $mysqli;
    // print "getTokenDecimalPrecision tick_id={$tick_id}\n";
    // Lookup decimal precision using the issues table 
    // DO NOT lookup precision using getTokenInfo() (avoid recursive queries)
    $decimals = 0;
    $sql = "SELECT
                i.decimals
            FROM
                issues i,
                index_statuses s
            WHERE
                i.status_id=s.id AND
                i.tick_id='{$tick_id}' AND
                s.status='valid'";
    // print $sql;
    $results = $mysqli->query($sql);
    if($results){
        if($results->num_rows){
            while($row = $results->fetch_assoc()){
                $row = (object) $row;
                if(isset($row->decimals) && $row->decimals > $decimals)
                    $decimals = $row->decimals;
            }
        }
    } else {
        byeLog("Error while trying to lookup decimal precision from the issues table for tick: {$tick_id}");
    }                
    return $decimals;
}

// Handle getting credits or debits records for a given address
function getAddressCreditDebit($table=null, $address=null, $action=null, $block=null, $tx_index=null){
    global $mysqli;
    $data = array(); // Assoc array to store tick/credits
    $type = gettype($address);
    if($type==='integer' || is_numeric($address))
        $address_id = $address;
    if($type==='string' && !is_numeric($address))
        $address_id = createAddress($address);
    if(isset($action))
        $action_id = createAction($action);
    // Build out custom WHERE sql
    $whereSql = "";
    if(isset($action))
        $whereSql .= " AND t1.action_id={$action_id}";
    // Query using either block_index OR tx_index
    if(isset($tx_index) && is_numeric($tx_index)){
        $whereSql .= " AND t3.tx_index < {$tx_index}";
    } else if(isset($block) && is_numeric($block)){
        $whereSql .= " AND t1.block_index < {$block}";
    }
    if(in_array($table,array('credits','debits'))){
        // Get data from the table
        $sql = "SELECT 
                    t1.tick_id,
                    t1.amount,
                    t2.decimals
                FROM
                    {$table} t1,
                    tokens t2,
                    transactions t3
                WHERE 
                    t2.tick_id=t1.tick_id AND
                    t3.tx_hash_id=t1.event_id AND
                    t1.address_id='{$address_id}'
                    {$whereSql}";
        $results = $mysqli->query($sql);
        if($results){
            if($results->num_rows){
                while($row = $results->fetch_assoc()){
                    $row = (object) $row;
                    if(!$data[$row->tick_id])
                        $data[$row->tick_id] = 0;
                    $data[$row->tick_id] = bcadd($data[$row->tick_id], $row->amount, $row->decimals);
                }
            }
        } else {
            byeLog("Error while trying to lookup address {$table} for : {$address}");
        }
    }
    return $data;
}

// Get address balances using credits/debits table data
function getAddressBalances($address=null, $tick=null, $block=null, $tx_index=null){
    global $mysqli;
    $type = gettype($address);
    if($type==='integer' || is_numeric($address))
        $address_id = $address;
    if($type==='string' && !is_numeric($address))
        $address_id = createAddress($address);
    $credits  = getAddressCreditDebit('credits', $address_id, null, $block, $tx_index);
    $debits   = getAddressCreditDebit('debits',  $address_id, null, $block, $tx_index);
    $decimals = array(); // Assoc array to store tick/decimals
    $balances = array(); // Assoc array to store tick/balance
    foreach($credits as $tick_id => $amount)
        $decimals[$tick_id] = getTokenDecimalPrecision($tick_id);
    // Build out balances (credits - debits)
    foreach($credits as $tick_id => $amount){
        $credit  = $amount;
        $debit   = (isset($debits[$tick_id])) ? $debits[$tick_id] : 0;
        $decimal = $decimals[$tick_id];
        try {
            $balance = bcsub($credit, $debit, $decimal);
        } catch(Exception $e){
            $balance = number_format(0,$decimal,'.','');
        }
        // Pass forward any numeric values (including 0 balance)
        if(is_numeric($balance))
            $balances[$tick_id] = $balance;
    }
    return $balances;
}

// Get address balances using balances table data
function getAddressTableBalances($address=null){
    global $mysqli;
    $type = gettype($address);
    if($type==='integer' || is_numeric($address))
        $address_id = $address;
    if($type==='string' && !is_numeric($address))
        $address_id = createAddress($address);
    // Assoc array to store tick/balance
    $balances = array(); 
    $results = $mysqli->query("SELECT tick_id, amount FROM balances WHERE address_id='{$address_id}'");
    if($results){
        if($results->num_rows){
            while($row = $results->fetch_assoc()){
                $row = (object) $row;
                $balances[$row->tick_id] = $row->amount;
            }
        }
    } else {
        byeLog('Error while trying to lookup balances record for address=' . $address);
    }
    return $balances;
}

// Create/Update/Delete records in the 'balances' table
function updateAddressBalance( $address=null, $rollback=false){
    global $mysqli;
    // print "updateAddressBalance address={$address} rollback={$rollback}\n";
    $type = gettype($address);
    if($type==='integer' || is_numeric($address))
        $address_id = $address;
    if($type==='string' && !is_numeric($address))
        $address_id = createAddress($address);
    // Get list of address balances based on credits/debits tables
    $balances = getAddressBalances($address_id);
    if(count($balances)){
        foreach($balances as $tick_id => $balance){
            // print "processing balance address_id={$address_id} tick={$tick_id} balance={$balance}\n";
            $whereSql = "address_id='{$address_id}' AND tick_id='{$tick_id}'";
            // Check if we already have a record for this address/tick_id
            $sql     = "SELECT id FROM balances WHERE {$whereSql} LIMIT 1";
            $results = $mysqli->query($sql);
            if($results){
                $action = ($results->num_rows) ? 'update' : 'insert';
                if($balance==0)
                    $action = 'delete';
                // print "action={$action}\n";
                if($action=='delete'){
                    $sql = "DELETE FROM balances WHERE {$whereSql}";
                } else if($action=='update'){
                    $sql = "UPDATE balances SET amount='{$balance}' WHERE {$whereSql}";
                } else if($action=='insert'){
                    $sql = "INSERT INTO balances (tick_id, address_id, amount) values ('{$tick_id}','{$address_id}','{$balance}')";
                }
                $results = $mysqli->query($sql);
                if(!$results)
                    byeLog('Error while trying to ' . $action  . ' balance record for address=' . $address . ' tick_id=' . $tick_id);
            } else {
                byeLog('Error while trying to lookup balances record for address=' . $address . ' tick_id=' . $tick_id);
            }
        }
    }
    // If this is a rollback, then handle detecting records in balances table which should not exist and delete them
    if($rollback){
        // Get list of address balances based on balances table
        $old_balances = getAddressTableBalances($address_id);
        foreach($old_balances as $tick_id => $balance){
            $balance = $balances[$tick_id];
            if(!isset($balance) || $balance==0){
                $results = $mysqli->query("DELETE FROM balances WHERE address_id='{$address_id}' AND tick_id='{$tick_id}'");
                if(!$results)
                    byeLog('Error while trying to delete balance record for address=' . $address . ' tick_id=' . $tick_id);
            }
        }
    }
}

// Handle updating address balances (credits-debits=balance)
// @param {address}  boolean Full update
// @param {address}  string  Address string
// @param {address}  array   Array of address strings
// @param {rollback} boolean Rollback
function updateBalances( $address=null, $rollback=false ){
    global $mysqli;
    $addrs = [];
    $type  = gettype($address);
    if($type==='array'){
        foreach($address as $addr)
            if(!is_null($addr) && $addr!='')
                array_push($addrs, $addr);
    }
    if($type==='string')
        array_push($addrs, $address);
    // Dump full list of addresses
    if($type==='boolean' && $address===true){
        $results = $mysqli->query("SELECT address FROM index_addresses");
        if($results){
            if($results->num_rows)
                while($row = $results->fetch_assoc())
                    array_push($addrs, $row['address']);
        } else {
            byeLog('Error while trying to get list of all addresses');
        }
    }
    // Loop through addresses and update balance list
    foreach($addrs as $address)
        updateAddressBalance($address, $rollback);
}

// Handle updating token information (supply, price, etc)
// @param {tickers} boolean Full update
// @param {tickers} string  Ticker 
// @param {tickers} array   Array of Tickers
function updateTokens( $tickers=null, $rollback=true){
    global $mysqli;
    $tokens = [];
    $type  = gettype($tickers);
    if($type==='array')
        foreach($tickers as $tick)
            if(!is_null($tick) && $tick!='')
                array_push($tokens, $tick);
    if($type==='string')
        array_push($tokens, $tickers);
    // Dump full list of tokens
    if($type==='boolean' && $tickers===true){
        $results = $mysqli->query("SELECT t2.tick FROM tokens t1, index_tickers t2 WHERE t1.tick_id=t2.id");
        if($results){
            if($results->num_rows)
                while($row = $results->fetch_assoc())
                    array_push($tokens, $row['tick']);
        } else {
            byeLog('Error while trying to get list of all tokens');
        }
    }
    // Loop through tokens and update basic ifno
    foreach($tokens as $tick)
        updateTokenInfo($tick);
}

// Handle getting token info (supply, price, etc) and updating the `tokens` table
function updateTokenInfo( $tick=null ){
    // print "updateTokenInfo tick={$tick}\n";
    global $mysqli;
    $tick_id = createTicker($tick);
    // Lookup current token information
    $data = getTokenInfo($tick);
    // Update the record in `tokens` table
    if($data)
        createToken($data);
}

// Get token supply from credits/debits table (credits - debits = supply)
// @param {tick}            string  Ticker name
// @param {block_index}     integer Block Index 
// @param {tx_index}        integer tx_index of transaction
function getTokenSupply( $tick=null, $tick_id=null, $block_index=null, $tx_index=null ){
    global $mysqli;
    $credits = 0;
    $debits  = 0;
    $supply  = 0;
    $block   = (is_numeric($block_index)) ? $block_index : 99999999999999;
    // Get the tick_id for the given ticker
    if(!is_null($tick) && is_null($tick_id))
        $tick_id = createTicker($tick);
    // Get info on decimal precision
    $decimals = getTokenDecimalPrecision($tick_id);
    $whereSql = "";
    // Filter by block_index
    if(is_numeric($block))
        $whereSql .= " AND m.block_index <= '{$block}'";
    // Filter by tx_index
    if(is_numeric($tx_index))
        $whereSql .= " AND t.tx_index < {$tx_index}";
    // Get Credits 
    $sql = "SELECT 
                CAST(SUM(m.amount) AS DECIMAL(60,$decimals)) as credits 
            FROM 
                credits m,
                transactions t
            WHERE 
                m.event_id=t.tx_hash_id AND
                m.tick_id='{$tick_id}'
                {$whereSql}";
    $results = $mysqli->query($sql);
    if($results){
        if($results->num_rows){
            $row = (object) $results->fetch_assoc();
            $credits = $row->credits;
        }
    } else {
        byeLog('Error while trying to get list of credits');
    }
    // Get Debits
    $sql = "SELECT 
                CAST(SUM(m.amount) AS DECIMAL(60,$decimals)) as debits 
            FROM 
                debits m,
                transactions t
            WHERE 
                m.event_id=t.tx_hash_id AND
                m.tick_id='{$tick_id}'
                {$whereSql}";
    $results = $mysqli->query($sql);
    if($results){
        if($results->num_rows){
            $row = (object) $results->fetch_assoc();
            $debits = $row->debits;
        }
    } else {
        byeLog('Error while trying to get list of debits');
    }
    $supply = bcsub($credits, $debits, $decimals);
    return $supply;
}

// Get token supply from balances table
function getTokenSupplyBalance( $tick=null ){
    global $mysqli;
    $supply  = 0;
    $tick_id = createTicker($tick);
    // Get info on decimal precision
    $decimals = getTokenDecimalPrecision($tick_id);
    // Get Credits 
    $sql = "SELECT CAST(SUM(amount) AS DECIMAL(60,$decimals)) as supply FROM balances WHERE tick_id='{$tick_id}'";
    $results = $mysqli->query($sql);
    if($results){
        if($results->num_rows){
            $row    = (object) $results->fetch_assoc();
            $supply = (!is_null($row->supply)) ? $row->supply : 0;
        }
    } else {
        byeLog('Error while trying to get list of balances');
    }
    return $supply;
}

// Handle doing VERY lose validation on an address
function isCryptoAddress( $address=null ){
    $len   = strlen($address);
    // Check P2PKH (26-35 chars)
    if($len>=26 && $len<=35)
        return true;
    // Check Segwit (42 chars)
    if($len==42)
        return true;
    return false;
}

// Generalized function to invoke BTNS action commands
function btnsAction($action=null, $params=null, $data=null, $error=null){
    if($action=='ADDRESS')      btnsAddress($params, $data, $error);
    if($action=='AIRDROP')      btnsAirdrop($params, $data, $error);
    if($action=='BATCH')        btnsBatch($params, $data, $error);
    if($action=='BET')          btnsBet($params, $data, $error);
    if($action=='CALLBACK')     btnsCallback($params, $data, $error);
    if($action=='DESTROY')      btnsDestroy($params, $data, $error);
    if($action=='DISPENSER')    btnsDispenser($params, $data, $error);
    if($action=='DIVIDEND')     btnsDividend($params, $data, $error);
    if($action=='ISSUE')        btnsIssue($params, $data, $error);
    if($action=='LIST')         btnsList($params, $data, $error);
    if($action=='MINT')         btnsMint($params, $data, $error);
    if($action=='RUG')          btnsRug($params, $data, $error);
    if($action=='SLEEP')        btnsSleep($params, $data, $error);
    if($action=='SEND')         btnsSend($params, $data, $error);
    if($action=='SWEEP')        btnsSweep($params, $data, $error);
}

// Handles returning the highest tx_index from transactions table
function getNextTxIndex(){
    global $mysqli;
    $idx = 0;
    $results = $mysqli->query("SELECT tx_index FROM transactions ORDER BY tx_index DESC LIMIT 1");
    if($results && $results->num_rows){
        $row = (object) $results->fetch_assoc();
        $idx = (integer) $row->tx_index;
    } 
    $idx++;
    return $idx;
}

// Handles returning index_tx for a specific tx_hash from the transactions table
function getTxIndex($tx_hash=null){
    global $mysqli;
    $tx_hash_id = createTransaction($tx_hash);
    $results = $mysqli->query("SELECT tx_index FROM transactions WHERE tx_hash_id='{$tx_hash_id}'");
    if($results){
        if($results->num_rows){
            $row = (object) $results->fetch_assoc();
            return $row->tx_index;
        }
    } else {
        bye('Error while trying to lookup tx_index in the transactions table');
    }
}

// Create records in the 'transactions' table
function createTxIndex( $data=null ){
    global $mysqli;
    // Get highest tx_index
    $block_index = $data->BLOCK_INDEX;
    $tx_hash_id  = createTransaction($data->TX_HASH);
    $action_id   = createAction($data->ACTION); 
    $tx_index    = getNextTxIndex();
    $results  = $mysqli->query("SELECT action_id FROM transactions WHERE tx_hash_id='{$tx_hash_id}' LIMIT 1");
    if($results){
        if($results->num_rows==0){
            $results = $mysqli->query("INSERT INTO transactions (tx_index, block_index, tx_hash_id, action_id) values ('{$tx_index}','{$block_index}','{$tx_hash_id}', '{$action_id}')");
            if(!$results)
                byeLog('Error while trying to create record in transactions table');
        }
    } else {
        byeLog('Error while trying to lookup record in transactions table');
    }
}

// Handles adding protocol changes to $protocol_changes global var (used in PROTOCOL_CHANGES constant)
// @param {name}                string  Unique Name for protocol change
// @param {version_major}       integer BTNS Indexer MAJOR version
// @param {version_minor}       integer BTNS Indexer MINOR version
// @param {version_revision}    integer BTNS Indexer REVISION version
// @param {mainnet_block_index} integer BTNS Indexer REVISION version
// @param {testnet_block_index} integer BTNS Indexer REVISION version
// Returns boolean (true) for success or string with specific error message
function addProtocolChange($name=null, $version_major=null, $version_minor=null, $version_revision=null, $mainnet_block_index=null, $testnet_block_index=null){
    global $protocol_changes;
    $error = false;
    // Name must be string
    if(gettype($name)!='string')
      $error = 'name value must be string';
    $arr = array('version_major','version_minor','version_revision','mainnet_block_index','testnet_block_index');
    foreach($arr as $a){
        if(!$error && !is_int(${$a}))
            $error = $a . ' value must be integer';
    }
    if($error){
        return $error;
    } else {
        $protocol_changes[$name] = array($version_major, $version_minor, $version_revision, $mainnet_block_index, $testnet_block_index);
        return true;
    }
}

// Handle validating if a specific protocol change is activated yet
// @param {name}        string  Unique Name for protocol change
// @param {network}     string  Network Name (mainnet/testnet)
// @param {block_index} integer Block index to check if feature is active
function isEnabled($name=null, $network=null, $block_index=null){
    $info = PROTOCOL_CHANGES[$name];
    // Return false if we couldn't find any info on the specific protocol change
    if(isset($info)){
        $version_major       = $info[0];
        $version_minor       = $info[1];
        $version_revision    = $info[2];
        $mainnet_block_index = $info[3];
        $testnet_block_index = $info[4];
        $enable_block_index  = ${$network . '_block_index'};
        if(VERSION_MAJOR >= $version_major && VERSION_MINOR >= $version_minor && VERSION_REVISION >= $version_revision && $block_index >= $enable_block_index)
            return 1;
    }
    return 0;
}

// Handle returning integer format version
function getFormatVersion($format=null){
    $type = gettype($format);
    if($type=='integer')
        return $format;
    // Default to format 0 if none is given
    if($type=='NULL')
        return 0;
    if($type=='string'){
        // Default to format 0 if none is given
        if($format=='')
            return 0;
        // Strip out any quotes and double-quotes
        $format = preg_replace(array('/\"/','/\'/'),'',$format);
    }
    // Convert any numeric strings to integers
    if(is_numeric($format) && !is_float($format))
        return (int) $format;
    // Return NULL if not able to identify format version
    return NULL;
}  

// Handle validating amount format
function isValidAmountFormat($divisible=null, $amount=null){
    [$int, $sats] = explode('.',$amount);
    if(!$divisible && is_numeric($int) && $int==$amount)
        return true;
    if($divisible && is_numeric($int) && (is_null($sats) || is_numeric($sats)))
        return true;
    return false;
}

// Validate if a lock flag value evaluates to 0 (unlocked) or 1 (locked)
function isValidLockValue($value=null){
    $type  = gettype($value);
    $valid = array(0,1);
    // Convert any numeric strings to integer value
    if($type=='string' && is_numeric($value))
        $value = (int) $value;
    // Only return true for 0/1 values
    if(in_array($value, $valid))
        return true;
    return false;
}

// Handle validating lock status
function isValidLock($btInfo=null, $data=null, $lock=null){
    // Get lock VALUE
    $value = $data->{$lock};
    // If we dont have any info on the token, it hasn't been created yet, so all flags are valid
    if(!isset($btInfo))
        return true;
    // If token exists and lock value does not exist yet, its valid
    if($btInfo->{$lock}=="")
        return true;
    // If lock value is not changing, its valid
    if(isset($value) && $btInfo->{$lock}==$value)
        return true;
    // If lock is unlocked and we are locking, its valid
    if(isset($value) && $btInfo->{$lock}==0 && in_array($value,array(0,1)))
        return true;
    return false;
}

// Handle determining if first param is TICK or VERSION
function isLegacyBTNSFormat($params){
    $version = $params[0]; // VERSION or TICK
    // VERSION will max out at 99 (2 chars)
    if(strlen($version)>2)
        return true;
    // VERSION should be NULL or integer
    if(is_string($version) && !is_numeric($version))
        return true;
    // Add more rules here if ppl keep using old BTNS format...
    return false;
}

// Handle setting ACTION PARAMS based on format VERSION (updates BTNS transaction data object)
function setActionParams($data=null, $params=null, $format=null){
    $fields = explode('|',$format);
    foreach($fields as $idx => $field){
        $value = trim($params[$idx]);
        $data->{$field} = (strlen($value)!=0) ? $value : NULL;
    }
    return $data;
}

// Handle getting a list of TICK holders and amounts
// @param {tick}            string  Ticker name
// @param {block_index}     integer Block Index 
// @param {tx_index}        integer tx_index of transaction
function getHolders( $tick=null, $block_index=null, $tx_index=null ){
    global $mysqli, $dbase;
    $holders = [];
    $block   = (is_numeric($block_index)) ? $block_index : 99999999999999;
    $tick_id = createTicker($tick);
    // Get info on decimal precision
    $decimals = getTokenDecimalPrecision($tick_id);
    $whereSql = "";
    // Filter by block_index
    if(is_numeric($block))
        $whereSql .= " AND m.block_index <= '{$block}'";
    // Filter by tx_index
    if(is_numeric($tx_index))
        $whereSql .= " AND t.tx_index < {$tx_index}";
    // Get Credits 
    $sql = "SELECT 
                CAST(SUM(m.amount) AS DECIMAL(60,$decimals)) as credits,
                a.address
            FROM 
                credits m,
                transactions t,
                index_addresses a
            WHERE 
                m.event_id=t.tx_hash_id AND
                m.address_id=a.id AND
                m.tick_id='{$tick_id}'
                {$whereSql}
            GROUP BY a.address";
    $results = $mysqli->query($sql);
    if($results){
        if($results->num_rows){
            while($row = $results->fetch_assoc()){
                $row = (object) $row;
                $holders[$row->address] = $row->credits;
            }
        }
    } else {
        byeLog('Error while trying to get list of credits');
    }
    // Get Debits 
    $sql = "SELECT 
                CAST(SUM(m.amount) AS DECIMAL(60,$decimals)) as debits,
                a.address
            FROM 
                debits m,
                transactions t,
                index_addresses a
            WHERE 
                m.event_id=t.tx_hash_id AND
                m.address_id=a.id AND
                m.tick_id='{$tick_id}'
                {$whereSql}
            GROUP BY a.address";
    $results = $mysqli->query($sql);
    if($results){
        if($results->num_rows){
            while($row = $results->fetch_assoc()){
                $row = (object) $row;
                $balance = bcsub($holders[$row->address], $row->debits, $decimals);
                if($balance > 0)
                    $holders[$row->address] = $balance;
                else
                    unset($holders[$row->address]);
            }
        }
    } else {
        byeLog('Error while trying to get list of debits');
    }
    return $holders;
}

// Handle getting a list of ASSET holders and amounts
// @param {asset}           string  Asset name
// @param {block_index}     integer Block Index 
function getAssetHolders( $asset=null, $block_index=null ){
    global $mysqli, $dbase;
    $holders  = [];
    $block    = (is_numeric($block_index)) ? $block_index : 99999999999999;
    $asset_id = getAssetId($asset);
    // Filter by block_index
    if(is_numeric($block))
        $whereSql .= " AND m.block_index <= '{$block}'";
    // Get Credits 
    $sql = "SELECT 
                CAST(SUM(m.quantity) AS DECIMAL(60,0)) as credits,
                a.address
            FROM 
                {$dbase}.credits m,
                {$dbase}.index_addresses a
            WHERE 
                m.address_id=a.id AND
                m.asset_id='{$asset_id}'
                {$whereSql}
            GROUP BY a.address";
    $results = $mysqli->query($sql);
    if($results){
        if($results->num_rows){
            while($row = $results->fetch_assoc()){
                $row = (object) $row;
                if(isCryptoAddress($row->address))
                    $holders[$row->address] = $row->credits;
            }
        }
    } else {
        byeLog('Error while trying to get list of credits');
    }
    // Get Debits 
    $sql = "SELECT 
                CAST(SUM(m.quantity) AS DECIMAL(60,0)) as debits,
                a.address
            FROM 
                {$dbase}.debits m,
                {$dbase}.index_addresses a
            WHERE 
                m.address_id=a.id AND
                m.asset_id='{$asset_id}'
                {$whereSql}
            GROUP BY a.address";
    $results = $mysqli->query($sql);
    if($results){
        if($results->num_rows){
            while($row = $results->fetch_assoc()){
                $row = (object) $row;
                $balance = bcsub($holders[$row->address], $row->debits, 0);
                if($balance > 0)
                    $holders[$row->address] = $balance;
                else
                    unset($holders[$row->address]);
            }
        }
    } else {
        byeLog('Error while trying to get list of debits');
    }
    return $holders;
}


// Determine if an ticker is distributed to users (held by more than owner)
function isDistributed($tick=null, $block_index=null, $tx_index=null){
    $info    = getTokenInfo($tick, null, $block_index, $tx_index);
    $holders = ($info) ? getHolders($tick, $block_index, $tx_index) : [];
    // More than one holder
    if(count($holders)>1)
        return true;
    // Holder that is not OWNER
    foreach($holders as $address => $amount)
        if($address!=$info->OWNER)
            return true;
    return false;
}

// Validate if a list is a valid type
// @param {tx_hash}  string   TX_HASH to a list
// @param {type}     string   List Type (1=TICK, 2=ASSET, 3=ADDRESS)
function isValidList($tx_hash=null, $type=null){
    if(getListType($tx_hash)==$type)
        return true;
    return false;
}


// Validate if a balances array holds a certain amount of a tick token
function hasBalance($balances=null, $tick=null, $amount=null){
    $tick_id = createTicker($tick);
    $balance = (isset($balances[$tick_id])) ? $balances[$tick_id] : 0;
    if($balance >= $amount)
        return true;
    return false;
}

// Handle deducting TICK AMOUNT from balances and return updated balances array
function debitBalances($balances=null, $tick=null, $amount=null){
    $balance = (isset($balances[$tick])) ? $balances[$tick] : 0;
    $balances[$tick] = $balance - $amount;
    return $balances;
}

// Check if an address is allowed to perform an action on a token (allow/block list)
function isActionAllowed($tick=null, $address=null){
    $info  = getTokenInfo($tick);
    // False if we have an ALLOW_LIST and user is NOT on it
    if($info->ALLOW_LIST && !in_array($address,getList($info->ALLOW_LIST)))
        return false;
    // False if we have an BLOCK_LIST and user IS on it
    if($info->BLOCK_LIST && in_array($address,getList($info->BLOCK_LIST)))
        return false;
    return true;
}

// Validate that token supplys match credits/debits/balances information
function sanityCheck( $block=null ){
    global $mysqli, $network;
    $tickers     = []; // Assoc array of tickers
    $supply      = []; // Assoc array of supplys
    $block_index = $mysqli->real_escape_string($block);
    // Get list of tickers and supply from credits/debits/tokens tables using block_index
    $sql = "SELECT
                DISTINCT(x.tick_id),
                t2.tick,
                t1.supply 
            FROM 
                (
                    SELECT tick_id FROM credits WHERE block_index='{$block_index}' UNION
                    SELECT tick_id FROM debits  WHERE block_index='{$block_index}'
                ) as x,
                tokens t1,
                index_tickers t2
            WHERE
                t1.tick_id=x.tick_id AND
                t2.id=x.tick_id
            ORDER BY t2.tick ASC";
    $results = $mysqli->query($sql);
    if($results){
        if($results->num_rows){
            while($row = $results->fetch_assoc()){
                $row = (object) $row;
                // Add ticker and supply info to assoc arrays
                $tickers[$row->tick] = $row->tick_id;
                $supply[$row->tick]  = (!is_null($row->supply)) ? $row->supply : "0";
            }
        }
    } else {
        byeLog("Error while trying to lookup credits/debits in block : {$block}");
    }
    // Loop through the tickers and validate token supply match credits/debits/balances info
    foreach($tickers as $tick => $tick_id){
        $supplyA = $supply[$tick];               // Supply from tokens table
        $supplyB = getTokenSupplyBalance($tick); // Supply from balances table
        $supplyC = getTokenSupply($tick);        // Supply from credits/debits tables
        if($supplyA!=$supplyB)
            byeLog("SanityError: balances table supply does not match token supply : {$tick}");
        if($supplyA!=$supplyC)
            byeLog("SanityError: credits/debits table supply does not match token supply : {$tick}");
    }
}

// Consolidate Credit and Debit records
function consolidateCreditDebitRecords($type=null, $records=null){
    $arr  = [];
    $data = [];
    foreach($records as $rec){
        [$key, $amount, $destination] = $rec;
        if($type=='credits')
            $key .= '-' . $destination;
        $arr[$key] = ($arr[$key]) ? strval($arr[$key] + $amount) : $amount; 
    }
    foreach($arr as $tick => $amount){
        $info = array($tick, $amount);
        if($type=='credits'){
            [$tick, $destination] = explode('-',$tick);
            $info = array($tick, $amount, $destination);
        } 
        array_push($data, $info);
    }
    return $data;
}

// Get total amount of credit or debit records for a given address, ticker, and action
function getActionCreditDebitAmount($table=null, $action=null, $tick=null, $address=null, $tx_index=null){
    global $mysqli;
    $total   = 0;
    $tick_id = createTicker($tick);
    $addr_id = createAddress($address);
    $data    = getAddressCreditDebit($table, $addr_id, $action, null, $tx_index);
    if($data[$tick_id])
        $total = $data[$tick_id];
    return $total;
}



// Get all data from a given table for a given block
function getBlockTableData($table=null, $block=null){
    global $mysqli;
    $data   = [];
    // Get all block data from table
    $results = $mysqli->query("SELECT tx_index, status_id FROM {$table} WHERE block_index='{$block}' ORDER BY tx_index ASC");
    if($results){
        if($results->num_rows){
            while($row = $results->fetch_assoc())
                array_push($data, (object) $row);
        } else {
            array_push($data, (object) []);
        }
    } else {
        byeLog("Error while trying to lookup records in {$table} table");
    }
    return $data;
}

// Function to get a SHA256 hash of a given data object
function getDataHash($data=null){
    $hash = hash('sha256', json_encode($data));
    return $hash;
}

// Get block hashes using data from the ACTION tables
function getBlockDataHashes($block=null){
    global $mysqli;
    // Define a list of tables
    $tables = [
        'destroys',
        'issues',
        'lists',
        'mints',
        'sends',
    ];
    // Define response info object
    $info = [];
    // Loop through the data tables and dump a quick list of tx_index and status
    foreach($tables as $table){
        $data = getBlockTableData($table, $block);
        $hash = getDataHash($data);
        $info[$table] = [
            'hash' => $hash,
            'data' => $data
        ];
    }
    // Get hashes for data in the credits / debits / transactions tables
    $info = array_merge($info, getBlockHashes($block));
    return $info;
}

// Get block hashes using credits/debits/transactions table data and previous hash
function getBlockHashes($block=null){
    global $mysqli;
    $credits = array();
    $debits  = array();
    $txlist  = array();
    $info    = array();
    $hashes  = array();
    // Get data from credits table
    $results = $mysqli->query("SELECT * FROM credits WHERE block_index='{$block}' ORDER BY block_index ASC, tick_id ASC, address_id ASC, amount DESC");
    if($results){
        if($results->num_rows){
            while($row = $results->fetch_assoc())
                array_push($credits, $row);
        }
    } else {
        byeLog('Error while trying to lookup records in credits table');
    }
    // Get data from debits table
    $results = $mysqli->query("SELECT * FROM debits WHERE block_index='{$block}' ORDER BY block_index ASC, tick_id ASC, address_id ASC, amount DESC");
    if($results){
        if($results->num_rows){
            while($row = $results->fetch_assoc())
                array_push($debits, $row);
        }
    } else {
        byeLog('Error while trying to lookup records in debits table');
    }
    // Get all block data from transactions table
    $results = $mysqli->query("SELECT * FROM transactions WHERE block_index='{$block}' ORDER BY tx_index ASC");
    if($results){
        if($results->num_rows){
            while($row = $results->fetch_assoc())
                array_push($txlist, $row);
        }
    } else {
        byeLog('Error while trying to lookup records in transactions table');
    }
    // Subtract one block from current block
    $block--;
    // Get hashes from the last block to include in this blocks hash
    $sql = "SELECT
                t1.hash as credits,
                t2.hash as debits,
                t3.hash as txlist
            FROM
                blocks b,
                index_transactions t1,
                index_transactions t2,
                index_transactions t3
            WHERE
                t1.id=b.credits_hash_id AND
                t2.id=b.debits_hash_id AND
                t3.id=b.txlist_hash_id AND
                b.block_index='{$block}'";
    $results = $mysqli->query($sql);
    if($results){
        if($results->num_rows)
            $hashes = $results->fetch_assoc();
    } else {
        byeLog('Error while trying to lookup records in transactions table');
    }
    $tables = ['credits','debits','txlist'];
    // Loop through the tables, add previous hash to data, then create new block hash
    foreach($tables as $table){
        $data = ${$table};
        // Include the block_index and previous block hash in the hash calculation for this block hash
        $data['block_index']   = $block;
        $data['previous_hash'] = $hashes[$table];
        $info[$table] = [
            'hash' => getDataHash($data),
            // 'data' => $data,
        ];
    }
    return $info;
}

// Generalized function to handle processing a broadcast transaction
// @param {tx}               object     Transaction object
// @param {tx->source}       string     Source address
// @param {$tx->text}        string     Broadcast `text`
// @param {$tx->version}     integer    Broadcast `value`
// @param {$tx->tx_hash}     string     Transaction hash
// @param {$tx->block_index} string     Block index of tx
function processTransaction($tx=null){
    global $network;
    $error     = false;
    $tx        = (object) $tx;
    $prefixes  = array('/^bt:/','/^btns:/');
    $tx->raw   = preg_replace($prefixes,'',$tx->text);
    $params    = explode('|',$tx->raw);
    $version   = $tx->version;  // Project Version
    $source    = $tx->source;   // Source address

    // Create database records and get ids for tx_hash and source address
    $source_id  = createAddress($tx->source);
    $tx_hash_id = createTransaction($tx->tx_hash);

    // Trim whitespace from any PARAMS
    foreach($params as $idx => $value)
        $params[$idx] = trim($value);

    // Extract ACTION from PARAMS
    $action = strtoupper(array_shift($params)); 

    // Define ACTION aliases
    $aliases = array(
        // Old BRC20/SRC20 actions 
        'TRANSFER' => 'SEND',
        'DEPLOY'   => 'ISSUE',
        // Short action aliases
        'ADDR'     => 'ADDRESS'
    );

    // Set ACTION for any aliases
    foreach($aliases as $alias => $act)
        if($action==$alias)
            $action = $act;

    // Support legacy BTNS format with no VERSION (default to VERSION 0)
    if(in_array($action,array('ISSUE','MINT','SEND')) && isLegacyBTNSFormat($params))
        array_splice($params, 0, 0, 0);

    // Define basic BTNS transaction data object
    $data = (object) array(
        'ACTION'      => $action,          // Action (ISSUE, MINT, SEND, etc)
        'BLOCK_INDEX' => $tx->block_index, // Block index 
        'SOURCE'      => $tx->source,      // Source/Broadcasting address
        'TX_HASH'     => $tx->tx_hash,     // Transaction Hash
        'TX_RAW'      => $tx->raw          // Raw TX string
    );

    // Validate Action
    if(!array_key_exists($action,PROTOCOL_CHANGES)){
        $error = 'invalid: Unknown ACTION';
        $data->ACTION = $action = 'UNKNOWN';
    }

    // Verify action is activated (past ACTIVATION_BLOCK)
    if(!$error && !isEnabled($action, $network, $tx->block_index))
        $error = 'invalid: ACTIVATION_BLOCK';

    // Create a record of this transaction in the transactions table
    createTxIndex($data);

    // Get tx_index of record using tx_hash
    $data->TX_INDEX = getTxIndex($data->TX_HASH);

    // Handle processing the specific BTNS ACTION commands
    btnsAction($action, $params, $data, $error);
}

// Get broadcast transactions for a given block
function getBroadcastTransactions($block){
    global $mysqli, $dbase;
    $data = array();
    // Lookup any BTNS action broadcasts in this block (anything with bt: or btns: prefix)
    $sql = "SELECT
                b.text,
                b.value as version,
                t.hash as tx_hash,
                a.address as source,
                b.block_index as block_index
            FROM
                {$dbase}.broadcasts b,
                {$dbase}.index_transactions t,
                {$dbase}.index_addresses a
            WHERE 
                t.id=b.tx_hash_id AND
                a.id=b.source_id AND
                b.block_index='{$block}' AND
                b.status='valid' AND
                (b.text LIKE 'bt:%' OR b.text LIKE 'btns:%')
            ORDER BY b.tx_index ASC";
    $results = $mysqli->query($sql);
    if($results){
        if($results->num_rows)
            while($row = $results->fetch_assoc())
                array_push($data, $row);
    } else {
        byeLog("Error while trying to lookup BTNS broadcasts");
    }
    return $data;
}

// Get tx_index of the first valid ISSUE action for a given ticker
function getFirstIssuanceTxIndex($tick=null){
    global $mysqli;
    $tick_id = createTicker($tick);
    $sql = "SELECT 
                tx_index 
            FROM 
                issues i,
                index_statuses s
            WHERE 
                i.tick_id={$tick_id} AND 
                s.id=i.status_id AND
                s.status='valid'
            ORDER BY tx_index ASC LIMIT 1";
    $results = $mysqli->query($sql);
    if($results){
        if($results->num_rows){
            $row = (object) $results->fetch_assoc();
            return $row->tx_index;
        }
    } else {
        byeLog("Error while trying to look up tx_index of first valid issuance");
    }
    return false;
}

// Handles validating if a ticker is valid before a given tx_index
function validTickerBeforeTxIndex($tick=null, $txIndex=null){
    $issueIndex = getFirstIssuanceTxIndex($tick);
    if($issueIndex < $txIndex)
        return true;
    return false;
}

// Handle adding a ticker to the $addresses assoc array and $tickers array
function addAddressTicker($address=null, $tick=null){
    global $addresses, $tickers;
    $type = gettype($tick);
    $list = (isset($addresses[$address])) ? $addresses[$address] : [];
    // If $tick is an array, use the array
    if($type=="array"){
        foreach($tick as $t){
            // Add TICK to $addresses
            if(!in_array($t, $list))
                array_push($list, $t);
            // Add TICK to $tickers
            if(!in_array($t, $tickers))
                array_push($tickers, $t);
        }
    } else {
        // Add TICK to $addresses
        if(!in_array($tick, $list))
            array_push($list, $tick);
        // Add TICK to $tickers
        if(!in_array($tick, $tickers))
            array_push($tickers, $tick);
    }
    $addresses[$address] = $list;
}

// Handle displaying runtime information in a nice format
function printRuntime($seconds){
    $msg   = "";
    $hours = floor($seconds / 3600);
    $mins  = floor(($seconds / 60) % 60);
    $secs  = $seconds % 60;
    $ms    = explode(".",$seconds)[1];
    if($hours>0) $msg .= "{$hours} hours ";
    if($mins>0)  $msg .= "{$mins} minutes ";
    if($secs>0||$ms>0)  $msg .= "{$secs}.{$ms} seconds";
    print "Total Execution time: {$msg}\n";
}

// Create record in `addresses` table
function createAddressOption( $data=null ){
    global $mysqli;
    $source_id      = createAddress($data->SOURCE);
    $tx_hash_id     = createTransaction($data->TX_HASH);
    $block_index    = $mysqli->real_escape_string($data->BLOCK_INDEX);
    $status_id      = createStatus($data->STATUS);
    $tx_index       = $mysqli->real_escape_string($data->TX_INDEX);
    $fee_preference = $mysqli->real_escape_string($data->FEE_PREFERENCE);
    $require_memo   = $mysqli->real_escape_string($data->REQUIRE_MEMO);
    // Check if record already exists
    $results = $mysqli->query("SELECT tx_index FROM addresses WHERE tx_hash_id='{$tx_hash_id}'");
    if($results){
        if($results->num_rows){
            // UPDATE record
            $sql = "UPDATE
                        addresses
                    SET
                        fee_preference='{$fee_preference}',
                        require_memo='{$require_memo}',
                        source_id='{$source_id}',
                        block_index='{$block_index}',
                        tx_index='{$tx_index}',
                        status_id='{$status_id}'
                    WHERE 
                        tx_hash_id='{$tx_hash_id}'";
        } else {
            // INSERT record
            $sql = "INSERT INTO addresses (tx_index, source_id, tx_hash_id, block_index, fee_preference, require_memo, status_id) values ('{$tx_index}', '{$source_id}', '{$tx_hash_id}', '{$block_index}', '{$fee_preference}', '{$require_memo}', '{$status_id}')";
        }
        $results = $mysqli->query($sql);
        if(!$results)
            byeLog('Error while trying to create / update a record in the addresses table');
    } else {
        byeLog('Error while trying to lookup record in addresses table');
    }
}

// Create record in `batches` table
function createBatch( $data=null ){
    global $mysqli;
    $source_id      = createAddress($data->SOURCE);
    $tx_hash_id     = createTransaction($data->TX_HASH);
    $block_index    = $mysqli->real_escape_string($data->BLOCK_INDEX);
    $status_id      = createStatus($data->STATUS);
    $tx_index       = $mysqli->real_escape_string($data->TX_INDEX);
    // Check if record already exists
    $results = $mysqli->query("SELECT tx_index FROM batches WHERE tx_hash_id='{$tx_hash_id}'");
    if($results){
        if($results->num_rows){
            // UPDATE record
            $sql = "UPDATE
                        batches
                    SET
                        source_id='{$source_id}',
                        block_index='{$block_index}',
                        tx_index='{$tx_index}',
                        status_id='{$status_id}'
                    WHERE 
                        tx_hash_id='{$tx_hash_id}'";
        } else {
            // INSERT record
            $sql = "INSERT INTO batches (tx_index, source_id, tx_hash_id, block_index, status_id) values ('{$tx_index}', '{$source_id}', '{$tx_hash_id}', '{$block_index}', '{$status_id}')";
        }
        $results = $mysqli->query($sql);
        if(!$results)
            byeLog('Error while trying to create / update a record in the batches table');
    } else {
        byeLog('Error while trying to lookup record in batches table');
    }
}

// Determine if a tx hash is valid or not
function isValidTransactionHash($hash=null){
    if(strlen($hash)==64)
        return 1;
    return 0;
}

// Create record in `airdrops` table
function createAirdrop( $data=null ){
    global $mysqli;
    $tick_id        = createTicker($data->TICK);
    $source_id      = createAddress($data->SOURCE);
    $tx_hash_id     = createTransaction($data->TX_HASH);
    $list_id        = createTransaction($data->LIST);
    $memo_id        = createMemo($data->MEMO);
    $status_id      = createStatus($data->STATUS);
    $tx_index       = $mysqli->real_escape_string($data->TX_INDEX);
    $amount         = $mysqli->real_escape_string($data->AMOUNT);
    $block_index    = $mysqli->real_escape_string($data->BLOCK_INDEX);
    // Check if record already exists
    $sql = "SELECT
                tx_index
            FROM
                airdrops
            WHERE
                tick_id='{$tick_id}' AND
                source_id='{$source_id}' AND
                list_id='{$list_id}' AND
                amount='{$amount}' AND
                tx_hash_id='{$tx_hash_id}'";
    $results = $mysqli->query($sql);
    if($results){
        if($results->num_rows){
            // UPDATE record
            $sql = "UPDATE
                        airdrops
                    SET
                        tx_index='{$tx_index}',
                        block_index='{$block_index}',
                        memo_id='{$memo_id}',
                        status_id='{$status_id}'
                    WHERE 
                        tick_id='{$tick_id}' AND
                        source_id='{$source_id}' AND
                        list_id='{$list_id}' AND
                        amount='{$amount}' AND
                        tx_hash_id='{$tx_hash_id}'";
        } else {
            // INSERT record
            $sql = "INSERT INTO airdrops (tx_index, tick_id, source_id, list_id, amount, memo_id, tx_hash_id, block_index, status_id) values ('{$tx_index}','{$tick_id}', '{$source_id}', '{$list_id}', '{$amount}','{$memo_id}', '{$tx_hash_id}', '{$block_index}', '{$status_id}')";
        }
        $results = $mysqli->query($sql);
        if(!$results)
            byeLog('Error while trying to create / update a record in the airdrops table');
    } else {
        byeLog('Error while trying to lookup record in airdrops table');
    }
}


// Get address preferences for a given address
function getAddressPreferences($address=null, $block_index=null, $tx_index=null){
    global $mysqli;
    $address_id = createAddress($address);
    // Set default address preferences
    $data = (object)[
        'FEE_PREFERENCE' => 2, // 2=Donate FEES to development
        'REQUIRE_MEMO'   => 0  // Require memo on SENDs to this address
    ];
    // Get users ADDRESS preferences right before this tx
    $whereSql = "";
    if(isset($block_index) && is_numeric($block_index))
        $whereSql .= " AND block_index <= {$block_index}";
    if(isset($tx_index) && is_numeric($tx_index))
        $whereSql .= " AND tx_index < '{$tx_index}'";
    $sql = "SELECT
                fee_preference,
                require_memo
            FROM
                addresses a
            WHERE
                source_id='{$address_id}'
                {$whereSql}
            ORDER BY tx_index DESC
            LIMIT 1";
    $results = $mysqli->query($sql);
    if($results){
        if($results->num_rows){
            $row = (object) $results->fetch_assoc();
            $data->FEE_PREFERENCE = $row->fee_preference;
            $data->REQUIRE_MEMO   = $row->require_memo;
        }
    } else {
        byeLog('Error while trying to lookup record in addresses table');
    }
    return $data;
}

// Calculate Transaction fee based on number of database hits
// TODO: Make this code modular, so we can configure fees on actions on a per-chain basis
function getTransactionFee($db_hits=0){
    $cost = 1000;                           // Cost in sats per DB hit
    $sats = bcmul($db_hits, $cost , 0);     // FEE in sats (integer)
    $fee  = bcmul($sats, '0.00000001', 8);  // FEE in decimal (divisible)
    return $fee;
}

// Create record in `fees` table
function createFeeRecord( $data=null ){
    global $mysqli;
    $tick_id        = createTicker($data->TICK);
    $source_id      = createAddress($data->SOURCE);
    $destination_id = createAddress($data->DESTINATION);
    $tx_index       = $mysqli->real_escape_string($data->TX_INDEX);
    $amount         = $mysqli->real_escape_string($data->AMOUNT);
    $method         = $mysqli->real_escape_string($data->METHOD);
    $block_index    = $mysqli->real_escape_string($data->BLOCK_INDEX);
    // Check if record already exists
    $sql = "SELECT
                tx_index
            FROM
                fees
            WHERE
                tx_index='{$tx_index}'";
    $results = $mysqli->query($sql);
    if($results){
        if($results->num_rows){
            // UPDATE record
            $sql = "UPDATE
                        fees
                    SET
                        tick_id='{$tick_id}',
                        source_id='{$source_id}',
                        destination_id='{$destination_id}',
                        amount='{$amount}',
                        method='{$method}',
                        block_index='{$block_index}'
                    WHERE 
                        tx_index='{$tx_index}'";
        } else {
            // INSERT record
            $sql = "INSERT INTO fees (tx_index, block_index, source_id, tick_id, amount, method, destination_id) values ('{$tx_index}', '{$block_index}', '{$source_id}', '{$tick_id}', '{$amount}', '{$method}', '{$destination_id}')";
        }
        $results = $mysqli->query($sql);
        if(!$results)
            byeLog('Error while trying to create / update a record in the fees table');
    } else {
        byeLog('Error while trying to lookup record in fees table');
    }
}

// Create record in `dividends` table
function createDividend( $data=null ){
    global $mysqli;
    $tick_id          = createTicker($data->TICK);
    $dividend_tick_id = createTicker($data->DIVIDEND_TICK);
    $source_id        = createAddress($data->SOURCE);
    $tx_hash_id       = createTransaction($data->TX_HASH);
    $memo_id          = createMemo($data->MEMO);
    $status_id        = createStatus($data->STATUS);
    $tx_index         = $mysqli->real_escape_string($data->TX_INDEX);
    $amount           = $mysqli->real_escape_string($data->AMOUNT);
    $block_index      = $mysqli->real_escape_string($data->BLOCK_INDEX);
    // Check if record already exists
    $sql = "SELECT
                tx_index
            FROM
                dividends
            WHERE
                tick_id='{$tick_id}' AND
                dividend_tick_id='{$dividend_tick_id}' AND
                source_id='{$source_id}' AND
                amount='{$amount}' AND
                tx_hash_id='{$tx_hash_id}'";
    $results = $mysqli->query($sql);
    if($results){
        if($results->num_rows){
            // UPDATE record
            $sql = "UPDATE
                        dividends
                    SET
                        tx_index='{$tx_index}',
                        block_index='{$block_index}',
                        memo_id='{$memo_id}',
                        status_id='{$status_id}'
                    WHERE 
                        tick_id='{$tick_id}' AND
                        dividend_tick_id='{$dividend_tick_id}' AND
                        source_id='{$source_id}' AND
                        amount='{$amount}' AND
                        tx_hash_id='{$tx_hash_id}'";
        } else {
            // INSERT record
            $sql = "INSERT INTO dividends (tx_index, tick_id, source_id, dividend_tick_id, amount, memo_id, tx_hash_id, block_index, status_id) values ('{$tx_index}','{$tick_id}', '{$source_id}', '{$dividend_tick_id}', '{$amount}','{$memo_id}', '{$tx_hash_id}', '{$block_index}', '{$status_id}')";
        }
        $results = $mysqli->query($sql);
        if(!$results)
            byeLog('Error while trying to create / update a record in the dividends table');
    } else {
        byeLog('Error while trying to lookup record in dividends table');
    }
}

?>
