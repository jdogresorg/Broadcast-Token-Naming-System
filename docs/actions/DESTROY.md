# DESTROY command
This command permanently destroys `token` supply

## PARAMS
| Name      | Type   | Description                   |
| --------- | ------ | ----------------------------- |
| `VERSION` | String | Broadcast Format Version      |
| `TICK`    | String | 1 to 250 characters in length |
| `AMOUNT`  | String | Amount of `tokens` to destroy |
| `MEMO`    | String | An optional memo to include   |

## Formats

### Version `0`
- `VERSION|TICK|AMOUNT|MEMO`

### Version `1`
- `VERSION|TICK|AMOUNT|TICK|AMOUNT|MEMO`

### Version `2`
- `VERSION|TICK|AMOUNT|MEMO|TICK|AMOUNT|MEMO`


## Examples
```
bt:DESTROY|0|BRRR|1
This example destroys 1 BRRR token from the broadcasting address
```

```
bt:DESTROY|1|BRRR|1|GAS|10
This example destroys 1 BRRR token and 10 GAS tokens from the broadcasting address
```

```
bt:DESTROY|2|BRRR|1|foo|GAS|10|bar
This example destroys 1 BRRR token with the memo `foo`, and 10 GAS tokens with the memo `bar` from the broadcasting address
```

## Rules
- Any destroyed `token` supply should be debited from broadcasting address balances

## Notes
- Format version `0` allows for a single destroy
- Format version `1` allows for repeating `TICK` and `AMOUNT` params to enable multiple destroys
- Format version `2` allows for repeating `TICK`, `AMOUNT`, and `MEMO` params to enable multiple destroys
- Format version `0` and `1` allow for a single optional `MEMO` field to be included as the last PARAM
