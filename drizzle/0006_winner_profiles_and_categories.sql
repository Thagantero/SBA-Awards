ALTER TABLE award_winners ADD COLUMN country TEXT;
--> statement-breakpoint
ALTER TABLE award_winners ADD COLUMN image TEXT;
--> statement-breakpoint
UPDATE award_winners
SET country=(SELECT country FROM nominations WHERE id=award_winners.nominee),
    image=(SELECT photo FROM nominations WHERE id=award_winners.nominee)
WHERE nominee IS NOT NULL;
--> statement-breakpoint
UPDATE nominations SET category=CASE category WHEN 'Telecom' THEN 'Creative Economy' ELSE 'Media and Press' END
WHERE category IN ('Telecom','Publishing');
--> statement-breakpoint
UPDATE award_winners SET category=CASE category WHEN 'Telecom' THEN 'Creative Economy' ELSE 'Media and Press' END
WHERE category IN ('Telecom','Publishing');
--> statement-breakpoint
UPDATE judging_entries SET category=CASE category WHEN 'Telecom' THEN 'Creative Economy' ELSE 'Media and Press' END
WHERE category IN ('Telecom','Publishing');
--> statement-breakpoint
UPDATE award_results SET category=CASE category WHEN 'Telecom' THEN 'Creative Economy' ELSE 'Media and Press' END
WHERE category IN ('Telecom','Publishing');
--> statement-breakpoint
UPDATE sessions SET draft=json_remove(json_set(draft,'$."Creative Economy"',json_extract(draft,'$.Telecom')),'$.Telecom')
WHERE draft IS NOT NULL AND json_type(draft,'$.Telecom') IS NOT NULL;
--> statement-breakpoint
UPDATE sessions SET draft=json_remove(json_set(draft,'$."Media and Press"',json_extract(draft,'$.Publishing')),'$.Publishing')
WHERE draft IS NOT NULL AND json_type(draft,'$.Publishing') IS NOT NULL;
