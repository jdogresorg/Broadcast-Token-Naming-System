        Title: BTNS Token Standard (BTNS-420)
        Author: Jeremy Johnson (J-Dog)
        Status: Draft
        Type: Informational
        Created: 2023-05-25

# Abstract
Extend BTNS to establish a token standard.

# Motivation
Establish a standardized ruleset for additional BTNS `token` experimentation.

# Rationale
BTNS-420 builds on the token framework in the [Broadcast Token Naming System](./BTNS.md) and establishes a standard set of features and rules by which to operate. 

This spec defines the core `ACTION` commands that can be used to perform various functions within the BTNS.

BTNS-420 can be extended in the future to allow for additional `ACTION` and `PARAM` options. 

This spec is a work in progress, and additional rules and notes will be added as spec is more clearly defined. 

BTNS-420 `ACTION` `PARAMS` will not be considered finalized until `ACTIVATION_BLOCK` for the `ACTION` is reached.

# Definitions
- `ACTIVATION_BLOCK` - A specific block height when a BTNS `ACTION` becomes usable
- `ACTION` - A specific type of command performed on a `token`
- `ASSET` - A token created via a `issuance` transaction on the Counterparty platform
- `JSON` - A text-based way of representing JavaScript object literals, arrays, and scalar data
- `PARAMS` - Parameters specified along with an `ACTION` command
- `XCP` - A specific `ASSET` on the `counterparty` platform
- `GAS` - A specific `token` on the `BTNS` platform
- `broadcast` - A general purpose transaction type which allows broadcasting of a message to the Counterparty platform
- `counterparty` - A token platform on Bitcoin (BTC) which was created in 2014 ([counterparty.io](https://counterparty.io))
- `issuance` - A transaction type which allows for creation of `ASSET` and issuing of supply on the Counterparty platform
- `token` - A token created in the BTNS via a `MINT` or `ISSUE` `ACTION` `broadcast` transaction


# Specification

## Project Prefix
The default BTNS prefix which should be used for BTNS transactions is `BTNS` and `BT`. All BTNS actions will begin with `btns:` or `bt:` (case insensitive)

## Project Versioning
The default BTNS version is `0` when no `broadcast` `value` is specified

## `ACTION` and `PARAMS` commands
By establishing pre-defined `broadcast` commands with `ACTION` and `PARAMS` for each, one is able to create `tokens` and perform various actions them.

**Broadcast Format:**
`bt:ACTION|PARAMS`


## BTNS `ACTION` commands
Below is a list of the defined BTNS `ACTION` commands and the function of each:

- [`AIRDROP`](#airdrop-command) - Transfer/Distribute `token` supply to a `LIST`
- [`BATCH`](#batch-command) - Execute multiple BTNS `ACTION` commands in a single transaction
- [`BET`](#bet-command) - Bet `token` on `broadcast` oracle feed outcomes
- [`CALLBACK`](#callback-command) - Return all `token` supply to owner address after a set block, in exchange for a different `token`
- [`DESTROY`](#destroy-command) - Destroy `token` supply forever
- [`DISPENSER`](#dispenser-command) - Create a dispenser (vending machine) to dispense a `token` when triggered
- [`DIVIDEND`](#dividend-command) - Issue a dividend on a `token`
- [`ISSUE`](#issue-command) - Create or issue a `token` and define how the token works
- [`LIST`](#list-command) - Create a list for use with various BTNS `ACTION` commands
- [`MINT`](#mint-command) - Create `token` supply
- [`RUG`](#rug-command) - Perform a rug pull on a `token` 
- [`SLEEP`](#sleep-command) - Pause all actions on a `token` for a certain number of blocks
- [`SEND`](#send-command) - Transfer or move some `token` balances between addresses
- [`SWEEP`](#sweep-command) - Transfer all `token` and/or ownerships to a destination address


## AIRDROP command
This command airdrops `token` supply to one or more BTNS lists.

`PARAMS` options:
- `TICK` - 1 to 5 characters in length (required)
- `AMOUNT` - Amount of tokens to airdrop (required)
- `LIST` - `TX_HASH` of a BTNS `LIST` command (required)

**Broadcast Format:**
`bt:AIRDROP|AMOUNT|LIST`

**Broadcast Format2:**
`bt:AIRDROP|AMOUNT|LIST|LIST`

**Broadcast Format3:**
`bt:AIRDROP|AMOUNT|LIST|AMOUNT|LIST`

### Rules
### Notes
- The same `AMOUNT` is distributed to all airdrip recipients
- `AIRDROP` to a address `LIST` sends `AMOUNT` of `token` to each address on the list
- `AIRDROP` to a `token` `LIST` sends `AMOUNT` of `token` to holders of each `token` on the list


## BATCH command
This command batch executes multiple BTNS `ACTION` commands in a single transaction

`PARAMS` options:
- `TICK` - 1 to 5 characters in length (required)
- `COMMAND` - Any valid BTNS `ACTION` with `PARAMS`

**Broadcast Format:**
`bt:BATCH|COMMAND;COMMAND`

**Example 1:**
`bt:BATCH|ISSUE|JDOG;ISSUE|TEST`

### Rules
### Notes


## BET command
This command bets a `token` on a `broadcast` oracle feed

`PARAM` options:
- `FEED_ADDRESS` - The address that hosts the feed to be bet on.
- `BET_TYPE` - 0 for Bullish CFD, 1 for Bearish CFD, 2 for Equal, 3 for NotEqual.
- `DEADLINE` - The time at which the bet should be decided/settled, in Unix time (seconds since epoch).
- `WAGER_TICK` - 1 to 5 characters in length (required)
- `WAGER_AMOUNT` - The quantityof `token` to wager (integer, in satoshis).
- `COUNTERWAGER_TICK` - 1 to 5 characters in length (required)
- `COUNTERWAGER_AMOUNT` - The minimum quantity of `token` to be wagered against, for the bets to match.
- `EXPIRATION` - The number of blocks after which the bet expires if it remains unmatched.
- `LEVERAGE` - Leverage, as a fraction of 5040 (integer, default=5040)
- `TARGET_VALUE` - Target value for Equal/NotEqual bet (float, default=null)

**Broadcast Format:**
`bt:BET|FEED_ADDRESS|BET_TYPE|DEADLINE|WAGER_TICK|WAGER_AMOUNT|COUNTERWAGER_TICK|COUNTERWAGER_AMOUNT|EXPIRATION|LEVERAGE|TARGET_VALUE`

**Example 1:**
`bt:BET|1BetXQ5w9mMmJosZ21jUtrebdpgMhYQUaZ|3|1497625200|TEST|100|BACON|100|604|5040|7`
The above example places a bet on the feed at 1BetXQ5w9mMmJosZ21jUtrebdpgMhYQUaZ and wagers 100 TEST for 100 BACON that the final value will be 7.

### Rules
  - Bet `token` funds are escrowed until the bet is settled or expires
  - Oracle fee is collected at time when bet is settled or expires
### Notes
  - The betting system uses the Counterparty oracle system
  - Oracles require BTC to operate, and as such, collect a percentage (%) fee, which is determined by the `broadcast` `fee` value (0.01 = 1%)


## CALLBACK command
This command performs a callback on a `token`. 

`PARAM` options:
- `TICK` - 1 to 5 characters in length (required)

**Broadcast Format:**
`bt:CALLBACK|TICK`

**Example 1:**
`bt:CALLBACK|JDOG`
The above example calls back the JDOG `token` to the `token` owner address

### Rules
- `token` can only be called back after `CALLBACK_BLOCK`
- All `token` supply will be returned to `token` owner address
- All `token` supply holders will receive `CALLBACK_AMOUNT` of `CALLBACK_TICK` `token` per unit


## DESTROY command
This command permanently destroys `token` supply

`PARAM` options:
- `TICK` - 1 to 5 characters in length (required)
- `AMOUNT` - Amount of tokens to destroy (required)

**Broadcast Format:**
`bt:DESTROY|TICK|AMOUNT`

**Broadcast Format2:**
`bt:DESTROY|TICK|AMOUNT|TICK|AMOUNT`

**Example 1:**
`bt:DESTROY|BRRR|1`
The above example destroys 1 BRRR `token` from the `broadcast` address

### Rules
- Any destroyed `token` supply should be debited from broadcasting address balances


## DISPENSER command
This command creates a vending machine to dispense `tokens` when triggered

`PARAM` options:
- `GIVE_TICK` - 1 to 5 characters in length (required)
- `GIVE_AMOUNT` - Quantity of `GIVE_TICK` to dispense when triggered
- `ESCROW_AMOUNT` - Quantity of `GIVE_TICK` to escrow in dispenser
- `TRIGGER_TICK` - 1 to 5 characters in length (default=BTC)
- `TRIGGER_AMOUNT` - Quantity of `TRIGGER_TICK` required per dispense
- `STATUS` - The state of the dispenser. (0=Open, 10=Closed)
- `ADDRESS` - Address that you would like to open the dispenser on. (default=broadcasting address)
- `ORACLE_ADDRESS` - address that you would like to use as a price oracle for this dispenser.

**Broadcast Format:**
`bt:DISPENSER|GIVE_TICK|GIVE_AMOUNT|ESCROW_AMOUNT|TRIGGER_TICK|TRIGGER_AMOUNT|STATUS|ADDRESS|ORACLE_ADDRESS`

**Example 1:**
`bt:DISPENSER|JDOG|1|1|BTC|1.00000000|0|1JDogZS6tQcSxwfxhv6XKKjcyicYA4Feev`
The above example creates a dispenser and escrows 1 JDOG `token` in it, which will dispense when 1.00000000 BTC is sent to 1JDogZS6tQcSxwfxhv6XKKjcyicYA4Feev

**Example 2:**
`bt:DISPENSER|JDOG|1|1|BTC|1.00000000|10`
The above example closes the dispenser in example 1 and credits any escrowed JDOG to the dispenser address 1JDogZS6tQcSxwfxhv6XKKjcyicYA4Feev

**Example 3:**
`bt:DISPENSER|BRRR|1000|1|TEST|1|0|1JDogZS6tQcSxwfxhv6XKKjcyicYA4Feev`
The above example creates a dispenser and escrows 1000 BRRR `token` in it, which will dispense when 1 TEST `token` is sent to 1JDogZS6tQcSxwfxhv6XKKjcyicYA4Feev

**Example 4:**
`bt:DISPENSER|BRRR|1000|1|TEST|1|10|1BrrrrLLzVq8ZP1nE3BHKQZ14dBXkRVsx4`
The above example closes the dispenser in example 3 and credits any escrowed BRRR to the address 1BrrrrLLzVq8ZP1nE3BHKQZ14dBXkRVsx4

### Rules
- Dispensers can only be closed by the dispenser address

### Notes
- Can create a dispenser on any valid address (no new/empty address limitation like CP)
- `STATUS` changes to `10` when a dispener is closed
- `STATUS` changes to `10` automatically when a dispenser runs out of `tokens` to dispense
- `ORACLE_ADDRESS` option only works when `TRIGGER_TICK` is `BTC`
- When specifying an `ORACLE_ADDRESS`, `TRIGGER_AMOUNT` format becomes X.XX (fiat)
- The `ORACLE_ADDRESS` option uses the Counterparty oracle system
- Oracles require BTC to operate, and as such, collect a percentage (%) fee, which is determined by the `broadcast` `fee` value (0.01 = 1%)
- BTNS Dispensers that use `ORACLE_ADDRESS` pay the oracle the entire percentage (%) fee, at time of dispenser creation
- Dispenser payment can only trigger up to `25` sub-dispenses (dispenses that trigger additional dispenses)


## DIVIDEND command
This command pays a dividend to `token` holders of a `token`.

`PARAM` options:
- `TICK` - The `token` that dividends are being rewarded on
- `DIVIDEND_TICK` - The `token` that dividends are paid in
- `AMOUNT` - The quantity of `DIVIDEND_TICK` rewarded per `UNIT`

**Broadcast Format:**
`bt:DIVIDEND|TICK|DIVIDEND_TICK|AMOUNT`

**Example 1:**
`bt:DIVIDEND|BRRR|BACON|1`
The above example pays a dividend of 1 BACON to every holder of 1 BRRR


### Rules
- Dividends may only be paid out by the current `token` owner

### Notes
- `UNIT` - A specific unit of measure (1 or 1.0)
- To send large amounts of `tokens` to users, see the `AIRDROP` or `SEND` commands


## ISSUE command
This command creates or issues a `token`

`PARAM` options:
- `TICK` - 1 to 250 characters in length (see rules below ) (required)
- `MAX_SUPPLY` - Maximum token supply (max: 18,446,744,073,709,551,615 - commas not allowed)
- `MAX_MINT` - Maximum amount of supply a `MINT` transaction can issue
- `DECIMALS` - Number of decimal places token should have (max: 18, default: 0)
- `DESCRIPTION` - Description of token (250 chars max) 
- `MINT_SUPPLY` - Amount of token supply to mint in immediately (default:0)
- `TRANSFER` - Address to transfer ownership of the `token` to (owner can perform future actions on token)
- `TRANSFER_SUPPLY` - Address to transfer `MINT_SUPPLY` to (mint initial supply and transfer to address)
- `LOCK_SUPPLY` - Lock `MAX_SUPPLY` permanently (cannot increase `MAX_SUPPLY`)
- `LOCK_MINT` - Lock `MAX_MINT` permanently (cannot edit `MAX_MINT`)
- `LOCK_DESCRIPTION` - Lock `token` against `DESCRIPTION` changes
- `LOCK_RUG` - Lock `token` against `RUG` command
- `LOCK_SLEEP` - Lock `token` against `SLEEP` command
- `LOCK_CALLBACK` - Lock `token` `CALLBACK` info
- `CALLBACK_BLOCK` - Enable `CALLBACK` command after `CALLBACK_BLOCK` 
- `CALLBACK_TICK` - `TICK` `token` users get when `CALLBACK` command is used
- `CALLBACK_AMOUNT` - `TICK` `token` amount that users get when `CALLBACK` command is used

**Broadcast Format:**
`bt:ISSUE|TICK|MAX_SUPPLY|MAX_MINT|DECIMALS|DESCRIPTION|MINT_SUPPLY|TRANSFER|TRANSFER_SUPPLY|LOCK_SUPPLY|LOCK_MINT|LOCK_DESCRIPTION|LOCK_RUG|LOCK_SLEEP|LOCK_CALLBACK|CALLBACK_BLOCK|CALLBACK_TICK|CALLBACK_AMOUNT`

**Example 1:**
`bt:ISSUE|JDOG`
The above example issues a JDOG token 

**Example 2:**
`bt:ISSUE|JDOG||||||||1`
The above example issues a JDOG token and `LOCK_SUPPLY` set to `1` to permanently

**Example 3:**
`bt:ISSUE|JDOG|0|0|0|http://j-dog.net/images/JDOG_icon.png`
The above example issues a JDOG token with a `DESCRIPTION` which points to an icon

**Example 4:**
`bt:ISSUE|JDOG|0|0|0|http://j-dog.net/images/JDOG_icon.png|0|1JDogZS6tQcSxwfxhv6XKKjcyicYA4Feev` 
The above example issues a JDOG token with a `DESCRIPTION` which points to an icon, and transfers token ownership to 1JDogZS6tQcSxwfxhv6XKKjcyicYA4Feev

**Example 5:**
`bt:ISSUE|JDOG|1000|1|0`
The above example issues a JDOG token with a max supply of 1000, and a maximum mint of 1 JDOG per `MINT`

**Example 6:**
`bt:ISSUE|JDOG|1000|1|0|BTNS Tokens Are Cool!`
The above example issues a JDOG token with a max supply of 1000, and a `DESCRIPTION` of 'BTNS Tokens are Cool!'

**Example 7:**
`bt:ISSUE|BRRR|10000000000000000000|10000000000000|0|https://j-dog.net/json/JDOG.json|100`
The above example issues a BRRR token with a max supply of 1 Quandrillion supply and a maximum mint of 1 Trillion BRRR per `MINT`, associates a `JSON` file with the `token`, and immediately mints 100 BRRR to the broadcasting address.

**Example 8:**
`bt:ISSUE|TEST|100|1|0||1|1JDogZS6tQcSxwfxhv6XKKjcyicYA4Feev|1JDogZS6tQcSxwfxhv6XKKjcyicYA4Feev`
The above example issues a TEST token with a max supply of 100, and a maximum mint of 1 TEST per `MINT`. This also mints 1 TEST token, and transfers ownership AND initial token supply to 1JDogZS6tQcSxwfxhv6XKKjcyicYA4Feev

### Rules
- First `TICK` `ISSUE` will be considered as valid.
- `token` may be issued if `counterparty` `ASSET` of same name does not exist
- `token` may be issued if issuing address is the owner of the `counterparty` `ASSET` of the same name
- Additional `TICK` `ISSUE` transactions after first valid `TICK` `ISSUE`, will be considered invalid and ignored, unless broadcast from `token` owners address
- `DECIMALS` can not be changed after `token` supply is issued
- If `TICK` contains any unicode characters, then `TICK` should be `base64` encoded
- Allowed characters in `TICK`:
   - Any word character (alphanumeric characters and underscores)
   - Special characters: ~!@#$%^&*()_+\-={}[\]\\:<>.?/
   - Most printable emojis in U+1F300 to U+1F5FF
- Special characters pipe `|` and semicolon `;` are **NOT** to be used in `TICK` names 
- `TEXT` can contain a URL to a an icon to use for this token (48x48 standard size)
- `TEXT` can contain a URL to a JSON file with additional information

### Notes
- `ISSUE` `TICK` with `MAX_SUPPLY` set to `0` to reserve the `token` name (reserve name)
- `ISSUE` `TICK` with `MAX_SUPPLY` and `MINT_SUPPLY` set to any non `0` value, to mint supply until `MAX_SUPPLY` is reached (owner can mint beyond `MAX_MINT`)
- `ISSUE` `TICK` with `MAX_SUPPLY` and `MAX_MINT` set to any non `0` value, to enable user minting (fair minting)
- `ISSUE` `TICK` with `LOCK_SUPPLY` set to `1` to permanently lock `MAX_SUPPLY` (irreversible)
- `ISSUE` `TICK` with `LOCK_MINT` set to `1` to permanently lock `MAX_MINT` (irreversible)
- `ISSUE` `TICK` with `LOCK_RUG` set to `1` to permanently prevent use of the `RUG` command
- `ISSUE` `TICK` with `LOCK_SLEEP` set to `1` to permanently prevent use of the `SLEEP` command
- `ISSUE` `TICK` with `LOCK_CALLBACK` set to `1` to permanently lock `CALLBACK_BLOCK`, `CALLBACK_TICK`, and `CALLBACK_AMOUNT` (irreversible)
- `CALLBACK_BLOCK`, `CALLBACK_TICK`, and `CALLBACK_AMOUNT` can be edited via `ISSUE` action until `LOCK_CALLBACK` is set to `1`
- `DEPLOY` `ACTION` can be used for backwards-compatability with BRC20/SRC20 `DEPLOY`
- `DESCRIPTION` field can not contain any pipe `|` or semi-colon `;` characters, as these are reserved


## LIST command
This command creates a list of items for use in BTNS commands

`PARAM` options:
- `ITEM` - may be any valid `TICK`, `ASSET`, or address
- `TYPE` - List type (1=address, 2=TICK, 3=ASSET)

**Broadcast Format:**
`bt:LIST|TYPE|ITEM|ITEM|ITEM`

**Example 1:**
`bt:LIST|1|1JDogZS6tQcSxwfxhv6XKKjcyicYA4Feev|1FWDonkMbC6hL64JiysuggHnUAw2CKWszs|bc1q5jw436vef6ezsgggk93pwhh9swrdxzx2e3a7kj`
The above example creates a list of addresses

**Example 2:**
`bt:LIST|2|JDOG|BRRR|TEST`
The above example creates a list of `token` tickers

**Example 3:**
`bt:LIST|3|XCP|RAREPEPE|JPMCHASE|A4211151421115130001`
The above example creates a list of `counterparty` `ASSET`s

### Rules
- In order for a `LIST` to be considered `valid`, all tickers or addresses must be valid.
- A `TICK` list contain only BTNS `TICK` items
- A `ASSET` list contains only Counterparty `ASSET` items
- 

## MINT command
This command mints token supply

`PARAM` options:
- `TICK` - `token` name registered with `ISSUE` format (required)
- `AMOUNT` - Amount of tokens to mint (required)
- `DESTINATION` - Address to transfer tokens to

**Broadcast Format:**
`bt:MINT|TICK|AMOUNT|DESTINATION`

**Example 1:**
`bt:MINT|JDOG|1`
The above example mints 1 JDOG `token` to the broadcasting address

**Example 2:**
`bt:MINT|BRRR|10000000000000|1JDogZS6tQcSxwfxhv6XKKjcyicYA4Feev`
The above example mints 10,000,000,000,000 BRRR tokens and transfers them to 1JDogZS6tQcSxwfxhv6XKKjcyicYA4Feev 

### Rules
- `broadcast` `status` must be `valid`
- `token` supply may be minted until `MAX_SUPPLY` is reached.
- Transactions that attempt to mint supply beyond `MAX_SUPPLY` shall be considered invalid and ignored.


## RUG command
This command performs a rug pull on a `token`

`PARAM` options:
- `TICK` - `token` name registered with `ISSUE` format (required)

**Broadcast Format:**
`bt:RUG|TICK`

**Example 1:**
`bt:RUG|BRRR`
The above example does a rugpull on the BRRR `token`

### Rules
   - Mints `token` supply up to `MAX_SUPPLY`
   - Locks `MAX_SUPPLY` via `LOCK_SUPPLY`
   - Locks `MAX_MINT` via `LOCK_MINT`
   - Cancels all future BTNS `ACTION` commands for `token` 
   - Transfers `token` ownership to burn address
   - Destroys all `TICK` `token` supply (including `tokens` held in addresses)

### Notes
   - Can use `LOCK_RUG` in `ISSUE` command to prevent `RUG` command
   - Why? Why not! We are experimenting and having fun (Don't Trust, Verify!)


## SEND command
This command sends/transfers one or more `token`s between addresses

`PARAM` options:
- `TICK` - `token` name registered with `ISSUE` format (required)
- `AMOUNT` - Amount of tokens to send (required)
- `DESTINATION` - Address to transfer tokens to (required)
- `MEMO` - An optional Memo to include
This format also allows for repeating `AMOUNT` and `DESTINATION` to enable multiple transfers in a single transaction

**Broadcast Format:**
`bt:SEND|TICK|AMOUNT|DESTINATION`

**Broadcast Format2:**
`bt:SEND|TICK|AMOUNT|DESTINATION|MEMO`

**Broadcast Format3:**
`bt:SEND|TICK|AMOUNT|DESTINATION|AMOUNT|DESTINATION|MEMO`

**Broadcast Format4:**
`bt:SEND|TICK|AMOUNT|DESTINATION|TICK|AMOUNT|DESTINATION|MEMO`

**Example 1:**
`bt:SEND|JDOG|1|1JDogZS6tQcSxwfxhv6XKKjcyicYA4Feev`
The above example sends 1 JDOG token to 1JDogZS6tQcSxwfxhv6XKKjcyicYA4Feev

**Example 2:**
`bt:SEND|BRRR|5|1JDogZS6tQcSxwfxhv6XKKjcyicYA4Feev`
The above example sends 5 BRRR tokens to 1JDogZS6tQcSxwfxhv6XKKjcyicYA4Feev

**Example 3:**
`bt:SEND|BRRR|5|1JDogZS6tQcSxwfxhv6XKKjcyicYA4Feev|1|1BoogrfDADPLQpq8LMASmWQUVYDp4t2hF9`
The above example sends 5 BRRR tokens to 1JDogZS6tQcSxwfxhv6XKKjcyicYA4Feev and 1 BRRR token to 1BoogrfDADPLQpq8LMASmWQUVYDp4t2hF9

**Example 4:**
`bt:SEND|BRRR|5|1JDogZS6tQcSxwfxhv6XKKjcyicYA4Feev|TEST|1|1BoogrfDADPLQpq8LMASmWQUVYDp4t2hF9`
The above example sends 5 BRRR tokens to 1JDogZS6tQcSxwfxhv6XKKjcyicYA4Feev and 1 TEST token to 1BoogrfDADPLQpq8LMASmWQUVYDp4t2hF9

### Rules
- A `token` transfer shall only be considered valid if the broacasting address has balances of the `token` to cover the transfer `AMOUNT`
- A `token` transfer that does _not_ have `AMOUNT` in the broadcasting address shall be considered invalid and ignored.
- A valid `token` transfer will deduct the `token` `AMOUNT` from the broadcasting addresses balances
- A valid `token` tranfer will credit the `token` `AMOUNT` to the `DESTINATION` address or addresses
### Notes
- `MEMO` field is optional, and if included, is always the last PARAM on a `SEND` command
- `TRANSFER` `ACTION` can be used for backwards-compatability with BRC20/SRC20 `TRANSFER`


## SLEEP command
This command pauses all `token` `ACTIONS` until `RESUME_BLOCK` is reached

`PARAMS` options:
- `TICK` - 1 to 5 characters in length (required)
- `RESUME_BLOCK` - Block index to resume BTNS `ACTION` commands

**Broadcast Format:**
`bt:SLEEP|TICK|RESUME_BLOCK`

**Example 1:**
`bt:SLEEP|JDOG|791495`
The above example pauses/sleeps ALL BTNS `ACTION` commands on JDOG `token` until block 791495

### Rules
### Notes
 - USE WITH CAUTION! `SLEEP` will stop/pause all BTNS actions, including dispenses.
 - `SLEEP` can result in loss of user funds, as payments to BTNS dispensers will be ignored.
 - Can use `LOCK_SLEEP` in `ISSUE` command to prevent `SLEEP` command
 - Can issue a `SLEEP` before `RESUME_BLOCK` to extend a `SLEEP`


## SWEEP command
This command transfers all `token` balances and/or ownerships to a destination address

`PARAM` options:
- `DESTINATION` - address where `token` shall be swept
- `SWEEP_BALANCES` - Indicates if address `token` balances should be swept (default=1)
- `SWEEP_OWNERSHIP` - Indicates if address `token` balances should be swept (default=1)
- `MEMO` - Optional memo to include

**Broadcast Format:**
`bt:SWEEP|DESTINATION|SWEEP_BALANCES|SWEEP_OWNERSHIP|MEMO`

**Example 1:**
`bt:SWEEP|1JDogZS6tQcSxwfxhv6XKKjcyicYA4Feev|1|1`
The above example sweeps both `token` balances and ownership from the broadcasting address to 1JDogZS6tQcSxwfxhv6XKKjcyicYA4Feev

**Example 1:**
`bt:SWEEP|1BoogrfDADPLQpq8LMASmWQUVYDp4t2hF9|0|1`
The above example sweeps only `token` ownership from the broadcasting address to 1JDogZS6tQcSxwfxhv6XKKjcyicYA4Feev

### Rules
### Notes


## `ACTION` `ACTIVATION_BLOCK` list
BTNS `ACTION` commands are not to be considered `valid` until after `ACTIVATION_BLOCK` for each command has passed.

Below is a list of the BTNS `ACTION` commands and the `ACTIVATION_BLOCK` for each: 
- `AIRDROP` - TBD
- `BATCH` - TBD
- `BET` - TBD
- `CALLBACK` - TBD
- `DESTROY` - TBD
- `DISPENSER` -  TBD
- `DIVIDEND` - TBD
- `ISSUE`   - TBD
- `LIST` - TBD
- `MINT` - TBD
- `RUG` -  TBD
- `SEND` - TBD
- `SLEEP` - TBD
- `SWEEP` -  TBD


## BTNS Name Reservations
- `XCP` is reserved (avoids confusion with `counterparty` `XCP`)
- `GAS` is reserved for future use (anti-spam mechanism)
- Registered `counterparty` `ASSET` names are reserved within the BTNS for use by the `ASSET` owner


## Additional Notes
- `broadcast` `status` must be `valid` in order for BTNS `ACTION` to be considered `valid`
- BTNS tokens can also be used in combination with other protocols, by specifying the semicolon (`;`) as a protocol delimiter.
- Only one BTNS `ACTION` can be included in a `broadcast` (use `BATCH` to use multiple commands in a single transaction)
- BTNS tokens can be stamped using the STAMP Protocol
- By allowing combining of protocols, you can do many things in a single transaction, such as:
  - Issue BTNS `token` with a `DESCRIPTION` pointing to an external image file
  - Stamp JSON file with meta-data to BTNS token
  - Stamp image data inside a BTNS token
  - Reference an ordinals inscription
  - Reference an IPFS CID

**Example 1**
`bt:ISSUE|JDOG;stamp:base64data`
The above example issues a JDOG token, and STAMPs file data into the token.

## Current BTNS `ACTION` Functionality
- `AIRDROP`
- `BATCH`
- `BET`
- `CALLBACK`
   - Return all `token` supply to owner address after `CALLBACK_BLOCK`
   - Compensate `token` supply holders by giving them `CALLBACK_AMOUNT` of `CALLBACK_TICK` `token` per unit
- `ISSUE`
   - Register / Reserve `TICK` for `token` usage
   - Associate `ICON` with your `token`
   - Adjust `MAX_SUPPLY` until `LOCK_SUPPLY` is set to `1`
   - Adjust `MAX_MINT` until `LOCK_MINT` is set to `1`
   - Mint `token` supply immediately using `MINT_SUPPLY` (can bypass `MAX_MINT`)
   - Transfer `token` ownership via `TRANSFER`
   - Transfer `token` supply via `TRANSFER_SUPPLY`
   - Lock `MAX_SUPPLY` with `LOCK_SUPPLY` set to `1`
   - Lock `MAX_MINT` with `LOCK_MINT` set to `1`
   - Lock against `RUG` command by setting `LOCK_RUG` to `1`
   - Lock against `SLEEP` command by setting `LOCK_SLEEP` to `1`
   - Lock against `CALLBACK` command by setting `LOCK_CALLBACK` to `1`

- `DESTROY`
- `DISPENSER`
- `DIVIDEND`
- `LIST`
- `MINT`
   - Mint `tokens` at rate of `MAX_MINT` until `MAX_SUPPLY` is reached ("fair" mint)
   - Transfer minted `token` supply to `TRANSFER` address
- `RUG`
- `SEND`
  - Transfer `AMOUNT` of `token` from broadcast address to a `DESTINATION` address
  - Send multiple `AMOUNT` of `token` to multiple `DESTINATION` addresses
- `SLEEP`
- `SWEEP`

# Copyright
This document is placed in the public domain.