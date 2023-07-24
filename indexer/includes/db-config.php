<?php
/*********************************************************************
 * db-config.php - Database Config / Credentials
 ********************************************************************/

// Mainnet config
if(NETWORK=='mainnet'){
    define("DB_HOST", "localhost");
    define("DB_USER", "mysql_username");
    define("DB_PASS", "mysql_password");
    define("DB_DATA", "BTNS_Counterparty"); // Database where BTNS data is stored
    define("CP_DATA", "Counterparty");      // Database where Counterparty data is stored 
}

// Testnet config
if(NETWORK=='testnet'){
    define("DB_HOST", "localhost");
    define("DB_USER", "mysql_username");
    define("DB_PASS", "mysql_password");
    define("DB_DATA", "BTNS_Counterparty_Testnet"); // Database where BTNS data is stored
    define("CP_DATA", "Counterparty_Testnet");      // Database where Counterparty data is stored 
}

?>
