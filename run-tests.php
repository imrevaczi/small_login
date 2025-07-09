<?php
/**
 * Simple test runner script for the Small Login System
 * This script runs PHPUnit tests and provides a summary
 */

echo "=== Small Login System Test Suite ===\n";
echo "Running tests...\n\n";

// Run PHPUnit tests
$command = './vendor/bin/phpunit --colors=always --verbose';
$output = [];
$returnCode = 0;

exec($command . ' 2>&1', $output, $returnCode);

// Display output
foreach ($output as $line) {
    echo $line . "\n";
}

echo "\n=== Test Summary ===\n";
if ($returnCode === 0) {
    echo "✅ All tests passed!\n";
} else {
    echo "❌ Some tests failed (exit code: $returnCode)\n";
}

echo "\n=== Available Test Commands ===\n";
echo "• Run all tests: ./vendor/bin/phpunit\n";
echo "• Run specific test: ./vendor/bin/phpunit tests/ValidateTest.php\n";
echo "• Run with coverage: ./vendor/bin/phpunit --coverage-text\n";
echo "• Run only unit tests: ./vendor/bin/phpunit --exclude-group integration\n";

exit($returnCode);
?>