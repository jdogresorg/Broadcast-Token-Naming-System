# BATCH command
This command batch executes multiple `BTNS` `ACTION` commands in a single transaction

## PARAMS
| Name      | Type   | Description                            |
| --------- | ------ | -------------------------------------- |
| `VERSION` | String | Broadcast Format Version               |
| `COMMAND` | String | Any valid BTNS `ACTION` with `PARAMS`  |

## Formats

### Version `0`
- `VERSION|COMMAND;COMMAND`

## Examples
```
bt:BATCH|0|MINT|0|GAS|1000;ISSUE|0|JDOG
This example mints 1000 GAS tokens and reserves the JDOG token
```

## Rules
- Can only use one `MINT` command in a `BATCH` command
- Can only use one `ISSUE` command in a `BATCH` command

## Notes
