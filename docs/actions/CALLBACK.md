# CALLBACK command
This command performs a callback on a `token`. 

## PARAMS
| Name      | Type   | Description                   |
| --------- | ------ | ----------------------------- |
| `VERSION` | String | Broadcast Format Version      |
| `TICK`    | String | 1 to 250 characters in length |

## Formats

### Version `0`
- `CALLBACK|VERSION|TICK`

## Examples
```
bt:CALLBACK|0|JDOG
This example calls back the JDOG token to the token owner address
```

## Rules
- `token` can only be called back after `CALLBACK_BLOCK`
- All `token` supply will be returned to `token` owner address
- All `token` supply holders will receive `CALLBACK_AMOUNT` of `CALLBACK_TICK` `token` per unit

## Notes

