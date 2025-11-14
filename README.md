# Laravel Microservice App with Gemini AI

This project is a complete microservice-based web application built with Laravel 11 and Docker. It demonstrates a fully decoupled architecture with separate services for authentication, user profiles, and notifications, all orchestrated by an API Gateway.

The application features a "Backend for Frontend" (BFF) pattern, where a `gateway-service` serves both a public-facing API and a server-rendered web UI (using Blade). It also integrates with the Google Gemini API for AI-powered content generation.

## Architecture Overview

This project uses a database-per-service pattern, where each service is responsible for its own data. All services run in their own Docker containers and communicate over a private Docker network.

### Request Flow (Text Diagram)

```
(User's Browser)
       |
       v
[ http://localhost ]
       |
       v
+---------------------+
| gateway-service     | (Handles Web UI & Sessions, `gateway_db`)
+---------------------+
       |            |
       | (Web UI)   | (API Calls)
       |   |        |
       |   |        +---------------------> +---------------------+
       |   |        |                       | auth-service        | (Handles Login/Register, `auth_db`)
       |   |        |                       +---------------------+
       |   |        |                               |
       |   |        |                               v (Async Job)
       |   |        |                       +---------------------+
       |   |        |                       | auth_worker         |
       |   |        |                       +---------------------+
       |   |        |                               |
       |   |        |                               v (API Call)
       |   |        |                       +---------------------+
       |   |        |                       | notification-service| (Sends Email)
       |   |        |                       +---------------------+
       |   |        |
       |   |        +---------------------> +---------------------+
       |   |                                | profile-service     | (Handles Profile Data, `profile_db`)
       |   |                                +---------------------+
       |   |
       +---> (Returns HTML Page)

```

### The Containers

  * **`gateway-service`** (Laravel 11): The *only* public-facing service. It handles all web traffic, manages user sessions (in `gateway_db`), and routes API calls to the correct internal service.
  * **`auth-service`** (Laravel 11): Manages all user identity (`users` table in `auth_db`), registration, login, and API token generation.
  * **`auth_worker`** (Laravel 11): A separate container that runs the `auth-service`'s queue. It processes background jobs, like firing the event to the `notification-service`.
  * **`profile-service`** (Laravel 11): Manages all non-auth user data (`profiles` table in `profile_db`).
  * **`notification-service`** (Laravel 11): A stateless service that just receives API calls and sends emails.
  * **`database`** (MySQL 8): A single MySQL container that hosts **three separate, isolated databases**: `gateway_db`, `auth_db`, and `profile_db`.

## Features

  * **Microservice Architecture**: Fully decoupled "Database-per-Service" pattern.
  * **Dockerized Environment**: 100% containerized with Docker Compose.
  * **API Gateway & BFF**: A single entry point (`gateway-service`) handles all traffic.
  * **Asynchronous Communication**: Uses Laravel Queues (database driver) to send a welcome email via the `notification-service` *without* blocking the registration request.
  * **AI Integration**: A "Generate Bio" feature on the profile page that calls the Google Gemini API.
  * **Secure**: Internal services are not exposed. Web UI is protected by session-based auth.

## Technology Stack

  * **Backend**: Laravel 11 (PHP 8.3)
  * **Database**: MySQL 8
  * **Containerization**: Docker & Docker Compose
  * **Frontend**: Blade (served by the `gateway-service`)
  * **Queues**: Laravel Queues (Database Driver)
  * **AI**: Google Gemini API
  * **Mail**: Mailtrap (for testing)

-----

## 🚀 Getting Started

Follow these instructions to get the entire application running on your local machine.

### Prerequisites

  * **Docker Desktop**: Must be installed and running.
  * **Google Gemini API Key**: Get a free key from [Google AI Studio](https://aistudio.google.com/app/apikey).
  * **Mailtrap Account**: A free [Mailtrap.io](https://mailtrap.io) account.

### 1\. Clone the Repository

Clone this repository to a folder on your machine (e.g., `~/Sites`). The rest of these instructions assume your project root is `~/Sites`.

### 2\. Set Up Environment Files (`.env`)

This is the most critical step. Do **NOT** copy your old `.env` files. Create four new, blank `.env` files.

  * `touch gateway-service/.env`
  * `touch auth-service/.env`
  * `touch profile-service/.env`
  * `touch notification-service/.env`

Now, **paste the entire contents** below into each corresponding file.

**In `gateway-service/.env`:**

```env
# APP
APP_NAME=Gateway
APP_ENV=local
APP_KEY=base64:XvJ/5QcX9QDB4LwGBeT6r1e0NquEZNCs4zSwNqgqf4A=
APP_DEBUG=true
APP_URL=http://localhost

# DATABASE (for sessions)
DB_CONNECTION=mysql
DB_HOST=database
DB_PORT=3306
DB_DATABASE=gateway_db
DB_USERNAME=root
DB_PASSWORD=secret

# SESSION
SESSION_DRIVER=database
SESSION_CONNECTION=default
SESSION_LIFETIME=120

# CACHE
CACHE_DRIVER=file

# GEMINI API
GEMINI_API_KEY=YOUR_GEMINI_API_KEY_HERE
```

*(Note: I've included a sample `APP_KEY`. You can generate your own with `php artisan key:generate` if you wish, but this will work.)*

**In `auth-service/.env`:**

```env
# APP
APP_NAME=AuthService
APP_ENV=local
APP_KEY=base64:O+r3hK0jrgSceA7jJ/4c3gYJ5v6G9yVbWdGgSbeJ+qg=
APP_DEBUG=true

# DATABASE (for users, jobs)
DB_CONNECTION=mysql
DB_HOST=database
DB_PORT=3306
DB_DATABASE=auth_db
DB_USERNAME=root
DB_PASSWORD=secret

# QUEUE
QUEUE_CONNECTION=database
```

**In `profile-service/.env`:**

```env
# APP
APP_NAME=ProfileService
APP_ENV=local
APP_KEY=base64:7aJg2qYhP8nFwX6jD9eZkL+WwR/uO3yS1cR/mB6kG2c=
APP_DEBUG=true

# DATABASE (for profiles)
DB_CONNECTION=mysql
DB_HOST=database
DB_PORT=306
DB_DATABASE=profile_db
DB_USERNAME=root
DB_PASSWORD=secret
```

**In `notification-service/.env`:**

```env
# APP
APP_NAME=NotificationService
APP_ENV=local
APP_KEY=base64:bN/sR/qD0vK2cW/fE/aG8kF6jP/eS4tY5cR/mB6kG2c=
APP_DEBUG=true

# MAILTRAP
MAIL_MAILER=smtp
MAIL_HOST=sandbox.smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=YOUR_MAILTRAP_USERNAME
MAIL_PASSWORD=YOUR_MAILTRAP_PASSWORD
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="no-reply@my-app.com"
MAIL_FROM_NAME="${APP_NAME}"
```

### 3\. Build and Run the Application

This setup uses a bind mount for the database (`db-data`). If you have run this project before, you **must destroy the old data** to allow the `init.sql` script to create the new databases.

```bash
# In your project root (e.g., ~/Sites)

# 1. Stop any running containers and remove old volumes
docker-compose down -v

# 2. Manually remove the old database data folder
# THIS IS CRITICAL for the database-per-service setup to work.
rm -rf ./db-data

# 3. Build and start all containers in the background
docker-compose up --build -d

# 4. Wait ~30 seconds for the MySQL container to initialize.
```

### 4\. Run Database Migrations

Now that all containers are running and pointing to their *own* databases, we need to run their migrations.

```bash
# 1. Clear any cached configs (to ensure .env changes are loaded)
docker-compose exec gateway php artisan config:clear
docker-compose exec auth php artisan config:clear
docker-compose exec profile php artisan config:clear

# 2. Run migrations for each service
docker-compose exec gateway php artisan migrate
docker-compose exec auth php artisan migrate
docker-compose exec profile php artisan migrate
```

### 5\. You're All Set\!

The application is now running.

  * **Web Application:** [http://localhost](https://www.google.com/search?q=http://localhost)
  * **Mailtrap Inbox:** [https://mailtrap.io/inbox](https://www.google.com/search?q=https://mailtrap.io/inbox) (Check here for welcome emails after registering)

-----

## 🛠️ Common Issues & Troubleshooting

**IMPORTANT:** 99% of errors (like "Access Denied" or 500 errors) after changing an `.env` file are caused by a stale config cache.

**The \#1 Fix for Most Problems:**

```bash
# Clear the config cache for the service that is failing
docker-compose exec gateway php artisan config:clear
docker-compose exec auth php artisan config:clear
docker-compose exec profile php artisan config:clear
```

**Error: `SQLSTATE[HY000] [1045] Access denied...`**

  * **Cause:** The service is trying to connect to MySQL without a password, or with the wrong password.
  * **Fix:** Make sure the correct `DB_...` variables are in that service's `.env` file, then run `docker-compose exec <service-name> php artisan config:clear`.

**Error: `SQLSTATE[HY000] [2002] Connection refused...`**

  * **Cause:** The Laravel app can't find the `database` container.
  * **Fix:** Make sure `DB_HOST=database` is set in your `.env` file and that the `database` container is running (`docker-compose ps`).

**Error: `SQLSTATE[HY000] [1049] Unknown database 'gateway_db'`**

  * **Cause:** The `init.sql` script did not run, likely because the old `db-data` folder existed.
  * **Fix:** Run `docker-compose down` and `rm -rf ./db-data`, then run `docker-compose up --build -d` and re-run migrations.

**Error: "Registration Failed" on UI**

  * **Cause:** The `auth-service` threw an error.
  * **Fix:** Check the logs of the `auth-service` container: `docker-compose logs auth`. The full PHP stack trace will be there.

**Error: `Table '...' already exists`**

  * **Cause:** You are running migrations in two different services (e.g., `auth` and `profile`) that are pointed to the *same* database.
  * **Fix:** Ensure you have correctly followed Step 2 to set up separate databases (`auth_db`, `profile_db`) in each service's `.env` file.