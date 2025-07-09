# Small Login System

A simple and lightweight PHP-based user authentication system built with MySQL and Bootstrap 4. This project provides a secure foundation for user registration, login, and profile management with modern security practices—perfect for getting your next project up and running quickly!

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
- Start registering users and testing the login functionality—you're all set!

## Project Structure

```
small_login/
├── classes/              # Core PHP classes
│   ├── [Db.php](classes/Db.php)           # Database connection and prepared statements
│   ├── [User.php](classes/User.php)         # User authentication and management
│   ├── [SessionConfig.php](classes/SessionConfig.php) # Secure session configuration
│   └── [Validate.php](classes/Validate.php)     # Input validation and sanitization
├── config/              # Configuration files
│   └── db.sample.php    # Database configuration template
├── templates/           # HTML template files
├── tests/              # PHPUnit test files
│   ├── [SqlInjectionTest.php](tests/SqlInjectionTest.php)    # SQL injection security tests
│   ├── [SessionSecurityTest.php](tests/SessionSecurityTest.php) # Session security tests
│   └── [EregFixTest.php](tests/EregFixTest.php)         # Compatibility tests
├── index.php           # Main application entry point
├── settings.php        # Application settings and initialization
└── style.css           # Custom styling
```

## Security Features

This system implements multiple layers of security protection to keep your users safe:

- **SQL Injection Prevention**: All database queries use prepared statements with parameter binding
- **Session Security**: Comprehensive session protection with HttpOnly, Secure, and SameSite=Strict cookie flags
- **Input Validation**: Thorough sanitization of all user inputs
- **Password Security**: Secure password hashing using PHP's `password_hash()` function
- **CSRF Protection**: SameSite cookie attributes prevent cross-site request forgery
- **XSS Protection**: Input sanitization and HttpOnly cookies prevent cross-site scripting attacks
- **Session Hijacking Prevention**: Cookie-only sessions with no URL-based session IDs

For detailed information about recent security improvements, see [`SECURITY_FIXES.md`](SECURITY_FIXES.md).

## Testing

The project includes comprehensive test coverage to ensure everything works as expected:

```bash
# Run all tests
./vendor/bin/phpunit tests/

# Run security-specific tests
./vendor/bin/phpunit tests/SqlInjectionTest.php

# Run session security tests
./vendor/bin/phpunit tests/SessionSecurityTest.php
```

Tests cover:
- SQL injection prevention
- Session security and cookie protection
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