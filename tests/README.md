# Small Login System - Test Suite

This directory contains comprehensive tests for the Small Login System, covering unit tests, integration tests, and security tests.

## Test Structure

### Test Files

- **`ValidateTest.php`** - Tests for the Validate class (email validation, password validation, permissions)
- **`HelperTest.php`** - Tests for the Helper class (utility functions, error messages, redirects)
- **`DbTest.php`** - Tests for the Db class (database operations, setup, queries)
- **`UserTest.php`** - Tests for the User class (login, registration, user management)
- **`IntegrationTest.php`** - End-to-end integration tests requiring database
- **`SecurityTest.php`** - Security-focused tests checking for vulnerabilities

### Test Categories

1. **Unit Tests** - Test individual methods and classes in isolation
2. **Integration Tests** - Test complete workflows with database interactions
3. **Security Tests** - Test for common security vulnerabilities

## Running Tests

### Prerequisites

1. **PHP 7.4+** with required extensions:
   ```bash
   apt install php php-cli php-mbstring php-xml php-curl php-mysql
   ```

2. **Composer** for dependency management:
   ```bash
   curl -sS https://getcomposer.org/installer | php
   php composer.phar install
   ```

3. **Database** (optional, for integration tests):
   - MySQL/MariaDB server
   - Test database credentials configured in `phpunit.xml`

### Test Commands

```bash
# Run all tests
./vendor/bin/phpunit

# Run specific test file
./vendor/bin/phpunit tests/ValidateTest.php

# Run tests with coverage report
./vendor/bin/phpunit --coverage-text

# Run tests without database-dependent tests
./vendor/bin/phpunit --exclude-group integration

# Run only security tests
./vendor/bin/phpunit tests/SecurityTest.php

# Run with verbose output
./vendor/bin/phpunit --verbose

# Use the custom test runner
php run-tests.php
```

## Test Configuration

### PHPUnit Configuration (`phpunit.xml`)

- **Bootstrap**: `tests/bootstrap.php` - Sets up test environment
- **Test Directory**: `tests/` - All test files
- **Coverage**: Includes `classes/` directory, excludes `Autoloader.php`
- **Environment Variables**: Database configuration for integration tests

### Environment Variables

Set these in `phpunit.xml` or as environment variables:

```xml
<env name="DB_HOST" value="localhost"/>
<env name="DB_NAME" value="test_small_login"/>
<env name="DB_USER" value="test_user"/>
<env name="DB_PASS" value="test_pass"/>
```

## Known Issues and Limitations

### Current Code Issues Identified by Tests

1. **Deprecated Functions** (`Validate.php`):
   - Uses deprecated `ereg()` functions (removed in PHP 7+)
   - **Fix**: Replace with `preg_match()`

2. **SQL Injection Vulnerabilities**:
   - `User::verify_password()` - Direct string concatenation in SQL
   - `User::get_user_info()` - Vulnerable to SQL injection
   - **Fix**: Use prepared statements

3. **Session Security**:
   - Missing `httpOnly` and `secure` flags on cookies
   - **Fix**: Add security flags to `setcookie()` calls

4. **Input Validation**:
   - Limited XSS protection
   - **Fix**: Implement comprehensive input sanitization

### Test Limitations

1. **Database Tests**: Require MySQL/MariaDB connection
2. **Session Tests**: Limited due to CLI environment
3. **Header Tests**: Cannot test redirects in CLI environment
4. **File System Tests**: Limited file inclusion testing

## Test Coverage

The test suite covers:

✅ **Covered Areas**:
- Input validation and sanitization
- Password hashing and verification
- Error handling and display
- Database schema and setup
- User registration validation
- Basic security checks

⚠️ **Partially Covered**:
- Session management (limited in CLI)
- Cookie handling
- File operations
- HTTP redirects

❌ **Not Covered**:
- Template rendering
- Frontend JavaScript
- Apache/web server configuration
- Production environment issues

## Security Testing

The `SecurityTest.php` file specifically tests for:

- **Password Security**: Hashing, strength validation
- **Input Sanitization**: XSS prevention, SQL injection
- **Session Security**: Session regeneration, cookie settings
- **Deprecated Functions**: Use of outdated PHP functions
- **Injection Attacks**: SQL injection, header injection

## Contributing to Tests

### Adding New Tests

1. Create test file in `tests/` directory
2. Extend `PHPUnit\Framework\TestCase`
3. Use `resetGlobalState()` in `setUp()` method
4. Follow naming convention: `ClassNameTest.php`

### Test Best Practices

1. **Isolation**: Each test should be independent
2. **Cleanup**: Use `tearDown()` to clean up after tests
3. **Mocking**: Mock external dependencies (database, sessions)
4. **Assertions**: Use specific assertions with descriptive messages
5. **Documentation**: Comment complex test scenarios

### Example Test Structure

```php
<?php
use PHPUnit\Framework\TestCase;

class ExampleTest extends TestCase
{
    protected function setUp(): void
    {
        resetGlobalState();
        // Test-specific setup
    }

    public function testSomething()
    {
        // Arrange
        $input = 'test data';
        
        // Act
        $result = someFunction($input);
        
        // Assert
        $this->assertEquals('expected', $result);
    }

    protected function tearDown(): void
    {
        // Cleanup
        resetGlobalState();
    }
}
```

## Continuous Integration

For CI/CD pipelines, use:

```bash
# Install dependencies
composer install --no-dev --optimize-autoloader

# Run tests with JUnit output
./vendor/bin/phpunit --log-junit test-results.xml

# Generate coverage report
./vendor/bin/phpunit --coverage-clover coverage.xml
```

## Troubleshooting

### Common Issues

1. **"Call to undefined function ereg()"**:
   - The original code uses deprecated PHP functions
   - Tests document this issue for fixing

2. **Database connection errors**:
   - Check database credentials in `phpunit.xml`
   - Ensure MySQL/MariaDB is running
   - Integration tests will be skipped if DB unavailable

3. **Permission errors**:
   - Ensure write permissions for test database
   - Check file permissions for test files

4. **Memory issues**:
   - Increase PHP memory limit: `php -d memory_limit=256M vendor/bin/phpunit`

### Getting Help

- Check test output for specific error messages
- Review `tests/bootstrap.php` for environment setup
- Examine individual test files for expected behavior
- Use `--verbose` flag for detailed test information