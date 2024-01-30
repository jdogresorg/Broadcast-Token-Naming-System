DROP TABLE IF EXISTS destroys;
CREATE TABLE destroys (
    tx_index       INTEGER UNSIGNED, -- Unique transaction index
    tick_id        INTEGER UNSIGNED, -- id of record in index_ticks table
    amount         VARCHAR(250),     -- Amount of token to destroy
    source_id      INTEGER UNSIGNED, -- id of record in index_addresses table
    tx_hash_id     INTEGER UNSIGNED, -- id of record in index_transactions
    block_index    INTEGER UNSIGNED, -- block index of DESTROY transaction
    memo_id        INTEGER UNSIGNED, -- id of record in index_memos table 
    status_id      INTEGER UNSIGNED  -- id of record in index_statuses table
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE        INDEX tx_index       ON destroys (tx_index);
CREATE        INDEX tick_id        ON destroys (tick_id);
CREATE        INDEX source_id      ON destroys (source_id);
CREATE        INDEX tx_hash_id     ON destroys (tx_hash_id);
CREATE        INDEX block_index    ON destroys (block_index);
CREATE        INDEX memo_id        ON destroys (memo_id);
CREATE        INDEX status_id      ON destroys (status_id);

