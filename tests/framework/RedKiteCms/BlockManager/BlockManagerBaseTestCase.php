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
use RedKiteCms\Bridge\Dispatcher\Dispatcher;
use RedKiteCms\Content\BlockManager\BlockManagerApprover;
use RedKiteCms\TestCase;

/**
 * BlockManagerApproverTest
 *
 * @author RedKite Labs <webmaster@redkite-labs.com>
 */
class BlockManagerBaseTestCase extends TestCase
{
    protected $serializer;
    protected $root;
    protected $optionsResolver;
    protected $blockFactory;
    
    protected function setUp()
    {
        parent::setUp();

        $this->serializer = $this->getMock('JMS\Serializer\SerializerInterface');
        $this->optionsResolver = $this->getMock('Symfony\Component\OptionsResolver\OptionsResolver');
        $this->blockFactory = $this->getMock('\RedKiteCms\Content\Block\BlockFactoryInterface');
    }
    
    protected function checkBlockFiles(array $files)
    {
        foreach ($files as $file => $contents) {
            $filePath = vfsStream::url($file);
            $this->assertFileExists($filePath);
            $this->assertEquals($contents, file_get_contents($filePath));
        }
    }
    
    protected function checkNonExistentFiles(array $files)
    {
        foreach ($files as $file) {
            $filePath = vfsStream::url($file);
            $this->assertFileNotExists($filePath);
        }
    }

    protected function configureFilesystem($folders)
    {
        $this->root = vfsStream::setup('root', null, $folders);
    }
}