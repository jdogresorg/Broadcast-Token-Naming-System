# SWEEP command
This command transfers all `token` balances and/or ownerships to a destination address

## PARAMS
| Name          | Type   | Description              			                              |
| ------------- | ------ | ------------------------------------------------------------------ |
| `VERSION`     | String | Broadcast Format Version                                           |
| `DESTINATION` | String | address where `token` shall be swept                               |
| `BALANCES` 	| String | Indicates if address `token` balances should be swept (default=1)  |
| `OWNERSHIPS`  | String | Indicates if address `token` ownership should be swept (default=1) |
| `MEMO` 		| String | Optional memo to include                                           |

## Formats

### Version `0`
- `VERSION|DESTINATION|BALANCES|OWNERSHIPS|MEMO`

## Examples
```
bt:SWEEP|0|1JDogZS6tQcSxwfxhv6XKKjcyicYA4Feev|1|1
This example sweeps both token balances and ownerships from the broadcasting address to 1JDogZS6tQcSxwfxhv6XKKjcyicYA4Feev
```

```
bt:SWEEP|0|1BoogrfDADPLQpq8LMASmWQUVYDp4t2hF9|0|1
This example sweeps only token ownerships from the broadcasting address to 1BoogrfDADPLQpq8LMASmWQUVYDp4t2hF9
```

## Rules

## Notes
