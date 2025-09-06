# Certificate Extractor

A web application to extract and view certificate information from .pfx and .p12 files. This application allows you to upload certificate files, extract their contents, and view detailed information in a user-friendly interface.

## Features

- Upload .pfx and .p12 certificate files
- Extract and view certificate details
- Download certificate components (certificate, public key, private key)
- Clean, responsive UI with dark mode support
- Containerized with Docker for easy deployment

## Prerequisites

- Docker and Docker Compose
- Git
- PHP 8.1 or higher
- Composer
- Node.js 16+ and npm 9+

## Local Development Setup

### 1. Clone the Repository

```bash
git clone https://github.com/yourusername/certificate-extractor.git
cd certificate-extractor
```

### 2. Install PHP Dependencies

```bash
composer install
```

### 3. Install JavaScript Dependencies

```bash
npm install
```

### 4. Environment Setup

Copy the example environment file and update it with your configuration:

```bash
cp .env.example .env
```

### 5. Generate Application Key

```bash
php artisan key:generate
```

### 6. Build Frontend Assets

```bash
npm run build
```

## Running with Docker

### 1. Start Containers

```bash
docker-compose up -d
```

This will start the following services:
- Web server (Nginx)
- PHP-FPM

### 2. Run Migrations

```bash
docker-compose exec app php artisan migrate
```

### 3. Access the Application

Open your browser and visit:
```
http://localhost:8000
```

## Environment Variables

Create a `.env` file in the root directory with the following variables:

```env
APP_NAME="Certificate Extractor"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=sqlite
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=laravel
# DB_USERNAME=root
# DB_PASSWORD=
```

## Security

- Always keep your `.env` file secure and never commit it to version control
- Use strong passwords for your certificates
- The application is designed to process certificates securely in memory when possible

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
