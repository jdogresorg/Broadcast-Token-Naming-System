# AIRDROP command
This command airdrops `token` supply to one or more `BTNS` lists.

## PARAMS
| Name      | Type   | Description                   |
| --------- | ------ | ------------------------------|
| `VERSION` | String | Broadcast Format Version      |
| `TICK`    | String | 1 to 250 characters in length |
| `AMOUNT`  | String | Amount of `tokens` to airdrop |
| `LIST`    | String | `TX_HASH` of a BTNS `LIST`    |
| `MEMO`    | String | An optional memo to include   |

## Formats

### Version `0` - Single Airdrop
- `VERSION|TICK|AMOUNT|LIST|MEMO`

### Version `1` - Multi-Airdrop (brief)
- `VERSION|LIST|TICK|AMOUNT|TICK|AMOUNT|MEMO`

### Version `2` - Multi-Airdrop (Full)
- `VERSION|TICK|AMOUNT|LIST|TICK|AMOUNT|LIST|MEMO`

### Version `3` - Multi-Airdrop (Full) with Multiple Memos
- `VERSION|TICK|AMOUNT|LIST|MEMO|TICK|AMOUNT|LIST|MEMO`


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
- `DROP` `ACTION` can be used for shorter reference to `AIRDROP` `ACTION`
- `AIRDROP` to `address` `LIST` sends `AMOUNT` of `token` to each address on the list
- `AIRDROP` to `token` `LIST` sends `AMOUNT` of `token` to holders of each `token` on the list
- `AIRDROP` to `ASSET` `LIST` sends `AMOUNT` of `token` to holders of each `ASSET` on the list
- Format version `0` allows for a single airdrop
- Format version `1` allows for repeating `TICK` and `AMOUNT` params to enable multiple airdrops to a single list
- Format version `2` allows for repeating `TICK`, `AMOUNT` and `LIST` params to enable multiple airdrops to multiple lists
- Format version `3` allows for repeating `TICK`, `AMOUNT`, `LIST`, and `MEMO` params to enable multiple airdrops to multiple lists with multiple memos
- Format version `0`, `1`, and `2` allow for a single optional `MEMO` field to be included as the last PARAM

