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

namespace RedKiteCms\Action\Seo;


use RedKiteCms\Action\BaseAction;
use RedKiteCms\Content\BlockManager\BlockManagerAdd;
use RedKiteCms\Content\Page\PageManager;
use Silex\Application;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class EditSeoAction defines the object assigned to edit a page for a specific language
 *
 * @package RedKiteCms\Action
 */
class EditSeoAction extends BaseAction
{
    /**
     * {@inheritdoc}
     */
    public function execute(array $options, $username)
    {
        $data = $options["data"];
        $pageName = $data["pageName"];
        $seoData = $data["seoData"];
        $pageManager = $this->app["red_kite_cms.page_manager"];
        $pageManager
            ->contributor($username)
            ->edit($pageName, $seoData)
        ;
    }
}