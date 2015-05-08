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

namespace RedKiteCms\BlockManager;

use org\bovigo\vfs\vfsStream;
use RedKiteCms\Content\BlockManager\BlockManagerAdd;

/**
 * BlockManagerAddTest
 *
 * @author RedKite Labs <webmaster@redkite-labs.com>
 */
class BlockManagerAddTest extends BlockManagerBaseTestCase
{
    private $blockManager;
    
    protected function setUp()
    {
        parent::setUp();
        
        $this->blockManager = new BlockManagerAdd($this->serializer, $this->blockFactory, $this->optionsResolver);
    }

    /**
     * @dataProvider addProvider
     */
    public function testAdd($folders, $options, $blockAddedName, $expectedAddedBlocks, $logMessage,  $username = 'john')
    {
        $this->configureFilesystem($folders);
        $block = $this->configureBlock($blockAddedName, $options);
        
        $this->configureBlockFactory($block, $options);
        $this->configureSerializer($block);

        $this->checkDispatcher();
        $this->checkLogger($logMessage);


        $this->blockManager->add(vfsStream::url('root\redkitecms.com'), $options, $username);
        $this->checkBlockFiles($expectedAddedBlocks);
    }

    private function configureBlockFactory($block, array $options)
    {
        $this->blockFactory
            ->expects($this->once())
            ->method('createBlock')
            ->with($options["type"])
            ->will($this->returnValue($block))
        ;

        return $block;
    }

    private function configureBlock($blockAddedName, array $options)
    {
        $block = $this
            ->getMockBuilder('RedKiteCms\Block\Link\Core\LinkBlock')
            ->disableOriginalConstructor()
            ->getMock()
        ;
        $block
            ->expects($this->once())
            ->method('setName')
            ->with($blockAddedName)
        ;
        $block
            ->expects($this->once())
            ->method('setSlotName')
            ->with($options["slot"])
        ;

        return $block;
    }

    private function configureSerializer($block)
    {
        $this->serializer
            ->expects($this->once())
            ->method('serialize')
            ->with($block, 'json')
            ->will($this->returnValue('{"foo":"bar"}'))
        ;
    }

    private function checkDispatcher()
    {
        $this->dispatch(0, 'block.adding', '\RedKiteCms\EventSystem\Event\Block\BlockAddingEvent');
        $this->dispatch(1, 'block.added', '\RedKiteCms\EventSystem\Event\Block\BlockAddedEvent');
    }

    private function checkLogger($logMessage)
    {
        $this->log(0, 'info', 'The "block.adding" event was dispatched');
        $this->log(1, 'info', 'The "block.added" event was dispatched');
        $this->log(2, 'info', $logMessage);
    }

    /**
     * @codeCoverageIgnore
     */
    public function addProvider()
    {
        return array(
            // Block2 is added under block1 when designing a theme
            array(
                array(
                    'redkitecms.com' => array(
                        'slots' => array(
                            'logo' => array(
                                'active' => array(
                                    'blocks' => array(
                                        'block1.json' => '{"slot_name":"logo","name":"block1","list_name":"","type":"Text","is_child":false,"editor_disabled":false,"custom_tag":"rkcms-text","history_name":"","history":[],"revision":1,"is_removed":false,"html":"<p>This is a text<\/p>","editor_configuration":"standard"}',
                                    ),
                                    'slot.json' => '{"next":2,"blocks":["block1"],"revision":1}',
                                ),
                            ),
                        ),
                    ),
                ),
                array(
                    'page' => 'index',
                    'language' => 'en',
                    'country' => 'GB',
                    'slot' => 'logo',
                    'type' => 'Link',
                    'position' => 1,
                    'direction' => 'bottom',
                    'block' => '{"foo":"bar"}',
                    'baseBlock' => '{"foo":"bar"}',
                    'blockname' => 'block2',
                ),
                'block2',
                array(
                    'root\redkitecms.com\slots\logo\active\blocks\block1.json' => '{"slot_name":"logo","name":"block1","list_name":"","type":"Text","is_child":false,"editor_disabled":false,"custom_tag":"rkcms-text","history_name":"","history":[],"revision":1,"is_removed":false,"html":"<p>This is a text<\/p>","editor_configuration":"standard"}',
                    'root\redkitecms.com\slots\logo\active\blocks\block2.json' => '{"foo":"bar"}',
                    'root\redkitecms.com\slots\logo\active\slot.json' => '{"next":3,"blocks":["block1","block2"],"revision":1}',
                ),
                'Block "block2" has been added to the "logo" slot on page "index" for the "en_GB" language',
                null
            ),
            // Block2 is added under block1 after creating the contributors folder
            array(
                array(
                    'redkitecms.com' => array(
                        'slots' => array(
                            'logo' => array(
                                'active' => array(
                                    'blocks' => array(
                                        'block1.json' => '{"slot_name":"logo","name":"block1","list_name":"","type":"Text","is_child":false,"editor_disabled":false,"custom_tag":"rkcms-text","history_name":"","history":[],"revision":1,"is_removed":false,"html":"<p>This is a text<\/p>","editor_configuration":"standard"}',
                                    ),
                                    'slot.json' => '{"next":2,"blocks":["block1"],"revision":1}',
                                ),
                            ),
                        ),
                    ),
                ),
                array(
                    'page' => 'index',
                    'language' => 'en',
                    'country' => 'GB',
                    'slot' => 'logo',
                    'type' => 'Link',
                    'position' => 1,
                    'direction' => 'bottom',
                    'block' => '{"foo":"bar"}',
                    'baseBlock' => '{"foo":"bar"}',
                    'blockname' => 'block2',
                ),
                'block2',
                array(
                    'root\redkitecms.com\slots\logo\contributors\john\blocks\block1.json' => '{"slot_name":"logo","name":"block1","list_name":"","type":"Text","is_child":false,"editor_disabled":false,"custom_tag":"rkcms-text","history_name":"","history":[],"revision":1,"is_removed":false,"html":"<p>This is a text<\/p>","editor_configuration":"standard"}',
                    'root\redkitecms.com\slots\logo\contributors\john\blocks\block2.json' => '{"foo":"bar"}',
                    'root\redkitecms.com\slots\logo\contributors\john\slot.json' => '{"next":3,"blocks":["block1","block2"],"revision":1}',
                ),
                'Block "block2" has been added to the "logo" slot on page "index" for the "en_GB" language'
            ),
            // Block2 is added under block1
            array(
                array(
                    'redkitecms.com' => array(
                        'slots' => array(
                            'logo' => array(
                                'contributors' => array(
                                    'john' => array(
                                        'blocks' => array(
                                            'block1.json' => '{"slot_name":"logo","name":"block1","list_name":"","type":"Text","is_child":false,"editor_disabled":false,"custom_tag":"rkcms-text","history_name":"","history":[],"revision":1,"is_removed":false,"html":"<p>This is a text<\/p>","editor_configuration":"standard"}',
                                        ),
                                        'slot.json' => '{"next":2,"blocks":["block1"],"revision":1}',
                                    ),
                                ),
                            ),
                        ),
                    ),
                ),
                array(
                    'page' => 'index',
                    'language' => 'en',
                    'country' => 'GB',
                    'slot' => 'logo',
                    'type' => 'Link',
                    'position' => 1,
                    'direction' => 'bottom',
                    'block' => '{"foo":"bar"}',
                    'baseBlock' => '{"foo":"bar"}',
                    'blockname' => 'block2',
                ),
                'block2',
                array(
                    'root\redkitecms.com\slots\logo\contributors\john\blocks\block1.json' => '{"slot_name":"logo","name":"block1","list_name":"","type":"Text","is_child":false,"editor_disabled":false,"custom_tag":"rkcms-text","history_name":"","history":[],"revision":1,"is_removed":false,"html":"<p>This is a text<\/p>","editor_configuration":"standard"}',
                    'root\redkitecms.com\slots\logo\contributors\john\blocks\block2.json' => '{"foo":"bar"}',
                    'root\redkitecms.com\slots\logo\contributors\john\slot.json' => '{"next":3,"blocks":["block1","block2"],"revision":1}',
                ),
                'Block "block2" has been added to the "logo" slot on page "index" for the "en_GB" language'
            ),
            // Block2 is added above block1
            array(
                array(
                    'redkitecms.com' => array(
                        'slots' => array(
                            'logo' => array(
                                'contributors' => array(
                                    'john' => array(
                                        'blocks' => array(
                                            'block1.json' => '{"slot_name":"logo","name":"block1","list_name":"","type":"Text","is_child":false,"editor_disabled":false,"custom_tag":"rkcms-text","history_name":"","history":[],"revision":1,"is_removed":false,"html":"<p>This is a text<\/p>","editor_configuration":"standard"}',
                                        ),
                                        'slot.json' => '{"next":2,"blocks":["block1"],"revision":1}',
                                    ),
                                ),
                            ),
                        ),
                    ),
                ),
                array(
                    'page' => 'index',
                    'language' => 'en',
                    'country' => 'GB',
                    'slot' => 'logo',
                    'type' => 'Link',
                    'position' => 0,
                    'direction' => 'top',
                    'block' => '{"foo":"bar"}',
                    'baseBlock' => '{"foo":"bar"}',
                    'blockname' => 'block2',
                ),
                'block2',
                array(
                    'root\redkitecms.com\slots\logo\contributors\john\blocks\block1.json' => '{"slot_name":"logo","name":"block1","list_name":"","type":"Text","is_child":false,"editor_disabled":false,"custom_tag":"rkcms-text","history_name":"","history":[],"revision":1,"is_removed":false,"html":"<p>This is a text<\/p>","editor_configuration":"standard"}',
                    'root\redkitecms.com\slots\logo\contributors\john\blocks\block2.json' => '{"foo":"bar"}',
                    'root\redkitecms.com\slots\logo\contributors\john\slot.json' => '{"next":3,"blocks":["block2","block1"],"revision":1}',
                ),
                'Block "block2" has been added to the "logo" slot on page "index" for the "en_GB" language'
            ),
            // Block3 is added between block1 and block2
            array(
                array(
                    'redkitecms.com' => array(
                        'slots' => array(
                            'logo' => array(
                                'contributors' => array(
                                    'john' => array(
                                        'blocks' => array(
                                            'block1.json' => '{"slot_name":"logo","name":"block1","list_name":"","type":"Text","is_child":false,"editor_disabled":false,"custom_tag":"rkcms-text","history_name":"","history":[],"revision":1,"is_removed":false,"html":"<p>This is a text<\/p>","editor_configuration":"standard"}',
                                            'block2.json' => '{"slot_name":"logo","name":"block2","list_name":"","type":"Text","is_child":false,"editor_disabled":false,"custom_tag":"rkcms-text","history_name":"","history":[],"revision":1,"is_removed":false,"html":"<p>This is a text<\/p>","editor_configuration":"standard"}',
                                        ),
                                        'slot.json' => '{"next":3,"blocks":["block1","block2"],"revision":1}',
                                    ),
                                ),
                            ),
                        ),
                    ),
                ),
                array(
                    'page' => 'index',
                    'language' => 'en',
                    'country' => 'GB',
                    'slot' => 'logo',
                    'type' => 'Link',
                    'position' => 1,
                    'direction' => 'top',
                    'block' => '{"foo":"bar"}',
                    'baseBlock' => '{"foo":"bar"}',
                    'blockname' => 'block3',
                ),
                'block3',
                array(
                    'root\redkitecms.com\slots\logo\contributors\john\blocks\block1.json' => '{"slot_name":"logo","name":"block1","list_name":"","type":"Text","is_child":false,"editor_disabled":false,"custom_tag":"rkcms-text","history_name":"","history":[],"revision":1,"is_removed":false,"html":"<p>This is a text<\/p>","editor_configuration":"standard"}',
                    'root\redkitecms.com\slots\logo\contributors\john\blocks\block2.json' => '{"slot_name":"logo","name":"block2","list_name":"","type":"Text","is_child":false,"editor_disabled":false,"custom_tag":"rkcms-text","history_name":"","history":[],"revision":1,"is_removed":false,"html":"<p>This is a text<\/p>","editor_configuration":"standard"}',
                    'root\redkitecms.com\slots\logo\contributors\john\blocks\block3.json' => '{"foo":"bar"}',
                    'root\redkitecms.com\slots\logo\contributors\john\slot.json' => '{"next":4,"blocks":["block1","block3","block2"],"revision":1}',
                ),
                'Block "block3" has been added to the "logo" slot on page "index" for the "en_GB" language'
            ),
        );
    }
}