# Broadcast Token Naming System (BTNS)

This is the official repository for Broadcast Token Naming System (**`BTNS`**). 

The **`BTNS`** operates on the **`Counterparty`** platform using the `broadcast` feature. 

**`BTNS`** and **`BTNS-420`** are **NOT** _officially_ endorsed by the **`Counterparty`** project. 

**`BTNS-420`** was copied, almost entirely, from the existing **`Counterparty`** platform features. 

This is a personal hobby project, to allow for experimentation with additional token features and functionality.

If you find this project interesting, please consider making a donation of BTC, **`Counterparty`** assets, or **`BTNS`** tokens to `1BTNS42oqp1vzLTfBzHQGPhfvQdi2UjoEc` to support continued experimentation.


# Disclaimer 
`BTNS` is a bleeding-edge experimental protocol to play around with token functionality on **`Bitcoin`** and **`Counterparty`**. This is a hobby project, and  I am **NOT** responsible for any losses, financial or otherwise, incurred from using this experimental protocol and its functionality. 

Science is messy sometimes... _**DO NOT**_ put in funds your not willing to lose!


# BTNS Specs

Name                          | Title                                     |  Author / Owner        | Status        |
----------------------------  | ----------------------------------------- | ---------------------- | ------------- |
[`BTNS`](./docs/BTNS.md)        | Broadcast Token Naming System (**BTNS**) | Jeremy Johnson (J-Dog) | Accepted      |
[`BTNS-420`](./docs/BTNS-420.md)| BTNS Token Standard (**BTNS-420**)        | Jeremy Johnson (J-Dog) | Draft         |


# BTNS `ACTION` commands

| ACTION                                     | Description                                                                                       | 
| ------------------------------------------ | ------------------------------------------------------------------------------------------------- |
| [`AIRDROP`](./docs/actions/AIRDROP.md)     | Transfer/Distribute `token` supply to a `LIST`                                                    |
| [`BATCH`](./docs/actions/BATCH.md)         | Execute multiple BTNS `ACTION` commands in a single transaction                                   |
| [`BET`](./docs/actions/BET.md)             | Bet `token` on `broadcast` oracle feed outcomes                                                   |
| [`CALLBACK`](./docs/actions/CALLBACK.md)   | Return all `token` supply to owner address after a set block, in exchange for a different `token` |
| [`DESTROY`](./docs/actions/DESTROY.md)     | Destroy `token` supply forever                                                                    |
| [`DISPENSER`](./docs/actions/DISPENSER.md) | Create a dispenser (vending machine) to dispense a `token` when triggered                         |
| [`DIVIDEND`](./docs/actions/DIVIDEND.md)   | Issue a dividend on a `token`                                                                     |
| [`ISSUE`](./docs/actions/ISSUE.md)         | Create or issue a `token` and define how the token works                                          |
| [`LIST`](./docs/actions/LIST.md)           | Create a list for use with various BTNS `ACTION` commands                                         |
| [`MINT`](./docs/actions/MINT.md)           | Create or mint `token` supply                                                                     |
| [`RUG`](./docs/actions/RUG.md)             | Perform a rug pull on a `token`                                                                   |
| [`SEND`](./docs/actions/SEND.md)           | Transfer or move some `token` balances between addresses                                          |
| [`SLEEP`](./docs/actions/SLEEP.md)         | Pause all actions on a `token` for a certain number of blocks                                     |
| [`SWEEP`](./docs/actions/SWEEP.md)         | Transfer all `token` and/or ownerships to a destination address                                   |


# BTNS Indexers
- [BTNS Indexer](.indexer/) (PHP)


# BTNS Explorers
- [BTNS XChain (mainnet)](https://btns.xchain.io/)
- [BTNS XChain (testnet)](https://btns-testnet.xchain.io/)


# BTNS APIs
- [BTNS XChain API (mainnet)](https://btns.xchain.io/api)
- [BTNS XChain API (testnet)](https://btns-testnet.xchain.io/api)


# BTNS Wallets
- [Freewallet](https://freewallet.io/)


# Counterparty Platform
**`Counterparty`** is the swiss army knife of Bitcoin, and I encourage **ALL** developers looking to build on Bitcoin to take the time to go down the **`Counterparty`** rabbit hole.
- [Counterparty Website](https://counterparty.io)
- [Counterparty Documentation](https://docs.counterparty.io)
- [Counterparty API](https://api.counterparty.io)


# BTNS Logos

## BTNS Logo
![BTNS Logo](./logos/BTNS.wtf.png)

## BTNS-420 Logo
![BTNS Logo](./logos/BTNS-420.io.png)
