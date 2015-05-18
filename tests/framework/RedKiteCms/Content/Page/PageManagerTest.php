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

namespace RedKiteCms\Content\Page;

use org\bovigo\vfs\vfsStream;
use RedKiteCms\Bridge\Dispatcher\Dispatcher;
use RedKiteCms\Content\BlockManager\BlockManagerApprover;
use RedKiteCms\Content\Page\PageManager;
use RedKiteCms\Content\PageCollection\BasePagesTest;
use RedKiteCms\TestCase;

/**
 * PageManagerTest
 *
 * @author RedKite Labs <webmaster@redkite-labs.com>
 */
class PageManagerTest extends BasePagesTest
{
    /**
     * @dataProvider pagesProvider
     */
    public function testPageEdit($values, $events, $logs, $expectedResult, $expectedChangedPermalink)
    {
        $this->init();
        $configurationHandler = $this->initConfigurationHandler();
        $this->initDispatcherAndLogger($events, $logs);

        $pageManger = new PageManager($configurationHandler);
        $pageManger
            ->contributor('admin')
            ->edit('homepage', $values)
        ;
        $updatedFile = file_get_contents(vfsStream::url('localhost/pages/pages/homepage/en_GB/admin.json'));
        $this->assertEquals($expectedResult, $updatedFile);
        $this->assertEquals($expectedChangedPermalink, $pageManger->getChangedPermalink());
    }

    /**
     * @expectedException \RedKiteCms\Exception\Publish\PageNotPublishedException
     * @expectedExceptionMessage exception_page_not_published
     */
    public function testCannotApproveAnUnpublishedPage()
    {
        $folders = array(
            'pages' => array(
                'pages' => array(
                    'homepage' => array(
                        'en_GB' => array(
                            "admin.json" => '{"permalink":"en-gb-homepage","current_permalink":"en-gb-homepage","changed_permalinks":[],"title":"homepage-title","description":"homepage-description","keywords":"homepage-keywords","sitemap_frequency":"monthly","sitemap_priority":"0.5"}',
                        ),
                        "admin.json" => '{"name":"homepage","template":"home"}',
                        "page.json" => '{"name":"mine-homepage","template":"home"}',
                    ),
                ),
            ),
        );

        vfsStream::setup('localhost', null, $folders);
        $configurationHandler = $this->initConfigurationHandler();

        $pageManger = new PageManager($configurationHandler);
        $pageManger
            ->contributor('admin')
            ->approve('homepage', 'en_GB')
        ;
    }

    public function testPageApprove()
    {
        $folders = array(
            'pages' => array(
                'pages' => array(
                    'homepage' => array(
                        'en_GB' => array(
                            "admin.json" => '{"permalink":"en-gb-homepage","current_permalink":"en-gb-homepage","changed_permalinks":[],"title":"homepage-title","description":"homepage-description","keywords":"homepage-keywords","sitemap_frequency":"monthly","sitemap_priority":"0.5"}',
                            "seo.json" => '{"permalink":"mine-page","changed_permalinks":[],"title":"homepage-title","description":"homepage-description","keywords":"homepage-keywords","sitemap_frequency":"monthly","sitemap_priority":"0.5"}',
                        ),
                        "admin.json" => '{"name":"homepage","template":"home"}',
                        "page.json" => '{"name":"mine-homepage","template":"home"}',
                    ),
                ),
            ),
        );

        vfsStream::setup('localhost', null, $folders);
        $configurationHandler = $this->initConfigurationHandler();

        $this->dispatch(0, 'page.approving', '\RedKiteCms\EventSystem\Event\Page\PageApprovingEvent');
        $this->dispatch(1, 'page.approved', '\RedKiteCms\EventSystem\Event\Page\PageApprovedEvent');
        $this->log(0, 'info', 'The "page.approving" event was dispatched');

        $pageManger = new PageManager($configurationHandler);
        $pageManger
            ->contributor('admin')
            ->approve('homepage', 'en_GB')
        ;
        $contributorFile = file_get_contents(vfsStream::url('localhost/pages/pages/homepage/en_GB/admin.json'));
        $productionFile = file_get_contents(vfsStream::url('localhost/pages/pages/homepage/en_GB/seo.json'));

        $this->assertEquals($contributorFile, $productionFile);
    }

    public function testPagePublish()
    {
        $this->init();
        $configurationHandler = $this->initConfigurationHandler();

        $this->dispatch(0, 'page.publishing', '\RedKiteCms\EventSystem\Event\Page\PagePublishingEvent');
        $this->dispatch(1, 'page.published', '\RedKiteCms\EventSystem\Event\Page\PagePublishedEvent');
        $this->log(0, 'info', 'The "page.publishing" event was dispatched');

        $pageManger = new PageManager($configurationHandler);
        $pageManger
            ->contributor('admin')
            ->publish('homepage', 'en_GB')
        ;
        $contributorFile = file_get_contents(vfsStream::url('localhost/pages/pages/homepage/en_GB/admin.json'));
        $productionFile = file_get_contents(vfsStream::url('localhost/pages/pages/homepage/en_GB/seo.json'));
        $this->assertEquals($contributorFile, $productionFile);
    }

    public function testPageHide()
    {
        $this->init();
        $configurationHandler = $this->initConfigurationHandler();

        $this->dispatch(0, 'page.hiding', '\RedKiteCms\EventSystem\Event\Page\PageHidingEvent');
        $this->dispatch(1, 'page.hid', '\RedKiteCms\EventSystem\Event\Page\PageHidEvent');
        $this->log(0, 'info', 'The "page.hiding" event was dispatched');

        $seoFile = vfsStream::url('localhost/pages/pages/homepage/en_GB/seo.json');
        $this->assertFileExists($seoFile);
        $pageManger = new PageManager($configurationHandler);
        $pageManger
            ->contributor('admin')
            ->hide('homepage', 'en_GB')
        ;
        $this->assertFileNotExists($seoFile);
    }

    public function pagesProvider()
    {
        return array(
            array(
                array(
                    'language' => 'en_GB',
                    'permalink' => 'en-gb-homepage',
                    "title" => "An awesome page",
                    "description" => "homepage-description",
                    "keywords" => "homepage-keywords",
                    "sitemap_frequency" => "monthly",
                    "sitemap_priority" => "0.5",
                ),
                array(
                    'page.editing' => '\RedKiteCms\EventSystem\Event\Page\PageEditingEvent',
                    'page.edited' => '\RedKiteCms\EventSystem\Event\Page\PageEditedEvent',
                ),
                array(
                    'The "page.editing" event was dispatched',
                    'The "page.edited" event was dispatched',
                    'Page SEO attributes "homepage" for language "en_GB" were edited',
                ),
                '{"permalink":"en-gb-homepage","title":"An awesome page","description":"homepage-description","keywords":"homepage-keywords","sitemap_frequency":"monthly","sitemap_priority":"0.5"}',
                array(),
            ),
            array(
                array(
                    'language' => 'en_GB',
                    'permalink' => 'homepage',
                    "title" => "homepage-title",
                    "description" => "homepage-description",
                    "keywords" => "homepage-keywords",
                    "sitemap_frequency" => "monthly",
                    "sitemap_priority" => "0.5",
                ),
                array(
                    'page.permalink_changed' => '\RedKiteCms\EventSystem\Event\Page\PermalinkChangedEvent',
                    'page.editing' => '\RedKiteCms\EventSystem\Event\Page\PageEditingEvent',
                    'page.edited' => '\RedKiteCms\EventSystem\Event\Page\PageEditedEvent',
                ),
                array(
                    'The "page.permalink_changed" event was dispatched',
                    'The "page.editing" event was dispatched',
                    'The "page.edited" event was dispatched',
                    'Page SEO attributes "homepage" for language "en_GB" were edited',
                ),
                '{"permalink":"homepage","title":"homepage-title","description":"homepage-description","keywords":"homepage-keywords","sitemap_frequency":"monthly","sitemap_priority":"0.5"}',
                array(
                    'old' => 'en-gb-homepage',
                    'new' => 'homepage',
                ),
            ),
            array(
                array(
                    'language' => 'en_GB',
                    'permalink' => 'my great homepage',
                    "title" => "homepage-title",
                    "description" => "homepage-description",
                    "keywords" => "homepage-keywords",
                    "sitemap_frequency" => "monthly",
                    "sitemap_priority" => "0.5",
                ),
                array(
                    'page.slugging_permalink' => '\RedKiteCms\EventSystem\Event\Page\SluggingPermalinkEvent',
                    'page.permalink_changed' => '\RedKiteCms\EventSystem\Event\Page\PermalinkChangedEvent',
                    'page.editing' => '\RedKiteCms\EventSystem\Event\Page\PageEditingEvent',
                    'page.edited' => '\RedKiteCms\EventSystem\Event\Page\PageEditedEvent',
                ),
                array(
                    'The "page.slugging_permalink" event was dispatched',
                    'The "page.permalink_changed" event was dispatched',
                    'The "page.editing" event was dispatched',
                    'The "page.edited" event was dispatched',
                    'Page SEO attributes "homepage" for language "en_GB" were edited',
                ),
                '{"permalink":"my-great-homepage","title":"homepage-title","description":"homepage-description","keywords":"homepage-keywords","sitemap_frequency":"monthly","sitemap_priority":"0.5"}',
                array(
                    'old' => 'en-gb-homepage',
                    'new' => 'my-great-homepage',
                ),
            ),
            array(
                array(
                    'language' => 'en_GB',
                    'permalink' => 'homepage',
                    "title" => "An awesome page",
                    "description" => "An awesome description",
                    "keywords" => "some,keywords",
                    "sitemap_frequency" => "weekly",
                    "sitemap_priority" => "0.8",
                ),
                array(
                    'page.permalink_changed' => '\RedKiteCms\EventSystem\Event\Page\PermalinkChangedEvent',
                    'page.editing' => '\RedKiteCms\EventSystem\Event\Page\PageEditingEvent',
                    'page.edited' => '\RedKiteCms\EventSystem\Event\Page\PageEditedEvent',
                ),
                array(
                    'The "page.permalink_changed" event was dispatched',
                    'The "page.editing" event was dispatched',
                    'The "page.edited" event was dispatched',
                    'Page SEO attributes "homepage" for language "en_GB" were edited',
                ),
                '{"permalink":"homepage","title":"An awesome page","description":"An awesome description","keywords":"some,keywords","sitemap_frequency":"weekly","sitemap_priority":"0.8"}',
                array(
                    'old' => 'en-gb-homepage',
                    'new' => 'homepage',
                ),
            ),
        );
    }

    private function init()
    {
        $folders = array(
            'pages' => array(
                'pages' => array(
                    'homepage' => array(
                        'en_GB' => array(
                            "admin.json" => '{"permalink":"en-gb-homepage","changed_permalinks":[],"title":"homepage-title","description":"homepage-description","keywords":"homepage-keywords","sitemap_frequency":"monthly","sitemap_priority":"0.5"}',
                            "seo.json" => '{"permalink":"mine-page","changed_permalinks":[],"title":"homepage-title","description":"homepage-description","keywords":"homepage-keywords","sitemap_frequency":"monthly","sitemap_priority":"0.5"}',
                        ),
                        "admin.json" => '{"name":"homepage","template":"home"}',
                        "page.json" => '{"name":"mine-homepage","template":"home"}',
                    ),
                ),
            ),
        );

        vfsStream::setup('localhost', null, $folders);
    }
}