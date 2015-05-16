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
use RedKiteCms\Content\BlockManager\BlockManagerRestore;

/**
 * BlockManagerRestoreTest
 *
 * @author RedKite Labs <webmaster@redkite-labs.com>
 */
class BlockManagerRestoreTest extends BlockManagerBaseTestCase
{
    private $blockManager;
    
    protected function setUp()
    {
        parent::setUp();

        $this->blockManager = new BlockManagerRestore($this->serializer, $this->blockFactory, $this->optionsResolver);
    }

    /**
     * @dataProvider restoreProvider
     */
    public function testRestore($folders, $options, $restoreBlockName, $expectedRestoredBlock, $expectedAddedBlocks, $username = 'john')
    {
        $this->configureFilesystem($folders);
        $this->blockManager->restore(vfsStream::url('root\redkitecms.com'), $options, $username, $restoreBlockName);
        $this->checkBlockFiles($expectedAddedBlocks);
        $history = json_decode(file_get_contents('vfs://root/redkitecms.com/slots/logo/contributors/john/archive/block1/history.json'), true);

        // Checks that the active block just replaced has been added to history
        $historyBlock = array_values($history);
        $this->assertEquals(json_encode($historyBlock[0]), $expectedRestoredBlock);
    }

    /**
     * @codeCoverageIgnore
     */
    public function restoreProvider()
    {
        return array(
            // Block1 is restored from the '2014-11-18-19.25.43.json' archive file and the current block1 file is archived
            array(
                array(
                    'redkitecms.com' => array(
                        'slots' => array(
                            'logo' => array(
                                'contributors' => array(
                                    'john' => array(
                                        'archive' => array(
                                            'block1' => array(
                                                'history.json' => '{"2014-11-18-19.25.43":{"slot_name":"logo","name":"block1","list_name":"","type":"Text","is_child":false,"editor_disabled":false,"custom_tag":"rkcms-text","history_name":"","history":[],"revision":1,"is_removed":false,"html":"<p>This is a text<\/p>","editor_configuration":"standard"}}',
                                            ),
                                        ),
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
                ),
                '2014-11-18-19.25.43',
                '{"slot_name":"logo","name":"block1","list_name":"","type":"Text","is_child":false,"editor_disabled":false,"custom_tag":"rkcms-text","history_name":"","history":[],"revision":1,"is_removed":false,"html":"<p>John text<\/p>","editor_configuration":"standard"}',
                array(
                    'root\redkitecms.com\slots\logo\contributors\john\blocks\block1.json' => '{"slot_name":"logo","name":"block1","list_name":"","type":"Text","is_child":false,"editor_disabled":false,"custom_tag":"rkcms-text","history_name":"","history":[],"revision":1,"is_removed":false,"html":"<p>This is a text<\/p>","editor_configuration":"standard"}',
                    'root\redkitecms.com\slots\logo\contributors\john\slot.json' => '{"next":2,"blocks":["block1"],"revision":1}',
                ),
            ),
            // Block is not restored because the operation has been cancelled on the frontend
            array(
                array(
                    'redkitecms.com' => array(
                        'slots' => array(
                            'logo' => array(
                                'contributors' => array(
                                    'john' => array(
                                        'archive' => array(
                                            'block1' => array(
                                                'history.json' => '{"2014-11-18-19.25.43":{"slot_name":"logo","name":"block1","list_name":"","type":"Text","is_child":false,"editor_disabled":false,"custom_tag":"rkcms-text","history_name":"","history":[],"revision":1,"is_removed":false,"html":"<p>This is a text<\/p>","editor_configuration":"standard"}}',
                                            ),
                                        ),
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
                ),
                '2014-11-18-20.25.43',
                '{"slot_name":"logo","name":"block1","list_name":"","type":"Text","is_child":false,"editor_disabled":false,"custom_tag":"rkcms-text","history_name":"","history":[],"revision":1,"is_removed":false,"html":"<p>This is a text<\/p>","editor_configuration":"standard"}',
                array(
                    'root\redkitecms.com\slots\logo\contributors\john\blocks\block1.json' => '{"slot_name":"logo","name":"block1","list_name":"","type":"Text","is_child":false,"editor_disabled":false,"custom_tag":"rkcms-text","history_name":"","history":[],"revision":1,"is_removed":false,"html":"<p>John text<\/p>","editor_configuration":"standard"}',
                    'root\redkitecms.com\slots\logo\contributors\john\slot.json' => '{"next":2,"blocks":["block1"],"revision":1}',
                ),
            ),
        );
    }
}