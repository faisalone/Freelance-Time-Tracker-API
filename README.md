# Freelancer Time Tracker API

A modern Laravel 12 API for freelancers to track time across clients and projects with advanced reporting capabilities.

## 🚀 Features

- **Authentication**: Laravel Sanctum token-based authentication
- **Client Management**: Organize work by clients with contact information and hourly rates
- **Project Tracking**: Manage multiple projects per client with status tracking
- **Time Logging**: Start/stop timers or create manual time entries
- **Advanced Reporting**: Generate reports by project, client, or date range
- **PDF Exports**: Professional PDF reports with detailed time breakdowns
- **Email Notifications**: Automatic alerts for 8+ hour workdays
- **Modern Architecture**: Repository pattern, Service layer, and Event-driven design

## 📋 Requirements

- PHP 8.2+
- Composer
- SQLite/MySQL/PostgreSQL
- Node.js (for frontend assets, optional)

## 🛠️ Installation

### 1. Clone the Repository
```bash
git clone <repository-url>
cd "Freelancer Time Tracker API"
```

### 2. Install Dependencies
```bash
composer install
npm install  # Optional, for frontend assets
```

### 3. Environment Setup
```bash
cp .env.example .env
php artisan key:generate
```

### 4. Configure Database
Edit `.env` file with your database credentials:
```env
DB_CONNECTION=sqlite
DB_DATABASE=/absolute/path/to/database/database.sqlite
```

For MySQL:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=freelancer_tracker
DB_USERNAME=root
DB_PASSWORD=
```

### 5. Run Migrations & Seed Data
```bash
php artisan migrate:fresh --seed
```

### 6. Start Development Server
```bash
php artisan serve
```

The API will be available at `http://localhost:8000`

## 📊 Database Structure

### Users Table
- `id`, `name`, `email`, `password`, `email_verified_at`, `remember_token`, `created_at`, `updated_at`

### Clients Table
- `id`, `user_id`, `name`, `email`, `phone`, `address`, `hourly_rate`, `status`, `created_at`, `updated_at`

### Projects Table
- `id`, `user_id`, `client_id`, `name`, `description`, `status`, `deadline`, `created_at`, `updated_at`

### Time Logs Table
- `id`, `user_id`, `project_id`, `description`, `start_time`, `end_time`, `hours`, `is_billable`, `tags`, `created_at`, `updated_at`

## 🔐 Authentication

### Register
```http
POST /api/register
Content-Type: application/json

{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123",
    "password_confirmation": "password123"
}
```

### Login
```http
POST /api/login
Content-Type: application/json

{
    "email": "john@example.com",
    "password": "password123"
}
```

### Logout
```http
POST /api/logout
Authorization: Bearer {token}
```

## 📚 API Endpoints

### Clients
- `GET /api/clients` - List all clients
- `POST /api/clients` - Create new client
- `GET /api/clients/{id}` - Show client details
- `PUT /api/clients/{id}` - Update client
- `DELETE /api/clients/{id}` - Delete client

### Projects
- `GET /api/projects` - List all projects
- `POST /api/projects` - Create new project
- `GET /api/projects/{id}` - Show project details
- `PUT /api/projects/{id}` - Update project
- `DELETE /api/projects/{id}` - Delete project

### Time Logs
- `GET /api/time-logs` - List time logs
- `POST /api/time-logs` - Create time log
- `GET /api/time-logs/{id}` - Show time log
- `PUT /api/time-logs/{id}` - Update time log
- `DELETE /api/time-logs/{id}` - Delete time log
- `POST /api/time-logs/start` - Start a timer
- `POST /api/time-logs/{id}/stop` - Stop a running timer
- `GET /api/time-logs/running` - Get running timers

### Reports
- `GET /api/reports/time` - Generate time reports
- `GET /api/reports/time/pdf` - Download PDF report

## 📈 Report Examples

### Time Report by Date Range
```http
GET /api/reports/time?from=2024-01-01&to=2024-01-31
Authorization: Bearer {token}
```

### Time Report by Client
```http
GET /api/reports/time?client_id=1&from=2024-01-01&to=2024-01-31
Authorization: Bearer {token}
```

### Time Report by Project
```http
GET /api/reports/time?project_id=1&from=2024-01-01&to=2024-01-31
Authorization: Bearer {token}
```

### PDF Export
```http
GET /api/reports/time/pdf?client_id=1&from=2024-01-01&to=2024-01-31
Authorization: Bearer {token}
```

## 🎯 Example Usage

### Creating a Client
```http
POST /api/clients
Authorization: Bearer {token}
Content-Type: application/json

{
    "name": "TechCorp Solutions",
    "email": "contact@techcorp.com",
    "phone": "+1-555-0123",
    "address": "123 Tech Street, Silicon Valley, CA",
    "hourly_rate": 75.00,
    "status": "active"
}
```

### Starting a Time Log
```http
POST /api/time-logs/start
Authorization: Bearer {token}
Content-Type: application/json

{
    "project_id": 1,
    "description": "Working on user authentication feature"
}
```

### Creating Manual Time Entry
```http
POST /api/time-logs
Authorization: Bearer {token}
Content-Type: application/json

{
    "project_id": 1,
    "description": "Code review and testing",
    "start_time": "2024-01-15 09:00:00",
    "end_time": "2024-01-15 12:30:00",
    "is_billable": true,
    "tags": ["development", "testing"]
}
```

## 🧪 Testing

### Run All Tests
```bash
php artisan test
```

### Run Specific Test Suite
```bash
php artisan test tests/Feature/AuthTest.php
php artisan test tests/Feature/ClientTest.php
php artisan test tests/Feature/ProjectTest.php
php artisan test tests/Feature/TimeLogTest.php
php artisan test tests/Feature/ReportTest.php
```

### Test Coverage
The project includes comprehensive feature tests covering:
- Authentication flows
- CRUD operations for all entities
- Authorization policies
- Report generation
- PDF export functionality

## 🔧 Development

### Demo Users
After running seeders, you can use these demo accounts:

**Admin User:**
- Email: `admin@freelancer-tracker.com`
- Password: `password`

**Freelancer User:**
- Email: `john@freelancer-tracker.com`
- Password: `password`

### Seeded Data
The seeders create:
- 5 demo users
- 2-4 clients per user
- 2-3 projects per client
- 10+ time logs with realistic data

### Architecture

**Controllers**: Handle HTTP requests and responses
```
app/Http/Controllers/
├── Api/AuthController.php
├── Api/ReportController.php
├── ClientController.php
├── ProjectController.php
└── TimeLogController.php
```

**Form Requests**: Validate incoming data
```
app/Http/Requests/
├── ClientRequest.php
├── LoginRequest.php
├── ProjectRequest.php
├── RegisterRequest.php
└── TimeLogRequest.php
```

**API Resources**: Transform model data for API responses
```
app/Http/Resources/
├── ClientResource.php
├── ProjectResource.php
├── TimeLogResource.php
└── UserResource.php
```

**Actions**: Encapsulate business logic
```
app/Actions/
├── Auth/
│   ├── LoginUserAction.php
│   └── RegisterUserAction.php
└── TimeLog/
    ├── StartTimeLogAction.php
    └── StopTimeLogAction.php
```

**Services**: Handle complex operations
```
app/Services/
└── ReportService.php
```

**Policies**: Authorization logic
```
app/Policies/
├── ClientPolicy.php
├── ProjectPolicy.php
└── TimeLogPolicy.php
```

## 📧 Email Notifications

The system automatically sends email notifications when a user logs 8+ hours in a single day. Configure your mail settings in `.env`:

```env
MAIL_MAILER=smtp
MAIL_HOST=mailpit
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="hello@freelancer-tracker.com"
MAIL_FROM_NAME="${APP_NAME}"
```

## 📦 Postman Collection

Import the Postman collection from `postman/Freelancer_Time_Tracker_API.postman_collection.json` to test all API endpoints with:
- Pre-configured authentication
- Example requests and responses
- Environment variables for easy testing

## 🚀 Deployment

### Production Environment Variables
```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.com

# Database
DB_CONNECTION=mysql
DB_HOST=your-db-host
DB_DATABASE=your-db-name
DB_USERNAME=your-db-user
DB_PASSWORD=your-db-password

# Mail
MAIL_MAILER=smtp
MAIL_HOST=your-smtp-host
MAIL_PORT=587
MAIL_USERNAME=your-smtp-user
MAIL_PASSWORD=your-smtp-password
MAIL_ENCRYPTION=tls
```

### Deployment Steps
```bash
# Install dependencies
composer install --no-dev --optimize-autoloader

# Generate application key
php artisan key:generate

# Run migrations
php artisan migrate --force

# Cache configuration
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Set proper permissions
chmod -R 755 storage bootstrap/cache
```

## 🛡️ Security

- All routes are protected by Sanctum authentication
- Policy-based authorization ensures users can only access their own data
- Input validation using Form Request classes
- CSRF protection for web routes
- SQL injection protection via Eloquent ORM

## 📄 License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## 📞 Support

For support, email support@freelancer-tracker.com or create an issue in the repository.

---

**Built with ❤️ using Laravel 12**

## About Laravelcenter"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

You may also try the [Laravel Bootcamp](https://bootcamp.laravel.com), where you will be guided through building a modern Laravel application from scratch.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Redberry](https://redberry.international/laravel-development)**
- **[Active Logic](https://activelogic.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
