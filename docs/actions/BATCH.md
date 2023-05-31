# BATCH command
This command batch executes multiple `BTNS` `ACTION` commands in a single transaction

## PARAMS
| Name      | Type   | Description                            |
| --------- | ------ | -------------------------------------- |
| `TICK`    | String | 1 to 250 characters in length          |
| `COMMAND` | String | Any valid BTNS `ACTION` with `PARAMS`  |

## Formats
- `BATCH|COMMAND;COMMAND`

## Examples
```
bt:BATCH|ISSUE|JDOG;ISSUE|TEST
This example issues the JDOG and TEST tokens
```

## Rules

## Notes