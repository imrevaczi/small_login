# Small Login System

A simple and lightweight PHP-based user authentication system built with MySQL and Bootstrap 4. This project provides a secure foundation for user registration, login, and profile management with modern security practices.

## Features

- **Dual Login Options**: Users can log in with either their email address or username
- **User Registration**: Complete user registration with validation
- **User Profile Management**: Display and manage user information after login
- **Error Handling**: Clear error messages for failed login attempts
- **Responsive Design**: Built with Bootstrap 4 for mobile-friendly interface
- **Secure Authentication**: Password hashing, validation, and SQL injection protection

## Requirements

- PHP 7.0 or higher
- MySQL 5.6 or higher
- Web server (Apache, Nginx, or similar)

## Technology Stack

- **Backend**: PHP with object-oriented architecture
- **Database**: MySQL with prepared statements for security
- **Frontend**: Bootstrap 4 for responsive design
- **Testing**: PHPUnit for comprehensive test coverage

## Database Schema

The system stores user data with the following fields:
- Username
- Email address
- First name
- Last name
- Mobile number
- Password (securely hashed)

## Quick Start

### 1. Database Setup
- Copy [`config/db.sample.php`](config/db.sample.php) to `config/db.php`
- Update the database connection settings in [`config/db.php`](config/db.php)
- Create your MySQL database and ensure proper permissions

### 2. Installation
- Clone or download this repository to your web server directory
- Ensure PHP and MySQL are properly configured and running
- Install dependencies if needed (PHPUnit for testing)

### 3. Launch
- Navigate to [`index.php`](index.php) in your web browser
- The system will automatically create the necessary database tables on first use
- Start registering users and testing the login functionality!

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
```
small_login/
├── classes/           # Core PHP classes
│   ├── Db.php        # Database connection and prepared statements
│   ├── User.php      # User authentication and management
│   └── Validate.php  # Input validation and sanitization
├── config/           # Configuration files
│   └── db.sample.php # Database configuration template
├── templates/        # HTML template files
├── tests/           # PHPUnit test files
│   ├── SqlInjectionTest.php  # Security tests
│   └── EregFixTest.php       # Compatibility tests
├── index.php        # Main application entry point
├── settings.php     # Application settings and initialization
└── style.css        # Custom styling
```

## Security Features

This system implements multiple layers of security protection:

- **SQL Injection Prevention**: All database queries use prepared statements with parameter binding
- **Input Validation**: Comprehensive sanitization of all user inputs
- **Password Security**: Secure password hashing using PHP's `password_hash()` function
- **Session Management**: Proper session handling and regeneration
- **XSS Protection**: Input sanitization prevents cross-site scripting attacks

For detailed information about recent security improvements, see [`SECURITY_FIXES.md`](SECURITY_FIXES.md).

## Testing

The project includes comprehensive test coverage:

```bash
# Run all tests
./vendor/bin/phpunit tests/

# Run security-specific tests
./vendor/bin/phpunit tests/SqlInjectionTest.php
```

Tests cover:
- SQL injection prevention
- Input validation and sanitization
- Email validation compatibility
- Security boundary testing

## Contributing

We welcome contributions! Here's how you can help:

1. **Report Issues**: Found a bug or security issue? Please [open an issue](../../issues)
2. **Submit Pull Requests**: Have a fix or improvement? We'd love to review your PR
3. **Security**: For security-related issues, please follow responsible disclosure practices

## License

This project is open source and available under the MIT License.

## Support

If you encounter any issues or have questions:
- Check the existing [issues](../../issues) for solutions
- Create a new issue if your problem isn't already reported
- Review the [`SECURITY_FIXES.md`](SECURITY_FIXES.md) for security-related information

---

**Happy coding!** 🚀 This login system provides a solid foundation for your PHP projects with security best practices built in.
=======
- [`classes/`](classes/) - Contains core PHP classes for database, user management, and validation
- [`config/`](config/) - Database configuration files
- [`templates/`](templates/) - HTML template files for different pages
- [`tests/`](tests/) - Comprehensive test suite (unit, integration, security tests)
- [`index.php`](index.php) - Main entry point
- [`settings.php`](settings.php) - Application settings and initialization
- [`style.css`](style.css) - Custom styling
- [`composer.json`](composer.json) - PHP dependency management
- [`phpunit.xml`](phpunit.xml) - Test configuration
