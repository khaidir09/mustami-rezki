# Business Management System

This is a comprehensive web-based management system built with Laravel, designed to streamline business operations including inventory, sales, purchases, production, and financial reporting.

## Features

### Dashboard & Administration
- **Admin Dashboard:** Overview of key metrics.
- **Role & Permission Management:** Granular access control using Roles and Permissions.
- **User Management:** Manage admins and users.

### Inventory & Products
- **Product Management:** Manage products, categories, and stock.
- **Supplier & Customer Management:** Database of suppliers and customers.
- **Stock Transfers:** Manage stock movement between locations.

### Transactions
- **Sales:** Point of Sale (POS) like functionality, invoice generation, sales returns, and due management.
- **Purchases:** Purchase orders, product search, and returns.
- **Tailor Transactions:** Specialized module for managing tailoring orders and invoices.
- **Production:** Manage production processes.
- **Services:** Manage service offerings.

### Financials
- **Expense Management:** Track business expenses.
- **Payroll:** Employee payroll generation and history.
- **Financial Reports:**
  - Monthly and Daily Cash Flow (Arus Kas).
  - Profit Distribution.
  - Sales & Purchase Reports.
  - Stock Reports.

### HR & Attendance
- **Attendance:** Track employee attendance.

## Technology Stack

- **Backend:** [Laravel 12](https://laravel.com)
- **Frontend:** [Tailwind CSS](https://tailwindcss.com), [Alpine.js](https://alpinejs.dev)
- **Build Tool:** [Vite](https://vitejs.dev)
- **PDF Generation:** [laravel-dompdf](https://github.com/barryvdh/laravel-dompdf)
- **Image Handling:** [intervention/image](https://image.intervention.io)

## Installation

Follow these steps to set up the project locally:

1.  **Clone the repository**
    ```bash
    git clone <repository-url>
    cd <project-directory>
    ```

2.  **Install PHP dependencies**
    ```bash
    composer install
    ```

3.  **Install Node.js dependencies**
    ```bash
    npm install
    ```

4.  **Environment Configuration**
    Copy the example environment file and configure your database settings.
    ```bash
    cp .env.example .env
    ```
    Update the `.env` file with your database credentials (DB_DATABASE, DB_USERNAME, DB_PASSWORD).

5.  **Generate Application Key**
    ```bash
    php artisan key:generate
    ```

6.  **Run Migrations & Seeders**
    Set up the database tables and initial data.
    ```bash
    php artisan migrate --seed
    ```

7.  **Build Assets**
    ```bash
    npm run build
    ```

8.  **Run the Application**
    Start the local development server.
    ```bash
    php artisan serve
    ```
    The application will be accessible at `http://localhost:8000`.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
