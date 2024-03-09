DROP TABLE IF EXISTS issues;
CREATE TABLE issues (
    tx_index            INTEGER UNSIGNED NOT NULL,            -- Unique transaction index
    tick_id             INTEGER UNSIGNED,                     -- id of record in index_tickers table
    max_supply          VARCHAR(250),                         -- Maximum token supply (1000000000000000000000.000000000000000000 = 40 Characters)
    max_mint            VARCHAR(250),                         -- Maximum amount of supply a MINT transaction can issue
    decimals            INTEGER(2) UNSIGNED,                  -- Number of decimal places token should have (max: 18, default: 0)
    description         VARCHAR(250),                         -- URL to a an icon to use for this token (48x48 standard size)
    mint_supply         VARCHAR(250),                          -- Maximum amount of supply a MINT transaction can issue
    transfer_id         INTEGER UNSIGNED,                     -- id of record in index_addresses table
    transfer_supply_id  INTEGER UNSIGNED,                     -- id of record in index_addresses table
    lock_max_supply     TINYINT(1) NOT NULL DEFAULT 0,        -- Locks MAX_SUPPLY
    lock_mint           TINYINT(1) NOT NULL DEFAULT 0,        -- Locks MINT
    lock_max_mint       TINYINT(1) NOT NULL DEFAULT 0,        -- Locks MAX_MINT
    lock_description    TINYINT(1) NOT NULL DEFAULT 0,        -- Locks DESCRIPTION
    lock_rug            TINYINT(1) NOT NULL DEFAULT 0,        -- Locks RUG
    lock_sleep          TINYINT(1) NOT NULL DEFAULT 0,        -- Locks SLEEP
    lock_callback       TINYINT(1) NOT NULL DEFAULT 0,        -- Locks CALLBACK_BLOCK/TICK/AMOUNT
    callback_block      INTEGER UNSIGNED,                     -- block_index after which CALLBACK cand be used
    callback_tick_id    INTEGER UNSIGNED,                     -- id of record in index_tickers table
    callback_amount     VARCHAR(250),                         -- AMOUNT users get if CALLBACK
    allow_list_id       INTEGER UNSIGNED NOT NULL default 0,  -- id of record in index_transactions table
    block_list_id       INTEGER UNSIGNED NOT NULL default 0,  -- id of record in index_transactions table
    mint_address_max    VARCHAR(250),                         -- Maximum amount of supply an address can MINT
    mint_start_block    INTEGER UNSIGNED,                     -- block_index when MINT transactions are allowed (begin mint)
    mint_stop_block     INTEGER UNSIGNED,                     -- BLOCK_INDEX when MINT transactions are NOT allowed (end mint)
    source_id           INTEGER UNSIGNED,                     -- id of record in index_addresses table (address that did DEPLOY)
    tx_hash_id          INTEGER UNSIGNED,                     -- id of record in index_transactions
    block_index         INTEGER UNSIGNED,                     -- block index of DEPLOY transaction
    status_id           INTEGER UNSIGNED                      -- id of record in index_statuses table
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE UNIQUE INDEX tx_index           ON issues (tx_index);
CREATE        INDEX tick_id            ON issues (tick_id);
CREATE        INDEX source_id          ON issues (source_id);
CREATE        INDEX transfer_id        ON issues (transfer_id);
CREATE        INDEX transfer_supply_id ON issues (transfer_supply_id);
CREATE        INDEX status_id          ON issues (status_id);
CREATE        INDEX tx_hash_id         ON issues (tx_hash_id);
CREATE        INDEX callback_tick_id   ON issues (callback_tick_id);
CREATE        INDEX allow_list_id      ON issues (allow_list_id);
CREATE        INDEX block_list_id      ON issues (block_list_id);
