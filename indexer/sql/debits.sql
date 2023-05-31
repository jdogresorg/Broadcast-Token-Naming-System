DROP TABLE IF EXISTS debits;
CREATE TABLE debits (
    block_index INTEGER UNSIGNED, -- Block index that credit happened
    address_id  INTEGER UNSIGNED, -- id of record in index_addresses table
    tick_id     INTEGER UNSIGNED, -- id of record in index_tickers table
    quantity    VARCHAR(250),     -- AMOUNT of credit
    action      TEXT,             -- DEPLOY / MINT / TRANSFER
    event_id    INTEGER UNSIGNED  -- id of record in index_transactions table
) ENGINE=MyISAM DEFAULT  CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE INDEX block_index   ON debits (block_index);
CREATE INDEX address_id    ON debits (address_id);
CREATE INDEX tick_id       ON debits (tick_id);
CREATE INDEX event_id      ON debits (event_id);
