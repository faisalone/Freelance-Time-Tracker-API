# Freelance Time Tracker API

A comprehensive Laravel API that allows freelancers to log and manage their work time across clients and projects.

## Features

### Core Functionality
- **User Authentication** - Register/Login/Logout via Laravel Sanctum
- **Client Management** - Manage client information and contacts
- **Project Management** - Create and manage projects for clients
- **Time Tracking** - Start/stop timers and manual time entry
- **Reporting** - Generate detailed reports with multiple grouping options
- **PDF Export** - Export time reports as PDF documents

### Advanced Features
- Real-time time tracking with start/stop functionality
- Manual time log entries with validation
- Comprehensive reporting system (project, client, daily, weekly)
- PDF report generation with multiple grouping options
- User-specific data isolation
- Complete test coverage

## Tech Stack

- **Backend**: Laravel 11
- **Authentication**: Laravel Sanctum
- **Database**: MySQL/SQLite
- **PDF Generation**: DomPDF
- **Testing**: PHPUnit
- **API Documentation**: Postman Collection

## Requirements

- PHP 8.1+
- Composer
- MySQL 5.7+ or SQLite
- Node.js & NPM (for asset compilation)

## Installation

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd "Freelancer Time Tracker API"
   ```

2. **Install dependencies**
   ```bash
   composer install
   ```

3. **Environment setup**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Database configuration**
   ```bash
   # Configure your database in .env file
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=freelancer_time_tracker
   DB_USERNAME=your_username
   DB_PASSWORD=your_password
   ```

5. **Run migrations and seeders**
   ```bash
   php artisan migrate
   php artisan db:seed
   ```

6. **Start the development server**
   ```bash
   php artisan serve
   ```

The API will be available at `http://localhost:8000`

## Database Structure

### Users
- `id` - Primary key
- `name` - User's full name
- `email` - Email address (unique)
- `password` - Hashed password
- `timestamps`

### Clients
- `id` - Primary key
- `user_id` - Foreign key to users
- `name` - Client company name
- `email` - Client email
- `contact_person` - Contact person name
- `timestamps`

### Projects
- `id` - Primary key
- `user_id` - Foreign key to users
- `client_id` - Foreign key to clients
- `title` - Project title
- `description` - Project description
- `status` - Project status (active, completed)
- `deadline` - Project deadline
- `timestamps`

### Time Logs
- `id` - Primary key
- `user_id` - Foreign key to users
- `project_id` - Foreign key to projects
- `start_time` - Start timestamp
- `end_time` - End timestamp (nullable for running logs)
- `description` - Work description
- `hours` - Calculated hours (decimal)
- `timestamps`

## API Endpoints

### Authentication
```
POST /api/auth/register     - Register new user
POST /api/auth/login        - Login user
POST /api/auth/logout       - Logout user
GET  /api/auth/me           - Get current user
```

### Clients
```
GET    /api/clients         - Get all clients
POST   /api/clients         - Create client
GET    /api/clients/{id}    - Get specific client
PUT    /api/clients/{id}    - Update client
DELETE /api/clients/{id}    - Delete client
```

### Projects
```
GET    /api/projects        - Get all projects
POST   /api/projects        - Create project
GET    /api/projects/{id}   - Get specific project
PUT    /api/projects/{id}   - Update project
DELETE /api/projects/{id}   - Delete project
```

### Time Logs
```
GET    /api/time-logs           - Get all time logs
POST   /api/time-logs           - Create manual time log
GET    /api/time-logs/{id}      - Get specific time log
PUT    /api/time-logs/{id}      - Update time log
DELETE /api/time-logs/{id}      - Delete time log
POST   /api/time-logs/start     - Start time tracking
GET    /api/time-logs/running   - Get running time logs
POST   /api/time-logs/{id}/stop - Stop time tracking
```

### Reports
```
GET /api/reports                    - Get reports (requires type parameter)
GET /api/reports/summary            - Get user summary
GET /api/reports/client/{id}        - Get client-specific report
GET /api/reports/export/pdf         - Export PDF report
```

#### Report Types
- `project` - Hours grouped by project
- `client` - Hours grouped by client
- `daily` - Hours grouped by day
- `weekly` - Hours grouped by week

#### Report Filters
- `from` - Start date (YYYY-MM-DD)
- `to` - End date (YYYY-MM-DD)
- `client_id` - Filter by specific client
- `project_id` - Filter by specific project

## Usage Examples

### Authentication
```bash
# Register
curl -X POST http://localhost:8000/api/auth/register \
  -H "Content-Type: application/json" \
  -d '{"name":"John Doe","email":"john@example.com","password":"password123","password_confirmation":"password123"}'

# Login
curl -X POST http://localhost:8000/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"john@example.com","password":"password123"}'
```

### Time Tracking
```bash
# Start tracking
curl -X POST http://localhost:8000/api/time-logs/start \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"project_id":1,"description":"Working on homepage"}'

# Stop tracking
curl -X POST http://localhost:8000/api/time-logs/1/stop \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"description":"Completed homepage wireframes"}'
```

### Reports
```bash
# Get project reports
curl -X GET "http://localhost:8000/api/reports?type=project&from=2025-01-01&to=2025-01-31" \
  -H "Authorization: Bearer YOUR_TOKEN"

# Export PDF
curl -X GET "http://localhost:8000/api/reports/export/pdf?group_by=project&start_date=2025-01-01&end_date=2025-01-31" \
  -H "Authorization: Bearer YOUR_TOKEN" \
  --output report.pdf
```

## Testing

Run the test suite:
```bash
# Run all tests
php artisan test

# Run specific test file
php artisan test tests/Feature/TimeLogTest.php

# Run with coverage
php artisan test --coverage
```

## Postman Collection

A comprehensive Postman collection is included at:
```
/postman/Freelancer-Time-Tracker-API.postman_collection.json
```

### Collection Features
- Pre-configured environment variables
- Automatic token management
- Complete API endpoint coverage
- Sample request payloads
- Multiple report examples

### Using the Collection
1. Import the collection into Postman
2. Update the `base_url` variable if needed (default: `http://localhost:8000/api`)
3. Start with Authentication → Login to get a token
4. All subsequent requests will automatically use the stored token

## Seeded Data

The database seeder creates:
- 1 test user (`test@example.com` / `password`)
- 2 sample clients
- 3 sample projects
- 10+ time log entries

## Security Features

- Laravel Sanctum token-based authentication
- User-specific data isolation
- Input validation on all endpoints
- CSRF protection
- Rate limiting on authentication endpoints

## Error Handling

The API returns consistent JSON error responses:
```json
{
    "message": "Error description",
    "errors": {
        "field": ["Validation error message"]
    }
}
```

Common HTTP status codes:
- `200` - Success
- `201` - Created
- `400` - Bad Request
- `401` - Unauthorized
- `403` - Forbidden
- `404` - Not Found
- `422` - Validation Error
- `500` - Server Error

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests for new functionality
5. Ensure all tests pass
6. Submit a pull request

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
