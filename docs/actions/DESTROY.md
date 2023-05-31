# DESTROY command
This command permanently destroys `token` supply

## PARAMS
| Name     | Type   | Description                   |
| -------- | ------ | ----------------------------- |
| `TICK`   | String | 1 to 250 characters in length |
| `AMOUNT` | String | Amount of `tokens` to destroy |

## Formats
- `DESTROY|TICK|AMOUNT`
- `DESTROY|TICK|AMOUNT|TICK|AMOUNT`

## Examples
```
bt:DESTROY|BRRR|1
This example destroys 1 BRRR token from the broadcasting address
```

## Rules
- Any destroyed `token` supply should be debited from broadcasting address balances

## Notes