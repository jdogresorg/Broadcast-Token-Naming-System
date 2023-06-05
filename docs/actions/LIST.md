# LIST command
This command creates a list of items for use in `BTNS` commands

## PARAMS
| Name      | Type   | Description                            |
| --------- | ------ | ---------------------------------------|
| `VERSION` | String | Broadcast Format Version               |
| `TYPE`    | String | List type (1=address, 2=TICK, 3=ASSET) |
| `ITEM`    | String | Any valid `TICK`, `ASSET`, or address  |


## Formats

### Version `0`
- `VERSION|TYPE|ITEM|ITEM|ITEM`

## Examples
```
bt:LIST|0|1|1JDogZS6tQcSxwfxhv6XKKjcyicYA4Feev|1FWDonkMbC6hL64JiysuggHnUAw2CKWszs|bc1q5jw436vef6ezsgggk93pwhh9swrdxzx2e3a7kj
This example creates a list of addresses
```

```
bt:LIST|0|2|JDOG|BRRR|TEST
This example creates a list of BTNS token tickers
```

```
bt:LIST|0|3|XCP|RAREPEPE|JPMCHASE|A4211151421115130001
This example creates a list of counterparty assets
```

## Rules
- In order for a `LIST` to be considered `valid`, all tickers or addresses must be valid.
- A `TICK` list contain only BTNS `TICK` items
- A `ASSET` list contains only Counterparty `ASSET` items

## Notes