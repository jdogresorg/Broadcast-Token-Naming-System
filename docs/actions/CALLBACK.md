# CALLBACK command
This command performs a callback on a `token`. 

## PARAMS
| Name     | Type   | Description                   |
| -------- | ------ | ----------------------------- |
| `TICK`   | String | 1 to 250 characters in length |

## Formats
- `CALLBACK|TICK`

## Examples
```
bt:CALLBACK|JDOG
This example calls back the JDOG token to the token owner address
```

## Rules
- `token` can only be called back after `CALLBACK_BLOCK`
- All `token` supply will be returned to `token` owner address
- All `token` supply holders will receive `CALLBACK_AMOUNT` of `CALLBACK_TICK` `token` per unit

## Notes

