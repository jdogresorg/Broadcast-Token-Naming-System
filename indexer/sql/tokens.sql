DROP TABLE IF EXISTS tokens;
CREATE TABLE tokens (
    id                 INTEGER UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    tick_id            INTEGER UNSIGNED,                     -- id of record in index_ticks table
    block_index        INTEGER UNSIGNED,                     -- block index of ISSUE transaction (used in rollbacks)
    supply             VARCHAR(250),                         -- Current supply
    max_supply         VARCHAR(250),                         -- Maximum Supply
    max_mint           VARCHAR(250),                         -- Supply minted
    decimals           TINYINT(2),                           -- 0=non-divisible, 1-18=divisible
    description        VARCHAR(250),                         -- URL to icon 
    lock_supply        TINYINT(1) NOT NULL DEFAULT 0,        -- Locks MAX_SUPPLY
    lock_mint          TINYINT(1) NOT NULL DEFAULT 0,        -- Locks MAX_MINT
    lock_description   TINYINT(1) NOT NULL DEFAULT 0,        -- Locks DESCRIPTION
    lock_rug           TINYINT(1) NOT NULL DEFAULT 0,        -- Locks RUG
    lock_sleep         TINYINT(1) NOT NULL DEFAULT 0,        -- Locks SLEEP
    lock_callback      TINYINT(1) NOT NULL DEFAULT 0,        -- Locks CALLBACK_BLOCK/TICK/AMOUNT
    callback_block     INTEGER UNSIGNED,                     -- block_index after which CALLBACK cand be used
    callback_tick_id   INTEGER UNSIGNED,                     -- id of record in index_tickers table
    callback_amount    BIGINT UNSIGNED,                      -- AMOUNT users get if CALLBACK
    mint_allow_list_id INTEGER UNSIGNED NOT NULL default 0,  -- id of record in index_transactions table
    mint_block_list_id INTEGER UNSIGNED NOT NULL default 0,  -- id of record in index_transactions table
    owner_id           INTEGER UNSIGNED                      -- id of record in index_addresses table
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE        INDEX tick_id            ON tokens (tick_id);
CREATE        INDEX owner_id           ON tokens (owner_id);
CREATE        INDEX lock_supply        ON tokens (lock_supply);
CREATE        INDEX lock_mint          ON tokens (lock_mint);
CREATE        INDEX lock_description   ON tokens (lock_description);
CREATE        INDEX lock_rug           ON tokens (lock_rug);
CREATE        INDEX lock_sleep         ON tokens (lock_sleep);
CREATE        INDEX lock_callback      ON tokens (lock_callback);
CREATE        INDEX callback_tick_id   ON tokens (callback_tick_id);
CREATE        INDEX mint_allow_list_id ON tokens (mint_allow_list_id);
CREATE        INDEX mint_block_list_id ON tokens (mint_block_list_id);
