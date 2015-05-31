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

var queue = {};

window.onbeforeunload = function (e) {
    saveQueue();
};

saveQueue = function(){
    var localQueue = queue;
    var queueItems = Object.keys(localQueue).length;
    var hasPendingQueue = localStorage.getItem('rkcms-queue') != null && queueItems > 0;
    if (hasPendingQueue) {
        localQueue = ko.utils.parseJson(localStorage.getItem('rkcms-queue'));
    }

    if (queueItems === 0) {
        return;
    }

    var url = frontcontroller + '/backend/queue/save';
    executeAjax(url,
        {"queue": localQueue},
        function() {
            if (hasPendingQueue) {
                localStorage.clear();
                alertDialog(redkitecmsDomain.frontend_pending_queue, function()
                {
                    location.reload();
                });
            }

            queue = {};

            return true;
        },
        function() {
            localStorage.setItem('rkcms-queue', JSON.stringify(queue));

            return true;
        },
        null,
        false
    );
};

$(document).ready(function(){
    saveQueue();
});