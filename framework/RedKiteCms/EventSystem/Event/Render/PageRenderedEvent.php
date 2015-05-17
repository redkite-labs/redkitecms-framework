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

namespace RedKiteCms\EventSystem\Event\Render;

use RedKiteCms\EventSystem\Event\Event;
use RedKiteCms\FilesystemEntity\Page;

/**
 * Class PageRenderedEvent is the object assigned to implement the event raised after rendering a page
 *
 * Connect to this event when you need to replace change dynamically the page attributes
 *
 * @author  RedKite Labs <webmaster@redkite-labs.com>
 * @package RedKiteCms\EventSystem\Event\Render
 */
class PageRenderedEvent extends Event
{
    protected $page;

    public function __construct(Page $page)
    {
        $this->page = $page;
    }

    /**
     * @return Page
     */
    public function getPage()
    {
        return $this->page;
    }

    /**
     * @param Page $page
     */
    public function setPage($page)
    {
        $this->page = $page;
    }
}