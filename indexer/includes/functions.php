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
    // Convert supply amounts to integers
    $max_supply         = (isset($data->MAX_SUPPLY) && is_numeric($data->MAX_SUPPLY)) ? $data->MAX_SUPPLY : 0;
    $max_mint           = (isset($data->MAX_MINT) && is_numeric($data->MAX_MINT)) ? $data->MAX_MINT : 0;
    $mint_supply        = (isset($data->MINT_SUPPLY) && is_numeric($data->MINT_SUPPLY)) ? $data->MINT_SUPPLY : 0;
    $callback_amount    = (isset($data->CALLBACK_AMOUNT) && is_numeric($data->CALLBACK_AMOUNT)) ? $data->CALLBACK_AMOUNT : 0;
    $decimals           = (isset($data->DECIMALS)) ? $data->DECIMALS : 0;
    // If we have a valid decimal value, store amounts as satoshis (integers)
    if(is_numeric($decimals) && $decimals>=0 && $decimals<=18){
        $max_supply      = bcmul($max_supply,  '1' . str_repeat('0',$decimals),0);
        $max_mint        = bcmul($max_mint,    '1' . str_repeat('0',$decimals),0);
        $mint_supply     = bcmul($mint_supply, '1' . str_repeat('0',$decimals),0);
        $callback_amount = bcmul($callback_amount, '1' . str_repeat('0',$decimals),0);
    }
    $max_supply         = $mysqli->real_escape_string($max_supply);
    $max_mint           = $mysqli->real_escape_string($max_mint);
    $mint_supply        = $mysqli->real_escape_string($mint_supply);
    $decimals           = $mysqli->real_escape_string($decimals);
    $description        = $mysqli->real_escape_string($data->DESCRIPTION);
    $block_index        = $mysqli->real_escape_string($data->BLOCK_INDEX);
    $tx_index           = $mysqli->real_escape_string($data->TX_INDEX);
    $status             = $mysqli->real_escape_string($data->STATUS);
    // Force lock fields to integer values 
    $lock_supply        = ($data->LOCK_SUPPLY==1) ? 1 : 0;
    $lock_supply        = ($data->LOCK_SUPPLY==1) ? 1 : 0;
    $lock_mint          = ($data->LOCK_MINT==1) ? 1 : 0;
    $lock_description   = ($data->LOCK_DESCRIPTION==1) ? 1 : 0;
    $lock_rug           = ($data->LOCK_RUG==1) ? 1 : 0;
    $lock_sleep         = ($data->LOCK_SLEEP==1) ? 1 : 0;
    $lock_callback      = ($data->LOCK_CALLBACK==1) ? 1 : 0;
    $callback_block     = ($data->CALLBACK_BLOCK>0) ? $data->CALLBACK_BLOCK : 0;
    $callback_amount    = $mysqli->real_escape_string($callback_amount);
    $callback_tick_id   = createTicker($data->CALLBACK_TICK);
    $tick_id            = createTicker($data->TICK);
    $source_id          = createAddress($data->SOURCE);
    $transfer_id        = createAddress($data->TRANSFER);
    $transfer_supply_id = createAddress($data->TRANSFER_SUPPLY);
    $tx_hash_id         = createTransaction($data->TX_HASH);
    $mint_allow_list_id = createTransaction($data->MINT_ALLOW_LIST);
    $mint_block_list_id = createTransaction($data->MINT_BLOCK_LIST);
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
                        lock_supply='{$lock_supply}',
                        lock_mint='{$lock_mint}',
                        lock_description='{$lock_description}',
                        lock_rug='{$lock_rug}',
                        lock_sleep='{$lock_sleep}',
                        lock_callback='{$lock_callback}',
                        callback_block='{$callback_block}',
                        callback_tick_id='{$callback_tick_id}',
                        callback_amount='{$callback_amount}',
                        mint_allow_list_id='{$mint_allow_list_id}',
                        mint_block_list_id='{$mint_block_list_id}',
                        source_id='{$source_id}',
                        block_index='{$block_index}',
                        tx_index='{$tx_index}',
                        status_id='{$status_id}'
                    WHERE 
                        tx_hash_id='{$tx_hash_id}'";
        } else {
            // INSERT record
            $sql = "INSERT INTO issues (tick_id, max_supply, max_mint, decimals, description, mint_supply, transfer_id, transfer_supply_id, lock_supply, lock_mint, lock_description, lock_rug, lock_sleep, lock_callback, callback_block, callback_tick_id, callback_amount, mint_allow_list_id, mint_block_list_id, source_id, tx_hash_id, block_index, tx_index, status_id) values ('{$tick_id}', '{$max_supply}', '{$max_mint}', '{$decimals}', '{$description}', '{$mint_supply}', '{$transfer_id}', '{$transfer_supply_id}', '{$lock_supply}', '{$lock_mint}', '{$lock_description}', '{$lock_rug}', '{$lock_sleep}', '{$lock_callback}', '{$callback_block}', '{$callback_tick_id}', '{$callback_amount}', '{$mint_allow_list_id}', '{$mint_block_list_id}','{$source_id}', '{$tx_hash_id}', '{$block_index}', '{$tx_index}', '{$status_id}')";
        }
        // print $sql;
        $results = $mysqli->query($sql);
        if(!$results)
            byeLog('Error while trying to create / update a record in the deploys table');
    } else {
        byeLog('Error while trying to lookup record in deploys table');
    }
}


// Create record in `mints` table
function createMint( $data=null ){
    global $mysqli;
    $tick_id      = createTicker($data->TICK);
    $source_id    = createAddress($data->SOURCE);
    $transfer_id  = createAddress($data->TRANSFER);
    $tx_hash_id   = createTransaction($data->TX_HASH);
    $status_id    = createStatus($data->STATUS);
    $tx_index     = $mysqli->real_escape_string($data->TX_INDEX);
    $amount       = $mysqli->real_escape_string($data->AMOUNT);
    $block_index  = $mysqli->real_escape_string($data->BLOCK_INDEX);
    $amount       = $mysqli->real_escape_string($data->AMOUNT);
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
                        destination_id='{$transfer_id}',
                        source_id='{$source_id}',
                        block_index='{$block_index}',
                        tx_index='{$tx_index}',
                        status_id='{$status_id}'
                    WHERE 
                        tx_hash_id='{$tx_hash_id}'";
        } else {
            // INSERT record
            $sql = "INSERT INTO mints (tx_index, tick_id, amount, source_id, destination_id, tx_hash_id, block_index, status_id) values ('{$tx_index}','{$tick_id}', '{$amount}', '{$source_id}', '{$transfer_id}', '{$tx_hash_id}', '{$block_index}', '{$status_id}')";
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

// Create / Update record in `tokens` table
function createToken( $data=null ){
    global $mysqli;
    // Convert supply amounts to integers
    $supply             = (isset($data->SUPPLY) && is_numeric($data->SUPPLY)) ? $data->SUPPLY : 0;
    $max_supply         = (isset($data->MAX_SUPPLY) && is_numeric($data->MAX_SUPPLY)) ? $data->MAX_SUPPLY : 0;
    $max_mint           = (isset($data->MAX_MINT) && is_numeric($data->MAX_MINT)) ? $data->MAX_MINT : 0;
    $mint_supply        = (isset($data->MINT_SUPPLY) && is_numeric($data->MINT_SUPPLY)) ? $data->MINT_SUPPLY : 0;
    $callback_amount    = (isset($data->CALLBACK_AMOUNT) && is_numeric($data->CALLBACK_AMOUNT)) ? $data->CALLBACK_AMOUNT : 0;
    $decimals           = (isset($data->DECIMALS)) ? $data->DECIMALS : 0;
    // If we have a valid decimal value, store amounts as satoshis (integers)
    if(is_numeric($decimals) && $decimals>=0 && $decimals<=18){
        $max_supply      = bcmul($max_supply,  '1' . str_repeat('0',$decimals),0);
        $max_mint        = bcmul($max_mint,    '1' . str_repeat('0',$decimals),0);
        $mint_supply     = bcmul($mint_supply, '1' . str_repeat('0',$decimals),0);
        $callback_amount = bcmul($callback_amount, '1' . str_repeat('0',$decimals),0);
    }
    $supply             = $mysqli->real_escape_string($supply);
    $max_supply         = $mysqli->real_escape_string($max_supply);
    $max_mint           = $mysqli->real_escape_string($max_mint);
    $decimals           = $mysqli->real_escape_string($decimals);
    $description        = $mysqli->real_escape_string($data->DESCRIPTION);
    $block_index        = $mysqli->real_escape_string($data->BLOCK_INDEX);
    // Force lock fields to integer values 
    $lock_supply        = ($data->LOCK_SUPPLY==1) ? 1 : 0;
    $lock_supply        = ($data->LOCK_SUPPLY==1) ? 1 : 0;
    $lock_mint          = ($data->LOCK_MINT==1) ? 1 : 0;
    $lock_description   = ($data->LOCK_DESCRIPTION==1) ? 1 : 0;
    $lock_rug           = ($data->LOCK_RUG==1) ? 1 : 0;
    $lock_sleep         = ($data->LOCK_SLEEP==1) ? 1 : 0;
    $lock_callback      = ($data->LOCK_CALLBACK==1) ? 1 : 0;
    $callback_block     = ($data->CALLBACK_BLOCK>0) ? $data->CALLBACK_BLOCK : 0;
    $callback_amount    = $mysqli->real_escape_string($callback_amount);
    $callback_tick_id   = createTicker($data->CALLBACK_TICK);
    $tick_id            = createTicker($data->TICK);
    $owner_id           = createAddress($data->OWNER);
    $mint_allow_list_id = createTransaction($data->MINT_ALLOW_LIST);
    $mint_block_list_id = createTransaction($data->MINT_BLOCK_LIST);
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
                        lock_supply='{$lock_supply}',
                        lock_mint='{$lock_mint}',
                        lock_description='{$lock_description}',
                        lock_rug='{$lock_rug}',
                        lock_sleep='{$lock_sleep}',
                        lock_callback='{$lock_callback}',
                        callback_block='{$callback_block}',
                        callback_tick_id='{$callback_tick_id}',
                        callback_amount='{$callback_amount}',
                        mint_allow_list_id='{$mint_allow_list_id}',
                        mint_block_list_id='{$mint_block_list_id}',
                        block_index='{$block_index}',
                        supply='{$supply}',
                        owner_id='{$owner_id}'
                    WHERE 
                        tick_id='{$tick_id}'";
        } else {
            // INSERT record
            $sql = "INSERT INTO tokens (tick_id, max_supply, max_mint, decimals, description, lock_supply, lock_mint, lock_description, lock_rug, lock_sleep, lock_callback, callback_block, callback_tick_id, callback_amount, mint_allow_list_id, mint_block_list_id, owner_id, supply, block_index) values ('{$tick_id}', '{$max_supply}', '{$max_mint}', '{$decimals}', '{$description}', '{$lock_supply}', '{$lock_mint}', '{$lock_description}', '{$lock_rug}', '{$lock_sleep}', '{$lock_callback}', '{$callback_block}', '{$callback_tick_id}', '{$callback_amount}', '{$mint_allow_list_id}', '{$mint_block_list_id}', '{$owner_id}','{$supply}', '{$block_index}')";
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
    $credits      = array();
    $debits       = array();
    $balances     = array();
    $transactions = array();
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

    // Get all data from credits table
    $results = $mysqli->query("SELECT * FROM credits WHERE block_index<='{$block}' ORDER BY block_index ASC, tick_id ASC, address_id ASC, amount DESC");
    if($results){
        if($results->num_rows){
            while($row = $results->fetch_assoc())
                array_push($credits, (object) $row);
        }
    } else {
        byeLog('Error while trying to lookup records in credits table');
    }
    // Get all data from debits table
    $results = $mysqli->query("SELECT * FROM debits WHERE block_index<='{$block}' ORDER BY block_index ASC, tick_id ASC, address_id ASC, amount DESC");
    if($results){
        if($results->num_rows){
            while($row = $results->fetch_assoc())
                array_push($debits, (object) $row);
        }
    } else {
        byeLog('Error while trying to lookup records in debits table');
    }
    // Get all data from balances table
    $results = $mysqli->query("SELECT * FROM balances WHERE id IS NOT NULL ORDER BY tick_id ASC, address_id ASC, amount DESC");
    if($results){
        if($results->num_rows){
            while($row = $results->fetch_assoc())
                array_push($balances, (object) $row);
        }
    } else {
        byeLog('Error while trying to lookup records in balances table');
    }
    // Get all data from transactions table
    $results = $mysqli->query("SELECT * FROM transactions WHERE tx_index IS NOT NULL ORDER BY tx_index ASC");
    if($results){
        if($results->num_rows){
            while($row = $results->fetch_assoc())
                array_push($transactions, (object) $row);
        }
    } else {
        byeLog('Error while trying to lookup records in balances table');
    }
    // Generate SHA256 hashes based on the json object
    // This is a rough/dirty way to get some sha256 hashes qucikly... def should revisit when not in a rush
    $credits_hash            = hash('sha256', json_encode($credits));
    $debits_hash             = hash('sha256', json_encode($debits));
    $balances_hash           = hash('sha256', json_encode($balances));
    $transactions_hash       = hash('sha256', json_encode($transactions));
    $credits_hash_short      = substr($credits_hash,0,5);
    $debits_hash_short       = substr($debits_hash,0,5);
    $balances_hash_short     = substr($balances_hash,0,5);
    $transactions_hash_short = substr($transactions_hash,0,5);
    $credits_hash_id         = createTransaction($credits_hash);
    $debits_hash_id          = createTransaction($debits_hash);
    $balances_hash_id        = createTransaction($balances_hash);
    $txlist_hash_id          = createTransaction($transactions_hash);
    print "\n\t [credits:{$credits_hash_short} debits:{$debits_hash_short} balances:{$balances_hash_short} txlist:{$transactions_hash_short}]";
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
                        balances_hash_id='{$balances_hash_id}',
                        txlist_hash_id='{$txlist_hash_id}'
                    WHERE 
                        block_index='{$block}'";
        } else {
            // INSERT record
            $sql = "INSERT INTO blocks (block_index, block_time, credits_hash_id, debits_hash_id, balances_hash_id, txlist_hash_id) values ('{$block}', '{$block_time}', '{$credits_hash_id}', '{$debits_hash_id}', '{$balances_hash_id}', '{$txlist_hash_id}')";
        }
        $results = $mysqli->query($sql);
        if(!$results)
            byeLog('Error while trying to create / update a record in the blocks table');
    } else {
        byeLog('Error while trying to lookup record in blocks table');
    }
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


// Handle getting token information for a given tick
function getTokenInfo($tick=null){
    global $mysqli;
    $type = gettype($tick);
    $data = false; // Default to 0 (no supply)
    if($type==='integer' || is_numeric($tick))
        $tick_id = $tick;
    if($type==='string' && !is_numeric($tick))
        $tick_id = createTicker($tick);
    // Get data from tokens table
    $sql = "SELECT 
                t2.tick,
                t1.max_supply,
                t1.max_mint,
                t1.decimals,
                t1.description,
                t1.block_index,
                t1.supply,
                t1.lock_supply,
                t1.lock_mint,
                t1.lock_description,
                t1.lock_rug,
                t1.lock_sleep,
                t1.lock_callback,
                t1.callback_block,
                t3.tick as callback_tick,            
                t1.callback_amount,
                t4.hash as mint_allow_list,
                t5.hash as mint_block_list,
                a.address as owner
            FROM 
                tokens t1
                LEFT JOIN index_tickers t3 on (t3.id=t1.callback_tick_id)
                LEFT JOIN index_transactions t4 on (t4.id=t1.mint_allow_list_id)
                LEFT JOIN index_transactions t5 on (t5.id=t1.mint_block_list_id),
                index_tickers t2,
                index_addresses a
            WHERE 
                t2.id=t1.tick_id AND
                a.id=t1.owner_id AND
                t1.tick_id='{$tick_id}'";
                // print $sql;
    $results = $mysqli->query($sql);
    if($results){
        if($results->num_rows){
            $row  = (object) $results->fetch_assoc();
            $data = (object) array(
                'TICK'              => $row->tick,
                'BLOCK_INDEX'       => $row->block_index,
                'MAX_SUPPLY'        => $row->max_supply,
                'MAX_MINT'          => $row->max_mint,
                'DECIMALS'          => $row->decimals,
                'DESCRIPTION'       => $row->description,
                'SUPPLY'            => $row->supply,
                'OWNER'             => $row->owner,
                'LOCK_SUPPLY'       => $row->lock_supply,
                'LOCK_MINT'         => $row->lock_mint,
                'LOCK_DESCRIPTION'  => $row->lock_description,
                'LOCK_RUG'          => $row->lock_rug,
                'LOCK_SLEEP'        => $row->lock_sleep,
                'LOCK_CALLBACK'     => $row->lock_callback,
                'CALLBACK_TICK'     => $row->callback_tick,
                'CALLBACK_BLOCK'    => $row->callback_block,
                'CALLBACK_AMOUNT'   => $row->callback_amount,
                'MINT_ALLOW_LIST'   => $row->mint_allow_list,
                'MINT_BLOCK_LIST'   => $row->mint_block_list
            );
        } 
    } else {
        byeLog("Error while trying to lookup token info for : {$tick}");
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
function getAssetInfo($asset=null){
    global $mysqli, $dbase;
    $type = gettype($tick);
    $data = false;
    if($type==='integer' || is_numeric($asset)){
        $asset_id = $asset;
    } else {
        $asset_id = getAssetId($asset);
    }
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
    }
    return $data;
}

// Handle getting decimal precision for a given tick
function getTokenDecimalPrecision($tick=null){
    $info = getTokenInfo($tick);
    $decimals = ($info) ? $info->DECIMALS : 0;
    return $decimals;
}

// Handle getting credits for a given address
function getAddressCredits($address=null){
    global $mysqli;
    $data = array(); // Assoc array to store tick/credits
    $type = gettype($address);
    if($type==='integer' || is_numeric($address))
        $address_id = $address;
    if($type==='string' && !is_numeric($address))
        $address_id = createAddress($address);
    // Get Credits
    $sql = "SELECT tick_id, sum(amount) as amount FROM credits WHERE address_id='{$address_id}' GROUP BY tick_id";
    $results = $mysqli->query($sql);
    if($results){
        if($results->num_rows){
            while($row = $results->fetch_assoc()){
                $row = (object) $row;
                $data[$row->tick_id] = $row->amount;
            }
        }
    } else {
        byeLog("Error while trying to lookup address credits for : {$address}");
    }
    return $data;
}

// Handle getting debits for a given address
function getAddressDebits($address=null){
    global $mysqli;
    $data = array(); // Assoc array to store tick/debits
    $type = gettype($address);
    if($type==='integer' || is_numeric($address))
        $address_id = $address;
    if($type==='string' && !is_numeric($address))
        $address_id = createAddress($address);
    // Get Debits
    $results = $mysqli->query("SELECT tick_id, sum(amount) as amount FROM debits WHERE address_id='{$address_id}' GROUP BY tick_id");
    if($results){
        if($results->num_rows){
            while($row = $results->fetch_assoc()){
                $row = (object) $row;
                $data[$row->tick_id] = $row->amount;
            }
        }
    } else {
        byeLog("Error while trying to lookup address debits for : {$address}");
    }
    return $data;
}

// Handle getting address balances for a given address
function getAddressBalances($address=null){
    global $mysqli;
    if($type==='integer' || is_numeric($address))
        $address_id = $address;
    if($type==='string' && !is_numeric($address))
        $address_id = createAddress($address);
    $credits  = getAddressCredits($address_id);
    $debits   = getAddressDebits($address_id);
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
        if(is_numeric($balance) && $balance>0)
            $balances[$tick_id] = $balance;
    }
    return $balances;
}

// Create/Update records in the 'balances' table
function updateAddressBalance( $address=null){
    global $mysqli;
    $type = gettype($address);
    if($type==='integer' || is_numeric($address))
        $address_id = $address;
    if($type==='string' && !is_numeric($address))
        $address_id = createAddress($address);
    $balances = getAddressBalances($address_id);
    if(count($balances)){
        foreach($balances as $tick_id => $balance){
            // print "processing balance tick={$tick_id} balance={$balance}\n";
            // Check if we already have a record for this address/tick_id
            $sql     = "SELECT id FROM balances WHERE address_id='{$address_id}' AND tick_id='{$tick_id}' LIMIT 1";
            $results = $mysqli->query($sql);
            if($results){
                $update = ($results->num_rows) ? true : false;
                if($update){
                    $sql = "UPDATE balances SET amount='{$balance}' WHERE address_id='{$address_id}' AND tick_id='{$tick_id}'";
                } else {
                    $sql = "INSERT INTO balances (tick_id, address_id, amount) values ('{$tick_id}','{$address_id}','{$balance}')";
                }
                // Create/Update balances records
                if($update||(!$update && $balance)){
                    $results = $mysqli->query($sql);
                    if(!$results){
                        $action = ($update) ? 'update' : 'created';
                        byeLog('Error while trying to ' . $action  . ' balance record for address=' . $address . ' tick_id=' . $tick_id);
                    }
                }
            } else {
                byeLog('Error while trying to lookup balances record for address=' . $address . ' tick_id=' . $tick_id);
            }
        }
    }
}


// Handle updating address balances (credits-debits=balance)
// @param {address} boolean Full update
// @param {address} string  Address string
// @param {address} array   Array of address strings
function updateBalances( $address=null ){
    global $mysqli;
    $addrs = [];
    $type  = gettype($address);
    if($type==='array')
        foreach($address as $addr)
            if(!is_null($addr) && $addr!='')
                array_push($addrs, $addr);
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
        updateAddressBalance($address);
}


// Handle updating token information (supply, price, etc)
// @param {tickers} boolean Full update
// @param {tickers} string  Ticker string
// @param {tickers} array   Array of address strings
function updateTokens( $tickers=null){
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
    if($type==='boolean' && $address===true){
        $results = $mysqli->query("SELECT tick_id FROM tokens");
        if($results){
            if($results->num_rows)
                while($row = $results->fetch_assoc())
                    array_push($tokens, $row['tick_id']);
        } else {
            byeLog('Error while trying to get list of all tokens');
        }
    }
    // Loop through tokens and update basic ifno
    foreach($tokens as $tick)
        updateTokenInfo($tick);
}

// Handle getting token info (supply, price, etc) and updating the `tokens` table
function updateTokenInfo( $tick=null){
    // print "updateTokenInfo tick={$tick}\n";
    global $mysqli;
    $type = gettype($tick);
    if($type==='integer' || is_numeric($tick))
        $tick_id = $tick;
    if($type==='string' && !is_numeric($tick))
        $tick_id = createTicker($tick);
    // Lookup current token information
    $data = getTokenInfo($tick);
    // Get current token supply (current token supply)
    $data->SUPPLY = getTokenSupply($tick);
    // Update the record in `tokens` table
    createToken($data);
}

// Get token supply from credits/debits table (credits - debits = supply)
function getTokenSupply( $tick=null ){
    global $mysqli;
    $credits = 0;
    $debits  = 0;
    $supply  = 0;
    $type = gettype($tick);
    if($type==='integer' || is_numeric($tick))
        $tick_id = $tick;
    if($type==='string' && !is_numeric($tick))
        $tick_id = createTicker($tick);
    // Get Credits
    $sql = "SELECT SUM(amount) as credits FROM credits WHERE tick_id='{$tick_id}'";
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
    $sql = "SELECT SUM(amount) as debits FROM debits WHERE tick_id='{$tick_id}'";
    $results = $mysqli->query($sql);
    if($results){
        if($results->num_rows){
            $row = (object) $results->fetch_assoc();
            $debits = $row->debits;
        }
    } else {
        byeLog('Error while trying to get list of debits');
    }
    $decimals = getTokenDecimalPrecision($tick_id);
    $supply   = bcsub($credits, $debits, $decimals);
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


// Create records in the 'tx_index_types' table and return record id
function createTxType( $type=null ){
    global $mysqli;
    $type    = $mysqli->real_escape_string($type);
    $results = $mysqli->query("SELECT id FROM index_tx_types WHERE type='{$type}' LIMIT 1");
    if($results){
        if($results->num_rows){
            $row = $results->fetch_assoc();
            return $row['id'];
        } else {
            $results = $mysqli->query("INSERT INTO index_tx_types (type) values ('{$type}')");
            if($results){
                return $mysqli->insert_id;
            } else {
                byeLog('Error while trying to create record in index_tx_types table');
            }
        }
    } else {
        byeLog('Error while trying to lookup record in index_tx_types table');
    }
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
    $type_id     = createTxType($data->ACTION); 
    $tx_index    = getNextTxIndex();
    $results  = $mysqli->query("SELECT type_id FROM transactions WHERE tx_hash_id='{$tx_hash_id}' LIMIT 1");
    if($results){
        if($results->num_rows==0){
            $results = $mysqli->query("INSERT INTO transactions (tx_index, block_index, tx_hash_id, type_id) values ('{$tx_index}','{$block_index}','{$tx_hash_id}', '{$type_id}')");
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
        // if(VERSION_MAJOR < $version_major) return false;
        // if(VERSION_MINOR < $version_minor) return false;
        // if(VERSION_REVISION < $version_revision) return false;
        if($block_index >= $enable_block_index) 
            return true;
    }
    return false;
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
    if(!$divisible && is_numeric($int))
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
    if(is_string($version))
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

// Handle getting a list of holders
// @param {tick}  string   TICK or ASSET name
// @param {type}  integer  Holder Type (1=TICK, 2=ASSET)
function getHolders( $tick=null, $type=null){
    global $mysqli, $dbase;
    $holders = [];
    $type = ($type>1) ? $type : 1;
    $tick = $mysqli->real_escape_string($tick);
    // Query TICK Holders
    if($type==1){
        $sql = "SELECT
                    a.address,
                    b.amount
                FROM
                    balances b,
                    index_tickers t,
                    index_addresses a
                WHERE
                    t.id=b.tick_id AND
                    a.id=b.address_id AND
                    t.tick='{$tick}'";
    }
    // Query ASSET holders
    if($type==2){
        // Handle CP ASSETS here... coming soon
    }
    $results = $mysqli->query($sql);
    if($results){
        if($results->num_rows){
            while($row = $results->fetch_assoc()){
                $row = (object) $row;
                $holders[$row->address] = $row->amount;
            }
        }
    } else {
        byeLog("Error while trying to lookup holders of : {$tick}");
    }
    return $holders;
}

// Determine if an ticker is distributed to users (held by more than owner)
function isDistributed($tick=null){
    $info    = getTokenInfo($tick);
    $holders = ($info) ? getHolders($data->TICK) : [];
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
    global $mysqli;
    // Coming soon... gotta get list functionality written first... lol
    return false;

}


// Validate if a balances array holds a certain amount of a tick token
function hasBalance($balances=null, $tick=null, $amount=null){
    $balance = (isset($balances[$tick])) ? $balances[$tick] : 0;
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
?>