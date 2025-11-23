--
-- Schema selection
--
-- This script can be executed directly in psql/pgAdmin, or from PHP/Laravel.
-- It reads the session setting `app.schema` to decide which schema to target.
--  * If `app.schema` is set (e.g. in Laravel with DB::statement('SET app.schema TO ?')),
--    that schema will be used.
--  * If not set, it falls back to the default schema name "thingy".
--

--
-- Schema (re)creation
-- The DO block is needed because identifiers (schema names) cannot be parameterized.
--
DO $do$
DECLARE 
    s text := COALESCE(current_setting('app.schema', true), 'lbaw2544');
BEGIN 
    -- identifiers require dynamic SQL
    EXECUTE format('DROP SCHEMA IF EXISTS %I CASCADE', s);
    EXECUTE format('CREATE SCHEMA IF NOT EXISTS %I', s);
    
    -- set search_path for the rest of the script
    PERFORM set_config('search_path', format('%I, public', s), false);
END $do$ LANGUAGE plpgsql;

--
-- Create tables
--

-- MEDIA (Base)
CREATE TABLE media (
    id INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    creator VARCHAR(255) NOT NULL,
    releaseYear INT,
    coverImage VARCHAR(255)
);

CREATE TABLE book (
    mediaId INTEGER PRIMARY KEY REFERENCES media(id) ON DELETE CASCADE
);

CREATE TABLE film (
    mediaId INTEGER PRIMARY KEY REFERENCES media(id) ON DELETE CASCADE
);

CREATE TABLE music (
    mediaId INTEGER PRIMARY KEY REFERENCES media(id) ON DELETE CASCADE
);

-- USERS
CREATE TABLE users (
    id INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    passwordHash VARCHAR(255) NOT NULL,
    bio TEXT,
    profilePicture VARCHAR(255),
    isPrivate BOOLEAN DEFAULT FALSE,
    isAdmin BOOLEAN DEFAULT FALSE,
    isBlocked BOOLEAN DEFAULT FALSE,
    favoriteFilm INTEGER REFERENCES media(id),
    favoriteBook INTEGER REFERENCES media(id),
    favoriteSong INTEGER REFERENCES media(id),
    createdAt TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- POSTS
CREATE TABLE post (
    id INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    userId INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    createdAt TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE post_like (
    postId INTEGER REFERENCES post(id) ON DELETE CASCADE,
    userId INTEGER REFERENCES users(id) ON DELETE CASCADE,
    createdAt TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (postId, userId)
);

CREATE TABLE post_tag (
    postId INTEGER REFERENCES post(id) ON DELETE CASCADE,
    userId INTEGER REFERENCES users(id) ON DELETE CASCADE,
    createdAt TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (postId, userId)
);

-- STANDARD POST
CREATE TABLE standard_post (
    postId INTEGER PRIMARY KEY REFERENCES post(id) ON DELETE CASCADE,
    text TEXT,
    imageUrl VARCHAR(255),
    CHECK (text IS NOT NULL OR imageUrl IS NOT NULL)
);

-- REVIEW POST
CREATE TABLE review (
    postId INTEGER PRIMARY KEY REFERENCES post(id) ON DELETE CASCADE,
    rating INTEGER NOT NULL CHECK (rating >= 0 AND rating <= 5),
    mediaId INTEGER NOT NULL REFERENCES media(id) ON DELETE CASCADE,
    content TEXT
);

-- COMMENTS
CREATE TABLE comment (
    id INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    postId INTEGER NOT NULL REFERENCES post(id) ON DELETE CASCADE,
    userId INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    content TEXT NOT NULL,
    createdAt TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE comment_like (
    commentId INTEGER REFERENCES comment(id) ON DELETE CASCADE,
    userId INTEGER REFERENCES users(id) ON DELETE CASCADE,
    createdAt TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (commentId, userId)
);

-- GROUPS
CREATE TABLE groups (
    id INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    isPrivate BOOLEAN NOT NULL DEFAULT FALSE,
    icon VARCHAR(255),
    createdAt TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE membership (
    userId INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    groupId INTEGER NOT NULL REFERENCES groups(id) ON DELETE CASCADE,
    isOwner BOOLEAN DEFAULT FALSE,
    joinedAt TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (userId, groupId)
);

-- NOTIFICATIONS
CREATE TABLE notification (
    id INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    message TEXT NOT NULL,
    isRead BOOLEAN DEFAULT FALSE,
    receiverId INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    createdAt TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- ACTIVITY NOTIFICATIONS
CREATE TABLE activity_notification (
    notificationId INTEGER PRIMARY KEY REFERENCES notification(id) ON DELETE CASCADE,
    postId INTEGER REFERENCES post(id) ON DELETE CASCADE
);

CREATE TABLE comment_notification (
    notificationId INTEGER PRIMARY KEY REFERENCES activity_notification(notificationId) ON DELETE CASCADE,
    commentId INTEGER REFERENCES comment(id) ON DELETE CASCADE
);

CREATE TABLE tag_notification (
    notificationId INTEGER PRIMARY KEY REFERENCES activity_notification(notificationId) ON DELETE CASCADE,
    postId INTEGER REFERENCES post(id) ON DELETE CASCADE
);

CREATE TABLE like_notification (
    notificationId INTEGER PRIMARY KEY REFERENCES activity_notification(notificationId) ON DELETE CASCADE,
    postId INTEGER REFERENCES post(id) ON DELETE CASCADE
);

-- REQUESTS
CREATE TABLE request (
    notificationId INTEGER REFERENCES notification(id) ON DELETE CASCADE PRIMARY KEY,
    status VARCHAR(20) NOT NULL DEFAULT 'pending' CHECK (status IN ('pending', 'accepted', 'rejected')),
    senderId INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE friend_request (
    requestId INTEGER PRIMARY KEY REFERENCES request(notificationId) ON DELETE CASCADE
);

CREATE TABLE group_invite_request (
    requestId INTEGER PRIMARY KEY REFERENCES request(notificationId) ON DELETE CASCADE,
    groupId INTEGER NOT NULL REFERENCES groups(id) ON DELETE CASCADE
);

CREATE TABLE group_join_request (
    requestId INTEGER PRIMARY KEY REFERENCES request(notificationId) ON DELETE CASCADE,
    groupId INTEGER NOT NULL REFERENCES groups(id) ON DELETE CASCADE
);

-- FRIENDSHIPS
CREATE TABLE friendship (
    userId1 INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    userId2 INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    createdAt TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (userId1, userId2),
    CHECK (userId1 < userId2)
);

-- REPORTS
CREATE TABLE report (
    id INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    reason TEXT NOT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'pending' CHECK (status IN ('pending', 'accepted', 'rejected')),
    postId INTEGER REFERENCES post(id) ON DELETE CASCADE,
    commentId INTEGER REFERENCES comment(id) ON DELETE CASCADE,
    createdAt TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CHECK (
        (postId IS NOT NULL AND commentId IS NULL) OR 
        (postId IS NULL AND commentId IS NOT NULL)
    )
);

--
-- Indexes
--

-- IDX01: Post Timeline (Main Feed)
CREATE INDEX post_created_at_idx ON post USING btree (createdAt DESC);
CLUSTER post USING post_created_at_idx;

-- IDX02: User Profile Feed
CREATE INDEX post_user_created_at_idx ON post USING btree (userId, createdAt DESC);

-- IDX03: Post Comments
CREATE INDEX comment_post_created_at_idx ON comment USING btree (postId, createdAt ASC);

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
END 
$$ LANGUAGE plpgsql;

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
END 
$$ LANGUAGE plpgsql;

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
END 
$$ LANGUAGE plpgsql;

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
END 
$$ LANGUAGE plpgsql;

-- 3. Create the trigger
CREATE TRIGGER review_search_update_trigger 
BEFORE INSERT OR UPDATE ON review 
FOR EACH ROW EXECUTE PROCEDURE review_search_update();

-- 4. Create the GIN index
CREATE INDEX fts_review_idx ON review USING gin(fts_document);

--
-- Triggers
--

-- FUNCTION: Notify Like on Post
CREATE OR REPLACE FUNCTION fn_notify_post_like() RETURNS TRIGGER AS $$
DECLARE 
    notif_id INT;
    post_owner INT;
BEGIN
    SELECT userId INTO post_owner FROM post WHERE id = NEW.postId;
    
    INSERT INTO notification (message, receiverId)
    VALUES (CONCAT('Your post received a like from ', NEW.userId), post_owner)
    RETURNING id INTO notif_id;
    
    INSERT INTO activity_notification (notificationId, postId)
    VALUES (notif_id, NEW.postId);
    
    INSERT INTO like_notification (notificationId, postId)
    VALUES (notif_id, NEW.postId);
    
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

-- TRIGGER: Notify Like on Post
CREATE OR REPLACE TRIGGER trg_post_like_notify
AFTER INSERT ON post_like 
FOR EACH ROW EXECUTE FUNCTION fn_notify_post_like();

-- FUNCTION: Notify Like on Comment
CREATE OR REPLACE FUNCTION fn_notify_comment_like() RETURNS TRIGGER AS $$
DECLARE 
    notif_id INTEGER;
    comment_owner INTEGER;
    post_ref INTEGER;
BEGIN
    SELECT userId, postId INTO comment_owner, post_ref
    FROM comment
    WHERE id = NEW.commentId;
    
    INSERT INTO notification (message, receiverId)
    VALUES (CONCAT('Your comment received a like from ', NEW.userId), comment_owner)
    RETURNING id INTO notif_id;
    
    INSERT INTO activity_notification (notificationId, postId)
    VALUES (notif_id, post_ref);
    
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

-- TRIGGER: Notify Like on Comment
CREATE OR REPLACE TRIGGER trg_comment_like_notify
AFTER INSERT ON comment_like 
FOR EACH ROW EXECUTE FUNCTION fn_notify_comment_like();

-- FUNCTION: Notify Comment on Post
CREATE OR REPLACE FUNCTION fn_notify_comment() RETURNS TRIGGER AS $$
DECLARE 
    notif_id INTEGER;
    post_owner INTEGER;
BEGIN
    SELECT userId INTO post_owner FROM post WHERE id = NEW.postId;
    
    INSERT INTO notification (message, receiverId)
    VALUES (CONCAT('Your post received a comment from ', NEW.userId), post_owner)
    RETURNING id INTO notif_id;
    
    INSERT INTO activity_notification (notificationId, postId)
    VALUES (notif_id, NEW.postId);
    
    INSERT INTO comment_notification (notificationId, commentId)
    VALUES (notif_id, NEW.id);
    
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

-- TRIGGER: Notify Comment on Post
CREATE OR REPLACE TRIGGER trg_comment_notify
AFTER INSERT ON comment 
FOR EACH ROW EXECUTE FUNCTION fn_notify_comment();

-- FUNCTION: Notify Friend Request  
-- Note: This trigger is not needed since fn_send_friend_request() already creates the notification
CREATE OR REPLACE FUNCTION fn_notify_friend_request() RETURNS TRIGGER AS $$ 
BEGIN 
    -- No action needed - notification already created by fn_send_friend_request()
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

-- TRIGGER: Notify Friend Request
CREATE OR REPLACE TRIGGER trg_friend_request_notify
AFTER INSERT ON friend_request 
FOR EACH ROW EXECUTE FUNCTION fn_notify_friend_request();

-- FUNCTION: Notify Group Join Request
CREATE OR REPLACE FUNCTION fn_notify_group_join_request() RETURNS TRIGGER AS $$ 
BEGIN
    INSERT INTO notification (message, receiverId)
    SELECT CONCAT(
        'User ',
        (SELECT username FROM users WHERE id = (SELECT senderId FROM request WHERE notificationId = NEW.requestId)),
        ' requested to join your group.'
    ),
    (SELECT userId FROM membership WHERE groupId = NEW.groupId AND isOwner = TRUE LIMIT 1);
    
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

-- TRIGGER: Notify Group Join Request
CREATE OR REPLACE TRIGGER trg_group_join_notify
AFTER INSERT ON group_join_request 
FOR EACH ROW EXECUTE FUNCTION fn_notify_group_join_request();

-- FUNCTION: Anonymize User Data
CREATE OR REPLACE FUNCTION fn_anonymize_user_data(p_user_id INT) RETURNS VOID AS $$ 
BEGIN
    UPDATE users
    SET name = 'Deleted User',
        username = CONCAT('deleted_', id),
        email = CONCAT('deleted_', id, '@anon.com'),
        bio = NULL,
        profilePicture = NULL,
        isPrivate = TRUE,
        passwordHash = 'deleted'
    WHERE id = p_user_id;

    DELETE FROM friendship WHERE userId1 = p_user_id OR userId2 = p_user_id;
    DELETE FROM membership WHERE userId = p_user_id;
    DELETE FROM post WHERE userId = p_user_id;
    DELETE FROM comment WHERE userId = p_user_id;
END;
$$ LANGUAGE plpgsql;

--
-- Transactions
--

-- US18 - Edit Profile
CREATE OR REPLACE FUNCTION fn_edit_profile(
    p_user_id INT,
    p_name TEXT,
    p_bio TEXT,
    p_picture TEXT
) RETURNS VOID AS $$ 
BEGIN
    SET TRANSACTION ISOLATION LEVEL READ COMMITTED;
    -- Suitable: simple update, only needs to avoid dirty reads.
    UPDATE users
    SET name = p_name,
        bio = p_bio,
        profilePicture = p_picture
    WHERE id = p_user_id;
END;
$$ LANGUAGE plpgsql;

-- US22 - Create Regular Post
CREATE OR REPLACE FUNCTION fn_create_standard_post(p_user_id INT, p_text TEXT, p_image_url TEXT) RETURNS VOID AS $$
DECLARE 
    new_post_id INT;
BEGIN
    SET TRANSACTION ISOLATION LEVEL READ COMMITTED;
    -- Suitable: short insert transaction, no concurrency risk.
    INSERT INTO post (userId)
    VALUES (p_user_id)
    RETURNING id INTO new_post_id;
    
    INSERT INTO standard_post (postId, text, imageUrl)
    VALUES (new_post_id, p_text, p_image_url);
END;
$$ LANGUAGE plpgsql;

-- US23 - Create Review Post
CREATE OR REPLACE FUNCTION fn_create_review_post(
    p_user_id INT,
    p_rating INT,
    p_media_id INT,
    p_content TEXT
) RETURNS VOID AS $$
DECLARE 
    new_post_id INT;
BEGIN
    SET TRANSACTION ISOLATION LEVEL READ COMMITTED;
    -- Similar to standard post creation.
    INSERT INTO post (userId)
    VALUES (p_user_id)
    RETURNING id INTO new_post_id;
    
    INSERT INTO review (postId, rating, mediaId, content)
    VALUES (new_post_id, p_rating, p_media_id, p_content);
END;
$$ LANGUAGE plpgsql;

-- Create music
CREATE OR REPLACE FUNCTION fn_create_music(
    p_title VARCHAR(255),
    p_creator VARCHAR(255),
    p_release_year INT,
    p_cover_image VARCHAR(255)
) RETURNS INTEGER AS $$
DECLARE
    new_media_id INTEGER;
BEGIN
    INSERT INTO media (title, creator, releaseYear, coverImage)
    VALUES (p_title, p_creator, p_release_year, p_cover_image)
    RETURNING id INTO new_media_id;

    INSERT INTO music (mediaId)
    VALUES (new_media_id);

    RETURN new_media_id;
END;
$$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION fn_create_book(
    p_title VARCHAR(255),
    p_creator VARCHAR(255),
    p_release_year INT,
    p_cover_image VARCHAR(255)
) RETURNS INTEGER AS $$
DECLARE
    new_media_id INTEGER;
BEGIN
    INSERT INTO media (title, creator, releaseYear, coverImage)
    VALUES (p_title, p_creator, p_release_year, p_cover_image)
    RETURNING id INTO new_media_id;

    INSERT INTO book (mediaId)
    VALUES (new_media_id);

    RETURN new_media_id;
END;
$$ LANGUAGE plpgsql;

-- US24 - Report Content
CREATE OR REPLACE FUNCTION fn_report_post(p_post_id INT, p_reason TEXT) RETURNS VOID AS $$ 
BEGIN
    SET TRANSACTION ISOLATION LEVEL READ COMMITTED;
    -- Suitable: single insert, minimal concurrency impact.
    INSERT INTO report (reason, status, postId, createdAt)
    VALUES (p_reason, 'pending', p_post_id, CURRENT_TIMESTAMP);
END;
$$ LANGUAGE plpgsql;

-- US26 - Delete Account (Anonymization)
CREATE OR REPLACE FUNCTION fn_delete_account(p_user_id INT) RETURNS VOID AS $$ 
BEGIN
    SET TRANSACTION ISOLATION LEVEL SERIALIZABLE;
    -- Critical operation across multiple tables; must prevent interference.
    PERFORM fn_anonymize_user_data(p_user_id);
END;
$$ LANGUAGE plpgsql;

-- US28 - Send Friend Request
CREATE OR REPLACE FUNCTION fn_send_friend_request(p_sender_id INT, p_receiver_id INT) RETURNS VOID AS $$
DECLARE 
    notif_id INT;
BEGIN
    SET TRANSACTION ISOLATION LEVEL REPEATABLE READ;
    -- Prevents duplicate requests between same users under concurrency.
    INSERT INTO notification (message, receiverId)
    VALUES (CONCAT('You received a friend request from ', (SELECT username FROM users WHERE id = p_sender_id)), p_receiver_id)
    RETURNING id INTO notif_id;
    
    INSERT INTO request (notificationId, status, senderId)
    VALUES (notif_id, 'pending', p_sender_id);
    
    INSERT INTO friend_request (requestId)
    VALUES (notif_id);
END;
$$ LANGUAGE plpgsql;

-- US29 - Unfriend
CREATE OR REPLACE FUNCTION fn_unfriend(p_user1_id INT, p_user2_id INT) RETURNS VOID AS $$ 
BEGIN
    SET TRANSACTION ISOLATION LEVEL READ COMMITTED;
    -- Simple delete operation; stronger isolation not needed.
    DELETE FROM friendship
    WHERE (userId1 = p_user1_id AND userId2 = p_user2_id)
       OR (userId1 = p_user2_id AND userId2 = p_user1_id);
END;
$$ LANGUAGE plpgsql;

-- US30 - Comment on Post
CREATE OR REPLACE FUNCTION fn_comment_on_post(p_post_id INT, p_user_id INT, p_content TEXT) RETURNS VOID AS $$ 
BEGIN
    SET TRANSACTION ISOLATION LEVEL READ COMMITTED;
    -- Simple insert, only depends on committed data.
    INSERT INTO comment (postId, userId, content)
    VALUES (p_post_id, p_user_id, p_content);
END;
$$ LANGUAGE plpgsql;

-- US31 - React to Post
CREATE OR REPLACE FUNCTION fn_react_to_post(p_post_id INT, p_user_id INT) RETURNS VOID AS $$ 
BEGIN
    SET TRANSACTION ISOLATION LEVEL REPEATABLE READ;
    -- Prevents duplicate likes under concurrent transactions.
    INSERT INTO post_like (postId, userId)
    VALUES (p_post_id, p_user_id) ON CONFLICT DO NOTHING;
END;
$$ LANGUAGE plpgsql;

-- US32 - React to Comment
CREATE OR REPLACE FUNCTION fn_react_to_comment(p_comment_id INT, p_user_id INT) RETURNS VOID AS $$ 
BEGIN
    SET TRANSACTION ISOLATION LEVEL REPEATABLE READ;
    -- Same reasoning as post reactions.
    INSERT INTO comment_like (commentId, userId)
    VALUES (p_comment_id, p_user_id) ON CONFLICT DO NOTHING;
END;
$$ LANGUAGE plpgsql;

-- US33 - Create Group
CREATE OR REPLACE FUNCTION fn_create_group(
    p_user_id INT,
    p_name TEXT,
    p_description TEXT,
    p_is_private BOOLEAN
) RETURNS VOID AS $$
DECLARE 
    new_group_id INT;
BEGIN
    SET TRANSACTION ISOLATION LEVEL SERIALIZABLE;
    -- Group creation and membership insert must be atomic.
    INSERT INTO groups (name, description, isPrivate)
    VALUES (p_name, p_description, p_is_private)
    RETURNING id INTO new_group_id;
    
    INSERT INTO membership (userId, groupId, isOwner)
    VALUES (p_user_id, new_group_id, TRUE);
END;
$$ LANGUAGE plpgsql;

-- US34 - Send Group Invitation
CREATE OR REPLACE FUNCTION fn_send_group_invite(
    p_sender_id INT,
    p_receiver_id INT,
    p_group_id INT
) RETURNS VOID AS $$
DECLARE 
    notif_id INT;
BEGIN
    SET TRANSACTION ISOLATION LEVEL REPEATABLE READ;
    -- Prevents duplicate invitations for same user and group.
    INSERT INTO notification (message, receiverId)
    VALUES (CONCAT('You have been invited to join ', (SELECT name FROM groups WHERE id = p_group_id)), p_receiver_id)
    RETURNING id INTO notif_id;
    
    INSERT INTO request (notificationId, status, senderId)
    VALUES (notif_id, 'pending', p_sender_id);
    
    INSERT INTO group_invite_request (requestId, groupId)
    VALUES (notif_id, p_group_id);
END;
$$ LANGUAGE plpgsql;

-- US51 - Manage Reported Content
CREATE OR REPLACE FUNCTION fn_manage_report(p_report_id INT, p_new_status TEXT) RETURNS VOID AS $$
DECLARE 
    post_target INT;
BEGIN
    -- Ensures two moderators can’t process the same report simultaneously.
    SET TRANSACTION ISOLATION LEVEL SERIALIZABLE; 
    
    UPDATE report
    SET status = p_new_status
    WHERE id = p_report_id;

    SELECT postId INTO post_target FROM report WHERE id = p_report_id;

    IF p_new_status = 'accepted' THEN
        DELETE FROM post WHERE id = post_target;
    END IF;
END;
$$ LANGUAGE plpgsql;

-- US53 - Block/Unblock User
CREATE OR REPLACE FUNCTION fn_toggle_block_user(p_user_id INT, p_is_blocked BOOLEAN) RETURNS VOID AS $$ 
BEGIN
    SET TRANSACTION ISOLATION LEVEL READ COMMITTED; -- Simple update, no need for strong isolation.
    
    UPDATE users
    SET isBlocked = p_is_blocked
    WHERE id = p_user_id;
END;
$$ LANGUAGE plpgsql;

-- US54 - Admin Delete User Account
CREATE OR REPLACE FUNCTION fn_admin_delete_user(p_user_id INT) RETURNS VOID AS $$ 
BEGIN
    -- Administrative critical operation; must be fully isolated.
    SET TRANSACTION ISOLATION LEVEL SERIALIZABLE; 
    
    PERFORM fn_anonymize_user_data(p_user_id);
END;
$$ LANGUAGE plpgsql;

-- Get user friendship count
CREATE OR REPLACE FUNCTION fn_get_friendship_count(p_user_id INT) RETURNS INT AS $$
DECLARE
    friendship_count INT;
BEGIN
    SELECT COUNT(*) INTO friendship_count
    FROM friendship
    WHERE userId1 = p_user_id OR userId2 = p_user_id;
    
    RETURN friendship_count;
END;
$$ LANGUAGE plpgsql;

-- Get user posts count  
CREATE OR REPLACE FUNCTION fn_get_user_posts_count(p_user_id INT) RETURNS INT AS $$
DECLARE
    posts_count INT;
BEGIN
    SELECT COUNT(*) INTO posts_count
    FROM post
    WHERE userId = p_user_id;
    
    RETURN posts_count;
END;
$$ LANGUAGE plpgsql;

-- Check if two users are friends
CREATE OR REPLACE FUNCTION fn_are_friends(p_user1_id INT, p_user2_id INT) RETURNS BOOLEAN AS $$
DECLARE
    friendship_exists BOOLEAN := FALSE;
BEGIN
    SELECT EXISTS(
        SELECT 1 FROM friendship 
        WHERE (userId1 = LEAST(p_user1_id, p_user2_id) AND userId2 = GREATEST(p_user1_id, p_user2_id))
    ) INTO friendship_exists;
    
    RETURN friendship_exists;
END;
$$ LANGUAGE plpgsql;

--
-- Insert values
--

-- CLEAN CURRENT DATA
TRUNCATE TABLE 
    friend_request,
    group_invite_request,
    group_join_request,
    request,
    like_notification,
    tag_notification,
    comment_notification,
    activity_notification,
    notification,
    friendship,
    membership,
    groups,
    comment_like,
    comment,
    post_tag,
    post_like,
    review,
    standard_post,
    post,
    users,
    film,
    book,
    music,
    media,
    report 
RESTART IDENTITY CASCADE;

-- MEDIA
INSERT INTO media (title, creator, releaseYear, coverImage) VALUES 
    ('Inception', 'Christopher Nolan', 2010, 'inception.jpg'),
    ('The Great Gatsby', 'F. Scott Fitzgerald', 2003, 'http://books.google.com/books/content?id=iXn5U2IzVH0C&printsec=frontcover&img=1&zoom=1&edge=curl&source=gbs_api'),
    ('Get Lucky', 'Daft Punk', 2013, 'https://i.scdn.co/image/ab67616d0000b2739b9b36b0e22870b9f542d937'),
    ('The Matrix', 'Lana Wachowski', 1999, 'matrix.jpg'),
    ('To Kill a Mockingbird', 'Harper Lee', 1960, 'http://books.google.com/books/content?id=DRagKAMw8AcC&printsec=frontcover&img=1&zoom=1&source=gbs_api'),
    ('Interstellar', 'Christopher Nolan', 2014, 'interstellar.jpg'),
    ('Strawberry Fields Forever - Remastered 2009', 'The Beatles', 1967, 'https://i.scdn.co/image/ab67616d0000b273692d9189b2bd75525893f0c1'),
    ('1984', 'George Orwell', 2021, 'http://books.google.com/books/content?id=5AwIEAAAQBAJ&printsec=frontcover&img=1&zoom=1&edge=curl&source=gbs_api');

INSERT INTO film (mediaId) VALUES 
    (1), (4), (6);

INSERT INTO book (mediaId) VALUES 
    (2), (5), (8);

INSERT INTO music (mediaId) VALUES 
    (3), (7);

-- USERS
INSERT INTO users (
    name, username, email, passwordHash, bio, 
    profilePicture, isPrivate, isAdmin, 
    favoriteFilm, favoriteBook, favoriteSong
) VALUES 
    ('Alice Martins', 'alice', 'alice@example.org', '$2y$10$HfzIhGCCaxqyaIdGgjARSuOKAcm1Uy82YfLuNaajn6JrjLWy9Sj/W', 'Movie lover', 'alice.jpg', FALSE, FALSE, 1, 2, 3),
    ('Bruno Silva', 'bruno', 'bruno@example.org', '$2y$10$HfzIhGCCaxqyaIdGgjARSuOKAcm1Uy82YfLuNaajn6JrjLWy9Sj/W', 'Reader & gamer', 'bruno.jpg', FALSE, TRUE, 4, 5, 3),
    ('Carla Dias', 'carla', 'carla@example.org', '$2y$10$HfzIhGCCaxqyaIdGgjARSuOKAcm1Uy82YfLuNaajn6JrjLWy9Sj/W', 'Music addict', 'carla.jpg', TRUE, FALSE, 1, 2, 7),
    ('David Costa', 'david', 'david@example.org', '$2y$10$HfzIhGCCaxqyaIdGgjARSuOKAcm1Uy82YfLuNaajn6JrjLWy9Sj/W', 'Cinephile', 'david.jpg', FALSE, FALSE, 6, NULL, NULL),
    ('Eva Rocha', 'eva', 'eva@example.org', '$2y$10$HfzIhGCCaxqyaIdGgjARSuOKAcm1Uy82YfLuNaajn6JrjLWy9Sj/W', 'Book enthusiast', 'eva.jpg', TRUE, FALSE, 4, 8, 7),
    ('Filipe Moreira', 'filipe', 'filipe@example.org', '$2y$10$HfzIhGCCaxqyaIdGgjARSuOKAcm1Uy82YfLuNaajn6JrjLWy9Sj/W', 'Vinyl collector', 'filipe.jpg', FALSE, FALSE, 1, NULL, 3),
    ('John Doe', 'john_doe', 'john@example.org', '$2y$10$HfzIhGCCaxqyaIdGgjARSuOKAcm1Uy82YfLuNaajn6JrjLWy9Sj/W', 'Private person', 'john.jpg', TRUE, FALSE, 1, 2, 3),
    ('Jane Smith', 'jane_doe', 'jane@example.org', '$2y$10$HfzIhGCCaxqyaIdGgjARSuOKAcm1Uy82YfLuNaajn6JrjLWy9Sj/W', 'Keep it secret', 'jane.jpg', TRUE, FALSE, 4, 5, 7);

-- POSTS
INSERT INTO post (userId) VALUES 
    (1), (2), (3), (4), (5), (6);

INSERT INTO standard_post (postId, text, imageUrl) VALUES 
    (1, 'Just watched Inception again. Still brilliant.', 'inception-post.jpg'),
    (2, 'Reading The Great Gatsby this weekend.', NULL),
    (5, 'Finally finished 1984. Heavy stuff.', '1984-review.jpg');

INSERT INTO review (postId, rating, mediaId, content) VALUES 
    (3, 5, 3, 'This song is timeless.'),
    (4, 4, 6, 'Interstellar soundtrack gives me chills.'),
    (6, 3, 8, 'Good but depressing.');

-- POST INTERACTIONS
INSERT INTO post_like (postId, userId) VALUES 
    (1, 2), (1, 3), (1, 4),
    (2, 1),
    (3, 5),
    (4, 3),
    (5, 6),
    (6, 2);

INSERT INTO post_tag (postId, userId) VALUES 
    (1, 3),
    (2, 4),
    (3, 5),
    (4, 6),
    (5, 1),
    (6, 2);

-- COMMENTS
INSERT INTO comment (postId, userId, content) VALUES 
    (1, 2, 'Totally agree! It’s a masterpiece.'),
    (1, 3, 'Love that movie too.'),
    (2, 1, 'The Gatsby prose is just magical.'),
    (3, 5, 'Daft Punk never misses.'),
    (4, 1, 'That soundtrack is pure emotion.'),
    (5, 2, '1984 hits differently nowadays.'),
    (6, 4, 'Yeah, definitely not a light read.');

INSERT INTO comment_like (commentId, userId) VALUES 
    (1, 1),
    (2, 1),
    (3, 2),
    (4, 1),
    (5, 3),
    (6, 5);

-- GROUPS
INSERT INTO groups (name, description, isPrivate, icon) VALUES 
    ('Film Buffs', 'Discuss your favorite movies', FALSE, 'film-buffs.jpg'),
    ('Bookworms', 'Share and review your favorite books', TRUE, 'bookworms.jpg'),
    ('Music Lovers', 'Everything about records and concerts', FALSE, 'music-lovers.jpg');

INSERT INTO membership (userId, groupId, isOwner) VALUES 
    (1, 1, TRUE),
    (2, 1, FALSE),
    (3, 3, TRUE),
    (4, 1, FALSE),
    (5, 2, TRUE),
    (6, 3, FALSE);

-- FRIENDSHIPS
INSERT INTO friendship (userId1, userId2) VALUES 
    (1, 2),
    (1, 3),
    (2, 4),
    (3, 5),
    (4, 6),
    (2, 5),
    (2, 3);

-- NOTIFICATIONS
-- Note: Notifications are automatically created by triggers when:
-- - post_like inserts trigger fn_notify_post_like() 
-- - comment_like inserts trigger fn_notify_comment_like()
-- - comment inserts trigger fn_notify_comment()
-- - friend_request inserts trigger fn_notify_friend_request() 
-- - group_join_request inserts trigger fn_notify_group_join_request()
-- So we don't manually insert notifications here to avoid conflicts

-- REQUESTS
INSERT INTO request (notificationId, status, senderId) VALUES 
    (7, 'pending', 2),
    (3, 'accepted', 1),
    (8, 'pending', 3);

INSERT INTO friend_request (requestId) VALUES 
    (7);

INSERT INTO group_invite_request (requestId, groupId) VALUES 
    (3, 1);

INSERT INTO group_join_request (requestId, groupId) VALUES 
    (8, 2);

-- REPORTS
INSERT INTO report (reason, status, postId, commentId) VALUES 
    ('Inappropriate content', 'pending', 4, NULL),
    ('Spam comment', 'accepted', NULL, 2),
    ('Harassment', 'pending', 5, NULL);