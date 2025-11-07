SET search_path TO lbaw2544;

-- =============================================================
--  SECTION 1 — PERFORMANCE INDEXES
-- =============================================================

-- IDX01: Post Timeline (Main Feed)
CREATE INDEX post_created_at_idx ON post USING btree (createdAt DESC); 
CLUSTER post USING post_created_at_idx;

-- IDX02: User Profile Feed
CREATE INDEX post_user_created_at_idx ON post USING btree (userId, createdAt DESC);

-- IDX03: Post Comments
CREATE INDEX comment_post_created_at_idx ON comment USING btree (postId, createdAt ASC);

-- =============================================================
--  SECTION 2 — FULL-TEXT SEARCH INDEXES
-- =============================================================
-- Note: These indexes depend on the 'fts_document' column and triggers
-- defined in the triggers/functions file.

-- == IDX11: User Search ==
-- 1. Add tsvector column
ALTER TABLE users ADD COLUMN fts_document tsvector;

-- 2. Create the trigger function for users
CREATE FUNCTION users_search_update() RETURNS trigger AS $$
BEGIN
    IF TG_OP = 'INSERT' THEN
        NEW.fts_document = (
            setweight(to_tsvector('english', coalesce(NEW.name, '')), 'A') ||
            setweight(to_tsvector('english', coalesce(NEW.username, '')), 'A') ||
            setweight(to_tsvector('english', coalesce(NEW.bio, '')), 'B')
        );
    END IF;
    IF TG_OP = 'UPDATE' THEN
        IF (NEW.name <> OLD.name OR NEW.username <> OLD.username OR NEW.bio <> OLD.bio) THEN
            NEW.fts_document = (
                setweight(to_tsvector('english', coalesce(NEW.name, '')), 'A') ||
                setweight(to_tsvector('english', coalesce(NEW.username, '')), 'A') ||
                setweight(to_tsvector('english', coalesce(NEW.bio, '')), 'B')
            );
        END IF;
    END IF;
    RETURN NEW;
END $$ LANGUAGE plpgsql;

-- 3. Create the trigger
CREATE TRIGGER users_search_update_trigger
    BEFORE INSERT OR UPDATE ON users
    FOR EACH ROW EXECUTE PROCEDURE users_search_update();

-- 4. Create the GIN index
CREATE INDEX fts_users_idx ON users USING gin(fts_document);


-- == IDX12: Group Search ==
-- 1. Add tsvector column
ALTER TABLE groups ADD COLUMN fts_document tsvector;

-- 2. Create the trigger function for groups
CREATE FUNCTION groups_search_update() RETURNS trigger AS $$
BEGIN
    IF TG_OP = 'INSERT' THEN
        NEW.fts_document = to_tsvector('english', coalesce(NEW.name, ''));
    END IF;
    IF TG_OP = 'UPDATE' THEN
        IF (NEW.name <> OLD.name) THEN
            NEW.fts_document = to_tsvector('english', coalesce(NEW.name, ''));
        END IF;
    END IF;
    RETURN NEW;
END $$ LANGUAGE plpgsql;

-- 3. Create the trigger
CREATE TRIGGER groups_search_update_trigger
    BEFORE INSERT OR UPDATE ON groups
    FOR EACH ROW EXECUTE PROCEDURE groups_search_update();

-- 4. Create the GIN index
CREATE INDEX fts_groups_idx ON groups USING gin(fts_document);

-- == IDX13: Standard Post Search ==
-- 1. Add tsvector column
ALTER TABLE standard_post ADD COLUMN fts_document tsvector;

-- 2. Create the trigger function for standard posts
CREATE FUNCTION standard_post_search_update() RETURNS trigger AS $$
BEGIN
    IF TG_OP = 'INSERT' THEN
        NEW.fts_document = to_tsvector('english', coalesce(NEW.text, ''));
    END IF;
    IF TG_OP = 'UPDATE' THEN
        IF (NEW.text <> OLD.text) THEN
            NEW.fts_document = to_tsvector('english', coalesce(NEW.text, ''));
        END IF;
    END IF;
    RETURN NEW;
END $$ LANGUAGE plpgsql;

-- 3. Create the trigger
CREATE TRIGGER standard_post_search_update_trigger
    BEFORE INSERT OR UPDATE ON standard_post
    FOR EACH ROW EXECUTE PROCEDURE standard_post_search_update();


-- 4. Create the GIN index
CREATE INDEX fts_standard_post_idx ON standard_post USING gin(fts_document);

-- == IDX14: Review Post Search ==
-- 1. Add tsvector column
ALTER TABLE review ADD COLUMN fts_document tsvector;

-- 2. Create the trigger function for review posts
CREATE FUNCTION review_search_update() RETURNS trigger AS $$
BEGIN
    IF TG_OP = 'INSERT' THEN
        NEW.fts_document = to_tsvector('english', coalesce(NEW.content, ''));
    END IF;
    IF TG_OP = 'UPDATE' THEN
        IF (NEW.content <> OLD.content) THEN
            NEW.fts_document = to_tsvector('english', coalesce(NEW.content, ''));
        END IF;
    END IF;
    RETURN NEW;
END $$ LANGUAGE plpgsql;

-- 3. Create the trigger
CREATE TRIGGER review_search_update_trigger
    BEFORE INSERT OR UPDATE ON review
    FOR EACH ROW EXECUTE PROCEDURE review_search_update();

-- 4. Create the GIN index
CREATE INDEX fts_review_idx ON review USING gin(fts_document);
