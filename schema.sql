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

-- Posts Table and its Subtypes

CREATE TABLE post (
    id SERIAL PRIMARY KEY,
    userId INTEGER REFERENCES users(id) ON DELETE CASCADE,
    content TEXT NOT NULL,
    createdAt TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE standart (
    postId INTEGER PRIMARY KEY REFERENCES post(id) ON DELETE CASCADE,
    imageUrl VARCHAR(255)
);

-- review and media are specialized post types

CREATE TABLE review (
    postId INTEGER PRIMARY KEY REFERENCES post(id) ON DELETE CASCADE,
    rating INTEGER CHECK (rating >= 1 AND rating <= 5),
);

CREATE TABLE media (
    postId INTEGER PRIMARY KEY REFERENCES post(id) ON DELETE CASCADE,
    title VARCHAR(255) NOT NULL,
    creator VARCHAR(255) NOT NULL,
    releaseYear INT,
    coverImage VARCHAR(255),
    mediaType VARCHAR(20) NOT NULL CHECK (mediaType IN ('Book', 'Film', 'Music'))
);

CREATE TABLE comment (
    id SERIAL PRIMARY KEY,
    postId INTEGER REFERENCES post(id) ON DELETE CASCADE,
    userId INTEGER REFERENCES users(id) ON DELETE CASCADE,
    content TEXT NOT NULL,
    createdAt TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE group (
    id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    isPrivate BOOLEAN DEFAULT FALSE,
    icon VARCHAR(255)
);

CREATE TABLE membership (
    userId INTEGER REFERENCES users(id) ON DELETE CASCADE,
    groupId INTEGER REFERENCES group(id) ON DELETE CASCADE,
    isOwner BOOLEAN DEFAULT FALSE,
    joinedAt TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (userId, groupId)
);

CREATE TABLE request (
    id SERIAL PRIMARY KEY,
    status VARCHAR(20) NOT NULL CHECK (status IN ('Pending', 'Approved', 'Rejected')),
    createdAt TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    senderId INTEGER REFERENCES users(id) ON DELETE CASCADE,
    receiverId INTEGER REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE friend_request (
    resquestId INTEGER PRIMARY KEY REFERENCES request(id) ON DELETE CASCADE
);

CREATE TABLE group_request (
    resquestId INTEGER PRIMARY KEY REFERENCES request(id) ON DELETE CASCADE,
    groupId INTEGER REFERENCES group(id) ON DELETE CASCADE
);

CREATE TABLE notification (
    id SERIAL PRIMARY KEY,
    message TEXT NOT NULL,
    isRead BOOLEAN DEFAULT FALSE,
    createdAt TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    userId INTEGER REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE post_related (
    id_notification INTEGER REFERENCES notification(id) ON DELETE CASCADE,
    id_post INTEGER REFERENCES post(id) ON DELETE CASCADE,
    actionType VARCHAR(20) NOT NULL CHECK (actionType IN ('like','comment','tag')),
    PRIMARY KEY (id_notification, id_post, actionType)
);

CREATE TABLE friendship (
    userId1 INTEGER REFERENCES users(id) ON DELETE CASCADE,
    userId2 INTEGER REFERENCES users(id) ON DELETE CASCADE,
    PRIMARY KEY (userId1, userId2)
);

CREATE TABLE join_group (
    notificationId INTEGER REFERENCES notification(id) ON DELETE CASCADE,
    groupId INTEGER REFERENCES group(id) ON DELETE CASCADE,
    PRIMARY KEY (notificationId, groupId, actionType)
);