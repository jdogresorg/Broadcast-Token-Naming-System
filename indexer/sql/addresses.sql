DROP TABLE IF EXISTS addresses;
CREATE TABLE addresses (
    tx_index       INTEGER UNSIGNED, -- Unique transaction index
    source_id      INTEGER UNSIGNED, -- id of record in index_addresses table
    tx_hash_id     INTEGER UNSIGNED, -- id of record in index_transactions
    block_index    INTEGER UNSIGNED, -- block index of ADDRESS transaction
    fee_preference INTEGER UNSIGNED,
    require_memo   INTEGER UNSIGNED,
    status_id      INTEGER UNSIGNED  -- id of record in index_statuses table
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE UNIQUE INDEX tx_index       ON addresses (tx_index);
CREATE        INDEX source_id      ON addresses (source_id);
CREATE        INDEX tx_hash_id     ON addresses (tx_hash_id);
CREATE        INDEX block_index    ON addresses (block_index);
CREATE        INDEX status_id      ON addresses (status_id);