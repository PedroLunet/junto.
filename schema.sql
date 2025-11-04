-- USERS
CREATE TABLE users (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    passwordHash VARCHAR(255) NOT NULL,
    bio TEXT,
    profilePicture VARCHAR(255),
    isPrivate BOOLEAN DEFAULT FALSE,
    createdAt TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- POSTS (base table)
CREATE TABLE post (
    id SERIAL PRIMARY KEY,
    userId INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    content TEXT NOT NULL,
    createdAt TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- STANDARD POST
CREATE TABLE standard (
    postId INTEGER PRIMARY KEY REFERENCES post(id) ON DELETE CASCADE,
    imageUrl VARCHAR(255)
);

-- REVIEW POST
CREATE TABLE review (
    postId INTEGER PRIMARY KEY REFERENCES post(id) ON DELETE CASCADE,
    rating INTEGER CHECK (rating >= 1 AND rating <= 5)
);

-- MEDIA POST
CREATE TABLE media (
    postId INTEGER PRIMARY KEY REFERENCES post(id) ON DELETE CASCADE,
    title VARCHAR(255) NOT NULL,
    creator VARCHAR(255) NOT NULL,
    releaseYear INT,
    coverImage VARCHAR(255),
    mediaType VARCHAR(20) NOT NULL CHECK (mediaType IN ('Book', 'Film', 'Music'))
);

-- COMMENTS
CREATE TABLE comment (
    id SERIAL PRIMARY KEY,
    postId INTEGER NOT NULL REFERENCES post(id) ON DELETE CASCADE,
    userId INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    content TEXT NOT NULL,
    createdAt TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- GROUPS
CREATE TABLE groups (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    isPrivate BOOLEAN DEFAULT FALSE,
    icon VARCHAR(255)
);

-- MEMBERSHIP (User ↔ Group)
CREATE TABLE membership (
    userId INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    groupId INTEGER NOT NULL REFERENCES groups(id) ON DELETE CASCADE,
    isOwner BOOLEAN DEFAULT FALSE,
    joinedAt TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (userId, groupId)
);

-- REQUESTS (base table)
CREATE TABLE request (
    id SERIAL PRIMARY KEY,
    status VARCHAR(20) NOT NULL CHECK (status IN ('Pending', 'Approved', 'Rejected')),
    createdAt TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    senderId INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    receiverId INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE
);

-- FRIEND REQUEST
CREATE TABLE friend_request (
    requestId INTEGER PRIMARY KEY REFERENCES request(id) ON DELETE CASCADE
);

-- GROUP REQUEST
CREATE TABLE group_request (
    requestId INTEGER PRIMARY KEY REFERENCES request(id) ON DELETE CASCADE,
    groupId INTEGER NOT NULL REFERENCES groups(id) ON DELETE CASCADE
);

-- NOTIFICATIONS
CREATE TABLE notification (
    id SERIAL PRIMARY KEY,
    message TEXT NOT NULL,
    isRead BOOLEAN DEFAULT FALSE,
    createdAt TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    userId INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE
);

-- POST-RELATED NOTIFICATION (e.g., like, comment, tag)
CREATE TABLE post_related (
    notificationId INTEGER NOT NULL REFERENCES notification(id) ON DELETE CASCADE,
    postId INTEGER NOT NULL REFERENCES post(id) ON DELETE CASCADE,
    actionType VARCHAR(20) NOT NULL CHECK (actionType IN ('like', 'comment', 'tag')),
    PRIMARY KEY (notificationId, postId, actionType)
);

-- FRIENDSHIPS
CREATE TABLE friendship (
    userId1 INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    userId2 INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
    PRIMARY KEY (userId1, userId2),
    CHECK (userId1 < userId2)

-- GROUP-RELATED NOTIFICATION (e.g., join request, invite)
CREATE TABLE join_group (
    notificationId INTEGER NOT NULL REFERENCES notification(id) ON DELETE CASCADE,
    groupId INTEGER NOT NULL REFERENCES groups(id) ON DELETE CASCADE,
    actionType VARCHAR(20) NOT NULL CHECK (actionType IN ('join', 'invite')),
    PRIMARY KEY (notificationId, groupId, actionType)
);
