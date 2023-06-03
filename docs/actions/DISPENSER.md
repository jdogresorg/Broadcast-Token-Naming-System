# DISPENSER command
This command creates a vending machine to dispense `tokens` when triggered

## PARAMS
| Name             | Type   | Description                                                                          |
| ---------------- | ------ | ------------------------------------------------------------------------------------ |
| `VERSION`        | String | Broadcast Format Version                                                             |
| `GIVE_TICK`      | String | 1 to 250 characters in length                                                        |
| `GIVE_AMOUNT`    | String | Quantity of `GIVE_TICK` to dispense when triggered                                   |
| `ESCROW_AMOUNT`  | String | Quantity of `GIVE_TICK` to escrow in dispenser                                       |
| `TRIGGER_TICK`   | String | 1 to 250 characters in length  (default=BTC)                                         |
| `TRIGGER_AMOUNT` | String | Quantity of `TRIGGER_TICK` required per dispense                                     |
| `STATUS`         | String | The state of the dispenser. (0=Open, 10=Closed)                                      |
| `ADDRESS`        | String | Address that you would like to open the dispenser on. (default=broadcasting address) |
| `ORACLE_ADDRESS` | String | address that you would like to use as a price oracle for this dispenser.             |
| `ALLOW_LIST`     | String | `TX_HASH` of a BTNS `LIST` of addresses to allow trigger dispenser                   |
| `BLOCK_LIST`     | String | `TX_HASH` of a BTNS `LIST` of addresses to NOT allow to trigger a dispenser          |

## Formats

### Version `0`
- `DISPENSER|VERSION|GIVE_TICK|GIVE_AMOUNT|ESCROW_AMOUNT|TRIGGER_TICK|TRIGGER_AMOUNT|STATUS|ADDRESS|ORACLE_ADDRESS|ALLOW_LIST|BLOCK_LIST`

## Examples
```
bt:DISPENSER|0|JDOG|1|1|BTC|1.00000000|0|1JDogZS6tQcSxwfxhv6XKKjcyicYA4Feev
This example creates a dispenser and escrows 1 JDOG `token` in it, which will dispense when 1.00000000 BTC is sent to 1JDogZS6tQcSxwfxhv6XKKjcyicYA4Feev
```

```
bt:DISPENSER|0|JDOG|1|1|BTC|1.00000000|10
This example closes the dispenser in example 1 and credits any escrowed JDOG to the dispenser address 1JDogZS6tQcSxwfxhv6XKKjcyicYA4Feev
```

```
bt:DISPENSER|0|BRRR|1000|1|TEST|1|0|1JDogZS6tQcSxwfxhv6XKKjcyicYA4Feev
This example creates a dispenser and escrows 1000 BRRR `token` in it, which will dispense when 1 TEST `token` is sent to 1JDogZS6tQcSxwfxhv6XKKjcyicYA4Feev
```

```
bt:DISPENSER|0|BRRR|1000|1|TEST|1|0|1JDogZS6tQcSxwfxhv6XKKjcyicYA4Feev
This example closes the dispenser in example 3 and credits any escrowed BRRR to the address 1BrrrrLLzVq8ZP1nE3BHKQZ14dBXkRVsx4
```


## Rules
- Dispensers can only be closed by the dispenser address

## Notes
- Can create a dispenser on any valid address (no new/empty address limitation like CP)
- `STATUS` changes to `10` when a dispener is closed
- `STATUS` changes to `10` automatically when a dispenser runs out of `tokens` to dispense
- `ORACLE_ADDRESS` option only works when `TRIGGER_TICK` is `BTC`
- When specifying an `ORACLE_ADDRESS`, `TRIGGER_AMOUNT` format becomes X.XX (fiat)
- The `ORACLE_ADDRESS` option uses the Counterparty oracle system
- Oracles require BTC to operate, and as such, collect a percentage (%) fee, which is determined by the `broadcast` `fee` value (0.01 = 1%)
- BTNS Dispensers that use `ORACLE_ADDRESS` pay the oracle the entire percentage (%) fee, at time of dispenser creation
- Dispenser payment can only trigger up to `25` sub-dispenses (dispenses that trigger additional dispenses)