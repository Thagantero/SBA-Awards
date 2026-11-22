CREATE TABLE traffic_days (
 day TEXT PRIMARY KEY, visitors INTEGER NOT NULL DEFAULT 0, views INTEGER NOT NULL DEFAULT 0
);
--> statement-breakpoint
CREATE TABLE traffic_visitors (
 day TEXT NOT NULL, browser TEXT NOT NULL, PRIMARY KEY(day,browser)
);
--> statement-breakpoint
CREATE TABLE traffic_pages (
 day TEXT NOT NULL, path TEXT NOT NULL, views INTEGER NOT NULL DEFAULT 0, PRIMARY KEY(day,path)
);
--> statement-breakpoint
CREATE TRIGGER count_daily_visitor AFTER INSERT ON traffic_visitors BEGIN
 INSERT INTO traffic_days(day,visitors) VALUES(NEW.day,1)
 ON CONFLICT(day) DO UPDATE SET visitors=visitors+1;
END;
--> statement-breakpoint
CREATE INDEX votes_cast_at ON votes(cast_at);
