# DIVIDEND command
This command pays a dividend to `token` holders of a `token`.

## PARAMS
| Name            | Type   | Description                             |
| --------------- | ------ | --------------------------------------- |
| `VERSION`       | String | Broadcast Format Version                |
| `TICK`          | String | 1 to 250 characters in length           |
| `DIVIDEND_TICK` | String | The `token` that dividends are paid in  |
| `AMOUNT`        | String | Amount of `token` to pay out per `UNIT` |
| `MEMO`          | String | An optional memo to include             |

## Formats

### Version `0`
- `VERSION|TICK|DIVIDEND_TICK|AMOUNT|MEMO`

## Examples
```
bt:DIVIDEND|0|BRRR|BACON|1
This example pays a dividend of 1 BACON token to every holder of 1 BRRR token
```

```
bt:DIVIDEND|0|TEST|BACON|1
This example pays a dividend of 1 BACON token to every holder of 1 TEST token
```

## Rules
- Dividends may be used on any `TICK` 
- If `TICK` or `DIVIDEND_TICK` is non-divisible, `AMOUNT` must be integer

## Notes
- `UNIT` - A specific unit of measure (1 or 1.0)
- To send large amounts of `tokens` to users, see the `AIRDROP` or `SEND` commands
- If `TICK` is divisible and `DIVIDEND_TICK` is non-divisble, quantities under 1.0 will receive no `DIVIDEND_TICK`
