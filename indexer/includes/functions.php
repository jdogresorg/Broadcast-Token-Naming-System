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
        return;
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


// Create record in `issuance` table
function createIssuance( $data=null ){
    global $mysqli;
    // Convert supply amounts to integers
    $max_supply         = $data->MAX_SUPPLY;
    $max_mint           = $data->MAX_MINT;
    $mint_supply        = $data->MINT_SUPPLY;
    $decimals           = $data->DECIMALS;
    // If we have a valid decimal value, store amounts as satoshis (integers)
    if(is_numeric($decimals) && $decimals>=0 && $decimals<=18){
        $max_supply     = bcmul($data->MAX_SUPPLY,  '1' . str_repeat('0',$decimals),0);
        $max_mint       = bcmul($data->MAX_MINT,    '1' . str_repeat('0',$decimals),0);
        $mint_supply    = bcmul($data->MINT_SUPPLY, '1' . str_repeat('0',$decimals),0);
    }
    $max_supply         = $mysqli->real_escape_string($max_supply);
    $max_mint           = $mysqli->real_escape_string($max_mint);
    $mint_supply        = $mysqli->real_escape_string($mint_supply);
    $decimals           = $mysqli->real_escape_string($decimals);
    $icon               = $mysqli->real_escape_string($data->ICON);
    $block_index        = $mysqli->real_escape_string($data->BLOCK_INDEX);
    $status             = $mysqli->real_escape_string($data->STATUS);
    $tick_id            = createTicker($data->TICK);
    $source_id          = createAddress($data->SOURCE);
    $transfer_id        = createAddress($data->TRANSFER);
    $transfer_supply_id = createAddress($data->TRANSFER_SUPPLY);
    $tx_hash_id         = createTransaction($data->TX_HASH);
    // Check if record already exists
    $results = $mysqli->query("SELECT id FROM issuances WHERE tx_hash_id='{$tx_hash_id}'");
    if($results){
        if($results->num_rows){
            // UPDATE record
            $sql = "UPDATE
                        issuances
                    SET
                        tick_id='{$tick_id}',
                        max_supply='{$max_supply}',
                        max_mint='{$max_mint}',
                        decimals='{$decimals}',
                        icon='{$icon}',
                        mint_supply='{$mint_supply}',
                        transfer_id='{$transfer_id}',
                        transfer_supply_id='{$transfer_supply_id}',
                        source_id='{$source_id}',
                        block_index='{$block_index}',
                        status='{$status}'
                    WHERE 
                        tx_hash_id='{$tx_hash_id}'";
        } else {
            // INSERT record
            $sql = "INSERT INTO issuances (tick_id, max_supply, max_mint, decimals, icon, mint_supply, transfer_id, transfer_supply_id, source_id, tx_hash_id, block_index, status) values ('{$tick_id}', '{$max_supply}', '{$max_mint}', '{$decimals}', '{$icon}', '{$mint_supply}', '{$transfer_id}', '{$transfer_supply_id}', '{$source_id}', '{$tx_hash_id}', '{$block_index}', '{$status}')";
        }
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
    $info         = getTokenInfo($data->TICK);
    $tick_id      = createTicker($data->TICK);
    $source_id    = createAddress($data->SOURCE);
    $transfer_id  = createAddress($data->TRANSFER);
    $tx_hash_id   = createTransaction($data->TX_HASH);
    $amount       = $mysqli->real_escape_string($data->AMOUNT);
    $block_index  = $mysqli->real_escape_string($data->BLOCK_INDEX);
    $status       = $mysqli->real_escape_string($data->STATUS);
    $amount       = $mysqli->real_escape_string($data->AMOUNT);
    // Check if record already exists
    $results = $mysqli->query("SELECT id FROM mints WHERE tx_hash_id='{$tx_hash_id}'");
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
                        status='{$status}'
                    WHERE 
                        tx_hash_id='{$tx_hash_id}'";
        } else {
            // INSERT record
            $sql = "INSERT INTO mints (tick_id, amount, source_id, destination_id, tx_hash_id, block_index, status) values ('{$tick_id}', '{$amount}', '{$source_id}', '{$transfer_id}', '{$tx_hash_id}', '{$block_index}', '{$status}')";
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
    $info         = getTokenInfo($data->TICK);
    $tick_id      = createTicker($data->TICK);
    $source_id    = createAddress($data->SOURCE);
    $tx_hash_id   = createTransaction($data->TX_HASH);
    $block_index  = $mysqli->real_escape_string($data->BLOCK_INDEX);
    $status       = $mysqli->real_escape_string($data->STATUS);
    // Check if record already exists
    $results = $mysqli->query("SELECT id FROM sends WHERE tx_hash_id='{$tx_hash_id}'");
    if($results){
        if($results->num_rows){
            // UPDATE record
            $sql = "UPDATE
                        sends
                    SET
                        tick_id='{$tick_id}',
                        source_id='{$source_id}',
                        block_index='{$block_index}',
                        status='{$status}'
                    WHERE 
                        tx_hash_id='{$tx_hash_id}'";
        } else {
            // INSERT record
            $sql = "INSERT INTO sends (tick_id, source_id, tx_hash_id, block_index, status) values ('{$tick_id}', '{$source_id}', '{$tx_hash_id}', '{$block_index}', '{$status}')";
        }
        $results = $mysqli->query($sql);
        if(!$results)
            byeLog('Error while trying to create / update a record in the transfers table');
    } else {
        byeLog('Error while trying to lookup record in transfers table');
    }
}


// Create / Update record in `tokens` table
function createToken( $data=null ){
    global $mysqli;
    // Convert supply amounts to integers
    $supply     = $data->SUPPLY;
    $max_supply = $data->MAX_SUPPLY;
    $max_mint   = $data->MAX_MINT;
    $decimals   = $data->DECIMALS;
    // If we have a valid decimal value, store amounts as satoshis (integers)
    if(is_numeric($decimals) && $decimals>=0 && $decimals<=18){
        $supply     = bcmul($data->SUPPLY,     '1' . str_repeat('0',$decimals),0);
        $max_supply = bcmul($data->MAX_SUPPLY, '1' . str_repeat('0',$decimals),0);
        $max_mint   = bcmul($data->MAX_MINT,   '1' . str_repeat('0',$decimals),0);
    }
    $max_supply  = $mysqli->real_escape_string($max_supply);
    $max_mint    = $mysqli->real_escape_string($max_mint);
    $decimals    = $mysqli->real_escape_string($decimals);
    $icon        = $mysqli->real_escape_string($data->ICON);
    $tick_id     = createTicker($data->TICK);
    $owner_id    = createAddress($data->OWNER);
    $block_index = $data->BLOCK_INDEX;
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
                        icon='{$icon}',
                        supply='{$supply}',
                        owner_id='{$owner_id}',
                        block_index='{$block_index}'
                    WHERE 
                        tick_id='{$tick_id}'";
        } else {
            // INSERT record
            $sql = "INSERT INTO tokens (tick_id, max_supply, max_mint, decimals, icon, supply, owner_id, block_index) values ('{$tick_id}', '{$max_supply}', '{$max_mint}', '{$decimals}', '{$icon}', '{$supply}', '{$owner_id}','{$block_index}')";
        }
        $results = $mysqli->query($sql);
        if(!$results)
            byeLog('Error while trying to create / update a record in the deploys table');
    } else {
        byeLog('Error while trying to lookup record in deploys table');
    }
}

// Create record in `credits` table
function createCredit( $action=null, $block_index=null, $event=null, $tick=null, $quantity=null, $address=null ){
    global $mysqli;
    $action      = $mysqli->real_escape_string($action);
    $quantity    = $mysqli->real_escape_string($quantity);
    $block_index = $mysqli->real_escape_string($block_index);
    $tick_id     = createTicker($tick);
    $address_id  = createAddress($address);
    $event_id    = createTransaction($event);
    // Check if record already exists
    $results = $mysqli->query("SELECT event_id FROM credits WHERE address_id='{$address_id}' AND event_id='{$event_id}'");
    if($results){
        if($results->num_rows){
            // UPDATE record
            $sql = "UPDATE
                        credits
                    SET
                        action='{$action}',
                        quantity='{$quantity}',
                        block_index='{$block_index}',
                        tick_id='{$tick_id}'
                    WHERE 
                        address_id='{$address_id}' AND
                        event_id='{$event_id}'";
        } else {
            // INSERT record
            $sql = "INSERT INTO credits (block_index, address_id, tick_id, quantity, action, event_id) values ('{$block_index}', '{$address_id}', '{$tick_id}', '{$quantity}', '{$action}', '{$event_id}')";
        }
        $results = $mysqli->query($sql);
        if(!$results)
            byeLog('Error while trying to create / update a record in the credits table');
    } else {
        byeLog('Error while trying to lookup record in credits table');
    }
}

// Create record in `debits` table
function createDebit( $action=null, $block_index=null, $event=null, $tick=null, $quantity=null, $address=null ){
    global $mysqli;
    $action      = $mysqli->real_escape_string($action);
    $quantity    = $mysqli->real_escape_string($quantity);
    $block_index = $mysqli->real_escape_string($block_index);
    $tick_id     = createTicker($tick);
    $address_id  = createAddress($address);
    $event_id    = createTransaction($event);
    // Check if record already exists
    $results = $mysqli->query("SELECT event_id FROM debits WHERE address_id='{$address_id}' AND event_id='{$event_id}'");
    if($results){
        if($results->num_rows){
            // UPDATE record
            $sql = "UPDATE
                        debits
                    SET
                        action='{$action}',
                        quantity='{$quantity}',
                        block_index='{$block_index}',
                        tick_id='{$tick_id}'
                    WHERE 
                        address_id='{$address_id}' AND
                        event_id='{$event_id}'";
        } else {
            // INSERT record
            $sql = "INSERT INTO debits (block_index, address_id, tick_id, quantity, action, event_id) values ('{$block_index}', '{$address_id}', '{$tick_id}', '{$quantity}', '{$action}', '{$event_id}')";
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
    global $mysqli;
    $credits      = array();
    $debits       = array();
    $balances     = array();
    $transactions = array();
    // Get all data from credits table
    $results = $mysqli->query("SELECT * FROM credits WHERE block_index<='{$block}' ORDER BY block_index ASC, tick_id ASC, address_id ASC, quantity DESC");
    if($results){
        if($results->num_rows){
            while($row = $results->fetch_assoc())
                array_push($credits, (object) $row);
        }
    } else {
        byeLog('Error while trying to lookup records in credits table');
    }
    // Get all data from debits table
    $results = $mysqli->query("SELECT * FROM debits WHERE block_index<='{$block}' ORDER BY block_index ASC, tick_id ASC, address_id ASC, quantity DESC");
    if($results){
        if($results->num_rows){
            while($row = $results->fetch_assoc())
                array_push($debits, (object) $row);
        }
    } else {
        byeLog('Error while trying to lookup records in debits table');
    }
    // Get all data from balances table
    $results = $mysqli->query("SELECT * FROM balances WHERE id IS NOT NULL ORDER BY tick_id ASC, address_id ASC, quantity DESC");
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
                        credits_hash_id='{$credits_hash_id}',
                        debits_hash_id='{$debits_hash_id}',
                        balances_hash_id='{$balances_hash_id}',
                        txlist_hash_id='{$txlist_hash_id}'
                    WHERE 
                        block_index='{$block}'";
        } else {
            // INSERT record
            $sql = "INSERT INTO blocks (block_index, credits_hash_id, debits_hash_id, balances_hash_id, txlist_hash_id) values ('{$block}', '{$credits_hash_id}', '{$debits_hash_id}', '{$balances_hash_id}', '{$txlist_hash_id}')";
        }
        $results = $mysqli->query($sql);
        if(!$results)
            byeLog('Error while trying to create / update a record in the blocks table');
    } else {
        byeLog('Error while trying to lookup record in blocks table');
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
                tokens t1,
                index_tickers t2,
                index_tickers t3,
                index_transactions t4,
                index_transactions t5,
                index_addresses a
            WHERE 
                t2.id=t1.tick_id AND
                t3.id=t1.callback_tick_id AND
                t4.id=t1.mint_allow_list_id AND
                t5.id=t1.mint_block_list_id AND
                a.id=t1.owner_id AND
                t1.tick_id='{$tick_id}'";
                // print $sql;
    $results = $mysqli->query($sql);
    if($results){
        if($results->num_rows){
            $row  = (object) $results->fetch_assoc();
            $data = (object) array(
                'TICK'              => $row->tick,
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
                'LOCK_CALLBACK'     => $row->lock_callback
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
                    {$dbase}.assets a1,
                    {$dbase}.index_addresses a2
                WHERE 
                    a2.id=a1.owner_id AND
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
    $sql = "SELECT tick_id, sum(quantity) as quantity FROM credits WHERE address_id='{$address_id}' GROUP BY tick_id";
    $results = $mysqli->query($sql);
    if($results){
        if($results->num_rows){
            while($row = $results->fetch_assoc()){
                $row = (object) $row;
                $data[$row->tick_id] = $row->quantity;
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
    $results = $mysqli->query("SELECT tick_id, sum(quantity) as quantity FROM debits WHERE address_id='{$address_id}' GROUP BY tick_id");
    if($results){
        if($results->num_rows){
            while($row = $results->fetch_assoc()){
                $row = (object) $row;
                $data[$row->tick_id] = $row->quantity;
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
    foreach($credits as $tick_id => $quantity)
        $decimals[$tick_id] = getTokenDecimalPrecision($tick_id);
    // Build out balances (credits - debits)
    foreach($credits as $tick_id => $quantity){
        $credit  = $quantity;
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
                    $sql = "UPDATE balances SET quantity='{$balance}' WHERE address_id='{$address_id}' AND tick_id='{$tick_id}'";
                } else {
                    $sql = "INSERT INTO balances (tick_id, address_id, quantity) values ('{$tick_id}','{$address_id}','{$balance}')";
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
    print "updateTokenInfo tick={$tick}\n";
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
    $sql = "SELECT SUM(quantity) as credits FROM credits WHERE tick_id='{$tick_id}'";
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
    $sql = "SELECT SUM(quantity) as debits FROM debits WHERE tick_id='{$tick_id}'";
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
    var_dump($supply);
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
function getTxIndex($next=null){
    global $mysqli;
    $idx = 0;
    $results = $mysqli->query("SELECT tx_index FROM transactions ORDER BY tx_index DESC LIMIT 1");
    if($results && $results->num_rows){
        $row = (object) $results->fetch_assoc();
        $idx = (integer) $row->tx_index;
    } 
    // Increase by 1 if request wants next available index
    if($next)
        $idx++;
    return $idx;
}

// Create records in the 'transactions' table
function createTxIndex( $data=null ){
    global $mysqli;
    // Get highest tx_index
    $block_index = $data->BLOCK_INDEX;
    $tx_hash_id  = createTransaction($data->TX_HASH);
    $type_id     = createTxType($data->ACTION); 
    $tx_index    = getTxIndex(true);
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

?>