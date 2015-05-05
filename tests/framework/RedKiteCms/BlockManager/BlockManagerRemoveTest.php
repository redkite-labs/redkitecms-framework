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
use RedKiteCms\Content\BlockManager\BlockManagerRemove;

/**
 * BlockManagerRemoveTest
 *
 * @author RedKite Labs <webmaster@redkite-labs.com>
 */
class BlockManagerRemoveTest extends BlockManagerBaseTestCase
{
    private $blockManager;
    
    protected function setUp()
    {
        parent::setUp();

        $this->blockManager = new BlockManagerRemove($this->serializer, $this->blockFactory, $this->optionsResolver);
    }

    /**
     * @dataProvider deleteProvider
     */
    public function testRemove($folders, $options, $expectedFiles, $expectedNonExistentFiles, $logMessage)
    {
        $this->configureFilesystem($folders);

        $this->dispatch(0, 'block.removing', '\RedKiteCms\EventSystem\Event\Block\BlockRemovingEvent');
        $this->dispatch(1, 'block.removed', '\RedKiteCms\EventSystem\Event\Block\BlockRemovedEvent');
        $this->log(0, 'info', 'The "block.removing" event was dispatched');
        $this->log(1, 'info', 'The "block.removed" event was dispatched');
        $this->log(2, 'info', $logMessage);
        $this->blockManager->remove(vfsStream::url('root\redkitecms.com'), $options, 'john');
        
        $this->checkBlockFiles($expectedFiles);
        $this->checkNonExistentFiles($expectedNonExistentFiles);
    }

    protected function configureContributionManager($username, array $options, $method = 'add')
    {
        $options["block"] = json_decode($options["block"], true);
        $this->contributionManager
            ->expects($this->once())
            ->method($method)
            ->with($username, $options)
        ;
    }

    public function deleteProvider()
    {
        return array(
            // Block1 has been removed
            array(
                array(
                    'redkitecms.com' => array(
                        'slots' => array(
                            'logo' => array(
                                'contributors' => array(
                                    'john' => array(
                                        'blocks' => array(
                                            'block1.json' => '{"slot_name":"logo","name":"block1","list_name":"","type":"Text","block_class":"RedKiteCms\\\\Block\\\\Text\\\\Core\\\\TextBlock","is_child":false,"editor_disabled":false,"custom_tag":"rkcms-text","history_name":"","history":[],"revision":1,"is_removed":false,"html":"<p>This is a text<\/p>","editor_configuration":"standard"}',
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
                    'block' => '{"slot_name":"logo","name":"block1","list_name":"","type":"Text","block_class":"RedKiteCms\\\\Block\\\\Text\\\\Core\\\\TextBlock","is_child":false,"editor_disabled":false,"custom_tag":"rkcms-text","history_name":"","history":[],"revision":1,"is_removed":false,"html":"<p>This is a text<\/p>","editor_configuration":"standard"}',
                ),
                array(
                    'root\redkitecms.com\slots\logo\contributors\john\slot.json' => '{"next":2,"blocks":[],"revision":1}',
                ),
                array(
                    'root\redkitecms.com\slots\logo\contributors\john\blocks\block1.json',
                ),
                'Block "block1" has been removed from the "logo" slot on page "index" for the "en_GB" language',
            ),
            // Block1 has been removed
            array(
                array(
                    'redkitecms.com' => array(
                        'slots' => array(
                            'logo' => array(
                                'contributors' => array(
                                    'john' => array(
                                        'blocks' => array(
                                            'block1.json' => '{"slot_name":"logo","name":"block1","list_name":"","type":"Text","block_class":"RedKiteCms\\\\Block\\\\Text\\\\Core\\\\TextBlock","is_child":false,"editor_disabled":false,"custom_tag":"rkcms-text","history_name":"","history":[],"revision":1,"is_removed":false,"html":"<p>This is a text<\/p>","editor_configuration":"standard"}',
                                            'block2.json' => '{"slot_name":"logo","name":"block2","list_name":"","type":"Text","block_class":"RedKiteCms\\\\Block\\\\Text\\\\Core\\\\TextBlock","is_child":false,"editor_disabled":false,"custom_tag":"rkcms-text","history_name":"","history":[],"revision":1,"is_removed":false,"html":"<p>This is a text<\/p>","editor_configuration":"standard"}',
                                        ),
                                        'slot.json' => '{"next":3,"blocks":["block1", "block2"],"revision":1}',
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
                    'block' => '{"slot_name":"logo","name":"block1","list_name":"","type":"Text","block_class":"RedKiteCms\\\\Block\\\\Text\\\\Core\\\\TextBlock","is_child":false,"editor_disabled":false,"custom_tag":"rkcms-text","history_name":"","history":[],"revision":1,"is_removed":false,"html":"<p>This is a text<\/p>","editor_configuration":"standard"}',
                ),
                array(
                    'root\redkitecms.com\slots\logo\contributors\john\slot.json' => '{"next":3,"blocks":["block2"],"revision":1}',
                ),
                array(
                    'root\redkitecms.com\slots\logo\contributors\john\blocks\block1.json',
                ),
                'Block "block1" has been removed from the "logo" slot on page "index" for the "en_GB" language',
            ),
            // Block2 has been removed
            array(
                array(
                    'redkitecms.com' => array(
                        'slots' => array(
                            'logo' => array(
                                'contributors' => array(
                                    'john' => array(
                                        'blocks' => array(
                                            'block1.json' => '{"slot_name":"logo","name":"block1","list_name":"","type":"Text","block_class":"RedKiteCms\\\\Block\\\\Text\\\\Core\\\\TextBlock","is_child":false,"editor_disabled":false,"custom_tag":"rkcms-text","history_name":"","history":[],"revision":1,"is_removed":false,"html":"<p>This is a text<\/p>","editor_configuration":"standard"}',
                                            'block2.json' => '{"slot_name":"logo","name":"block2","list_name":"","type":"Text","block_class":"RedKiteCms\\\\Block\\\\Text\\\\Core\\\\TextBlock","is_child":false,"editor_disabled":false,"custom_tag":"rkcms-text","history_name":"","history":[],"revision":1,"is_removed":false,"html":"<p>This is a text<\/p>","editor_configuration":"standard"}',
                                            'block3.json' => '{"slot_name":"logo","name":"block3","list_name":"","type":"Text","block_class":"RedKiteCms\\\\Block\\\\Text\\\\Core\\\\TextBlock","is_child":false,"editor_disabled":false,"custom_tag":"rkcms-text","history_name":"","history":[],"revision":1,"is_removed":false,"html":"<p>This is a text<\/p>","editor_configuration":"standard"}',
                                        ),
                                        'slot.json' => '{"next":4,"blocks":["block1", "block2", "block3"],"revision":1}',
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
                    'block' => '{"slot_name":"logo","name":"block2","list_name":"","type":"Text","block_class":"RedKiteCms\\\\Block\\\\Text\\\\Core\\\\TextBlock","is_child":false,"editor_disabled":false,"custom_tag":"rkcms-text","history_name":"","history":[],"revision":1,"is_removed":false,"html":"<p>This is a text<\/p>","editor_configuration":"standard"}',
                ),
                array(
                    'root\redkitecms.com\slots\logo\contributors\john\slot.json' => '{"next":4,"blocks":["block1","block3"],"revision":1}',
                ),
                array(
                    'root\redkitecms.com\slots\logo\contributors\john\blocks\block2.json',
                ),
                'Block "block2" has been removed from the "logo" slot on page "index" for the "en_GB" language',
            ),
        );
    }
}