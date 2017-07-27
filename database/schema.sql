DROP TABLE IF EXISTS pastes;
DROP TABLE IF EXISTS pastebins;

CREATE TABLE pastes(
  id INTEGER PRIMARY KEY,
  paste TEXT
);

CREATE TABLE pastebins(
  id INTEGER PRIMARY KEY,
  paste_id INTEGER,
  title TEXT,
  syntax TEXT,
  base62 TEXT,
  FOREIGN KEY (paste_id) REFERENCES pastes (id)
);