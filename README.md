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

## Project Structure

- [`classes/`](classes/) - Contains core PHP classes for database, user management, and validation
- [`config/`](config/) - Database configuration files
- [`templates/`](templates/) - HTML template files for different pages
- [`index.php`](index.php) - Main entry point
- [`settings.php`](settings.php) - Application settings and initialization
- [`style.css`](style.css) - Custom styling