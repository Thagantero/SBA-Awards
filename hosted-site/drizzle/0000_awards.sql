CREATE TABLE voters (
 id INTEGER PRIMARY KEY, first TEXT NOT NULL, last TEXT NOT NULL,
 username TEXT NOT NULL COLLATE NOCASE UNIQUE, email TEXT NOT NULL COLLATE NOCASE UNIQUE,
 password TEXT NOT NULL, credits INTEGER NOT NULL DEFAULT 0 CHECK(credits >= 0)
);
--> statement-breakpoint
CREATE TABLE sessions (
 id TEXT PRIMARY KEY, csrf TEXT NOT NULL, expires INTEGER NOT NULL,
 voter INTEGER REFERENCES voters(id), admin INTEGER NOT NULL DEFAULT 0, draft TEXT
);
--> statement-breakpoint
CREATE TABLE nominations (
 id INTEGER PRIMARY KEY, category TEXT NOT NULL, company TEXT NOT NULL, country TEXT NOT NULL,
 details TEXT NOT NULL, photo TEXT NOT NULL, proof TEXT NOT NULL, status TEXT NOT NULL DEFAULT 'pending'
);
--> statement-breakpoint
CREATE TABLE messages (
 id INTEGER PRIMARY KEY, name TEXT NOT NULL, email TEXT NOT NULL, message TEXT NOT NULL
);
--> statement-breakpoint
CREATE TABLE daily_vote_claims (
 voter INTEGER NOT NULL REFERENCES voters(id), day TEXT NOT NULL, PRIMARY KEY(voter,day)
);
--> statement-breakpoint
CREATE TABLE votes (id INTEGER PRIMARY KEY, nominee INTEGER NOT NULL REFERENCES nominations(id));
--> statement-breakpoint
CREATE INDEX votes_nominee ON votes(nominee);
--> statement-breakpoint
CREATE TABLE orders (
 id TEXT PRIMARY KEY, voter INTEGER NOT NULL REFERENCES voters(id), kind TEXT NOT NULL,
 units INTEGER NOT NULL, amount INTEGER NOT NULL, day TEXT NOT NULL, free INTEGER NOT NULL,
 paid INTEGER NOT NULL, picks TEXT NOT NULL, status TEXT NOT NULL DEFAULT 'starting',
 merchant TEXT, checkout TEXT UNIQUE, created INTEGER NOT NULL DEFAULT (unixepoch())
);
--> statement-breakpoint
CREATE UNIQUE INDEX orders_pending ON orders(voter) WHERE status IN ('starting','pending','unknown');
--> statement-breakpoint
CREATE TABLE submissions (
 id TEXT PRIMARY KEY, voter INTEGER NOT NULL REFERENCES voters(id),
 day TEXT NOT NULL, free INTEGER NOT NULL, paid INTEGER NOT NULL, picks TEXT NOT NULL,
 payment TEXT UNIQUE REFERENCES orders(id)
);
--> statement-breakpoint
CREATE TRIGGER apply_submission AFTER INSERT ON submissions BEGIN
 INSERT INTO daily_vote_claims(voter,day) SELECT NEW.voter,NEW.day WHERE NEW.free > 0;
 UPDATE voters SET credits=credits-NEW.paid WHERE id=NEW.voter AND NEW.paid > 0;
 INSERT INTO votes(nominee) SELECT value FROM json_each(NEW.picks);
 DELETE FROM submissions WHERE id=NEW.id;
END;
--> statement-breakpoint
CREATE TRIGGER apply_payment AFTER UPDATE OF status ON orders
WHEN OLD.status='pending' AND NEW.status='paid' BEGIN
 UPDATE voters SET credits=credits+NEW.units WHERE id=NEW.voter AND NEW.kind='topup';
 INSERT INTO submissions(id,voter,day,free,paid,picks,payment)
 SELECT NEW.id,NEW.voter,NEW.day,NEW.free,NEW.paid,NEW.picks,NEW.id WHERE NEW.kind='ballot';
END;
