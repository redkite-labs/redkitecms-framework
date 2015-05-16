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
 * Class AsseticFactoryBuilderTest
 *
 * @author RedKite Labs <webmaster@redkite-labs.com>
 */
class ConfigurationHandlerTest extends TestCase
{
    /**
     * @expectedException \RedKiteCms\Exception\General\RuntimeException
     * @expectedExceptionMessage Method "baz" does not exist for ConfigurationHandler object
     */
    public function testPropertyNotHandled()
    {
        $this->init();

        $configurationHandler = new ConfigurationHandler(
            vfsStream::url('RedKiteCMS'),
            'redkitecms.com',
            'vendor/redkitecms-framework'
        );
        $configurationHandler->boot();
        $configurationHandler->baz();
    }

    public function testCheckTheme()
    {
        $this->init('redkitecms.com.theme');

        $configurationHandler = new ConfigurationHandler(
            vfsStream::url('RedKiteCMS'),
            'redkitecms.com.theme',
            'vendor/redkitecms-framework'
        );
        $configurationHandler->boot();
        $this->assertTrue($configurationHandler->isTheme());
    }

    public function testGlobalConfigurationChanged()
    {
        $customConfig = array(
        'assets.json' => '{
            "prod" :{
                "getExternalStylesheets": [
                    "%web_dir%/components/redkitecms/twitter-bootstrap/css/myasset.min.css"
                ]
            },
            "cms" :{
                "getExternalJavascripts": [
                    "%web_dir%/components/redkitecms/myasset.min.js"
                ]
            }
        }');

        $this->init('redkitecms.com', $customConfig);

        $configurationHandler = new ConfigurationHandler(
            vfsStream::url('RedKiteCMS'),
            'redkitecms.com',
            'vendor/redkitecms-framework'
        );
        $configurationHandler->boot();

        $expectedConfiguration = array
        (
            "assets" => array
            (
                "prod" => array
                (
                    "getExternalStylesheets" => array
                    (
                        "vfs://RedKiteCMS/web/components/redkitecms/twitter-bootstrap/css/myasset.min.css",
                    ),
                ),
                "cms" => array
                (
                    "getExternalStylesheets" => array
                    (
                        "vfs://RedKiteCMS/web/plugins/redkitecms/css/skins/redkite/skin.css",
                    ),
                    "getExternalJavascripts" => array
                    (
                        "vfs://RedKiteCMS/web/components/redkitecms/jquery-ui/jquery-ui.min.js",
                        "vfs://RedKiteCMS/web/components/redkitecms/myasset.min.js",
                    ),
                ),
                "dashboard" => array
                (
                    "getExternalStylesheets" => array
                    (
                        "vfs://RedKiteCMS/web/components/redkitecms/twitter-bootstrap/css/bootstrap.min.css",
                    ),
                    "getExternalJavascripts" => array
                    (
                        "vfs://RedKiteCMS/web/components/jquery/jquery.min.js",
                    ),
                ),
            ),
            "general" => array
            (
                "skin" => "RedKiteCms/public/css/skins/redkite",
                "baseTemplate" => "RedKiteCms/Resources/views/Frontend/base.html.twig"
            ),
            "foo" => array(
                "bar"
            )
        );
        $this->assertEquals($expectedConfiguration, $configurationHandler->configuration());
    }

    public function testSiteConfigurationChanged()
    {
        $customConfig = array(
            'assets.json' => '{
            "prod" :{
                "getExternalStylesheets": [
                    "%web_dir%/components/redkitecms/twitter-bootstrap/css/myasset.min.css"
                ]
            },
            "cms" :{
                "getExternalJavascripts": [
                    "%web_dir%/components/redkitecms/myasset.min.js"
                ]
            }
        }');

        $this->init('redkitecms.com', array(), $customConfig);

        $configurationHandler = new ConfigurationHandler(
            vfsStream::url('RedKiteCMS'),
            'redkitecms.com',
            'vendor/redkitecms-framework'
        );
        $configurationHandler->boot();


        $expectedConfiguration = array
        (
            "assets" => array
            (
                "prod" => array
                (
                    "getExternalStylesheets" => array
                    (
                        "vfs://RedKiteCMS/web/components/redkitecms/twitter-bootstrap/css/myasset.min.css",
                    ),
                ),
                "cms" => array
                (
                    "getExternalStylesheets" => array
                    (
                        "vfs://RedKiteCMS/web/plugins/redkitecms/css/skins/redkite/skin.css",
                    ),
                    "getExternalJavascripts" => array
                    (
                        "vfs://RedKiteCMS/web/components/redkitecms/jquery-ui/jquery-ui.min.js",
                        "vfs://RedKiteCMS/web/components/redkitecms/myasset.min.js",
                    ),
                ),
                "dashboard" => array
                (
                    "getExternalStylesheets" => array
                    (
                        "vfs://RedKiteCMS/web/components/redkitecms/twitter-bootstrap/css/bootstrap.min.css",
                    ),
                    "getExternalJavascripts" => array
                    (
                        "vfs://RedKiteCMS/web/components/jquery/jquery.min.js",
                    ),
                ),
            ),
            "general" => array
            (
                "skin" => "RedKiteCms/public/css/skins/redkite",
                "baseTemplate" => "RedKiteCms/Resources/views/Frontend/base.html.twig"
            ),
            "foo" => array(
                "bar"
            )
        );
        $this->assertEquals($expectedConfiguration, $configurationHandler->configuration());
    }

    public function testImagesFolderAlreadyExists()
    {
        $customConfig = array(
            'assets.json' => '{
            "prod" :{
                "getExternalStylesheets": [
                    "%web_dir%/components/redkitecms/twitter-bootstrap/css/myasset.min.css"
                ]
            },
            "cms" :{
                "getExternalJavascripts": [
                    "%web_dir%/components/redkitecms/jquery-ui/myasset.min.js"
                ]
            }
        }');

        $this->init('redkitecms.com', $customConfig);

        $configurationHandler = new ConfigurationHandler(
            vfsStream::url('RedKiteCMS'),
            'redkitecms.com',
            'vendor/redkitecms-framework'
        );
        $configurationHandler->boot();
    }

    public function testCheckConfiguration()
    {
        $this->init();

        $configurationHandler = new ConfigurationHandler(vfsStream::url('RedKiteCMS'), 'redkitecms.com', 'vendor/redkitecms-framework');
        $configurationHandler->boot();
        $this->assertEquals('vfs://RedKiteCMS/app', $configurationHandler->appDir());
        $this->assertEquals('vfs://RedKiteCMS/app/logs', $configurationHandler->logDir());
        $this->assertEquals('vfs://RedKiteCMS/app/cache', $configurationHandler->cacheDir());
        $this->assertEquals('vfs://RedKiteCMS/app/cache/redkitecms.com', $configurationHandler->siteCacheDir());
        $this->assertEquals('vfs://RedKiteCMS/app/data', $configurationHandler->dataDir());
        $this->assertEquals('vfs://RedKiteCMS/app/data/redkitecms.com', $configurationHandler->siteDir());
        $this->assertEquals('vfs://RedKiteCMS/app/data/redkitecms.com/users', $configurationHandler->usersDir());
        $this->assertEquals('vfs://RedKiteCMS/web', $configurationHandler->webDir());
        $this->assertEquals('vfs://RedKiteCMS/vendor/redkitecms-framework/plugins/RedKiteCms', $configurationHandler->corePluginsDir());
        $this->assertEquals('vfs://RedKiteCMS/app/plugins/RedKiteCms', $configurationHandler->customPluginsDir());
        $this->assertEquals('vfs://RedKiteCMS/app/data/redkitecms.com/pages', $configurationHandler->pagesRootDir());
        $this->assertEquals('vfs://RedKiteCMS/app/data/redkitecms.com/pages/removed', $configurationHandler->pagesRemovedDir());
        $this->assertEquals('vfs://RedKiteCMS/web/upload/assets/redkitecms.com/backend', $configurationHandler->uploadAssetsDir());
        $this->assertEquals('vfs://RedKiteCMS/web/upload/assets/redkitecms.com/production', $configurationHandler->uploadAssetsDirProduction());
        $this->assertEquals('/upload/assets/redkitecms.com/backend', $configurationHandler->absoluteUploadAssetsDir());
        $this->assertEquals('vfs://RedKiteCMS/vendor/redkitecms-framework/config', $configurationHandler->coreConfigDir());
        $this->assertEquals('web', $configurationHandler->webDirname());
        $this->assertEquals(true, $configurationHandler->isProduction());
        $this->assertEquals(false, $configurationHandler->isTheme());
        $this->assertEquals(array(
                "vfs://RedKiteCMS/vendor/redkitecms-framework/plugins/RedKiteCms",
                "vfs://RedKiteCMS/app/plugins/RedKiteCms",
        ), $configurationHandler->pluginFolders());

        $this->assertEmpty($configurationHandler->handledTheme());
        $this->assertEquals("SimpleTheme", $configurationHandler->theme());
        $this->assertEquals("en-gb-homepage", $configurationHandler->homepagePermalink());
        $this->assertEquals("en_GB", $configurationHandler->defaultLanguage());
        $this->assertEquals(array("en_GB"), $configurationHandler->languages());
        $this->assertEquals("homepage", $configurationHandler->homepage());
        $this->assertEquals("home", $configurationHandler->homepageTemplate());

        $expectedSiteInfo = array
        (
            "theme" => "SimpleTheme",
            "homepage" => "homepage",
            "locale_default" => "en_GB",
            "homepage_permalink" => "en-gb-homepage",
            "languages" => array
            (
                "en_GB",
            ),
            "handled_theme" => "",
        );
        $this->assertEquals($expectedSiteInfo, $configurationHandler->siteInfo());

        $expectedConfiguration = array
        (
            "assets" => array
            (
                "prod" => array
                (
                    "getExternalStylesheets" => array
                    (
                        "vfs://RedKiteCMS/web/components/redkitecms/twitter-bootstrap/css/bootstrap.min.css",
                    ),
                    "getExternalJavascripts" => array
                    (
                        "vfs://RedKiteCMS/web/components/jquery/jquery.min.js",
                    ),
                ),
                "cms" => array
                (
                    "getExternalStylesheets" => array
                    (
                        "vfs://RedKiteCMS/web/plugins/redkitecms/css/skins/redkite/skin.css",
                    ),
                    "getExternalJavascripts" => array
                    (
                        "vfs://RedKiteCMS/web/components/redkitecms/jquery-ui/jquery-ui.min.js",
                    ),
                ),
                "dashboard" => array
                (
                    "getExternalStylesheets" => array
                    (
                        "vfs://RedKiteCMS/web/components/redkitecms/twitter-bootstrap/css/bootstrap.min.css",
                    ),
                    "getExternalJavascripts" => array
                    (
                        "vfs://RedKiteCMS/web/components/jquery/jquery.min.js",
                    ),
                ),
            ),
            "general" => array
            (
                "skin" => "RedKiteCms/public/css/skins/redkite",
                "baseTemplate" => "RedKiteCms/Resources/views/Frontend/base.html.twig"
            ),
            "foo" => array(
                "bar"
            )
        );

        $this->assertEquals($expectedConfiguration, $configurationHandler->configuration());
        $this->assertEquals('en', $configurationHandler->language());
        $this->assertEquals('GB', $configurationHandler->country());

        $this->assertEmpty($configurationHandler->getAssetsByType('foo'));
        $expectedAssets = array
        (
            "getExternalStylesheets" => array
            (
                "vfs://RedKiteCMS/web/components/redkitecms/twitter-bootstrap/css/bootstrap.min.css",
            ),
            "getExternalJavascripts" => array
            (
                "vfs://RedKiteCMS/web/components/jquery/jquery.min.js",
            ),
        );
        $this->assertEquals($expectedAssets, $configurationHandler->getAssetsByType('prod'));
        $expectedAssets = array
        (
            "getExternalStylesheets" => array
            (
                "vfs://RedKiteCMS/web/plugins/redkitecms/css/skins/redkite/skin.css",
            ),
            "getExternalJavascripts" => array
            (
                "vfs://RedKiteCMS/web/components/redkitecms/jquery-ui/jquery-ui.min.js",
            ),
        );
        $this->assertEquals($expectedAssets, $configurationHandler->getAssetsByType('cms'));

        $expectedAssets = array
        (
            "getExternalStylesheets" => array
            (
                "vfs://RedKiteCMS/web/components/redkitecms/twitter-bootstrap/css/bootstrap.min.css",
            ),
            "getExternalJavascripts" => array
            (
                "vfs://RedKiteCMS/web/components/jquery/jquery.min.js",
            ),
        );
        $this->assertEquals($expectedAssets, $configurationHandler->getAssetsByType('dashboard'));
        $this->assertEquals("RedKiteCms/public/css/skins/redkite", $configurationHandler->skin());
        $this->assertEquals("RedKiteCms/Resources/views/Frontend/base.html.twig", $configurationHandler->baseTemplate());
        $this->assertEquals(array("bar"), $configurationHandler->foo());
    }

    public function testConfigurationOptions()
    {
        $this->init();

        $configurationHandler = new ConfigurationHandler(vfsStream::url('RedKiteCMS'), 'redkitecms.com', 'vendor/redkitecms-framework');
        $configurationHandler->setConfigurationOptions(array(
                'web_dir' => 'public_folder',
                'uploads_dir' => '/files',
            ));
        $configurationHandler->boot();
        $this->assertEquals('public_folder', $configurationHandler->webDirname());
        $this->assertEquals('vfs://RedKiteCMS/public_folder/files/redkitecms.com/backend', $configurationHandler->uploadAssetsDir());
        $this->assertEquals('vfs://RedKiteCMS/public_folder/files/redkitecms.com/production', $configurationHandler->uploadAssetsDirProduction());
    }

    private function init($siteName = 'redkitecms.com', $customGlobalConfig = array(), $siteGlobalConfig = array())
    {
        $folders = array(
            'RedKiteCMS' => array(
                'app' => array(
                    'cache' => array(),
                    'config' => $customGlobalConfig,
                    'data' => array(
                        $siteName => array(
                            'config' => $siteGlobalConfig,
                            'pages' => array(
                                'pages' => array(
                                    'homepage' => array(
                                        "page.json" => '{"name":"homepage","template":"home"}',
                                    ),
                                ),
                            ),
                            'roles' => array(),
                            'slots' => array(),
                            'users' => array(),
                            'site.json' => '{"theme":"SimpleTheme","homepage":"homepage","locale_default":"en_GB","homepage_permalink":"en-gb-homepage","languages":["en_GB"],"handled_theme":""}',
                        ),
                    ),
                    'logs' => array(),
                    'plugins' => array(
                        'RedKiteCms' => array(
                            'Block' => array(),
                            'Theme' => array(),
                        ),
                    ),
                ),
                'src' => array(),
                'web' => array(),
                'vendor' => array(
                    'redkitecms-framework' => array(
                        'config' => array(
                            'assets.json' => '
                                {
                                    "prod" :{
                                        "getExternalStylesheets": [
                                            "%web_dir%/components/redkitecms/twitter-bootstrap/css/bootstrap.min.css"
                                        ],
                                        "getExternalJavascripts": [
                                            "%web_dir%/components/jquery/jquery.min.js"
                                        ]
                                    },
                                    "cms" :{
                                        "getExternalStylesheets": [
                                            "%web_dir%/plugins/redkitecms/css/skins/redkite/skin.css"
                                        ],
                                        "getExternalJavascripts": [
                                            "%web_dir%/components/redkitecms/jquery-ui/jquery-ui.min.js"
                                        ]
                                    },
                                    "dashboard" :{
                                        "getExternalStylesheets": [
                                            "%web_dir%/components/redkitecms/twitter-bootstrap/css/bootstrap.min.css"
                                        ],
                                        "getExternalJavascripts": [
                                            "%web_dir%/components/jquery/jquery.min.js"
                                        ]
                                    }
                                }',
                            'general.json' => '
                                {
                                  "skin": "RedKiteCms/public/css/skins/redkite",
                                  "baseTemplate": "RedKiteCms/Resources/views/Frontend/base.html.twig"
                                }',
                            'foo.json' => '
                                  [
                                    "bar"
                                  ]
                                ',
                        ),
                    )
                ),
            ),
        );

        vfsStream::setup('www', null, $folders);
    }
}
