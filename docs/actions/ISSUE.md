# ISSUE command
This command creates or issues a `BTNS` `token`

## PARAMS
| Name               | Type   | Description                                                                                 |
| ------------------ | ------ | ------------------------------------------------------------------------------------------- |
| `TICK`             | String | 1 to 250 characters in length (see rules below )                                            |
| `MAX_SUPPLY`       | String | Maximum token supply (max: 18,446,744,073,709,551,615 - commas not allowed)                 |
| `MAX_MINT`         | String | Maximum amount of supply a `MINT` transaction can issue                                     |
| `DECIMALS`         | String | Number of decimal places token should have (max: 18, default: 0)                            |
| `DESCRIPTION`      | String | Description of token (250 chars max)                                                        |
| `MINT_SUPPLY`      | String | Amount of token supply to mint in immediately (default:0)                                   |
| `TRANSFER`         | String | Address to transfer ownership of the `token` to (owner can perform future actions on token) |
| `TRANSFER_SUPPLY`  | String | Address to transfer `MINT_SUPPLY` to (mint initial supply and transfer to address)          |
| `LOCK_SUPPLY`      | String | Lock `MAX_SUPPLY` permanently (cannot increase `MAX_SUPPLY`)                                |
| `LOCK_MINT`        | String | Lock `MAX_MINT` permanently (cannot edit `MAX_MINT`)                                        |
| `LOCK_DESCRIPTION` | String | Lock `token` against `DESCRIPTION` changes                                                  |
| `LOCK_RUG`         | String | Lock `token` against `RUG` command                                                          |
| `LOCK_SLEEP`       | String | Lock `token` against `SLEEP` command                                                        |
| `LOCK_CALLBACK`    | String | Lock `token` `CALLBACK` info                                                                |
| `CALLBACK_BLOCK`   | String | Enable `CALLBACK` command after `CALLBACK_BLOCK`                                            |
| `CALLBACK_TICK`    | String | `TICK` `token` users get when `CALLBACK` command is used                                    |
| `CALLBACK_AMOUNT`  | String | `TICK` `token` amount that users get when `CALLBACK` command is used                        |
| `MINT_ALLOW_LIST`  | String | `TX_HASH` of a BTNS `LIST` of addresses to allow minting from                               |
| `MINT_BLOCK_LIST`  | String | `TX_HASH` of a BTNS `LIST` of addresses to NOT allow minting from                           |


## Formats
- `ISSUE|TICK|MAX_SUPPLY|MAX_MINT|DECIMALS|DESCRIPTION|MINT_SUPPLY|TRANSFER|TRANSFER_SUPPLY|LOCK_SUPPLY|LOCK_MINT|LOCK_DESCRIPTION|LOCK_RUG|LOCK_SLEEP|LOCK_CALLBACK|CALLBACK_BLOCK|CALLBACK_TICK|CALLBACK_AMOUNT|MINT_ALLOW_LIST|MINT_BLOCK_LIST`

## Examples
```
bt:ISSUE|JDOG
This example issues a JDOG token 
```

```
bt:ISSUE|JDOG||||||||1
This example issues a JDOG token with LOCK_SUPPLY set to 1 to permanently
```

```
bt:ISSUE|JDOG|0|0|0|http://j-dog.net/images/JDOG_icon.png
This example issues a JDOG token with a DESCRIPTION which points to an icon
```

```
bt:ISSUE|JDOG|0|0|0|http://j-dog.net/images/JDOG_icon.png|0|1JDogZS6tQcSxwfxhv6XKKjcyicYA4Feev
This example issues a JDOG token with a DESCRIPTION which points to an icon, and transfers token ownership to 1JDogZS6tQcSxwfxhv6XKKjcyicYA4Feev
```

```
bt:ISSUE|JDOG|1000|1|0
This example issues a JDOG token with a max supply of 1000, and a maximum mint of 1 JDOG per mint
```

```
bt:ISSUE|JDOG|1000|1|0|BTNS Tokens Are Cool!
This example issues a JDOG token with a max supply of 1000, and a DESCRIPTION of 'BTNS Tokens are Cool!'
```

```
bt:ISSUE|BRRR|10000000000000000000|10000000000000|0|https://j-dog.net/json/JDOG.json|100
This example issues a BRRR token with a max supply of 1 Quandrillion supply and a maximum mint of 1 Trillion BRRR per mint, associates a JSON file with the token, and immediately mints 100 BRRR to the broadcasting address.
```

```
bt:ISSUE|TEST|100|1|0||1|1JDogZS6tQcSxwfxhv6XKKjcyicYA4Feev|1JDogZS6tQcSxwfxhv6XKKjcyicYA4Feev
This example issues a TEST token with a max supply of 100, and a maximum mint of 1 TEST per mint. This also mints 1 TEST token, and transfers ownership AND initial token supply to 1JDogZS6tQcSxwfxhv6XKKjcyicYA4Feev
```

## Rules
- First `TICK` `ISSUE` will be considered as valid.
- `token` may be issued if `counterparty` `ASSET` of same name does not exist
- `token` may be issued if issuing address is the owner of the `counterparty` `ASSET` of the same name
- Additional `TICK` `ISSUE` transactions after first valid `TICK` `ISSUE`, will be considered invalid and ignored, unless broadcast from `token` owners address
- `DECIMALS` can not be changed after `token` supply is issued
- If `TICK` contains any unicode characters, then `TICK` should be `base64` encoded
- Allowed characters in `TICK`:
   - Any word character (alphanumeric characters and underscores)
   - Special characters: ~!@#$%^&*()_+\-={}[\]\\:<>.?/
   - Most printable emojis in U+1F300 to U+1F5FF
- Special characters pipe `|` and semicolon `;` are **NOT** to be used in `TICK` names 
- `TEXT` can contain a URL to a an icon to use for this token (48x48 standard size)
- `TEXT` can contain a URL to a JSON file with additional information

## Notes
- `ISSUE` `TICK` with `MAX_SUPPLY` set to `0` to reserve the `token` name (reserve name)
- `ISSUE` `TICK` with `MAX_SUPPLY` and `MINT_SUPPLY` set to any non `0` value, to mint supply until `MAX_SUPPLY` is reached (owner can mint beyond `MAX_MINT`)
- `ISSUE` `TICK` with `MAX_SUPPLY` and `MAX_MINT` set to any non `0` value, to enable user minting (fair minting)
- `ISSUE` `TICK` with `LOCK_SUPPLY` set to `1` to permanently lock `MAX_SUPPLY` (irreversible)
- `ISSUE` `TICK` with `LOCK_MINT` set to `1` to permanently lock `MAX_MINT` (irreversible)
- `ISSUE` `TICK` with `LOCK_RUG` set to `1` to permanently prevent use of the `RUG` command
- `ISSUE` `TICK` with `LOCK_SLEEP` set to `1` to permanently prevent use of the `SLEEP` command
- `ISSUE` `TICK` with `LOCK_CALLBACK` set to `1` to permanently lock `CALLBACK_BLOCK`, `CALLBACK_TICK`, and `CALLBACK_AMOUNT` (irreversible)
- `CALLBACK_BLOCK`, `CALLBACK_TICK`, and `CALLBACK_AMOUNT` can be edited via `ISSUE` action until `LOCK_CALLBACK` is set to `1`
- `DEPLOY` `ACTION` can be used for backwards-compatability with BRC20/SRC20 `DEPLOY`
- `DESCRIPTION` field can not contain any pipe `|` or semi-colon `;` characters, as these are reserved
- By default any address can `MINT`, to change this behavior use `MINT_ALLOW_LIST` and `MINT_BLOCK_LIST`