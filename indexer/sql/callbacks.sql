DROP TABLE IF EXISTS callbacks;
CREATE TABLE callbacks (
    tx_index         INTEGER UNSIGNED, -- Unique transaction index
    tx_hash_id       INTEGER UNSIGNED, -- id of record in index_transactions
    block_index      INTEGER UNSIGNED, -- block index of CALLBACKS transaction
    tick_id          INTEGER UNSIGNED, -- id of record in index_tickers
    callback_tick_id INTEGER UNSIGNED, -- id of record in index_tickers
    callback_amount  VARCHAR(250),     -- Amount of token per unit
    source_id        INTEGER UNSIGNED, -- id of record in index_addresses table
    memo_id          INTEGER UNSIGNED, -- id of record in index_memos table 
    status_id        INTEGER UNSIGNED  -- id of record in index_statuses table
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE        INDEX tx_index         ON callbacks (tx_index);
CREATE        INDEX source_id        ON callbacks (source_id);
CREATE        INDEX tick_id          ON callbacks (tick_id);
CREATE        INDEX callback_tick_id ON callbacks (callback_tick_id);
CREATE        INDEX tx_hash_id       ON callbacks (tx_hash_id);
CREATE        INDEX block_index      ON callbacks (block_index);
CREATE        INDEX memo_id          ON callbacks (memo_id);
CREATE        INDEX status_id        ON callbacks (status_id);

