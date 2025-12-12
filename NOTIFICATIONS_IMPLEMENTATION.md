# Notifications Feature Implementation

## Overview
This document outlines the implementation of the notification system for the junto. platform, allowing users to receive and manage notifications about interactions with their content.

## Features Implemented

### 1. View Notifications
- Users can view a centralized list of all their notifications at `/notifications`
- Notifications are paginated (15 per page)
- Unread notifications are visually distinguished with a blue left border
- Each notification shows the action description and when it was created

### 2. Post Likes Notifications
- When a user likes another user's post, the post owner receives a notification
- The notification message includes the liker's name
- Notifications are only created for new likes (not on unlike)
- Uses the `post_like` table and `like_notification` table

### 3. Comment Notifications
- When a user comments on another user's post, the post owner receives a notification
- The notification message includes the commenter's name
- Uses the `comment` table and `comment_notification` table

### 4. Friend Request Notifications
- Friend requests already create notifications through the existing `request` table
- The system integrates with the existing FriendRequest model

### 5. Mark as Read
- Users can mark individual notifications as read
- "Mark all as read" button to read all notifications at once
- Read notifications appear with a gray left border instead of blue
- AJAX updates without page reload for single notifications

### 6. Snooze Notifications
- Users can snooze notifications for predefined durations:
  - 30 minutes
  - 1 hour
  - 8 hours
  - 1 day
- Snoozed notifications are excluded from unread count until snooze expires
- Users can "unsnooze" a snoozed notification

### 7. Unread Count Badge
- Notification bell icon in the sidebar shows unread count
- Updates automatically every 30 seconds
- Only visible for authenticated users
- Red badge displays the number of unread notifications

## Files Created

### Controllers
- `app/Http/Controllers/Notification/NotificationController.php` - Handles all notification-related requests

### Services
- `app/Services/NotificationService.php` - Business logic for creating notifications

### Models (Modified)
- `app/Models/User/Notification.php` - Added `snoozed_until` field support

### Views
- `resources/views/pages/notifications/index.blade.php` - Notifications listing page

### Routes
- All routes in `routes/web.php` under the "Notifications" section

### Migrations
- `database/migrations/0001_01_01_000003_add_snoozed_until_to_notification.php` - Adds snoozed_until column

## Files Modified

1. **routes/web.php**
   - Added notification routes
   - Imported NotificationController

2. **app/Http/Controllers/Home/HomeController.php**
   - Added notification trigger on post like

3. **app/Http/Controllers/Post/CommentController.php**
   - Added notification trigger on comment creation

4. **resources/views/layouts/app.blade.php**
   - Added notification bell icon to sidebar (only for authenticated users)
   - Added unread count badge
   - Added JavaScript to fetch and update unread count

## Database Schema

The implementation uses the existing notification tables:
- `notification` - Main notification table
  - id (Primary Key)
  - message (TEXT)
  - isRead (BOOLEAN)
  - receiverId (Foreign Key to users)
  - createdAt (TIMESTAMP)
  - snoozed_until (TIMESTAMP, nullable) - NEW COLUMN

Related tables used:
- `activity_notification` - Links notifications to posts
- `like_notification` - Links notifications to post likes
- `comment_notification` - Links notifications to comments
- `request` - Already handles friend request notifications

## API Endpoints

- `GET /notifications` - View notifications page
- `POST /notifications/{id}/read` - Mark a notification as read
- `POST /notifications/read-all` - Mark all notifications as read
- `POST /notifications/{id}/snooze` - Snooze a notification
- `GET /notifications/unread-count` - Get unread notification count (JSON)

## Frontend Features

### JavaScript Functions
- `markAsRead(notificationId)` - Mark single notification as read
- `markAllAsRead()` - Mark all notifications as read
- `snoozeNotification(notificationId, duration)` - Snooze for specified duration
- `unsnoozeNotification(notificationId)` - Remove snooze
- `updateUnreadCount()` - Fetch and update badge count
- `updateNotificationBadge()` - Updates sidebar badge (called on page load and every 30 seconds)

## Authorization
- All notification endpoints require authentication
- Users can only view, modify, and interact with their own notifications
- Policy checks ensure users cannot access other users' notifications

## Notes
- The implementation follows existing code style and conventions
- No verbose comments added (as per requirements)
- Components and functions are reused where possible
- Notifications are created at the time of action (like, comment, friend request)
- Snoozed notifications are hidden from unread count and notification list
