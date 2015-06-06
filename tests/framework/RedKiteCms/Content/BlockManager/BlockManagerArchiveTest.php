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
use RedKiteCms\Content\BlockManager\BlockManagerArchive;
use RedKiteCms\Content\BlockManager\BlockManagerRestore;

/**
 * BlockManagerArchiveTest
 *
 * @author RedKite Labs <webmaster@redkite-labs.com>
 */
class BlockManagerArchiveTest extends BlockManagerBaseTestCase
{
    private $blockManager;
    
    protected function setUp()
    {
        parent::setUp();

        $this->blockManager = new BlockManagerArchive($this->serializer, $this->optionsResolver);
    }

    /**
     * @dataProvider archiveProvider
     */
    public function testArchive($folders, $options, $expectedAddedBlocks, $username = 'john')
    {
        $this->configureFilesystem($folders);
        //print_r(vfsStream::inspect(new \org\bovigo\vfs\visitor\vfsStreamStructureVisitor())->getStructure());exit;
        $block = file_get_contents('vfs://root/redkitecms.com/slots/logo/contributors/john/blocks/block1.json');

        $this->blockManager->archive(vfsStream::url('root\redkitecms.com'), $options, $username, $block);
        $this->checkBlockFiles($expectedAddedBlocks);
    }

    public function archiveProvider()
    {
        return array(
            array(
                array(
                    'redkitecms.com' => array(
                        'slots' => array(
                            'logo' => array(
                                'contributors' => array(
                                    'john' => array(
                                        'archive' => array(
                                        ),
                                        'blocks' => array(
                                            'block1.json' => '{"slot_name":"logo","name":"block1","list_name":"","type":"Text","is_child":false,"editor_disabled":false,"custom_tag":"rkcms-text","history_name":"","history":[],"revision":1,"is_removed":false,"html":"<p>This is a text<\/p>","editor_configuration":"standard","history_name":"2015-05-06-08.45.59"}',
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
                array(
                    'root/redkitecms.com/slots/logo/contributors/john/archive/block1/history.json' => '{"2015-05-06-08.45.59":{"slot_name":"logo","name":"block1","list_name":"","type":"Text","is_child":false,"editor_disabled":false,"custom_tag":"rkcms-text","history_name":"2015-05-06-08.45.59","history":[],"revision":1,"is_removed":false,"html":"<p>This is a text<\/p>","editor_configuration":"standard"}}',
                    'root\redkitecms.com\slots\logo\contributors\john\slot.json' => '{"next":2,"blocks":["block1"],"revision":1}',
                ),
            ),
            array(
                array(
                    'redkitecms.com' => array(
                        'slots' => array(
                            'logo' => array(
                                'contributors' => array(
                                    'john' => array(
                                        'archive' => array(
                                            'block1' => array(
                                                'history.json' => '{"2015-04-06-08.45.59": {"slot_name":"logo","name":"block1","list_name":"","type":"Text","is_child":false,"editor_disabled":false,"custom_tag":"rkcms-text","history_name":"","history":[],"revision":1,"is_removed":false,"html":"<p>This is a text<\/p>","editor_configuration":"standard","history_name":""}}'
                                            ),
                                        ),
                                        'blocks' => array(
                                            'block1.json' => '{"slot_name":"logo","name":"block1","list_name":"","type":"Text","is_child":false,"editor_disabled":false,"custom_tag":"rkcms-text","history_name":"","history":[],"revision":1,"is_removed":false,"html":"<p>This is a text<\/p>","editor_configuration":"standard","history_name":"2015-05-06-08.45.59"}',
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
                array(
                    'root/redkitecms.com/slots/logo/contributors/john/archive/block1/history.json' => '{"2015-04-06-08.45.59":{"slot_name":"logo","name":"block1","list_name":"","type":"Text","is_child":false,"editor_disabled":false,"custom_tag":"rkcms-text","history_name":"","history":[],"revision":1,"is_removed":false,"html":"<p>This is a text<\/p>","editor_configuration":"standard"},"2015-05-06-08.45.59":{"slot_name":"logo","name":"block1","list_name":"","type":"Text","is_child":false,"editor_disabled":false,"custom_tag":"rkcms-text","history_name":"2015-05-06-08.45.59","history":[],"revision":1,"is_removed":false,"html":"<p>This is a text<\/p>","editor_configuration":"standard"}}',
                    'root\redkitecms.com\slots\logo\contributors\john\slot.json' => '{"next":2,"blocks":["block1"],"revision":1}',
                ),
            ),
        );
    }
}