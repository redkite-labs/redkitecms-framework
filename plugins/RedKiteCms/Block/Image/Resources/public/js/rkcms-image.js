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

var Image = function (params)
{
    var self = this;
    Extendable.call(self, params);
    self.href = ko.observable(params.block.href);
    self.isLinked = ko.computed(function(){
        return self.href() != "";
    });    
    self.selectedImage = ko.observable('');
    self.toolbar.push("image-button", "permalinks");

    _initImageEditor = function(){
        $(".rkcms-ace-editor:visible").aceEditor('open', { width: 600 });
    };
};

Image.prototype = Object.create(Extendable.prototype);
Image.prototype.constructor = Image;
Image.prototype.startBlockEditing = function()
{
    if (Block.prototype.startBlockEditing.call(this)){
        return true;
    }

    _initImageEditor();
};
Image.prototype.doClosePanel = function(view)
{
    Block.prototype.doClosePanel(view);

    if ($(document).data('rkcms-active-model') != null) {
        _initImageEditor();
    }
};

Image.prototype.blockToJson = function()
{
    var self = this;
    var block = self.block;
    block.value = self.value();
    block.tags = self.tags();
    block.href = self.href();
    block.source = self.source;

    return ko.toJSON(block);
};

ko.components.register('rkcms-image', {
    viewModel: Image,
    template: { element: 'rkcms-image-editor' }
});