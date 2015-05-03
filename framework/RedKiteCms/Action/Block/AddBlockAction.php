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

namespace RedKiteCms\Action\Block;

use RedKiteCms\Action\BaseAction;
use RedKiteCms\Content\BlockManager\BlockManagerAdd;
use Silex\Application;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class AddBlockAction defines the object deputed to add a block to a web site page
 *
 * @package RedKiteCms\Action
 */
class AddBlockAction extends BaseAction
{
    /**
     * {@inheritdoc}
     */
    public function execute(array $options, $username)
    {
        $data = $options["data"];
        $addOptions = array(
            'page' => $data['page'],
            'language' => $data['language'],
            'country' => $data['country'],
            'slot' => $data['slot'],
            'blockname' => $data['name'],
            'direction' => $data['direction'],
            'type' => $data['type'],
            'position' => $data['position'],
        );

        $blockManager = new BlockManagerAdd($this->app["jms.serializer"], $this->app["red_kite_cms.block_factory"], new OptionsResolver());

        return $blockManager->add($this->app["red_kite_cms.configuration_handler"]->siteDir(), $addOptions, $username);
    }
}