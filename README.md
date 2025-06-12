# Ravana API

[![Repository](https://img.shields.io/badge/GitHub-ravana--api-blue?logo=github)](https://github.com/mlk9/ravana-api)

Ravana is a blogging platform backend built with **Laravel 12**, offering a RESTful API to manage articles, categories, comments, user authentication, bookmarks, and an admin panel with role-based access control.

---

## Core Features

- **Public Access**  
  Browse articles, categories, and article comments without authentication.

- **User Authentication**  
  Register, login, password reset, and profile management secured via Laravel Sanctum.

- **Commenting System**  
  Authenticated users can create, view, and manage comments on articles.

- **Bookmarking**  
  Save and sync bookmarks across devices.

- **Admin Panel**  
  Role-based access for CEO and Writers to manage content, users, roles, comments, and images.

- **Rate Limiting & Security**  
  Throttle middleware for API protection and custom middleware to handle suspended users and permissions.

---

## API Routes Structure

### Public Routes (`/api/v1`)

| Method | Endpoint                     | Description                            |
|--------|------------------------------|----------------------------------------|
| GET    | `/articles`                  | List all articles                      |
| GET    | `/articles/{slug}`           | Get article details by slug            |
| GET    | `/categories`                | List all categories                    |
| GET    | `/categories/{slug}`         | Get category details by slug           |
| GET    | `/articles/{uuid}/comments`  | List comments for a specific article   |

### Authenticated Routes (`/api/v1` - Sanctum protected)

| Method | Endpoint                 | Description                                         |
|--------|--------------------------|-----------------------------------------------------|
| GET    | `/comments`              | List user comments                                  |
| GET    | `/comments/{id}`         | Show a specific comment                             |
| POST   | `/comments`              | Create a new comment                                |
| POST   | `/bookmarks/sync`        | Sync user bookmarks                                 |
| GET    | `/bookmarks`             | List user bookmarks                                 |
| GET    | `/me`                    | Get current user profile                            |
| POST   | `/articles/information`  | Get articles uuid sent information like is_bookmark |
| GET    | `/sanctum/csrf-cookie`   | Get CSRF                                            |

### Authentication Routes (`/api/v1/auth` - Guest access)

| Method | Endpoint                  | Description                 |
|--------|---------------------------|-----------------------------|
| POST   | `/register`               | Register a new user         |
| POST   | `/login`                  | User login                  |
| POST   | `/forgot`                 | Request password reset      |
| POST   | `/forgot/change-password` | Change password after reset |

### Admin Panel Routes (`/api/v1/panel` - Auth & Role protected)

| Method          | Endpoint                     | Description                     |
|-----------------|------------------------------|---------------------------------|
| GET             | `/auth/profile`              | View admin profile              |
| PUT             | `/auth/profile`              | Update admin profile            |
| *API Resource*  | `/articles`                  | Manage articles (CRUD)          |
| *API Resource*  | `/categories`                | Manage categories (CRUD)        |
| *API Resource*  | `/roles`                     | Manage user roles (CRUD)        |
| POST            | `/comments/{comment}/answer` | Answer to a comment             |
| *API Resource*  | `/comments`                  | Manage comments (CRUD)          |
| *API Resource*  | `/users`                     | Manage users (CRUD)             |
| POST            | `/images/upload`             | Upload images                   |
| DELETE          | `/images/delete`             | Delete images                   |

---

## Repository

You can find the source code and contribute at:  
[https://github.com/mlk9/ravana-api](https://github.com/mlk9/ravana-api)

---

Feel free to open issues or submit pull requests to help improve the Ravana API.
