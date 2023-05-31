<?php
/*********************************************************************
 * bets.php - BET command
 *
 * PARAMS:
 * - FEED_ADDRESS           - The address that hosts the feed to be bet on.
 * - BET_TYPE               - 0 for Bullish CFD, 1 for Bearish CFD, 2 for Equal, 3 for NotEqual.
 * - DEADLINE               - The time at which the bet should be decided/settled, in Unix time (seconds since epoch).
 * - WAGER_TICK             - 1 to 5 characters in length (required)
 * - WAGER_AMOUNT           - The quantityof token to wager (integer, in satoshis).
 * - COUNTERWAGER_TICK      - 1 to 5 characters in length (required)
 * - COUNTERWAGER_AMOUNT    - The minimum quantity of token to be wagered against, for the bets to match.
 * - EXPIRATION             - The number of blocks after which the bet expires if it remains unmatched.
 * - LEVERAGE               - Leverage, as a fraction of 5040 (integer, default=5040)
 * - TARGET_VALUE           - Target value for Equal/NotEqual bet (float, default=null)
 * 
 * FORMATS:
 * - bt:BET|FEED_ADDRESS|BET_TYPE|DEADLINE|WAGER_TICK|WAGER_AMOUNT|COUNTERWAGER_TICK|COUNTERWAGER_AMOUNT|EXPIRATION|LEVERAGE|TARGET_VALUE
 ********************************************************************/
function btnsBet($params=null, $data=null, $error=null){
    global $mysqli;
    // Coming soon
}

?>