DROP TABLE IF EXISTS tokens;
CREATE TABLE tokens (
    id          INTEGER UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    tick_id     INTEGER UNSIGNED, -- id of record in index_ticks table
    block_index INTEGER UNSIGNED, -- block index of ISSUE transaction (used in rollbacks)
    max_supply  VARCHAR(250),     -- Maximum Supply
    max_mint    VARCHAR(250),     -- Supply minted
    decimals    TINYINT(2),       -- 0=non-divisible, 1-18=divisible
    icon        VARCHAR(250),     -- URL to icon 
    supply      BIGINT UNSIGNED,  -- Supply minted
    owner_id    INTEGER UNSIGNED  -- id of record in index_addresses table
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

CREATE        INDEX tick_id   ON tokens (tick_id);
CREATE        INDEX owner_id  ON tokens (owner_id);
