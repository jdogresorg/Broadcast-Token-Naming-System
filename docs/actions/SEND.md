# SEND command
This command sends/transfers one or more `token`s between addresses


## PARAMS
| Name          | Type   | Description                     |
| ------------- | ------ | ------------------------------- |
| `TICK`        | String | 1 to 250 characters in length   |
| `AMOUNT`      | String | Amount of `tokens` to send      |
| `DESTINATION` | String | Address to transfer `tokens` to |
| `MEMO`        | String | An optional memo to include     |

## Formats
- `SEND|TICK|AMOUNT|DESTINATION`
- `SEND|TICK|AMOUNT|DESTINATION|MEMO`
- `SEND|TICK|AMOUNT|DESTINATION|AMOUNT|DESTINATION|MEMO`
- `SEND|TICK|AMOUNT|DESTINATION|TICK|AMOUNT|DESTINATION|MEMO`

These formats allows for repeating `AMOUNT` and `DESTINATION` to enable multiple transfers in a single transaction

## Examples
```
bt:SEND|JDOG|1|1JDogZS6tQcSxwfxhv6XKKjcyicYA4Feev
This example sends 1 JDOG token to 1JDogZS6tQcSxwfxhv6XKKjcyicYA4Feev
```

```
bt:SEND|BRRR|5|1JDogZS6tQcSxwfxhv6XKKjcyicYA4Feev
This example sends 5 BRRR tokens to 1JDogZS6tQcSxwfxhv6XKKjcyicYA4Feev
```

```
bt:SEND|BRRR|5|1JDogZS6tQcSxwfxhv6XKKjcyicYA4Feev|1|1BoogrfDADPLQpq8LMASmWQUVYDp4t2hF9
This example sends 5 BRRR tokens to 1JDogZS6tQcSxwfxhv6XKKjcyicYA4Feev and 1 BRRR token to 1BoogrfDADPLQpq8LMASmWQUVYDp4t2hF9
```

```
bt:SEND|BRRR|5|1JDogZS6tQcSxwfxhv6XKKjcyicYA4Feev|TEST|1|1BoogrfDADPLQpq8LMASmWQUVYDp4t2hF9
This example sends 5 BRRR tokens to 1JDogZS6tQcSxwfxhv6XKKjcyicYA4Feev and 1 TEST token to 1BoogrfDADPLQpq8LMASmWQUVYDp4t2hF9
```

## Rules
- A `token` transfer shall only be considered valid if the broacasting address has balances of the `token` to cover the transfer `AMOUNT`
- A `token` transfer that does _not_ have `AMOUNT` in the broadcasting address shall be considered invalid and ignored.
- A valid `token` transfer will deduct the `token` `AMOUNT` from the broadcasting addresses balances
- A valid `token` tranfer will credit the `token` `AMOUNT` to the `DESTINATION` address or addresses


## Notes
- `MEMO` field is optional, and if included, is always the last PARAM on a `SEND` command
- `TRANSFER` `ACTION` can be used for backwards-compatability with BRC20/SRC20 `TRANSFER`