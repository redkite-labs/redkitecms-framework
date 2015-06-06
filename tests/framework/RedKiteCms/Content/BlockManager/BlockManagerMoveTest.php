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
use RedKiteCms\Content\BlockManager\BlockManagerMove;

/**
 * BlockManagerMoveTest
 *
 * @author RedKite Labs <webmaster@redkite-labs.com>
 */
class BlockManagerMoveTest extends BlockManagerBaseTestCase
{    
    private $blockManager;
    
    protected function setUp()
    {
        parent::setUp();

        $this->blockManager = new BlockManagerMove($this->serializer, $this->optionsResolver);
    }

    /**
     * @dataProvider moveProvider
     */
    public function testMove($folders, $options, $expectedBlock, $expectedEditedBlocks, $nonExistentBlocks = array(), $historyFile = "", $historyValue = "")
    {
        $this->configureFilesystem($folders);
        
        unset($options["internalOptions"]);
        //print_r(vfsStream::inspect(new \org\bovigo\vfs\visitor\vfsStreamStructureVisitor())->getStructure());exit;
        $block = $this->blockManager->move(vfsStream::url('root\redkitecms.com'), $options, "john");
        $this->assertEquals($block, $expectedBlock);
        $this->checkBlockFiles($expectedEditedBlocks);
        $this->checkNonExistentFiles($nonExistentBlocks);
        if (!empty($historyFile)) {
            $history = json_decode(file_get_contents($historyFile), true)
            ;
            $this->assertEquals($history, json_decode($historyValue, true));
        }
    }

    /**
     * @codeCoverageIgnore
     */
    public function moveProvider()
    {
        return array(
            array(
                array(
                    'redkitecms.com' => array(
                        'slots' => array(
                            'logo' => array(
                                'contributors' => array(
                                    'john' => array(
                                        'blocks' => array(
                                            'block1.json' => '{"slot_name":"logo","name":"block1","list_name":"","type":"Text","is_child":false,"editor_disabled":false,"custom_tag":"rkcms-text","history_name":"","history":[],"revision":1,"is_removed":false,"html":"<p>Block1 text<\/p>","editor_configuration":"standard"}',
                                            'block2.json' => '{"slot_name":"logo","name":"block2","list_name":"","type":"Text","is_child":false,"editor_disabled":false,"custom_tag":"rkcms-text","history_name":"","history":[],"revision":1,"is_removed":false,"html":"<p>Block2 text<\/p>","editor_configuration":"standard"}',
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
                    'sourceSlot' => 'logo',
                    'blockname' => 'block2',
                    'position' => 0,
                ),
                '{"slot_name":"logo","name":"block2","list_name":"","type":"Text","is_child":false,"editor_disabled":false,"custom_tag":"rkcms-text","history_name":"","history":[],"revision":1,"is_removed":false,"html":"<p>Block2 text<\/p>","editor_configuration":"standard"}',
                array(
                    'root\redkitecms.com\slots\logo\contributors\john\blocks\block1.json' => '{"slot_name":"logo","name":"block1","list_name":"","type":"Text","is_child":false,"editor_disabled":false,"custom_tag":"rkcms-text","history_name":"","history":[],"revision":1,"is_removed":false,"html":"<p>Block1 text<\/p>","editor_configuration":"standard"}',
                    'root\redkitecms.com\slots\logo\contributors\john\blocks\block2.json' => '{"slot_name":"logo","name":"block2","list_name":"","type":"Text","is_child":false,"editor_disabled":false,"custom_tag":"rkcms-text","history_name":"","history":[],"revision":1,"is_removed":false,"html":"<p>Block2 text<\/p>","editor_configuration":"standard"}',
                    'root\redkitecms.com\slots\logo\contributors\john\slot.json' => '{"next":3,"blocks":["block2","block1"],"revision":1}',
                ),
            ),
            array(
                array(
                    'redkitecms.com' => array(
                        'slots' => array(
                            'logo' => array(
                                'contributors' => array(
                                    'john' => array(
                                        'blocks' => array(
                                            'block1.json' => '{"slot_name":"logo","name":"block1","list_name":"","type":"Text","is_child":false,"editor_disabled":false,"custom_tag":"rkcms-text","history_name":"","history":[],"revision":1,"is_removed":false,"html":"<p>Block1 text<\/p>","editor_configuration":"standard"}',
                                            'block2.json' => '{"slot_name":"logo","name":"block2","list_name":"","type":"Text","is_child":false,"editor_disabled":false,"custom_tag":"rkcms-text","history_name":"","history":[],"revision":1,"is_removed":false,"html":"<p>Block2 text<\/p>","editor_configuration":"standard"}',
                                            'block3.json' => '{"slot_name":"logo","name":"block3","list_name":"","type":"Text","is_child":false,"editor_disabled":false,"custom_tag":"rkcms-text","history_name":"","history":[],"revision":1,"is_removed":false,"html":"<p>Block3 text<\/p>","editor_configuration":"standard"}',
                                        ),
                                        'slot.json' => '{"next":4,"blocks":["block1","block2","block3"],"revision":1}',
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
                    'sourceSlot' => 'logo',
                    'blockname' => 'block3',
                    'position' => 1,
                ),
                '{"slot_name":"logo","name":"block3","list_name":"","type":"Text","is_child":false,"editor_disabled":false,"custom_tag":"rkcms-text","history_name":"","history":[],"revision":1,"is_removed":false,"html":"<p>Block3 text<\/p>","editor_configuration":"standard"}',
                array(
                    'root\redkitecms.com\slots\logo\contributors\john\blocks\block1.json' => '{"slot_name":"logo","name":"block1","list_name":"","type":"Text","is_child":false,"editor_disabled":false,"custom_tag":"rkcms-text","history_name":"","history":[],"revision":1,"is_removed":false,"html":"<p>Block1 text<\/p>","editor_configuration":"standard"}',
                    'root\redkitecms.com\slots\logo\contributors\john\blocks\block2.json' => '{"slot_name":"logo","name":"block2","list_name":"","type":"Text","is_child":false,"editor_disabled":false,"custom_tag":"rkcms-text","history_name":"","history":[],"revision":1,"is_removed":false,"html":"<p>Block2 text<\/p>","editor_configuration":"standard"}',
                    'root\redkitecms.com\slots\logo\contributors\john\blocks\block3.json' => '{"slot_name":"logo","name":"block3","list_name":"","type":"Text","is_child":false,"editor_disabled":false,"custom_tag":"rkcms-text","history_name":"","history":[],"revision":1,"is_removed":false,"html":"<p>Block3 text<\/p>","editor_configuration":"standard"}',
                    'root\redkitecms.com\slots\logo\contributors\john\slot.json' => '{"next":4,"blocks":["block1","block3","block2"],"revision":1}',
                ),
            ),
            // Block1 from logo slot has been moved to menu slot over the block1 block. The dropped block has been renamed to
            // block2 because that slot already contains the block1 block and all it's archived blocks have been moved to new
            // slot
            array(
                array(
                    'redkitecms.com' => array(
                        'slots' => array(
                            'logo' => array(
                                'contributors' => array(
                                    'john' => array(
                                        'archive' => array(
                                            'block1' => array(
                                                'history.json' => '
                                                {"2014-11-18-19.25.43":{"slot_name":"logo","name":"block1","list_name":"","type":"Text","is_child":false,"editor_disabled":false,"custom_tag":"rkcms-text","history_name":"","history":[],"revision":1,"is_removed":false,"html":"<p>Block1 first text<\/p>","editor_configuration":"standard"},
                                                "2014-11-18-19.26.14":{"slot_name":"logo","name":"block1","list_name":"","type":"Text","is_child":false,"editor_disabled":false,"custom_tag":"rkcms-text","history_name":"","history":[],"revision":1,"is_removed":false,"html":"<p>Block1 second text<\/p>","editor_configuration":"standard"},
                                                "2014-11-18-19.26.58":{"slot_name":"logo","name":"block1","list_name":"","type":"Text","is_child":false,"editor_disabled":false,"custom_tag":"rkcms-text","history_name":"","history":[],"revision":1,"is_removed":false,"html":"<p>Block1 third text<\/p>","editor_configuration":"standard"}}',
                                            ),
                                        ),
                                        'blocks' => array(
                                            'block1.json' => '{"slot_name":"logo","name":"block1","list_name":"","type":"Text","is_child":false,"editor_disabled":false,"custom_tag":"rkcms-text","history_name":"","history":[],"revision":1,"is_removed":false,"html":"<p>Block1 text<\/p>","editor_configuration":"standard"}',
                                        ),
                                        'slot.json' => '{"next":2,"blocks":["block1"],"revision":1}',
                                    ),
                                ),
                            ),
                            'menu' => array(
                                'contributors' => array(
                                    'john' => array(
                                        'blocks' => array(
                                            'block1.json' => '{"slot_name":"menu","name":"block1","list_name":"","type":"Text","is_child":false,"editor_disabled":false,"custom_tag":"rkcms-text","history_name":"","history":[],"revision":1,"is_removed":false,"html":"<p>Block1 text<\/p>","editor_configuration":"standard"}',
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
                    'oldName' => 'block1',
                    'newName' => 'block2',
                    'position' => 0,
                    'sourceSlot' => 'logo',
                    'targetSlot' => 'menu',
                ),
                '{"slot_name":"menu","name":"block2","list_name":"","type":"Text","is_child":false,"editor_disabled":false,"custom_tag":"rkcms-text","history_name":"","history":[],"revision":1,"is_removed":false,"html":"<p>Block1 text<\/p>","editor_configuration":"standard"}',
                array(
                    'root\redkitecms.com\slots\menu\contributors\john\blocks\block1.json' => '{"slot_name":"menu","name":"block1","list_name":"","type":"Text","is_child":false,"editor_disabled":false,"custom_tag":"rkcms-text","history_name":"","history":[],"revision":1,"is_removed":false,"html":"<p>Block1 text<\/p>","editor_configuration":"standard"}',
                    'root\redkitecms.com\slots\menu\contributors\john\blocks\block2.json' => '{"slot_name":"menu","name":"block2","list_name":"","type":"Text","is_child":false,"editor_disabled":false,"custom_tag":"rkcms-text","history_name":"","history":[],"revision":1,"is_removed":false,"html":"<p>Block1 text<\/p>","editor_configuration":"standard"}',
                    'root\redkitecms.com\slots\logo\contributors\john\slot.json' => '{"next":2,"blocks":[],"revision":1}',
                    'root\redkitecms.com\slots\menu\contributors\john\slot.json' => '{"next":3,"blocks":["block2","block1"],"revision":1}',
                ),
                array(
                    'root\redkitecms.com\slots\logo\contributors\john\blocks\block1.json',
                    'root\redkitecms.com\slots\logo\contributors\john\archive\block1',
                ),
                'vfs://root\redkitecms.com\slots\menu\contributors\john\archive\block2\history.json',
                '{"2014-11-18-19.25.43":{"slot_name":"menu","name":"block2","list_name":"","type":"Text","is_child":false,"editor_disabled":false,"custom_tag":"rkcms-text","history_name":"","history":[],"revision":1,"is_removed":false,"html":"<p>Block1 first text<\/p>","editor_configuration":"standard"},"2014-11-18-19.26.14":{"slot_name":"menu","name":"block2","list_name":"","type":"Text","is_child":false,"editor_disabled":false,"custom_tag":"rkcms-text","history_name":"","history":[],"revision":1,"is_removed":false,"html":"<p>Block1 second text<\/p>","editor_configuration":"standard"},"2014-11-18-19.26.58":{"slot_name":"menu","name":"block2","list_name":"","type":"Text","is_child":false,"editor_disabled":false,"custom_tag":"rkcms-text","history_name":"","history":[],"revision":1,"is_removed":false,"html":"<p>Block1 third text<\/p>","editor_configuration":"standard"}}',
            ),
            // The same test above but RedKite CMS creates the contributors folder on the slots where the content is moved
            array(
                array(
                    'redkitecms.com' => array(
                        'slots' => array(
                            'logo' => array(
                                'contributors' => array(
                                    'john' => array(
                                        'archive' => array(
                                            'block1' => array(
                                                'history.json' => '
                                                {"2014-11-18-19.25.43":{"slot_name":"logo","name":"block1","list_name":"","type":"Text","is_child":false,"editor_disabled":false,"custom_tag":"rkcms-text","history_name":"","history":[],"revision":1,"is_removed":false,"html":"<p>Block1 first text<\/p>","editor_configuration":"standard"},
                                                "2014-11-18-19.26.14":{"slot_name":"logo","name":"block1","list_name":"","type":"Text","is_child":false,"editor_disabled":false,"custom_tag":"rkcms-text","history_name":"","history":[],"revision":1,"is_removed":false,"html":"<p>Block1 second text<\/p>","editor_configuration":"standard"},
                                                "2014-11-18-19.26.58":{"slot_name":"logo","name":"block1","list_name":"","type":"Text","is_child":false,"editor_disabled":false,"custom_tag":"rkcms-text","history_name":"","history":[],"revision":1,"is_removed":false,"html":"<p>Block1 third text<\/p>","editor_configuration":"standard"}}',
                                            ),
                                        ),
                                        'blocks' => array(
                                            'block1.json' => '{"slot_name":"logo","name":"block1","list_name":"","type":"Text","is_child":false,"editor_disabled":false,"custom_tag":"rkcms-text","history_name":"","history":[],"revision":1,"is_removed":false,"html":"<p>Block1 text<\/p>","editor_configuration":"standard"}',
                                        ),
                                        'slot.json' => '{"next":2,"blocks":["block1"],"revision":1}',
                                    ),
                                ),
                            ),
                            'menu' => array(
                                'active' => array(
                                    'blocks' => array(
                                        'block1.json' => '{"slot_name":"menu","name":"block1","list_name":"","type":"Text","is_child":false,"editor_disabled":false,"custom_tag":"rkcms-text","history_name":"","history":[],"revision":1,"is_removed":false,"html":"<p>Block1 text<\/p>","editor_configuration":"standard"}',
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
                    'oldName' => 'block1',
                    'newName' => 'block2',
                    'position' => 0,
                    'sourceSlot' => 'logo',
                    'targetSlot' => 'menu',
                ),
                '{"slot_name":"menu","name":"block2","list_name":"","type":"Text","is_child":false,"editor_disabled":false,"custom_tag":"rkcms-text","history_name":"","history":[],"revision":1,"is_removed":false,"html":"<p>Block1 text<\/p>","editor_configuration":"standard"}',
                array(
                    'root\redkitecms.com\slots\menu\contributors\john\blocks\block1.json' => '{"slot_name":"menu","name":"block1","list_name":"","type":"Text","is_child":false,"editor_disabled":false,"custom_tag":"rkcms-text","history_name":"","history":[],"revision":1,"is_removed":false,"html":"<p>Block1 text<\/p>","editor_configuration":"standard"}',
                    'root\redkitecms.com\slots\menu\contributors\john\blocks\block2.json' => '{"slot_name":"menu","name":"block2","list_name":"","type":"Text","is_child":false,"editor_disabled":false,"custom_tag":"rkcms-text","history_name":"","history":[],"revision":1,"is_removed":false,"html":"<p>Block1 text<\/p>","editor_configuration":"standard"}',
                    'root\redkitecms.com\slots\logo\contributors\john\slot.json' => '{"next":2,"blocks":[],"revision":1}',
                    'root\redkitecms.com\slots\menu\contributors\john\slot.json' => '{"next":3,"blocks":["block2","block1"],"revision":1}',
                ),
                array(
                    'root\redkitecms.com\slots\logo\contributors\john\blocks\block1.json',
                    'root\redkitecms.com\slots\logo\contributors\john\archive\block1',
                ),
                'vfs://root\redkitecms.com\slots\menu\contributors\john\archive\block2\history.json',
                '{"2014-11-18-19.25.43":{"slot_name":"menu","name":"block2","list_name":"","type":"Text","is_child":false,"editor_disabled":false,"custom_tag":"rkcms-text","history_name":"","history":[],"revision":1,"is_removed":false,"html":"<p>Block1 first text<\/p>","editor_configuration":"standard"},"2014-11-18-19.26.14":{"slot_name":"menu","name":"block2","list_name":"","type":"Text","is_child":false,"editor_disabled":false,"custom_tag":"rkcms-text","history_name":"","history":[],"revision":1,"is_removed":false,"html":"<p>Block1 second text<\/p>","editor_configuration":"standard"},"2014-11-18-19.26.58":{"slot_name":"menu","name":"block2","list_name":"","type":"Text","is_child":false,"editor_disabled":false,"custom_tag":"rkcms-text","history_name":"","history":[],"revision":1,"is_removed":false,"html":"<p>Block1 third text<\/p>","editor_configuration":"standard"}}',
            ),
            // Block1 from logo slot has been moved to the empty menu slot
            array(
                array(
                    'redkitecms.com' => array(
                        'slots' => array(
                            'logo' => array(
                                'contributors' => array(
                                    'john' => array(
                                        'blocks' => array(
                                            'block1.json' => '{"slot_name":"logo","name":"block1","list_name":"","type":"Text","is_child":false,"editor_disabled":false,"custom_tag":"rkcms-text","history_name":"","history":[],"revision":1,"is_removed":false,"html":"<p>Block1 text<\/p>","editor_configuration":"standard"}',
                                        ),
                                        'slot.json' => '{"next":2,"blocks":["block1"],"revision":1}',
                                    ),
                                ),
                            ),
                            'menu' => array(
                                'contributors' => array(
                                    'john' => array(
                                        'blocks' => array(),
                                        'slot.json' => '{"next":1,"blocks":[],"revision":1}',
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
                    'oldName' => 'block1',
                    'newName' => 'block1',
                    'position' => 0,
                    'sourceSlot' => 'logo',
                    'targetSlot' => 'menu',
                ),
                '{"slot_name":"menu","name":"block1","list_name":"","type":"Text","is_child":false,"editor_disabled":false,"custom_tag":"rkcms-text","history_name":"","history":[],"revision":1,"is_removed":false,"html":"<p>Block1 text<\/p>","editor_configuration":"standard"}',
                array(
                    'root\redkitecms.com\slots\menu\contributors\john\blocks\block1.json' => '{"slot_name":"menu","name":"block1","list_name":"","type":"Text","is_child":false,"editor_disabled":false,"custom_tag":"rkcms-text","history_name":"","history":[],"revision":1,"is_removed":false,"html":"<p>Block1 text<\/p>","editor_configuration":"standard"}',
                    'root\redkitecms.com\slots\logo\contributors\john\slot.json' => '{"next":2,"blocks":[],"revision":1}',
                    'root\redkitecms.com\slots\menu\contributors\john\slot.json' => '{"next":2,"blocks":["block1"],"revision":1}',
                ),
                array(
                    'root\redkitecms.com\slots\logo\contributors\john\blocks\block1.json',
                ),
            ),
            // Block2 from logo slot has been moved to menu slot over the block1 block
            array(
                array(
                    'redkitecms.com' => array(
                        'slots' => array(
                            'logo' => array(
                                'contributors' => array(
                                    'john' => array(
                                        'blocks' => array(
                                            'block1.json' => '{"slot_name":"logo","name":"block1","list_name":"","type":"Text","is_child":false,"editor_disabled":false,"custom_tag":"rkcms-text","history_name":"","history":[],"revision":1,"is_removed":false,"html":"<p>Block1 text<\/p>","editor_configuration":"standard"}',
                                            'block2.json' => '{"slot_name":"logo","name":"block2","list_name":"","type":"Text","is_child":false,"editor_disabled":false,"custom_tag":"rkcms-text","history_name":"","history":[],"revision":1,"is_removed":false,"html":"<p>Block2 text<\/p>","editor_configuration":"standard"}',
                                        ),
                                        'slot.json' => '{"next":3,"blocks":["block2", "block1"],"revision":1}',
                                    ),
                                ),
                            ),
                            'menu' => array(
                                'contributors' => array(
                                    'john' => array(
                                        'blocks' => array(
                                            'block1.json' => '{"slot_name":"menu","name":"block1","list_name":"","type":"Text","is_child":false,"editor_disabled":false,"custom_tag":"rkcms-text","history_name":"","history":[],"revision":1,"is_removed":false,"html":"<p>Block1 text<\/p>","editor_configuration":"standard"}',
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
                    'oldName' => 'block2',
                    'newName' => 'block2',
                    'position' => 1,
                    'sourceSlot' => 'logo',
                    'targetSlot' => 'menu'
                ),
                '{"slot_name":"menu","name":"block2","list_name":"","type":"Text","is_child":false,"editor_disabled":false,"custom_tag":"rkcms-text","history_name":"","history":[],"revision":1,"is_removed":false,"html":"<p>Block2 text<\/p>","editor_configuration":"standard"}',
                array(
                    'root\redkitecms.com\slots\logo\contributors\john\blocks\block1.json' => '{"slot_name":"logo","name":"block1","list_name":"","type":"Text","is_child":false,"editor_disabled":false,"custom_tag":"rkcms-text","history_name":"","history":[],"revision":1,"is_removed":false,"html":"<p>Block1 text<\/p>","editor_configuration":"standard"}',
                    'root\redkitecms.com\slots\menu\contributors\john\blocks\block1.json' => '{"slot_name":"menu","name":"block1","list_name":"","type":"Text","is_child":false,"editor_disabled":false,"custom_tag":"rkcms-text","history_name":"","history":[],"revision":1,"is_removed":false,"html":"<p>Block1 text<\/p>","editor_configuration":"standard"}',
                    'root\redkitecms.com\slots\menu\contributors\john\blocks\block2.json' => '{"slot_name":"menu","name":"block2","list_name":"","type":"Text","is_child":false,"editor_disabled":false,"custom_tag":"rkcms-text","history_name":"","history":[],"revision":1,"is_removed":false,"html":"<p>Block2 text<\/p>","editor_configuration":"standard"}',
                    'root\redkitecms.com\slots\logo\contributors\john\slot.json' => '{"next":3,"blocks":["block1"],"revision":1}',
                    'root\redkitecms.com\slots\menu\contributors\john\slot.json' => '{"next":3,"blocks":["block1","block2"],"revision":1}',
                ),
                array(
                    'root\redkitecms.com\slots\logo\contributors\john\blocks\block2.json',
                ),
            ),
        );
    }
}