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

        $blockValues = json_decode($values, true);
        if (array_key_exists("children", $blockValues)) {
            $at = -1;
            for($i=0;$i<3;$i++) {
                $block = $this
                    ->getMockBuilder('\RedKiteCms\Block\IconLinked\Core\IconLinkedBlock')
                    ->disableOriginalConstructor()
                    ->getMock()
                ;
                $block
                    ->expects($this->once())
                    ->method('updateSource')
                    ->will($this->returnValue(array("block" => "block source updated")))
                ;

                $this->blockFactory
                    ->expects($this->at($i))
                    ->method('createBlock')
                    ->with('IconLinked')
                    ->will($this->returnValue($block))
                ;

                $at++;
                $this->serializer

                    ->expects($this->at($at))
                    ->method('serialize')
                    ->with($block, 'json')
                    ->will($this->returnValue('{"block":"encoded"}'))
                ;

                $at++;
                $this->serializer
                    ->expects($this->at($at))
                    ->method('deserialize')
                    ->will($this->returnValue($block))
                ;

                $at++;
                $this->serializer
                    ->expects($this->at($at))
                    ->method('serialize')
                    ->will($this->returnValue('{"block":"encoded"}'))
                ;
            }
        }

        $this->blockManager->edit(vfsStream::url('root\redkitecms.com'), $options, $username, $values);
        if (null === $expectedEditedBlocks) {
            return;
        }
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

    /**
     * @codeCoverageIgnore
     */
    public function editProvider()
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
                    'block' => '{"slot_name":"logo","name":"block1","list_name":"","type":"Text","is_child":false,"editor_disabled":false,"custom_tag":"rkcms-text","history_name":"","history":[],"revision":1,"is_removed":false,"html":"<p>A text changed by John<\/p>","editor_configuration":"standard"}',
                ),
                '{"slot_name":"logo","name":"block1","list_name":"","type":"Text","is_child":false,"editor_disabled":false,"custom_tag":"rkcms-text","history_name":"","history":[],"revision":1,"is_removed":false,"html":"<p>A text changed by John<\/p>","editor_configuration":"standard"}',
                array(
                    'root\redkitecms.com\slots\logo\contributors\john\blocks\block1.json' => '{"slot_name":"logo","name":"block1","list_name":"","type":"Text","is_child":false,"editor_disabled":false,"custom_tag":"rkcms-text","history_name":"","history":[],"revision":1,"is_removed":false,"html":"<p>A text changed by John<\/p>","editor_configuration":"standard"}',
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
                                            'block1.json' => '{"slot_name":"menu","name":"block1","type":"Menu","custom_tag":"rkcms-menu","history_name":"","history":[],"revision":0,"value":"","tags":[],"source":"children:\n  item1:\n    children:\n      item1:\n        value: \'homepage\'\n        tags:\n          class: \'fa fa-home\'\n        type: Icon\n    tags:\n      href: \'en-gb-homepage\'\n    type: IconLinked\n  item2:\n    children:\n      item1:\n        value: \'About\'\n        tags:\n          class: \'fa fa-info-circle\'\n        type: Icon\n    tags:\n      href: \'en-gb-about\'\n    type: IconLinked\n  item3:\n    children:\n      item1:\n        value: \'Contacts\'\n        tags:\n          class: \'fa fa-phone\'\n        type: Icon\n    tags:\n      href: \'en-gb-contacts\'\n    type: IconLinked\n","children":[{"name":"","type":"Foo","custom_tag":"rkcms-icon-linked","history_name":"","history":[],"revision":0,"value":"","tags":{"href":"en-gb-homepage"},"source":"value: \'\'\ntags:\n  href: en-gb-homepage\n","children":{"item1":{"value":"homepage","tags":{"class":"fa fa-home"},"type":"Icon"}}},{"name":"","type":"IconLinked","custom_tag":"rkcms-icon-linked","history_name":"","history":[],"revision":0,"value":"","tags":{"href":"en-gb-about"},"source":"value: \'\'\ntags:\n  href: en-gb-about\n","children":{"item1":{"value":"About","tags":{"class":"fa fa-info-circle"},"type":"Icon"}}},{"name":"","type":"IconLinked","custom_tag":"rkcms-icon-linked","history_name":"","history":[],"revision":0,"value":"","tags":{"href":"en-gb-contacts"},"source":"value: \'\'\ntags:\n  href: en-gb-contacts\n","children":{"item1":{"value":"Contacts","tags":{"class":"fa fa-phone"},"type":"Icon"}}}]}',
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
                '{"slot_name":"menu","name":"block1","type":"Menu","custom_tag":"rkcms-menu","history_name":"","history":[],"revision":0,"value":"","tags":[],"source":"children:\n  item1:\n    children:\n      item1:\n        value: \'The homepage\'\n        tags:\n          class: \'fa fa-home\'\n        type: Icon\n    tags:\n      href: \'en-gb-homepage\'\n    type: IconLinked\n  item2:\n    children:\n      item1:\n        value: \'About\'\n        tags:\n          class: \'fa fa-info-circle\'\n        type: Icon\n    tags:\n      href: \'en-gb-about\'\n    type: IconLinked\n  item3:\n    children:\n      item1:\n        value: \'Contacts\'\n        tags:\n          class: \'fa fa-phone\'\n        type: Icon\n    tags:\n      href: \'en-gb-contacts\'\n    type: IconLinked\n","children":[{"name":"","type":"IconLinked","custom_tag":"rkcms-icon-linked","history_name":"","history":[],"revision":0,"value":"","tags":{"href":"en-gb-homepage"},"source":"value: \'\'\ntags:\n  href: en-gb-homepage\n","children":{"item1":{"value":"homepage","tags":{"class":"fa fa-home"},"type":"Icon"}}},{"name":"","type":"IconLinked","custom_tag":"rkcms-icon-linked","history_name":"","history":[],"revision":0,"value":"","tags":{"href":"en-gb-about"},"source":"value: \'\'\ntags:\n  href: en-gb-about\n","children":{"item1":{"value":"About","tags":{"class":"fa fa-info-circle"},"type":"Icon"}}},{"name":"","type":"IconLinked","custom_tag":"rkcms-icon-linked","history_name":"","history":[],"revision":0,"value":"","tags":{"href":"en-gb-contacts"},"source":"value: \'\'\ntags:\n  href: en-gb-contacts\n","children":{"item1":{"value":"Contacts","tags":{"class":"fa fa-phone"},"type":"Icon"}}}]}',
                null,
            ),
        );
    }
}