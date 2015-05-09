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

namespace RedKiteCms\Bridge\Assetic;

use org\bovigo\vfs\vfsStream;
use RedKiteCms\Bridge\Routing\RoutingGenerator;
use RedKiteCms\TestCase;

/**
 * Class RoutingFrontendTest
 *
 * @author RedKite Labs <webmaster@redkite-labs.com>
 */
class RoutingGeneratorTest extends TestCase
{
    private $configurationHandler;

    protected function setUp()
    {
        $this->configurationHandler = $this
            ->getMockBuilder('\RedKiteCms\Configuration\ConfigurationHandler')
            ->setMethods(array('pagesDir', 'language', 'country', 'homepage'))
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $this->routeCollection = $this
            ->getMockBuilder('\Symfony\Component\Routing\RouteCollection')
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $this->router = $this->getMock('\Symfony\Component\Routing\RouterInterface');
        $this->router
            ->expects($this->once())
            ->method('getRouteCollection')
            ->will($this->returnValue($this->routeCollection))
        ;
    }

    /**
     * @dataProvider routesProvider
     */
    public function testRouterCreation($folders, $options, $expectedRoutes, $expectedIndexedRoutes)
    {
        $this->configurationHandler
            ->expects($this->once())
            ->method('language')
            ->will($this->returnValue('en'))
        ;

        $this->configurationHandler
            ->expects($this->once())
            ->method('country')
            ->will($this->returnValue('GB'))
        ;

        $this->configurationHandler
            ->expects($this->once())
            ->method('homepage')
            ->will($this->returnValue('index'))
        ;

        $this->configurationHandler
            ->expects($this->once())
            ->method('pagesDir')
            ->will($this->returnValue(vfsStream::url('root\redkitecms.com\pages\pages')))
        ;

        $at = 0;
        foreach($expectedRoutes as $expectedRoute) {
            $this->routeCollection
                ->expects($this->at($at))
                ->method('add')
                ->with($expectedRoute)
            ;
            $at++;
        }

        $this->root = vfsStream::setup('root', null, $folders);

        $routingGenerator = new RoutingGenerator($this->configurationHandler);
        $routingGenerator
            ->pattern('/backend')
            ->frontController('/the/front/controller:show')
            ->contributor($options["contributor"])
            ->explicitHomepageRoute($options["explicitHomepageRoute"])
            ->generate($this->router)
        ;
        $this->assertEquals($expectedIndexedRoutes, $routingGenerator->getRoutes());
    }

    public function routesProvider()
    {
        return array(
            // The seo file for admin contributor does not exists
            array(
                array(
                    'redkitecms.com' => array(
                        'pages' => array(
                            'pages' => array(
                                'homepage' => array(
                                    'en_GB' => array(
                                        'john.json' => '{"permalink":"en-gb-homepage","changed_permalinks":[],"title":"homepage-title","description":"homepage-description","keywords":"homepage-keywords","sitemap_frequency":"monthly","sitemap_priority":"0.5"}',
                                    ),
                                    'admin.json' => '{"name":"homepage","template":"home"}',
                                ),
                            ),
                        ),
                    ),
                ),
                array(
                    'contributor' => 'admin',
                    'explicitHomepageRoute' => false,
                ),
                array(),
                array(
                    'homepage' => '_home_en_GB_index',
                    'pages' => array(),
                ),
            ),
            // Generates the route for a single page
            array(
                array(
                    'redkitecms.com' => array(
                        'pages' => array(
                            'pages' => array(
                                'homepage' => array(
                                    'en_GB' => array(
                                        'admin.json' => '{"permalink":"en-gb-homepage","changed_permalinks":[],"title":"homepage-title","description":"homepage-description","keywords":"homepage-keywords","sitemap_frequency":"monthly","sitemap_priority":"0.5"}',
                                    ),
                                    'admin.json' => '{"name":"homepage","template":"home"}',
                                ),
                            ),
                        ),
                    ),
                ),
                array(
                    'contributor' => 'admin',
                    'explicitHomepageRoute' => false,
                ),
                array(
                    '_en_GB_homepage',
                ),
                array(
                    'homepage' => '_home_en_GB_index',
                    'pages' => array(
                        '_en_GB_homepage',
                    ),
                ),
            ),
            // Generates the route for two pages
            array(
                array(
                    'redkitecms.com' => array(
                        'pages' => array(
                            'pages' => array(
                                'homepage' => array(
                                    'en_GB' => array(
                                        'admin.json' => '{"permalink":"en-gb-homepage","changed_permalinks":[],"title":"homepage-title","description":"homepage-description","keywords":"homepage-keywords","sitemap_frequency":"monthly","sitemap_priority":"0.5"}',
                                    ),
                                    'admin.json' => '{"name":"homepage","template":"home"}',
                                ),
                                'about' => array(
                                    'en_GB' => array(
                                        'admin.json' => '{"permalink":"en-gb-about","changed_permalinks":[],"title":"about-title","description":"about-description","keywords":"about-keywords","sitemap_frequency":"monthly","sitemap_priority":"0.5"}',
                                    ),
                                    'admin.json' => '{"name":"about","template":"internal"}',
                                ),
                            ),
                        ),
                    ),
                ),
                array(
                    'contributor' => 'admin',
                    'explicitHomepageRoute' => false,
                ),
                array(
                    '_en_GB_homepage',
                    '_en_GB_about',
                ),
                array(
                    'homepage' => '_home_en_GB_index',
                    'pages' => array(
                        '_en_GB_homepage',
                        '_en_GB_about',
                    ),
                ),
            ),
            // Generates explicitely the homepage route
            array(
                array(
                    'redkitecms.com' => array(
                        'pages' => array(
                            'pages' => array(
                                'homepage' => array(
                                    'en_GB' => array(
                                        'admin.json' => '{"permalink":"en-gb-homepage","changed_permalinks":[],"title":"homepage-title","description":"homepage-description","keywords":"homepage-keywords","sitemap_frequency":"monthly","sitemap_priority":"0.5"}',
                                    ),
                                    'admin.json' => '{"name":"homepage","template":"home"}',
                                ),
                            ),
                        ),
                    ),
                ),
                array(
                    'contributor' => 'admin',
                    'explicitHomepageRoute' => true,
                ),
                array(
                    '_home_en_GB_index',
                    '_en_GB_homepage',
                ),
                array(
                    'homepage' => '_home_en_GB_index',
                    'pages' => array(
                        '_en_GB_homepage',
                    ),
                ),
            ),
            // Covers the case when changed_permalinks option is not found in the seo file
            array(
                array(
                    'redkitecms.com' => array(
                        'pages' => array(
                            'pages' => array(
                                'homepage' => array(
                                    'en_GB' => array(
                                        'admin.json' => '{"permalink":"redkitecms-homepage","title":"homepage-title","description":"homepage-description","keywords":"homepage-keywords","sitemap_frequency":"monthly","sitemap_priority":"0.5"}',
                                    ),
                                    'admin.json' => '{"name":"homepage","template":"home"}',
                                ),
                            ),
                        ),
                    ),
                ),
                array(
                    'contributor' => 'admin',
                    'explicitHomepageRoute' => false,
                ),
                array(
                    '_en_GB_homepage',
                ),
                array(
                    'homepage' => '_home_en_GB_index',
                    'pages' => array(
                        '_en_GB_homepage',
                    ),
                ),
            ),
            // Generates a route for the changed name "en-gb-homepage" route
            array(
                array(
                    'redkitecms.com' => array(
                        'pages' => array(
                            'pages' => array(
                                'homepage' => array(
                                    'en_GB' => array(
                                        'admin.json' => '{"permalink":"redkitecms-homepage","changed_permalinks":["en-gb-homepage"],"title":"homepage-title","description":"homepage-description","keywords":"homepage-keywords","sitemap_frequency":"monthly","sitemap_priority":"0.5"}',
                                    ),
                                    'admin.json' => '{"name":"homepage","template":"home"}',
                                ),
                            ),
                        ),
                    ),
                ),
                array(
                    'contributor' => 'admin',
                    'explicitHomepageRoute' => false,
                ),
                array(
                    '_removed_en-gb-homepage',
                    '_en_GB_homepage',
                ),
                array(
                    'homepage' => '_home_en_GB_index',
                    'pages' => array(
                        '_en_GB_homepage',
                    ),
                ),
            ),
        );
    }
}
