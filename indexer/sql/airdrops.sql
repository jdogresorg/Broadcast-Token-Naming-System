DROP TABLE IF EXISTS airdrops;
CREATE TABLE airdrops (
    tx_index       INTEGER UNSIGNED, -- Unique transaction index
    tx_hash_id     INTEGER UNSIGNED, -- id of record in index_transactions
    block_index    INTEGER UNSIGNED, -- block index of AIRDROP transaction
    tick_id        INTEGER UNSIGNED, -- id of record in index_ticks
    source_id      INTEGER UNSIGNED, -- id of record in index_addresses table
    list_id        INTEGER UNSIGNED, -- id of record in index_transactions 
    amount         VARCHAR(250),     -- Amount of token in airdrop
    memo_id        INTEGER UNSIGNED, -- id of record in index_memos table 
    status_id      INTEGER UNSIGNED  -- id of record in index_statuses table
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE        INDEX tx_index       ON airdrops (tx_index);
CREATE        INDEX source_id      ON airdrops (source_id);
CREATE        INDEX tx_hash_id     ON airdrops (tx_hash_id);
CREATE        INDEX block_index    ON airdrops (block_index);
CREATE        INDEX list_id        ON airdrops (list_id);
CREATE        INDEX tick_id        ON airdrops (tick_id);
CREATE        INDEX memo_id        ON airdrops (memo_id);
CREATE        INDEX status_id      ON airdrops (status_id);

