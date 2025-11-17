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
  * **Scripted Management**: Includes easy-to-use shell scripts (`start.sh`, `stop.sh`, `logs.sh`) to manage the entire application stack.

## Technology Stack

  * **Backend**: Laravel 11 (PHP 8.3)
  * **Database**: MySQL 8
  * **Containerization**: Docker & Docker Compose
  * **Frontend**: Blade (served by the `gateway-service`)
  * **Queues**: Laravel Queues (Database Driver)
  * **Mail**: Mailtrap (for testing)

-----

## 🚀 Getting Started

Follow these instructions to get the entire application running on your local machine.

### Prerequisites

  * **Docker Desktop**: Must be installed and running.
  * **Google Gemini API Key**: Get a free key from [Google AI Studio](https://aistudio.google.com/app/apikey).
  * **Mailtrap Account**: A free [Mailtrap.io](https://mailtrap.io) account.
  * **macOS/Linux**: These instructions and scripts are designed for a Unix-based environment (like macOS or Linux).

### 1\. Clone the Repository

Clone this repository to a folder on your machine (e.g., `~/Sites`). The rest of these instructions assume your project root is `~/Sites`.

### 2\. Set Up Environment Files (`.env`)

This is the most critical step. You must create four new, blank `.env` files for the services.

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
DB_PORT=3306
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

### 3\. Make Scripts Executable

This only needs to be done once. This command gives your Mac permission to run the shell scripts.

```bash
# In your project root (e.g., ~/Sites)
chmod +x start.sh
chmod +x stop.sh
```

### 4\. Run the Application

Now, you can start the entire application with one command.

```bash
./start.sh
```

This script will:

1.  Stop and destroy any old containers and volumes.
2.  Delete the old database data to ensure a clean install.
3.  Build and start all 5 containers.
4.  Wait for the database to be healthy.
5.  Clear all config caches.
6.  Run all database migrations.

### 5\. You're All Set\!

The application is now running.

  * **Web Application:** [http://localhost](https://www.google.com/search?q=http://localhost)
  * **Mailtrap Inbox:** [https://mailtrap.io/inbox](https://www.google.com/search?q=https://mailtrap.io/inbox) (Check here for welcome emails after registering)

-----

## 🛠️ Application Management

Use these scripts in your project root to manage your environment.

### `start.sh`

Performs a "fresh start." This is the main command you will use. It destroys all old data (including the database) and rebuilds everything from scratch.

```bash
./start.sh
```

### `stop.sh`

Stops and removes all running containers. Your database data will be saved.

```bash
./stop.sh
```

*(To restart after a simple `stop`, just run `docker compose up -d`)*


### Manual Commands

You can still run manual commands inside any container.

```bash
# Example: Run a specific command
docker compose exec auth php artisan route:list

# Example: Get a shell inside the profile-service container
docker compose exec profile-service bash
```

## Troubleshooting

**IMPORTANT:** 99% of errors (like "Access Denied" or 500 errors) after changing an `.env` file are caused by a stale config cache. The `start.sh` script fixes this automatically.

**Error: "Registration Failed" on UI**

  * **Cause:** The `auth-service` or `profile-service` threw an error.
  * **Fix:** Run `./logs.sh` or check the specific service's log: `docker compose logs auth`. The full PHP stack trace will be there.

**Error: `SQLSTATE[HY000] [1045] Access denied...`**

  * **Cause:** The service is trying to connect to MySQL without a password, or with the wrong password.
  * **Fix:** Make sure the correct `DB_...` variables are in that service's `.env` file, then run `./start.sh`.

**Error: `SQLSTATE[HY000] [2002] Connection refused...`**

  * **Cause:** The `auth_worker` (or another service) started before the `database` container was ready.
  * **Fix:** The `healthcheck` in `compose.yaml` should prevent this. If it still happens, your `docker-compose up` command may be running an old configuration. Run `./start.sh` to fix it.

**Error: `Table '...' already exists`**

  * **Cause:** You are running migrations in two different services (e.g., `auth` and `profile`) that are pointed to the *same* database.
  * **Fix:** Ensure you have correctly followed Step 2 to set up separate databases (`auth_db`, `profile_db`) in each service's `.env` file. Run `./start.sh` to clear the old state.