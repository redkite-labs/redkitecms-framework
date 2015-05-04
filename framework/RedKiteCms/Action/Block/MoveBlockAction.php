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
use RedKiteCms\Content\BlockManager\BlockManagerMove;
use RedKiteCms\Content\BlockManager\BlockManagerRemove;
use Silex\Application;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class MoveBlockAction defines the object deputed to move a block on a web site page
 *
 * @package RedKiteCms\Action
 */
class MoveBlockAction extends BaseAction
{
    /**
     * {@inheritdoc}
     */
    public function execute(array $options, $username)
    {
        $data = $options["data"];
        $moveOptions = array(
            'page' => $data['page'],
            'language' => $data['language'],
            'country' => $data['country'],
            'sourceSlot' => $data['sourceSlot'],
            'oldName' => $data['oldName'],
            'newName' => $data['newName'],
            'blockname' => $data['name'],
            'position' => $data['position'],
        );

        if (null !== $data['targetSlot']) {
            $moveOptions['targetSlot'] = $data['targetSlot'];
        }

        $blockManager = $this->app["red_kite_cms.blocks_manager_factory"]->create('move');
        
        return $blockManager->move($this->app["red_kite_cms.configuration_handler"]->siteDir(), $moveOptions, $username);
    }
}