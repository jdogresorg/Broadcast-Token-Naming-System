<?php
/*********************************************************************
 * batch.php - BATCH command
 *
 * PARAMS:
 * - VERSION - Broadcast Format Version
 * - COMMAND - Any valid BTNS ACTION with PARAMS
 * 
 * FORMATS:
 * 0 = VERSION|COMMAND;COMMAND
 ********************************************************************/
function btnsBatch($params=null, $data=null, $error=null){
    global $mysqli, $reparse;

    // Define list of known FORMATS
    $formats = array(
        0 => 'VERSION|COMMAND'
    );

    // Define list of ACTIONS and usage limits
    $limits = array(
        'BATCH' => 0,
        'MINT'  => 1,
        'ISSUE' => 1
    );

    // Define list of ACTIONS and count of usage within BATCH
    $actions = array();

    // Clone the raw data for storage in batches table
    $batch = clone($data);

    /*****************************************************************
     * DEBUGGING - Force params
     ****************************************************************/
    // $data->TX_RAW = "BATCH|0|MINT|0|GAS|60;ISSUE|0|JDOGTEST";

    // Validate that format is known
    $format = getFormatVersion($params[0]);
    if(!$error && ($format===NULL || !in_array($format,array_keys($formats))))
        $error = 'invalid: VERSION (unknown)';

    // Get list of commands
    $commands = explode(';',$data->TX_RAW);
    if(!$error && count($commands)==0){
        $error = 'invalid: COMMAND (unknown)';
    } else {
        // Trim BATCH/VERSION from first command
        $commands[0] = str_replace("BATCH|{$format}|",'',$commands[0]);
    }

    // Build out array of ACTIONs and count of times used in BATCH
    foreach($commands as $command){
        $action = explode('|',$command)[0];
        if(!$actions[$action])
            $actions[$action] = 0;
        $actions[$action]++;
    }

    /*****************************************************************
     * General Validations
     ****************************************************************/

    // Verify all command ACTIONs are valid
    foreach($commands as $command)
        if(!$error && !array_key_exists(explode('|',$command)[0],PROTOCOL_CHANGES))
            $error = 'invalid: ACTION (unknown)';

    // Verify command ACTION limits
    foreach($actions as $action => $limit)
        if(!$error && array_key_exists($action,$limits) && $actions[$action]>$limits[$action])
            $error = "invalid: {$action} (limit)";

    // Determine final status
    $batch->STATUS = $status = ($error) ? $error : 'valid';

    // Print status message 
    print "\n\t BATCH : {$data->SOURCE} : {$batch->STATUS}";

    // Create record in batches table
    createBatch($batch);

    // Handle processing the specific BTNS ACTION commands
    if($status=='valid'){
        foreach($commands as $command){
            $params = explode('|',$command);
            $action = strtoupper(array_shift($params));
            btnsAction($action, $params, $data, $error);
        }
    }

}

?>
