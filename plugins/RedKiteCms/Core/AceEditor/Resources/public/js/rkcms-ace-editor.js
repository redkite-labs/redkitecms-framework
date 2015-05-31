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

(function($){
    "use strict";
    
    var AceEditor = function(element, options)
    {
        var self = this;
        self._element = element;
        self._options = $.extend({}, AceEditor.DEFAULTS, options);
        self._editor = null;
        self._forceEditorInit = false;
        self._timer1 = null;
        self._model = null;
        self._visible = true;

        this._placeEditor = function()
        {
            window.setTimeout(function()
            {
                var width, height;
                var target = blockEditorModel.activeModel.target();
                var $target = $(target);

                $(".rkcms-blocks-editor:visible")
                    .removeClass('fullscreen')
                ;
                if (blockEditorModel.mode() == 'fullscreen') {
                    $(".rkcms-blocks-editor:visible")
                        .addClass('fullscreen')
                        .width('100%')
                        .height('100%')
                        .position({
                            of: window,
                            my: "left top",
                            at: "left top",
                            collision: "none"
                        })
                    ;

                    _resize(
                        $(".rkcms-blocks-editor:visible").width() - 22,
                        $(".rkcms-blocks-editor:visible").height() - 58
                    );

                    return;
                }

                if (blockEditorModel.mode() == 'inline') {
                    width = self._options.width;
                    height = self._options.height;
                    var $element = $target;
                    if ($target.height() == 0) {
                        // Finds the slot container to try to get its height
                        $element = $target.closest('.rkcms-slot').parent();
                    }

                    $(".rkcms-blocks-editor:visible")
                        .width(width)
                        .height(height)
                        .position({
                            of: $element,
                            my: "left-4 top+4",
                            at: "left bottom",
                            collision: "none"
                        })
                    ;

                    _resize(width - 22, height);

                    return;
                }

                $(".rkcms-blocks-editor:visible")
                    .width($target.width())
                    .height($target.height())
                    .position({
                        of: $target,
                        my: "left top",
                        at: "left top",
                        collision: "none"
                    })
                ;

                width = $target.width() - 20;
                height = $target.height() - $('.rkcms-blocks-editor-toolbar:visible').height() - 20;
                if (height < 300) {
                    height = self._options.height;
                }
                _resize(width, height);
            }, 1);
        };

        function _resize(width, height)
        {
            var $editor = $(self._element);
            $editor
                .width(width + 'px')
                .height(height + 'px')
            ;
            self._editor.resize();
        }
    };
    
    AceEditor.DEFAULTS = {
        theme: 'twilight',
        mode: 'yaml',
        width: '450',
        height: '150'
    };

    AceEditor.prototype.open = function()
    {
        var self = this;
        if(!self._forceEditorInit && self._model != null && self._model == $(document).data('rkcms-active-model')) {
            return;
        }

        $(self._element)
            .width(self._options.width)
            .height(self._options.height)
        ;

        if(self._editor != null) {
            self._editor.destroy();
        }

        self._model = blockEditorModel.activeModel;
        if (self._model == null) {
            return;
        }

        self._editor = ace.edit(self._element);
        self._editor.setValue(self._model.source, -1);
        self._editor.setTheme("ace/theme/" + self._options.theme);
        self._editor.setFontSize(14);
        self._editor.getSession().setMode("ace/mode/" + self._options.mode);
        self._editor.getSession().setUseWrapMode(true);
        self._editor.on("change", function(event, editor) {
            window.clearTimeout(self._timer1);
            var content = editor.getValue();
            if (self._options.mode != 'yaml')
            {
                self._timer1 = window.setTimeout(_update, 500, content, content);

                return;
            }
            self._timer1 = window.setTimeout(_parseYaml, 500, content);
        });
        self._model.editor = self._editor;
        this._placeEditor(this._editor);

        function _update(content, source)
        {
            var model = self._model;
            if (model.source != content) {
                self._forceEditorInit = true;
                if (model.update(content, source) == false){
                    return;
                }
                model.resize();
                self._placeEditor();
                _save();
            }
        }

        function _parseYaml(content)
        {
            var RkCmsYamlType = new jsyaml.Type('!rkcms', {
                kind: 'sequence',
                construct: function (data) {
                    return data.map(function (string) { return 'rkcms ' + string; });
                }
            });
            var RKCMS_SCHEMA = jsyaml.Schema.create([ RkCmsYamlType ]);

            blockEditorModel.error("");
            try {
                var obj = jsyaml.load(content, { schema: RKCMS_SCHEMA });
                inspect(obj, false, 10);

                _update(obj, content);
            } catch (err) {
                var msg = '<p>The yml code you entered is malformed:<br />' + err.message + '</p>';
                blockEditorModel.error(msg);
            }
        }

        function _save()
        {
            blockEditorModel.edit();
        }
    };

    AceEditor.prototype.close = function()
    {
        this._editor.destroy();
    };

    AceEditor.prototype.place = function()
    {
        this._placeEditor();
    };

    AceEditor.prototype.toggle = function()
    {
        $(".rkcms-blocks-editor:visible").toggle();
    };

    // YMLEDITOR EDITOR PLUGIN DEFINITION
    // ==================================
    var old = $.fn.aceEditor;

    $.fn.aceEditor = function (command, options) {
        return this.each(function () {
            var $this = $(this);

            var data = $this.data('rkcms.ace_editor');
            var parsedOptions = $.extend({}, AceEditor.DEFAULTS, typeof options == 'object' && options);

            parsedOptions.width = parsedOptions.width.replace( /[^\d.]/g, '' );
            parsedOptions.height = parsedOptions.height.replace( /[^\d.]/g, '' );

            // Rebuilds ace editor only when an editor should be opened and the model has been changed,
            // because just destroying the editor before opening it causes a wrong behavior
            if (data && blockEditorModel.isModelChanged() && command == 'open') {
                data.close();

                // Forces the plugin to rebuild the editor
                data = false;
            }

            if (!data) {
                $this.data('rkcms.ace_editor', (data = new AceEditor(this, parsedOptions)));
            }

            if (typeof command == 'string') {
                data[command]();

                if (command == 'destroy') {
                    $this.removeData('rkcms.ace_editor');
                }
            }
        });
    };

    $.fn.aceEditor.Constructor = AceEditor;

    // YMLEDITOR EDITOR NO CONFLICT
    // ============================
    $.fn.aceEditor.noConflict = function () {
        $.fn.aceEditor = old;

        return this;
    };
    
})(jQuery);