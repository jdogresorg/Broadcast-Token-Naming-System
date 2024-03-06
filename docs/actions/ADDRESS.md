# ADDRESS command
This command configures address specific options.

## PARAMS
| Name             | Type   | Description                             |
| ---------------- | ------ | ----------------------------------------|
| `VERSION`        | String | Broadcast Format Version                |
| `FEE_PREFERENCE` | String | Set preference for how `FEE` is used    |
| `REQUIRE_MEMO`   | String | Require a `MEMO` on any received `SEND` |

## Formats

### Version `0`
- `VERSION|FEE_PREFERENCE|REQUIRE_MEMO`

## Examples
```
bt:ADDRESS|0|1|0
This example sets the address to DESTROY fees
```

```
bt:ADDRESS|0|2|0
This example sets the address to DONATE fees
```

```
bt:ADDRESS|0|0|1
This example sets the address to require a `MEMO` on any received `SEND`
```

## `FEE_PREFERENCE` Options
- `1` = Destroy `FEE`, provably lowering supply
- `2` = Donate `FEE` to protocol development (default)
- `3` = Donate `FEE` to community development

## Notes
