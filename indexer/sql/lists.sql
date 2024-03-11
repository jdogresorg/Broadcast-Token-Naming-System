DROP TABLE IF EXISTS lists;
CREATE TABLE lists (
    tx_index            INTEGER UNSIGNED NOT NULL, -- Unique transaction index
    type                VARCHAR(1),                -- List type (1=TICK, 2=ASSET, 3=ADDRESS)
    edit                VARCHAR(1),                -- Edit action (1=ADD, 2=REMOVE)
    source_id           INTEGER UNSIGNED,          -- id of record in index_addresses table
    list_tx_hash_id     INTEGER UNSIGNED,          -- id of record in index_tarnsactions (list being edited)
    tx_hash_id          INTEGER UNSIGNED,          -- id of record in index_transactions (list being created)
    block_index         INTEGER UNSIGNED,          -- block index of LIST transaction
    status_id           INTEGER UNSIGNED           -- id of record in index_statuses table
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE UNIQUE INDEX tx_index        ON lists (tx_index);
CREATE        INDEX block_index     ON lists (block_index);
CREATE        INDEX type            ON lists (type);
CREATE        INDEX edit            ON lists (edit);
CREATE        INDEX list_tx_hash_id ON lists (list_tx_hash_id);
CREATE        INDEX tx_hash_id      ON lists (tx_hash_id);
CREATE        INDEX source_id       ON lists (source_id);
CREATE        INDEX status_id       ON lists (status_id);



