DROP TABLE IF EXISTS pastes;
DROP TABLE IF EXISTS pastebox;

CREATE TABLE pastes(
  id INTEGER PRIMARY KEY,
  paste TEXT
);

CREATE TABLE pastebox(
  id INTEGER PRIMARY KEY,
  paste_id INTEGER,
  title TEXT,
  syntax TEXT,
  base62 TEXT,
  created_at TEXT,
  FOREIGN KEY (paste_id) REFERENCES pastes (id)
);