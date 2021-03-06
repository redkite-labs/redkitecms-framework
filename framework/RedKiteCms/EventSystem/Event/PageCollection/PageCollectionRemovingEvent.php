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

namespace RedKiteCms\EventSystem\Event\PageCollection;

use RedKiteCms\EventSystem\Event\JsonFileEvent;

/**
 * Class PageCollectionRemovingEvent is the object assigned to implement the event raised before removing a page
 *
 * @author  RedKite Labs <webmaster@redkite-labs.com>
 * @package RedKiteCms\EventSystem\Event\Page
 */
class PageCollectionRemovingEvent extends JsonFileEvent
{
    /**
     * @type string
     */
    private $username;

    /**
     * Constructor
     *
     * @param string $username
     * @param null|string $filePath
     * @param null|string $fileContent
     */
    public function __construct($username, $filePath = null, $fileContent = null)
    {
        parent::__construct($filePath, $fileContent);

        $this->username = $username;
    }

    /**
     * Returns the username
     *
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Sets the username
     * @param $username
     *
     * @return $this
     */
    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }
}