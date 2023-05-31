DROP TABLE IF EXISTS balances;
CREATE TABLE balances (
    id         INTEGER UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    address_id INTEGER UNSIGNED, -- id of record in index_addresses
    tick_id    INTEGER UNSIGNED,  -- id of record in index_tickers
    quantity   BIGINT  UNSIGNED   
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE INDEX address_id ON balances (address_id);
CREATE INDEX tick_id    ON balances (tick_id);