<?php
/*********************************************************************
 * protocol_changes.php - Tracks protocol changes and activation blocks
 ********************************************************************/

$protocol_changes = array();

function addProtocolChange($name='', $version_major=null, $version_minor=null, $version_revision=null, $mainnet_block_index=null, $textnet_block_index=null){

}

// addProtocolChange("numeric_asset_names",9,47,1,333500,0);
// {
//   "numeric_asset_names": {
//     "minimum_version_major": 9,
//     "minimum_version_minor": 47,
//     "minimum_version_revision": 1,
//     "block_index": 333500,
//     "testnet_block_index": 0
//   },
// }

?>