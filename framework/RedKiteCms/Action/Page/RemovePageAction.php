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

namespace RedKiteCms\Action\Page;


use RedKiteCms\Action\BaseAction;
use RedKiteCms\Content\BlockManager\BlockManagerAdd;
use RedKiteCms\Content\Page\PageManager;
use Silex\Application;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class RemovePageAction defines the object assigned to remove a page from the website
 *
 * @package RedKiteCms\Action
 */
class RemovePageAction extends BaseAction
{
    /**
     * {@inheritdoc}
     */
    public function execute(array $options, $username)
    {
        $values = $options["data"];
        $pageManager = $this->app["red_kite_cms.page_collection_manager"];
        $pageManager
            ->contributor($username)
            ->remove($values["name"])
        ;
    }
}