DROP TABLE IF EXISTS issuances;
CREATE TABLE issuances (
    tx_index            INTEGER UNSIGNED,                     -- Unique transaction index
    tick_id             INTEGER UNSIGNED,                     -- id of record in index_tickers table
    max_supply          BIGINT UNSIGNED,                      -- Maximum token supply
    max_mint            BIGINT UNSIGNED,                      -- Maximum amount of supply a MINT transaction can issue
    decimals            INTEGER(2) UNSIGNED,                  -- Number of decimal places token should have (max: 18, default: 0)
    description         VARCHAR(250),                         -- URL to a an icon to use for this token (48x48 standard size)
    mint_supply         BIGINT UNSIGNED,                      -- Maximum amount of supply a MINT transaction can issue
    transfer_id         INTEGER UNSIGNED,                     -- id of record in index_addresses table
    transfer_supply_id  INTEGER UNSIGNED,                     -- id of record in index_addresses table
    lock_supply         TINYINT(1) NOT NULL DEFAULT 0,        -- Locks MAX_SUPPLY
    lock_mint           TINYINT(1) NOT NULL DEFAULT 0,        -- Locks MAX_MINT
    lock_description    TINYINT(1) NOT NULL DEFAULT 0,        -- Locks DESCRIPTION
    lock_rug            TINYINT(1) NOT NULL DEFAULT 0,        -- Locks RUG
    lock_sleep          TINYINT(1) NOT NULL DEFAULT 0,        -- Locks SLEEP
    lock_callback       TINYINT(1) NOT NULL DEFAULT 0,        -- Locks CALLBACK_BLOCK/TICK/AMOUNT
    callback_block      INTEGER UNSIGNED,                     -- block_index after which CALLBACK cand be used
    callback_tick_id    INTEGER UNSIGNED,                     -- id of record in index_tickers table
    callback_amount     BIGINT UNSIGNED,                      -- AMOUNT users get if CALLBACK
    mint_allow_list_id  INTEGER UNSIGNED NOT NULL default 0,  -- id of record in index_transactions table
    mint_block_list_id  INTEGER UNSIGNED NOT NULL default 0,  -- id of record in index_transactions table
    source_id           INTEGER UNSIGNED,                     -- id of record in index_addresses table (address that did DEPLOY)
    tx_hash_id          INTEGER UNSIGNED,                     -- id of record in index_transactions
    block_index         INTEGER UNSIGNED,                     -- block index of DEPLOY transaction
    status_id           INTEGER UNSIGNED                      -- id of record in index_statuses table
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE UNIQUE INDEX tx_index           ON issuances (tx_index);
CREATE        INDEX tick_id            ON issuances (tick_id);
CREATE        INDEX source_id          ON issuances (source_id);
CREATE        INDEX transfer_id        ON issuances (transfer_id);
CREATE        INDEX transfer_supply_id ON issuances (transfer_supply_id);
CREATE        INDEX status_id          ON issuances (status_id);
CREATE        INDEX tx_hash_id         ON issuances (tx_hash_id);
CREATE        INDEX callback_tick_id   ON issuances (callback_tick_id);
CREATE        INDEX mint_allow_list_id ON issuances (mint_allow_list_id);
CREATE        INDEX mint_block_list_id ON issuances (mint_block_list_id);
