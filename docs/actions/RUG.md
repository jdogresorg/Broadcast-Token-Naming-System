# RUG command
This command performs a rug pull on a `token` using various methods

## PARAMS
| Name                 | Type   | Description                                                           |
| -------------------- | ------ | ----------------------------------------------------------------      |
| `VERSION`            | String | Broadcast Format Version                                              |
| `TICK`               | String | 1 to 250 characters in length                                         |
| `MINT_MAX_SUPPLY`    | String | Mint `token` supply to `MAX_SUPPLY`                                   |
| `SLEEP_FOREVER`      | String | `SLEEP` token actions permanently (`SLEEP` with `value` set to `-1`)  |
| `CLOSE_DISPENSERS`   | String | Close any open dispensers immediately                                 |
| `RECALL_SUPPLY`      | String | Recall all `token` balances to `SOURCE` address                       |
| `BURN_OWNERSHIP`     | String | Burn `token` ownership permanently (send to `BURN_ADDRESSS`)          |
| `BURN_SUPPLY`        | String | Burn any `token` supply in `SOURCE` address (send to `BURN_ADDRESSS`) |
| `DESTROY_SUPPLY`     | String | Destroy any `token` supply in `SOURCE` address                        |

## Formats

### Version `0`
- `VERSION|TICK`

## Examples
```
bt:RUG|0|BRRR
This example does a rugpull on the BRRR `token`
```

## Rules


## Notes
- This is a _HIGHLY DANGEROUS_ function, built for entertainment purposes only!
- `ISSUE` `TICK` with `LOCK_RUG` set to `1` to permanently prevent use of the `RUG` command on a `token`