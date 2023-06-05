# DIVIDEND command
This command pays a dividend to `token` holders of a `token`.

## PARAMS
| Name            | Type   | Description                                      |
| --------------- | ------ | ------------------------------------------------ |
| `VERSION`       | String | Broadcast Format Version                         |
| `TICK`          | String | The `token` that dividends are being rewarded on |
| `DIVIDEND_TICK` | String | The `token` that dividends are paid in           |
| `AMOUNT`        | String | Amount of `tokens` to destroy                    |

## Formats

### Version `0`
- `VERSION|TICK|DIVIDEND_TICK|AMOUNT`

## Examples
```
bt:DIVIDEND|0|BRRR|BACON|1
This example pays a dividend of 1 BACON to every holder of 1 BRRR
```

## Rules
- Dividends may only be paid out by the current `token` owner

## Notes
- `UNIT` - A specific unit of measure (1 or 1.0)
- To send large amounts of `tokens` to users, see the `AIRDROP` or `SEND` commands