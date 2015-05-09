<?php
/**
 * This file is part of the RedKite CMS Application and it is distributed
 * under the GPL LICENSE Version 2.0. To use this application you must leave
 * intact this copyright notice.
 *
 * Copyright (c) RedKite Labs <info@redkite-labs.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * For extra documentation and help please visit http://www.redkite-labs.com
 *
 * @license    GPL LICENSE Version 2.0
 *
 */

namespace RedKiteCms\Bridge\Assetic;

use org\bovigo\vfs\vfsStream;
use RedKiteCms\Bridge\Dispatcher\Dispatcher;
use RedKiteCms\Bridge\Routing\Routing;
use RedKiteCms\Bridge\Routing\RoutingFrontend;
use RedKiteCms\Bridge\Translation\TranslationLoader;
use RedKiteCms\Bridge\Translation\Translator;
use RedKiteCms\TestCase;

/**
 * Class TranslatorLoaderTest
 *
 * @author RedKite Labs <webmaster@redkite-labs.com>
 */
class TranslatorLoaderTest extends TestCase
{
    public function testMessageIsTranslated()
    {
        $translator = $this
            ->getMockBuilder('\Symfony\Component\Translation\Translator')
            ->disableOriginalConstructor()
            ->getMock()
        ;

        $translator
            ->expects($this->at(0))
            ->method('addResource')
            ->with('xliff', vfsStream::url('root/Core/Resources/translations/dictionary.xliff'))
        ;
        $dirs = array(
            'Core' => array(
                'Resources' => array(
                    'translations' => array(
                        'dictionary.xliff' => 'some translations'
                    ),
                ),
            ),
            'Blocks' => array(
                'Resources' => array(
                    'translations' => array(
                        'dictionary.xliff' => 'some translations'
                    ),
                ),
            ),
        );
        $root = vfsStream::setup('root', null, $dirs);
        $folders = array(
            vfsStream::url('root/Core/Resources/translations'),
            vfsStream::url('root/Blocks/Resources/translations'),
        );

        $loader = new TranslationLoader();
        $loader->registerResources($translator, $folders);
    }
}
