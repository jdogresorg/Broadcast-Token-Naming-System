<?php
/*********************************************************************
 * sweep.php - SWEEP command
 *
 * PARAMS:
 * VERSION         - Broadcast Format Version                                         
 * DESTINATION     - address where `token` shall be swept                             
 * SWEEP_BALANCES  - Indicates if address `token` balances should be swept (default=1)
 * SWEEP_OWNERSHIP - Indicates if address `token` balances should be swept (default=1)
 * MEMO            - Optional memo to include                                         
 * 
 * FORMATS:
 * 0 = VERSION|DESTINATION|SWEEP_BALANCES|SWEEP_OWNERSHIP|MEMO
 ********************************************************************/
function btnsSweep($params=null, $data=null, $error=null){
    global $mysqli;
    // Coming soon
}

?>