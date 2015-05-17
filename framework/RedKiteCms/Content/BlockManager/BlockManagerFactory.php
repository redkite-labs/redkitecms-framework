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

namespace RedKiteCms\Content\BlockManager;

use JMS\Serializer\SerializerInterface;
use RedKiteCms\Content\Block\BlockFactoryInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * This object is assigned to instantiate a new BlockManager object from the action required
 *
 * @author  RedKite Labs <webmaster@redkite-labs.com>
 * @package RedKiteCms\Content\BlockManager
 */
class BlockManagerFactory
{
    /**
     * @var SerializerInterface
     */
    private $serializer;
    /**
     * @var BlockFactoryInterface
     */
    private $blockFactory;
    /**
     * @var OptionsResolver
     */
    private $optionsResolver;

    /**
     * Constructor
     *
     * @param SerializerInterface $serializer
     * @param BlockFactoryInterface $blockFactory
     * @param OptionsResolver $optionsResolver
     */
    public function __construct(SerializerInterface $serializer, BlockFactoryInterface $blockFactory, OptionsResolver $optionsResolver)
    {
        $this->serializer = $serializer;
        $this->blockFactory = $blockFactory;
        $this->optionsResolver = $optionsResolver;
    }

    /**
     * Creates a block manager object
     *
     * @param $action
     * @return null|\RedKiteCms\Content\BlockManager\BlockManager
     */
    public function create($action)
    {
        $actionName = ucfirst($action);
        $class = sprintf('RedKiteCms\Content\BlockManager\BlockManager%s', $actionName);

        if (!class_exists($class)) {
            return null;
        }

        $reflectionClass = new \ReflectionClass($class);

        return $reflectionClass->newInstance($this->serializer, $this->blockFactory, $this->optionsResolver);
    }
}