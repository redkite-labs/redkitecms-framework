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

namespace RedKiteCms\Content\BlockManager;

use org\bovigo\vfs\vfsStream;
use RedKiteCms\Content\Block\BlockFactory;
use RedKiteCms\Content\BlockManager\BlockManagerAdd;
use RedKiteCms\TestCase;

/**
 * BlockFactoryTest
 *
 * @author RedKite Labs <webmaster@redkite-labs.com>
 */
class BlockFactoryTest extends TestCase
{
    private $blockFactory;

    protected function setUp()
    {
        parent::setUp();

        $this->init();
        $configurationHandler = $this->initConfigurationHandler();
        $this->blockFactory = new BlockFactory($configurationHandler);
    }

    /**
     * @expectedException \RedKiteCms\Exception\General\RuntimeException
     * @expectedExceptionMessage The plugin Foo is not registered: the block has not been created
     */
    public function testBlockNotRegistered()
    {
        $this->blockFactory
            ->boot()
            ->createBlock('Foo')
        ;
    }

    public function testBlockCreated()
    {
        $block = $this->blockFactory
            ->boot()
            ->createBlock('Link')
        ;

        $this->assertInstanceOf('\RedKiteCms\Block\Link\Core\LinkBlock', $block);
    }

    public function testAllBlocksCreated()
    {
        $blocks = $this->blockFactory
            ->boot()
            ->createAllBlocks()
        ;

        $this->assertCount(2, $blocks);
        $this->assertEquals(array(
            "Link" => 'RedKiteCms\Block\Link\Core\LinkBlock',
            "Text" => 'RedKiteCms\Block\Text\Core\TextBlock',
        ), $this->blockFactory->getAvailableBlocks());
        $this->assertInstanceOf('\RedKiteCms\Block\Link\Core\LinkBlock', $blocks[0]);
        $this->assertInstanceOf('\RedKiteCms\Block\Text\Core\TextBlock', $blocks[1]);
    }

    private function init()
    {
        $folders = array(
            'RedKiteCMS' => array(
                'Block' => array(
                    'Link' => array(
                        'Core' => array(
                            'LinkBlock.php' => '',
                        ),
                        'plugin.json' => '{"author": "RedKite Labs"}',
                    ),
                    'Text' => array(
                        'Core' => array(
                            'TextBlock.php' => '',
                        ),
                        'plugin.json' => '{"author": "RedKite Labs"}',
                    ),
                ),
            ),
        );

        vfsStream::setup('plugins', null, $folders);
    }

    private function initConfigurationHandler()
    {
        $configurationHandler = $this
            ->getMockBuilder('\RedKiteCms\Configuration\ConfigurationHandler')
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $configurationHandler
            ->expects($this->once())
            ->method('pluginFolders')
            ->will($this->returnValue(array(vfsStream::url('plugins/RedKiteCMS'))));
        ;

        return $configurationHandler;
    }
}