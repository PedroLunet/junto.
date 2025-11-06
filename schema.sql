DROP TABLE IF EXISTS 
    comment_like,
    comment,
    like_notification,
    tag_notification,
    comment_notification,
    activity_notification,
    notification,
    membership,
    groups,
    friendship,
    friend_request,
    group_invite_request,
    group_join_request,
    request,
    report,
    post_like,
    post_tag,
    review,
    standard_post,
    post,
    users,
    book,
    film,
    music,
    media
CASCADE;

SET search_path TO lbaw2544;

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
    status VARCHAR(20) NOT NULL DEFAULT 'pending'
        CHECK (status IN ('pending', 'accepted', 'rejected')),
    senderId INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    receiverId INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE friend_request (
    requestId INTEGER PRIMARY KEY REFERENCES request(id) ON DELETE CASCADE
);

CREATE TABLE group_invite_request (
    requestId INTEGER PRIMARY KEY REFERENCES request(id) ON DELETE CASCADE,
    groupId INTEGER NOT NULL REFERENCES groups(id) ON DELETE CASCADE
);

CREATE TABLE group_join_request (
    requestId INTEGER PRIMARY KEY REFERENCES request(id) ON DELETE CASCADE,
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
    status VARCHAR(20) NOT NULL DEFAULT 'pending'
        CHECK (status IN ('pending', 'accepted', 'rejected')),
    postId INTEGER REFERENCES post(id) ON DELETE CASCADE,
    commentId INTEGER REFERENCES comment(id) ON DELETE CASCADE,
    createdAt TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CHECK (
        (postId IS NOT NULL AND commentId IS NULL)
        OR (postId IS NULL AND commentId IS NOT NULL)
    )
);