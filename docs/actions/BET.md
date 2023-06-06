# BET command
This command bets a `token` on a `broadcast` oracle feed

## PARAMS
| Name                  | Type   | Description                                                                              |
| --------------------- | ------ | ---------------------------------------------------------------------------------------- |
| `VERSION`             | String | Broadcast Format Version                                                                 |
| `FEED_ADDRESS`        | String | The address that hosts the feed to be bet on.                                            |
| `BET_TYPE`            | String | 0 for Bullish CFD, 1 for Bearish CFD, 2 for Equal, 3 for NotEqual.                       |
| `DEADLINE`            | String | The time at which the bet should be decided/settled, in Unix time (seconds since epoch). |
| `WAGER_TICK`          | String | 1 to 250 characters in length                                                            |
| `WAGER_AMOUNT`        | String | The quantityof `token` to wager (integer, in satoshis).                                  |
| `COUNTERWAGER_TICK`   | String | 1 to 250 characters in length                                                            |
| `COUNTERWAGER_AMOUNT` | String | The minimum quantity of `token` to be wagered against, for the bets to match.            |
| `EXPIRATION`          | String | The number of blocks after which the bet expires if it remains unmatched.                |
| `LEVERAGE`            | String | Leverage, as a fraction of 5040 (integer, default=5040)                                  |
| `TARGET_VALUE`        | String | Target value for Equal/NotEqual bet (float, default=null)                                |


## Formats

### Version `0`
- `VERSION|FEED_ADDRESS|BET_TYPE|DEADLINE|WAGER_TICK|WAGER_AMOUNT|COUNTERWAGER_TICK|COUNTERWAGER_AMOUNT|EXPIRATION|LEVERAGE|TARGET_VALUE`

## Examples
```
bt:BET|0|1BetXQ5w9mMmJosZ21jUtrebdpgMhYQUaZ|3|1497625200|TEST|100|BACON|100|604|5040|7
This example places a bet on the feed at 1BetXQ5w9mMmJosZ21jUtrebdpgMhYQUaZ and wagers 100 TEST for 100 BACON that the final value will be 7.
```

## Rules
- Bet `token` funds are escrowed until the bet is settled or expires
- Oracle fee is collected at time when bet is settled or expires

## Notes
- The betting system uses the Counterparty oracle system
- Oracles require BTC to operate, and as such, collect a percentage (%) fee, which is determined by the `broadcast` `fee` value (0.01 = 1%)
