DROP TABLE IF EXISTS pastes;
DROP TABLE IF EXISTS pastebox;
DROP TABLE IF EXISTS pastebins;

CREATE TABLE pastes(
  id INTEGER PRIMARY KEY,
  paste TEXT NOT NULL
);

CREATE TABLE pastebox(
  id INTEGER PRIMARY KEY,
  paste_id INTEGER,
  title TEXT,
  syntax TEXT,
  base62 TEXT UNIQUE,
  created_at TEXT NOT NULL,
  FOREIGN KEY (paste_id) REFERENCES pastes (id)
);