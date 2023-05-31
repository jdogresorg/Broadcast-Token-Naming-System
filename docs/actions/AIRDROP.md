# AIRDROP command
This command airdrops `token` supply to one or more `BTNS` lists.

## PARAMS
| Name     | Type   | Description                         |
| -------- | ------ | ----------------------------------- |
| `TICK`   | String | 1 to 250 characters in length       |
| `AMOUNT` | String | Amount of `tokens` to airdrops      |
| `LIST`   | String | `TX_HASH` of a BTNS `LIST` commands |

## Formats
- `AIRDROP|AMOUNT|LIST`
- `AIRDROP|AMOUNT|LIST|LIST`
- `AIRDROP|AMOUNT|LIST|AMOUNT|LIST`

## Examples

## Rules

## Notes
- The same `AMOUNT` is distributed to all airdrip recipients
- `AIRDROP` to a address `LIST` sends `AMOUNT` of `token` to each address on the list
- `AIRDROP` to a `token` `LIST` sends `AMOUNT` of `token` to holders of each `token` on the list

