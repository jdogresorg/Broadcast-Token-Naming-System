# Broadcast Token Name System (BTNS) Indexer

This is a very basic BTNS indexer which supports the following BTNS specs:

- BTNS (version `0` - `DEPLOY`, `MINT`, `TRANSFER`)
- BTNS-69    (coming soon)
- BTNS-420   (coming soon)
- BTNS-442   (coming soon)
- BTNS-l33t  (coming soon)
- BTNS-80085 (coming soon)

BTNS actions (`DEPLOY`, `MINT`, `TRANSFER`)

- add database tables

We include block_index in every tx, so we can rollback
- add rollback functionality

 * DISCLAIMER: This is cutting-edge / experimental technology and BTNS
 * is something I wrote out of necessity. This first BTNS indexer is 
 * meant as a BASE framework to determine what BTNS actions are valid
 * and which are considered invalid (and ignored). While the BTNS rules
 * are strightforward, the code to interpret those rules always needs
 * refining as time goes on. BTNS Transactions that are considered valid 
 * one day may be considered invalid the next day after further review.
 * 
 * TLDR: We are doing science, it can be messy, please bear with us.
 * 
 * Author: Jeremy Johnson <j-dog@j-dog.net>

Requires BCMATH

Note: This indexer requires the usage of [counterparty2mysql](https://github.com/jdogresorg/counterparty2mysql) which pulls Counterparty data into a MySQL database. Counteparty2mysql includes some additional database optimizations, such as indexing all addresses, transactions, and assets for faster queries.