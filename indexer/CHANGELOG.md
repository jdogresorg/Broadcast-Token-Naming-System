CHANGELOG
---
0.13.0
- Added support for `--compare=DB`
- start using `—block=#` instead of `—reparse=#`
- Updated `createTxIndex()` to call on `createAction()` instead of `createTxType()`
- Updated `transactions` table to change `type_id` to `action_id`
- Updated `isValidLock()` so unset lock values are valid
- Updated `getTokenInfo()` so empty values are ignored
- Removed `index_tx_types` table (duplicated via `index_actions`)
- Removed `createTxType()` function
- Updated `getAddressCreditDebit()` to lookup balances using `block_index` or `tx_index` 
- Updated `getTokenInfo()` to removed duplicated `OWNER` array item
- Cleanup `MINT_START/STOP_BLOCK` logic
- Updated `--compare=DB` to ignore missing txs in compare ledger

0.12.0
- Added support for `--reparse`
- Optimized ledger hashing
- `ADDRESS` support
- Renamed `LOCK_MINT` param to `LOCK_MAX_MINT`
- Renamed `LOCK_SUPPLY` param to `LOCK_MAX_SUPPLY`
- Added `LOCK_MINT` param to lock against `MINT` command
- Added `LOCK_MINT_SUPPLY` param
- `BATCH` support
- `AIRDROP` support

0.11.1
- Added support for `MINT_START_BLOCK`
- Added support for `MINT_STOP_BLOCK`
- Added support for `MINT_ADDRESS_MAX`
- Updates to support `GAS` minting

0.11.0
- `DESTROY` support
- Added basic sanity checks
- Fixed sanity issues related to credit/debits/balances/supply mismatches
- Cleaned up code

0.10.1
- set `MIN_TOKEN_SUPPLY` to 0.000000000000000001
- Prevent `LOCK_SUPPLY` if no supply is issued 
- Prevent setting `MAX_SUPPLY` below current SUPPLY
- Updated `getTokenSupply()` to use CAST in sql queries
- Updated `getAssetInfo()` to only lookup assets using name 

0.10.0 - Initial Release
- `ISSUE` support
- `LIST` support
- `MINT` support
- `SEND` support
