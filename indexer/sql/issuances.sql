DROP TABLE IF EXISTS issuances;
CREATE TABLE issuances (
    tx_index            INTEGER UNSIGNED, -- Unique transaction index
    tick_id             INTEGER UNSIGNED, -- id of record in index_tickers table
    max_supply          VARCHAR(250),     -- Maximum token supply (max: 18,446,744,073,709,551,615.00000000)
    max_mint            VARCHAR(250),     -- Maximum amount of supply a MINT transaction can issue
    decimals            VARCHAR(250),     -- Number of decimal places token should have (max: 18, default: 0)
    icon                VARCHAR(250),     -- URL to a an icon to use for this token (48x48 standard size)
    mint_supply         VARCHAR(250),     -- Maximum amount of supply a MINT transaction can issue
    transfer_id         INTEGER UNSIGNED, -- id of record in index_addresses table
    transfer_supply_id  INTEGER UNSIGNED, -- id of record in index_addresses table
    source_id           INTEGER UNSIGNED, -- id of record in index_addresses table (address that did DEPLOY)
    tx_hash_id          INTEGER UNSIGNED, -- id of record in index_transactions
    block_index         INTEGER UNSIGNED, -- block index of DEPLOY transaction
    status_id           INTEGER UNSIGNED  -- id of record in index_statuses table
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE UNIQUE INDEX tx_index           ON issuances (tx_index);
CREATE        INDEX tick_id            ON issuances (tick_id);
CREATE        INDEX source_id          ON issuances (source_id);
CREATE        INDEX transfer_id        ON issuances (transfer_id);
CREATE        INDEX transfer_supply_id ON issuances (transfer_supply_id);
CREATE        INDEX status_id          ON issuances (status_id);
