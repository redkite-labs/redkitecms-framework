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

use RedKiteCms\Bridge\Dispatcher\Dispatcher;
use RedKiteCms\Bridge\Routing\Routing;
use RedKiteCms\Bridge\Routing\RoutingFrontend;
use RedKiteCms\Bridge\Translation\Translator;
use RedKiteCms\TestCase;

/**
 * Class TranslatorTest
 *
 * @author RedKite Labs <webmaster@redkite-labs.com>
 */
class TranslatorTest extends TestCase
{
    public function testMessageIsNotTranslatedWhenTranslatorHasNotBeenSet()
    {
        $message = "An awesome message";
        $translatedMessage = Translator::translate($message);

        $this->assertEquals($translatedMessage, $message);
    }

    public function testMessageIsTranslated()
    {
        $message = "An awesome message";
        $expectedMessage = "Translated message";

        $parameters = array('foo' => 'bar,');
        $domain = "RedKiteCms";
        $locale = 'it';

        $translator = $this->getMock('Symfony\Component\Translation\TranslatorInterface');
        $translator
            ->expects($this->once())
            ->method('trans')
            ->with($message, $parameters, $domain, $locale)
            ->will($this->returnValue($expectedMessage))
        ;

        Translator::setTranslator($translator);
        $translatedMessage = Translator::translate($message, $parameters, $domain, $locale);

        $this->assertEquals($translatedMessage, $expectedMessage);
    }
}
