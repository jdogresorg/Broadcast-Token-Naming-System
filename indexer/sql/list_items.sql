DROP TABLE IF EXISTS list_items;
CREATE TABLE list_items (
    list_id   INTEGER UNSIGNED NOT NULL, -- id of record in index_transactions (list being created)
    item_id   INTEGER UNSIGNED           -- id of record (tick_id, asset_id, address_id) tables
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE        INDEX list_id           ON list_items (list_id);
CREATE        INDEX item_id           ON list_items (item_id);
