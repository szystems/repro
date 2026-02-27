# REPRO - Polygraph Evaluation Management Platform

<p align="center">
  <img src="https://img.shields.io/badge/Laravel-12-red.svg" alt="Laravel 12">
  <img src="https://img.shields.io/badge/PHP-8.3-blue.svg" alt="PHP 8.3">
  <img src="https://img.shields.io/badge/PHPUnit-11-green.svg" alt="PHPUnit 11">
  <img src="https://img.shields.io/badge/Psalm-Static%20Analysis-purple.svg" alt="Psalm">
  <img src="https://img.shields.io/badge/Docker-Ready-2496ED.svg" alt="Docker">
</p>

A comprehensive Laravel platform for managing polygraph evaluations, VSA (Voice Stress Analysis), and socioeconomic assessments. Built for REPRO Guatemala with a multi-role architecture, dynamic questionnaire engine, order lifecycle management, and PDF/Excel reporting.

## Key Features

- **Order Management** — Full lifecycle tracking of evaluation orders with multi-evaluee support per order, status workflows, and company assignment
- **Dynamic Questionnaire Engine** — 5-section structured questionnaires with real-time auto-save, progress tracking, digital signatures, and conditional field logic
- **Role-Based Access Control** — Granular permission system (26 permissions across 8 modules) with Admin, Staff, and Company roles
- **Document Processing** — PDF report generation (DomPDF) and Excel data exports (Maatwebsite/Excel)
- **Email Notifications** — Automated email workflows for order status changes and evaluation completions
- **Multi-Tenant Companies** — Companies manage their own orders and view only their evaluation results
- **Evaluee Portal** — Token-based temporary access for evaluees to complete questionnaires via DPI (national ID) without registration

## Tech Stack

| Layer | Technology |
|-------|-----------|
| **Framework** | Laravel 12 (upgraded from 8) |
| **Language** | PHP 8.3 |
| **Database** | MySQL 8.0+ |
| **Auth** | Laravel Sanctum + Custom Role/Permission System |
| **Frontend** | Blade Templates + Bootstrap 5 + jQuery |
| **PDF/Excel** | DomPDF, Maatwebsite/Excel |
| **Static Analysis** | Psalm |
| **Testing** | PHPUnit 11 (13+ Feature Tests) |
| **DevOps** | Docker + Docker Compose |

## Architecture

```
app/
├── Console/           # Artisan commands
├── Exceptions/        # Custom exception handlers
├── Exports/           # Excel export classes (Maatwebsite)
├── Http/
│   ├── Controllers/   # API & web controllers
│   ├── Middleware/     # Auth, role-checking middleware
│   └── Requests/      # Form Request validation classes
├── Mail/              # Mailable classes for notifications
├── Models/
│   ├── Cuestionario.php       # Questionnaire with progress tracking
│   ├── CuestionarioRespuesta.php  # Individual questionnaire answers
│   ├── DocumentoEvaluado.php  # Uploaded evaluation documents
│   ├── Empresa.php            # Client companies
│   ├── EvaluadoOrden.php      # Evaluees linked to orders
│   ├── FormularioCampo.php    # Dynamic form field definitions
│   ├── Orden.php              # Evaluation orders (core entity)
│   ├── Sede.php               # Company branch locations
│   ├── Role.php / Permission.php  # RBAC models
│   └── User.php               # Multi-role authenticated users
├── Providers/         # Service providers
└── Traits/            # Shared model behaviors

tests/
├── Feature/
│   ├── CuestionarioTest.php           # Questionnaire CRUD & flow
│   ├── CuestionarioDatosPersonalesTest.php  # Personal data validation
│   ├── CuestionarioModuloCompletoTest.php   # Full module integration
│   ├── DashboardTest.php              # Dashboard access & metrics
│   ├── EmpresaModulosTest.php         # Company module permissions
│   ├── NotificacionesEmailTest.php    # Email notification testing
│   ├── OrdenesControllerTest.php      # Order CRUD operations
│   ├── OrdenesCreateFormTest.php      # Order creation validation
│   ├── ReportesTest.php               # Report generation
│   ├── ResultadosVisibilidadTest.php  # Result visibility by role
│   └── SedesTest.php                  # Branch location management
└── Unit/
```

## Getting Started

### With Docker

```bash
git clone https://github.com/szystems/repro.git
cd repro
docker-compose up -d
docker exec -it repro-app composer install
docker exec -it repro-app php artisan migrate --seed
```

### Local Development

```bash
git clone https://github.com/szystems/repro.git
cd repro
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
npm install && npm run build
php artisan serve
```

## Testing

```bash
# Run all tests
php artisan test

# Run feature tests only
php artisan test --filter=Feature

# Run specific test
php artisan test --filter=OrdenesControllerTest

# Static analysis
./vendor/bin/psalm
```

## Author

**Otto Szarata** — Senior Full-Stack Developer  
[GitHub](https://github.com/szystems) · Victoria, BC, Canada

## License

This project is proprietary software. All rights reserved.
