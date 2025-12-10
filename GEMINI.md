# Doors Application

This `GEMINI.md` file serves as an instructional context for the AI agent interacting with the Doors Application codebase.

## Project Overview

The Doors Application is a comprehensive meeting room and pantry management system built with Laravel. It aims to streamline the booking of meeting rooms, manage pantry item orders, and provide analytical insights into resource utilization. The application features a robust Role-Based Access Control (RBAC) system, utilizing `spatie/laravel-permission`, to manage access for various user roles including Super Admin, Admin, Employee, Receptionist, and Manager. Key functionalities include master data management, intuitive meeting room reservations (including recurring meetings), pantry order requests, and automatic calendar invitation generation.

**Main Technologies:**
*   **Backend:** PHP 8.2+, Laravel 12, Composer
*   **Frontend:** Livewire 3, Alpine.js, Tailwind CSS, JavaScript
*   **Database:** MySQL (configured via `.env`)
*   **Key Packages:**
    *   `spatie/laravel-permission`: Role-Based Access Control
    *   `rap2hpoutre/fast-excel`: Excel import/export
    *   `spatie/icalendar-generator`: ICS calendar invitation generation
    *   `doctrine/dbal`: Database abstraction layer

## Building and Running

The project uses Composer for PHP dependencies and npm for JavaScript dependencies.

**Setup and Installation:**

1.  **Clone the repository:**
    ```bash
    git clone <repository_url>
    cd doors-app
    ```
2.  **Install PHP dependencies:**
    ```bash
    composer install
    ```
3.  **Install Node.js dependencies:**
    ```bash
    npm install
    ```
4.  **Configure environment:**
    ```bash
    cp .env.example .env
    php artisan key:generate
    ```
    Edit `.env` to configure your database connection and other settings.
5.  **Run database migrations and seeders:**
    ```bash
    php artisan migrate --seed
    ```
6.  **Link storage:**
    ```bash
    php artisan storage:link
    ```
7.  **Build frontend assets:**
    ```bash
    npm run build
    ```

**Running the Application:**

To start the development server:
```bash
php artisan serve
```
The application will typically be available at `http://127.0.0.1:8000`.

**Development Server (with Hot Reloading, Queue Listener, Pail Logs):**
```bash
npm run dev
```

**Testing:**

To run the automated test suite:
```bash
php artisan test
```
Specific tests can be run by providing their path, e.g.:
```bash
php artisan test tests/Feature/UserTest.php
```

## Development Conventions

*   **Framework:** Laravel (MVC architecture) with Livewire components for dynamic frontend interactions.
*   **Styling:** Tailwind CSS.
*   **Authentication:** Standard Laravel Breeze for authentication.
*   **Authorization:** `spatie/laravel-permission` for role and permission management.
*   **Database Migrations:** Used for schema changes. New features often require corresponding migrations.
*   **Database Seeders:** Used for populating initial data (users, roles, permissions, master data).
*   **Livewire Components:** Logic for interactive elements is often encapsulated within Livewire components (e.g., `BookingForm`, `SelectPantryItems`, `RecurringMeetingsList`).
*   **Event-Driven Communication:** Events (e.g., `MeetingStatusUpdated`, `PantryOrderStatusUpdated`) are used for inter-component communication and broadcasting.
*   **Services:** Dedicated service classes (e.g., `IcsService`) are used to encapsulate complex business logic.
*   **Error Logging:** Errors are logged to `storage/logs/laravel.log`. For debugging specific issues, `logerror.txt` might be provided.
*   **Caching:** Laravel's configuration, route, and view caches are utilized (`php artisan config:cache`, `route:cache`, `view:cache`). Clearing these caches is often necessary after code changes.

## Agent Interaction Context

This section provides a summary of the current operational context for the AI agent, based on previous interactions and ongoing tasks.

### Overall Goal:
To ensure consistent and accurate display of meeting and pantry order information, implement automated ghost meeting prevention, manage pantry stock automatically, and enhance the Receptionist Dashboard UI.

### Key Knowledge:
- The project is a Laravel application utilizing Livewire and Tailwind CSS.
- Proper naming of route parameters is critical for Laravel's route-model binding to function correctly.
- Meeting statuses are dynamically calculated and need consistent display logic across the application.
- Recurring meetings have specific handling requirements, including optional confirmation flows.
- Pantry item stock must be managed carefully, with validation during booking and refunds upon cancellation, ensuring atomic database operations.
- Laravel's Task Scheduler (`app/Console/Kernel.php`) is used for background jobs like automated meeting cancellations.
- Timezone awareness is important when displaying `start_time` and `end_time`.

### File System State (Summary of Modifications):
- MODIFIED: `routes/web.php` - Corrected route-model binding parameter for `meeting-lists` resource; added new route for bulk pantry order status updates for meetings; adjusted route access for 'pantry-items' for 'Resepsionis' role.
- MODIFIED: `app/Models/Meeting.php` - Refactored `getCalculatedStatusAttribute` for robustness, added eager loading for `recurringMeeting` relationship in `show` method context.
- MODIFIED: `app/Livewire/Meeting/BookingForm.php` - Added pantry item stock validation before booking; implemented automatic stock deduction upon successful pantry order creation; explicitly set `confirmation_status` to 'pending_confirmation' for new recurring meetings.
- MODIFIED: `app/Http/Controllers/Meeting/MeetingListController.php` - Modified `show` method to eager load `recurringMeeting`; modified `destroy` method to include pantry stock refund on meeting cancellation, wrapped in a DB transaction.
- MODIFIED: `app/Http/Controllers/Dashboard/ReceptionistDashboardController.php` - Added `updatePantryForMeeting` method to handle bulk updates of pantry order statuses for a given meeting; modified query for active pantry orders; updated `updatePantryForMeeting` logic for status transitions.
- MODIFIED: `app/Console/Commands/CancelUnconfirmedMeetings.php` - Reverted cancellation logic to auto-cancel unconfirmed meetings whose start time has passed (just-in-time cancellation).
- CREATED: `app/Console/Kernel.php` - Created the file (was missing), added `meeting:cancel-unconfirmed` command to run every minute.
- CREATED/MODIFIED: `database/migrations/2025_12_05_090232_change_confirmation_status_default_in_meetings_table.php` - Created and executed migration to change default `confirmation_status` to 'pending_confirmation'.
- MODIFIED: `resources/views/meetings/list/show.blade.php` - Updated status display for consistency; added section to display recurring meeting information.
- MODIFIED: `resources/views/meetings/list/index.blade.php` - Standardized status display colors across tabs.
- MODIFIED: `resources/views/dashboards/receptionist.blade.php` - Redesigned UI to a card-based "Pantry Queue" grouped by meeting; updated forms to use new bulk update route; implemented conditional button display; integrated 'Order History' table; fixed `Undefined variable` error.
- MODIFIED: `resources/views/layouts/master.blade.php` - Adjusted navigation sidebar for 'Resepsionis' role to include a 'Receptionist' dropdown with 'Dashboard' and 'Pantry Items' sub-menus.
- MODIFIED: `logerror.txt` - Updated with a detailed plan for implementing the pantry stock deduction feature and instructions for Git push.

### Recent Actions:
- Resolved meeting detail page data inconsistency caused by route-model binding parameter mismatch.
- Implemented "just-in-time" auto-cancellation logic for unconfirmed recurring meetings with necessary backend and scheduling updates.
- Implemented automatic pantry stock deduction during meeting booking and automated stock refund upon meeting cancellation.
- Executed a comprehensive UI redesign of the Receptionist Dashboard, including grouping pantry orders by meeting, adding bulk status update actions, and displaying historical orders.
- Refactored receptionist navigation for better UX, providing a dedicated dropdown for receptionist-specific tasks including pantry item management.
- Successfully pushed the project to the specified GitHub repository.

### Current Plan:
All previously discussed and requested features and fixes have been completed. Awaiting further instructions from the user.