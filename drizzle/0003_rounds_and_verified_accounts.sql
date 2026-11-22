ALTER TABLE voters ADD COLUMN email_verified INTEGER;
--> statement-breakpoint
ALTER TABLE votes ADD COLUMN cast_at INTEGER;
--> statement-breakpoint
CREATE TABLE voting_rounds (
 id INTEGER PRIMARY KEY, title TEXT NOT NULL, opened_at INTEGER NOT NULL DEFAULT (unixepoch()),
 closed_at INTEGER
);
--> statement-breakpoint
CREATE UNIQUE INDEX voting_round_open ON voting_rounds((1)) WHERE closed_at IS NULL;
--> statement-breakpoint
CREATE UNIQUE INDEX voting_round_title ON voting_rounds(title COLLATE NOCASE);
--> statement-breakpoint
ALTER TABLE votes ADD COLUMN round_id INTEGER REFERENCES voting_rounds(id);
--> statement-breakpoint
CREATE INDEX votes_recent ON votes(round_id,cast_at,nominee);
--> statement-breakpoint
CREATE TRIGGER timestamp_vote AFTER INSERT ON votes
WHEN NEW.cast_at IS NULL BEGIN
 UPDATE votes SET cast_at=unixepoch(),
    round_id=(SELECT id FROM voting_rounds WHERE closed_at IS NULL) WHERE id=NEW.id;
END;
--> statement-breakpoint
CREATE TABLE award_winners (
 id INTEGER PRIMARY KEY, category TEXT NOT NULL, company TEXT NOT NULL, edition TEXT NOT NULL,
 awarded_at INTEGER NOT NULL, nominee INTEGER REFERENCES nominations(id), votes INTEGER,
 UNIQUE(category,edition)
);
--> statement-breakpoint
CREATE TABLE email_verifications (
 voter INTEGER PRIMARY KEY REFERENCES voters(id),
 token_hash TEXT NOT NULL UNIQUE, expires INTEGER NOT NULL, attempted INTEGER NOT NULL
);
--> statement-breakpoint
CREATE TABLE nominee_pairings (
 nomination INTEGER NOT NULL REFERENCES nominations(id), voter INTEGER NOT NULL REFERENCES voters(id),
 status TEXT NOT NULL DEFAULT 'pending' CHECK (status IN ('pending','approved','rejected')),
 PRIMARY KEY(nomination,voter)
);
--> statement-breakpoint
CREATE UNIQUE INDEX nominee_owner ON nominee_pairings(nomination) WHERE status='approved';
--> statement-breakpoint
CREATE INDEX nominee_pairings_voter ON nominee_pairings(voter,status);
--> statement-breakpoint
CREATE TRIGGER request_pairing_after_verification AFTER UPDATE OF email_verified ON voters
WHEN OLD.email_verified IS NULL AND NEW.email_verified IS NOT NULL BEGIN
 INSERT OR IGNORE INTO nominee_pairings(nomination,voter)
 SELECT id,NEW.id FROM nominations
 WHERE lower(trim(json_extract(details,'$.Email')))=lower(trim(NEW.email));
END;
--> statement-breakpoint
CREATE TRIGGER request_pairing_after_nomination AFTER INSERT ON nominations BEGIN
 INSERT OR IGNORE INTO nominee_pairings(nomination,voter)
 SELECT NEW.id,id FROM voters
 WHERE email_verified IS NOT NULL AND lower(trim(email))=lower(trim(json_extract(NEW.details,'$.Email')));
END;
--> statement-breakpoint
CREATE TRIGGER assign_nominee_role AFTER UPDATE OF status ON nominee_pairings
WHEN OLD.status='pending' AND NEW.status='approved' BEGIN
 UPDATE voters SET role='nominee' WHERE id=NEW.voter AND email_verified IS NOT NULL;
END;
