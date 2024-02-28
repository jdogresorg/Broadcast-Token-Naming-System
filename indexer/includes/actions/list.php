<?php
/*********************************************************************
 * list.php - LIST command
 * 
 * PARAMS:
 * - VERSION       -  Broadcast Format Version
 * - TYPE          -  List type (1=TICK, 2=ASSET, 3=ADDRESS)
 * - ITEM          -  Any valid `TICK`, `ASSET`, or address
 * - EDIT          -  Edit action (1=ADD, 2=REMOVE)
 * - LIST_TX_HASH  -  `TX_HASH` of existing BTNS `LIST`
 * 
 * FORMATS :
 * - 0 = Create LIST
 * - 1 = Edit LIST
 * 
 ********************************************************************/
function btnsList( $params=null, $data=null, $error=null){
    global $mysqli, $reparse, $tickers, $addresses;

    // Define list of known FORMATS
    $formats = array(
        0 => 'VERSION|TYPE|ITEM',
        1 => 'VERSION|EDIT|LIST_TX_HASH|ITEM'
    );

    /*****************************************************************
     * DEBUGGING - Force params
     ****************************************************************/
    // $str = "0|1|JDOG|BRRR|TEST";
    // $str = "0|2|XCP|RAREPEPE|JPMCHASE|A4211151421115130001";
    // $str = "0|3|1JDogZS6tQcSxwfxhv6XKKjcyicYA4Feev|1FWDonkMbC6hL64JiysuggHnUAw2CKWszs|1BTNSGASK5En7rFurDJ79LQ8CVYo2ecLC8";
    // $str = "1|2|860dc04b2b59657005a0955f282043c04bc9d5520562d317119722956043ffee|1JDogZS6tQcSxwfxhv6XKKjcyicYA4Feev|1FWDonkMbC6hL64JiysuggHnUAw2CKWszs";
    // $str = "1|1|b21f92568cf4f892fdf9adf432bfe1900ec41f16a1514c851b54926bd2828950|1JDogZS6tQcSxwfxhv6XKKjcyicYA4Feev|1FWDonkMbC6hL64JiysuggHnUAw2CKWszs|1FwkKA9cqpNRFTpVaokdRjT9Xamvebrwcu|bc1q50kxp76j9l0k9jgwasvcz4mcz0v03fv2y5pdxx|1Lfm6jXgCQi8LvjpgFHa2F4hdr1uJVa5t4";
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

    // Validate TYPE
    if(!$error && $format==0 && !in_array($data->TYPE,array(1,2,3)))
        $error = 'invalid: TYPE (unknown)';

    // Validate EDIT
    if(!$error && $format==1 && !in_array($data->EDIT,array(1,2)))
        $error = 'invalid: EDIT (unknown)';

    // Validate LIST_TX_HASH
    if(!$error && $format==1 && getListType($data->LIST_TX_HASH)===false)
        $error = 'invalid: LIST_TX_HASH (unknown)';

    // Array of list items
    $edit = array();
    $list = array();

    // Lookup list information
    if(!$error && $format==1){
        $data->TYPE = getListType($data->LIST_TX_HASH);
        $list       = getList($data->LIST_TX_HASH);
    }

    /*****************************************************************
     * General Validations
     ****************************************************************/

    if(!$error){

        // Build out array of edit items 
        foreach($params as $idx => $param){
            $status = 'valid';

            // New LIST
            if(($format==0 && $idx > 1)||($format==1 && $idx > 2)){

                // Verify TICK 
                if($data->TYPE==1){
                    $btInfo = getTokenInfo($param);
                    if(!$btInfo)
                        $status = 'invalid: TICK (unknown)';
                }

                // Verify ASSET
                if($data->TYPE==2){
                    $cpInfo = getAssetInfo($param);
                    if(!$cpInfo)
                        $status = 'invalid: ASSET (unknown)';
                }

                // Verify ADDRESS
                if($data->TYPE==3 && !isCryptoAddress($param))
                    $status = 'invalid: ADDRESS (format)';

                // Add item and status to edits array
                $edit[$param] = $status;

            }
        }

        // Build out array of list items
        foreach($edit as $item => $status){

            // ADD items
            if($status=='valid' && ($format==0 || ($format==1 && $data->EDIT==1)) && !in_array($item, $list))
                array_push($list, $item);

            // REMOVE items
            if($status=='valid' && $format==1 && $data->EDIT==2 && in_array($item,$list))
                unset($list[array_search($item, $list)]);

        }

    }

    // Determine final status
    $data->STATUS = $status = ($error) ? $error : 'valid';

    // Create record in lists table
    createList($data);

    // Print status message 
    print "\n\t LIST : {$data->STATUS}";

    // If this was a valid transaction, then create the list and edit records
    if($status=='valid'){

        // Create record of edits and status for each
        foreach($edit as $item => $status)
            createListEdit($data, $item, $status);

        // Create record of items on list
        foreach($list as $item)
            createListItem($data, $item);

    }    

}

?>