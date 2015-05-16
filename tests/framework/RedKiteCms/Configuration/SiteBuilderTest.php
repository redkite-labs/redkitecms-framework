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

namespace RedKiteCms\Configuration;

use org\bovigo\vfs\vfsStream;
use RedKiteCms\TestCase;

/**
 * Class SiteBuilderTest
 *
 * @author RedKite Labs <webmaster@redkite-labs.com>
 */
class SiteBuilderTest extends TestCase
{
    public function testBuildSite()
    {
        $folders = array(
            'RedKiteCMS' => array(
                'app' => array(
                    'cache' => array(),
                    'config' => array(),
                    'data' => array(
                        'redkitecms.com' => array(
                            'config' => array(),
                            'pages' => array(
                                'pages' => array(),
                            ),
                            'roles' => array(),
                            'slots' => array(),
                            'users' => array(),
                        ),
                    ),
                    'logs' => array(),
                    'plugins' => array(
                        'RedKiteCms' => array(
                            'Block' => array(),
                            'Theme' => array(),
                        ),
                    ),
                    'RedKiteCms.php' => 'class RedKiteCms extends RedKiteCmsBase{}',
                ),
                'src' => array(),
                'web' => array(),
            ),
        );

        vfsStream::setup('www', null, $folders);
        $siteBuilder = new SiteBuilder(vfsStream::url('www/RedKiteCMS'), 'redkitecms.com');
        $siteBuilder->build();

        $structure = vfsStream::inspect(new \org\bovigo\vfs\visitor\vfsStreamStructureVisitor())->getStructure();
        $siteStructure = array(
            "redkitecms.com" => array(
                "config" => array
                (
                ),
                "pages" => array
                (
                    "pages" => array
                    (
                    ),
                ),
                "roles" => array
                (
                    "roles.json" => '["ROLE_ADMIN"]',
                ),
                "slots" => array
                (
                ),
                "users" => array
                (
                    "users.json" => '{"admin":{"roles":["ROLE_ADMIN"],"password":"RVxE\/NkQGEhSimsAzsmSIwDv1p+lhP5SDT6Gfnh8QS32yk7W6A+pW5GXUBxJ3ud9La5khARoH2uQ5VRYkPG\/Fw==","salt":"q4mfgrnsn2occ4kw4k008cskkwkg800"}}',
                ),
                "RedKiteCms.php" => 'class RedKiteCms extends RedKiteCmsBase{}',
                "site.json" => '{"theme":"SimpleTheme","homepage":"homepage","locale_default":"en_GB","homepage_permalink":"en-gb-homepage","languages":["en_GB"],"handled_theme":""}',
                "incomplete.json" => null,
            ),
        );
        $this->assertEquals($siteStructure, $structure["www"]["RedKiteCMS"]["app"]["data"]);
    }

    public function testBuildSiteHandlingTheme()
    {
        $folders = array(
            'RedKiteCMS' => array(
                'app' => array(
                    'cache' => array(),
                    'config' => array(),
                    'data' => array(
                        'redkitecms.com' => array(
                            'config' => array(),
                            'pages' => array(
                                'pages' => array(),
                            ),
                            'roles' => array(),
                            'slots' => array(),
                            'users' => array(),
                        ),
                    ),
                    'logs' => array(),
                    'plugins' => array(
                        'RedKiteCms' => array(
                            'Block' => array(),
                            'Theme' => array(),
                        ),
                    ),
                    'RedKiteCms.php' => 'class RedKiteCms extends RedKiteCmsBase{}',
                ),
                'src' => array(),
                'web' => array(),
            ),
        );

        vfsStream::setup('www', null, $folders);
        $siteBuilder = new SiteBuilder(vfsStream::url('www/RedKiteCMS'), 'redkitecms.com');
        $siteBuilder
            ->theme("FooTheme")
            ->handleTheme(true)
            ->build();

        $structure = vfsStream::inspect(new \org\bovigo\vfs\visitor\vfsStreamStructureVisitor())->getStructure();
        $siteStructure = array(
            "redkitecms.com" => array(
                "config" => array
                (
                ),
                "pages" => array
                (
                    "pages" => array
                    (
                    ),
                ),
                "roles" => array
                (
                    "roles.json" => '["ROLE_ADMIN"]',
                ),
                "slots" => array
                (
                ),
                "users" => array
                (
                    "users.json" => '{"admin":{"roles":["ROLE_ADMIN"],"password":"RVxE\/NkQGEhSimsAzsmSIwDv1p+lhP5SDT6Gfnh8QS32yk7W6A+pW5GXUBxJ3ud9La5khARoH2uQ5VRYkPG\/Fw==","salt":"q4mfgrnsn2occ4kw4k008cskkwkg800"}}',
                ),
                "RedKiteCms.php" => 'class RedKiteCms extends RedKiteCmsBase{}',
                "site.json" => '{"theme":"FooTheme","homepage":"homepage","locale_default":"en_GB","homepage_permalink":"en-gb-homepage","languages":["en_GB"],"handled_theme":"FooTheme"}',
                "incomplete.json" => null,
            ),
        );
        $this->assertEquals($siteStructure, $structure["www"]["RedKiteCMS"]["app"]["data"]);
    }
}
