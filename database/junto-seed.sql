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
    passwordHash VARCHAR(255) NOT NULL,
    bio TEXT,
    profilePicture VARCHAR(255),
    isPrivate BOOLEAN DEFAULT FALSE,
    isAdmin BOOLEAN DEFAULT FALSE,
    isBlocked BOOLEAN DEFAULT FALSE,
    favoriteFilm INTEGER REFERENCES media(id),
    favoriteBook INTEGER REFERENCES media(id),
    favoriteSong INTEGER REFERENCES media(id),
    remember_token VARCHAR(100),
    createdAt TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
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
    groupId INTEGER REFERENCES groups(id) ON DELETE CASCADE, -- New Column: NULL = Profile Post, ID = Group Post
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

--
-- Indexes
--

-- IDX01: Post Timeline (Main Feed)
CREATE INDEX post_created_at_idx ON post USING btree (createdAt DESC);
CLUSTER post USING post_created_at_idx;

-- IDX02: User Profile Feed
CREATE INDEX post_user_created_at_idx ON post USING btree (userId, createdAt DESC);

-- IDX03: Group Feed (NEW - Added for group posts)
CREATE INDEX post_group_created_at_idx ON post USING btree (groupId, createdAt DESC) WHERE groupId IS NOT NULL;

-- IDX04: Post Comments
CREATE INDEX comment_post_created_at_idx ON comment USING btree (postId, createdAt ASC);

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
    report 
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

-- GROUPS (Moved Insert Up)
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

-- MEMBERSHIP (Moved Insert Up)
INSERT INTO membership (userId, groupId, isOwner) VALUES 
    (1, 1, TRUE),
    (2, 1, FALSE),
    (3, 3, TRUE),
    (4, 1, FALSE),
    (5, 2, TRUE),
    (6, 3, FALSE),
    (9, 1, FALSE),
    (9, 10, TRUE),
    (10, 7, TRUE),
    (10, 8, FALSE),
    (11, 5, TRUE),
    (11, 4, FALSE),
    (12, 6, TRUE),
    (12, 3, FALSE),
    (13, 4, TRUE),
    (13, 5, FALSE),
    (14, 1, FALSE),
    (14, 9, TRUE),
    (15, 8, TRUE),
    (15, 1, FALSE),
    (16, 3, FALSE),
    (16, 7, FALSE),
    (17, 2, FALSE),
    (17, 1, FALSE),
    (18, 2, FALSE),
    (18, 6, FALSE),
    (19, 4, FALSE),
    (19, 9, FALSE),
    (20, 5, FALSE),
    (20, 9, FALSE);

-- POSTS (Updated Insert with Group IDs)
INSERT INTO post (userId, groupId, createdAt) VALUES 
    (1, 1, CURRENT_TIMESTAMP - INTERVAL '45 days'),    -- Film Buffs
    (2, 2, CURRENT_TIMESTAMP - INTERVAL '42 days'),    -- Bookworms
    (3, 3, CURRENT_TIMESTAMP - INTERVAL '40 days'),    -- Music Lovers
    (4, 3, CURRENT_TIMESTAMP - INTERVAL '38 days'),    -- Music Lovers
    (5, 2, CURRENT_TIMESTAMP - INTERVAL '35 days'),    -- Bookworms
    (6, 2, CURRENT_TIMESTAMP - INTERVAL '33 days'),    -- Bookworms
    (7, 8, CURRENT_TIMESTAMP - INTERVAL '30 days'),    -- Foodies (Coffee)
    (8, NULL, CURRENT_TIMESTAMP - INTERVAL '28 days'), -- Profile
    (9, 10, CURRENT_TIMESTAMP - INTERVAL '25 days'),   -- Photography
    (10, NULL, CURRENT_TIMESTAMP - INTERVAL '23 days'), -- Profile
    (11, 5, CURRENT_TIMESTAMP - INTERVAL '21 days'),   -- Fitness
    (12, 6, CURRENT_TIMESTAMP - INTERVAL '19 days'),   -- Art
    (13, 4, CURRENT_TIMESTAMP - INTERVAL '17 days'),   -- Tech
    (14, NULL, CURRENT_TIMESTAMP - INTERVAL '15 days'), -- Profile
    (15, 8, CURRENT_TIMESTAMP - INTERVAL '14 days'),   -- Foodies
    (16, 7, CURRENT_TIMESTAMP - INTERVAL '13 days'),   -- Travel
    (17, 3, CURRENT_TIMESTAMP - INTERVAL '12 days'),   -- Music
    (18, NULL, CURRENT_TIMESTAMP - INTERVAL '11 days'), -- Profile
    (19, 9, CURRENT_TIMESTAMP - INTERVAL '10 days'),   -- Study
    (20, NULL, CURRENT_TIMESTAMP - INTERVAL '9 days'), -- Profile
    (1, NULL, CURRENT_TIMESTAMP - INTERVAL '8 days'),
    (2, NULL, CURRENT_TIMESTAMP - INTERVAL '7 days'),
    (3, NULL, CURRENT_TIMESTAMP - INTERVAL '6 days'),
    (4, NULL, CURRENT_TIMESTAMP - INTERVAL '5 days'),
    (5, NULL, CURRENT_TIMESTAMP - INTERVAL '5 days' - INTERVAL '12 hours'),
    (9, 7, CURRENT_TIMESTAMP - INTERVAL '4 days'),      -- Travel
    (10, 9, CURRENT_TIMESTAMP - INTERVAL '4 days' - INTERVAL '6 hours'), -- Study
    (11, 8, CURRENT_TIMESTAMP - INTERVAL '3 days'),     -- Foodies
    (12, NULL, CURRENT_TIMESTAMP - INTERVAL '3 days' - INTERVAL '8 hours'),
    (13, 5, CURRENT_TIMESTAMP - INTERVAL '2 days'),     -- Fitness
    (14, 10, CURRENT_TIMESTAMP - INTERVAL '2 days' - INTERVAL '10 hours'), -- Photo
    (15, NULL, CURRENT_TIMESTAMP - INTERVAL '2 days' - INTERVAL '4 hours'),
    (16, 4, CURRENT_TIMESTAMP - INTERVAL '1 day' - INTERVAL '18 hours'), -- Tech
    (17, 10, CURRENT_TIMESTAMP - INTERVAL '1 day' - INTERVAL '12 hours'), -- Photo
    (18, NULL, CURRENT_TIMESTAMP - INTERVAL '1 day' - INTERVAL '6 hours'),
    (19, NULL, CURRENT_TIMESTAMP - INTERVAL '1 day'),
    (20, NULL, CURRENT_TIMESTAMP - INTERVAL '20 hours'),
    (1, 10, CURRENT_TIMESTAMP - INTERVAL '16 hours'),   -- Photo
    (9, NULL, CURRENT_TIMESTAMP - INTERVAL '14 hours'),
    (10, NULL, CURRENT_TIMESTAMP - INTERVAL '12 hours'),
    (11, 1, CURRENT_TIMESTAMP - INTERVAL '10 hours'),   -- Film
    (12, 3, CURRENT_TIMESTAMP - INTERVAL '8 hours'),    -- Music
    (13, 1, CURRENT_TIMESTAMP - INTERVAL '6 hours'),    -- Film
    (14, 1, CURRENT_TIMESTAMP - INTERVAL '5 hours'),    -- Film
    (15, 3, CURRENT_TIMESTAMP - INTERVAL '4 hours'),    -- Music
    (16, 1, CURRENT_TIMESTAMP - INTERVAL '3 hours'),    -- Film
    (17, 2, CURRENT_TIMESTAMP - INTERVAL '2 hours'),    -- Book
    (18, 1, CURRENT_TIMESTAMP - INTERVAL '90 minutes'), -- Film
    (19, 2, CURRENT_TIMESTAMP - INTERVAL '45 minutes'), -- Book
    (20, 3, CURRENT_TIMESTAMP - INTERVAL '15 minutes'); -- Music

INSERT INTO standard_post (postId, text, imageUrl) VALUES 
    (1, 'GROUP POST: Just watched Inception again. Still brilliant.', 'inception-post.jpg'),
    (2, 'GROUP POST: Reading The Great Gatsby this weekend.', NULL),
    (5, 'GROUP POST: Finally finished 1984. Heavy stuff.', '1984-review.jpg'),
    (7, 'GROUP POST: Had the best cappuccino today at that new café downtown ☕', 'coffee.jpg'),
    (9, 'GROUP POST: Sunset from my balcony today was absolutely stunning 🌅', 'sunset.jpg'),
    (11, 'GROUP POST: Legs day at the gym = can''t walk properly tomorrow 😅', 'gym.jpg'),
    (12, 'GROUP POST: Working on a new painting. Abstract art is harder than it looks!', 'painting.jpg'),
    (13, 'GROUP POST: Debug session lasted 4 hours. The bug was a missing semicolon. I hate everything.', NULL),
    (15, 'GROUP POST: Best pizza I''ve ever had! Why did no one tell me about this place? 🍕', 'pizza.jpg'),
    (16, 'GROUP POST: Just booked tickets for my next trip! Can''t wait to explore new places ✈️', NULL),
    (17, 'GROUP POST: Found my old vinyl collection in the attic. Time for a nostalgia trip!', 'vinyls.jpg'),
    (19, 'GROUP POST: Productivity hack: put your phone in another room. Works like magic!', NULL),
    (21, 'GROUP POST: Started learning guitar. My neighbors probably hate me already 🎸', NULL),
    (22, 'GROUP POST: Nothing beats a rainy Sunday with a good book and hot chocolate.', 'rainy-day.jpg'),
    (24, 'GROUP POST: Meal prep Sunday! Ready for a healthy week ahead 🥗', 'meal-prep.jpg'),
    (26, 'GROUP POST: Beach day with friends = best therapy ever 🏖️', 'beach.jpg'),
    (32, 'GROUP POST: Finally organized my bookshelf by color. Yes, I''m that person now.', 'bookshelf.jpg'),
    (34, 'GROUP POST: Adopted a rescue dog today! Meet Charlie 🐕', 'dog.jpg'),
    (36, 'GROUP POST: Homemade pasta from scratch. I''m basically a chef now 👨‍🍳', 'pasta.jpg'),
    (38, 'GROUP POST: New camera arrived! Time to take way too many photos of everything.', 'camera.jpg'),
    (8, 'Does anyone else feel like Monday mornings should be illegal?', NULL),
    (10, 'Finally cleaned my entire apartment. Feeling so accomplished!', NULL),
    (14, 'Watched a documentary on climate change. We really need to do better.', NULL),
    (18, 'My cat just knocked over my coffee. Third time this week. Send help.', 'cat-mess.jpg'),
    (20, 'Hospital shift was exhausting but we saved lives today. Worth it. ❤️', NULL),
    (23, 'Why do all my houseplants keep dying? I give them love and water!', NULL),
    (25, 'That moment when you realize you''ve been singing the wrong lyrics for years...', NULL),
    (27, 'Learning Spanish on Duolingo. That owl is very threatening.', NULL),
    (28, 'Homemade bread attempt #3. Finally looks edible!', 'bread.jpg'),
    (29, 'Late night thoughts: are we living in a simulation?', NULL),
    (30, 'Running my first 10K next month. Training is killing me but I''m doing it!', NULL),
    (31, 'Tried to take an aesthetic photo of my breakfast. Ate it before remembering. Classic.', NULL),
    (33, 'Coding playlist recommendations? Need something to keep me focused!', NULL),
    (35, 'Why does time go so slowly at work but fly by on weekends?', NULL),
    (37, 'Sometimes you just need to disconnect and enjoy the little things.', NULL),
    (39, 'Meditation challenge day 30! Feeling more zen than ever 🧘', NULL),
    (40, 'When did groceries become so expensive? RIP my bank account.', NULL);

INSERT INTO review (postId, rating, mediaId, content) VALUES 
    (3, 5, 3, 'GROUP POST: This song is timeless. Daft Punk''s production is absolutely genius.'),
    (4, 4, 6, 'GROUP POST: Interstellar soundtrack gives me chills every single time.'),
    (6, 3, 8, 'GROUP POST: Good but depressing. Orwell was way too accurate about the future.'),
    (41, 5, 9, 'GROUP POST: The Shawshank Redemption is a masterpiece. The ending still gets me emotional.'),
    (42, 5, 26, 'GROUP POST: Bohemian Rhapsody is the greatest rock song ever made. Fight me.'),
    (43, 4, 10, 'GROUP POST: Pulp Fiction''s non-linear storytelling is brilliant. Tarantino at his best!'),
    (44, 5, 17, 'GROUP POST: Harry Potter was my childhood. Still magical after all these years.'),
    (45, 4, 28, 'GROUP POST: Smells Like Teen Spirit defined a generation. Raw and powerful.'),
    (46, 5, 11, 'GROUP POST: The Dark Knight isn''t just a superhero movie, it''s a crime masterpiece.'),
    (47, 3, 21, 'GROUP POST: Dune is dense but worth the effort. Herbert''s worldbuilding is insane.'),
    (48, 5, 16, 'GROUP POST: Spirited Away is pure magic. Miyazaki is a genius storyteller.'),
    (49, 4, 18, 'GROUP POST: The Hobbit is the perfect adventure story. Comfort reading at its finest.'),
    (50, 5, 35, 'GROUP POST: Shape of You is so catchy it should be illegal. Ed Sheeran killed it.');

-- POST INTERACTIONS
INSERT INTO post_like (postId, userId) VALUES 
    (1, 2), (1, 3), (1, 4), (1, 9), (1, 10),
    (2, 1), (2, 5), (2, 12),
    (3, 5), (3, 6), (3, 15),
    (4, 3), (4, 6), (4, 13),
    (5, 6), (5, 2), (5, 11),
    (6, 2), (6, 14),
    (7, 10), (7, 12), (7, 15), (7, 19),
    (8, 1), (8, 11), (8, 13), (8, 17),
    (9, 2), (9, 16), (9, 18),
    (10, 9), (10, 14), (10, 20),
    (11, 4), (11, 10), (11, 19),
    (12, 3), (12, 9), (12, 14),
    (13, 2), (13, 11), (13, 19),
    (14, 5), (14, 17), (14, 20),
    (15, 6), (15, 10), (15, 16),
    (16, 1), (16, 9), (16, 18),
    (17, 3), (17, 6), (17, 15),
    (18, 12), (18, 14), (18, 20),
    (19, 2), (19, 11), (19, 13),
    (20, 5), (20, 14), (20, 16),
    (21, 3), (21, 6), (21, 13),
    (22, 1), (22, 5), (22, 18),
    (23, 9), (23, 12), (23, 15),
    (24, 4), (24, 11), (24, 20),
    (25, 3), (25, 10), (25, 17),
    (26, 1), (26, 9), (26, 16),
    (27, 5), (27, 8), (27, 12),
    (28, 2), (28, 15), (28, 19),
    (29, 4), (29, 13), (29, 14),
    (30, 11), (30, 16), (30, 20),
    (31, 6), (31, 10), (31, 18),
    (32, 1), (32, 5), (32, 17),
    (33, 2), (33, 11), (33, 13),
    (34, 9), (34, 12), (34, 14), (34, 18), (34, 20),
    (35, 3), (35, 8), (35, 15),
    (36, 4), (36, 10), (36, 19),
    (37, 1), (37, 9), (37, 16),
    (38, 2), (38, 12), (38, 17),
    (39, 5), (39, 14), (39, 20),
    (40, 6), (40, 11), (40, 13), (40, 19),
    (41, 1), (41, 4), (41, 9), (41, 14),
    (42, 3), (42, 6), (42, 15), (42, 17),
    (43, 1), (43, 10), (43, 12),
    (44, 5), (44, 17), (44, 18),
    (45, 3), (45, 6), (45, 15),
    (46, 1), (46, 4), (46, 11),
    (47, 5), (47, 11), (47, 17),
    (48, 9), (48, 16), (48, 18), (48, 20),
    (49, 5), (49, 17), (49, 18),
    (50, 3), (50, 10), (50, 12), (50, 16);

INSERT INTO post_tag (postId, userId) VALUES 
    (1, 3), (1, 4),
    (2, 4), (2, 5),
    (3, 5), (3, 15),
    (4, 6), (4, 13),
    (5, 1), (5, 11),
    (6, 2), (6, 14),
    (7, 12), (9, 16),
    (11, 10), (12, 14),
    (15, 16), (16, 18),
    (26, 9), (26, 16),
    (34, 12), (34, 18),
    (38, 17);

-- COMMENTS
INSERT INTO comment (postId, userId, content, createdAt) VALUES 
    (1, 2, 'Totally agree! It''s a masterpiece.', CURRENT_TIMESTAMP - INTERVAL '44 days'),
    (1, 3, 'Love that movie too.', CURRENT_TIMESTAMP - INTERVAL '43 days'),
    (2, 1, 'The Gatsby prose is just magical.', CURRENT_TIMESTAMP - INTERVAL '41 days'),
    (3, 5, 'Daft Punk never misses.', CURRENT_TIMESTAMP - INTERVAL '39 days'),
    (4, 1, 'That soundtrack is pure emotion.', CURRENT_TIMESTAMP - INTERVAL '37 days'),
    (5, 2, '1984 hits differently nowadays.', CURRENT_TIMESTAMP - INTERVAL '34 days'),
    (6, 4, 'Yeah, definitely not a light read.', CURRENT_TIMESTAMP - INTERVAL '32 days');

INSERT INTO comment_like (commentId, userId) VALUES 
    (1, 1),
    (2, 1),
    (3, 2),
    (4, 1),
    (5, 3),
    (6, 5);

-- FRIENDSHIPS
INSERT INTO friendship (userId1, userId2) VALUES 
    (1, 2),
    (1, 3),
    (1, 9),
    (1, 10),
    (2, 3),
    (2, 4),
    (2, 5),
    (2, 11),
    (3, 5),
    (3, 12),
    (3, 15),
    (4, 6),
    (4, 11),
    (4, 13),
    (5, 17),
    (5, 18),
    (6, 15),
    (6, 17),
    (9, 10),
    (9, 12),
    (9, 16),
    (9, 18),
    (10, 11),
    (10, 16),
    (10, 19),
    (11, 13),
    (11, 19),
    (12, 14),
    (12, 18),
    (13, 14),
    (13, 19),
    (14, 20),
    (15, 16),
    (16, 18),
    (17, 18),
    (17, 20),
    (18, 20),
    (19, 20);

-- REQUESTS
INSERT INTO request (notificationId, status, senderId) VALUES 
    (3, 'accepted', 1),
    (8, 'pending', 3);

INSERT INTO group_invite_request (requestId, groupId) VALUES 
    (3, 1);

INSERT INTO group_join_request (requestId, groupId) VALUES 
    (8, 2);

-- REPORTS
INSERT INTO report (reason, status, postId, commentId) VALUES 
    ('Inappropriate content', 'pending', 4, NULL),
    ('Spam comment', 'accepted', NULL, 2),
    ('Harassment', 'pending', 5, NULL),
    ('Offensive language', 'pending', NULL, 5),
    ('Misleading information', 'rejected', 13, NULL),
    ('Spam post', 'accepted', 23, NULL);