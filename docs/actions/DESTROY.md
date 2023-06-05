# DESTROY command
This command permanently destroys `token` supply

## PARAMS
| Name      | Type   | Description                   |
| --------- | ------ | ----------------------------- |
| `VERSION` | String | Broadcast Format Version      |
| `TICK`    | String | 1 to 250 characters in length |
| `AMOUNT`  | String | Amount of `tokens` to destroy |

## Formats

### Version `0`
- `VERSION|TICK|AMOUNT|TICK|AMOUNT`

## Examples
```
bt:DESTROY|0|BRRR|1
This example destroys 1 BRRR token from the broadcasting address
```

```
bt:DESTROY|0|BRRR|1|GAS|10
This example destroys 1 BRRR token and 10 GAS tokens from the broadcasting address
```

## Rules
- Any destroyed `token` supply should be debited from broadcasting address balances

## Notes