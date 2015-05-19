<?php
/**
 * This file is part of the RedKite CMS Application and it is distributed
 * under the MIT License. To use this application you must leave
 * intact this copyright notice.
 *
 * Copyright (c) RedKite Labs <webmaster@redkite-labs.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * For extra documentation and help please visit http://www.redkite-labs.com
 *
 * @license    MIT License
 *
 */

namespace RedKiteCms\Content\PageCollection;

use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\visitor\vfsStreamStructureVisitor;
use RedKiteCms\Bridge\Dispatcher\Dispatcher;
use RedKiteCms\Content\BlockManager\BlockManagerApprover;
use RedKiteCms\Content\Page\PageManager;
use RedKiteCms\Content\PageCollection\PageCollectionManager;
use RedKiteCms\TestCase;

/**
 * PageCollectionParserTest
 *
 * @author RedKite Labs <webmaster@redkite-labs.com>
 */
class PageCollectionParserTest extends BasePagesTest
{
    public function testParse()
    {
        $this->init();
        $configurationHandler = $this->initConfigurationHandler();
        $pageCollectionParser = new PagesCollectionParser($configurationHandler);

        $pageCollectionParser->parse();
        $this->pagesTest($pageCollectionParser);
        $this->pageTest($pageCollectionParser);
        $this->permalinksByLanguageTest($pageCollectionParser);
        $this->sitemapTest($pageCollectionParser);
    }

    protected function initConfigurationHandler()
    {
        $configurationHandler = parent::initConfigurationHandler();

        $configurationHandler
            ->expects($this->once())
            ->method('homepage')
            ->will($this->returnValue('homepage'))
        ;

        $configurationHandler
            ->expects($this->once())
            ->method('language')
            ->will($this->returnValue('en'))
        ;

        $configurationHandler
            ->expects($this->once())
            ->method('country')
            ->will($this->returnValue('GB'))
        ;

        $configurationHandler
            ->expects($this->once())
            ->method('languages')
            ->will($this->returnValue(array('en_GB', 'it_IT')))
        ;

        return $configurationHandler;
    }

    private function pagesTest($pageCollectionParser)
    {
        $this->assertEquals(array(
            array(
                "name" => "contacts",
                "currentName" => "contacts",
                "template" => "home",
                "isHome" => false,
                "seo" => array(
                    array(
                        "permalink" => "contact-us",
                        "title" => "new-page-1-title",
                        "description" => "new-page-1-description",
                        "keywords" => "new-page-1-keywords",
                        "language" => "en_GB",
                        "sitemap_frequency" => "monthly",
                        "sitemap_priority" => "0.5",
                    ),
                    array(
                        "permalink" => "contattaci",
                        "title" => "new-page-1-title",
                        "description" => "new-page-1-description",
                        "keywords" => "new-page-1-keywords",
                        "language" => "it_IT",
                        "sitemap_frequency" => "monthly",
                        "sitemap_priority" => "0.5",
                    ),
                ),
            ),
            array(
                "name" => "about",
                "currentName" => "about",
                "template" => "home",
                "isHome" => false,
                "seo" => array(
                    array(
                        "permalink" => "about-our-company",
                        "title" => "new-page-1-title",
                        "description" => "new-page-1-description",
                        "keywords" => "new-page-1-keywords",
                        "language" => "en_GB",
                        "sitemap_frequency" => "monthly",
                        "sitemap_priority" => "0.5",
                    ),
                    array(
                        "permalink" => "la-nostra-compagnia",
                        "title" => "new-page-1-title",
                        "description" => "new-page-1-description",
                        "keywords" => "new-page-1-keywords",
                        "language" => "it_IT",
                        "sitemap_frequency" => "monthly",
                        "sitemap_priority" => "0.5",
                    ),
                ),
            ),
            array(
                "name" => "homepage",
                "currentName" => "homepage",
                "template" => "home",
                "isHome" => true,
                "seo" => array(
                    array(
                        "permalink" => "welcome-to-our-site",
                        "title" => "new-page-1-title",
                        "description" => "new-page-1-description",
                        "keywords" => "new-page-1-keywords",
                        "language" => "en_GB",
                        "sitemap_frequency" => "monthly",
                        "sitemap_priority" => "0.5",
                    ),
                    array(
                        "permalink" => "benvenuti",
                        "title" => "new-page-1-title",
                        "description" => "new-page-1-description",
                        "keywords" => "new-page-1-keywords",
                        "language" => "it_IT",
                        "sitemap_frequency" => "monthly",
                        "sitemap_priority" => "0.5",
                    ),
                ),
            ),
        ), $pageCollectionParser->pages());
    }

    public function pageTest($pageCollectionParser)
    {
        $this->assertNull($pageCollectionParser->page("foo"));
        $this->assertEquals(array(
            "name" => "homepage",
            "currentName" => "homepage",
            "template" => "home",
            "isHome" => true,
            "seo" => array(
                array(
                    "permalink" => "welcome-to-our-site",
                    "title" => "new-page-1-title",
                    "description" => "new-page-1-description",
                    "keywords" => "new-page-1-keywords",
                    "language" => "en_GB",
                    "sitemap_frequency" => "monthly",
                    "sitemap_priority" => "0.5",
                ),
                array(
                    "permalink" => "benvenuti",
                    "title" => "new-page-1-title",
                    "description" => "new-page-1-description",
                    "keywords" => "new-page-1-keywords",
                    "language" => "it_IT",
                    "sitemap_frequency" => "monthly",
                    "sitemap_priority" => "0.5",
                ),
            ),
        ), $pageCollectionParser->page("homepage"));
    }

    private function permalinksByLanguageTest($pageCollectionParser)
    {
        $this->assertEquals(array(
            "contact-us",
            "about-our-company",
            "welcome-to-our-site",
        ), $pageCollectionParser->permalinksByLanguage());

        $this->assertEquals(array(
            "contattaci",
            "la-nostra-compagnia",
            "benvenuti",
        ), $pageCollectionParser->permalinksByLanguage('it_IT'));
    }

    private function sitemapTest($pageCollectionParser)
    {
        $this->assertEquals(array(
            "contact-us" => array(
                "sitemap_frequency" => "monthly",
                "sitemap_priority" => "0.5",
            ),
            "about-our-company" => array(
                "sitemap_frequency" => "monthly",
                "sitemap_priority" => "0.5",
            ),
            "welcome-to-our-site" => array(
                "sitemap_frequency" => "monthly",
                "sitemap_priority" => "0.5",
            ),
            "contattaci" => array(
                "sitemap_frequency" => "monthly",
                "sitemap_priority" => "0.5",
            ),
            "la-nostra-compagnia" => array(
                "sitemap_frequency" => "monthly",
                "sitemap_priority" => "0.5",
            ),
            "benvenuti" => array(
                "sitemap_frequency" => "monthly",
                "sitemap_priority" => "0.5",
            ),
        ), $pageCollectionParser->sitemap());
    }

    private function init()
    {
        $folders = array(
            'pages' => array(
                'pages' => array(
                    "homepage" => array(
                        "page.json" => '{"name":"homepage","currentName":"homepage","template":"home","isHome":false}',
                        "en_GB" => array(
                            "seo.json" => '{"permalink":"welcome-to-our-site","title":"new-page-1-title","description":"new-page-1-description","keywords":"new-page-1-keywords","sitemap_frequency":"monthly","sitemap_priority":"0.5"}',
                        ),
                        "it_IT" => array(
                            "seo.json" => '{"permalink":"benvenuti","title":"new-page-1-title","description":"new-page-1-description","keywords":"new-page-1-keywords","sitemap_frequency":"monthly","sitemap_priority":"0.5"}',
                        ),
                    ),
                    "about" => array(
                        "page.json" => '{"name":"about","currentName":"about","template":"home","isHome":false}',
                        "en_GB" => array(
                            "seo.json" => '{"permalink":"about-our-company","title":"new-page-1-title","description":"new-page-1-description","keywords":"new-page-1-keywords","sitemap_frequency":"monthly","sitemap_priority":"0.5"}',
                        ),
                        "it_IT" => array(
                            "seo.json" => '{"permalink":"la-nostra-compagnia","title":"new-page-1-title","description":"new-page-1-description","keywords":"new-page-1-keywords","sitemap_frequency":"monthly","sitemap_priority":"0.5"}',
                        ),
                    ),
                    "not-published" => array(
                        "admin.json" => '{"name":"under-construction","currentName":"about","template":"home","isHome":false}',
                        "en_GB" => array(
                            "seo.json" => '{"permalink":"under-construction","title":"new-page-1-title","description":"new-page-1-description","keywords":"new-page-1-keywords","sitemap_frequency":"monthly","sitemap_priority":"0.5"}',
                        ),
                        "it_IT" => array(
                            "seo.json" => '{"permalink":"under-construction","title":"new-page-1-title","description":"new-page-1-description","keywords":"new-page-1-keywords","sitemap_frequency":"monthly","sitemap_priority":"0.5"}',
                        ),
                    ),
                    "contacts" => array(
                        "page.json" => '{"name":"contacts","currentName":"contacts","template":"home","isHome":false}',
                        "en_GB" => array(
                            "seo.json" => '{"permalink":"contact-us","title":"new-page-1-title","description":"new-page-1-description","keywords":"new-page-1-keywords","sitemap_frequency":"monthly","sitemap_priority":"0.5"}',
                        ),
                        "it_IT" => array(
                            "seo.json" => '{"permalink":"contattaci","title":"new-page-1-title","description":"new-page-1-description","keywords":"new-page-1-keywords","sitemap_frequency":"monthly","sitemap_priority":"0.5"}',
                        ),
                    ),
                ),
            ),
        );

        vfsStream::setup('localhost', null, $folders);
    }
}