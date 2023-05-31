DROP TABLE IF EXISTS blocks;
CREATE TABLE blocks (
    id               INTEGER UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    block_index      INTEGER UNSIGNED,
    credits_hash_id  INTEGER UNSIGNED,  -- id of record in index_transactions table (sha256 hash of credits data)
    debits_hash_id   INTEGER UNSIGNED,  -- id of record in index_transactions table (sha256 hash of debits data)
    balances_hash_id INTEGER UNSIGNED,  -- id of record in index_transactions table (sha256 hash of balances data)
    txlist_hash_id   INTEGER UNSIGNED   -- id of record in index_transactions table (sha256 hash of index_tx data)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;


CREATE INDEX block_index       ON blocks (block_index);
CREATE INDEX credits_hash_id   ON blocks (credits_hash_id);
CREATE INDEX debits_hash_id    ON blocks (debits_hash_id);
CREATE INDEX balances_hash_id  ON blocks (balances_hash_id);
