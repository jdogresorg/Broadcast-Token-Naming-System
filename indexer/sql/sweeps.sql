DROP TABLE IF EXISTS sweeps;
CREATE TABLE sweeps (
    tx_index         INTEGER UNSIGNED, -- Unique transaction index
    tx_hash_id       INTEGER UNSIGNED, -- id of record in index_transactions
    block_index      INTEGER UNSIGNED, -- block index of SWEEP transaction
    balances         INTEGER UNSIGNED, -- Indicates if token balances should be swept
    ownerships       INTEGER UNSIGNED, -- Indicates if token ownerships should be swept
    source_id        INTEGER UNSIGNED, -- id of record in index_addresses table
    destination_id   INTEGER UNSIGNED, -- id of record in index_addresses table
    memo_id          INTEGER UNSIGNED, -- id of record in index_memos table 
    status_id        INTEGER UNSIGNED  -- id of record in index_statuses table
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE        INDEX tx_index       ON sweeps (tx_index);
CREATE        INDEX source_id      ON sweeps (source_id);
CREATE        INDEX destination_id ON sweeps (destination_id);
CREATE        INDEX tx_hash_id     ON sweeps (tx_hash_id);
CREATE        INDEX block_index    ON sweeps (block_index);
CREATE        INDEX memo_id        ON sweeps (memo_id);
CREATE        INDEX status_id      ON sweeps (status_id);

