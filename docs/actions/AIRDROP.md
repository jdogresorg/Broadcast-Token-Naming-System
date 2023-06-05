# AIRDROP command
This command airdrops `token` supply to one or more `BTNS` lists.

## PARAMS
| Name      | Type   | Description                         |
| --------- | ------ | ----------------------------------- |
| `VERSION` | String | Broadcast Format Version            |
| `TICK`    | String | 1 to 250 characters in length       |
| `AMOUNT`  | String | Amount of `tokens` to airdrops      |
| `LIST`    | String | `TX_HASH` of a BTNS `LIST` commands |

## Formats

### Version `0`
- `VERSION|TICK|AMOUNT|LIST|LIST`

### Version `1`
- `VERSION|TICK|AMOUNT|LIST|TICK|AMOUNT|LIST`

## Examples
```
bt:AIRDROP|0|GAS|1|LIST
This example airdops 1 GAS to every holder on a list
```

```
bt:AIRDROP|1|GAS|1|LIST|BRRR|2|LIST
This example airdops 1 GAS to every holder on a list and 2 BRRR to every holder on a list
```

## Rules

## Notes
-  Use format `0` to send the same `AMOUNT`  of `token` to one or more `LIST`
-  Use format `1` to send multiple `AMOUNT` of multiple `token` to different `LIST`
- `AIRDROP` to `address` `LIST` sends `AMOUNT` of `token` to each address on the list
- `AIRDROP` to `token` `LIST` sends `AMOUNT` of `token` to holders of each `token` on the list
- `AIRDROP` to `ASSET` `LIST` sends `AMOUNT` of `token` to holders of each `ASSET` on the list
