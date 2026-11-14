<?php
use PHPUnit\Framework\TestCase;

class SessionAndSecurityTest extends TestCase
{
    protected function setUp(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }
    }

    public function testCsrfTokenGenerationAndVerification()
    {
        $token = generateCsrfToken();
        $this->assertNotEmpty($token);
        $this->assertEquals(64, strlen($token));
        $this->assertTrue(verifyCsrfToken($token));
        $this->assertFalse(verifyCsrfToken('invalid_token_string_here_1234567890'));
        $this->assertFalse(verifyCsrfToken(''));
    }

    public function testCsrfFieldHelper()
    {
        $field = csrfField();
        $this->assertStringContainsString('name="' . CSRF_TOKEN_NAME . '"', $field);
        $this->assertStringContainsString('type="hidden"', $field);
    }

    public function testRoleCheckHelpers()
    {
        $_SESSION['user_id'] = 999;
        $_SESSION['user_role'] = 'admin';
        $this->assertTrue(isAdmin());
        $this->assertFalse(isStaff());
        $this->assertFalse(isUser());
        $this->assertTrue(isStaffOrAdmin());

        $_SESSION['user_role'] = 'staff';
        $this->assertFalse(isAdmin());
        $this->assertTrue(isStaff());
        $this->assertFalse(isUser());
        $this->assertTrue(isStaffOrAdmin());

        $_SESSION['user_role'] = 'user';
        $this->assertFalse(isAdmin());
        $this->assertFalse(isStaff());
        $this->assertTrue(isUser());
        $this->assertFalse(isStaffOrAdmin());
    }

    public function testFlashMessaging()
    {
        setFlash('success', 'Operation completed successfully.');
        $flash = getFlash();
        $this->assertNotNull($flash);
        $this->assertEquals('success', $flash['type']);
        $this->assertEquals('Operation completed successfully.', $flash['message']);

        // getFlash clears the flash message
        $this->assertNull(getFlash());
    }

    public function testDashboardUrlResolution()
    {
        $this->assertStringContainsString('admin/dashboard.php', getDashboardUrl('admin'));
        $this->assertStringContainsString('staff/dashboard.php', getDashboardUrl('staff'));
        $this->assertStringContainsString('user/dashboard.php', getDashboardUrl('user'));
    }
}

