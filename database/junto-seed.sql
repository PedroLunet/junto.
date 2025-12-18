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
    title TEXT NOT NULL,
    creator VARCHAR(255) NOT NULL,
    releaseYear INT,
    coverImage TEXT
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
    passwordHash VARCHAR(255),
    bio TEXT,
    profilePicture VARCHAR(255),
    isPrivate BOOLEAN DEFAULT FALSE,
    isAdmin BOOLEAN DEFAULT FALSE,
    isBlocked BOOLEAN DEFAULT FALSE,
    favoriteFilm INTEGER REFERENCES media(id),
    favoriteBook INTEGER REFERENCES media(id),
    favoriteSong INTEGER REFERENCES media(id),
    remember_token VARCHAR(100),
    createdAt TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    google_id VARCHAR
);

CREATE TABLE password_reset_tokens (
    email VARCHAR(255) PRIMARY KEY,
    token VARCHAR(255) NOT NULL,
    created_at TIMESTAMP
);

-- GROUPS (Moved UP so POST can reference it)
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

-- POSTS (Modified to include groupId)
CREATE TABLE post (
    id INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    userId INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    groupId INTEGER REFERENCES groups(id) ON DELETE CASCADE, -- NULL = Profile Post, ID = Group Post
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

-- UNBLOCK APPEALS
CREATE TABLE unblock_appeal (
    id INTEGER GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    userId INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    reason TEXT NOT NULL,
    status VARCHAR(20) DEFAULT 'pending' CHECK (status IN ('pending', 'approved', 'rejected')),
    adminNotes TEXT,
    createdAt TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updatedAt TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

--
-- Indexes
--

-- IDX01: Post Timeline (Main Feed)
CREATE INDEX post_created_at_idx ON post USING btree (createdAt DESC);
CLUSTER post USING post_created_at_idx;

-- IDX02: User Profile Feed
CREATE INDEX post_user_created_at_idx ON post USING btree (userId, createdAt DESC);

-- IDX03: Group Feed
CREATE INDEX post_group_created_at_idx ON post USING btree (groupId, createdAt DESC) WHERE groupId IS NOT NULL;

-- IDX04: Post Comments
CREATE INDEX comment_post_created_at_idx ON comment USING btree (postId, createdAt ASC);

-- IDX05: Unblock Appeals by User
CREATE INDEX unblock_appeal_user_idx ON unblock_appeal USING btree (userId);

-- IDX06: Unblock Appeals by Status
CREATE INDEX unblock_appeal_status_idx ON unblock_appeal USING btree (status);

-- == IDX11: User Search ==
-- 1. Add tsvector column
ALTER TABLE users ADD COLUMN fts_document tsvector;

-- 2. Create the trigger function for users
CREATE FUNCTION users_search_update() RETURNS trigger AS $$ 
BEGIN 
    IF TG_OP = 'INSERT' OR (TG_OP = 'UPDATE' AND (NEW.name <> OLD.name OR NEW.username <> OLD.username OR NEW.bio <> OLD.bio)) THEN 
        NEW.fts_document = (
            setweight(to_tsvector('english', coalesce(NEW.name, '')), 'A') || 
            setweight(to_tsvector('english', coalesce(NEW.username, '')), 'A') || 
            setweight(to_tsvector('english', coalesce(NEW.bio, '')), 'B')
        );
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
    IF TG_OP = 'INSERT' OR (TG_OP = 'UPDATE' AND NEW.name <> OLD.name) THEN 
        NEW.fts_document = to_tsvector('english', coalesce(NEW.name, ''));
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
    IF TG_OP = 'INSERT' OR (TG_OP = 'UPDATE' AND NEW.text <> OLD.text) THEN 
        NEW.fts_document = to_tsvector('english', coalesce(NEW.text, ''));
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
    IF TG_OP = 'INSERT' OR (TG_OP = 'UPDATE' AND NEW.content <> OLD.content) THEN 
        NEW.fts_document = to_tsvector('english', coalesce(NEW.content, ''));
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
    liker_name VARCHAR(100);
BEGIN
    SELECT userId INTO post_owner FROM post WHERE id = NEW.postId;
    SELECT name INTO liker_name FROM users WHERE id = NEW.userId;
    
    INSERT INTO notification (message, receiverId)
    VALUES (CONCAT('Your post received a like from ', liker_name), post_owner)
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
    liker_name VARCHAR(100);
BEGIN
    SELECT userId, postId INTO comment_owner, post_ref
    FROM comment
    WHERE id = NEW.commentId;
    SELECT name INTO liker_name FROM users WHERE id = NEW.userId;
    
    INSERT INTO notification (message, receiverId)
    VALUES (CONCAT('Your comment received a like from ', liker_name), comment_owner)
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
    commenter_name VARCHAR(100);
BEGIN
    SELECT userId INTO post_owner FROM post WHERE id = NEW.postId;
    SELECT name INTO commenter_name FROM users WHERE id = NEW.userId;
    
    INSERT INTO notification (message, receiverId)
    VALUES (CONCAT('Your post received a comment from ', commenter_name), post_owner)
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

-- US22 - Create Regular Post (Updated to allow Groups)
CREATE OR REPLACE FUNCTION fn_create_standard_post(
    p_user_id INT, 
    p_text TEXT, 
    p_image_url TEXT,
    p_group_id INT DEFAULT NULL
) RETURNS VOID AS $$
DECLARE 
    new_post_id INT;
BEGIN
    SET TRANSACTION ISOLATION LEVEL READ COMMITTED;
    -- Suitable: short insert transaction, no concurrency risk.
    INSERT INTO post (userId, groupId)
    VALUES (p_user_id, p_group_id)
    RETURNING id INTO new_post_id;
    
    INSERT INTO standard_post (postId, text, imageUrl)
    VALUES (new_post_id, p_text, p_image_url);
END;
$$ LANGUAGE plpgsql;

-- US23 - Create Review Post (Updated to allow Groups)
CREATE OR REPLACE FUNCTION fn_create_review_post(
    p_user_id INT,
    p_rating INT,
    p_media_id INT,
    p_content TEXT,
    p_group_id INT DEFAULT NULL
) RETURNS VOID AS $$
DECLARE 
    new_post_id INT;
BEGIN
    SET TRANSACTION ISOLATION LEVEL READ COMMITTED;
    -- Similar to standard post creation.
    INSERT INTO post (userId, groupId)
    VALUES (p_user_id, p_group_id)
    RETURNING id INTO new_post_id;
    
    INSERT INTO review (postId, rating, mediaId, content)
    VALUES (new_post_id, p_rating, p_media_id, p_content);
END;
$$ LANGUAGE plpgsql;

-- Media Creation Functions
CREATE OR REPLACE FUNCTION fn_create_music(
    p_title VARCHAR(255), p_creator VARCHAR(255), p_release_year INT, p_cover_image VARCHAR(255)
) RETURNS INTEGER AS $$
DECLARE new_media_id INTEGER;
BEGIN
    INSERT INTO media (title, creator, releaseYear, coverImage) VALUES (p_title, p_creator, p_release_year, p_cover_image) RETURNING id INTO new_media_id;
    INSERT INTO music (mediaId) VALUES (new_media_id);
    RETURN new_media_id;
END;
$$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION fn_create_book(
    p_title VARCHAR(255), p_creator VARCHAR(255), p_release_year INT, p_cover_image VARCHAR(255)
) RETURNS INTEGER AS $$
DECLARE new_media_id INTEGER;
BEGIN
    INSERT INTO media (title, creator, releaseYear, coverImage) VALUES (p_title, p_creator, p_release_year, p_cover_image) RETURNING id INTO new_media_id;
    INSERT INTO book (mediaId) VALUES (new_media_id);
    RETURN new_media_id;
END;
$$ LANGUAGE plpgsql;

CREATE OR REPLACE FUNCTION fn_create_film(
    p_title VARCHAR(255), p_creator VARCHAR(255), p_release_year INT, p_cover_image VARCHAR(255)
) RETURNS INTEGER AS $$
DECLARE new_media_id INTEGER;
BEGIN
    INSERT INTO media (title, creator, releaseYear, coverImage) VALUES ( p_title, p_creator, p_release_year, p_cover_image) RETURNING id INTO new_media_id;
    INSERT INTO film (mediaId) VALUES (new_media_id);
    RETURN new_media_id;
END;
$$ LANGUAGE plpgsql;

-- US24 - Report Content
CREATE OR REPLACE FUNCTION fn_report_post(p_post_id INT, p_reason TEXT) RETURNS VOID AS $$ 
BEGIN
    SET TRANSACTION ISOLATION LEVEL READ COMMITTED;
    INSERT INTO report (reason, status, postId, createdAt)
    VALUES (p_reason, 'pending', p_post_id, CURRENT_TIMESTAMP);
END;
$$ LANGUAGE plpgsql;

-- US26 - Delete Account
CREATE OR REPLACE FUNCTION fn_delete_account(p_user_id INT) RETURNS VOID AS $$ 
BEGIN
    SET TRANSACTION ISOLATION LEVEL SERIALIZABLE;
    PERFORM fn_anonymize_user_data(p_user_id);
END;
$$ LANGUAGE plpgsql;

-- US28 - Send Friend Request
CREATE OR REPLACE FUNCTION fn_send_friend_request(p_sender_id INT, p_receiver_id INT) RETURNS VOID AS $$
DECLARE 
    notif_id INT;
BEGIN
    SET TRANSACTION ISOLATION LEVEL REPEATABLE READ;
    INSERT INTO notification (message, receiverId)
    VALUES (CONCAT('You received a friend request from ', (SELECT username FROM users WHERE id = p_sender_id)), p_receiver_id)
    RETURNING id INTO notif_id;
    INSERT INTO request (notificationId, status, senderId) VALUES (notif_id, 'pending', p_sender_id);
    INSERT INTO friend_request (requestId) VALUES (notif_id);
END;
$$ LANGUAGE plpgsql;

-- US29 - Unfriend
CREATE OR REPLACE FUNCTION fn_unfriend(p_user1_id INT, p_user2_id INT) RETURNS VOID AS $$ 
BEGIN
    SET TRANSACTION ISOLATION LEVEL READ COMMITTED;
    DELETE FROM friendship
    WHERE (userId1 = p_user1_id AND userId2 = p_user2_id) OR (userId1 = p_user2_id AND userId2 = p_user1_id);
END;
$$ LANGUAGE plpgsql;

-- US30 - Comment on Post
CREATE OR REPLACE FUNCTION fn_comment_on_post(p_post_id INT, p_user_id INT, p_content TEXT) RETURNS VOID AS $$ 
BEGIN
    SET TRANSACTION ISOLATION LEVEL READ COMMITTED;
    INSERT INTO comment (postId, userId, content) VALUES (p_post_id, p_user_id, p_content);
END;
$$ LANGUAGE plpgsql;

-- US31 - React to Post
CREATE OR REPLACE FUNCTION fn_react_to_post(p_post_id INT, p_user_id INT) RETURNS VOID AS $$ 
BEGIN
    SET TRANSACTION ISOLATION LEVEL REPEATABLE READ;
    INSERT INTO post_like (postId, userId) VALUES (p_post_id, p_user_id) ON CONFLICT DO NOTHING;
END;
$$ LANGUAGE plpgsql;

-- US32 - React to Comment
CREATE OR REPLACE FUNCTION fn_react_to_comment(p_comment_id INT, p_user_id INT) RETURNS VOID AS $$ 
BEGIN
    SET TRANSACTION ISOLATION LEVEL REPEATABLE READ;
    INSERT INTO comment_like (commentId, userId) VALUES (p_comment_id, p_user_id) ON CONFLICT DO NOTHING;
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
    INSERT INTO notification (message, receiverId)
    VALUES (CONCAT('You have been invited to join ', (SELECT name FROM groups WHERE id = p_group_id)), p_receiver_id)
    RETURNING id INTO notif_id;
    INSERT INTO request (notificationId, status, senderId) VALUES (notif_id, 'pending', p_sender_id);
    INSERT INTO group_invite_request (requestId, groupId) VALUES (notif_id, p_group_id);
END;
$$ LANGUAGE plpgsql;

-- US51 - Manage Reported Content
CREATE OR REPLACE FUNCTION fn_manage_report(p_report_id INT, p_new_status TEXT) RETURNS VOID AS $$
DECLARE 
    post_target INT;
BEGIN
    SET TRANSACTION ISOLATION LEVEL SERIALIZABLE; 
    UPDATE report SET status = p_new_status WHERE id = p_report_id;
    SELECT postId INTO post_target FROM report WHERE id = p_report_id;
    IF p_new_status = 'accepted' THEN
        DELETE FROM post WHERE id = post_target;
    END IF;
END;
$$ LANGUAGE plpgsql;

-- US53 - Block/Unblock User
CREATE OR REPLACE FUNCTION fn_toggle_block_user(p_user_id INT, p_is_blocked BOOLEAN) RETURNS VOID AS $$ 
BEGIN
    SET TRANSACTION ISOLATION LEVEL READ COMMITTED; 
    UPDATE users SET isBlocked = p_is_blocked WHERE id = p_user_id;
END;
$$ LANGUAGE plpgsql;

-- US54 - Admin Delete User Account
CREATE OR REPLACE FUNCTION fn_admin_delete_user(p_user_id INT) RETURNS VOID AS $$ 
BEGIN
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
    report,
    unblock_appeal
RESTART IDENTITY CASCADE;

-- MEDIA
INSERT INTO media (title, creator, releaseYear, coverImage) VALUES 
    ('Inception', 'Christopher Nolan', 2010, 'https://image.tmdb.org/t/p/w300/xlaY2zyzMfkhk0HSC5VUwzoZPU1.jpg'),
    ('The Great Gatsby', 'F. Scott Fitzgerald', 2003, 'http://books.google.com/books/content?id=iXn5U2IzVH0C&printsec=frontcover&img=1&zoom=1&edge=curl&source=gbs_api'),
    ('Get Lucky', 'Daft Punk', 2013, 'https://i.scdn.co/image/ab67616d0000b2739b9b36b0e22870b9f542d937'),
    ('The Matrix', 'Lana Wachowski', 1999, 'https://image.tmdb.org/t/p/w300/p96dm7sCMn4VYAStA6siNz30G1r.jpg'),
    ('To Kill a Mockingbird', 'Harper Lee', 1960, 'http://books.google.com/books/content?id=DRagKAMw8AcC&printsec=frontcover&img=1&zoom=1&source=gbs_api'),
    ('Interstellar', 'Christopher Nolan', 2014, 'https://image.tmdb.org/t/p/w300/gEU2QniE6E77NI6lCU6MxlNBvIx.jpg'),
    ('Strawberry Fields Forever - Remastered 2009', 'The Beatles', 1967, 'https://i.scdn.co/image/ab67616d0000b273692d9189b2bd75525893f0c1'),
    ('1984', 'George Orwell', 2021, 'http://books.google.com/books/content?id=5AwIEAAAQBAJ&printsec=frontcover&img=1&zoom=1&edge=curl&source=gbs_api'),
    ('The Shawshank Redemption', 'Frank Darabont', 1994, 'https://image.tmdb.org/t/p/w300/q6y0Go1tsGEsmtFryDOJo3dEmqu.jpg'),
    ('Pulp Fiction', 'Quentin Tarantino', 1994, 'https://image.tmdb.org/t/p/w300/d5iIlFn5s0ImszYzBPb8JPIfbXD.jpg'),
    ('The Dark Knight', 'Christopher Nolan', 2008, 'https://image.tmdb.org/t/p/w300/qJ2tW6WMUDux911r6m7haRef0WH.jpg'),
    ('Fight Club', 'David Fincher', 1999, 'https://image.tmdb.org/t/p/w300/pB8BM7pdSp6B6Ih7QZ4DrQ3PmJK.jpg'),
    ('Forrest Gump', 'Robert Zemeckis', 1994, 'https://image.tmdb.org/t/p/w300/clolk7rB5lAjs41SD0Vt6IXYLMm.jpg'),
    ('The Lord of the Rings: The Return of the King', 'Peter Jackson', 2003, 'https://image.tmdb.org/t/p/w300/rCzpDGLbOoPwLjy3OAm5NUPOTrC.jpg'),
    ('Parasite', 'Bong Joon-ho', 2019, 'https://image.tmdb.org/t/p/w300/7IiTTgloJzvGI1TAYymCfbfl3vT.jpg'),
    ('Spirited Away', 'Hayao Miyazaki', 2001, 'https://image.tmdb.org/t/p/w300/39wmItIWsg5sZMyRUHLkWBcuVCM.jpg'),
    ('Harry Potter and the Philosopher''s Stone', 'J.K. Rowling', 1997, 'http://books.google.com/books/content?id=wrOQLV6xB-wC&printsec=frontcover&img=1&zoom=1&source=gbs_api'),
    ('The Hobbit', 'J.R.R. Tolkien', 1937, 'http://books.google.com/books/content?id=hFfhrCWiLSMC&printsec=frontcover&img=1&zoom=1&source=gbs_api'),
    ('Pride and Prejudice', 'Jane Austen', 1813, 'http://books.google.com/books/content?id=s1gVAAAAYAAJ&printsec=frontcover&img=1&zoom=1&source=gbs_api'),
    ('The Catcher in the Rye', 'J.D. Salinger', 1951, 'http://books.google.com/books/content?id=PCDengEACAAJ&printsec=frontcover&img=1&zoom=1&source=gbs_api'),
    ('Dune', 'Frank Herbert', 1965, 'http://books.google.com/books/content?id=B1hSG45JCX4C&printsec=frontcover&img=1&zoom=1&source=gbs_api'),
    ('The Alchemist', 'Paulo Coelho', 1988, 'http://books.google.com/books/content?id=FzVjBgAAQBAJ&printsec=frontcover&img=1&zoom=1&source=gbs_api'),
    ('Sapiens', 'Yuval Noah Harari', 2011, 'http://books.google.com/books/content?id=1EiJAwAAQBAJ&printsec=frontcover&img=1&zoom=1&source=gbs_api'),
    ('Educated', 'Tara Westover', 2018, 'http://books.google.com/books/content?id=2ObWDgAAQBAJ&printsec=frontcover&img=1&zoom=1&source=gbs_api'),
    ('The Midnight Library', 'Matt Haig', 2020, 'http://books.google.com/books/content?id=CvYzEAAAQBAJ&printsec=frontcover&img=1&zoom=1&source=gbs_api'),
    ('Bohemian Rhapsody', 'Queen', 1975, 'https://i.scdn.co/image/ab67616d0000b273ce4f1737bc8a646c8c4bd25a'),
    ('Hotel California', 'Eagles', 1976, 'https://i.scdn.co/image/ab67616d0000b2734637341b9f507521afa9a778'),
    ('Smells Like Teen Spirit', 'Nirvana', 1991, 'https://i.scdn.co/image/ab67616d0000b273e175a19e530c898d167d39bf'),
    ('Wonderwall', 'Oasis', 1995, 'https://i.scdn.co/image/ab67616d0000b273b366d7cf48e05c4c0e5d8f65'),
    ('Billie Jean', 'Michael Jackson', 1982, 'https://i.scdn.co/image/ab67616d0000b27382b47b2b4c1d6c95b52b8ad7'),
    ('Imagine', 'John Lennon', 1971, 'https://i.scdn.co/image/ab67616d0000b2739e447b59bd3e2cbefaa31d91'),
    ('Sweet Child O'' Mine', 'Guns N'' Roses', 1987, 'https://i.scdn.co/image/ab67616d0000b273b3c0e0973db8f325c4732d79'),
    ('Stairway to Heaven', 'Led Zeppelin', 1971, 'https://i.scdn.co/image/ab67616d0000b2739c0084d10a0e2ea1fd5e3e5e'),
    ('Blinding Lights', 'The Weeknd', 2019, 'https://i.scdn.co/image/ab67616d0000b2738863bc11d2aa12b54f5aeb36'),
    ('Shape of You', 'Ed Sheeran', 2017, 'https://i.scdn.co/image/ab67616d0000b273ba5db46f4b838ef6027e6f96');

INSERT INTO film (mediaId) VALUES (1), (4), (6), (9), (10), (11), (12), (13), (14), (15), (16);
INSERT INTO book (mediaId) VALUES (2), (5), (8), (17), (18), (19), (20), (21), (22), (23), (24), (25);
INSERT INTO music (mediaId) VALUES (3), (7), (26), (27), (28), (29), (30), (31), (32), (33), (34), (35);

-- USERS
INSERT INTO users (name, username, email, passwordHash, bio, profilePicture, isPrivate, isAdmin, favoriteFilm, favoriteBook, favoriteSong) VALUES 
    ('Admin', 'admin', 'admin@example.org', '$2y$10$HfzIhGCCaxqyaIdGgjARSuOKAcm1Uy82YfLuNaajn6JrjLWy9Sj/W', NULL, NULL, FALSE, TRUE, NULL, NULL, NULL),
    ('Alice Martins', 'alice', 'alice@example.org', '$2y$10$HfzIhGCCaxqyaIdGgjARSuOKAcm1Uy82YfLuNaajn6JrjLWy9Sj/W', 'Movie lover and aspiring filmmaker 🎬', 'alice.jpg', FALSE, FALSE, 1, 2, 3),
    ('Bruno Silva', 'bruno', 'bruno@example.org', '$2y$10$HfzIhGCCaxqyaIdGgjARSuOKAcm1Uy82YfLuNaajn6JrjLWy9Sj/W', 'Reader & gamer. Tech enthusiast 💻', 'bruno.jpg', FALSE, FALSE, 4, 5, 3),
    ('Carla Dias', 'carla', 'carla@example.org', '$2y$10$HfzIhGCCaxqyaIdGgjARSuOKAcm1Uy82YfLuNaajn6JrjLWy9Sj/W', 'Music addict 🎵 Concerts are life!', 'carla.jpg', TRUE, FALSE, 1, 2, 7),
    ('David Costa', 'david', 'david@example.org', '$2y$10$HfzIhGCCaxqyaIdGgjARSuOKAcm1Uy82YfLuNaajn6JrjLWy9Sj/W', 'Cinephile. Nolan fanboy.', 'david.jpg', FALSE, FALSE, 6, NULL, NULL),
    ('Eva Rocha', 'eva', 'eva@example.org', '$2y$10$HfzIhGCCaxqyaIdGgjARSuOKAcm1Uy82YfLuNaajn6JrjLWy9Sj/W', 'Book enthusiast 📚 Coffee lover ☕', 'eva.jpg', TRUE, FALSE, 4, 8, 7),
    ('Filipe Moreira', 'filipe', 'filipe@example.org', '$2y$10$HfzIhGCCaxqyaIdGgjARSuOKAcm1Uy82YfLuNaajn6JrjLWy9Sj/W', 'Vinyl collector. Old school music only 🎸', 'filipe.jpg', FALSE, FALSE, 1, NULL, 3),
    ('John Doe', 'john_doe', 'john@example.org', '$2y$10$HfzIhGCCaxqyaIdGgjARSuOKAcm1Uy82YfLuNaajn6JrjLWy9Sj/W', 'Private person', 'john.jpg', TRUE, FALSE, 1, 2, 3),
    ('Jane Smith', 'jane_doe', 'jane@example.org', '$2y$10$HfzIhGCCaxqyaIdGgjARSuOKAcm1Uy82YfLuNaajn6JrjLWy9Sj/W', 'Keep it secret', 'jane.jpg', TRUE, FALSE, 4, 5, 7),
    ('Miguel Santos', 'miguelito', 'miguel@example.org', '$2y$10$HfzIhGCCaxqyaIdGgjARSuOKAcm1Uy82YfLuNaajn6JrjLWy9Sj/W', 'Part-time photographer 📸 Full-time dreamer', 'miguel.jpg', FALSE, FALSE, 9, 17, 26),
    ('Sofia Almeida', 'sofia_a', 'sofia@example.org', '$2y$10$HfzIhGCCaxqyaIdGgjARSuOKAcm1Uy82YfLuNaajn6JrjLWy9Sj/W', 'Travel blogger ✈️ Always exploring', 'sofia.jpg', FALSE, FALSE, 16, 22, 34),
    ('Ricardo Pereira', 'ricky', 'ricardo@example.org', '$2y$10$HfzIhGCCaxqyaIdGgjARSuOKAcm1Uy82YfLuNaajn6JrjLWy9Sj/W', 'Gym rat 💪 Science fiction nerd', 'ricardo.jpg', FALSE, FALSE, 4, 21, 28),
    ('Ana Ferreira', 'ana_f', 'ana@example.org', '$2y$10$HfzIhGCCaxqyaIdGgjARSuOKAcm1Uy82YfLuNaajn6JrjLWy9Sj/W', 'Art student 🎨 Indie music lover', 'ana.jpg', FALSE, FALSE, 10, 19, 29),
    ('Pedro Oliveira', 'pedro_o', 'pedro@example.org', '$2y$10$HfzIhGCCaxqyaIdGgjARSuOKAcm1Uy82YfLuNaajn6JrjLWy9Sj/W', 'Software engineer by day, musician by night 🎹', 'pedro.jpg', FALSE, FALSE, 11, 23, 32),
    ('Inês Costa', 'ines_c', 'ines@example.org', '$2y$10$HfzIhGCCaxqyaIdGgjARSuOKAcm1Uy82YfLuNaajn6JrjLWy9Sj/W', 'Psychology student 🧠 Love discussing films', 'ines.jpg', TRUE, FALSE, 12, 24, 30),
    ('Tiago Ribeiro', 'tiago_r', 'tiago@example.org', '$2y$10$HfzIhGCCaxqyaIdGgjARSuOKAcm1Uy82YfLuNaajn6JrjLWy9Sj/W', 'Foodie 🍕 Podcast enthusiast', 'tiago.jpg', FALSE, FALSE, 13, NULL, 31),
    ('Mariana Sousa', 'mari_s', 'mariana@example.org', '$2y$10$HfzIhGCCaxqyaIdGgjARSuOKAcm1Uy82YfLuNaajn6JrjLWy9Sj/W', 'Dancing through life 💃 Musical theatre fan', 'mariana.jpg', FALSE, FALSE, 14, 25, 27),
    ('João Rodrigues', 'joao_rod', 'joao@example.org', '$2y$10$HfzIhGCCaxqyaIdGgjARSuOKAcm1Uy82YfLuNaajn6JrjLWy9Sj/W', 'History buff 📜 Documentary lover', 'joao.jpg', FALSE, FALSE, 15, 20, NULL),
    ('Beatriz Lima', 'bia_lima', 'beatriz@example.org', '$2y$10$HfzIhGCCaxqyaIdGgjARSuOKAcm1Uy82YfLuNaajn6JrjLWy9Sj/W', 'Cat mom 🐱 Fantasy books enthusiast', 'beatriz.jpg', TRUE, FALSE, 16, 18, 33),
    ('Gonçalo Martins', 'goncalo_m', 'goncalo@example.org', '$2y$10$HfzIhGCCaxqyaIdGgjARSuOKAcm1Uy82YfLuNaajn6JrjLWy9Sj/W', 'Startup founder 🚀 Productivity nerd', 'goncalo.jpg', FALSE, FALSE, 1, 23, 35),
    ('Catarina Neves', 'cat_neves', 'catarina@example.org', '$2y$10$HfzIhGCCaxqyaIdGgjARSuOKAcm1Uy82YfLuNaajn6JrjLWy9Sj/W', 'Medical student ⚕️ Anime watcher', 'catarina.jpg', FALSE, FALSE, 16, 8, 7);

-- GROUPS
INSERT INTO groups (name, description, isPrivate, icon) VALUES 
    ('Film Buffs', 'Discuss your favorite movies', FALSE, 'film-buffs.jpg'),
    ('Bookworms', 'Share and review your favorite books', TRUE, 'bookworms.jpg'),
    ('Music Lovers', 'Everything about records and concerts', FALSE, 'music-lovers.jpg'),
    ('Tech Talks', 'Software engineering and technology discussions', FALSE, 'tech-talks.jpg'),
    ('Fitness Freaks', 'Workout tips, motivation, and health', FALSE, 'fitness.jpg'),
    ('Art & Design', 'Share your creative work and get feedback', FALSE, 'art-design.jpg'),
    ('Travel Buddies', 'Travel stories, tips, and meetups', FALSE, 'travel.jpg'),
    ('Foodies Unite', 'Restaurant recommendations and recipes', FALSE, 'foodies.jpg'),
    ('Study Group', 'Academic support and productivity tips', TRUE, 'study.jpg'),
    ('Photography Club', 'Share photos and photography techniques', FALSE, 'photography.jpg');

-- MEMBERSHIP
INSERT INTO membership (userId, groupId, isOwner) VALUES 
    (1, 1, TRUE), (2, 1, FALSE), (3, 3, TRUE), (4, 1, FALSE), (5, 2, TRUE), 
    (6, 3, FALSE), (9, 1, FALSE), (9, 10, TRUE), (10, 7, TRUE), (10, 8, FALSE), 
    (11, 5, TRUE), (11, 4, FALSE), (12, 6, TRUE), (12, 3, FALSE), (13, 4, TRUE), 
    (13, 5, FALSE), (14, 1, FALSE), (14, 9, TRUE), (15, 8, TRUE), (15, 1, FALSE), 
    (16, 3, FALSE), (16, 7, FALSE), (17, 2, FALSE), (17, 1, FALSE), (18, 2, FALSE), 
    (18, 6, FALSE), (19, 4, FALSE), (19, 9, FALSE), (20, 5, FALSE), (20, 9, FALSE);

-- ====================================================
-- MASSIVE POST & REVIEW INSERT
-- ====================================================
-- Strategy:
-- IDs 1-30: GROUP POSTS (assigned to a groupId) -> Must have "GROUP POST:" prefix
-- IDs 31-60: PROFILE POSTS (groupId IS NULL) -> Must NOT have prefix
-- ====================================================

INSERT INTO post (userId, groupId, createdAt) VALUES 
    -- [GROUP POSTS 1-10]
    (2, 1, NOW() - INTERVAL '30 days'), (5, 2, NOW() - INTERVAL '29 days'), (4, 3, NOW() - INTERVAL '28 days'), 
    (11, 5, NOW() - INTERVAL '27 days'), (13, 4, NOW() - INTERVAL '26 days'), (16, 7, NOW() - INTERVAL '25 days'), 
    (9, 10, NOW() - INTERVAL '24 days'), (12, 6, NOW() - INTERVAL '23 days'), (15, 8, NOW() - INTERVAL '22 days'), 
    (19, 9, NOW() - INTERVAL '21 days'),
    -- [GROUP POSTS 11-20]
    (2, 1, NOW() - INTERVAL '20 days'), (5, 2, NOW() - INTERVAL '19 days'), (4, 3, NOW() - INTERVAL '18 days'), 
    (11, 5, NOW() - INTERVAL '17 days'), (13, 4, NOW() - INTERVAL '16 days'), (16, 7, NOW() - INTERVAL '15 days'), 
    (9, 10, NOW() - INTERVAL '14 days'), (12, 6, NOW() - INTERVAL '13 days'), (15, 8, NOW() - INTERVAL '12 days'), 
    (19, 9, NOW() - INTERVAL '11 days'),
    -- [GROUP POSTS 21-30]
    (1, 1, NOW() - INTERVAL '10 days'), (6, 2, NOW() - INTERVAL '9 days'), (7, 3, NOW() - INTERVAL '8 days'), 
    (20, 5, NOW() - INTERVAL '7 days'), (11, 4, NOW() - INTERVAL '6 days'), (10, 7, NOW() - INTERVAL '5 days'), 
    (14, 10, NOW() - INTERVAL '4 days'), (18, 6, NOW() - INTERVAL '3 days'), (10, 8, NOW() - INTERVAL '2 days'), 
    (14, 9, NOW() - INTERVAL '1 day'),

    -- [NORMAL PROFILE POSTS 31-40]
    (2, NULL, NOW() - INTERVAL '30 days'), (3, NULL, NOW() - INTERVAL '29 days'), (4, NULL, NOW() - INTERVAL '28 days'), 
    (5, NULL, NOW() - INTERVAL '27 days'), (6, NULL, NOW() - INTERVAL '26 days'), (7, NULL, NOW() - INTERVAL '25 days'), 
    (8, NULL, NOW() - INTERVAL '24 days'), (9, NULL, NOW() - INTERVAL '23 days'), (10, NULL, NOW() - INTERVAL '22 days'), 
    (11, NULL, NOW() - INTERVAL '21 days'),
    -- [NORMAL PROFILE POSTS 41-50]
    (12, NULL, NOW() - INTERVAL '20 days'), (13, NULL, NOW() - INTERVAL '19 days'), (14, NULL, NOW() - INTERVAL '18 days'), 
    (15, NULL, NOW() - INTERVAL '17 days'), (16, NULL, NOW() - INTERVAL '16 days'), (17, NULL, NOW() - INTERVAL '15 days'), 
    (18, NULL, NOW() - INTERVAL '14 days'), (19, NULL, NOW() - INTERVAL '13 days'), (20, NULL, NOW() - INTERVAL '12 days'), 
    (2, NULL, NOW() - INTERVAL '11 days'),
    -- [NORMAL PROFILE POSTS 51-60]
    (3, NULL, NOW() - INTERVAL '10 days'), (4, NULL, NOW() - INTERVAL '9 days'), (5, NULL, NOW() - INTERVAL '8 days'), 
    (6, NULL, NOW() - INTERVAL '7 days'), (7, NULL, NOW() - INTERVAL '6 days'), (8, NULL, NOW() - INTERVAL '5 days'), 
    (9, NULL, NOW() - INTERVAL '4 days'), (10, NULL, NOW() - INTERVAL '3 days'), (11, NULL, NOW() - INTERVAL '2 days'), 
    (12, NULL, NOW() - INTERVAL '1 day');

INSERT INTO standard_post (postId, text, imageUrl) VALUES 
    -- GROUP POSTS (Must have "GROUP POST:")
    (1, 'GROUP POST: Who else is excited for the new Dune movie? 🎥', 'inception-post.jpg'),
    (4, 'GROUP POST: Leg day today. Pray for me. 🏋️', 'gym.jpg'),
    (5, 'GROUP POST: Python 3.12 features are actually looking pretty good.', NULL),
    (6, 'GROUP POST: Planning a group trip to Bali next summer! ✈️', 'beach.jpg'),
    (7, 'GROUP POST: Look at the lighting in this shot I took yesterday! 📸', 'camera.jpg'),
    (8, 'GROUP POST: Working on a new oil painting. Thoughts? 🎨', 'painting.jpg'),
    (9, 'GROUP POST: Found the best taco place downtown! 🌮', 'tacos.jpg'),
    (10, 'GROUP POST: Anyone up for a late night study session on Discord?', NULL),
    (11, 'GROUP POST: Top 5 underrated directors. Go!', NULL),
    (14, 'GROUP POST: Remember: Consistency > Intensity. 💪', NULL),
    (15, 'GROUP POST: Anyone here used Rust for web dev yet?', NULL),
    (16, 'GROUP POST: Missing the mountains today. 🏔️', NULL),
    (17, 'GROUP POST: Need feedback on this portrait edit.', 'portrait.jpg'),
    (18, 'GROUP POST: Abstract art is harder than it looks.', NULL),
    (19, 'GROUP POST: Homemade pasta attempt #1. 🍝', 'pasta.jpg'),
    (20, 'GROUP POST: Tip: Pomodoro technique saved my grades.', NULL),
    (21, 'GROUP POST: Movie night this Friday? 🍿', NULL),
    (24, 'GROUP POST: New PR on bench press! 100kg! 😤', NULL),
    (25, 'GROUP POST: My code works but I have no idea why.', 'code-meme.jpg'),
    (26, 'GROUP POST: Travel checklist for Japan. Help needed!', NULL),
    (27, 'GROUP POST: Golden hour was perfect today. ☀️', 'sunset.jpg'),
    (28, 'GROUP POST: Digital art sketch dump.', 'sketch.jpg'),
    (29, 'GROUP POST: Best coffee shops for working?', 'coffee.jpg'),
    (30, 'GROUP POST: Exam season is approaching. We got this! 📚', NULL),

    -- NORMAL POSTS (No Prefix)
    (31, 'Just adopted a cat! Meet Luna 🐱', 'cat.jpg'),
    (32, 'Why is Monday so far from Friday but Friday so close to Monday?', NULL),
    (34, 'Finally finished my degree! 🎓', 'graduation.jpg'),
    (35, 'Coffee is the only thing keeping me alive right now.', 'coffee-cup.jpg'),
    (36, 'Beautiful sunset today.', 'sunset-view.jpg'),
    (38, 'Sometimes you just need to disconnect.', NULL),
    (39, 'Anyone know a good mechanic?', NULL),
    (40, 'Just moved into my new apartment!', 'apartment.jpg'),
    (41, 'Cooking dinner for friends. Wish me luck.', NULL),
    (42, 'The traffic today was absolute insanity.', NULL),
    (44, 'Can''t believe it''s already December.', NULL),
    (45, 'My dog ate my homework. Literally.', 'dog-shame.jpg'),
    (46, 'Going to a concert tonight! So excited!', NULL),
    (48, 'Started learning Spanish today. Hola!', NULL),
    (49, 'Rainy days are for reading.', 'rainy-window.jpg'),
    (50, 'Just got a promotion at work!', NULL),
    (51, 'Is it too early for Christmas music?', NULL),
    (52, 'Pizza is always the answer.', 'pizza-slice.jpg'),
    (54, 'Gym was empty today. Pure bliss.', NULL),
    (55, 'Thinking about dyeing my hair blue.', NULL),
    (56, 'Watching old cartoons and feeling nostalgic.', NULL),
    (58, 'Cleaned my room. Found things from 2010.', NULL),
    (59, 'Hiking trip this weekend was amazing.', 'hiking.jpg'),
    (60, 'Trying to bake bread. It looks like a rock.', 'bread-fail.jpg');

INSERT INTO review (postId, rating, mediaId, content) VALUES 
    -- GROUP REVIEWS (Must have "GROUP POST:")
    (2, 5, 2, 'GROUP POST: The Great Gatsby is a tragedy about the American Dream. Beautifully written.'),
    (3, 4, 3, 'GROUP POST: Get Lucky is catchy, but the rest of the album is better.'),
    (12, 5, 17, 'GROUP POST: Harry Potter defined my childhood. 10/10.'),
    (13, 3, 29, 'GROUP POST: Wonderwall is overplayed but still a classic.'),
    (22, 4, 8, 'GROUP POST: 1984 is terrifyingly accurate. A must read.'),
    (23, 5, 26, 'GROUP POST: Bohemian Rhapsody is the greatest song ever written.'),

    -- NORMAL REVIEWS (No Prefix)
    (33, 5, 1, 'Inception blew my mind. Nolan is a genius.'),
    (37, 2, 35, 'Shape of You is way too repetitive for me.'),
    (43, 4, 11, 'The Dark Knight is the best superhero movie. Period.'),
    (47, 5, 16, 'Spirited Away is pure magic. I cried.'),
    (53, 3, 21, 'Dune is great but the pacing is a bit slow.'),
    (57, 5, 6, 'Interstellar soundtrack is a masterpiece.');

-- POST INTERACTIONS
INSERT INTO post_like (postId, userId) VALUES 
    (1, 2), (1, 3), (1, 4), (31, 2), (31, 5), (31, 9), (2, 5), (2, 6), (33, 1), (33, 4),
    (3, 5), (3, 12), (3, 15), (35, 12), (35, 18), (35, 19), (4, 11), (4, 13), (4, 20),
    (36, 1), (36, 10), (36, 14), (5, 13), (5, 14), (6, 10), (6, 16), (40, 3), (40, 8),
    (40, 15), (7, 9), (7, 10), (7, 12), (43, 2), (43, 5), (43, 11), (8, 12), (8, 17),
    (45, 6), (45, 18), (45, 20), (9, 11), (9, 15), (10, 19), (10, 14), (49, 2), (49, 5),
    (11, 1), (11, 2), (11, 5), (50, 4), (50, 9), (50, 16), (12, 5), (12, 17), (12, 19);

INSERT INTO post_tag (postId, userId) VALUES 
    (1, 3), (1, 4), (31, 4), (31, 5), (6, 10), (6, 12), (36, 9), (36, 16), (26, 18), (45, 12);

-- COMMENTS
INSERT INTO comment (postId, userId, content, createdAt) VALUES 
    (1, 2, 'Totally agree! It is a masterpiece.', NOW() - INTERVAL '9 days'),
    (1, 3, 'Love that movie too.', NOW() - INTERVAL '8 days'),
    (31, 1, 'So cute!!', NOW() - INTERVAL '29 days'),
    (31, 4, 'What a lovely cat.', NOW() - INTERVAL '29 days'),
    (2, 6, 'Sad ending though.', NOW() - INTERVAL '19 days'),
    (33, 5, 'The spinning top fell!', NOW() - INTERVAL '20 days');

INSERT INTO comment_like (commentId, userId) VALUES (1, 1), (2, 1), (3, 2), (4, 1);

-- FRIENDSHIPS
INSERT INTO friendship (userId1, userId2) VALUES 
    (1, 2), (1, 3), (1, 9), (1, 10), (2, 3), (2, 4), (2, 5), (2, 11), (3, 5), (3, 12), (3, 15),
    (4, 6), (4, 11), (4, 13), (5, 17), (5, 18), (6, 15), (6, 17), (9, 10), (9, 12), (9, 16),
    (9, 18), (10, 11), (10, 16), (10, 19), (11, 13), (11, 19), (12, 14), (12, 18), (13, 14);

-- REQUESTS & REPORTS
INSERT INTO request (notificationId, status, senderId) VALUES (1, 'accepted', 1);
INSERT INTO group_invite_request (requestId, groupId) VALUES (1, 1);

-- REPORTS (Posts and Comments)
INSERT INTO report (reason, status, postId, commentId, createdAt) VALUES 
    -- Pending Reports on Posts
    ('Spam', 'pending', 37, NULL, NOW() - INTERVAL '1 day'),
    ('Inappropriate content', 'pending', 42, NULL, NOW() - INTERVAL '2 days'),
    ('Harassment', 'pending', 45, NULL, NOW() - INTERVAL '3 days'),
    ('Misinformation', 'pending', 32, NULL, NOW() - INTERVAL '4 days'),
    ('Spam', 'pending', 51, NULL, NOW() - INTERVAL '5 days'),
    ('Hate speech', 'pending', 44, NULL, NOW() - INTERVAL '6 days'),
    ('Violence', 'pending', 38, NULL, NOW() - INTERVAL '7 days'),
    ('Inappropriate content', 'pending', 55, NULL, NOW() - INTERVAL '8 days'),
    ('Spam', 'pending', 48, NULL, NOW() - INTERVAL '9 days'),
    ('Copyright violation', 'pending', 56, NULL, NOW() - INTERVAL '10 days'),
    
    -- Accepted Reports on Posts
    ('Spam', 'accepted', 52, NULL, NOW() - INTERVAL '11 days'),
    ('Harassment', 'accepted', 46, NULL, NOW() - INTERVAL '12 days'),
    ('Inappropriate content', 'accepted', 58, NULL, NOW() - INTERVAL '13 days'),
    ('Hate speech', 'accepted', 39, NULL, NOW() - INTERVAL '14 days'),
    ('Misinformation', 'accepted', 41, NULL, NOW() - INTERVAL '15 days'),
    ('Spam', 'accepted', 54, NULL, NOW() - INTERVAL '16 days'),
    ('Violence', 'accepted', 34, NULL, NOW() - INTERVAL '17 days'),
    ('Inappropriate content', 'accepted', 50, NULL, NOW() - INTERVAL '18 days'),
    
    -- Rejected Reports on Posts
    ('Spam', 'rejected', 1, NULL, NOW() - INTERVAL '19 days'),
    ('Inappropriate content', 'rejected', 31, NULL, NOW() - INTERVAL '20 days'),
    ('Harassment', 'rejected', 33, NULL, NOW() - INTERVAL '21 days'),
    ('Spam', 'rejected', 35, NULL, NOW() - INTERVAL '22 days'),
    ('Misinformation', 'rejected', 36, NULL, NOW() - INTERVAL '23 days'),
    ('Hate speech', 'rejected', 40, NULL, NOW() - INTERVAL '24 days'),
    ('Inappropriate content', 'rejected', 43, NULL, NOW() - INTERVAL '25 days'),
    ('Spam', 'rejected', 47, NULL, NOW() - INTERVAL '26 days'),
    ('Violence', 'rejected', 49, NULL, NOW() - INTERVAL '27 days'),
    ('Copyright violation', 'rejected', 53, NULL, NOW() - INTERVAL '28 days'),
    
    -- Pending Reports on Comments
    ('Spam', 'pending', NULL, 1, NOW() - INTERVAL '2 days'),
    ('Harassment', 'pending', NULL, 2, NOW() - INTERVAL '3 days'),
    ('Inappropriate content', 'pending', NULL, 3, NOW() - INTERVAL '5 days'),
    ('Hate speech', 'pending', NULL, 4, NOW() - INTERVAL '7 days'),
    ('Spam', 'pending', NULL, 5, NOW() - INTERVAL '9 days'),
    
    -- Accepted Reports on Comments
    ('Harassment', 'accepted', NULL, 6, NOW() - INTERVAL '11 days'),
    ('Inappropriate content', 'accepted', NULL, 1, NOW() - INTERVAL '13 days'),
    ('Spam', 'accepted', NULL, 2, NOW() - INTERVAL '15 days'),
    
    -- Rejected Reports on Comments
    ('Spam', 'rejected', NULL, 3, NOW() - INTERVAL '17 days'),
    ('Harassment', 'rejected', NULL, 4, NOW() - INTERVAL '19 days'),
    ('Inappropriate content', 'rejected', NULL, 5, NOW() - INTERVAL '21 days'),
    
    -- More varied reports
    ('Self-harm content', 'pending', 59, NULL, NOW() - INTERVAL '1 hour'),
    ('Scam or fraud', 'pending', 60, NULL, NOW() - INTERVAL '3 hours'),
    ('Impersonation', 'pending', 11, NULL, NOW() - INTERVAL '5 hours'),
    ('False information', 'pending', 15, NULL, NOW() - INTERVAL '7 hours'),
    ('Bullying', 'pending', 19, NULL, NOW() - INTERVAL '9 hours'),
    ('Adult content', 'accepted', 21, NULL, NOW() - INTERVAL '30 days'),
    ('Graphic violence', 'accepted', 24, NULL, NOW() - INTERVAL '29 days'),
    ('Terrorism', 'accepted', 27, NULL, NOW() - INTERVAL '28 days'),
    ('Illegal activities', 'rejected', 29, NULL, NOW() - INTERVAL '27 days'),
    ('Off-topic spam', 'rejected', 12, NULL, NOW() - INTERVAL '26 days');