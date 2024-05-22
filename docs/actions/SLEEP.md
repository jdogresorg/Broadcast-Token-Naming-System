# SLEEP command
This command pauses all `token` `ACTIONS` until `RESUME_BLOCK` is reached

## PARAMS
| Name           | Type   | Description                                  |
| -------------  | ------ | -------------------------------------------- |
| `VERSION`      | String | Broadcast Format Version                     |
| `TICK`         | String | 1 to 250 characters in length                |
| `RESUME_BLOCK` | String | Block index to resume BTNS `ACTION` commands |
| `MEMO`         | String | An optional memo to include                  |

## Formats

### Version `0`
- `VERSION|TICK|RESUME_BLOCK|MEMO`

## Examples
```
bt:SLEEP|0|JDOG|791495`
This example pauses/sleeps ALL BTNS `ACTION` commands on JDOG `token` until block 791495
```

## Rules

## Notes
- `SLEEP` does _NOT_ prevent `DISPENSER` dispenses, as that could result in a loss of user funds.
- `SLEEP` does _NOT_ prevent usage of the `SLEEP` command 
- `SLEEP` with `RESUME_BLOCK` set to `0` value, will unpause actions immediately.
- `SLEEP` with `RESUME_BLOCK` set to `-1` value, will pause actions indefinitely.
- `ISSUE` `TICK` with `LOCK_SLEEP` set to `1` to permanently prevent use of the `SLEEP` command
- Can use `BATCH` commands to stop `SLEEP`, execute `ACTION` commands, and then resume `SLEEP`, etc.
```
bt:BATCH|0|
SLEEP|0|JDOG|0;
ISSUE|1|JDOG|We are working to resolve the problem;
ISSUE|2|JDOG||1000;
SEND|0|JDOG|1000|1JDogZS6tQcSxwfxhv6XKKjcyicYA4Feev|Funding contract address;
MINT|0|JDOG||1000|1JDogZS6tQcSxwfxhv6XKKjcyicYA4Feev;
SLEEP|0|JDOG|-1
```
