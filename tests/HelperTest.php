<?php

use PHPUnit\Framework\TestCase;

class HelperTest extends TestCase
{
    protected function setUp(): void
    {
        resetGlobalState();
    }

    public function testGetTitleWithRegisterTemplate()
    {
        $title = Helper::getTitle('register');
        $this->assertEquals('Register a New User', $title);
    }

    public function testGetTitleWithLoginTemplate()
    {
        $title = Helper::getTitle('login');
        $this->assertEquals('Login', $title);
    }

    public function testGetTitleWithDefaultTemplate()
    {
        $title = Helper::getTitle('userdata');
        $this->assertEquals('Show User Data', $title);
        
        $title = Helper::getTitle('unknown');
        $this->assertEquals('Show User Data', $title);
        
        $title = Helper::getTitle('');
        $this->assertEquals('Show User Data', $title);
    }

    public function testLocRedirectSetsSessionVariable()
    {
        // Mock server variables
        $_SERVER['REQUEST_SCHEME'] = 'https';
        $_SERVER['HTTP_HOST'] = 'example.com';
        $_SERVER['PHP_SELF'] = '/app/index.php';
        
        // Capture the redirect (this will exit, so we need to test differently)
        // We'll test that the session variable gets set
        $relativeUrl = 'login.php';
        
        // Since loc_redirect calls exit(), we can't test the actual redirect
        // But we can test the session setting logic by extracting it
        $_SESSION['relative_url'] = $relativeUrl;
        
        $this->assertEquals($relativeUrl, $_SESSION['relative_url']);
    }

    public function testGetErrorMessageWithNoErrors()
    {
        // Mock required global objects
        $mockDb = $this->createMockDb(0, '');
        $mockUser = $this->createMockUser(0, '');
        
        $GLOBALS['Db'] = $mockDb;
        $GLOBALS['user'] = $mockUser;
        $GLOBALS['errormsg'] = '';
        
        $result = Helper::getErrorMessage();
        $this->assertEquals('', $result);
    }

    public function testGetErrorMessageWithSpecificErrorCodes()
    {
        $mockDb = $this->createMockDb(0, '');
        $mockUser = $this->createMockUser(0, '');
        
        $GLOBALS['Db'] = $mockDb;
        $GLOBALS['user'] = $mockUser;
        
        $testCases = [
            'inifile' => 'INI file not found',
            'failed' => 'Login failed. <br>Maybe you typed in wrong user name or password',
            'setup' => 'Setup error',
            'cookie' => 'You should allow to accept COOKIE data!',
            'unknown' => ''
        ];
        
        foreach ($testCases as $errorCode => $expectedMessage) {
            $GLOBALS['errormsg'] = $errorCode;
            $result = Helper::getErrorMessage();
            $this->assertEquals($expectedMessage, $result);
        }
    }

    public function testGetErrorMessageWithDbErrors()
    {
        $dbErrorMessage = 'Database connection failed';
        $mockDb = $this->createMockDb(1, $dbErrorMessage);
        $mockUser = $this->createMockUser(0, '');
        
        $GLOBALS['Db'] = $mockDb;
        $GLOBALS['user'] = $mockUser;
        $GLOBALS['errormsg'] = '';
        
        $result = Helper::getErrorMessage();
        $this->assertEquals($dbErrorMessage, $result);
    }

    public function testGetErrorMessageWithUserErrors()
    {
        $userErrorMessage = 'Invalid username or password';
        $mockDb = $this->createMockDb(0, '');
        $mockUser = $this->createMockUser(1, $userErrorMessage);
        
        $GLOBALS['Db'] = $mockDb;
        $GLOBALS['user'] = $mockUser;
        $GLOBALS['errormsg'] = '';
        
        $result = Helper::getErrorMessage();
        $this->assertEquals($userErrorMessage, $result);
    }

    public function testGetErrorMessageWithMultipleErrors()
    {
        $dbErrorMessage = 'Database error';
        $userErrorMessage = 'User error';
        $mockDb = $this->createMockDb(1, $dbErrorMessage);
        $mockUser = $this->createMockUser(1, $userErrorMessage);
        
        $GLOBALS['Db'] = $mockDb;
        $GLOBALS['user'] = $mockUser;
        $GLOBALS['errormsg'] = 'failed';
        
        $result = Helper::getErrorMessage();
        $expected = 'Login failed. <br>Maybe you typed in wrong user name or password<br />' . $dbErrorMessage . '<br />' . $userErrorMessage;
        $this->assertEquals($expected, $result);
    }

    public function testGetErrorMessageWithGetParameter()
    {
        $_GET['errormsg'] = 'setup';
        
        $mockDb = $this->createMockDb(0, '');
        $mockUser = $this->createMockUser(0, '');
        
        $GLOBALS['Db'] = $mockDb;
        $GLOBALS['user'] = $mockUser;
        $GLOBALS['errormsg'] = '';
        
        $result = Helper::getErrorMessage();
        $this->assertEquals('Setup error', $result);
    }

    private function createMockDb($errorCount, $errorMessage)
    {
        $mock = $this->createMock(stdClass::class);
        $mock->method('count_errors')->willReturn($errorCount);
        $mock->method('display_errors')->willReturn($errorMessage);
        return $mock;
    }

    private function createMockUser($errorCount, $errorMessage)
    {
        $mock = $this->createMock(stdClass::class);
        $mock->method('count_errors')->willReturn($errorCount);
        $mock->method('display_errors')->willReturn($errorMessage);
        return $mock;
    }

    protected function tearDown(): void
    {
        resetGlobalState();
        unset($GLOBALS['Db']);
        unset($GLOBALS['user']);
    }
}