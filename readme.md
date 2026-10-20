# Leave Management

A web application for managing employee leave requests. Employees can review their leave balances, submit requests, and see their requests alongside the team calendar. Managers can review eligible pending requests directly from the calendar.

## Features

- Employee and manager sign-in, with role-aware actions.
- Calendar view of approved, pending, and rejected leave.
- Leave balances and a summary of the employee's requests for the current year.
- Leave requests with date, leave-type, allowance, and replacement validation.
- Manager approval and rejection actions for:
  - employees in the manager's department; and
  - other managers, regardless of department.
- Pending-request counts on the calendar's previous and next month controls.
- Employee creation for managers and password changes for signed-in users.

## Technology

- PHP 8.3 and Apache
- MySQL 8
- PDO
- Docker Compose
- FullCalendar, Bootstrap, and Font Awesome (loaded from CDNs)

## Run locally

### Prerequisites

- Docker with Docker Compose
- A browser
- An internet connection to load the calendar and UI libraries from their CDNs

### Start the application

From the project root, run:

```sh
docker-compose up -d --build
```

Docker builds the PHP/Apache container, starts MySQL, and initializes a new database with the SQL files in `database/`.

Open the application at **http://localhost:8000**.

The Compose file also starts phpMyAdmin at **http://localhost:8080**. For the local Compose database, it is configured with:

| User | Password |
| --- | --- |
| `app` | `app` |
| `root` | `root` |

These credentials are for the local development setup only.

### Sign in

The database seed creates example manager and employee accounts. Sign in with an account's email address and password. The seed stores password hashes rather than documenting plaintext passwords; use credentials available to you for the seeded accounts.

New employees created from the manager menu are assigned the initial password `Password123!`. They should change it after their first sign-in.

## Leave request rules

- Requests must have valid dates, start and end on weekdays, and begin at least three working days from today.
- Annual leave limits and replacement requirements depend on the employee's position and leave type.
- A replacement is selected from eligible, available employees in the requester's department when the leave rule requires one.
- Leave balances count approved leave and exclude weekends.

## Database and Docker commands

The MySQL data is stored in the `db_data` Docker volume. Stopping or removing the containers does not remove this volume.

Stop the containers while keeping the database and its data:

```sh
docker-compose down
```

Alternatively, stop the containers without removing them:

```sh
docker-compose stop
```

Start the containers again:

```sh
docker-compose start
```

To remove the containers **and permanently delete the database volume**, then rebuild and initialize a fresh database:

```sh
docker-compose down -v
docker-compose up -d --build
```

> **Warning:** `docker-compose down -v` deletes the database and all data stored in it. The SQL initialization files run automatically only when MySQL creates a new data directory.

## Project structure

```text
config/                 Database and dependency-injection configuration
database/               MySQL schema and example seed data
docker/                 PHP/Apache container configuration
public/                 Web entry point and public assets
src/
  Controllers/           HTTP request handling
  Core/                  Routing, views, database, and shared helpers
  Models/                Application data models
  Repositories/          Database queries
  Services/              Application and business rules
views/                   PHP templates
```

## Notes

This project is intended as a learning and portfolio application. Its bundled database credentials and initial employee password are convenient for a local demo and should not be reused in a public or production deployment.
