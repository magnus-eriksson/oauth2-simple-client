<?php


/**
 * @coversDefaultClass \Maer\Oauth2Simple\Client\AccessControl
 */
class AccessControlTest extends PHPUnit_Framework_TestCase
{
 
    public $accessControl;


    public function __construct()
    {
        $this->accessControl = new Maer\Oauth2Simple\Client\AccessControl;
    }


    /**
    * @covers ::isEmailAllowed
    **/
    public function testAllow()
    {
        $allow = ['email@allowed-email.com', 'email2@allowed-email.com', '@allowed-domain.com'];
        $deny  = [];

        // Use not allowed e-mail
        $result = $this->accessControl->isEmailAllowed($allow, $deny, 'test@not-allowed.com');
        $this->assertFalse($result, "Invalid allowed address");

        // Use allowed e-mail
        $result = $this->accessControl->isEmailAllowed($allow, $deny, 'email@allowed-email.com');
        $this->assertTrue($result, "Valid allowed address");

        // Use allowed domain
        $result = $this->accessControl->isEmailAllowed($allow, $deny, 'anything@allowed-domain.com');
        $this->assertTrue($result, "Valid domain");

    }

 
    /**
    * @covers ::isEmailAllowed
    **/
    public function testDeny()
    {
        $allow = [];
        $deny  = ['email@denied-email.com', 'email2@denied-email.com', '@denied-domain.com'];

        // Use invalid e-mail
        $result = $this->accessControl->isEmailAllowed($allow, $deny, 'email@denied-email.com');
        $this->assertFalse($result, "Denied address");

        // Use valid e-mail
        $result = $this->accessControl->isEmailAllowed($allow, $deny, 'anything@anything.com');
        $this->assertTrue($result, "Allowed address");

        // Use denied domain
        $result = $this->accessControl->isEmailAllowed($allow, $deny, 'anything@denied-domain.com');
        $this->assertFalse($result, "Denied domain");
    }

}