# Ravana API

Ravana is a blogging platform backend built with **Laravel 12**, providing a RESTful API for managing articles, categories, comments, user authentication, bookmarks, and admin panel features.

---

## Core Features

- **Public Access**  
  Browse articles, categories, and article comments without authentication.

- **User Authentication**  
  Register, login, password reset, and profile management with Laravel Sanctum.

- **Commenting System**  
  Authenticated users can create, view, and manage comments on articles.

- **Bookmarking**  
  Save and sync bookmarks across devices.

- **Admin Panel**  
  Role-based access for CEO and Writers to manage articles, categories, roles, comments, users, and images.

- **Rate Limiting**  
  Extensive throttle middleware to protect the API from abuse.

- **Security**  
  Middleware to check suspended users and enforce roles.

---

## API Routes Structure

### Public Routes (`/api/v1`)

| Method | Endpoint                     | Description                        |
|--------|------------------------------|----------------------------------|
| GET    | `/articles`                  | List all articles                 |
| GET    | `/articles/{slug}`           | Get article details by slug       |
| GET    | `/categories`                | List all categories               |
| GET    | `/categories/{slug}`         | Get category details by slug      |
| GET    | `/articles/{uuid}/comments` | List comments for a specific article |

### Authenticated Routes (`/api/v1` - Sanctum protected)

| Method | Endpoint              | Description                 |
|--------|-----------------------|-----------------------------|
| GET    | `/comments`           | List user comments          |
| GET    | `/comments/{id}`      | Show a specific comment     |
| POST   | `/comments`           | Create a new comment        |
| POST   | `/bookmarks/sync`     | Sync user bookmarks         |
| GET    | `/bookmarks`          | List user bookmarks         |
| GET    | `/me`                 | Get current user profile    |

### Authentication Routes (`/api/v1/auth` - Guest access)

| Method | Endpoint                  | Description                 |
|--------|---------------------------|-----------------------------|
| POST   | `/register`               | Register a new user          |
| POST   | `/login`                  | User login                  |
| POST   | `/forgot`                 | Request password reset       |
| POST   | `/forgot/change-password` | Change password after reset  |

### Admin Panel Routes (`/api/v1/panel` - Auth & Role protected)

| Method | Endpoint                     | Description                     |
|--------|------------------------------|---------------------------------|
| GET    | `/auth/profile`              | View admin profile              |
| PUT    | `/auth/profile`              | Update admin profile            |
| *API Resource* | `/articles`          | Manage articles (CRUD)          |
| *API Resource* | `/categories`        | Manage categories (CRUD)        |
| *API Resource* | `/roles`             | Manage user roles (CRUD)        |
| POST   | `/comments/{comment}/answer` | Answer to a comment             |
| *API Resource* | `/comments`          | Manage comments (CRUD)          |
| *API Resource* | `/users`             | Manage users (CRUD)             |
| POST   | `/images/upload`             | Upload images                   |
| DELETE | `/images/delete`             | Delete images                   |

---

Feel free to contribute or report issues to improve the Ravana API.
