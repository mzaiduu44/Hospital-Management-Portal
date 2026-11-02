# Healthcare Portal - Laravel Backend

A complete Laravel 11 API backend for the Covid Vaccination Management System.

## Requirements

- PHP 8.2 or higher
- Composer
- MySQL 8.0+ or MariaDB 10.5+
- Node.js (optional, for frontend)

## Quick Setup

### 1. Install Dependencies

```bash
cd backend
composer install
```

### 2. Configure Environment

```bash
cp .env.example .env
php artisan key:generate
```

Edit `.env` file and set your database credentials:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=healthcare_portal
DB_USERNAME=root
DB_PASSWORD=your_password
```

### 3. Database Setup

**Option A: Using Laravel Migrations (Recommended)**

```bash
php artisan migrate
php artisan db:seed
```

**Option B: Using SQL File Directly**

Import `database.sql` directly into MySQL:

```bash
mysql -u root -p < database.sql
```

### 4. Start Development Server

```bash
php artisan serve
```

The API will be available at `http://localhost:8000/api`

## Demo Credentials

| Role     | Email/Username        | Password  |
|----------|----------------------|-----------|
| Patient  | rahul@demo.com       | demo123   |
| Hospital | city@hospital.com    | demo123   |
| Admin    | admin                | admin123  |

## API Endpoints

### Authentication

| Method | Endpoint                      | Description              |
|--------|-------------------------------|--------------------------|
| POST   | /api/auth/patient/register    | Register new patient     |
| POST   | /api/auth/patient/login       | Patient login            |
| POST   | /api/auth/hospital/register   | Register new hospital    |
| POST   | /api/auth/hospital/login      | Hospital login           |
| POST   | /api/auth/admin/login         | Admin login              |
| POST   | /api/auth/logout              | Logout (requires auth)   |
| GET    | /api/auth/me                  | Get current user         |

### Patients

| Method | Endpoint                        | Description                 |
|--------|---------------------------------|-----------------------------|
| GET    | /api/patients                   | List all patients           |
| GET    | /api/patients/{id}              | Get patient details         |
| PUT    | /api/patients/{id}              | Update patient              |
| DELETE | /api/patients/{id}              | Delete patient              |
| GET    | /api/patients/{id}/appointments | Get patient's appointments  |
| GET    | /api/patients/{id}/covid-tests  | Get patient's covid tests   |
| GET    | /api/patients/{id}/vaccinations | Get patient's vaccinations  |

### Hospitals

| Method | Endpoint                         | Description                  |
|--------|----------------------------------|------------------------------|
| GET    | /api/hospitals                   | List all hospitals           |
| GET    | /api/hospitals/{id}              | Get hospital details         |
| PUT    | /api/hospitals/{id}              | Update hospital              |
| PUT    | /api/hospitals/{id}/status       | Update hospital status       |
| DELETE | /api/hospitals/{id}              | Delete hospital              |
| GET    | /api/hospitals/{id}/appointments | Get hospital's appointments  |
| GET    | /api/hospitals/cities            | Get list of cities           |

### Appointments

| Method | Endpoint                        | Description                |
|--------|---------------------------------|----------------------------|
| GET    | /api/appointments               | List all appointments      |
| POST   | /api/appointments               | Create appointment         |
| GET    | /api/appointments/{id}          | Get appointment details    |
| PUT    | /api/appointments/{id}          | Update appointment         |
| PUT    | /api/appointments/{id}/status   | Update appointment status  |
| DELETE | /api/appointments/{id}          | Delete appointment         |

### Covid Tests

| Method | Endpoint                        | Description            |
|--------|---------------------------------|------------------------|
| GET    | /api/covid-tests                | List all covid tests   |
| POST   | /api/covid-tests                | Record covid test      |
| GET    | /api/covid-tests/{id}           | Get test details       |
| PUT    | /api/covid-tests/{id}           | Update test            |
| DELETE | /api/covid-tests/{id}           | Delete test            |

### Vaccinations

| Method | Endpoint                        | Description              |
|--------|---------------------------------|--------------------------|
| GET    | /api/vaccinations               | List all vaccinations    |
| POST   | /api/vaccinations               | Record vaccination       |
| GET    | /api/vaccinations/{id}          | Get vaccination details  |
| PUT    | /api/vaccinations/{id}          | Update vaccination       |
| DELETE | /api/vaccinations/{id}          | Delete vaccination       |

### Vaccines

| Method | Endpoint                           | Description              |
|--------|------------------------------------|--------------------------|
| GET    | /api/vaccines                      | List all vaccines        |
| POST   | /api/vaccines                      | Create vaccine           |
| GET    | /api/vaccines/{id}                 | Get vaccine details      |
| PUT    | /api/vaccines/{id}                 | Update vaccine           |
| PUT    | /api/vaccines/{id}/availability    | Toggle availability      |
| DELETE | /api/vaccines/{id}                 | Delete vaccine           |

### Dashboard

| Method | Endpoint                        | Description              |
|--------|---------------------------------|--------------------------|
| GET    | /api/dashboard/stats            | Get dashboard statistics |
| GET    | /api/dashboard/recent-appointments | Get recent appointments |
| GET    | /api/dashboard/monthly-stats    | Get monthly statistics   |
| GET    | /api/dashboard/city-stats       | Get city-wise statistics |

## Project Structure

```
backend/
├── app/
│   ├── Helpers/
│   │   └── ApiResponse.php         # API response helpers
│   ├── Http/
│   │   ├── Controllers/
│   │   │   └── Api/
│   │   │       ├── AuthController.php
│   │   │       ├── PatientController.php
│   │   │       ├── HospitalController.php
│   │   │       ├── AppointmentController.php
│   │   │       ├── CovidTestController.php
│   │   │       ├── VaccinationController.php
│   │   │       ├── VaccineController.php
│   │   │       └── DashboardController.php
│   │   └── Middleware/
│   └── Models/
│       ├── Patient.php
│       ├── Hospital.php
│       ├── Admin.php
│       ├── Appointment.php
│       ├── CovidTest.php
│       ├── Vaccination.php
│       └── Vaccine.php
├── config/
│   ├── app.php
│   ├── cors.php
│   ├── database.php
│   └── sanctum.php
├── database/
│   ├── migrations/
│   └── seeders/
│       └── DatabaseSeeder.php
├── routes/
│   └── api.php
├── .env.example
├── composer.json
├── database.sql                    # Complete SQL schema
└── README.md
```

## CORS Configuration

The backend is configured to accept requests from:
- `http://localhost:5173` (Vite dev server)
- `http://localhost:3000` (Alternative dev server)

To add more origins, edit `config/cors.php`.

## License

This project is open-sourced software licensed under the MIT license.
