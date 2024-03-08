<?php
/*********************************************************************
 * address.php - ADDRESS command
 *
 * PARAMS:
 * - VERSION        - Broadcast Format Version
 * - FEE_PREFERENCE - Set preference for how `FEE` is used
 * - REQUIRE_MEMO   - Require a `MEMO` on any received `SEND`
 * 
 * FORMATS:
 * - 0 = Full
 * 
 ********************************************************************/
function btnsAddress($params=null, $data=null, $error=null){
    global $mysqli, $reparse, $addresses, $tickers;

    // Define list of known FORMATS
    $formats = array(
        0 => 'VERSION|FEE_PREFERENCE|REQUIRE_MEMO'
    );

    /*****************************************************************
     * DEBUGGING - Force params
     ****************************************************************/
    // $str = "0|1|1";
    // $params = explode('|',$str);

    // Validate that broadcast format is known
    $format = getFormatVersion($params[0]);
    if(!$error && ($format===NULL || !in_array($format,array_keys($formats))))
        $error = 'invalid: VERSION (unknown)';

    // Parse PARAMS using given VERSION format and update BTNS transaction data object
    if(!$error)
        $data = setActionParams($data, $params, $formats[$format]);

    /*****************************************************************
     * FORMAT Validations
     ****************************************************************/

    // Verify FEE_PREFERENCE is numeric
    if(!$error && isset($data->FEE_PREFERENCE) && !is_numeric($data->FEE_PREFERENCE))
        $error = "invalid: FEE_PREFERENCE (format)";

    // Verify REQUIRE_MEMO is numeric
    if(!$error && isset($data->REQUIRE_MEMO) && !is_numeric($data->REQUIRE_MEMO))
        $error = "invalid: REQUIRE_MEMO (format)";

    /*****************************************************************
     * General Validations
     ****************************************************************/

    // Verify FEE_PREFERENCE value is valid
    if(!$error && isset($data->FEE_PREFERENCE) && !in_array($data->FEE_PREFERENCE,array(0,1,2)))
        $error = 'invalid: FEE_PREFERENCE';

    // Verify REQUIRE_MEMO value is valid
    if(!$error && isset($data->REQUIRE_MEMO) && !in_array($data->REQUIRE_MEMO,array(0,1)))
        $error = 'invalid: REQUIRE_MEMO';

    // Determine final status
    $data->STATUS = $status = ($error) ? $error : 'valid';

    // Print status message 
    print "\n\t ADDRESS : {$data->SOURCE} : {$data->STATUS}";

    // Create record in addresses table
    createAddressOption($data);

}

?>