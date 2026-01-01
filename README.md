# Junto - Social Media Platform

![Laravel](https://img.shields.io/badge/Laravel-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)
![PHP](https://img.shields.io/badge/PHP-777BB4?style=for-the-badge&logo=php&logoColor=white)
![PostgreSQL](https://img.shields.io/badge/PostgreSQL-316192?style=for-the-badge&logo=postgresql&logoColor=white)
![TailwindCSS](https://img.shields.io/badge/Tailwind_CSS-38B2AC?style=for-the-badge&logo=tailwind-css&logoColor=white)
![JavaScript](https://img.shields.io/badge/JavaScript-F7DF1E?style=for-the-badge&logo=javascript&logoColor=black)

A comprehensive social networking platform built with Laravel for the LBAW (Database and Web Applications Laboratory) course at FEUP. Junto connects users through shared interests in movies, books, and music, featuring a modern social feed, group interactions, and rich media integration.

## 🌐 About

Junto is a full-featured social media platform designed to bring people together through shared cultural interests. The platform combines traditional social networking features with media discovery, allowing users to connect, share, and discuss their favorite movies, books, and music.

## 🚀 Key Features

### 👤 Authentication & User Management
- **Secure Authentication**: Email/password login with Google OAuth integration
- **Password Recovery**: Email-based password reset system
- **User Profiles**: Customizable profiles with bio, profile pictures, and favorite media
- **Account Status**: Block/unblock system with appeal mechanism for blocked users
- **Privacy Controls**: Manage visibility and friend connections

### 📱 Social Feed & Interactions
- **Dynamic Timeline**: Personalized feed with posts from friends and groups
- **Post Creation**: Share text posts with image attachments
- **Media Reviews**: Rate and review movies, books, and music
- **Engagement System**: Like and comment on posts and reviews
- **Real-time Updates**: AJAX-powered feed updates without page refresh
- **Content Filtering**: Switch between all posts and friends-only feed

### 💬 Messaging System
- **Direct Messages**: One-on-one private conversations
- **Real-time Chat**: Live message updates via AJAX polling
- **Message Management**: Delete conversations and manage chat history
- **Mention System**: Tag users with @ mentions in posts and comments

### 👥 Friend Network
- **Friend Requests**: Send, accept, or reject friend requests
- **Friend Management**: View friends list and unfriend users
- **User Search**: Discover and connect with other users
- **Friend Activity**: See posts from friends in dedicated feed

### 🎬 Media Library Integration
- **Movie Database**: Browse and search movies
- **Book Collection**: Discover and review books
- **Music Library**: Explore music albums and artists
- **Favorites System**: Add media items to personal favorites
- **Media Reviews**: Share detailed reviews with ratings

### 👨‍👩‍👧‍👦 Groups & Communities
- **Create Groups**: Start communities around shared interests
- **Group Management**: Admin controls for members and invitations
- **Join Requests**: Request-based or invite-based membership
- **Group Posts**: Dedicated feeds for group discussions
- **Membership Roles**: Owner, admin, and member permissions

### 🔍 Advanced Search
- **User Search**: Find users by name or username
- **Post Search**: Full-text search across all posts
- **Comment Search**: Search within post comments
- **Group Discovery**: Find and join relevant groups

### 🛡️ Administration & Moderation
- **Admin Dashboard**: Comprehensive platform management
- **User Moderation**: Block/unblock users with appeal system
- **Content Reports**: Review and manage reported posts and comments
- **Appeal Management**: Handle unblock requests from users
- **Platform Analytics**: Monitor user activity and engagement

### 🔔 Notification System
- **Friend Requests**: Notifications for new friend requests
- **Group Invites**: Alerts for group invitations
- **Post Interactions**: Notifications for likes and comments
- **Message Alerts**: New message notifications

## 🛠 Technical Implementation

### Tech Stack
- **Framework**: Laravel 12.x (PHP 8.2+)
- **Database**: PostgreSQL with Docker
- **Frontend**: TailwindCSS 4.0, Vite
- **Authentication**: Laravel Sanctum + Google OAuth (Socialite)
- **Storage**: Local filesystem for user uploads
- **Development**: Docker Compose for services

### System Architecture
```
┌─────────────────────────────┐
│   Frontend (Blade + Vite)   │
│   TailwindCSS + JavaScript  │
├─────────────────────────────┤
│   Laravel 12 Backend        │
│   (MVC + Policies)          │
├─────────────────────────────┤
│   PostgreSQL Database       │
│   (Docker Container)        │
└─────────────────────────────┘
```

### Database Schema
The database includes comprehensive tables for:
- **Users & Authentication**: Users, password resets, sessions
- **Social Network**: Friend requests, friendships, followers
- **Content**: Posts, reviews, comments, likes
- **Groups**: Groups, memberships, invites, join requests
- **Media**: Movies, books, music, user favorites
- **Messaging**: Direct messages between users
- **Notifications**: Friend requests, group invites, mentions
- **Moderation**: Reports, blocks, unblock appeals
- **Security**: Login attempts tracking, session management

### Key Laravel Features
- **Eloquent ORM**: Rich model relationships and queries
- **Authorization Policies**: Fine-grained access control
- **Form Validation**: Request validation for data integrity
- **Middleware**: Authentication and authorization guards
- **Service Layer**: Business logic abstraction
- **File Uploads**: Image storage and processing
- **Email**: Password reset and notification emails

## 🏗 Setup and Installation

### Prerequisites
- PHP 8.2 or higher
- Composer 2.2+
- Docker & Docker Compose
- Node.js 18+ (for Vite)

### Quick Start

1. **Clone the repository**
```bash
git clone git@github.com:YourUsername/junto.git
cd junto
```

2. **Install dependencies**
```bash
composer install
npm install
```

3. **Start PostgreSQL with Docker**
```bash
docker compose up -d
```

4. **Configure environment**
```bash
cp .env.example .env
php artisan key:generate
```

Update `.env` with database credentials:
```env
DB_CONNECTION=pgsql
DB_HOST=localhost
DB_PORT=5432
DB_DATABASE=postgres
DB_USERNAME=postgres
DB_PASSWORD=password
```

5. **Seed the database**
```bash
php artisan db:seed
```

6. **Build frontend assets**
```bash
npm run build
# Or for development with hot reload:
npm run dev
```

7. **Start the development server**
```bash
php artisan serve
```

Access the application at `http://localhost:8000`

### Database Management

**pgAdmin 4** is available at `http://localhost:4321` to manage the PostgreSQL database.

Connection details:
- **Hostname**: `postgres`
- **Username**: `postgres`
- **Password**: `password`

### Troubleshooting

**Database connection issues:**
```bash
docker compose down
docker compose up -d
php artisan db:seed
```

**Asset compilation errors:**
```bash
npm ci
npm run build
```

## 🔐 Test Credentials

You can use the seeded database accounts or create your own.

**Example credentials** (after seeding):
- Check the database seed file or create a new account via `/register`

## 📁 Project Structure

```
junto/
├── app/
│   ├── Http/
│   │   ├── Controllers/       # Request handlers
│   │   │   ├── Admin/         # Admin panel controllers
│   │   │   ├── Auth/          # Authentication
│   │   │   ├── Friendship/    # Friend system
│   │   │   ├── Media/         # Movies, books, music
│   │   │   ├── Post/          # Posts, comments, reviews
│   │   │   └── Search/        # Search functionality
│   │   └── Middleware/        # Request filters
│   ├── Models/                # Eloquent models
│   ├── Policies/              # Authorization rules
│   └── Services/              # Business logic
├── database/
│   ├── migrations/            # Database schema
│   └── junto-seed.sql         # Initial data
├── resources/
│   ├── views/                 # Blade templates
│   ├── css/                   # Stylesheets
│   └── js/                    # JavaScript modules
├── routes/
│   └── web.php                # Application routes
├── public/                    # Public assets
├── docker-compose.yaml        # Docker services
└── docs/
    └── a7_openapi.yaml        # API specification
```

## 🎯 Features Implemented

- ✅ Complete authentication system with OAuth
- ✅ User profiles with customizable bios and avatars
- ✅ Social feed with posts, reviews, and comments
- ✅ Friend system with requests and management
- ✅ Real-time messaging between users
- ✅ Media library (movies, books, music)
- ✅ Favorites and user media preferences
- ✅ Groups with membership management
- ✅ Advanced search across users, posts, comments, and groups
- ✅ Like system for posts and comments
- ✅ Mention system with @ tagging
- ✅ Notification system for interactions
- ✅ Admin panel with moderation tools
- ✅ Report system for content moderation
- ✅ Block/unblock with appeal mechanism
- ✅ Responsive design with TailwindCSS
- ✅ Image upload and storage
- ✅ Email notifications for password reset

## 🏆 Learning Outcomes

Through this project, I developed expertise in:
- **Full-stack Web Development**: End-to-end application with Laravel
- **Database Design**: Complex relational schemas with PostgreSQL
- **Modern PHP**: Laravel best practices and design patterns
- **Frontend Development**: TailwindCSS and modern JavaScript
- **Authentication & Authorization**: Policies and middleware
- **API Design**: RESTful routing and resource management
- **DevOps**: Docker containerization and deployment
- **Security**: Input validation, CSRF protection, SQL injection prevention
- **User Experience**: Responsive design and intuitive interfaces
- **Team Collaboration**: Git workflow and code reviews

## 👥 Development Team

This project was developed by:
- Pedro Lunet
- [Francisca Portugal](https://github.com/franpts2)
- [Maria Luiza Vieira](https://github.com/maluviieira)
- [Francisco Bandeira](https://github.com/fmbb8)

## 📜 Course Information

Developed for the LBAW (Database and Web Applications Laboratory) course at FEUP (Faculty of Engineering, University of Porto).

**Course**: LBAW 2024/2025  
**Project Code**: lbaw2544

---

*Built with Laravel 12, PostgreSQL, and TailwindCSS. Features modern web development practices with a focus on scalability, security, and user experience.*
