ALTER TABLE voting_rounds ADD COLUMN results_published_at INTEGER;
--> statement-breakpoint
UPDATE voting_rounds SET results_published_at=closed_at WHERE closed_at IS NOT NULL;
--> statement-breakpoint
CREATE TABLE judging_entries (
 round_id INTEGER NOT NULL REFERENCES voting_rounds(id), nominee INTEGER NOT NULL REFERENCES nominations(id),
 category TEXT NOT NULL, company TEXT NOT NULL, votes INTEGER NOT NULL,
 panel_score INTEGER CHECK(panel_score BETWEEN 0 AND 100),
 PRIMARY KEY(round_id,nominee)
);
--> statement-breakpoint
CREATE TABLE award_results (
 round_id INTEGER NOT NULL REFERENCES voting_rounds(id), category TEXT NOT NULL, place INTEGER NOT NULL,
 company TEXT NOT NULL, votes INTEGER NOT NULL, public_points REAL NOT NULL,
 panel_score INTEGER NOT NULL, total REAL NOT NULL,
 PRIMARY KEY(round_id,category,place)
);
