DROP TABLE IF EXISTS index_actions;
CREATE TABLE index_actions (
    id     INTEGER UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    action VARCHAR(250) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

CREATE INDEX action on index_actions (action);

