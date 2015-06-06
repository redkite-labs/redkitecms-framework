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
    private $configurationHandler;

    protected function setUp()
    {
        parent::setUp();

        $this->init();
        $configurationHandler = $this->initConfigurationHandler();
        BlockFactory::boot($configurationHandler);
    }

    /**
     * @expectedException \RedKiteCms\Exception\General\RuntimeException
     * @expectedExceptionMessage The plugin Foo is not registered: the block has not been created
     */
    public function testBlockNotRegistered()
    {
        BlockFactory::createBlock('Foo');
    }

    public function testBlockCreated()
    {
        $block = BlockFactory::createBlock('Link');

        $this->assertInstanceOf('\RedKiteCms\Block\Link\Core\LinkBlock', $block);
    }

    public function testGetBlockClass()
    {
        $blockClass = BlockFactory::getBlockClass('Foo');
        $this->assertEquals('', $blockClass);

        $blockClass = BlockFactory::getBlockClass('Link');
        $this->assertEquals('RedKiteCms\Block\Link\Core\LinkBlock', $blockClass);
    }

    public function testAllBlocksCreated()
    {
        $blocks = BlockFactory::createAllBlocks();

        $this->assertCount(2, $blocks);
        $this->assertEquals(array(
            "Link" => 'RedKiteCms\Block\Link\Core\LinkBlock',
            "Text" => 'RedKiteCms\Block\Text\Core\TextBlock',
        ), BlockFactory::getAvailableBlocks());
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