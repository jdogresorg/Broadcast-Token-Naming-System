<?php
/*********************************************************************
 * airdrops.php - AIRDROP command
 *
 * PARAMS:
 * - VERSION - Broadcast Format Version
 * - TICK    - 1 to 250 characters in length
 * - AMOUNT  - Amount of tokens to airdrop
 * - LIST    - `TX_HASH` of a BTNS `LIST`
 * 
 * FORMATS:
 * 0 = VERSION|TICK|AMOUNT|LIST|LIST
 * 1 = VERSION|TICK|AMOUNT|LIST|TICK|AMOUNT|LIST
 ********************************************************************/
function btnsAirdrop($params=null, $data=null, $error=null){
    global $mysqli, $reparse;
    // Coming soon
}

?>