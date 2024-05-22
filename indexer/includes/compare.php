<?php
/*********************************************************************
 * compare.php - Handles comparing ledger against a given database 
 ********************************************************************/
function btnsCompare($database=null){
    global $mysqli, $block, $single, $runtime;

    // Placeholder for any error message
    $error = false;

    // Array of tables containing ledger data
    $tables = [
        'addresses',
        'airdrops',
        'batches',
        'destroys',
        'issues',
        'lists',
        'mints',
        'sends'
    ];

    // Array of ledgers to compare
    $ledgers = array (
        'current' => DB_DATA,
        'compare' => $database
    );

    // Setup placeholders for ledger transactions 
    $data = array();
    foreach($ledgers as $name => $db)
        $data[$name] = array();

    // Verify database exists
    try {
        $database = $mysqli->real_escape_string($database);
        $results  = $mysqli->query("USE {$database}");
    } catch (Exception $e){
        $error = "database {$database} not found";
    }

    // Build out SQL query based on runtime flags
    $whereSql = '';
    $limitSql = ($single) ? 'LIMIT 1' : '';
    if($block){
        $op = ($single) ? '=' : '>=';
        $whereSql = " AND block_index {$op} {$block}";
    }

    // Get list of transactions from each ledger
    foreach($ledgers as $name => $db){
        if(!$error){
            $data[$name]['transactions'] = array();
            print "Getting data for {$name} ledger from {$db} database...\n";
            $sql = "SELECT
                        t1.tx_index,
                        t2.hash as tx_hash,
                        a.action
                    FROM
                        {$db}.transactions t1,
                        {$db}.index_transactions t2,
                        {$db}.index_actions a
                    WHERE 
                        a.id=t1.action_id AND
                        t2.id=t1.tx_hash_id
                        {$whereSql}
                    ORDER BY tx_index ASC
                    {$limitSql}";
            $results = $mysqli->query($sql);
            if($results){
                if($results->num_rows){
                    while($row = $results->fetch_assoc())
                        $data[$name]['transactions'][$row['tx_index']] = $row;
                }
            } else {
                $error = "Error while looking up transactions in the {$db} database";
            }
        }
        if(!$error)
            print "Found " . number_format(count($data[$name]['transactions'])) . " transactions\n";
    }

    // Loop through ledger transactions
    foreach($data['current']['transactions'] as $tx_index => $info){

        // debug: force mismatches
        // $data['compare']['transactions'][1]['tx_hash'] = 'test';
        // $data['compare']['transactions'][1]['action']  = 'test';

        // Compare basic transaction data (tx_index, tx_hash, action)
        foreach(array_keys($info) as $field)
            if(!$error && isset($data['compare']['transactions'][$tx_index]) && $data['compare']['transactions'][$tx_index][$field]!=$info[$field])
                $error = "ERROR: Found ledger {$field} difference at tx_index {$tx_index}! ({$info[$field]} != {$data['compare']['transactions'][$tx_index][$field]})";

        // Lookup transaction statuses for records related to this transaction
        if(!$error){
            $action = strtolower($info['action']);
            $append = (in_array($action,array('address','batch'))) ? 'es' : 's';
            $table  = $action . $append;
            if(in_array($table, $tables)){

                // Handle batches by looping through all tables looking for data
                $arr = ($table=='batch') ? $tables : [$table];

                // Loop through tables with transaction data
                foreach($arr as $table){
                    foreach($ledgers as $name => $db){
                        if(!$error){
                            $data[$name]['txinfo'] = array();
                            $sql = "SELECT
                                        a.address as source,
                                        s.status
                                    FROM
                                        {$db}.{$table} m,
                                        {$db}.index_statuses s,
                                        {$db}.index_addresses a
                                    WHERE
                                        s.id=m.status_id AND
                                        a.id=m.source_id AND
                                        m.tx_index='{$tx_index}'";
                            // print $sql;
                            $results = $mysqli->query($sql);
                            if($results){
                                if($results->num_rows)
                                    while($row = $results->fetch_assoc())
                                        array_push($data[$name]['txinfo'], $row);
                            } else {
                                $error = "Error while looking up {$table} data in the {$db} database";
                            }
                        }
                    }

                    // debug: force mismatches
                    // $data['compare']['txinfo'][0]['source'] = 'test';
                    // $data['compare']['txinfo'][0]['status'] = 'test';

                    // Compare transaction data (source, status)
                    if(!$error){
                        foreach($data['current']['txinfo'] as $idx => $nfo){
                            foreach(array_keys($nfo) as $field)
                                if(!$error && isset($data['compare']['txinfo'][$idx]) && $data['compare']['txinfo'][$idx][$field]!=$nfo[$field])
                                    $error = "ERROR: Found ledger {$field} difference at tx_index {$tx_index}! ({$nfo[$field]} != {$data['compare']['txinfo'][$idx][$field]})";
                        }
                    }
                }
            } else {
                // Throw error if table is not found
                // $error = "Error table {$table} not found\n";
            }
        }

        // Bail out on any error
        if($error)
            break;
    }

    // Display any errors and exit
    if($error)
        byeLog($error);

    // Print out information on the total runtime
    printRuntime($runtime->finish());

    // Notify user comparison is complete
    byeLog("Compare complete.");
}

?>
