# RUG command
This command performs a rug pull on a `token`

## PARAMS
| Name    | Type   | Description              			    |
| ------- | ------ | -------------------------------------- |
| `TICK`  | String | 1 to 250 characters in length          |

## Formats
- `RUG|TICK`

## Examples
```
bt:RUG|BRRR
This example does a rugpull on the BRRR `token`
```

## Rules
- Mints `token` supply up to `MAX_SUPPLY`
- Locks `MAX_SUPPLY` via `LOCK_SUPPLY`
- Locks `MAX_MINT` via `LOCK_MINT`
- Cancels all future BTNS `ACTION` commands for `token` 
- Transfers `token` ownership to burn address
- Destroys all `TICK` `token` supply (including `tokens` held in addresses)

## Notes