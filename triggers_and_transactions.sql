

SET search_path TO lbaw2544;

-- =============================================================
--  SECTION 4 — TRIGGERS AND USER DEFINED FUNCTIONS
-- =============================================================


-- FUNCTION: Notify Like on Post
CREATE OR REPLACE FUNCTION fn_notify_post_like() RETURNS TRIGGER AS $$
DECLARE notif_id INT;
DECLARE post_owner INT;
BEGIN
    SELECT userId INTO post_owner FROM post WHERE id = NEW.postId;

    INSERT INTO notification (message, receiverId)
    VALUES (
        CONCAT('Your post received a like from ', NEW.userId),
        post_owner
    )
    RETURNING id INTO notif_id;

    INSERT INTO like_notification (notificationId, postId)
    VALUES (notif_id, NEW.postId);

    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

-- Justification: Notifies the post author when their post receives a like.


-- TRIGGER: Notify Like on Post
CREATE OR REPLACE TRIGGER trg_post_like_notify
AFTER INSERT ON post_like
FOR EACH ROW EXECUTE FUNCTION fn_notify_post_like();



-- FUNCTION: Notify Like on Comment
CREATE OR REPLACE FUNCTION fn_notify_comment_like() RETURNS TRIGGER AS $$
DECLARE notif_id INTEGER;
DECLARE comment_owner INTEGER;
DECLARE post_ref INTEGER;
BEGIN
  SELECT userId, postId INTO comment_owner, post_ref FROM comment WHERE id = NEW.commentId;

  INSERT INTO notification (message, receiverId)
  VALUES (
    CONCAT('Your comment received a like from ', NEW.userId),
    comment_owner
  )
  RETURNING id INTO notif_id;

  INSERT INTO activity_notification (notificationId, postId)
  VALUES (notif_id, post_ref);

  RETURN NEW;
END;
$$ LANGUAGE plpgsql;

-- Justification: Notifies the comment author when their comment receives a like.


-- TRIGGER: Notify Like on Comment
CREATE OR REPLACE TRIGGER trg_comment_like_notify
AFTER INSERT ON comment_like
FOR EACH ROW EXECUTE FUNCTION fn_notify_comment_like();



-- FUNCTION: Notify Comment on Post
CREATE OR REPLACE FUNCTION fn_notify_comment() RETURNS TRIGGER AS $$
DECLARE notif_id INTEGER;
DECLARE post_owner INTEGER;
BEGIN
  SELECT userId INTO post_owner FROM post WHERE id = NEW.postId;

  INSERT INTO notification (message, receiverId)
  VALUES (
    CONCAT('Your post received a comment from ', NEW.userId),
    post_owner
  )
  RETURNING id INTO notif_id;

  INSERT INTO activity_notification (notificationId, postId)
  VALUES (notif_id, NEW.postId);

  INSERT INTO comment_notification (notificationId, commentId)
  VALUES (notif_id, NEW.id);

  RETURN NEW;
END;
$$ LANGUAGE plpgsql;

-- Justification: Notifies the post author when a new comment is added to their post.


-- TRIGGER: Notify Comment on Post
CREATE OR REPLACE TRIGGER trg_comment_notify
AFTER INSERT ON comment
FOR EACH ROW EXECUTE FUNCTION fn_notify_comment();



-- FUNCTION: Notify Friend Request
CREATE OR REPLACE FUNCTION fn_notify_friend_request() RETURNS TRIGGER AS $$
DECLARE sender_name TEXT;
DECLARE receiver INTEGER;
BEGIN
  SELECT (SELECT username FROM users WHERE id = senderId), receiverId
  INTO sender_name, receiver
  FROM request WHERE id = NEW.requestId;

  INSERT INTO notification (message, receiverId)
  VALUES (
    CONCAT('You received a friend request from ', sender_name),
    receiver
  );

  RETURN NEW;
END;
$$ LANGUAGE plpgsql;

-- Justification: Notifies a user when they receive a friend request.


-- TRIGGER: Notify Friend Request
CREATE OR REPLACE TRIGGER trg_friend_request_notify
AFTER INSERT ON friend_request
FOR EACH ROW EXECUTE FUNCTION fn_notify_friend_request();



-- FUNCTION: Notify Group Join Request
CREATE OR REPLACE FUNCTION fn_notify_group_join_request() RETURNS TRIGGER AS $$
BEGIN
    INSERT INTO notification (message, receiverId)
    SELECT
        CONCAT(
            'User ',
            (SELECT username FROM users WHERE id = (SELECT senderId FROM request WHERE id = NEW.requestId)),
            ' requested to join your group.'
        ),
        (SELECT userId FROM membership WHERE groupId = NEW.groupId AND isOwner = TRUE LIMIT 1);
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

-- Justification: Notifies the group owner when a user requests to join their group.


-- TRIGGER: Notify Group Join Request
CREATE OR REPLACE TRIGGER trg_group_join_notify
AFTER INSERT ON group_join_request
FOR EACH ROW EXECUTE FUNCTION fn_notify_group_join_request();



-- FUNCTION: Anonymize User Data
CREATE OR REPLACE FUNCTION fn_anonymize_user_data(p_user_id INT) RETURNS VOID AS $$
BEGIN
    UPDATE users
    SET
        name = 'Deleted User',
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

-- Justification: Anonymizes user data and removes related social interactions when an account is deleted.



-- =============================================================
--  SECTION 5 — DATABASE TRANSACTIONS (Generalized)
-- =============================================================


-- US18 - Edit Profile
CREATE OR REPLACE FUNCTION fn_edit_profile(p_user_id INT, p_name TEXT, p_bio TEXT, p_picture TEXT) RETURNS VOID AS $$
BEGIN
  SET TRANSACTION ISOLATION LEVEL READ COMMITTED; -- Suitable: simple update, only needs to avoid dirty reads.

  UPDATE users
  SET name = p_name,
      bio = p_bio,
      profilePicture = p_picture
  WHERE id = p_user_id;
END;
$$ LANGUAGE plpgsql;

-- Justification: Updates a user's personal information in their profile.



-- US22 - Create Regular Post
CREATE OR REPLACE FUNCTION fn_create_standard_post(p_user_id INT, p_text TEXT, p_image_url TEXT) RETURNS VOID AS $$
DECLARE new_post_id INT;
BEGIN
  SET TRANSACTION ISOLATION LEVEL READ COMMITTED; -- Suitable: short insert transaction, no concurrency risk.

  INSERT INTO post (userId)
  VALUES (p_user_id)
  RETURNING id INTO new_post_id;

  INSERT INTO standard (postId, text, imageUrl)
  VALUES (new_post_id, p_text, p_image_url);
END;
$$ LANGUAGE plpgsql;

-- Justification: Creates a new standard post with text and/or image content.



-- US23 - Create Review Post
CREATE OR REPLACE FUNCTION fn_create_review_post(p_user_id INT, p_rating INT, p_media_id INT, p_content TEXT) RETURNS VOID AS $$
DECLARE new_post_id INT;
BEGIN
  SET TRANSACTION ISOLATION LEVEL READ COMMITTED; -- Similar to standard post creation.

  INSERT INTO post (userId)
  VALUES (p_user_id)
  RETURNING id INTO new_post_id;

  INSERT INTO review (postId, rating, mediaId, content)
  VALUES (new_post_id, p_rating, p_media_id, p_content);
END;
$$ LANGUAGE plpgsql;

-- Justification: Creates a new review post with rating and media reference.



-- US24 - Report Content
CREATE OR REPLACE FUNCTION fn_report_post(p_post_id INT, p_reason TEXT) RETURNS VOID AS $$
BEGIN
  SET TRANSACTION ISOLATION LEVEL READ COMMITTED; -- Suitable: single insert, minimal concurrency impact.

  INSERT INTO report (reason, status, postId, createdAt)
  VALUES (p_reason, 'pending', p_post_id, CURRENT_TIMESTAMP);
END;
$$ LANGUAGE plpgsql;

-- Justification: Allows a user to report a post for moderation.



-- US26 - Delete Account (Anonymization)
CREATE OR REPLACE FUNCTION fn_delete_account(p_user_id INT) RETURNS VOID AS $$
BEGIN
  SET TRANSACTION ISOLATION LEVEL SERIALIZABLE; -- Critical operation across multiple tables; must prevent interference.

  PERFORM fn_anonymize_user_data(p_user_id);
END;
$$ LANGUAGE plpgsql;

-- Justification: Performs account deletion by anonymizing user data.



-- US28 - Send Friend Request
CREATE OR REPLACE FUNCTION fn_send_friend_request(p_sender_id INT, p_receiver_id INT) RETURNS VOID AS $$
DECLARE new_request_id INT;
BEGIN
  SET TRANSACTION ISOLATION LEVEL REPEATABLE READ; -- Prevents duplicate requests between same users under concurrency.

  INSERT INTO request (notificationId, status, senderId, receiverId)
  VALUES (NULL, 'pending', p_sender_id, p_receiver_id)
  RETURNING id INTO new_request_id;

  INSERT INTO friend_request (requestId)
  VALUES (new_request_id);
END;
$$ LANGUAGE plpgsql;

-- Justification: Creates a friend request entry linking sender and receiver.



-- US29 - Unfriend
CREATE OR REPLACE FUNCTION fn_unfriend(p_user1_id INT, p_user2_id INT) RETURNS VOID AS $$
BEGIN
  SET TRANSACTION ISOLATION LEVEL READ COMMITTED; -- Simple delete operation; stronger isolation not needed.

  DELETE FROM friendship
  WHERE (userId1 = p_user1_id AND userId2 = p_user2_id)
     OR (userId1 = p_user2_id AND userId2 = p_user1_id);
END;
$$ LANGUAGE plpgsql;

-- Justification: Removes the friendship relationship between two users.



-- US30 - Comment on Post
CREATE OR REPLACE FUNCTION fn_comment_on_post(p_post_id INT, p_user_id INT, p_content TEXT) RETURNS VOID AS $$
BEGIN
  SET TRANSACTION ISOLATION LEVEL READ COMMITTED; -- Simple insert, only depends on committed data.

  INSERT INTO comment (postId, userId, content)
  VALUES (p_post_id, p_user_id, p_content);
END;
$$ LANGUAGE plpgsql;

-- Justification: Adds a new comment to a specific post.



-- US31 - React to Post
CREATE OR REPLACE FUNCTION fn_react_to_post(p_post_id INT, p_user_id INT) RETURNS VOID AS $$
BEGIN
  SET TRANSACTION ISOLATION LEVEL REPEATABLE READ; -- Prevents duplicate likes under concurrent transactions.

  INSERT INTO post_like (postId, userId)
  VALUES (p_post_id, p_user_id)
  ON CONFLICT DO NOTHING;
END;
$$ LANGUAGE plpgsql;

-- Justification: Registers a like on a post, ignoring duplicates.



-- US32 - React to Comment
CREATE OR REPLACE FUNCTION fn_react_to_comment(p_comment_id INT, p_user_id INT) RETURNS VOID AS $$
BEGIN
  SET TRANSACTION ISOLATION LEVEL REPEATABLE READ; -- Same reasoning as post reactions.

  INSERT INTO comment_like (commentId, userId)
  VALUES (p_comment_id, p_user_id)
  ON CONFLICT DO NOTHING;
END;
$$ LANGUAGE plpgsql;

-- Justification: Registers a like on a comment, ignoring duplicates.



-- US33 - Create Group
CREATE OR REPLACE FUNCTION fn_create_group(p_user_id INT, p_name TEXT, p_description TEXT, p_is_private BOOLEAN) RETURNS VOID AS $$
DECLARE new_group_id INT;
BEGIN
  SET TRANSACTION ISOLATION LEVEL SERIALIZABLE; -- Group creation and membership insert must be atomic.

  INSERT INTO groups (name, description, isPrivate)
  VALUES (p_name, p_description, p_is_private)
  RETURNING id INTO new_group_id;

  INSERT INTO membership (userId, groupId, isOwner)
  VALUES (p_user_id, new_group_id, TRUE);
END;
$$ LANGUAGE plpgsql;

-- Justification: Creates a new group and assigns the creator as the owner.



-- US34 - Send Group Invitation
CREATE OR REPLACE FUNCTION fn_send_group_invite(p_sender_id INT, p_receiver_id INT, p_group_id INT) RETURNS VOID AS $$
DECLARE new_req_id INT;
BEGIN
  SET TRANSACTION ISOLATION LEVEL REPEATABLE READ; -- Prevents duplicate invitations for same user and group.

  INSERT INTO request (notificationId, status, senderId, receiverId)
  VALUES (NULL, 'pending', p_sender_id, p_receiver_id)
  RETURNING id INTO new_req_id;

  INSERT INTO group_invite_request (requestId, groupId)
  VALUES (new_req_id, p_group_id);
END;
$$ LANGUAGE plpgsql;

-- Justification: Sends a group invitation request to another user.



-- US51 - Manage Reported Content
CREATE OR REPLACE FUNCTION fn_manage_report(p_report_id INT, p_new_status TEXT) RETURNS VOID AS $$
DECLARE post_target INT;
BEGIN
  SET TRANSACTION ISOLATION LEVEL SERIALIZABLE; -- Ensures two moderators can’t process the same report simultaneously.

  UPDATE report
  SET status = p_new_status
  WHERE id = p_report_id;

  SELECT postId INTO post_target FROM report WHERE id = p_report_id;

  IF p_new_status = 'accepted' THEN
    DELETE FROM post WHERE id = post_target;
  END IF;
END;
$$ LANGUAGE plpgsql;

-- Justification: Updates a report’s status and removes offending content if accepted.



-- US53 - Block/Unblock User
CREATE OR REPLACE FUNCTION fn_toggle_block_user(p_user_id INT, p_is_blocked BOOLEAN) RETURNS VOID AS $$
BEGIN
  SET TRANSACTION ISOLATION LEVEL READ COMMITTED; -- Simple update, no need for strong isolation.

  UPDATE users
  SET isBlocked = p_is_blocked
  WHERE id = p_user_id;
END;
$$ LANGUAGE plpgsql;

-- Justification: Enables or disables the blocked status of a user.



-- US54 - Admin Delete User Account
CREATE OR REPLACE FUNCTION fn_admin_delete_user(p_user_id INT) RETURNS VOID AS $$
BEGIN
  SET TRANSACTION ISOLATION LEVEL SERIALIZABLE; -- Administrative critical operation; must be fully isolated.

  PERFORM fn_anonymize_user_data(p_user_id);
END;
$$ LANGUAGE plpgsql;

-- Justification: Allows an admin to delete a user by anonymizing their data.
