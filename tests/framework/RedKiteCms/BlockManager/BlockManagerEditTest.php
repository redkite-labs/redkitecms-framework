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
use RedKiteCms\Content\BlockManager\BlockManagerEdit;

/**
 * BlockManagerEditTest
 *
 * @author RedKite Labs <webmaster@redkite-labs.com>
 */
class BlockManagerEditTest extends BlockManagerBaseTestCase
{    
    private $blockManager;
    
    protected function setUp()
    {
        parent::setUp();

        $this->blockManager = new BlockManagerEdit($this->serializer, $this->blockFactory, $this->optionsResolver);
    }

    /**
     * @dataProvider editProvider
     */
    public function testEdit($folders, $options, $values, $expectedEditedBlocks, $username = 'john')
    {
        $this->configureFilesystem($folders);

        $this->checkDispatcher();
        $this->checkLogger('Block "block1" has been edited on the "logo" slot on page "index" for the "en_GB" language');
        $this->blockManager->edit(vfsStream::url('root\redkitecms.com'), $options, $username, $values);
        $this->checkBlockFiles($expectedEditedBlocks);
    }

    private function checkDispatcher()
    {
        $this->dispatch(0, 'block.editing', '\RedKiteCms\EventSystem\Event\Block\BlockEditingEvent');
        $this->dispatch(1, 'block.edited', '\RedKiteCms\EventSystem\Event\Block\BlockEditedEvent');
    }

    private function checkLogger($logMessage)
    {
        $this->log(0, 'info', 'The "block.editing" event was dispatched');
        $this->log(1, 'info', 'The "block.edited" event was dispatched');
        $this->log(2, 'info', $logMessage);
    }

    public function editProvider()
    {
        return array(
            // Block1 has been edited
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
                    'blockname' => 'block1',
                    'baseBlock' => '{"slot_name":"logo","name":"block1","list_name":"","type":"Text","is_child":false,"editor_disabled":false,"custom_tag":"rkcms-text","history_name":"","history":[],"revision":1,"is_removed":false,"html":"<p>This is a text<\/p>","editor_configuration":"standard"}',
                    'block' => '{"slot_name":"logo","name":"block1","list_name":"","type":"Text","is_child":false,"editor_disabled":false,"custom_tag":"rkcms-text","history_name":"","history":[],"revision":1,"is_removed":false,"html":"<p>A text changed by John<\/p>","editor_configuration":"standard"}',
                ),
                '{"slot_name":"logo","name":"block1","list_name":"","type":"Text","is_child":false,"editor_disabled":false,"custom_tag":"rkcms-text","history_name":"","history":[],"revision":1,"is_removed":false,"html":"<p>A text changed by John<\/p>","editor_configuration":"standard"}',
                array(
                    'root\redkitecms.com\slots\logo\contributors\john\blocks\block1.json' => '{"slot_name":"logo","name":"block1","list_name":"","type":"Text","is_child":false,"editor_disabled":false,"custom_tag":"rkcms-text","history_name":"","history":[],"revision":1,"is_removed":false,"html":"<p>A text changed by John<\/p>","editor_configuration":"standard"}',
                ),
            ),
        );
    }
}