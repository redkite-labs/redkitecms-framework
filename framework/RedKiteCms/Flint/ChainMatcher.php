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

namespace RedKiteCms\Flint;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Matcher\RequestMatcherInterface;
use Symfony\Component\Routing\Matcher\UrlMatcherInterface;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\Exception\MethodNotAllowedException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;

/**
 * ChainMatcher.
 *
 * This wraps multiple UrlMatcherInterface's in order to not overwrite Silex
 * internals.
 *
 * Copyright (c) 2013 Henrik Bjørnskov
 *
 */
class ChainMatcher implements UrlMatcherInterface, RequestMatcherInterface
{
    protected $matchers = array();
    protected $context;

    public function __construct($matchers = array())
    {
        foreach ($matchers as $matcher) {
            $this->add($matcher);
        }
    }

    public function add(UrlMatcherInterface $matcher, $priority = 0)
    {
        $this->matchers[$priority][] = $matcher;
    }

    public function setContext(RequestContext $context)
    {
        $this->context = $context;
    }

    public function getContext()
    {
        return $this->context;
    }

    public function matchRequest(Request $request)
    {
        return $this->doMatch($request->getPathInfo(), $request);
    }

    public function match($pathinfo)
    {
        return $this->doMatch($pathinfo);
    }

    protected function doMatch($pathinfo, Request $request = null)
    {
        $notAllowed = null;

        foreach ($this->sort() as $matcher) {
            $matcher->setContext($this->context);

            try {
                if ($request && $matcher instanceof RequestMatcherInterface) {
                    return $matcher->matchRequest($request);
                }

                return $matcher->match($pathinfo);
            } catch (ResourceNotFoundException $e) {
                // Special case
            } catch (MethodNotAllowedException $e) {
                $notAllowed = $e;
            }
        }

        if ($notAllowed) {
            throw $notAllowed;
        }

        $info = $request ? 'this request\n' . $request : 'url "' . $pathinfo . '"';

        throw new ResourceNotFoundException('None of the routers in the chain matched ' . $info);
    }

    protected function sort()
    {
        $matchers = array();

        krsort($this->matchers);

        foreach ($this->matchers as $collection) {
            $matchers = array_merge($matchers, $collection);
        }

        return $matchers;
    }
}
