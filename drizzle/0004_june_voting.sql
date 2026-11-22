DROP TRIGGER timestamp_vote;
--> statement-breakpoint
CREATE TRIGGER timestamp_vote AFTER INSERT ON votes
WHEN NEW.cast_at IS NULL BEGIN
 UPDATE votes SET cast_at=unixepoch(), round_id=(
    SELECT id FROM voting_rounds WHERE closed_at IS NULL AND opened_at<=unixepoch()
 ) WHERE id=NEW.id;
END;
--> statement-breakpoint
INSERT INTO voting_rounds(title,opened_at)
SELECT '2027 SBA Awards',1811797200
WHERE NOT EXISTS (SELECT 1 FROM voting_rounds WHERE closed_at IS NULL OR title='2027 SBA Awards');
