DROP TABLE IF EXISTS list_edits;
CREATE TABLE list_edits (
    list_id   INTEGER UNSIGNED NOT NULL,  -- id of record in index_transactions (list being created)
    item_id   INTEGER UNSIGNED,           -- id of record (tick_id, asset_id, address_id) tables
    status_id INTEGER UNSIGNED            -- id of record in index_statuses table
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE        INDEX list_id           ON list_edits (list_id);
CREATE        INDEX item_id           ON list_edits (item_id);
CREATE        INDEX status_id         ON list_edits (status_id);
