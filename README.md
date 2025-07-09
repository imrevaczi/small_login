# Small Login System

A simple and lightweight PHP-based user authentication system built with MySQL and Bootstrap 4.

## Features

- **Dual Login Options**: Users can log in with either their email address or username
- **User Registration**: Complete user registration with validation
- **User Profile Management**: Display and manage user information after login
- **Error Handling**: Clear error messages for failed login attempts
- **Responsive Design**: Built with Bootstrap 4 for mobile-friendly interface
- **Secure Authentication**: Password hashing and validation

## Technology Stack

- **Backend**: PHP
- **Database**: MySQL
- **Frontend**: Bootstrap 4
- **Architecture**: Object-oriented PHP with custom classes

## Database Schema

The system stores user data with the following fields:
- Username
- Email address
- First name
- Last name
- Mobile number
- Password (securely hashed)

## Setup Instructions

1. **Database Configuration**: 
   - Copy [`config/db.sample.php`](config/db.sample.php) to `config/db.php`
   - Update the database connection settings in `config/db.php`

2. **Web Server**: 
   - Place the project files in your web server directory
   - Ensure PHP and MySQL are properly configured

3. **Access**: 
   - Navigate to [`index.php`](index.php) in your web browser to start using the system

## Testing

This project includes a comprehensive test suite with 60+ tests covering:

- **Unit Tests** - Individual class and method testing
- **Integration Tests** - End-to-end workflow testing
- **Security Tests** - Vulnerability and security analysis

### Running Tests

```bash
# Install dependencies
composer install

# Run all tests
./vendor/bin/phpunit

# Run specific test file
./vendor/bin/phpunit tests/ValidateTest.php

# Run with coverage report
./vendor/bin/phpunit --coverage-text

# Use custom test runner
php run-tests.php
```

### Test Documentation

- [`tests/README.md`](tests/README.md) - Comprehensive test documentation
- [`TEST_RESULTS.md`](TEST_RESULTS.md) - Test results and security analysis
- [`phpunit.xml`](phpunit.xml) - PHPUnit configuration

**Note**: Tests identify several security vulnerabilities in the original code that should be addressed before production use.

## Project Structure

- [`classes/`](classes/) - Contains core PHP classes for database, user management, and validation
- [`config/`](config/) - Database configuration files
- [`templates/`](templates/) - HTML template files for different pages
- [`tests/`](tests/) - Comprehensive test suite (unit, integration, security tests)
- [`index.php`](index.php) - Main entry point
- [`settings.php`](settings.php) - Application settings and initialization
- [`style.css`](style.css) - Custom styling
- [`composer.json`](composer.json) - PHP dependency management
- [`phpunit.xml`](phpunit.xml) - Test configuration