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
use RedKiteCms\Content\BlockManager\BlockManagerApprover;

/**
 * BlockManagerApproverTest
 *
 * @author RedKite Labs <webmaster@redkite-labs.com>
 */
class BlockManagerApproverTest extends BlockManagerBaseTestCase
{   
    private $blockManager;
    
    protected function setUp()
    {
        parent::setUp();

        $this->blockManager = new BlockManagerApprover($this->serializer, $this->blockFactory, $this->optionsResolver);
    }

    /**
     * @dataProvider approveProvider
     */
    public function testApprove($folders, $options, $expectedActiveBlocks, $logMessage, $expectedNonExistentFiles = array(), $username = 'john')
    {
        $this->configureFilesystem($folders);
        $this->dispatch(0, 'block.approving', '\RedKiteCms\EventSystem\Event\Block\BlockApprovingEvent');
        $this->dispatch(1, 'block.approved', '\RedKiteCms\EventSystem\Event\Block\BlockApprovedEvent');
        $this->log(0, 'info', 'The "block.approving" event was dispatched');
        $this->log(1, 'info', 'The "block.approved" event was dispatched');
        $this->log(2, 'info', $logMessage);
        $this->blockManager->approve(vfsStream::url('root\redkitecms.com'), $options, $username);

        $this->checkBlockFiles($expectedActiveBlocks);
        $this->checkNonExistentFiles($expectedNonExistentFiles);
    }


    /**
     * @dataProvider approveRemovalProvider
     */
    public function testApproveRemoval($folders, $options, $expectedActiveBlocks, $logMessage)
    {
        $this->configureFilesystem($folders);
        $this->dispatch(0, 'block.approving_removal', '\RedKiteCms\EventSystem\Event\Block\BlockApprovingRemovalEvent');
        $this->dispatch(1, 'block.approved_removal', '\RedKiteCms\EventSystem\Event\Block\BlockApprovedRemovalEvent');
        $this->log(0, 'info', 'The "block.approving_removal" event was dispatched');
        $this->log(1, 'info', 'The "block.approved_removal" event was dispatched');
        $this->log(2, 'info', $logMessage);
        $this->blockManager->approveRemoval(vfsStream::url('root\redkitecms.com'), $options, 'john');

        $this->checkBlockFiles($expectedActiveBlocks);
    }

    public function approveProvider()
    {
        return array(
            // Block1 has been edited
            array(
                array(
                    'redkitecms.com' => array(
                        'slots' => array(
                            'logo' => array(
                                'active' => array(
                                    'archive' => array(),
                                    'blocks' => array(
                                        'block1.json' => '{"slot_name":"logo","name":"block1","list_name":"","type":"Text","is_child":false,"editor_disabled":false,"custom_tag":"rkcms-text","history_name":"","history":[],"revision":1,"is_removed":false,"html":"<p>This is a text<\/p>","editor_configuration":"standard"}',
                                    ),
                                    'slot.json' => '{"next":2,"blocks":["block1"],"revision":1}',
                                ),
                                'contributors' => array(
                                    'john' => array(
                                        'blocks' => array(
                                            'block1.json' => '{"slot_name":"logo","name":"block1","list_name":"","type":"Text","is_child":false,"editor_disabled":false,"custom_tag":"rkcms-text","history_name":"","history":[],"revision":1,"is_removed":false,"html":"<p>John text<\/p>","editor_configuration":"standard"}',
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
                    'blockname' => 'block1',
                    'block' => '{"slot_name":"logo","name":"block1","list_name":"","type":"Text","is_child":false,"editor_disabled":false,"custom_tag":"rkcms-text","history_name":"","history":[],"revision":1,"is_removed":false,"html":"<p>John text<\/p>","editor_configuration":"standard"}',
                ),
                array(
                    'root\redkitecms.com\slots\logo\active\blocks\block1.json' => '{"slot_name":"logo","name":"block1","list_name":"","type":"Text","is_child":false,"editor_disabled":false,"custom_tag":"rkcms-text","history_name":"","history":[],"revision":1,"is_removed":false,"html":"<p>John text<\/p>","editor_configuration":"standard"}',
                ),
                'Block "block1" has been approved on the "logo" slot on page "index" for the "en_GB" language',
            ),
            // Block2 has been added under the block1
            array(
                array(
                    'redkitecms.com' => array(
                        'slots' => array(
                            'logo' => array(
                                'active' => array(
                                    'archive' => array(),
                                    'blocks' => array(
                                        'block1.json' => '{"slot_name":"logo","name":"block1","list_name":"","type":"Text","is_child":false,"editor_disabled":false,"custom_tag":"rkcms-text","history_name":"","history":[],"revision":1,"is_removed":false,"html":"<p>This is a text<\/p>","editor_configuration":"standard"}',
                                    ),
                                    'slot.json' => '{"next":2,"blocks":["block1"],"revision":1}',
                                ),
                                'contributors' => array(
                                    'john' => array(
                                        'blocks' => array(
                                            'block1.json' => '{"slot_name":"logo","name":"block1","list_name":"","type":"Text","is_child":false,"editor_disabled":false,"custom_tag":"rkcms-text","history_name":"","history":[],"revision":1,"is_removed":false,"html":"<p>John text<\/p>","editor_configuration":"standard"}',
                                            'block2.json' => '{"slot_name":"logo","name":"block2","list_name":"","type":"Text","is_child":false,"editor_disabled":false,"custom_tag":"rkcms-text","history_name":"","history":[],"revision":1,"is_removed":false,"html":"<p>A new john text<\/p>","editor_configuration":"standard"}',
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
                    'blockname' => 'block2',
                    'position' => 2,
                ),
                array(
                    'root\redkitecms.com\slots\logo\active\blocks\block1.json' => '{"slot_name":"logo","name":"block1","list_name":"","type":"Text","is_child":false,"editor_disabled":false,"custom_tag":"rkcms-text","history_name":"","history":[],"revision":1,"is_removed":false,"html":"<p>This is a text<\/p>","editor_configuration":"standard"}',
                    'root\redkitecms.com\slots\logo\active\blocks\block2.json' => '{"slot_name":"logo","name":"block2","list_name":"","type":"Text","is_child":false,"editor_disabled":false,"custom_tag":"rkcms-text","history_name":"","history":[],"revision":1,"is_removed":false,"html":"<p>A new john text<\/p>","editor_configuration":"standard"}',
                ),
                'Block "block2" has been approved on the "logo" slot on page "index" for the "en_GB" language',
            ),
            // Block2 has been added above the block1
            array(
                array(
                    'redkitecms.com' => array(
                        'slots' => array(
                            'logo' => array(
                                'active' => array(
                                    'archive' => array(),
                                    'blocks' => array(
                                        'block1.json' => '{"slot_name":"logo","name":"block1","list_name":"","type":"Text","is_child":false,"editor_disabled":false,"custom_tag":"rkcms-text","history_name":"","history":[],"revision":1,"is_removed":false,"html":"<p>This is a text<\/p>","editor_configuration":"standard"}',
                                    ),
                                    'slot.json' => '{"next":2,"blocks":["block1"],"revision":1}',
                                ),
                                'contributors' => array(
                                    'john' => array(
                                        'blocks' => array(
                                            'block1.json' => '{"slot_name":"logo","name":"block1","list_name":"","type":"Text","is_child":false,"editor_disabled":false,"custom_tag":"rkcms-text","history_name":"","history":[],"revision":1,"is_removed":false,"html":"<p>John text<\/p>","editor_configuration":"standard"}',
                                            'block2.json' => '{"slot_name":"logo","name":"block2","list_name":"","type":"Text","is_child":false,"editor_disabled":false,"custom_tag":"rkcms-text","history_name":"","history":[],"revision":1,"is_removed":false,"html":"<p>A new john text<\/p>","editor_configuration":"standard"}',
                                        ),
                                        'slot.json' => '{"next":3,"blocks":["block2","block1"],"revision":1}',
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
                    'blockname' => 'block2',
                    'position' => 1,
                ),
                array(
                    'root\redkitecms.com\slots\logo\active\blocks\block2.json' => '{"slot_name":"logo","name":"block2","list_name":"","type":"Text","is_child":false,"editor_disabled":false,"custom_tag":"rkcms-text","history_name":"","history":[],"revision":1,"is_removed":false,"html":"<p>A new john text<\/p>","editor_configuration":"standard"}',
                    'root\redkitecms.com\slots\logo\active\blocks\block1.json' => '{"slot_name":"logo","name":"block1","list_name":"","type":"Text","is_child":false,"editor_disabled":false,"custom_tag":"rkcms-text","history_name":"","history":[],"revision":1,"is_removed":false,"html":"<p>This is a text<\/p>","editor_configuration":"standard"}',
                ),
                'Block "block2" has been approved on the "logo" slot on page "index" for the "en_GB" language',
            ),
        );
    }

    public function approveRemovalProvider()
    {
        return array(
            array(
                array(
                    'redkitecms.com' => array(
                        'slots' => array(
                            'logo' => array(
                                'active' => array(
                                    'archive' => array(),
                                    'blocks' => array(
                                        'block1.json' => '{"slot_name":"logo","name":"block1","list_name":"","type":"Text","is_child":false,"editor_disabled":false,"custom_tag":"rkcms-text","history_name":"","history":[],"revision":1,"is_removed":false,"html":"<p>This is a text<\/p>","editor_configuration":"standard"}',
                                        'block2.json' => '{"slot_name":"logo","name":"block2","list_name":"","type":"Text","is_child":false,"editor_disabled":false,"custom_tag":"rkcms-text","history_name":"","history":[],"revision":1,"is_removed":false,"html":"<p>This is a text<\/p>","editor_configuration":"standard"}',
                                    ),
                                    'slot.json' => '{"next":3,"blocks":["block1","block2"],"revision":1}',
                                ),
                                'contributors' => array(
                                    'john' => array(
                                        'blocks' => array(
                                            'block2.json' => '{"slot_name":"logo","name":"block2","list_name":"","type":"Text","is_child":false,"editor_disabled":false,"custom_tag":"rkcms-text","history_name":"","history":[],"revision":1,"is_removed":false,"html":"<p>This is a text<\/p>","editor_configuration":"standard"}',
                                        ),
                                        'slot.json' => '{"next":3,"blocks":["block2"],"revision":1}',
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
                    'blockname' => 'block1',
                ),
                array(
                    'root\redkitecms.com\slots\logo\active\blocks\block2.json' => '{"slot_name":"logo","name":"block2","list_name":"","type":"Text","is_child":false,"editor_disabled":false,"custom_tag":"rkcms-text","history_name":"","history":[],"revision":1,"is_removed":false,"html":"<p>This is a text<\/p>","editor_configuration":"standard"}',
                ),
                'Block "block1" has been approved for removal on the "logo" slot on page "index" for the "en_GB" language',
            ),
            array(
                array(
                    'redkitecms.com' => array(
                        'slots' => array(
                            'logo' => array(
                                'active' => array(
                                    'archive' => array(),
                                    'blocks' => array(
                                        'block1.json' => '{"slot_name":"logo","name":"block1","list_name":"","type":"Text","is_child":false,"editor_disabled":false,"custom_tag":"rkcms-text","history_name":"","history":[],"revision":1,"is_removed":false,"html":"<p>This is a text<\/p>","editor_configuration":"standard"}',
                                        'block2.json' => '{"slot_name":"logo","name":"block2","list_name":"","type":"Text","is_child":false,"editor_disabled":false,"custom_tag":"rkcms-text","history_name":"","history":[],"revision":1,"is_removed":false,"html":"<p>This is a text<\/p>","editor_configuration":"standard"}',
                                    ),
                                    'slot.json' => '{"next":3,"blocks":["block1","block2"],"revision":1}',
                                ),
                                'contributors' => array(
                                    'john' => array(
                                        'blocks' => array(
                                            'block1.json' => '{"slot_name":"logo","name":"block1","list_name":"","type":"Text","is_child":false,"editor_disabled":false,"custom_tag":"rkcms-text","history_name":"","history":[],"revision":1,"is_removed":false,"html":"<p>This is a text<\/p>","editor_configuration":"standard"}',
                                        ),
                                        'slot.json' => '{"next":3,"blocks":["block2"],"revision":1}',
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
                    'blockname' => 'block2',
                ),
                array(
                    'root\redkitecms.com\slots\logo\active\blocks\block1.json' => '{"slot_name":"logo","name":"block1","list_name":"","type":"Text","is_child":false,"editor_disabled":false,"custom_tag":"rkcms-text","history_name":"","history":[],"revision":1,"is_removed":false,"html":"<p>This is a text<\/p>","editor_configuration":"standard"}',
                ),
                'Block "block2" has been approved for removal on the "logo" slot on page "index" for the "en_GB" language',
            ),
            array(
                array(
                    'redkitecms.com' => array(
                        'slots' => array(
                            'logo' => array(
                                'active' => array(
                                    'archive' => array(),
                                    'blocks' => array(
                                        'block1.json' => '{"slot_name":"logo","name":"block1","list_name":"","type":"Text","is_child":false,"editor_disabled":false,"custom_tag":"rkcms-text","history_name":"","history":[],"revision":1,"is_removed":false,"html":"<p>This is a text<\/p>","editor_configuration":"standard"}',
                                    ),
                                    'slot.json' => '{"next":2,"blocks":["block1"],"revision":1}',
                                ),
                                'contributors' => array(
                                    'john' => array(
                                        'blocks' => array(
                                        ),
                                        'slot.json' => '{"next":2,"blocks":[],"revision":1}',
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
                    'blockname' => 'block1',
                ),
                array(
                ),
                'Block "block1" has been approved for removal on the "logo" slot on page "index" for the "en_GB" language',
            ),
        );
    }
}