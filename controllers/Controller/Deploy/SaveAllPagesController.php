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

namespace Controller\Deploy;

use RedKiteCms\Rendering\Controller\Deploy\SaveAllPagesController as BaseSaveAllPagesController;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;

/**
 * This object implements the Silex controller to save the whole website
 *
 * @author  RedKite Labs <webmaster@redkite-labs.com>
 * @package Controller\Page
 */
class SaveAllPagesController extends BaseSaveAllPagesController
{
    /**
     * Save site action
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \Silex\Application                        $app
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function saveAction(Request $request, Application $app)
    {
        $options = array(
            "request" => $request,
            "deployer" => $app["red_kite_cms.deployer"],
            "block_factory" => $app["red_kite_cms.block_factory"],
            "sitemap_generator" => $app["red_kite_cms.sitemap_generator"],
            "serializer" => $app["jms.serializer"],
            "configuration_handler" => $app["red_kite_cms.configuration_handler"],
            "username" => $this->fetchUsername($app["security"], $app["red_kite_cms.configuration_handler"]),
        );

        return parent::save($options);
    }
}