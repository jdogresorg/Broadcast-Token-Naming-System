DROP TABLE IF EXISTS index_memos;
CREATE TABLE index_memos (
    id     INTEGER UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    memo   VARCHAR(250) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

CREATE INDEX memo on index_memos (memo);