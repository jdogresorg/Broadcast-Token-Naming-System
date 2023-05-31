<?php
/*********************************************************************
 * dispensers.php - DISPENSER command
 *
 * PARAMS:
 * - GIVE_TICK      - 1 to 5 characters in length (required)
 * - GIVE_AMOUNT    - Quantity of GIVE_TICK to dispense when triggered
 * - ESCROW_AMOUNT  - Quantity of GIVE_TICK to escrow in dispenser
 * - TRIGGER_TICK   - 1 to 5 characters in length (default=BTC)
 * - TRIGGER_AMOUNT - Quantity of TRIGGER_TICK required per dispense
 * - STATUS         - The state of the dispenser. (0=Open, 10=Closed)
 * - ADDRESS        - Address that you would like to open the dispenser on. (default=broadcasting address)
 * - ORACLE_ADDRESS - address that you would like to use as a price oracle for this dispenser.
 * - ALLOW_LIST     - TX_HASH of a BTNS LIST of addresses to allow trigger dispenser
 * - BLOCK_LIST     - TX_HASH of a BTNS LIST of addresses to NOT allow to trigger a dispenser
 *
 * FORMATS:
 * - bt:DISPENSER|GIVE_TICK|GIVE_AMOUNT|ESCROW_AMOUNT|TRIGGER_TICK|TRIGGER_AMOUNT|STATUS|ADDRESS|ORACLE_ADDRESS|ALLOW_LIST|BLOCK_LIST
 ********************************************************************/
function btnsDispenser($params=null, $data=null, $error=null){
    global $mysqli;
    // Coming soon
}

?>