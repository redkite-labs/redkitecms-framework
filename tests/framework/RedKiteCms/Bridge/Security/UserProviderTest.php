<?php
/**
 * This file is part of the RedKite CMS Application and it is distributed
 * under the GPL LICENSE Version 2.0. To use this application you must leave
 * intact this copyright notice.
 *
 * Copyright (c) RedKite Labs <info@redkite-labs.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * For extra documentation and help please visit http://www.redkite-labs.com
 *
 * @license    GPL LICENSE Version 2.0
 *
 */

namespace RedKiteCms\Bridge\Security;

use org\bovigo\vfs\vfsStream;
use RedKiteCms\Bridge\Security\UserProvider;
use RedKiteCms\TestCase;

/**
 * Class UserProviderTest
 *
 * @author RedKite Labs <webmaster@redkite-labs.com>
 */
class UserProviderTest extends TestCase
{
    private $configurationHandler;
    private $userProvider;

    protected function setUp()
    {
        $this->configurationHandler = $this
            ->getMockBuilder('\RedKiteCms\Configuration\ConfigurationHandler')
            ->setMethods(array('siteDir'))
            ->disableOriginalConstructor()
            ->getMock()
        ;



        $this->userProvider = new UserProvider($this->configurationHandler);
    }

    /**
     * @expectedException \Symfony\Component\Security\Core\Exception\UsernameNotFoundException
     * @expectedExceptionMessage Username "john" does not exist.
     */
    public function testUserNotFound()
    {
        $this->init();
        $this->userProvider->loadUserByUsername('john');
    }

    public function testLoadUser()
    {
        $this->init();
        $user = $this->userProvider->loadUserByUsername('admin');
        $this->assertInstanceOf('\RedKiteCms\Bridge\Security\User', $user);
        $this->assertEquals('password', $user->getPassword());
        $this->assertEquals('salt', $user->getSalt());
        $this->assertEquals(array('ROLE_ADMIN'), $user->getRoles());
        $this->assertTrue($user->isEqualTo($user));

        return $user;
    }

    /**
     * @expectedException \Symfony\Component\Security\Core\Exception\UnsupportedUserException
     */
    public function testUserNotRefreshed()
    {
        $user = $this->getMock('\Symfony\Component\Security\Core\User\UserInterface');
        $this->userProvider->refreshUser($user);
    }

    /**
     * @depends testLoadUser
     */
    public function testRefreshUser($user)
    {
        $this->init();
        $user = $this->userProvider->refreshUser($user);
        $this->assertInstanceOf('\RedKiteCms\Bridge\Security\User', $user);
    }

    private function init()
    {
        $this->configurationHandler
            ->expects($this->once())
            ->method('siteDir')
            ->will($this->returnValue(vfsStream::url('root\redkitecms.com')))
        ;

        $folders = array(
            'redkitecms.com' => array(
                'users' => array(
                    'users.json' => '{"admin":{"roles":["ROLE_ADMIN"],"password":"password","salt":"salt"}}'
                ),
            ),
        );
        $this->root = vfsStream::setup('root', null, $folders);
    }
}
