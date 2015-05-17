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

namespace RedKiteCms\EventSystem\Event\Block;

use JMS\Serializer\SerializerInterface;

/**
 * Class BlockRestoringEvent is the object assigned to
 *
 * @author  RedKite Labs <webmaster@redkite-labs.com>
 * @package RedKiteCms\EventSystem\Event\Block
 */
class BlockRestoringEvent extends BlockEventBase
{
    /**
     * @type string
     */
    protected $restoringBlock;

    /**
     * Constructor
     *
     * @param \JMS\Serializer\SerializerInterface $serializer
     * @param null $filePath
     * @param null $archiveFilePath
     * @param null $fileContent
     * @param null $blockClass
     */
    public function __construct(
        SerializerInterface $serializer,
        $filePath = null,
        $restoringBlock = null,
        $fileContent = null,
        $blockClass = null
    ) {
        parent::__construct($serializer, $filePath, $fileContent, $blockClass);

        $this->restoringBlock = $restoringBlock;
    }

    /**
     * Returns the restoring block
     *
     * @return string
     */
    public function getRestoringBlock()
    {
        return $this->restoringBlock;
    }

    /**
     * Sets the restoring block
     * @param $restoringBlock
     *
     * @return $this
     */
    public function setRestoringBlock($restoringBlock)
    {
        $this->restoringBlock = $restoringBlock;

        return $this;
    }
}