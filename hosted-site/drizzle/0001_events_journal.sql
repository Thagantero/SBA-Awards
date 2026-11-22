CREATE TABLE events (
 id INTEGER PRIMARY KEY, title TEXT NOT NULL, description TEXT NOT NULL, day TEXT NOT NULL, time TEXT NOT NULL,
 location TEXT NOT NULL, map_url TEXT NOT NULL, dress_code TEXT NOT NULL, theme TEXT NOT NULL,
 price INTEGER NOT NULL CHECK(price>=0), image TEXT, published INTEGER NOT NULL DEFAULT 1
);
--> statement-breakpoint
CREATE TABLE posts (
 id INTEGER PRIMARY KEY, title TEXT NOT NULL, excerpt TEXT NOT NULL, body TEXT NOT NULL,
 category TEXT NOT NULL, tags TEXT NOT NULL, image TEXT, published INTEGER NOT NULL DEFAULT 0,
 created INTEGER NOT NULL DEFAULT (unixepoch())
);
--> statement-breakpoint
CREATE TABLE ticket_bookings (
 id TEXT PRIMARY KEY, voter INTEGER NOT NULL REFERENCES voters(id), event INTEGER NOT NULL REFERENCES events(id),
 quantity INTEGER NOT NULL CHECK(quantity>0), guest TEXT NOT NULL, email TEXT NOT NULL,
 order_id TEXT UNIQUE REFERENCES orders(id), status TEXT NOT NULL DEFAULT 'pending', checked_in INTEGER
);
--> statement-breakpoint
CREATE INDEX ticket_bookings_voter ON ticket_bookings(voter);
--> statement-breakpoint
CREATE TRIGGER issue_paid_tickets AFTER UPDATE OF status ON orders
WHEN OLD.status='pending' AND NEW.status='paid' AND NEW.kind='ticket' BEGIN
 UPDATE ticket_bookings SET status='confirmed' WHERE order_id=NEW.id AND status='pending';
END;
