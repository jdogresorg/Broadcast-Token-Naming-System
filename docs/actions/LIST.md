# LIST command
This command creates a list of items for use in `BTNS` commands

## PARAMS
| Name           | Type   | Description                            |
| -------------- | ------ | ---------------------------------------|
| `VERSION`      | String | Broadcast Format Version               |
| `TYPE`         | String | List type (1=TICK, 2=ASSET, 3=ADDRESS) |
| `ITEM`         | String | Any valid `TICK`, `ASSET`, or address  |
| `ACT`          | String | Act to perform (1=ADD, 2=REMOVE)       |
| `LIST_TX_HASH` | String | `TX_HASH` of existing BTNS `LIST`      |


## Formats

### Version `0` 
- `VERSION|TYPE|ITEM`

### Version `1` 
- `VERSION|ACT|LIST_TX_HASH|ITEM`


## Examples
```
bt:LIST|0|1|JDOG|BRRR|TEST
This example creates a list of 3 BTNS token tickers
```

```
bt:LIST|0|2|XCP|RAREPEPE|JPMCHASE|A4211151421115130001
This example creates a list of 3 counterparty assets
```

```
bt:LIST|0|3|1JDogZS6tQcSxwfxhv6XKKjcyicYA4Feev|1FWDonkMbC6hL64JiysuggHnUAw2CKWszs|1BTNSGASK5En7rFurDJ79LQ8CVYo2ecLC8
This example creates a list of 3 addresses
```

```
bt:LIST|1|1|860dc04b2b59657005a0955f282043c04bc9d5520562d317119722956043ffee|1JDogZS6tQcSxwfxhv6XKKjcyicYA4Feev|1FWDonkMbC6hL64JiysuggHnUAw2CKWszs
This example creates a new list from an existing list and adds 2 addresses to the new list
```

```
bt:LIST|1|2|860dc04b2b59657005a0955f282043c04bc9d5520562d317119722956043ffee|1JDogZS6tQcSxwfxhv6XKKjcyicYA4Feev|1FWDonkMbC6hL64JiysuggHnUAw2CKWszs
This example creates a new list from an existing list and removes 2 addresses to the new list
```

## Rules
- In order for a `LIST` to be considered `valid`, all `TICK`, `ASSET`, or `ADDRESS`  must be valid
- A `TICK` list contain only BTNS `TICK` items
- A `ASSET` list contains only Counterparty `ASSET` items
- A `ADDRESS` list contains only `ADDRESS` items

## Notes
- Format version `0` allows for creating a list of `TYPE`
- Format version `1` allows for editing of a list via `LIST_TX_HASH` and `ACT`
- `ITEM` can be repeated many times in a `LIST` request
- `ITEM` values should be unique
