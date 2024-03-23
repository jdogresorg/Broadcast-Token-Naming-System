DROP TABLE IF EXISTS fees;
CREATE TABLE fees (
    tx_index       INTEGER UNSIGNED NOT NULL, -- Unique transaction index
    block_index    INTEGER UNSIGNED,          -- block index of transaction
    source_id      INTEGER UNSIGNED,          -- id of record in index_addresses table
    tick_id        INTEGER UNSIGNED,          -- id of record in index_tickers (default = GAS) 
    amount         VARCHAR(250),              -- Amount of TICK
    method         INTEGER UNSIGNED NOT NULL, -- FEE Payment Method (1=Destroy, 2=Donate)
    destination_id INTEGER UNSIGNED           -- id of record in index_addresses table
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE        INDEX tx_index       ON fees (tx_index);
CREATE        INDEX block_index    ON fees (block_index);
CREATE        INDEX source_id      ON fees (source_id);
CREATE        INDEX tick_id        ON fees (tick_id);
CREATE        INDEX destination_id ON fees (destination_id);

