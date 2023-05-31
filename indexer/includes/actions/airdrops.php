<?php
/*********************************************************************
 * airdrops.php - AIRDROP command
 *
 * PARAMS:
 * - TICK   - 1 to 5 characters in length (required)
 * - AMOUNT - Amount of tokens to airdrop (required)
 * - LIST   - `TX_HASH` of a BTNS `LIST` command (required)
 * 
 * FORMATS:
 * - bt:AIRDROP|AMOUNT|LIST
 * - bt:AIRDROP|AMOUNT|LIST|LIST
 * - bt:AIRDROP|AMOUNT|LIST|AMOUNT|LIST
 ********************************************************************/
function btnsAirdrop($params=null, $data=null, $error=null){
    global $mysqli;
    // Coming soon
}
