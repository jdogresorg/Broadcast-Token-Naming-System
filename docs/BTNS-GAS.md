        Title: BTNS GAS Utility Token
        Author: Jeremy Johnson (J-Dog)
        Status: Draft
        Type: Standards
        Created: 2024-02-12

# Abstract
Establish an fairly minted GAS utility token 

# Motivation
Establish a standard GAS utility token to provide functionality where it isn't technically possible to use BTC.

# Specification

```
GAS Supply  : 10,000,000.00000000 (10M)
Mint Period : 30 days
Mint Method : Fair / Open Mint
```

### Mint Period 1
```
Mint Supply : 2,500,000.00000000 (2.5M)
Mint Amount : 60.0 GAS (40 GAS + 50% Bonus)
Mint Target : 41,666 transactions
Mint Starts : Block # XXX,XXXX
```

## Mint Period 2
```
Mint Supply : 2,500,000.00000000 (2.5M)
Mint Amount : 50.0 GAS (40 GAS + 25% Bonus)
Mint Target : 50,000 transactions
Mint Starts : Block # XXX,XXXX
```

## Mint Period 3
```
Mint Supply : 2,500,000.00000000 (2.5M)
Mint Amount : 44.0 GAS (40 GAS + 10% Bonus)
Mint Target : 56,818 transactions
Mint Starts : Block # XXX,XXXX
```

## Mint Period 4
```
Mint Supply : 2,500,000.00000000 (2.5M)
Mint Amount : 40.0 GAS (40 GAS + 0% Bonus)
Mint Target : 56,818 transactions
Mint Starts : Block # XXX,XXXX
```

# Notes
- Address `1BTNSGASK5En7rFurDJ79LQ8CVYo2ecLC8` is the initial issuing address for the GAS token
- Each period starts at a specific block # (1,008 blocks - 1 week)
- 2.5M GAS minted each period
- 1,000 GAS MINT per address limit in place