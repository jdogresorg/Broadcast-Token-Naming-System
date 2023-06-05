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
- `ASSET` - A token created via `issuance` transactions on the `counterparty` platform
- `VERSION` - A specific `ACTION` command `broadcast` format version
- `JSON` - A text-based way of representing JavaScript object literals, arrays, and scalar data
- `PARAMS` - Parameters specified along with an `ACTION` command
- `XCP` - A specific `ASSET` on the `counterparty` platform
- `GAS` - A specific `token` on the `BTNS` platform
- `broadcast` - A general purpose transaction type which allows broadcasting of a message to the `counterparty` platform
- `counterparty` - A token platform on Bitcoin (BTC) which was created in 2014 ([counterparty.io](https://counterparty.io))
- `issuance` - A transaction type which allows for creation of `ASSET` and issuing of supply on the Counterparty platform
- `token` - A token created in the BTNS via a `MINT` or `ISSUE` `ACTION` `broadcast` transaction


# Specification

## Project Prefix
The default BTNS prefix which should be used for BTNS transactions is `BTNS` and `BT`. All BTNS actions will begin with `btns:` or `bt:` (case insensitive)

## Project Versioning
The default BTNS version is `0` when no `broadcast` `value` is specified

## Format Versioning
Establishing a `VERSION` as the first field in every `ACTION` command, allows for easier expansion and parsing of `PARAMS` in various standardized formats.

The default BTNS format version is `0` when no `VERSION` is given

## `ACTION` commands and `PARAMS`
By defining `ACTION` commands and `PARAMS` for each command, we standardize a way in which the `BTNS` `token` functionality can be extended.

**Broadcast Format:**
`bt:ACTION|PARAMS`


## BTNS `ACTION` commands
Below is a list of the defined BTNS `ACTION` commands and the function of each:

| ACTION                                | Description                                                                                       | 
| ------------------------------------- | ------------------------------------------------------------------------------------------------- |
| [`AIRDROP`](./actions/AIRDROP.md)     | Transfer/Distribute `token` supply to a `LIST`                                                    |
| [`BATCH`](./actions/BATCH.md)         | Execute multiple BTNS `ACTION` commands in a single transaction                                   |
| [`BET`](./actions/BET.md)             | Bet `token` on `broadcast` oracle feed outcomes                                                   |
| [`CALLBACK`](./actions/CALLBACK.md)   | Return all `token` supply to owner address after a set block, in exchange for a different `token` |
| [`DESTROY`](./actions/DESTROY.md)     | Destroy `token` supply forever                                                                    |
| [`DISPENSER`](./actions/DISPENSER.md) | Create a dispenser (vending machine) to dispense a `token` when triggered                         |
| [`DIVIDEND`](./actions/DIVIDEND.md)   | Issue a dividend on a `token`                                                                     |
| [`ISSUE`](./actions/ISSUE.md)         | Create or issue a `token` and define how the token works                                          |
| [`LIST`](./actions/LIST.md)           | Create a list for use with various BTNS `ACTION` commands                                         |
| [`MINT`](./actions/MINT.md)           | Create or mint `token` supply                                                                     |
| [`RUG`](./actions/RUG.md)             | Perform a rug pull on a `token`                                                                   |
| [`SEND`](./actions/SEND.md)           | Transfer or move some `token` balances between addresses                                          |
| [`SLEEP`](./actions/SLEEP.md)         | Pause all actions on a `token` for a certain number of blocks                                     |
| [`SWEEP`](./actions/SWEEP.md)         | Transfer all `token` and/or ownerships to a destination address                                   |


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