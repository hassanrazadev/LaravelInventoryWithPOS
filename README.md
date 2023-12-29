# Laravel Inventory Management with POS

This project is a Laravel-based Inventory Management System integrated with a Point of Sale (POS) system.

## Installation

To install and run this project locally, follow these steps:

### Prerequisites

- PHP (>=7.3)
- Composer
- MySQL or another compatible database system

### Steps

1. Clone the repository:

    ```bash
    git clone https://github.com/hassanrazadev/LaravelInventoryWithPOS.git
    ```

2. Navigate to the project directory:

    ```bash
    cd LaravelInventoryManagementWIthPOS
    ```

3. Install PHP dependencies:

    ```bash
    composer install
    ```

4. Copy the `.env.example` file to `.env` and configure the database settings:

    ```bash
    cp .env.example .env
    ```

   Update `.env` file with your database credentials.

5. Generate application key:

    ```bash
    php artisan key:generate
    ```

6. Run database migrations and seeders:

    ```bash
    php artisan migrate --seed
    ```

7. Start the server:

    ```bash
    php artisan serve
    ```

8. Access the application in your browser at `http://localhost:8000`.

## License

This project is licensed under the MIT License. See the [LICENSE](LICENSE) file for details.

## Developer

- Name: Hassan Raza
- Domain: [hassanraza.net](https://hassanraza.net)
- Contact: info@hassanraza.net

