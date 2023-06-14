DROP TABLE IF EXISTS index_transactions;
CREATE TABLE index_transactions (
    id   INTEGER UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    hash VARCHAR(250) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE INDEX hash on index_transactions (hash(20));

-- Create record for blank/empty transaction
INSERT INTO index_transactions (id,hash) values (0,'');
