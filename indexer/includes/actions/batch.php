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

    // Clone the raw data for storage in mints table
    $batch = clone($data);

    /*****************************************************************
     * DEBUGGING - Force params
     ****************************************************************/
    // $data->TX_RAW = "BATCH|0|MINT|0|GAS|60;ISSUE|0|JDOGTEST";

    // Validate that format is known
    $format = getFormatVersion($params[0]);
    if(!$error && ($format===NULL || !in_array($format,array_keys($formats))))
        $error = 'invalid: VERSION (unknown)';

    // Get list of commands (after removing BATCH/VERSION)
    $commands = explode(';',str_replace('BATCH|0|','',$data->TX_RAW));
    if(!$error && count($commands)==0)
        $error = 'invalid: COMMAND (unknown)';

    /*****************************************************************
     * General Validations
     ****************************************************************/

    // Verify all actions are valid
    foreach($commands as $command)
        if(!$error && !array_key_exists(explode('|',$command)[0],PROTOCOL_CHANGES))
            $error = 'invalid: ACTION (unknown)';

    // Determine final status
    $batch->STATUS = $status = ($error) ? $error : 'valid';

    // Print status message 
    print "\n\t BATCH : {$data->SOURCE} : {$batch->STATUS}";

    // Create record in addresses table
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
