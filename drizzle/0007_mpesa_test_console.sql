CREATE TABLE mpesa_tests (
 id TEXT PRIMARY KEY,
 environment TEXT NOT NULL CHECK(environment IN ('sandbox','production')),
 status TEXT NOT NULL CHECK(status IN ('starting','pending','unknown','failed','paid')),
 stage TEXT NOT NULL,
 merchant TEXT,
 checkout TEXT UNIQUE,
 code TEXT,
 created INTEGER NOT NULL DEFAULT (unixepoch()),
 callback_at INTEGER
);
