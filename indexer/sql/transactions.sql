DROP TABLE IF EXISTS transactions;
CREATE TABLE transactions (
  tx_index    INTEGER UNSIGNED NOT NULL,
  block_index INTEGER,
  tx_hash_id  INTEGER UNSIGNED NOT NULL, -- id of record in index_transactions table
  action_id   INTEGER UNSIGNED NOT NULL  -- id of record in index_actions table
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE UNIQUE INDEX tx_index    on transactions (tx_index);
CREATE        INDEX block_index on transactions (block_index);
CREATE        INDEX tx_hash_id  on transactions (tx_hash_id);
CREATE        INDEX action_id   on transactions (action_id);
