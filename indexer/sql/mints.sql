DROP TABLE IF EXISTS mints;
CREATE TABLE mints (
    tx_index       INTEGER UNSIGNED, -- Unique transaction index
    tick_id        INTEGER UNSIGNED, -- id of record in index_ticks table
    amount         BIGINT,           -- Amount of token to transfer
    source_id      INTEGER UNSIGNED, -- id of record in index_addresses table (address that did MINT)
    destination_id INTEGER UNSIGNED, -- id of record in index_addresses table (optional, mint and transfer)
    tx_hash_id     INTEGER UNSIGNED, -- id of record in index_transactions
    block_index    INTEGER UNSIGNED, -- block index of MINT transaction
    status_id      INTEGER UNSIGNED  -- id of record in index_statuses table
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE UNIQUE INDEX tx_index       ON mints (tx_index);
CREATE        INDEX tick_id        ON mints (tick_id);
CREATE        INDEX tx_hash_id     ON mints (tx_hash_id);
CREATE        INDEX source_id      ON mints (source_id);
CREATE        INDEX destination_id ON mints (destination_id);
CREATE        INDEX status_id      ON mints (status_id);
