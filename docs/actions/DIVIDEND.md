# DIVIDEND command
This command pays a dividend to `token` holders of a `token`.

## PARAMS
| Name            | Type   | Description                              |
| --------------- | ------ | ---------------------------------------- |
| `VERSION`       | String | Broadcast Format Version                 |
| `TYPE`          | String | Tick type (1=TICK, 2=ASSET)              |
| `TICK`          | String | Any valid `TICK` or `ASSET`              |
| `DIVIDEND_TICK` | String | The `token` that dividends are paid in   |
| `AMOUNT`        | String | Amount of `tokens` to pay out per `UNIT` |

## Formats

### Version `0`
- `VERSION|TYPE|TICK|DIVIDEND_TICK|AMOUNT`

## Examples
```
bt:DIVIDEND|0|1|BRRR|BACON|1
This example pays a dividend of 1 BACON token to every holder of 1 BRRR token
```

```
bt:DIVIDEND|0|2|TEST|BACON|1
This example pays a dividend of 1 BACON token to every holder of 1 BACON ASSET
```

## Rules
- Dividends may only be paid out by the current `token` owner
- Dividends may be used on any `TICK` or `ASSET`

## Notes
- `UNIT` - A specific unit of measure (1 or 1.0)
- To send large amounts of `tokens` to users, see the `AIRDROP` or `SEND` commands