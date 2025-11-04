SET search_path TO lbaw2544;

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
  standard,
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
('The Great Gatsby', 'F. Scott Fitzgerald', 1925, 'gatsby.jpg'),
('Random Access Memories', 'Daft Punk', 2013, 'ram.jpg'),
('The Matrix', 'Lana Wachowski', 1999, 'matrix.jpg'),
('To Kill a Mockingbird', 'Harper Lee', 1960, 'mockingbird.jpg'),
('Interstellar', 'Christopher Nolan', 2014, 'interstellar.jpg'),
('The Beatles: Abbey Road', 'The Beatles', 1969, 'abbeyroad.jpg'),
('1984', 'George Orwell', 1949, '1984.jpg');

INSERT INTO film (mediaId) VALUES (1), (4), (6);
INSERT INTO book (mediaId) VALUES (2), (5), (8);
INSERT INTO music (mediaId) VALUES (3), (7);

-- USERS

INSERT INTO users (name, username, email, passwordHash, bio, profilePicture, isPrivate, isAdmin, favoriteFilm, favoriteBook, favoriteSong)
VALUES
('Alice Martins', 'alice', 'alice@example.org', 'hash1', 'Movie lover', 'alice.jpg', FALSE, FALSE, 1, 2, 3),
('Bruno Silva', 'bruno', 'bruno@example.org', 'hash2', 'Reader & gamer', 'bruno.jpg', FALSE, TRUE, 4, 5, 3),
('Carla Dias', 'carla', 'carla@example.org', 'hash3', 'Music addict', 'carla.jpg', TRUE, FALSE, 1, 2, 7),
('David Costa', 'david', 'david@example.org', 'hash4', 'Cinephile', 'david.jpg', FALSE, FALSE, 6, NULL, NULL),
('Eva Rocha', 'eva', 'eva@example.org', 'hash5', 'Book enthusiast', 'eva.jpg', TRUE, FALSE, 4, 8, 7),
('Filipe Moreira', 'filipe', 'filipe@example.org', 'hash6', 'Vinyl collector', 'filipe.jpg', FALSE, FALSE, 1, NULL, 3);

-- POSTS

INSERT INTO post (userId) VALUES
(1), (2), (3), (4), (5), (6);

INSERT INTO standard (postId, text, imageUrl) VALUES
(1, 'Just watched Inception again. Still brilliant.', 'inception-post.jpg'),
(2, 'Reading The Great Gatsby this weekend.', NULL),
(5, 'Finally finished 1984. Heavy stuff.', '1984-review.jpg');

INSERT INTO review (postId, rating, mediaId, content) VALUES
(3, 5, 3, 'This album is timeless.'),
(4, 4, 6, 'Interstellar soundtrack gives me chills.'),
(6, 3, 8, 'Good but depressing.');

-- POST INTERACTIONS

INSERT INTO post_like (postId, userId) VALUES
(1, 2), (1, 3), (1, 4),
(2, 1), (3, 5), (4, 3), (5, 6), (6, 2);

INSERT INTO post_tag (postId, userId) VALUES
(1, 3), (2, 4), (3, 5), (4, 6), (5, 1), (6, 2);

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
(1, 1), (2, 1), (3, 2), (4, 1), (5, 3), (6, 5);

-- GROUPS

INSERT INTO groups (name, description, isPrivate, icon) VALUES
('Film Buffs', 'Discuss your favorite movies', FALSE, 'film-buffs.jpg'),
('Bookworms', 'Share and review your favorite books', TRUE, 'bookworms.jpg'),
('Music Lovers', 'Everything about records and concerts', FALSE, 'music-lovers.jpg');

INSERT INTO membership (userId, groupId, isOwner) VALUES
(1, 1, TRUE), (2, 1, FALSE), (3, 3, TRUE), (4, 1, FALSE),
(5, 2, TRUE), (6, 3, FALSE);

-- FRIENDSHIPS

INSERT INTO friendship (userId1, userId2) VALUES
(1, 2), (1, 3), (2, 4), (3, 5), (4, 6), (2, 5);

-- NOTIFICATIONS

INSERT INTO notification (message, receiverId, isRead) VALUES
('Bruno liked your post.', 1, FALSE),
('Carla commented on your post.', 1, FALSE),
('New group invite: Film Buffs.', 3, TRUE),
('Alice tagged you in a post.', 2, FALSE),
('Filipe mentioned you in Music Lovers.', 3, FALSE),
('Eva replied to your comment.', 4, FALSE);

INSERT INTO activity_notification (notificationId, postId) VALUES
(1, 1), (2, 1), (4, 1), (5, 3), (6, 5);

INSERT INTO comment_notification (notificationId, commentId) VALUES
(2, 1), (6, 7);

INSERT INTO tag_notification (notificationId, postId) VALUES
(4, 1), (5, 3);

INSERT INTO like_notification (notificationId, postId) VALUES
(1, 1);

-- REQUESTS

INSERT INTO request (notificationId, status, senderId) VALUES
(NULL, 'pending', 2),
(3, 'accepted', 1),
(NULL, 'rejected', 3),
(NULL, 'pending', 5),
(NULL, 'accepted', 6);

INSERT INTO friend_request (requestId) VALUES (1), (4);
INSERT INTO group_invite_request (requestId, groupId) VALUES (2, 1), (5, 3);
INSERT INTO group_join_request (requestId, groupId) VALUES (3, 2);

-- REPORTS

INSERT INTO report (reason, status, postId, commentId) VALUES
('Inappropriate content', 'pending', 4, NULL),
('Spam comment', 'accepted', NULL, 2),
('Harassment', 'pending', 5, NULL);
