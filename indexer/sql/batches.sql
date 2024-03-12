DROP TABLE IF EXISTS batches;
CREATE TABLE batches (
    tx_index       INTEGER UNSIGNED, -- Unique transaction index
    source_id      INTEGER UNSIGNED, -- id of record in index_addresses table
    tx_hash_id     INTEGER UNSIGNED, -- id of record in index_transactions
    block_index    INTEGER UNSIGNED, -- block index of ADDRESS transaction
    status_id      INTEGER UNSIGNED  -- id of record in index_statuses table
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE UNIQUE INDEX tx_index       ON batches (tx_index);
CREATE        INDEX source_id      ON batches (source_id);
CREATE        INDEX tx_hash_id     ON batches (tx_hash_id);
CREATE        INDEX block_index    ON batches (block_index);
CREATE        INDEX status_id      ON batches (status_id);