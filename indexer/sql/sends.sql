DROP TABLE IF EXISTS sends;
CREATE TABLE sends (
    tx_index       INTEGER UNSIGNED, -- Unique transaction index
    tx_hash_id     INTEGER UNSIGNED, -- id of record in index_transactions
    block_index    INTEGER UNSIGNED, -- block index of TRANSFER transaction
    tick_id        INTEGER UNSIGNED, -- id of record in index_ticks table
    source_id      INTEGER UNSIGNED, -- id of record in index_addresses table (address that did MINT)
    destination_id INTEGER UNSIGNED, -- id of record in index_addresses table (address that did MINT)
    quantity       VARCHAR(250),     -- Quantity of token in send
    memo           LONGTEXT,         -- Memo included with send (optional)
    status_id      INTEGER UNSIGNED  -- id of record in index_statuses table
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE UNIQUE INDEX tx_index       ON mints (tx_index);
CREATE        INDEX tick_id        ON sends (tick_id);
CREATE        INDEX tx_hash_id     ON sends (tx_hash_id);
CREATE        INDEX source_id      ON sends (source_id);
CREATE        INDEX destination_id ON sends (destination_id);
CREATE        INDEX block_index    ON sends (block_index);
CREATE        INDEX status_id      ON sends (status_id);
