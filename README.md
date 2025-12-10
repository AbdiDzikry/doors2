# Doors Application

## Project Description

Doors is a comprehensive meeting room and pantry management system designed to streamline the booking of meeting rooms, manage pantry item orders, and provide analytical insights into resource utilization. It features a robust Role-Based Access Control (RBAC) system to ensure secure and appropriate access for different user roles, including Super Admin, Admin, Employee, Receptionist, and Manager.

## Features

### Master Data Management (Admin Module)
*   **External Participants:** Manage details of external attendees for meetings.
*   **Pantry Items:** Maintain a catalog of available pantry items, including stock levels and status.
*   **Rooms:** Manage meeting rooms, including their availability and status.
*   **Priority Guests:** Handle special guests with priority access.
*   **Users:** User management with role assignment (Super Admin, Admin, Employee, Receptionist, Manager).

### Meeting Room Management (Employee Module)
*   **Room Reservation:** Intuitive calendar-based interface for booking meeting rooms.
*   **Meeting Booking:** Detailed form for scheduling meetings, including recurring meetings, participant management, and pantry order requests.
*   **Meeting List:** View and manage scheduled meetings with filtering options.
*   **Analytics:** Visual dashboards (charts) for insights into room utilization and pantry item consumption.

### Settings (Super Admin Module)
*   **Configuration:** Manage application-wide settings and key-value configurations.
*   **Role & Permission:** UI for managing user roles and their associated permissions.

### Receptionist Dashboard
*   **Pantry Order Queue:** Real-time dashboard for managing pending pantry orders, with actions to mark orders as "Prepared" or "Delivered".

### Core Functionality
*   **Authentication:** Secure user login, registration, and password management.
*   **Role-Based Access Control (RBAC):** Granular permission management using `spatie/laravel-permission`.
*   **Recurring Meetings:** Support for scheduling meetings that repeat on a defined schedule.
*   **Automatic Calendar Invitations:** Generation and sending of `.ics` calendar invitation files to meeting participants.

## Installation Guide

To set up the Doors application locally, follow these steps:

1.  **Clone the repository:**
    ```bash
    git clone <repository_url>
    cd doors-app
    ```

2.  **Install Composer dependencies:**
    ```bash
    composer install
    ```

3.  **Install Node.js dependencies:**
    ```bash
    npm install
    ```

4.  **Create a `.env` file:**
    Copy the `.env.example` file and configure your database connection and other environment variables.
    ```bash
    cp .env.example .env
    ```

5.  **Generate application key:**
    ```bash
    php artisan key:generate
    ```

6.  **Configure database:**
    Ensure your `.env` file has the correct database credentials (e.g., for MySQL or PostgreSQL).

7.  **Run database migrations and seeders:**
    ```bash
    php artisan migrate --seed
    ```
    This will create the necessary tables and populate them with initial data (roles, permissions, default users).

8.  **Link storage:**
    ```bash
    php artisan storage:link
    ```

9.  **Build frontend assets:**
    ```bash
    npm run dev
    # or for production
    # npm run build
    ```

10. **Start the development server:**
    ```bash
    php artisan serve
    ```
    The application will be accessible at `http://127.0.0.1:8000` (or your configured host/port).

## Usage Instructions

1.  **Access the application:** Open your web browser and navigate to the URL where the application is served (e.g., `http://127.0.0.1:8000`).

2.  **Login:** Use the default credentials created by the seeders:
    *   **Super Admin:** `superadmin@example.com` / `password`
    *   **Admin:** `admin@example.com` / `password`
    *   **Employee:** `employee@example.com` / `password`
    *   **Receptionist:** `receptionist@example.com` / `password`
    *   **Manager:** `manager@example.com` / `password`

3.  **Navigate:** Use the sidebar to access different modules based on your assigned role.

    *   **Admin Module:** Manage master data (Users, Rooms, Pantry Items, External Participants, Priority Guests).
    *   **Employee Module:** Book meeting rooms, view meeting lists, and access analytics.
    *   **Super Admin Module:** Configure application settings and manage roles/permissions.
    *   **Receptionist Dashboard:** Manage pantry orders.

## Testing Instructions

To run the automated tests for the application, use the following command:

```bash
php artisan test
```

This will execute all unit and feature tests. Specific tests can be run by providing their path:

```bash
php artisan test tests/Feature/UserTest.php
```

**Note:** Some Excel import/export tests for the `UserController` are currently disabled due to environment-specific issues with `Maatwebsite\Excel` in the testing framework. The core functionality is assumed to be working in the application.

## Deployment Notes

For deploying the application to a production environment, consider the following:

*   **Environment Variables:** Ensure all `.env` variables are correctly configured for production (e.g., `APP_ENV=production`, `APP_DEBUG=false`, database credentials, mail settings).
*   **Caching:** Optimize performance by caching configuration, routes, and views:
    ```bash
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
    ```
*   **Queue Workers:** If using queues for tasks like sending emails, ensure queue workers are running.
*   **Web Server Configuration:** Configure your web server (Nginx, Apache) to point to the `public` directory of the Laravel application.
*   **Security:** Implement appropriate security measures, including HTTPS, strong passwords, and regular security audits.
*   **Backup Strategy:** Establish a regular database backup strategy.
