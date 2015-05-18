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

namespace RedKiteCms\Rendering\Controller\Deploy;

use RedKiteCms\Content\BlockManager\BlockManagerApprover;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class SaveAllPagesController is the object assigned to save the website in production
 *
 * @author  RedKite Labs <webmaster@redkite-labs.com>
 * @package RedKiteCms\Rendering\Controller\Page
 */
abstract class SaveAllPagesController extends BaseSavePageController
{
    /**
     * Implements the action to save the website
     * @param array $options
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function save(array $options)
    {
        $serializer = $options["serializer"];
        $deployer = $options["deployer"];
        $languages = $options["configuration_handler"]->languages();

        $blockManager = new BlockManagerApprover($serializer, $options["block_factory"], new OptionsResolver());
        $deployer
            ->contributor($options["username"])
            ->saveAllPages($blockManager, $languages);

        $this->buldSitemap($options);
        $this->removeCache($options);

        return $this->buildJSonResponse(array());
    }
}