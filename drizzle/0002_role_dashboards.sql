ALTER TABLE voters ADD COLUMN role TEXT NOT NULL DEFAULT 'voter' CHECK (role IN ('voter','nominee'));
--> statement-breakpoint
ALTER TABLE nominations ADD COLUMN submitted_by INTEGER REFERENCES voters(id);
--> statement-breakpoint
CREATE INDEX nominations_submitted_by ON nominations(submitted_by);
