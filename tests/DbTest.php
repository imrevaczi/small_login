<?php

use PHPUnit\Framework\TestCase;

class DbTest extends TestCase
{
    private $db;

    protected function setUp(): void
    {
        resetGlobalState();
        $this->db = new Db();
        $GLOBALS['Db'] = $this->db;
    }

    public function testConstructorSetsProperties()
    {
        $this->assertEquals(DB_HOST, $this->getPrivateProperty($this->db, 'dbhost'));
        $this->assertEquals(DB_USER, $this->getPrivateProperty($this->db, 'dbuser'));
        $this->assertEquals(DB_PASS, $this->getPrivateProperty($this->db, 'dbuserpass'));
        $this->assertEquals(DB_NAME, $this->getPrivateProperty($this->db, 'database'));
    }

    public function testToStringReturnsVersion()
    {
        $result = (string) $this->db;
        $this->assertStringContains('Version: 0.9', $result);
    }

    public function testDisplayErrorsWithNoErrors()
    {
        $result = $this->db->display_errors();
        $this->assertEquals('', $result);
    }

    public function testDisplayErrorsWithErrors()
    {
        // Add an error to the private error array
        $this->setPrivateProperty($this->db, 'error', ['Test error 1', 'Test error 2']);
        
        $result = $this->db->display_errors();
        $expected = 'Test error 1<br />Test error 2<br />';
        $this->assertEquals($expected, $result);
    }

    public function testCountErrorsWithNoErrors()
    {
        $result = $this->db->count_errors();
        $this->assertEquals(0, $result);
    }

    public function testCountErrorsWithErrors()
    {
        $this->setPrivateProperty($this->db, 'error', ['Error 1', 'Error 2', 'Error 3']);
        
        $result = $this->db->count_errors();
        $this->assertEquals(3, $result);
    }

    public function testGetUserFieldsStructure()
    {
        // This test requires a database connection, so we'll skip it if no DB is available
        if (!$this->isDatabaseAvailable()) {
            $this->markTestSkipped('Database not available for testing');
        }

        // Set up test database
        $this->setupTestDatabase();
        
        $fields = Db::getUserFields('user');
        
        $this->assertIsArray($fields);
        $this->assertGreaterThan(0, count($fields));
        
        // Check that ID field is excluded
        foreach ($fields as $field) {
            $this->assertNotEquals('ID', $field['Field']);
        }
        
        // Check for expected fields
        $fieldNames = array_column($fields, 'Field');
        $expectedFields = ['username', 'firstname', 'lastname', 'email', 'mobile', 'password'];
        
        foreach ($expectedFields as $expectedField) {
            $this->assertContains($expectedField, $fieldNames);
        }
    }

    public function testCheckSetupCreatesTableIfNotExists()
    {
        if (!$this->isDatabaseAvailable()) {
            $this->markTestSkipped('Database not available for testing');
        }

        // Clean up any existing table
        cleanupTestDatabase();
        
        $result = Db::checkSetup();
        $this->assertTrue($result);
        
        // Verify table was created
        $connection = createTestDbConnection();
        $result = mysqli_query($connection, "SHOW TABLES LIKE 'user'");
        $this->assertEquals(1, mysqli_num_rows($result));
        mysqli_close($connection);
    }

    public function testCheckSetupReturnsTrueIfTableExists()
    {
        if (!$this->isDatabaseAvailable()) {
            $this->markTestSkipped('Database not available for testing');
        }

        // Ensure table exists
        $this->setupTestDatabase();
        
        $result = Db::checkSetup();
        $this->assertTrue($result);
    }

    public function testCreateDatabase()
    {
        if (!$this->isDatabaseAvailable()) {
            $this->markTestSkipped('Database not available for testing');
        }

        $connection = mysqli_connect(DB_HOST, DB_USER, DB_PASS);
        $testDbName = 'test_create_db_' . time();
        
        $result = Db::createDatabase($connection, $testDbName);
        $this->assertTrue($result);
        
        // Clean up
        mysqli_query($connection, "DROP DATABASE IF EXISTS `$testDbName`");
        mysqli_close($connection);
    }

    public function testQueryDbWithValidQuery()
    {
        if (!$this->isDatabaseAvailable()) {
            $this->markTestSkipped('Database not available for testing');
        }

        $this->setupTestDatabase();
        
        $result = Db::query_db("SELECT 1 as test_value");
        $this->assertNotFalse($result);
        
        $row = mysqli_fetch_assoc($result);
        $this->assertEquals(1, $row['test_value']);
        mysqli_free_result($result);
    }

    public function testQueryDbWithInsertQuery()
    {
        if (!$this->isDatabaseAvailable()) {
            $this->markTestSkipped('Database not available for testing');
        }

        $this->setupTestDatabase();
        
        $query = "INSERT INTO user (username, firstname, lastname, email, mobile, password) 
                  VALUES ('testuser', 'Test', 'User', 'test@example.com', '1234567890', 'hashedpass')";
        
        $result = Db::query_db($query);
        $this->assertIsInt($result); // Should return insert ID
        $this->assertGreaterThan(0, $result);
    }

    private function isDatabaseAvailable()
    {
        try {
            $connection = mysqli_connect(DB_HOST, DB_USER, DB_PASS);
            if ($connection) {
                mysqli_close($connection);
                return true;
            }
        } catch (Exception $e) {
            // Database not available
        }
        return false;
    }

    private function setupTestDatabase()
    {
        $connection = createTestDbConnection();
        
        // Create user table
        $sql = 'CREATE TABLE IF NOT EXISTS `user` (
            `ID` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
            `username` varchar(100) NOT NULL,
            `firstname` varchar(100) NOT NULL,
            `lastname` varchar(100) NOT NULL,
            `email` varchar(100) NOT NULL,
            `mobile` varchar(100) NOT NULL,
            `password` varchar(100) NOT NULL
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8;';
        
        mysqli_query($connection, $sql);
        mysqli_close($connection);
    }

    private function getPrivateProperty($object, $property)
    {
        $reflection = new ReflectionClass($object);
        $prop = $reflection->getProperty($property);
        $prop->setAccessible(true);
        return $prop->getValue($object);
    }

    private function setPrivateProperty($object, $property, $value)
    {
        $reflection = new ReflectionClass($object);
        $prop = $reflection->getProperty($property);
        $prop->setAccessible(true);
        $prop->setValue($object, $value);
    }

    protected function tearDown(): void
    {
        cleanupTestDatabase();
        resetGlobalState();
        unset($GLOBALS['Db']);
    }
}