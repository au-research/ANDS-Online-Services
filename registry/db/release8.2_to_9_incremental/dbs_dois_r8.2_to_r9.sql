UPDATE doi_objects SET updated_when = created_when WHERE updated_when IS NULL;
ALTER TABLE doi_objects ALTER COLUMN updated_when SET DEFAULT now();
