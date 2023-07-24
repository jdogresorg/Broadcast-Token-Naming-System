DROP TABLE IF EXISTS index_statuses;
CREATE TABLE index_statuses (
    id     INTEGER UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    status VARCHAR(250) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

CREATE INDEX status on index_statuses (status);
