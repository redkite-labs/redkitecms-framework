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
 * PageCollectionManagerTest
 *
 * @author RedKite Labs <webmaster@redkite-labs.com>
 */
class PageCollectionManagerTest extends BasePagesTest
{
    private $slotsManagerFactory;
    private $configurationHandler;
    private $pageManger;

    protected function setUp()
    {
        parent::setUp();

        $this->slotsManagerFactory = $this->getMock('\RedKiteCms\Content\SlotsManager\SlotsManagerFactoryInterface');
        $this->configurationHandler = $this->initConfigurationHandler();
        $this->pageManger = new PageCollectionManager($this->configurationHandler, $this->slotsManagerFactory);
    }

    /**
     * @expectedException \RedKiteCms\Exception\General\InvalidArgumentException
     * @expectedExceptionMessage {"message":"exception_page_exists","parameters":{"%page_name%":"new-page-1"}}
     */
    public function testAddPageFailsBecausePageAlreadyExists()
    {
        $this->init();
        $theme = $this
            ->getMockBuilder('\RedKiteCms\Content\Theme\Theme')
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $pages = array(
            "new-page-1" => array(
                "admin.json" => '{"name":"new-page-1","currentName":"new-page-1","template":"home","isHome":false}',
                "page.json" => '{"name":"new-page-1","currentName":"new-page-1","template":"home","isHome":false}',
                "en_GB" => array(
                    "admin.json" => '{"permalink":"en-gb-new-page-1","title":"new-page-1-title","description":"new-page-1-description","keywords":"new-page-1-keywords","sitemap_frequency":"monthly","sitemap_priority":"0.5"}',
                ),
            ),
        );
        $this->init($pages);
        $values = array(
            "name" => "new-page-1",
            "currentName" => "new-page-1",
            "template" => "home",
            "isHome" => false,
            "seo" => array(
                array(
                    "permalink" => "en-gb-new-page-1",
                    "title" => "new-page-1-title",
                    "description" => "new-page-1-description",
                    "keywords" => "new-page-1-keywords",
                    "language" => "en_GB",
                    "sitemap_frequency" => "monthly",
                    "sitemap_priority" => "0.5",
                )
            ),
        );
        $this->pageManger
            ->contributor('admin')
            ->add($theme, $values)
        ;
    }

    public function testPageAdd()
    {
        $this->init();
        $theme = $this
            ->getMockBuilder('\RedKiteCms\Content\Theme\Theme')
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $theme
            ->expects($this->once())
            ->method('addTemplateSlots')
            ->with('home', 'admin')
        ;
        $this->dispatch(0, 'page.collection.adding', '\RedKiteCms\EventSystem\Event\PageCollection\PageCollectionAddingEvent');
        $this->dispatch(1, 'page.collection.added', '\RedKiteCms\EventSystem\Event\PageCollection\PageCollectionAddedEvent');
        $this->log(0, 'info', 'The "page.collection.adding" event was dispatched');
        $this->log(1, 'info', 'The "page.collection.added" event was dispatched');
        $this->log(2, 'info', 'Page "new-page-1" was successfully added to the website');

        $values = array(
            "name" => "new-page-1",
            "currentName" => "new-page-1",
            "template" => "home",
            "isHome" => false,
            "seo" => array(
                array(
                    "permalink" => "en-gb-new-page-1",
                    "title" => "new-page-1-title",
                    "description" => "new-page-1-description",
                    "keywords" => "new-page-1-keywords",
                    "language" => "en_GB",
                    "sitemap_frequency" => "monthly",
                    "sitemap_priority" => "0.5",
                )
            ),
        );
        $this->pageManger
            ->contributor('admin')
            ->add($theme, $values)
        ;

        $expectedPage = array(
            "new-page-1" => array(
                "admin.json" => '{"name":"new-page-1","currentName":"new-page-1","template":"home","isHome":false}',
                "page.json" => '{"name":"new-page-1","currentName":"new-page-1","template":"home","isHome":false}',
                "en_GB" => array(
                    "admin.json" => '{"permalink":"en-gb-new-page-1","title":"new-page-1-title","description":"new-page-1-description","keywords":"new-page-1-keywords","sitemap_frequency":"monthly","sitemap_priority":"0.5"}',
                ),
            ),
        );

        $structure = vfsStream::inspect(new vfsStreamStructureVisitor())->getStructure();
        $this->assertEquals($expectedPage, $structure["localhost"]["pages"]["pages"]);
    }

    /**
     * @expectedException \RedKiteCms\Exception\General\InvalidArgumentException
     * @expectedExceptionMessage {"message":"exception_page_exists","parameters":{"%page_name%":"homepage"}}
     */
    public function testEditPageFailsBecausePageAlreadyExists()
    {
        $pages = array(
            "new-page-1" => array(
                "admin.json" => '{"name":"new-page-1","currentName":"new-page-1","template":"home","isHome":false}',
                "page.json" => '{"name":"new-page-1","currentName":"new-page-1","template":"home","isHome":false}',
                "en_GB" => array(
                    "admin.json" => '{"permalink":"en-gb-new-page-1","title":"new-page-1-title","description":"new-page-1-description","keywords":"new-page-1-keywords","sitemap_frequency":"monthly","sitemap_priority":"0.5"}',
                ),
            ),
            "homepage" => array(
                "admin.json" => '{"name":"homepage","currentName":"new-page-1","template":"home","isHome":false}',
                "page.json" => '{"name":"new-page-1","currentName":"new-page-1","template":"home","isHome":false}',
                "en_GB" => array(
                    "admin.json" => '{"permalink":"en-gb-new-page-1","title":"new-page-1-title","description":"new-page-1-description","keywords":"new-page-1-keywords","sitemap_frequency":"monthly","sitemap_priority":"0.5"}',
                ),
            ),
        );
        $this->init($pages);
        $values = array(
            "name" => "homepage",
            "currentName" => "new-page-1",
        );
        $this->pageManger
            ->contributor('admin')
            ->edit($values)
        ;
    }
    /**
     * @dataProvider pagesProvider
     */
    public function testPageEdit($values, $events, $logs, $expectedPage)
    {
        $pages = array(
            "new-page-1" => array(
                "admin.json" => '{"name":"new-page-1","currentName":"new-page-1","template":"home","isHome":false}',
                "page.json" => '{"name":"new-page-1","currentName":"new-page-1","template":"home","isHome":false}',
                "en_GB" => array(
                    "admin.json" => '{"permalink":"en-gb-new-page-1","title":"new-page-1-title","description":"new-page-1-description","keywords":"new-page-1-keywords","sitemap_frequency":"monthly","sitemap_priority":"0.5"}',
                ),
            ),
        );
        $this->init($pages);
        $this->initDispatcherAndLogger($events, $logs);

        $this->pageManger
            ->contributor('admin')
            ->edit($values)
        ;
        $structure = vfsStream::inspect(new vfsStreamStructureVisitor())->getStructure();
        $this->assertEquals($expectedPage, $structure["localhost"]["pages"]["pages"]);
    }

    /**
     * @expectedException \RedKiteCms\Exception\General\RuntimeException
     * @expectedExceptionMessage exception_homepage_cannot_be_removed
     */
    public function testHomePageCannotBeRemove()
    {
        $pages = array(
            "homepage" => array(
                "admin.json" => '{"name":"homepage","currentName":"new-page-1","template":"home","isHome":false}',
                "page.json" => '{"name":"new-page-1","currentName":"new-page-1","template":"home","isHome":false}',
                "en_GB" => array(
                    "admin.json" => '{"permalink":"en-gb-new-page-1","title":"new-page-1-title","description":"new-page-1-description","keywords":"new-page-1-keywords","sitemap_frequency":"monthly","sitemap_priority":"0.5"}',
                ),
            ),
        );
        $this->init($pages);

        $this->configurationHandler
            ->expects($this->once())
            ->method('homepage')
            ->will($this->returnValue('homepage'));
        ;

        $this->pageManger
            ->contributor('admin')
            ->remove("homepage")
        ;
    }

    public function testPageRemove()
    {
        $pages = array(
            "new-page-1" => array(
                "admin.json" => '{"name":"new-page-1","currentName":"new-page-1","template":"home","isHome":false}',
                "page.json" => '{"name":"new-page-1","currentName":"new-page-1","template":"home","isHome":false}',
                "en_GB" => array(
                    "admin.json" => '{"permalink":"en-gb-new-page-1","title":"new-page-1-title","description":"new-page-1-description","keywords":"new-page-1-keywords","sitemap_frequency":"monthly","sitemap_priority":"0.5"}',
                ),
            ),
        );
        $this->init($pages);
        $this->dispatch(0, 'page.collection.removing', '\RedKiteCms\EventSystem\Event\PageCollection\PageCollectionRemovingEvent');
        $this->dispatch(1, 'page.collection.removed', '\RedKiteCms\EventSystem\Event\PageCollection\PageCollectionRemovedEvent');
        $this->log(0, 'info', 'The "page.collection.removing" event was dispatched');
        $this->log(1, 'info', 'The "page.collection.removed" event was dispatched');
        $this->log(2, 'info', 'Page "new-page-1" was successfully removed from website');

        $this->pageManger
            ->contributor('admin')
            ->remove("new-page-1")
        ;
        $this->assertFileNotExists(vfsStream::url('localhost/pages/pages/new-page-1'));
    }

    public function pagesProvider()
    {
        return array(
            array(
                array(
                    "name" => "homepage",
                    "currentName" => "new-page-1",
                ),
                array(
                    'page.collection.editing' => '\RedKiteCms\EventSystem\Event\PageCollection\PageCollectionEditingEvent',
                    'page.collection.edited' => '\RedKiteCms\EventSystem\Event\PageCollection\PageCollectionEditedEvent',
                ),
                array(
                    'The "page.collection.editing" event was dispatched',
                    'The "page.collection.edited" event was dispatched',
                    'Page "new-page-1" was successfully edited',
                ),
                array(
                    "homepage" => array(
                        "admin.json" => '{"name":"homepage","currentName":"new-page-1","template":"home","isHome":false}',
                        "page.json" => '{"name":"new-page-1","currentName":"new-page-1","template":"home","isHome":false}',
                        "en_GB" => array(
                            "admin.json" => '{"permalink":"en-gb-new-page-1","title":"new-page-1-title","description":"new-page-1-description","keywords":"new-page-1-keywords","sitemap_frequency":"monthly","sitemap_priority":"0.5"}',
                        ),
                    ),
                ),
            ),
            array(
                array(
                    "name" => "my awesome homepage",
                    "currentName" => "new-page-1",
                ),
                array(
                    'page.slugging_page_collection_name' => '\RedKiteCms\EventSystem\Event\PageCollection\SluggingPageNameEvent',
                    'page.collection.editing' => '\RedKiteCms\EventSystem\Event\PageCollection\PageCollectionEditingEvent',
                    'page.collection.edited' => '\RedKiteCms\EventSystem\Event\PageCollection\PageCollectionEditedEvent',
                ),
                array(
                    'The "page.slugging_page_collection_name" event was dispatched',
                    'The "page.collection.editing" event was dispatched',
                    'The "page.collection.edited" event was dispatched',
                    'Page "new-page-1" was successfully edited',
                ),
                array(
                    "my-awesome-homepage" => array(
                        "admin.json" => '{"name":"my-awesome-homepage","currentName":"new-page-1","template":"home","isHome":false}',
                        "page.json" => '{"name":"new-page-1","currentName":"new-page-1","template":"home","isHome":false}',
                        "en_GB" => array(
                            "admin.json" => '{"permalink":"en-gb-new-page-1","title":"new-page-1-title","description":"new-page-1-description","keywords":"new-page-1-keywords","sitemap_frequency":"monthly","sitemap_priority":"0.5"}',
                        ),
                    ),
                ),
            ),
            array(
                array(
                    "name" => "homepage",
                    "template" => "internal",
                    "currentName" => "new-page-1",
                ),
                array(
                    'page.collection.template_changed' => '\RedKiteCms\EventSystem\Event\PageCollection\TemplateChangedEvent',
                    'page.collection.editing' => '\RedKiteCms\EventSystem\Event\PageCollection\PageCollectionEditingEvent',
                    'page.collection.edited' => '\RedKiteCms\EventSystem\Event\PageCollection\PageCollectionEditedEvent',
                ),
                array(
                    'The "page.collection.template_changed" event was dispatched',
                    'The "page.collection.editing" event was dispatched',
                    'The "page.collection.edited" event was dispatched',
                    'Page "new-page-1" was successfully edited',
                ),
                array(
                    "homepage" => array(
                        "admin.json" => '{"name":"homepage","currentName":"new-page-1","template":"internal","isHome":false}',
                        "page.json" => '{"name":"new-page-1","currentName":"new-page-1","template":"home","isHome":false}',
                        "en_GB" => array(
                            "admin.json" => '{"permalink":"en-gb-new-page-1","title":"new-page-1-title","description":"new-page-1-description","keywords":"new-page-1-keywords","sitemap_frequency":"monthly","sitemap_priority":"0.5"}',
                        ),
                    ),
                ),
            ),
        );
    }

    private function init($pages = array())
    {
        $folders = array(
            'pages' => array(
                'pages' => $pages,
            ),
        );

        vfsStream::setup('localhost', null, $folders);
    }
}