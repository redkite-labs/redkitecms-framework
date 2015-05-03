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

use RedKiteCms\Bridge\Dispatcher\Dispatcher;
use RedKiteCms\Bridge\Monolog\DataLogger;
use RedKiteCms\EventSystem\BlockEvents;
use RedKiteCms\EventSystem\Event\Block\BlockRestoredEvent;
use RedKiteCms\EventSystem\Event\Block\BlockRestoringEvent;

/**
 *
 *
 * @author RedKite Labs <webmaster@redkite-labs.com>
 */
class BlockManagerRestore extends BlockManager
{
    public function restore($sourceDir, array $options, $username, $restoringBlockName)
    {
        $this->createContributorDir($sourceDir, $options, $username);
        $historyFileName = sprintf('%s/%s/history.json', $this->getArchiveDir(), $options["blockname"]);
        $history = json_decode(file_get_contents($historyFileName), true);

        // This happens when a user confirms a restoration then returns back and confirms again.
        // In this case there's nothing to restore.
        if (!array_key_exists($restoringBlockName, $history)) {
            return;
        }
        $restoringBlock = $history[$restoringBlockName];
        $filename = sprintf('%s/blocks/%s.json', $this->contributorDir, $options["blockname"]);
        $currentBlock = file_get_contents($filename);

        Dispatcher::dispatch(
            BlockEvents::BLOCK_RESTORING,
            new BlockRestoringEvent($this->serializer, $filename, $restoringBlock)
        );

        file_put_contents($filename, json_encode($restoringBlock));
        unset($history[$restoringBlockName]);
        $now = date("Y-m-d-H.i.s");
        $history[$now] = json_decode($currentBlock, true);
        file_put_contents($historyFileName, json_encode($history));

        Dispatcher::dispatch(
            BlockEvents::BLOCK_RESTORED,
            new BlockRestoredEvent($this->serializer, $filename, $restoringBlock)
        );

        DataLogger::log(
            sprintf(
                'Block "%s" has been restored as "%s" on the slot "%s" on "%s" page for "%s_%s" language',
                $restoringBlockName,
                $options["blockname"],
                $options["slot"],
                $options["page"],
                $options["language"],
                $options["country"]
            )
        );
    }
}