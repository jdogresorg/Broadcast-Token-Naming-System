DROP TABLE IF EXISTS dividends;
CREATE TABLE dividends (
    tx_index         INTEGER UNSIGNED, -- Unique transaction index
    tx_hash_id       INTEGER UNSIGNED, -- id of record in index_transactions
    block_index      INTEGER UNSIGNED, -- block index of DIVIDEND transaction
    source_id        INTEGER UNSIGNED, -- id of record in index_addresses table
    tick_id          INTEGER UNSIGNED, -- id of record in index_ticks
    dividend_tick_id INTEGER UNSIGNED, -- id of record in index_ticks
    amount           VARCHAR(250),     -- Amount of token per unit
    memo_id          INTEGER UNSIGNED, -- id of record in index_memos table 
    status_id        INTEGER UNSIGNED  -- id of record in index_statuses table
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE        INDEX tx_index         ON dividends (tx_index);
CREATE        INDEX source_id        ON dividends (source_id);
CREATE        INDEX tx_hash_id       ON dividends (tx_hash_id);
CREATE        INDEX block_index      ON dividends (block_index);
CREATE        INDEX tick_id          ON dividends (tick_id);
CREATE        INDEX dividend_tick_id ON dividends (dividend_tick_id);
CREATE        INDEX memo_id          ON dividends (memo_id);
CREATE        INDEX status_id        ON dividends (status_id);

